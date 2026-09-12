<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartLocation;
use App\Models\CartSchedule;
use App\Models\CartUpdate;
use App\Models\Follow;
use App\Models\Photo;
use App\Models\Setting;
use App\Services\Geo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class CartController extends Controller
{
    public function index(Request $r)
    {
        $data = $r->validate(['search' => 'nullable|string|max:100', 'latitude' => 'required_with:longitude|numeric|between:-90,90', 'longitude' => 'required_with:latitude|numeric|between:-180,180', 'radius_meters' => 'sometimes|integer|min:100|max:50000']);
        $query = Cart::visible()->with(['location', 'photos', 'schedules']);
        if (! empty($data['search'])) {
            $query->where('name', 'like', '%'.$data['search'].'%');
        }
        if (isset($data['latitude'],$data['longitude'])) {
            $radius = $data['radius_meters'] ?? 5000;
            // Exact distance is calculated after a portable latitude bounding filter.
            $query->whereHas('location', fn ($q) => $q->where('updated_at', '>=', now()->subMinutes((int) Setting::valueOf('location_max_age_minutes', 30)))->whereBetween('latitude', [$data['latitude'] - $radius / 111000, $data['latitude'] + $radius / 111000]));
            $carts = $query->get()->each(function ($c) use ($data) {
                $c->distance_meters = round(Geo::meters($data['latitude'], $data['longitude'], $c->location->latitude, $c->location->longitude));
            })->filter(fn ($c) => $c->distance_meters <= $radius)->sortBy('distance_meters')->values();
            $page = max(1, (int) $r->input('page', 1));

            return new LengthAwarePaginator($carts->forPage($page, 25)->values(), $carts->count(), 25, $page, ['path' => $r->url(), 'query' => $r->query()]);
        }

        return $query->latest()->paginate(25);
    }

    public function show(Cart $cart)
    {
        abort_unless(Cart::visible()->whereKey($cart->id)->exists(), 404);

        return $cart->load(['location', 'photos', 'schedules']);
    }

    public function mine(Request $r)
    {
        return Cart::where('owner_id', $r->user()->id)->with(['location', 'photos', 'schedules'])->paginate(25);
    }

    public function store(Request $r)
    {
        $data = $r->validate($this->rules());

        return response()->json(Cart::create($data + ['owner_id' => $r->user()->id]), 201);
    }

    public function update(Request $r, Cart $cart)
    {
        $this->authorizeCart($r, $cart);
        $cart->update($r->validate($this->rules(false)));

        return $cart;
    }

    public function destroy(Request $r, Cart $cart)
    {
        $this->authorizeCart($r, $cart);
        $paths = $cart->photos()->pluck('path')->all();
        $cart->delete();
        Storage::disk('public')->delete($paths);

        return response()->noContent();
    }

    public function location(Request $r, Cart $cart)
    {
        $this->authorizeCart($r, $cart);

        return tap(CartLocation::updateOrCreate(['cart_id' => $cart->id], $r->validate(['latitude' => 'required|numeric|between:-90,90', 'longitude' => 'required|numeric|between:-180,180', 'address' => 'nullable|string|max:255'])), fn ($location) => $location->touch());
    }

    public function updates(Cart $cart)
    {
        $this->show($cart);

        return CartUpdate::where('cart_id', $cart->id)->latest()->paginate(25);
    }

    public function postUpdate(Request $r, Cart $cart)
    {
        $this->authorizeCart($r, $cart);
        abort_unless($cart->moderation_status === 'approved', 403, 'Cart must be approved before publishing.');

        return response()->json(CartUpdate::create($r->validate(['title' => 'required|string|max:150', 'body' => 'required|string|max:3000', 'type' => 'sometimes|in:announcement,opened,closed,moved,scheduleChanged,photoAdded']) + ['cart_id' => $cart->id]), 201);
    }

    public function schedule(Request $r, Cart $cart)
    {
        $this->authorizeCart($r, $cart);
        $data = $r->validate(['day_of_week' => 'required|integer|between:1,7', 'opens_at' => 'required|date_format:H:i', 'closes_at' => 'required|date_format:H:i', 'timezone' => 'required|timezone', 'address' => 'nullable|string|max:255', 'specific_date' => 'nullable|date_format:Y-m-d', 'is_active' => 'sometimes|boolean', 'latitude' => 'nullable|required_with:longitude|numeric|between:-90,90', 'longitude' => 'nullable|required_with:latitude|numeric|between:-180,180']);

        return CartSchedule::updateOrCreate(['cart_id' => $cart->id, 'day_of_week' => $data['day_of_week'], 'specific_date' => $data['specific_date'] ?? null], $data);
    }

    public function deleteSchedule(Request $r, Cart $cart, CartSchedule $schedule)
    {
        $this->authorizeCart($r, $cart);
        abort_unless($schedule->cart_id === $cart->id, 404);
        $schedule->delete();

        return response()->noContent();
    }

    public function photo(Request $r, Cart $cart)
    {
        $this->authorizeCart($r, $cart);
        $r->validate(['photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120']);
        abort_if($cart->photos()->count() >= 10, 422, 'A cart can have at most ten photos.');
        $path = $r->file('photo')->store('carts/'.$cart->id, 'public');
        try {
            $photo = Photo::create(['cart_id' => $cart->id, 'path' => $path]);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }

        return response()->json($photo, 201);
    }

    public function deletePhoto(Request $r, Cart $cart, Photo $photo)
    {
        $this->authorizeCart($r, $cart);
        abort_unless($photo->cart_id === $cart->id, 404);
        $photo->delete();
        Storage::disk('public')->delete($photo->path);

        return response()->noContent();
    }

    public function follow(Request $r, Cart $cart)
    {
        $this->show($cart);

        return Follow::updateOrCreate(['user_id' => $r->user()->id, 'cart_id' => $cart->id], ['is_following' => true]);
    }

    public function unfollow(Request $r, Cart $cart)
    {
        Follow::where('user_id', $r->user()->id)->where('cart_id', $cart->id)->update(['is_following' => false, 'updated_at' => now()]);

        return response()->noContent();
    }

    private function authorizeCart(Request $r, Cart $cart): void
    {
        abort_unless($r->user()->role === 'admin' || ($r->user()->role === 'owner' && $cart->owner_id === $r->user()->id), 403);
    }

    private function rules(bool $creating = true): array
    {
        return ['name' => ($creating ? 'required' : 'sometimes').'|string|max:100', 'description' => 'nullable|string|max:3000', 'cuisine' => 'nullable|string|max:100', 'status' => 'sometimes|in:open,closed'];
    }
}
