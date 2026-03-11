<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Seller;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Compte désactivé.'], 403);
        }

        $token = Str::random(60);
        $user->update(['api_token' => $token]);

        $profile = match ($user->role) {
            'supplier' => $user->supplier,
            'seller'   => $user->seller?->load('supplier.user'),
            'client'   => $user->client?->load('seller.user'),
            default    => null,
        };

        return response()->json([
            'token'   => $token,
            'user'    => $user->only('id', 'name', 'email', 'phone', 'role'),
            'profile' => $profile,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user('api')?->update(['api_token' => null]);

        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user('api');

        $profile = match ($user->role) {
            'supplier' => $user->supplier,
            'seller'   => $user->seller?->load('supplier.user'),
            'client'   => $user->client?->load('seller.user'),
            default    => null,
        };

        return response()->json([
            'user'    => $user->only('id', 'name', 'email', 'phone', 'role', 'is_active'),
            'profile' => $profile,
        ]);
    }
}
