<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

use App\Models\Kitchen;
use App\Models\Branch;

use Illuminate\Support\Facades\Auth;

class KitchenController extends Controller
{
    /**
     * Show kitchen dashboard
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        $kitchens = Kitchen::where('branch_id', $branchId)->get();
        $branchName = Branch::find($branchId)->name;

        return Inertia::render('kitchen/index', [
            'kitchens' => $kitchens,
            'branchName' => $branchName,
        ]);
    }
}
