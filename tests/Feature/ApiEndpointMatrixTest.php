<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartLocation;
use App\Models\CartSchedule;
use App\Models\CartUpdate;
use App\Models\DeviceToken;
use App\Models\Follow;
use App\Models\Notification;
use App\Models\Photo;
use App\Models\Report;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ApiEndpointMatrixTest extends TestCase
{
    use RefreshDatabase;

    private const IDS = ['cart' => 401, 'device' => 601, 'notification' => 701, 'schedule' => 501, 'photo' => 901, 'user' => 102, 'report' => 801];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        foreach ([101 => ['customer', 'customer@example.test'], 102 => ['customer', 'other@example.test'], 201 => ['owner', 'owner@example.test'], 202 => ['owner', 'other-owner@example.test'], 301 => ['admin', 'admin@example.test']] as $id => [$role,$email]) {
            User::factory()->create(['id' => $id, 'role' => $role, 'email' => $email, 'name' => $id === 101 ? 'Customer Test' : 'Test '.$role, 'password' => 'Test-password-123!']);
        }
        UserSetting::create(['user_id' => 101]);
        foreach ([401 => 201, 402 => 202] as $id => $owner) {
            $cart = new Cart(['owner_id' => $owner, 'name' => 'Tokyo Taco Club', 'status' => 'open', 'moderation_status' => 'approved']);
            $cart->id = $id;
            $cart->save();
        }
        CartLocation::create(['cart_id' => 401, 'latitude' => 35.6812, 'longitude' => 139.7671]);
        $schedule = new CartSchedule(['cart_id' => 401, 'day_of_week' => 1, 'opens_at' => '11:00', 'closes_at' => '22:00', 'timezone' => 'Asia/Tokyo']);
        $schedule->id = 501;
        $schedule->save();
        $device = new DeviceToken(['user_id' => 101, 'token' => 'existing-device', 'token_hash' => hash('sha256', 'existing-device'), 'platform' => 'android']);
        $device->id = 601;
        $device->save();
        $notification = new Notification(['user_id' => 101, 'cart_id' => 401, 'dedupe_key' => 'fixture', 'type' => 'update', 'title' => 'Lunch', 'body' => 'Lunch ready']);
        $notification->id = 701;
        $notification->save();
        $report = new Report(['reporter_id' => 101, 'target_type' => 'cart', 'target_id' => 401, 'reason' => 'wrongLocation']);
        $report->id = 801;
        $report->save();
        Storage::disk('public')->put('carts/401/fixture.png', file_get_contents(base_path('doc/test/fixtures/cart.png')));
        $photo = new Photo(['cart_id' => 401, 'path' => 'carts/401/fixture.png']);
        $photo->id = 901;
        $photo->save();
        Follow::create(['user_id' => 101, 'cart_id' => 401, 'is_following' => true]);
        CartUpdate::create(['cart_id' => 401, 'title' => 'Lunch', 'body' => 'Lunch is ready', 'type' => 'announcement']);
    }

    private static function catalog(): array
    {
        return json_decode(file_get_contents(__DIR__.'/../Fixtures/api-cases.json'), true, flags: JSON_THROW_ON_ERROR);
    }

    public static function endpoints(): iterable
    {
        foreach (self::catalog() as $endpoint) {
            yield $endpoint['slug'] => [$endpoint];
        }
    }

    public static function invalidInputs(): iterable
    {
        foreach (self::catalog() as $endpoint) {
            foreach ($endpoint['cases'] as $case) {
                yield $endpoint['slug'].' / '.$case['name'] => [$endpoint, $case];
            }
        }
    }

    public static function acceptedInputs(): iterable
    {
        foreach (self::catalog() as $endpoint) {
            foreach ($endpoint['accepted_cases'] as $case) {
                yield $endpoint['slug'].' / '.$case['name'] => [$endpoint, $case];
            }
        }
    }

    public static function protectedEndpoints(): iterable
    {
        foreach (self::catalog() as $endpoint) {
            if ($endpoint['role'] !== 'public') {
                yield $endpoint['slug'] => [$endpoint];
            }
        }
    }

    public static function privilegedEndpoints(): iterable
    {
        foreach (self::catalog() as $endpoint) {
            if (in_array($endpoint['role'], ['owner', 'admin'])) {
                yield $endpoint['slug'] => [$endpoint];
            }
        }
    }

    public static function boundEndpoints(): iterable
    {
        foreach (self::catalog() as $endpoint) {
            preg_match_all('/\{([^}]+)\}/', $endpoint['path'], $matches);
            foreach ($matches[1] as $parameter) {
                yield $endpoint['slug'].' / '.$parameter => [$endpoint, $parameter];
            }
        }
    }

    private function requestEndpoint(array $endpoint, ?array $case = null, ?string $role = null, array $pathIds = [])
    {
        $role ??= $endpoint['role'];
        if ($role !== 'public') {
            $user = User::findOrFail(['customer' => 101, 'owner' => 201, 'admin' => 301][$role]);
            $this->withToken($user->createToken('matrix', ['*'], now()->addDay())->plainTextToken);
        }
        $path = '/api/v1/'.$endpoint['path'];
        foreach (array_replace(self::IDS, $pathIds) as $name => $id) {
            $path = str_replace('{'.$name.'}', (string) $id, $path);
        }
        $payload = $endpoint['method'] === 'GET' ? $endpoint['query_payload'] : $endpoint['payload'];
        if ($endpoint['slug'] === 'upload-photo') {
            $payload = ['photo' => UploadedFile::fake()->createWithContent('cart.png', file_get_contents(base_path('doc/test/fixtures/cart.png')))];
        }
        if ($case) {
            if ($case['omit']) {
                unset($payload[$case['field']]);
            } else {
                $payload[$case['field']] = $case['value'];
            }
        }
        if ($case && ($case['accepted'] ?? false) && $case['field'] === 'password' && $endpoint['slug'] === 'register') {
            $payload['password_confirmation'] = $payload['password'];
        }
        if ($case && ($case['accepted'] ?? false) && $case['field'] === 'target_type' && $case['value'] === 'user') {
            $payload['target_id'] = 102;
        }
        if ($endpoint['method'] === 'GET') {
            return $this->getJson($path.($payload ? '?'.http_build_query(array_map(fn ($v) => $v === null ? '' : $v, $payload)) : ''));
        }

        return $this->json($endpoint['method'], $path, $payload);
    }

    #[DataProvider('endpoints')]
    public function test_full_parameter_example(array $endpoint): void
    {
        $response = $this->requestEndpoint($endpoint)->assertStatus($endpoint['status']);
        if ($endpoint['status'] === 204) {
            $this->assertSame('', $response->getContent());
        } elseif ($endpoint['query'] && array_key_exists('page', $endpoint['query'])) {
            $response->assertJsonStructure(['data', 'current_page', 'per_page', 'total', 'last_page'])->assertJsonPath('per_page', 25);
        } else {
            $this->assertIsArray($response->json());
        }
        switch ($endpoint['slug']) {
            case 'register':
                $response->assertJsonPath('user.role', 'customer')->assertJsonStructure(['token']);
                $this->assertDatabaseHas('users', ['email' => 'new-customer@example.test', 'role' => 'customer']);
                break;
            case 'login': $response->assertJsonPath('user.id', 101)->assertJsonStructure(['token']);
                break;
            case 'logout':$this->assertDatabaseCount('personal_access_tokens', 0);
                break;
            case 'get-profile':$response->assertJsonPath('id', 101)->assertJsonMissingPath('password');
                break;
            case 'update-profile':$this->assertDatabaseHas('users', ['id' => 101, 'name' => 'Customer Updated']);
                break;
            case 'update-settings':$this->assertDatabaseHas('user_settings', ['user_id' => 101, 'radius_meters' => 5000]);
                break;
            case 'set-user-location':$this->assertDatabaseHas('user_locations', ['user_id' => 101, 'latitude' => 35.6812]);
                break;
            case 'register-device':$response->assertJsonMissingPath('token')->assertJsonMissingPath('token_hash');
                $this->assertDatabaseCount('device_tokens', 2);
                break;
            case 'delete-device':$this->assertDatabaseMissing('device_tokens', ['id' => 601]);
                break;
            case 'read-notification':case 'read-all-notifications':$this->assertNotNull(Notification::findOrFail(701)->read_at);
                break;
            case 'submit-report':$this->assertDatabaseHas('reports', ['id' => $response->json('id'), 'reporter_id' => 101, 'status' => 'open']);
                break;
            case 'follow-cart':$this->assertDatabaseCount('follows', 1);
                $this->assertDatabaseHas('follows', ['user_id' => 101, 'cart_id' => 401, 'is_following' => true]);
                break;
            case 'unfollow-cart':$this->assertDatabaseHas('follows', ['user_id' => 101, 'cart_id' => 401, 'is_following' => false]);
                break;
            case 'create-cart':$response->assertJsonPath('owner_id', 201)->assertJsonPath('moderation_status', 'pending');
                break;
            case 'update-cart':$this->assertDatabaseHas('carts', ['id' => 401, 'name' => 'Tokyo Taco Club Updated']);
                break;
            case 'delete-cart':$this->assertDatabaseMissing('carts', ['id' => 401]);
                $this->assertDatabaseMissing('photos', ['id' => 901]);
                Storage::disk('public')->assertMissing('carts/401/fixture.png');
                break;
            case 'set-cart-location':$this->assertDatabaseHas('cart_locations', ['cart_id' => 401, 'address' => 'Tokyo station, Marunouchi exit']);
                break;
            case 'publish-update':$this->assertDatabaseHas('cart_updates', ['cart_id' => 401, 'title' => 'Lunch is ready', 'type' => 'announcement']);
                break;
            case 'upsert-schedule':$this->assertDatabaseHas('cart_schedules', ['cart_id' => 401, 'day_of_week' => 7, 'specific_date' => '2026-10-04']);
                break;
            case 'delete-schedule':$this->assertDatabaseMissing('cart_schedules', ['id' => 501]);
                break;
            case 'upload-photo':Storage::disk('public')->assertExists($response->json('path'));
                break;
            case 'delete-photo':$this->assertDatabaseMissing('photos', ['id' => 901]);
                Storage::disk('public')->assertMissing('carts/401/fixture.png');
                break;
            case 'admin-update-user':$this->assertDatabaseHas('users', ['id' => 102, 'role' => 'owner', 'is_active' => false]);
                break;
            case 'admin-update-cart':$this->assertDatabaseHas('carts', ['id' => 401, 'is_featured' => true]);
                break;
            case 'admin-update-report':$this->assertDatabaseHas('reports', ['id' => 801, 'status' => 'resolved', 'resolved_by' => 301]);
                break;
        }
    }

    #[DataProvider('invalidInputs')]
    public function test_invalid_parameter(array $endpoint, array $case): void
    {
        $response = $this->requestEndpoint($endpoint, $case)->assertStatus($case['status']);
        if ($case['name'] !== 'target_id — nonexistent target') {
            $response->assertJsonStructure(['message', 'errors']);
        }
    }

    #[DataProvider('acceptedInputs')]
    public function test_accepted_parameter_variants(array $endpoint, array $case): void
    {
        $this->requestEndpoint($endpoint, $case)->assertSuccessful();
    }

    #[DataProvider('protectedEndpoints')]
    public function test_authentication_required(array $endpoint): void
    {
        $this->requestEndpoint($endpoint, role: 'public')->assertUnauthorized();
    }

    #[DataProvider('protectedEndpoints')]
    public function test_disabled_account_rejected(array $endpoint): void
    {
        User::findOrFail(['customer' => 101, 'owner' => 201, 'admin' => 301][$endpoint['role']])->forceFill(['is_active' => false])->save();
        $this->requestEndpoint($endpoint)->assertForbidden();
    }

    #[DataProvider('privilegedEndpoints')]
    public function test_customer_cannot_use_privileged_path(array $endpoint): void
    {
        $this->requestEndpoint($endpoint, role: 'customer')->assertForbidden();
    }

    #[DataProvider('boundEndpoints')]
    public function test_unknown_path_resource(array $endpoint, string $parameter): void
    {
        $this->requestEndpoint($endpoint, pathIds: [$parameter => 999999])->assertNotFound();
    }

    public function test_every_api_route_has_a_documented_example(): void
    {
        $actual = [];
        foreach (Route::getRoutes() as $route) {
            if (str_starts_with($route->uri(), 'api/v1/')) {
                foreach ($route->methods() as $method) {
                    if ($method !== 'HEAD') {
                        $actual[] = $method.' /'.$route->uri();
                    }
                }
            }
        }
        $documented = array_map(fn ($e) => $e['method'].' /api/v1/'.$e['path'], self::catalog());
        sort($actual);
        sort($documented);
        $this->assertSame($actual, $documented);
        foreach (self::catalog() as $index => $endpoint) {
            $doc = base_path('doc/test/'.sprintf('%02d', $index + 1).'-'.$endpoint['slug'].'.md');
            $this->assertFileExists($doc);
            $this->assertStringStartsWith('# '.$endpoint['title']."\n", file_get_contents($doc));
        }
    }
}
