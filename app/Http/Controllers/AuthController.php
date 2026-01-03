<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

use App\Enums\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;

use App\Services\MenuService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected MenuService $menuService;

    public function __construct()
    {
        $this->menuService = app(MenuService::class);
    }

    /**
     * Show the login page.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('auth/login');
    }

    /**
     * Handle user login.
     * @param  \App\Http\Requests\AuthRequest  $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(AuthRequest $request)
    {
        $credentials = $request->only('username', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()->withErrors([
                'username' => 'Thông tin đăng nhập không chính xác',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->hasRole(Role::ADMIN->value)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'username' => 'Access denied for admin role',
            ]);
        } elseif ($user->hasRole(Role::STAFF->value)) {
            return redirect()->intended('staff/');
        }

        return redirect()->intended('/');
    }

    /**
     * Handle user logout.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Inertia::location(route('login'));
    }
}
