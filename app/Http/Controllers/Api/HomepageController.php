<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductTemplate;
use App\Models\Banner;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HomepageController extends Controller
{
    /**
     * 获取所有首页数据
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllData()
    {
        // This method now relies on the caching implemented in the individual getter methods below.
        // Consider caching the entire combined result if needed, but individual caches offer more granularity.
        $cacheKeyAllData = 'homepage_all_data_combined';
        $ttlAllData = 900; // Cache combined result for 15 minutes

        try {
             $allData = Cache::remember($cacheKeyAllData, $ttlAllData, function () use ($cacheKeyAllData) {
                Log::info("[Cache Miss] Fetching combined homepage data from DB/individual caches for key: {$cacheKeyAllData}");
                // Call individual methods which should now be cached
                $featuredTemplates = $this->getFeaturedTemplatesData(); // Use internal helper
                $newArrivalTemplates = $this->getNewArrivalTemplatesData(); // Use internal helper
                $saleTemplates = $this->getSaleTemplatesData(); // Use internal helper
                $banners = $this->getBannersData(); // Use internal helper
                $settings = $this->getHomepageSettings(); // Uses its own cache

                 return [
                    'featured_templates' => $featuredTemplates,
                    'new_arrival_templates' => $newArrivalTemplates,
                    'sale_templates' => $saleTemplates,
                    'banners' => $banners,
                    'settings' => $settings,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $allData
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve homepage data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Attempt to clear the potentially bad combined cache entry
            Cache::forget($cacheKeyAllData);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve homepage data: ' . $e->getMessage()
            ], 500);
        }
    }

    // --- Helper methods for fetching data (used by getAllData and potentially direct API calls) ---

    private function getFeaturedTemplatesData($page = 1, $perPage = 8) // Default limit for internal call
    {
        $cacheKey = "homepage_featured_templates_page_{$page}_limit_{$perPage}";
        $ttl = 1800; // 30 minutes

        return Cache::remember($cacheKey, $ttl, function() use ($page, $perPage, $cacheKey) {
            Log::info("[Cache Miss] Fetching featured templates from DB for key: {$cacheKey}");
            $query = ProductTemplate::where('is_active', true)
                ->where('is_featured', true)
                ->with('category', 'media', 'products', 'linkedProducts')
                ->orderBy('name'); // Consider featured_order if available

            // If called internally with default limit, use take(). If potentially paginated, use paginate().
            // For simplicity in this refactor, sticking to take() based on original getAllData logic.
            // If the API endpoint getFeaturedTemplates needs real pagination, adjust logic there.
             if ($perPage === 8 && $page === 1) { // Match original getAllData logic
                $templates = $query->take($perPage)->get();
             } else {
                 // Basic pagination if different params used (adjust if full pagination needed)
                 $templates = $query->skip(($page - 1) * $perPage)->take($perPage)->get();
             }
            
            return $this->formatTemplates($templates);
        });
    }

     private function getNewArrivalTemplatesData($page = 1, $perPage = 8)
    {
        $cacheKey = "homepage_new_templates_page_{$page}_limit_{$perPage}";
        $ttl = 900; // 15 minutes

        return Cache::remember($cacheKey, $ttl, function() use ($page, $perPage, $cacheKey) {
            Log::info("[Cache Miss] Fetching new templates from DB for key: {$cacheKey}");
            $query = ProductTemplate::where('is_active', true)
                ->where('is_new_arrival', true)
                ->with('category', 'media', 'products', 'linkedProducts')
                ->orderBy('created_at', 'desc');

             if ($perPage === 8 && $page === 1) {
                $templates = $query->take($perPage)->get();
             } else {
                 $templates = $query->skip(($page - 1) * $perPage)->take($perPage)->get();
             }

            return $this->formatTemplates($templates);
        });
    }

     private function getSaleTemplatesData($page = 1, $perPage = 8)
    {
        $cacheKey = "homepage_sale_templates_page_{$page}_limit_{$perPage}";
        $ttl = 1800; // 30 minutes

        return Cache::remember($cacheKey, $ttl, function() use ($page, $perPage, $cacheKey) {
            Log::info("[Cache Miss] Fetching sale templates from DB for key: {$cacheKey}");
            $query = ProductTemplate::where('is_active', true)
                ->where('is_sale', true)
                ->with('category', 'media', 'products', 'linkedProducts')
                ->orderBy('name');

            if ($perPage === 8 && $page === 1) {
                $templates = $query->take($perPage)->get();
             } else {
                 $templates = $query->skip(($page - 1) * $perPage)->take($perPage)->get();
             }

            return $this->formatTemplates($templates);
        });
    }

     private function getBannersData()
    {
        $cacheKey = 'homepage_banners_active';
        $ttl = 3600; // 1 hour

        return Cache::remember($cacheKey, $ttl, function() use ($cacheKey){
             Log::info("[Cache Miss] Fetching active banners from DB for key: {$cacheKey}");
             return Banner::where('is_active', true)
                ->orderBy('order')
                ->get()
                ->map(function($banner) {
                    return [
                        'id' => $banner->id,
                        'title' => $banner->title,
                        'subtitle' => $banner->subtitle,
                        'button_text' => $banner->button_text,
                        'button_link' => $banner->button_link,
                        'image_url' => $banner->getImageUrl(),
                        'order' => $banner->order,
                        'is_active' => $banner->is_active, // Redundant but matches original code
                    ];
                });
        });
    }

    // --- Public API endpoint methods --- 

    /**
     * 获取特色产品模板 (API Endpoint)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFeaturedTemplates(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
         // Use a slightly different cache key for the paginated endpoint vs internal call
        $cacheKey = "homepage_featured_templates_api_page_{$page}_limit_{$perPage}";
        $ttl = 1800; // 30 minutes

        try {
            // Use Cache::remember with pagination logic
             $paginatedData = Cache::remember($cacheKey, $ttl, function() use ($page, $perPage, $cacheKey) {
                 Log::info("[Cache Miss] Fetching paginated featured templates from DB for key: {$cacheKey}");
                 $query = ProductTemplate::where('is_active', true)
                    ->where('is_featured', true)
                    ->with('category', 'media', 'products', 'linkedProducts') // Ensure relations are loaded
                    ->orderBy('name'); // Or featured_order

                 $templatesPaginated = $query->paginate($perPage, ['*'], 'page', $page);

                 $formattedTemplates = $this->formatTemplates($templatesPaginated->items()); // Format items

                 return [
                        'templates' => $formattedTemplates,
                        'pagination' => [
                            'total' => $templatesPaginated->total(),
                            'per_page' => $templatesPaginated->perPage(),
                            'current_page' => $templatesPaginated->currentPage(),
                            'last_page' => $templatesPaginated->lastPage(),
                            'from' => $templatesPaginated->firstItem(),
                            'to' => $templatesPaginated->lastItem()
                        ]
                    ];
             });
            
            return response()->json([
                'success' => true,
                'data' => $paginatedData
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve featured templates (API)', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString()
            ]);
            Cache::forget($cacheKey);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve featured templates: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取新品产品模板 (API Endpoint)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNewArrivalTemplates(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $cacheKey = "homepage_new_templates_api_page_{$page}_limit_{$perPage}";
        $ttl = 900; // 15 minutes

        try {
            $paginatedData = Cache::remember($cacheKey, $ttl, function() use ($page, $perPage, $cacheKey) {
                 Log::info("[Cache Miss] Fetching paginated new templates from DB for key: {$cacheKey}");
                 $query = ProductTemplate::where('is_active', true)
                    ->where('is_new_arrival', true)
                    ->with('category', 'media', 'products', 'linkedProducts')
                    ->orderBy('created_at', 'desc');

                $templatesPaginated = $query->paginate($perPage, ['*'], 'page', $page);
                $formattedTemplates = $this->formatTemplates($templatesPaginated->items());

                 return [
                        'templates' => $formattedTemplates,
                        'pagination' => [
                            'total' => $templatesPaginated->total(),
                            'per_page' => $templatesPaginated->perPage(),
                            'current_page' => $templatesPaginated->currentPage(),
                            'last_page' => $templatesPaginated->lastPage(),
                            'from' => $templatesPaginated->firstItem(),
                            'to' => $templatesPaginated->lastItem()
                        ]
                    ];
             });

            return response()->json([
                'success' => true,
                'data' => $paginatedData
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve new arrival templates (API)', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString()
                ]);
            Cache::forget($cacheKey);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve new arrival templates: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取促销产品模板 (API Endpoint)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSaleTemplates(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $cacheKey = "homepage_sale_templates_api_page_{$page}_limit_{$perPage}";
        $ttl = 1800; // 30 minutes

        try {
             $paginatedData = Cache::remember($cacheKey, $ttl, function() use ($page, $perPage, $cacheKey) {
                 Log::info("[Cache Miss] Fetching paginated sale templates from DB for key: {$cacheKey}");
                 $query = ProductTemplate::where('is_active', true)
                    ->where('is_sale', true)
                    ->with('category', 'media', 'products', 'linkedProducts')
                    ->orderBy('name');

                 $templatesPaginated = $query->paginate($perPage, ['*'], 'page', $page);
                 $formattedTemplates = $this->formatTemplates($templatesPaginated->items());

                 return [
                        'templates' => $formattedTemplates,
                        'pagination' => [
                            'total' => $templatesPaginated->total(),
                            'per_page' => $templatesPaginated->perPage(),
                            'current_page' => $templatesPaginated->currentPage(),
                            'last_page' => $templatesPaginated->lastPage(),
                            'from' => $templatesPaginated->firstItem(),
                            'to' => $templatesPaginated->lastItem()
                        ]
                    ];
             });

            return response()->json([
                'success' => true,
                'data' => $paginatedData
            ]);
        } catch (\Exception $e) {
             Log::error('Failed to retrieve sale templates (API)', [
                 'error' => $e->getMessage(), 
                 'trace' => $e->getTraceAsString()
                 ]);
            Cache::forget($cacheKey);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve sale templates: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取首页轮播图 (API Endpoint)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBanners()
    {
        $cacheKey = 'homepage_banners_active'; // Use the same key as internal helper
        $ttl = 3600; // 1 hour
        try {
            // Retrieve banners using the cached helper method
            $banners = $this->getBannersData(); 
            
            return response()->json([
                'success' => true,
                'data' => [
                    'banners' => $banners
                ]
            ]);
        } catch (\Exception $e) {
             Log::error('Failed to retrieve banners (API)', [
                 'error' => $e->getMessage(), 
                 'trace' => $e->getTraceAsString()
                 ]);
            // No specific cache key for this endpoint method, relies on helper cache
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve banners: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取首页设置 (API Endpoint)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSettings()
    {
         // This method uses the internal getHomepageSettings which has its own cache
        try {
            $settings = $this->getHomepageSettings(); 
            
            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
        } catch (\Exception $e) {
             Log::error('Failed to retrieve settings (API)', [
                 'error' => $e->getMessage(), 
                 'trace' => $e->getTraceAsString()
                 ]);
             // No specific cache key for this endpoint method, relies on helper cache
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve homepage settings: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 格式化产品模板数据
     *
     * @param \Illuminate\Database\Eloquent\Collection|array $templates
     * @return array
     */
    private function formatTemplates($templates)
    {
        // Ensure $templates is iterable
        if (!is_iterable($templates)) {
            Log::warning('[HomepageController] formatTemplates received non-iterable data.', [
                'type' => gettype($templates)
            ]);
            return [];
        }
        
        $formatted = [];
        foreach ($templates as $template) {
            if ($template instanceof ProductTemplate) { // Ensure it's the correct model
                $formatted[] = $this->formatTemplate($template);
            } else {
                 Log::warning('[HomepageController] formatTemplates encountered unexpected item type.', [
                    'item_type' => get_class($template) ?? gettype($template)
                 ]);
            }
        }
        return $formatted;
        // Original code:
        // return $templates->map(function($template) {
        //     return $this->formatTemplate($template);
        // })->toArray();
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
            
            // 处理媒体
            if ($template->relationLoaded('media') && $template->media->isNotEmpty()) {
                $images = $template->media->map(function($media) {
                    // Use helper or direct path construction if Storage facade isn't desired here
                    // Assuming 'storage/' is the correct public path prefix
                    $url = asset('storage/' . $media->path);
                    return [
                        'id' => $media->id,
                        'url' => $url,
                        'thumbnail' => $url, // Use same URL for thumbnail for simplicity
                    ];
                })->toArray();
            } else {
                 Log::debug('[HomepageController] Template media relation not loaded or empty', ['template_id' => $template->id]);
            }
        
        // 获取参数
        $parameters = [];
        if (!empty($template->parameters) && is_array($template->parameters)) {
            $parameters = $template->parameters;
        } elseif (!empty($template->parameters) && is_string($template->parameters)) {
            try {
                $parameters = json_decode($template->parameters, true) ?? [];
            } catch (\Exception $e) {
                Log::error('[HomepageController] Failed to decode template parameters JSON', [
                    'template_id' => $template->id, 
                    'error' => $e->getMessage()
                ]);
                $parameters = [];
            }
        } else {
             Log::debug('[HomepageController] Template parameters are empty or not array/string', ['template_id' => $template->id]);
        }
            
            // 处理关联的商品
            $linkedProducts = [];
            
            if ($template->relationLoaded('linkedProducts') && $template->linkedProducts->isNotEmpty()) {
                $linkedProducts = $template->linkedProducts->map(function($product) {
                    $productImages = [];
                    
                    // Check if product relationship is loaded (it might not be if eager loading wasn't perfect)
                    if ($product instanceof \App\Models\Product) {
                        if ($product->relationLoaded('media') && $product->media->isNotEmpty()) {
                            $productImages = $product->media->map(function($media) {
                                $url = asset('storage/' . $media->path);
                                return [
                                    'id' => $media->id,
                                    'url' => $url,
                                    'thumbnail' => $url,
                                ];
                            })->toArray();
                        }
                    
                        // 获取产品的详细价格信息
                        $price = (float)($product->selling_price ?? 0); // Use selling_price as the primary price
                        $originalPrice = (float)($product->price ?? $price); // Use price as original, fallback to selling_price
                        $discountPercentage = (float)($product->discount_percentage ?? 0); // 折扣百分比
                        
                        // Ensure originalPrice is not lower than price if discount is 0
                        if ($discountPercentage == 0 && $originalPrice < $price) {
                            $originalPrice = $price;
                        }
                        // If discount exists, calculate the discounted price based on original price
                        // If no discount, the selling_price is the final price
                        if ($discountPercentage > 0) {
                             $finalPrice = round($originalPrice * (1 - $discountPercentage / 100), 2);
                        } else {
                             $finalPrice = $price; // selling_price is the final price if no discount
                        }
                        
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'price' => $finalPrice, // Final calculated price
                            'original_price' => $originalPrice, // Original price before discount
                            'discount_percentage' => $discountPercentage,
                            'stock_quantity' => method_exists($product, 'getTotalStock') ? $product->getTotalStock() : 0, // Use getTotalStock method获取总库存
                            'images' => $productImages, // Use loaded product images
                            'parameter_group' => $product->pivot->parameter_group ?? null,
                            'relation_type' => 'linked',
                            'parameters' => is_array($product->parameters) ? $product->parameters : (json_decode($product->parameters, true) ?? [])
                        ];
                    } else {
                         Log::warning('[HomepageController] Encountered non-Product item in linkedProducts relation.', ['item_type' => get_class($product) ?? gettype($product)]);
                         return null; // Skip this item
                    }
                })->filter()->values()->toArray(); // filter() removes null items, values() re-indexes
            } else {
                 Log::debug('[HomepageController] Template linkedProducts relation not loaded or empty', ['template_id' => $template->id]);
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
                'linked_products' => $linkedProducts
            ];
    }
    
    /**
     * 获取首页设置 (Optimized with selective fetch and caching)
     *
     * @return array
     */
    private function getHomepageSettings()
    {
        // Define the keys needed for the homepage
        $requiredKeys = [
            'show_promotion',
            'new_products_days', // Assuming used for logic, even if not directly returned
            'homepage_new_product_days', // Assuming used for logic
            'homepage_auto_add_new_products', // Assuming used for logic
            'homepage_auto_add_sale_products', // Assuming used for logic
            'auto_add_new_products', // Assuming used for logic
            'new_products_display_days', // Assuming used for logic
            'site_title',
            'site_description',
            'products_per_page', // Assuming used for logic
            'new_product_days', // Assuming used for logic
            'layout',
            'show_brands',
            'auto_add_sale_products', // Assuming used for logic
            'featured_products_title',
            'featured_products_subtitle',
            'featured_products_button_text',
            'featured_products_button_link',
            'featured_products_banner_title', // Assuming used for logic
            'featured_products_banner_subtitle', // Assuming used for logic
            'new_products_title',
            'new_products_subtitle',
            'new_products_button_text',
            'new_products_button_link',
            'sale_products_title',
            'sale_products_subtitle',
            'sale_products_button_text',
            'sale_products_button_link',
            // Add other setting keys if they are directly part of the returned settings object
        ];

        // Define a cache key
        $cacheKey = 'homepage_settings_api';
        // Define cache duration (e.g., 1 hour)
        $cacheDuration = now()->addHours(1); // Use Carbon helper

        try {
            // Attempt to retrieve settings from cache, or fetch and cache if not present
            $settings = Cache::remember($cacheKey, $cacheDuration, function () use ($requiredKeys, $cacheKey) { // Pass key
                Log::info('[HomepageController] Fetching homepage settings from DB for key: {$cacheKey}');
                // Fetch only the required settings from the database
                return Setting::whereIn('key', $requiredKeys)
                              ->pluck('value', 'key') // Get 'value' indexed by 'key'
                              ->all(); // Convert to a plain array
            });

            // Provide default values for any missing keys to ensure consistency
            $defaultValues = [
                'show_promotion' => true,
                'new_products_days' => 30,
                'homepage_new_product_days' => 30,
                'homepage_auto_add_new_products' => 'false',
                'homepage_auto_add_sale_products' => 'false',
                'auto_add_new_products' => false,
                'new_products_display_days' => 30,
                'site_title' => 'Optic System',
                'site_description' => 'Default Description',
                'products_per_page' => 12,
                'new_product_days' => 30,
                'layout' => 'modern',
                'show_brands' => true,
                'auto_add_sale_products' => true,
                'featured_products_title' => 'Featured Products',
                'featured_products_subtitle' => 'Default Subtitle',
                'featured_products_button_text' => 'View All',
                'featured_products_button_link' => '/products?featured=true',
                'featured_products_banner_title' => 'Featured Banner Title',
                'featured_products_banner_subtitle' => 'Featured Banner Subtitle',
                'new_products_title' => 'New Arrivals',
                'new_products_subtitle' => 'Default Subtitle',
                'new_products_button_text' => 'View All',
                'new_products_button_link' => '/products?new=true',
                'sale_products_title' => 'On Sale',
                'sale_products_subtitle' => 'Default Subtitle',
                'sale_products_button_text' => 'View All',
                'sale_products_button_link' => '/products?sale=true',
            ];

            // Merge fetched settings with defaults, ensuring required keys exist
            $mergedSettings = array_merge($defaultValues, $settings);

            // Filter to return only the keys explicitly needed by the frontend API response structure
            // (Adjust this list based on what `getAllData` actually returns under 'settings')
            $returnedKeys = [
                'show_promotion',
                'site_title',
                'site_description',
                'layout',
                'show_brands',
                'featured_products_title',
                'featured_products_subtitle',
                'featured_products_button_text',
                'featured_products_button_link',
                'new_products_title',
                'new_products_subtitle',
                'new_products_button_text',
                'new_products_button_link',
                'sale_products_title',
                'sale_products_subtitle',
                'sale_products_button_text',
                'sale_products_button_link',
                // Include other relevant keys returned by the original implementation
            ];
            
            $finalSettings = array_intersect_key($mergedSettings, array_flip($returnedKeys));

            // Convert boolean-like strings to actual booleans if needed by frontend
            if (isset($finalSettings['show_promotion'])) {
                $finalSettings['show_promotion'] = filter_var($finalSettings['show_promotion'], FILTER_VALIDATE_BOOLEAN);
            }
            if (isset($finalSettings['show_brands'])) {
                $finalSettings['show_brands'] = filter_var($finalSettings['show_brands'], FILTER_VALIDATE_BOOLEAN);
            }
            // Add similar conversions for other boolean settings if necessary
            
            Log::debug('[HomepageController] Returning settings:', $finalSettings);
            return $finalSettings;

        } catch (\Exception $e) {
            // Log the error and return an empty array or default settings array
            Log::error('Error fetching homepage settings: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            // On error, maybe try to forget the potentially bad cache entry
            Cache::forget($cacheKey); // Forget settings cache on error
            // Return default structure on error to prevent breaking the API response
            return []; // Or return the $defaultValues filtered by $returnedKeys
        }
    }
} 