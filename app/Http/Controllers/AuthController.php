<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

use App\Models\Customer;

use App\Enums\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;

use App\Services\MenuService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use  \Illuminate\Support\Facades\Hash;

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
     * Show the registration page.
     *
     * @return \Inertia\Response
     */
    public function register()
    {
        return Inertia::render('auth/register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function storeRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|confirmed|min:4',
        ]);

        $user = Customer::create([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'username' => $request->input('username'),
            'password' => Hash::make($request->input('password')),
        ]);

        $user->assignRole(Role::CUSTOMER);

        Auth::login($user);

        return redirect('/');
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

        switch (true) {
            case $user->hasRole(Role::ADMIN->value):
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'username' => 'Access denied for admin role',
                ]);
            case $user->hasRole(Role::STAFF->value):
                if (str_contains(session('url.intended', ''), '/kitchen')) {
                    session()->forget('url.intended');
                }

                return redirect()->intended('staff/');
            case $user->hasRole(Role::KITCHEN->value):
                if (str_contains(session('url.intended', ''), '/staff')) {
                    session()->forget('url.intended');
                }

                return redirect()->intended('kitchen/');
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
