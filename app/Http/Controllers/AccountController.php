<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartUpdate;
use App\Models\DeviceToken;
use App\Models\Follow;
use App\Models\Notification;
use App\Models\Report;
use App\Models\User;
use App\Models\UserLocation;
use App\Models\UserSetting;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function profile(Request $r)
    {
        $r->user()->update($r->validate(['name' => 'required|string|max:100']));

        return $r->user();
    }

    public function readAll(Request $r)
    {
        Notification::where('user_id', $r->user()->id)->whereNull('read_at')->update(['read_at' => now()]);

        return response()->noContent();
    }

    public function report(Request $r)
    {
        $data = $r->validate(['target_type' => 'required|in:cart,user', 'target_id' => 'required|integer', 'reason' => 'required|in:spam,fakeListing,wrongLocation,offensive,other', 'note' => 'nullable|string|max:2000']);
        $model = $data['target_type'] === 'cart' ? Cart::class : User::class;
        abort_unless($model::whereKey($data['target_id'])->exists(), 422, 'Target does not exist.');

        return response()->json(Report::create($data + ['reporter_id' => $r->user()->id]), 201);
    }

    public function me(Request $r)
    {
        return $r->user();
    }

    public function settings(Request $r)
    {
        return UserSetting::firstOrCreate(['user_id' => $r->user()->id])->fresh();
    }

    public function saveSettings(Request $r)
    {
        return UserSetting::updateOrCreate(['user_id' => $r->user()->id], $r->validate(['push_enabled' => 'sometimes|boolean', 'nearby_enabled' => 'sometimes|boolean', 'updates_enabled' => 'sometimes|boolean', 'radius_meters' => 'sometimes|integer|between:100,50000']));
    }

    public function location(Request $r)
    {
        return tap(UserLocation::updateOrCreate(['user_id' => $r->user()->id], $r->validate(['latitude' => 'required|numeric|between:-90,90', 'longitude' => 'required|numeric|between:-180,180'])), fn ($location) => $location->touch());
    }

    public function forgetLocation(Request $r)
    {
        UserLocation::where('user_id', $r->user()->id)->delete();

        return response()->noContent();
    }

    public function following(Request $r)
    {
        return Cart::visible()->whereIn('id', Follow::where('user_id', $r->user()->id)->where('is_following', true)->select('cart_id'))->with(['location', 'photos'])->paginate(25);
    }

    public function updates(Request $r)
    {
        return CartUpdate::whereHas('cart', fn ($q) => $q->visible())->whereIn('cart_id', Follow::where('user_id', $r->user()->id)->where('is_following', true)->select('cart_id'))->with('cart')->latest()->paginate(25);
    }

    public function device(Request $r)
    {
        $data = $r->validate(['token' => 'required|string|max:4096', 'platform' => 'required|in:android,ios,web']);

        return DeviceToken::updateOrCreate(['token_hash' => hash('sha256', $data['token'])], $data + ['user_id' => $r->user()->id]);
    }

    public function deleteDevice(Request $r, DeviceToken $device)
    {
        abort_unless($device->user_id === $r->user()->id, 404);
        $device->delete();

        return response()->noContent();
    }

    public function notifications(Request $r)
    {
        return Notification::where('user_id', $r->user()->id)->latest()->paginate(25);
    }

    public function read(Request $r, Notification $notification)
    {
        abort_unless($notification->user_id === $r->user()->id, 404);
        $notification->update(['read_at' => now()]);

        return $notification;
    }
}
