<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartUpdate;
use App\Models\DeviceToken;
use App\Models\Follow;
use App\Models\Notification;
use App\Models\PushDelivery;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationDispatcher
{
    public function fanout(): void
    {
        CartUpdate::whereNull('fanout_at')->whereHas('cart', fn ($q) => $q->visible())->orderBy('id')->chunkById(50, function ($updates) {
            foreach ($updates as $update) {
                DB::transaction(function () use ($update) {
                    $locked = CartUpdate::lockForUpdate()->find($update->id);
                    if (! $locked || $locked->fanout_at) {
                        return;
                    }
                    User::where('is_active', true)->whereIn('id', Follow::where('cart_id', $update->cart_id)->where('is_following', true)->where('created_at', '<=', $update->created_at)->select('user_id'))->with('settings')->chunkById(100, function ($users) use ($update) {
                        foreach ($users as $user) {
                            if ($user->settings?->updates_enabled ?? true) {
                                $this->notify($user, $update->cart_id, 'update', $update->title, $update->body, 'update:'.$update->id.':user:'.$user->id);
                            }
                        }
                    });
                    $locked->update(['fanout_at' => now()]);
                });
            }
        });
    }

    public function nearby(): void
    {
        $cutoff = now()->subMinutes((int) Setting::valueOf('location_max_age_minutes', 30));
        User::where('is_active', true)->whereHas('location', fn ($q) => $q->where('updated_at', '>=', $cutoff))->with(['settings', 'location'])->chunkById(100, function ($users) use ($cutoff) {
            foreach ($users as $user) {
                if (! ($user->settings?->nearby_enabled ?? true)) {
                    continue;
                }
                $radius = $user->settings?->radius_meters ?? 5000;
                Cart::visible()->where('status', 'open')->whereIn('id', Follow::where('user_id', $user->id)->where('is_following', true)->select('cart_id'))->whereHas('location', fn ($q) => $q->where('updated_at', '>=', $cutoff))->with('location')->chunkById(100, function ($carts) use ($user, $radius) {
                    foreach ($carts as $cart) {
                        if (Geo::meters($user->location->latitude, $user->location->longitude, $cart->location->latitude, $cart->location->longitude) > $radius) {
                            continue;
                        }
                        if (Notification::where('user_id', $user->id)->where('cart_id', $cart->id)->where('type', 'nearby')->where('created_at', '>', now()->subMinutes(10))->exists()) {
                            continue;
                        }
                        $this->notify($user, $cart->id, 'nearby', $cart->name.' is nearby', 'A cart you follow is open within your notification radius.', 'nearby:'.$cart->id.':'.$user->id.':'.intdiv(now()->timestamp, 600));
                    }
                });
            }
        });
    }

    private function notify(User $user, int $cartId, string $type, string $title, string $body, string $key): void
    {
        DB::transaction(function () use ($user, $cartId, $type, $title, $body, $key) {
            $notification = Notification::firstOrCreate(['dedupe_key' => $key], ['user_id' => $user->id, 'cart_id' => $cartId, 'type' => $type, 'title' => $title, 'body' => $body]);
            if (! ($user->settings?->push_enabled ?? true)) {
                return;
            }
            foreach (DeviceToken::where('user_id', $user->id)->cursor() as $device) {
                PushDelivery::firstOrCreate(['notification_id' => $notification->id, 'device_token_id' => $device->id], ['available_at' => now()]);
            }
        });
    }

    public function deliver(PushGateway $gateway): void
    {
        if (config('fudo.push_driver') === 'disabled' || ! (bool) Setting::valueOf('push_enabled', 1)) {
            return;
        }
        PushDelivery::where('status', 'pending')->where('available_at', '<=', now())->orderBy('id')->limit(100)->get()->each(function ($delivery) use ($gateway) {
            $notification = $delivery->notification;
            $user = $notification?->user;
            $device = $delivery->deviceToken;
            $settings = $user?->settings;
            if (! $user?->is_active || ! $device || $device->user_id !== $user->id || ! ($settings?->push_enabled ?? true) || ! ($notification->type === 'nearby' ? ($settings?->nearby_enabled ?? true) : ($settings?->updates_enabled ?? true)) || ! Cart::visible()->whereKey($notification->cart_id)->exists() || ! Follow::where('user_id', $user->id)->where('cart_id', $notification->cart_id)->where('is_following', true)->exists()) {
                $delivery->update(['status' => 'skipped']);

                return;
            }
            try {
                $gateway->send($device, $notification);
                $delivery->update(['status' => 'sent', 'attempts' => $delivery->attempts + 1, 'last_error' => null]);
            } catch (\Throwable $e) {
                $attempts = $delivery->attempts + 1;
                $delivery->update(['attempts' => $attempts, 'status' => $attempts >= 5 ? 'failed' : 'pending', 'available_at' => now()->addSeconds(min(3600, 60 * 2 ** $attempts)), 'last_error' => 'Push provider failed; check server credentials and connectivity.']);
                report($e);
            }
        });
    }
}
