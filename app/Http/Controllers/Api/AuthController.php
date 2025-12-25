<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;

use App\Services\MenuService;

use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected MenuService $menuService;

    public function __construct()
    {
        $this->menuService = app(MenuService::class);
    }

    /**
     * Handle user login and generate API token.
     * @param  \App\Http\Requests\AuthRequest  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(AuthRequest $request)
    {
        $credentials = $request->only('username', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $menus = null;
        if ($user->hasRole(Role::ADMIN->value)) {
            return response()->json(['message' => 'Access denied for admin role'], 403);
        } else if ($user->hasRole(Role::STAFF->value)) {
            $menus = $this->menuService->getMenus();
        }

        $oldTokens = $user->tokens();
        if ($oldTokens->count() > 0) {
            $oldTokens->delete();
        }
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $user,
            'role' => $user->roles->pluck('name'),
            'menus' => $menus,
        ]);
    }

    /**
     * Handle user logout and revoke API token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $user = Auth::user();
        $user->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
