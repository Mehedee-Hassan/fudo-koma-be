<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\Cart;
use App\Models\CartLocation;
use App\Models\CartUpdate;
use App\Models\DeviceToken;
use App\Models\Follow;
use App\Models\Notification;
use App\Models\PushDelivery;
use App\Models\User;
use App\Models\UserLocation;
use App\Models\UserSetting;
use App\Services\NotificationDispatcher;
use App\Services\PushGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FoodCartApiTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->create(['role' => 'owner']);
    }

    private function cart(?User $owner = null, array $extra = []): Cart
    {
        return Cart::create($extra + ['owner_id' => ($owner ?? $this->owner())->id, 'name' => 'Taco cart', 'moderation_status' => 'approved', 'status' => 'open']);
    }

    public function test_registration_cannot_grant_admin_and_tokens_can_be_revoked(): void
    {
        $r = $this->postJson('/api/v1/auth/register', ['name' => 'Sam', 'email' => 'sam@example.com', 'password' => 'a-secure-password', 'password_confirmation' => 'a-secure-password', 'role' => 'admin'])->assertCreated();
        $this->assertDatabaseHas('users', ['email' => 'sam@example.com', 'role' => 'customer']);
        $token = $r->json('token');
        $this->withToken($token)->getJson('/api/v1/me')->assertOk();
        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_cart_access_and_moderation_are_enforced(): void
    {
        $owner = $this->owner();
        $cart = $this->cart($owner);
        Sanctum::actingAs($this->owner());
        $this->patchJson('/api/v1/owner/carts/'.$cart->id, ['name' => 'Hijacked'])->assertForbidden();
        Sanctum::actingAs($owner);
        $this->patchJson('/api/v1/owner/carts/'.$cart->id, ['name' => 'Updated', 'moderation_status' => 'rejected'])->assertOk();
        $this->assertSame('approved', $cart->fresh()->moderation_status);
        $cart->update(['moderation_status' => 'pending']);
        $this->getJson('/api/v1/carts/'.$cart->id)->assertNotFound();
        $this->postJson('/api/v1/owner/carts/'.$cart->id.'/updates', ['title' => 'Hello', 'body' => 'World'])->assertForbidden();
    }

    public function test_follows_are_idempotent_and_private(): void
    {
        $cart = $this->cart();
        Sanctum::actingAs(User::factory()->create());
        $this->putJson('/api/v1/carts/'.$cart->id.'/follow')->assertSuccessful();
        $this->putJson('/api/v1/carts/'.$cart->id.'/follow')->assertSuccessful();
        $this->assertDatabaseCount('follows', 1);
        $this->getJson('/api/v1/me/following')->assertJsonCount(1, 'data');
        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/v1/me/following')->assertJsonCount(0, 'data');
    }

    public function test_nearby_excludes_distant_stale_and_blocked_carts(): void
    {
        $cart = $this->cart();
        CartLocation::create(['cart_id' => $cart->id, 'latitude' => 35.68, 'longitude' => 139.76]);
        $far = $this->cart();
        CartLocation::create(['cart_id' => $far->id, 'latitude' => 34, 'longitude' => 135]);
        $url = '/api/v1/carts?latitude=35.68&longitude=139.76&radius_meters=5000';
        $this->getJson($url)->assertOk()->assertJsonCount(1, 'data');
        CartLocation::where('cart_id', $cart->id)->update(['updated_at' => now()->subHour()]);
        $this->getJson($url)->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/carts?latitude=91&longitude=1')->assertUnprocessable();
        $cart->owner->forceFill(['is_active' => false])->save();
        $this->getJson('/api/v1/carts/'.$cart->id)->assertNotFound();
    }

    public function test_photo_upload_validates_content_and_ownership(): void
    {
        Storage::fake('public');
        $owner = $this->owner();
        $cart = $this->cart($owner);
        Sanctum::actingAs($owner);
        $this->postJson('/api/v1/owner/carts/'.$cart->id.'/photos', ['photo' => UploadedFile::fake()->create('bad.svg', 1, 'image/svg+xml')])->assertUnprocessable();
        $file = UploadedFile::fake()->createWithContent('photo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jV1sAAAAASUVORK5CYII='));
        $r = $this->postJson('/api/v1/owner/carts/'.$cart->id.'/photos', ['photo' => $file])->assertCreated();
        Storage::disk('public')->assertExists($r->json('path'));
    }

    public function test_dispatch_deduplicates_updates_and_nearby_and_retries_push(): void
    {
        $user = User::factory()->create();
        $cart = $this->cart();
        Follow::create(['user_id' => $user->id, 'cart_id' => $cart->id]);
        UserLocation::create(['user_id' => $user->id, 'latitude' => 35, 'longitude' => 139]);
        CartLocation::create(['cart_id' => $cart->id, 'latitude' => 35, 'longitude' => 139]);
        CartUpdate::create(['cart_id' => $cart->id, 'title' => 'Lunch', 'body' => 'Open now']);
        DeviceToken::create(['user_id' => $user->id, 'token' => 'test', 'token_hash' => hash('sha256', 'test'), 'platform' => 'android']);
        $this->artisan('notifications:dispatch')->assertSuccessful();
        $this->artisan('notifications:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('notifications', 2);
        $this->assertDatabaseCount('push_deliveries', 2);
        config(['fudo.push_driver' => 'fcm']);
        $gateway = $this->mock(PushGateway::class);
        $gateway->shouldReceive('send')->twice()->andThrow(new \RuntimeException('temporary'));
        app(NotificationDispatcher::class)->deliver($gateway);
        $this->assertDatabaseHas('push_deliveries', ['status' => 'pending', 'attempts' => 1]);
        $this->travel(3)->minutes();
        $gateway = $this->mock(PushGateway::class);
        $gateway->shouldReceive('send')->twice()->andReturnNull();
        app(NotificationDispatcher::class)->deliver($gateway);
        $this->assertSame(2, PushDelivery::where('status', 'sent')->count());
    }

    public function test_settings_and_notification_ownership_are_enforced(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $cart = $this->cart();
        UserSetting::create(['user_id' => $user->id, 'updates_enabled' => false, 'nearby_enabled' => false]);
        Follow::create(['user_id' => $user->id, 'cart_id' => $cart->id]);
        CartUpdate::create(['cart_id' => $cart->id, 'title' => 'Hi', 'body' => 'Hello']);
        $this->artisan('notifications:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('notifications', 0);
        $n = Notification::create(['user_id' => $other->id, 'cart_id' => $cart->id, 'title' => 'Hi', 'body' => 'Hi', 'type' => 'update', 'dedupe_key' => 'private']);
        Sanctum::actingAs($user);
        $this->patchJson('/api/v1/me/notifications/'.$n->id.'/read')->assertNotFound();
        $this->patchJson('/api/v1/me/settings', ['radius_meters' => 0])->assertUnprocessable();
    }

    public function test_admin_pages_and_self_protection(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $customer = User::factory()->create();
        $this->actingAs($customer)->get('/admin')->assertForbidden();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Good food. Great overview.');
        foreach (array_keys(AdminController::RESOURCES) as $resource) {
            $this->get('/admin/'.$resource)->assertOk();
        }
        $this->get('/admin/carts/create')->assertOk();
        $this->get('/admin/configuration')->assertOk();
        $this->put('/admin/users/'.$admin->id, ['name' => $admin->name, 'email' => $admin->email, 'role' => 'customer', 'is_active' => 1])->assertUnprocessable();
        $this->post('/admin/configuration', ['push_enabled' => 0, 'location_max_age_minutes' => 45])->assertRedirect();
        $this->assertDatabaseHas('settings', ['key' => 'location_max_age_minutes', 'value' => '45']);
    }

    public function test_schedules_and_reports(): void
    {
        $owner = $this->owner();
        $cart = $this->cart($owner);
        Sanctum::actingAs($owner);
        $this->putJson('/api/v1/owner/carts/'.$cart->id.'/schedules', ['day_of_week' => 7, 'opens_at' => '22:00', 'closes_at' => '01:00', 'timezone' => 'Asia/Tokyo', 'specific_date' => '2026-10-04'])->assertSuccessful();
        $this->postJson('/api/v1/reports', ['target_type' => 'cart', 'target_id' => $cart->id, 'reason' => 'wrongLocation', 'note' => 'Moved'])->assertCreated();
        $this->assertDatabaseHas('reports', ['status' => 'open', 'reporter_id' => $owner->id]);
    }

    public function test_admin_forms_render_and_create_owner_and_cart(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        foreach (['users', 'carts', 'updates', 'schedules', 'follows', 'cart-locations', 'user-settings'] as $resource) {
            $this->get('/admin/'.$resource.'/create')->assertOk();
        }
        $this->post('/admin/users', ['name' => 'Owner', 'email' => 'owner@example.test', 'password' => 'long-owner-password', 'role' => 'owner', 'is_active' => 1])->assertRedirect('/admin/users');
        $owner = User::where('email', 'owner@example.test')->firstOrFail();
        $this->post('/admin/carts', ['owner_id' => $owner->id, 'name' => 'Admin cart', 'status' => 'open', 'moderation_status' => 'approved', 'is_featured' => 0])->assertRedirect('/admin/carts');
        $cart = Cart::where('name', 'Admin cart')->firstOrFail();
        $this->get('/admin/carts/'.$cart->id.'/edit')->assertOk();
        $this->get('/admin/users/'.$owner->id.'/edit')->assertOk();
    }

    public function test_identical_locations_refresh_and_mobile_admin_is_protected(): void
    {
        $owner = $this->owner();
        $cart = $this->cart($owner);
        Sanctum::actingAs($owner);
        $this->putJson('/api/v1/me/location', ['latitude' => 35, 'longitude' => 139])->assertSuccessful();
        $this->putJson('/api/v1/owner/carts/'.$cart->id.'/location', ['latitude' => 35, 'longitude' => 139])->assertSuccessful();
        $before = now();
        $this->travel(5)->minutes();
        $this->putJson('/api/v1/me/location', ['latitude' => 35, 'longitude' => 139])->assertSuccessful();
        $this->putJson('/api/v1/owner/carts/'.$cart->id.'/location', ['latitude' => 35, 'longitude' => 139])->assertSuccessful();
        $this->assertTrue($owner->location->updated_at->greaterThan($before));
        $this->assertTrue($cart->location->updated_at->greaterThan($before));
        $this->getJson('/api/v1/admin/users')->assertForbidden();
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
        $this->patchJson('/api/v1/admin/users/'.$owner->id, ['is_active' => false])->assertOk();
        $this->getJson('/api/v1/carts/'.$cart->id)->assertNotFound();
    }
}
