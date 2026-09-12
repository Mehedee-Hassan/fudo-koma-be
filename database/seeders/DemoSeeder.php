<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartLocation;
use App\Models\CartUpdate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new \RuntimeException('Demo data is only available locally.');
        }
        $owner = User::firstOrCreate(['email' => 'demo-owner@example.test'], ['name' => 'Neighborhood Kitchen', 'password' => Str::random(48)]);
        $owner->forceFill(['role' => 'owner'])->save();
        foreach ([['Tokyo Taco Club', 'Mexican', 35.6812, 139.7671], ['Little Dumpling', 'Asian', 35.6852, 139.7701], ['The Coffee Stop', 'Coffee', 35.6772, 139.7631], ['Green Bowl', 'Healthy', 35.6872, 139.7751], ['Smoky Wheels', 'Barbecue', 35.6782, 139.7591], ['Sweet Sunday', 'Dessert', 35.6902, 139.7651], ['Curry Corner', 'Japanese', 35.6732, 139.7701]] as [$name,$cuisine,$lat,$lon]) {
            $cart = Cart::firstOrCreate(['name' => $name, 'owner_id' => $owner->id], ['cuisine' => $cuisine, 'description' => 'Fresh food from your neighborhood.', 'status' => 'open', 'moderation_status' => 'approved']);
            CartLocation::updateOrCreate(['cart_id' => $cart->id], ['latitude' => $lat, 'longitude' => $lon, 'address' => 'Tokyo station neighborhood']);
            CartUpdate::firstOrCreate(['cart_id' => $cart->id, 'title' => 'Ready to serve'], ['body' => 'Come by for something delicious.', 'fanout_at' => now()]);
        }
    }
}
