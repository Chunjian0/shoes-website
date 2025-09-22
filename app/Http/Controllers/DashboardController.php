<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Display dashboard
     */
    public function index(Request $request): View
    {
        // Get the current storeID, if not set, return0
        $storeId = session('store_id', 0);

        // Initialize variables
        $totalCustomers = 0;
        $totalProducts = 0;
        $birthdays = collect();
        $birthdayCount = 0;

        // Only as a storeIDQuery data only when it exists
        if ($storeId > 0) {
            // Get the total number of customers
            $totalCustomers = Customer::where('store_id', $storeId)->count();

            // Get the quantity of goods in stock
            $totalProducts = $this->stockService->getStoreProductCount($storeId);

            // Get customers for this week's birthday
            $birthdays = Customer::where('store_id', $storeId)
                ->whereRaw('WEEK(birthday) = WEEK(CURDATE())')
                ->get();

            $birthdayCount = $birthdays->count();
        }

        return view('dashboard', compact(
            'totalCustomers',
            'totalProducts',
            'birthdays',
            'birthdayCount'
        ));
    }

    /**
     * 管理员仪表板视图
     *
     * @return \Illuminate\View\View
     */
    public function adminDashboard()
    {
        // 重定向到分析仪表盘
        return redirect()->route('admin.analytics.dashboard');
    }
} 