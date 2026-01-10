<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class VoucherController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Get vouchers assigned to the user directly
        $personalVouchers = $user->vouchers()
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->get();

        $globalVouchers = Voucher::where('model_id', 0)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->get();

        $vouchers = $personalVouchers->merge($globalVouchers);

        return Inertia::render('voucher/index', [
            'vouchers' => $vouchers
        ]);
    }
}
