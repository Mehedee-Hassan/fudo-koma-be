<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartLocation;
use App\Models\CartSchedule;
use App\Models\CartUpdate;
use App\Models\Follow;
use App\Models\Notification;
use App\Models\Photo;
use App\Models\PushDelivery;
use App\Models\Report;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserLocation;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public const RESOURCES = [
        'users' => User::class, 'carts' => Cart::class, 'updates' => CartUpdate::class, 'schedules' => CartSchedule::class,
        'photos' => Photo::class, 'follows' => Follow::class, 'cart-locations' => CartLocation::class,
        'user-locations' => UserLocation::class, 'user-settings' => UserSetting::class,
        'notifications' => Notification::class, 'deliveries' => PushDelivery::class, 'reports' => Report::class,
    ];

    public function login(Request $r)
    {
        $data = $r->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (! Auth::attempt($data + ['role' => 'admin', 'is_active' => true])) {
            throw ValidationException::withMessages(['email' => 'The administrator credentials are invalid.']);
        }
        $r->session()->regenerate();

        return redirect()->intended('/admin');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect('/login');
    }

    public function dashboard()
    {
        return view('admin.dashboard', ['counts' => ['Carts' => Cart::count(), 'Customers' => User::where('role', 'customer')->count(), 'Active follows' => Follow::where('is_following', true)->count(), 'Pending review' => Cart::where('moderation_status', 'pending')->count()], 'carts' => Cart::with('owner')->latest()->limit(6)->get(), 'deliveries' => PushDelivery::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'), 'updates' => CartUpdate::with('cart')->latest()->limit(5)->get()]);
    }

    public function index(Request $r, string $resource)
    {
        $class = $this->model($resource);
        $q = $class::query();
        $search = $r->validate(['search' => 'nullable|string|max:100'])['search'] ?? '';
        if ($search && in_array($resource, ['users', 'carts'])) {
            $q->where('name', 'like', '%'.$search.'%');
        }

        return view('admin.index', ['resource' => $resource, 'rows' => $q->latest('id')->paginate(20)->withQueryString()]);
    }

    public function edit(string $resource, int $id)
    {
        $class = $this->model($resource);

        return $this->form($resource, $class::findOrFail($id));
    }

    public function create(string $resource)
    {
        abort_unless(in_array($resource, ['users', 'carts', 'updates', 'schedules', 'follows', 'cart-locations', 'user-settings']), 404);
        $class = $this->model($resource);

        return $this->form($resource, new $class);
    }

    private function form(string $resource, $record)
    {
        return view('admin.edit', compact('resource', 'record') + ['fields' => $this->fields($resource), 'choices' => ['owner_id' => User::whereIn('role', ['owner', 'admin'])->where('is_active', true)->orderBy('name')->get(['id', 'name']), 'user_id' => User::orderBy('name')->get(['id', 'name']), 'cart_id' => Cart::orderBy('name')->get(['id', 'name'])]]);
    }

    public function save(Request $r, string $resource, ?int $id = null)
    {
        $class = $this->model($resource);
        $record = $id ? $class::findOrFail($id) : new $class;
        abort_if(! $id && ! in_array($resource, ['users', 'carts', 'updates', 'schedules', 'follows', 'cart-locations', 'user-settings']), 403);
        $rules = $this->fields($resource);
        abort_if($rules === [], 403);
        $data = $r->validate($rules);
        if (in_array($resource, ['follows', 'cart-locations', 'user-settings'])) {
            $query = $class::query()->whereKeyNot($id ?? 0);
            foreach (($resource === 'follows' ? ['user_id', 'cart_id'] : [$resource === 'cart-locations' ? 'cart_id' : 'user_id']) as $key) {
                $query->where($key, $data[$key]);
            }
            if ($query->exists()) {
                throw ValidationException::withMessages(['record' => 'A record for this relationship already exists. Edit the existing record.']);
            }
        }
        if ($resource === 'users') {
            $r->validate(['email' => 'required|email|unique:users,email,'.($id ?? 'NULL'), 'password' => ($id ? 'nullable' : 'required').'|string|min:12']);
            if (empty($data['password'])) {
                unset($data['password']);
            }
            if ($id === $r->user()->id) {
                abort_if(($data['role'] ?? 'admin') !== 'admin' || ! ($data['is_active'] ?? true), 422, 'You cannot disable or demote your own account.');
            }
        }
        if ($resource === 'carts') {
            abort_unless(User::whereKey($data['owner_id'])->whereIn('role', ['owner', 'admin'])->where('is_active', true)->exists(), 422, 'Select an active owner or administrator.');
        }
        if ($resource === 'reports') {
            $data['resolved_by'] = $r->user()->id;
            $data['resolved_at'] = in_array($data['status'], ['resolved', 'dismissed']) ? now() : null;
        }
        DB::transaction(function () use ($record, $data, $resource) {
            $record->forceFill($data)->save();
            if ($resource === 'users' && ! $record->is_active) {
                $record->tokens()->delete();
            }
        });

        return redirect('/admin/'.$resource)->with('success', 'Changes saved.');
    }

    public function delete(Request $r, string $resource, int $id)
    {
        abort_unless(in_array($resource, ['updates', 'schedules', 'photos', 'follows', 'cart-locations', 'user-locations']), 403);
        $class = $this->model($resource);
        $record = $class::findOrFail($id);
        $record->delete();
        if ($resource === 'photos') {
            Storage::disk('public')->delete($record->path);
        }

        return back()->with('success', 'Record deleted.');
    }

    public function upload(Request $r, Cart $cart)
    {
        (new CartController)->photo($r, $cart);

        return back()->with('success', 'Photo uploaded.');
    }

    public function settings()
    {
        return view('admin.settings', ['push' => Setting::valueOf('push_enabled', 1), 'age' => Setting::valueOf('location_max_age_minutes', 30)]);
    }

    public function saveSettings(Request $r)
    {
        foreach ($r->validate(['push_enabled' => 'required|boolean', 'location_max_age_minutes' => 'required|integer|between:5,120']) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        return back()->with('success', 'Configuration saved.');
    }

    public function retry(PushDelivery $delivery)
    {
        abort_unless($delivery->status === 'failed', 422);
        $delivery->update(['status' => 'pending', 'attempts' => 0, 'available_at' => now(), 'last_error' => null]);

        return back()->with('success', 'Delivery queued for retry.');
    }

    private function model(string $resource): string
    {
        abort_unless(isset(self::RESOURCES[$resource]), 404);

        return self::RESOURCES[$resource];
    }

    private function fields(string $resource): array
    {
        return match ($resource) {
            'users' => ['name' => 'required|string|max:100', 'email' => 'required|email|max:255', 'password' => 'nullable|string|min:12', 'role' => 'required|in:customer,owner,admin', 'is_active' => 'required|boolean'],
            'carts' => ['owner_id' => 'required|exists:users,id', 'name' => 'required|string|max:100', 'description' => 'nullable|string|max:3000', 'cuisine' => 'nullable|string|max:100', 'status' => 'required|in:open,closed', 'moderation_status' => 'required|in:pending,approved,rejected', 'is_featured' => 'required|boolean'],
            'updates' => ['cart_id' => 'required|exists:carts,id', 'title' => 'required|string|max:150', 'body' => 'required|string|max:3000', 'type' => 'required|in:announcement,opened,closed,moved,scheduleChanged,photoAdded'],
            'schedules' => ['cart_id' => 'required|exists:carts,id', 'day_of_week' => 'required|integer|between:1,7', 'opens_at' => 'required|date_format:H:i', 'closes_at' => 'required|date_format:H:i', 'timezone' => 'required|timezone', 'address' => 'nullable|string|max:255', 'specific_date' => 'nullable|date_format:Y-m-d', 'is_active' => 'required|boolean'],
            'follows' => ['user_id' => 'required|exists:users,id', 'cart_id' => 'required|exists:carts,id', 'is_following' => 'required|boolean'],
            'cart-locations' => ['cart_id' => 'required|exists:carts,id', 'latitude' => 'required|numeric|between:-90,90', 'longitude' => 'required|numeric|between:-180,180', 'address' => 'nullable|string|max:255'],
            'user-settings' => ['user_id' => 'required|exists:users,id', 'push_enabled' => 'required|boolean', 'nearby_enabled' => 'required|boolean', 'updates_enabled' => 'required|boolean', 'radius_meters' => 'required|integer|between:100,50000'],
            'reports' => ['status' => 'required|in:open,reviewing,resolved,dismissed', 'resolution_note' => 'nullable|string|max:2000'],
            default => [],
        };
    }
}
