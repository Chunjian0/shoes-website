<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $employees = Employee::where('name', 'like', "%{$query}%")
            ->orWhere('employee_id', 'like', "%{$query}%")
            ->select('id', 'name', 'employee_id')
            ->limit(10)
            ->get();

        return response()->json($employees);
    }
} 