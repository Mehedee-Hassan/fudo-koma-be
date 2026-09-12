<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $r)
    {
        $data = $r->validate(['name' => 'required|string|max:100', 'email' => 'required|email|max:255|unique:users', 'password' => 'required|string|min:12|confirmed']);
        $user = DB::transaction(function () use ($data) {
            $user = User::create($data);
            UserSetting::create(['user_id' => $user->id]);

            return $user;
        });

        return response()->json(['user' => $user, 'token' => $user->createToken('mobile', ['*'], now()->addDays(30))->plainTextToken], 201);
    }

    public function login(Request $r)
    {
        $data = $r->validate(['email' => 'required|email', 'password' => 'required|string']);
        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password) || ! $user->is_active) {
            throw ValidationException::withMessages(['email' => 'The credentials are invalid.']);
        }

        return ['user' => $user, 'token' => $user->createToken('mobile', ['*'], now()->addDays(30))->plainTextToken];
    }

    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()?->delete();

        return response()->noContent();
    }
}
