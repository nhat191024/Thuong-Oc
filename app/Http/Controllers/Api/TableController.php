<?php

namespace App\Http\Controllers\Api;

use App\Enums\PayStatus;

use App\Models\Table;

use App\Http\Controllers\Controller;
use App\Http\Requests\TableIndexRequest;
use App\Http\Resources\TableResource;

class TableController extends Controller
{
    /**
     * Get a list of tables for a specific branch.
     *
     * @param  \App\Http\Requests\TableIndexRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(TableIndexRequest $request)
    {
        $branchId = $request->input('branch_id');

        $tables = Table::where('branch_id', $branchId)->get();
        $data = TableResource::collection($tables);

        return response()->json($data);
    }
}
