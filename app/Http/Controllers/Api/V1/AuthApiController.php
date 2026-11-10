<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// Endpoint autentikasi stateless untuk menerbitkan dan mencabut token Sanctum.
class AuthApiController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'type' => 'https://httpstatuses.com/401',
                'title' => 'Unauthorized',
                'status' => 401,
                'detail' => 'Email atau password tidak valid.',
                'instance' => $request->getRequestUri(),
            ], 401, [
                'Content-Type' => 'application/problem+json',
            ]);
        }

        $token = $user->createToken('sipus-api')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        if ($token !== null) {
            $token->delete();
        }

        return response()->json(null, 204);
    }
}