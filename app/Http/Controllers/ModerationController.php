<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModerationController extends Controller
{
    public function users(Request $r)
    {
        $data = $r->validate(['search' => 'nullable|string|max:100']);

        return User::when($data['search'] ?? null, fn ($q, $s) => $q->where('name', 'like', '%'.$s.'%'))->latest()->paginate(25);
    }

    public function user(Request $r, User $user)
    {
        $data = $r->validate(['is_active' => 'sometimes|boolean', 'role' => 'sometimes|in:customer,owner,admin']);
        abort_if($user->id === $r->user()->id && (($data['role'] ?? 'admin') !== 'admin' || ! ($data['is_active'] ?? true)), 422, 'You cannot disable or demote your own account.');
        DB::transaction(function () use ($user, $data) {
            $user->forceFill($data)->save();
            if (! $user->is_active) {
                $user->tokens()->delete();
            }
        });

        return $user;
    }

    public function carts()
    {
        return Cart::with(['location', 'photos', 'schedules'])->latest()->paginate(25);
    }

    public function cart(Request $r, Cart $cart)
    {
        $cart->update($r->validate(['moderation_status' => 'sometimes|in:pending,approved,rejected', 'is_featured' => 'sometimes|boolean']));

        return $cart;
    }

    public function reports(Request $r)
    {
        $data = $r->validate(['status' => 'nullable|in:open,reviewing,resolved,dismissed']);

        return Report::when($data['status'] ?? null, fn ($q, $s) => $q->where('status', $s))->latest()->paginate(25);
    }

    public function report(Request $r, Report $report)
    {
        $data = $r->validate(['status' => 'required|in:open,reviewing,resolved,dismissed', 'resolution_note' => 'nullable|string|max:2000']);
        $report->update($data + ['resolved_by' => $r->user()->id, 'resolved_at' => in_array($data['status'], ['resolved', 'dismissed']) ? now() : null]);

        return $report;
    }
}
