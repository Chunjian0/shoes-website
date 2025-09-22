<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ProductRepository extends BaseRepository
{
    protected function getModelClass(): Model
    {
        return new Product();
    }

    public function getAllWithCategory(): LengthAwarePaginator
    {
        $query = $this->model->with('category');

        // Basic Search
        if (request()->filled('search')) {
            $search = request()->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Classification filtering
        if (request()->filled('category_id')) {
            $query->where('category_id', request()->input('category_id'));
        }

        // Status Filter
        if (request()->filled('status')) {
            $query->where('is_active', request()->input('status'));
        }

        // Inventory status filtering
        if (request()->filled('stock_status')) {
            switch (request()->input('stock_status')) {
                case 'sufficient':
                    $query->whereRaw('inventory_count > min_stock');
                    break;
                case 'low':
                    $query->whereRaw('inventory_count <= min_stock AND inventory_count > 0');
                    break;
                case 'out':
                    $query->where('inventory_count', 0);
                    break;
            }
        }

        // Parameter search
        if (request()->filled('parameters')) {
            $parameters = request()->input('parameters');
            Log::info('Search parameters:', ['parameters' => $parameters]);
            foreach ($parameters as $name => $value) {
                if ($value !== null && $value !== '') {
                    Log::info('Processing parameters:', ['name' => $name, 'value' => $value]);
                    $query->whereRaw("JSON_EXTRACT(parameters, ?) = ?", ["$.{$name}", $value]);
                    Log::info('SQL:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);
                }
            }
        }

        // Automatically disable inventory as0Products
        $this->model->where('inventory_count', 0)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Add the finalSQLlog
        Log::info('Final query:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

        return $query->latest()->paginate();
    }

    public function create(array $data): Model
    {
        try {
            // Record the original data
            Log::info('ProductRepository::create - Raw data:', $data);

            // Extract parameter values
            $parameters = $data['parameters'] ?? [];
            unset($data['parameters']);
            Log::info('ProductRepository::create - Processed basic data:', $data);
            Log::info('ProductRepository::create - Extracted parameter values:', $parameters);

            // Create a product
            $product = parent::create($data);
            Log::info('ProductRepository::create - The product basic information was created successfully:', ['id' => $product->id]);

            // Save parameter values
            if (!empty($parameters)) {
                $product->parameters = $parameters;
                $saved = $product->save();
                Log::info('ProductRepository::create - Product parameter saving result:', [
                    'success' => $saved,
                    'parameters' => $parameters
                ]);
            }

            return $product;
        } catch (\Exception $e) {
            Log::error('ProductRepository::create - Product creation failed:' . $e->getMessage());
            Log::error('ProductRepository::create - Error stack:' . $e->getTraceAsString());
            throw $e;
        }
    }

    public function update(Model $model, array $data): bool
    {
        // Extract parameter values
        $parameters = $data['parameters'] ?? [];
        unset($data['parameters']);

        // Update basic product information
        $updated = parent::update($model, $data);

        // Update parameter values
        if ($updated && !empty($parameters)) {
            $model->parameters = $parameters;
            $model->save();
        }

        return $updated;
    }
} 