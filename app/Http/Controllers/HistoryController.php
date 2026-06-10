<?php

namespace App\Http\Controllers;

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

        $bills = Bill::whereCustomerId($user->id)
            ->with(['branch', 'table'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('history/index', [
            'bills' => $bills
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $bill = Bill::whereCustomerId($user->id)
            ->where('id', $id)
            ->with(['billDetails.dish.food', 'branch', 'table'])
            ->firstOrFail();

        return Inertia::render('history/show', [
            'bill' => $bill
        ]);
    }
}
