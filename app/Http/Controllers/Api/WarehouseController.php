<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WarehouseController extends Controller
{
    /**
     * Get the warehouse list
     */
    public function index(Request $request): JsonResponse
    {
        try {
            Log::info('Start getting warehouse lists', [
                'user_id' => auth()->id(),
                'search' => $request->get('search')
            ]);

            $warehouses = Warehouse::query()
                ->when($request->get('search'), function ($query, $search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
                })
                ->where('status', true)
                ->select(['id', 'name', 'code'])
                ->orderBy('name')
                ->get();

            Log::info('Successful obtaining warehouse list', [
                'count' => $warehouses->count()
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $warehouses
            ]);
        } catch (\Exception $e) {
            Log::error('Obtaining the warehouse list failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Obtaining the warehouse list failed'
            ], 500);
        }
    }
    
    /**
     * Get warehouse address
     */
    public function getAddress(Warehouse $warehouse): JsonResponse
    {
        try {
            Log::info('Getting warehouse address', [
                'user_id' => auth()->id(),
                'warehouse_id' => $warehouse->id
            ]);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'address' => $warehouse->address
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get warehouse address', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get warehouse address'
            ], 500);
        }
    }
} 