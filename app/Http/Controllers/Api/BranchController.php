<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function index()
    {
        $branches = Branch::all();
        return BranchResource::collection($branches);
    }

    public function show(string $id)
    {
        $branch = Branch::findOrFail($id);
        return new BranchResource($branch);
    }
}
