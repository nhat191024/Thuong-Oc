<?php

namespace App\Http\Controllers;

use App\Enums\PayStatus;
use App\Enums\Role;
use App\Models\Bill;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $bills = Bill::query()
            ->when(
                $user->hasRole(Role::STAFF->value),
                fn ($query) => $query
                    ->withTrashed()
                    ->where('branch_id', $user->branch_id)
                    ->where(function ($query) {
                        $query->where('pay_status', PayStatus::PAID)
                            ->orWhereNotNull('deleted_at');
                    }),
                fn ($query) => $query->whereCustomerId($user->id),
            )
            ->with(['branch', 'table'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('history/index', [
            'bills' => $bills,
            'isStaff' => $user->hasRole(Role::STAFF->value),
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $bill = Bill::query()
            ->when(
                $user->hasRole(Role::STAFF->value),
                fn ($query) => $query
                    ->withTrashed()
                    ->where('branch_id', $user->branch_id)
                    ->where(function ($query) {
                        $query->where('pay_status', PayStatus::PAID)
                            ->orWhereNotNull('deleted_at');
                    }),
                fn ($query) => $query->whereCustomerId($user->id),
            )
            ->where('id', $id)
            ->with(['billDetails.dish.food', 'branch', 'table'])
            ->firstOrFail();

        return Inertia::render('history/show', [
            'bill' => $bill,
            'isStaff' => $user->hasRole(Role::STAFF->value),
        ]);
    }
}
