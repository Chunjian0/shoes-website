<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ProductTemplateController extends Controller
{
    /**
     * 获取产品模板列表
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 12); // Default per page
            // Validate per_page to prevent excessively large values
            $perPage = min(max((int)$perPage, 1), 100); // Limit between 1 and 100
            
            $search = $request->input('search');
            $categoryId = $request->input('category_id'); // Assuming frontend sends category_id
            // If frontend sends category name/slug, adjust accordingly:
            // $categorySlug = $request->input('category'); 
            
            $isActive = $request->has('is_active') ? (bool)$request->input('is_active') : true; // Default to active only
            $minPrice = $request->input('min_price');
            $maxPrice = $request->input('max_price');
            $sort = $request->input('sort', 'newest'); // Default sort order
            
            $query = ProductTemplate::with([
                'category:id,name', // Select only needed category fields
                'media',
                'linkedProducts' => function($query) {
                    // Load media for linked products without selecting specific product fields here
                    $query->with('media'); 
                }
            ]);
            
            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      // ->orWhere('brand', 'like', "%{$search}%") // Uncomment if brand exists
                      // ->orWhere('model', 'like', "%{$search}%") // Uncomment if model exists
                      ->orWhere('description', 'like', "%{$search}%")
                      // Optionally search in category name
                      ->orWhereHas('category', function ($catQuery) use ($search) {
                          $catQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }
            
            // Apply category filter (assuming category_id is sent)
            if ($categoryId) {
                 $query->where('category_id', $categoryId);
            } 
            // Example if filtering by category slug:
            // if ($categorySlug) {
            //     $query->whereHas('category', function ($catQuery) use ($categorySlug) {
            //         $catQuery->where('slug', $categorySlug);
            //     });
            // }
            
            // Apply active filter
            if ($isActive !== null) {
                $query->where('is_active', $isActive);
            }

            // Apply price range filter (based on linked products' lowest price)
            if (($minPrice !== null && is_numeric($minPrice)) || ($maxPrice !== null && is_numeric($maxPrice))) {
                $query->whereHas('linkedProducts', function ($productQuery) use ($minPrice, $maxPrice) {
                    if ($minPrice !== null && is_numeric($minPrice)) {
                        $productQuery->where('selling_price', '>=', (float)$minPrice);
                    }
                    if ($maxPrice !== null && is_numeric($maxPrice)) {
                        $productQuery->where('selling_price', '<=', (float)$maxPrice);
                    }
                });
                // This approach filters templates *if any* of their linked products match the price range.
                // If you need to filter based on the *minimum* price across *all* linked products, 
                // the subquery approach might be needed, but it's more complex.
            }
            
            // Apply sorting
            switch ($sort) {
                case 'price_asc':
                    // Sort by the minimum price of linked products. Requires a more complex query or approximation.
                    // Simple approximation: order by template creation time for now.
                    $query->orderBy('created_at', 'asc'); // Placeholder
                    Log::warning('Sorting by price_asc not fully implemented, using created_at asc.');
                    break;
                case 'price_desc':
                    // Sort by the minimum price of linked products. Requires a more complex query or approximation.
                    // Simple approximation: order by template creation time for now.
                    $query->orderBy('created_at', 'desc'); // Placeholder
                     Log::warning('Sorting by price_desc not fully implemented, using created_at desc.');
                    break;
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
            
            // Execute query with pagination
            $paginatedTemplates = $query->paginate($perPage);
            
            // Format the paginated templates
            $templatesFormatted = $this->formatTemplates($paginatedTemplates->items());
            
            // Return standard Laravel pagination response
            return response()->json([
                'success' => true,
                // Directly return the paginated data structure
                'data' => $templatesFormatted,
                'links' => [ // Provide links for pagination
                    'first' => $paginatedTemplates->url(1),
                    'last' => $paginatedTemplates->url($paginatedTemplates->lastPage()),
                    'prev' => $paginatedTemplates->previousPageUrl(),
                    'next' => $paginatedTemplates->nextPageUrl(),
                ],
                'meta' => [ // Provide meta data for pagination
                    'current_page' => $paginatedTemplates->currentPage(),
                    'from' => $paginatedTemplates->firstItem(),
                    'last_page' => $paginatedTemplates->lastPage(),
                    'path' => $paginatedTemplates->path(),
                    'per_page' => $paginatedTemplates->perPage(),
                    'to' => $paginatedTemplates->lastItem(),
                    'total' => $paginatedTemplates->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('API - Failed to retrieve product templates', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product templates: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取单个产品模板详情
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $template = ProductTemplate::with([
                'category', 
                'media', 
                'linkedProducts' => function($query) {
                    $query->with('media');
                }
            ])->findOrFail($id);
            
            $formattedTemplate = $this->formatTemplate($template);
            
            return response()->json([
                'success' => true,
                'data' => $formattedTemplate
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product template not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('API - Failed to retrieve product template', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product template: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取产品模板关联的产品
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProducts(Request $request, $id)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            
            $template = ProductTemplate::findOrFail($id);
            
            $query = $template->linkedProducts();
            $total = $query->count();
            
            $products = $query->with('media')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();
            
            // 格式化产品数据
            $productsFormatted = $products->map(function($product) {
                $images = [];
                
                if ($product->relationLoaded('media') && $product->media->isNotEmpty()) {
                    $images = $product->media->map(function($media) {
                        return [
                            'id' => $media->id,
                            'url' => asset('storage/' . $media->path),
                            'thumbnail' => asset('storage/' . $media->path),
                        ];
                    })->toArray();
                }
                
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => (float)$product->price,
                    'stock_quantity' => (int)$product->stock_quantity,
                    'images' => $images,
                    'parameter_group' => $product->pivot->parameter_group ?? null,
                    'relation_type' => 'linked'
                ];
            })->toArray();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'template' => [
                        'id' => $template->id,
                        'name' => $template->name
                    ],
                    'products' => $productsFormatted,
                    'pagination' => [
                        'total' => $total,
                        'per_page' => (int)$perPage,
                        'current_page' => (int)$page,
                        'last_page' => ceil($total / $perPage),
                        'from' => $total ? ($page - 1) * $perPage + 1 : null,
                        'to' => $total ? min($page * $perPage, $total) : null
                    ]
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product template not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('API - Failed to retrieve products for template', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取指定参数组的产品
     * 
     * @param int $id
     * @param string $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductsByParameterGroup($id, $group)
    {
        try {
            $template = ProductTemplate::findOrFail($id);
            
            // 解码URL编码的参数组字符串
            $parameterGroup = urldecode($group);
            
            $products = $template->linkedProducts()
                ->where('parameter_group', $parameterGroup)
                ->with('media')
                ->get();
            
            // 格式化产品数据
            $productsFormatted = $products->map(function($product) {
                $images = [];
                
                if ($product->relationLoaded('media') && $product->media->isNotEmpty()) {
                    $images = $product->media->map(function($media) {
                        return [
                            'id' => $media->id,
                            'url' => asset('storage/' . $media->path),
                            'thumbnail' => asset('storage/' . $media->path),
                        ];
                    })->toArray();
                }
                
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => (float)$product->price,
                    'stock_quantity' => (int)$product->stock_quantity,
                    'images' => $images,
                    'relation_type' => 'linked'
                ];
            })->toArray();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'template' => [
                        'id' => $template->id,
                        'name' => $template->name
                    ],
                    'parameter_group' => $parameterGroup,
                    'products' => $productsFormatted
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product template not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('API - Failed to retrieve products by parameter group', [
                'id' => $id,
                'group' => $group,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 格式化模板集合
     * 
     * @param \Illuminate\Database\Eloquent\Collection $templates
     * @return array
     */
    private function formatTemplates($templates)
    {
        // Ensure $templates is iterable (Collection or array)
        if (!$templates instanceof \Illuminate\Support\Collection && !is_array($templates)) {
             Log::warning('formatTemplates received non-iterable data.', ['type' => gettype($templates)]);
             return []; // Return empty array if not iterable
         }
         
        return collect($templates)->map(function($template) {
            return $this->formatTemplate($template); // formatTemplate already returns an array
        })->toArray(); // Ensure the final output is an array
    }
    
    /**
     * 格式化单个模板
     * 
     * @param \App\Models\ProductTemplate $template
     * @return array
     */
    private function formatTemplate($template)
    {
        $images = [];
        
        // 处理模板自身的媒体，添加严格检查
        if (
            $template->relationLoaded('media') && 
            $template->media instanceof EloquentCollection && // Check if it's a collection
            $template->media->isNotEmpty()                   // Check if not empty
        ) {
            $images = $template->media->map(function($media) {
                return [
                    'id' => $media->id,
                    'url' => asset('storage/' . $media->path),
                    'thumbnail' => asset('storage/' . $media->path),
                ];
            })->toArray();
        }
        
        // 获取参数
        $parameters = [];
        if (!empty($template->parameters) && is_array($template->parameters)) {
            $parameters = $template->parameters;
        } elseif (!empty($template->parameters) && is_string($template->parameters)) {
            try {
                $parameters = json_decode($template->parameters, true) ?? [];
            } catch (\Exception $e) {
                $parameters = [];
            }
        }
        
        // 处理关联的商品
        $linkedProducts = [];
        
        if ($template->relationLoaded('linkedProducts') && $template->linkedProducts->isNotEmpty()) {
            $linkedProducts = $template->linkedProducts->map(function($product) {
                $productImages = []; // Renamed to avoid conflict
                
                // Check if media relation is loaded, is a Collection, and is not empty
                if (
                    $product->relationLoaded('media') &&
                    $product->media instanceof EloquentCollection && // Ensure it's a collection
                    $product->media->isNotEmpty()                    // Ensure it's not empty
                ) {
                    $productImages = $product->media->map(function($media) {
                        return [
                            'id' => $media->id,
                            'url' => asset('storage/' . $media->path),
                            'thumbnail' => asset('storage/' . $media->path),
                        ];
                    })->toArray(); // Ensure the final result is an array
                }
                
                // 获取产品的详细价格信息
                $price = (float)$product->selling_price; // 使用selling_price作为主要价格
                // Use the actual original_price column if it exists, otherwise fallback
                $originalPrice = property_exists($product, 'original_price') && $product->original_price ? (float)$product->original_price : $price;
                $discountPercentage = (float)$product->discount_percentage; // 折扣百分比
                
                // Re-calculate price if discount percentage is primary source of truth
                // (Adjust logic based on how discounts are actually applied)
                // if ($discountPercentage > 0 && $originalPrice > 0) { 
                //     $price = $originalPrice * (1 - $discountPercentage / 100);
                // } else if ($product->relationLoaded('priceAgreements')) { ... }

                // Use the getTotalStock() method from the Product model
                $stockQuantity = $product->getTotalStock(); 
                
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $price,
                    'original_price' => $originalPrice,
                    'discount_percentage' => $discountPercentage,
                    'stock_quantity' => $stockQuantity, // Use calculated stock
                    'images' => $productImages,
                    'parameter_group' => $product->pivot->parameter_group ?? null,
                    'relation_type' => 'linked',
                    'parameters' => $product->parameters ?: []
                ];
            })->toArray();
        }
        
        return [
            'id' => $template->id,
            'name' => $template->name,
            'description' => $template->description,
            'category' => $template->category ? [
                'id' => $template->category->id,
                'name' => $template->category->name,
            ] : null,
            'parameters' => $parameters,
            'images' => $images,
            'is_active' => (bool)$template->is_active,
            'is_featured' => (bool)$template->is_featured,
            'is_new_arrival' => (bool)$template->is_new_arrival,
            'is_sale' => (bool)$template->is_sale,
            'created_at' => $template->created_at ? $template->created_at->toIso8601String() : null,
            'updated_at' => $template->updated_at ? $template->updated_at->toIso8601String() : null,
            'linked_products' => $linkedProducts,
            'promo_page_url' => $template->promo_page_url ?? null // Add promo page URL
        ];
    }

    /**
     * 获取下一个参数组合
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNextParameterCombo(Request $request, $id)
    {
        try {
            $currentCombo = $request->input('current_combo');
            
            // 查找模板
            $template = ProductTemplate::findOrFail($id);
            $parameters = $template->parameters;
            
            if (empty($parameters) || !is_array($parameters)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Template has no parameters'
                ], 400);
            }
            
            // 获取所有可能的参数组合
            $combos = [];
            $this->generateParameterCombinations($parameters, $combos);
            
            // 排序参数组合以确保顺序一致
            sort($combos);
            
            if (empty($combos)) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'No parameter combinations found'
                ], 404);
            }
            
            // 获取已链接的参数组合
            $linkedCombos = DB::table('product_template_product')
                ->where('product_template_id', $id)
                ->pluck('parameter_group')
                ->toArray();
            
            // 筛选出未链接的组合
            $unlinkedCombos = array_diff($combos, $linkedCombos);
            
            if (empty($unlinkedCombos)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'All parameter combinations are linked',
                    'next_combo' => null
                ]);
            }
            
            // 如果提供了当前组合，查找下一个
            if ($currentCombo) {
                $foundCurrent = false;
                $nextCombo = null;
                
                foreach ($unlinkedCombos as $combo) {
                    if ($foundCurrent) {
                        $nextCombo = $combo;
                        break;
                    }
                    
                    if ($combo === $currentCombo) {
                        $foundCurrent = true;
                    }
                }
                
                // 如果没找到下一个，返回第一个
                if (!$nextCombo && !empty($unlinkedCombos)) {
                    $nextCombo = reset($unlinkedCombos);
                }
                
                return response()->json([
                    'status' => 'success',
                    'next_combo' => $nextCombo
                ]);
            } else {
                // 没有提供当前组合，返回第一个未链接的
                $nextCombo = reset($unlinkedCombos);
                
                return response()->json([
                    'status' => 'success',
                    'next_combo' => $nextCombo
                ]);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product template not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('API - Failed to get next parameter combo', [
                'id' => $id,
                'current_combo' => $request->input('current_combo'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get next parameter combo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取下一个未链接的参数
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNextUnlinkedParameter(Request $request, $id)
    {
        try {
            $currentGroup = $request->input('current_group');
            $currentValue = $request->input('current_value');
            
            // 查找模板
            $template = ProductTemplate::findOrFail($id);
            $parameters = $template->parameters;
            
            if (empty($parameters) || !is_array($parameters)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Template has no parameters'
                ], 400);
            }
            
            // 生成单参数值列表
            $parameterValues = [];
            foreach ($parameters as $group => $values) {
                foreach ($values as $value) {
                    $parameterValues[] = [
                        'group' => $group,
                        'value' => $value
                    ];
                }
            }
            
            // 排序参数值以确保顺序一致
            usort($parameterValues, function($a, $b) {
                $groupCompare = strcmp($a['group'], $b['group']);
                if ($groupCompare !== 0) {
                    return $groupCompare;
                }
                return strcmp($a['value'], $b['value']);
            });
            
            if (empty($parameterValues)) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'No parameter values found'
                ], 404);
            }
            
            // 获取已链接的参数值
            $linkedParams = DB::table('product_template_product')
                ->where('product_template_id', $id)
                ->where('parameter_group', '!=', null)
                ->whereRaw('parameter_group NOT LIKE "%=%"') // 排除参数组合(包含分号的)
                ->select('parameter_group', 'parameter_value')
                ->get();
            
            $linkedParamMap = [];
            foreach ($linkedParams as $param) {
                $linkedParamMap[$param->parameter_group . '=' . $param->parameter_value] = true;
            }
            
            // 筛选出未链接的参数值
            $unlinkedParams = [];
            foreach ($parameterValues as $param) {
                $key = $param['group'] . '=' . $param['value'];
                if (!isset($linkedParamMap[$key])) {
                    $unlinkedParams[] = $param;
                }
            }
            
            if (empty($unlinkedParams)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'All parameters are linked',
                    'next_parameter' => null
                ]);
            }
            
            // 如果提供了当前参数，查找下一个
            if ($currentGroup && $currentValue) {
                $foundCurrent = false;
                $nextParam = null;
                
                foreach ($unlinkedParams as $param) {
                    if ($foundCurrent) {
                        $nextParam = $param;
                        break;
                    }
                    
                    if ($param['group'] === $currentGroup && $param['value'] === $currentValue) {
                        $foundCurrent = true;
                    }
                }
                
                // 如果没找到下一个，返回第一个
                if (!$nextParam && !empty($unlinkedParams)) {
                    $nextParam = reset($unlinkedParams);
                }
                
                return response()->json([
                    'status' => 'success',
                    'next_parameter' => $nextParam
                ]);
            } else {
                // 没有提供当前参数，返回第一个未链接的
                $nextParam = reset($unlinkedParams);
                
                return response()->json([
                    'status' => 'success',
                    'next_parameter' => $nextParam
                ]);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product template not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('API - Failed to get next unlinked parameter', [
                'id' => $id,
                'current_group' => $request->input('current_group'),
                'current_value' => $request->input('current_value'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get next unlinked parameter: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 生成参数组合
     * 
     * @param array $parameters
     * @param array &$result
     * @param array $current
     * @param int $index
     * @return void
     */
    private function generateParameterCombinations($parameters, &$result, $current = [], $index = 0)
    {
        $groups = array_keys($parameters);
        
        // 如果已经处理完所有参数组，添加当前组合
        if ($index >= count($groups)) {
            $comboString = '';
            foreach ($current as $group => $value) {
                if (!empty($comboString)) {
                    $comboString .= ';';
                }
                $comboString .= $group . '=' . $value;
            }
            
            if (!empty($comboString)) {
                $result[] = $comboString;
            }
            return;
        }
        
        $group = $groups[$index];
        foreach ($parameters[$group] as $value) {
            $current[$group] = $value;
            $this->generateParameterCombinations($parameters, $result, $current, $index + 1);
        }
    }
} 