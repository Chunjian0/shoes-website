<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockService;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Collection;

class StockController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
    ) {}

    /**
     * Show inventory warning product list
     */
    public function lowStock(): View
    {
        /** @var Collection<int, Product> $products */
        $products = Product::with(['category'])
            ->where('status', true)
            ->where('min_stock', '>', 0)
            ->get()
            ->filter(function (Product $product) {
                $stock = $this->stockService->getProductStock($product);
                return $stock <= $product->min_stock;
            });

        return view('stock.low-stock', compact('products'));
    }

    /**
     * Show out-of-stock product list
     */
    public function outOfStock(): View
    {
        /** @var Collection<int, Product> $products */
        $products = Product::with(['category'])
            ->where('status', true)
            ->get()
            ->filter(function (Product $product) {
                $stock = $this->stockService->getProductStock($product);
                return $stock <= 0;
            });

        return view('stock.out-of-stock', compact('products'));
    }
} 