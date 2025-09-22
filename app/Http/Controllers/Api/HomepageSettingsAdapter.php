<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

/**
 * 首页设置API适配器控制器
 * 
 * 该控制器的目的是将设置表中的首页设置数据转换为与前端期望的格式一致
 */
class HomepageSettingsAdapter extends Controller
{
    protected $settingsService;
    protected $productController;
    
    public function __construct(SettingsService $settingsService, ProductController $productController)
    {
        $this->settingsService = $settingsService;
        $this->productController = $productController;
    }
    
    /**
     * 获取首页设置
     * 
     * @return JsonResponse
     */
    public function getHomepageSettings(): JsonResponse
    {
        try {
            // 从设置表中获取首页相关设置
            $settings = Setting::where('group', 'homepage')->get();
            
            // 将设置转换为键值对数组
            $settingsArray = [];
            foreach ($settings as $setting) {
                $settingsArray[$setting->key] = $setting->value;
            }
            
            Log::debug('Retrieved homepage settings from database', [
                'settings' => $settingsArray,
                'count' => count($settings)
            ]);
            
            // 获取轮播图数据
            $banners = $this->getBanners();
            
            // 设置默认值
            $defaults = [
                // Featured Products 默认值
                'featured_products_title' => 'Featured Products',
                'featured_products_subtitle' => 'Our carefully selected premium products known for exceptional quality',
                'featured_products_button_text' => 'View All Featured Products',
                'featured_products_button_link' => '/products?featured=true',
                'featured_products_banner_title' => 'Featured Products Collection',
                'featured_products_banner_subtitle' => 'Discover our selection of premium quality shoes',

                
                // New Products 默认值
                'new_products_title' => 'New Arrivals',
                'new_products_subtitle' => 'Check out our latest products',
                'new_products_button_text' => 'View All New Arrivals',
                'new_products_button_link' => '/products?new=true',
                
                // Sale Products 默认值
                'sale_products_title' => 'On Sale',
                'sale_products_subtitle' => 'Great deals on quality products',
                'sale_products_button_text' => 'View All Sale Items',
                'sale_products_button_link' => '/products?sale=true',
                
                // Carousel 默认值
                'carousel_autoplay' => true,
                'carousel_delay' => 5000,
                'carousel_transition' => 'slide',
                'carousel_show_navigation' => true,
                'carousel_show_indicators' => true,
                
                // 其他设置默认值
                'products_per_page' => 12,
                'new_product_days' => 30,
                'layout' => 'standard',
                'show_brands' => true,
                'show_promotion' => true,
                'auto_add_new_products' => true,
                'auto_add_sale_products' => true
            ];
            
            // 合并数据库值和默认值，优先使用数据库值
            $flatData = array_merge($defaults, $settingsArray);
            
            // 处理媒体ID和图片URL (现在主要针对offer区域)
            if (!empty($flatData['offer_media_id'])) {
                $media = Media::find($flatData['offer_media_id']);
                if ($media) {
                    $flatData['offer_image_url'] = asset('storage/' . $media->path);
                }
            }
            
            // 获取产品数据
            // ------------------------------------------------
            // 1. 获取精选产品
            $featuredProductsRequest = new Request();
            $featuredProductsRequest->merge([
                'limit' => $flatData['featured_products_limit'] ?? 8,
                'per_page' => $flatData['featured_products_limit'] ?? 8,
                'page' => 1
            ]);
            $featuredProductsResponse = $this->productController->getFeaturedProducts($featuredProductsRequest);
            $featuredProductsData = json_decode($featuredProductsResponse->getContent(), true);
            $featuredProducts = [];
            
            if (isset($featuredProductsData['status']) && $featuredProductsData['status'] === 'success') {
                $featuredProducts = $featuredProductsData['data']['products'] ?? [];
            }
            
            // 2. 获取新品
            $newArrivalsRequest = new Request();
            $newArrivalsRequest->merge([
                'limit' => $flatData['new_products_limit'] ?? 8
            ]);
            $newArrivalsResponse = $this->productController->getNewArrivals($newArrivalsRequest);
            $newArrivalsData = json_decode($newArrivalsResponse->getContent(), true);
            $newArrivals = [];
            
            if (isset($newArrivalsData['status']) && $newArrivalsData['status'] === 'success') {
                $newArrivals = $newArrivalsData['data']['products'] ?? [];
            }
            
            // 3. 获取促销产品
            $saleProductsRequest = new Request();
            $saleProductsRequest->merge([
                'limit' => $flatData['sale_products_limit'] ?? 8,
                'per_page' => $flatData['sale_products_limit'] ?? 8,
                'page' => 1
            ]);
            $saleProductsResponse = $this->productController->getPromotionProducts($saleProductsRequest);
            $saleProductsData = json_decode($saleProductsResponse->getContent(), true);
            $saleProducts = [];
            
            if (isset($saleProductsData['status']) && $saleProductsData['status'] === 'success') {
                $saleProducts = $saleProductsData['data']['products'] ?? [];
            }
            
            // 记录获取到的产品数据
            Log::debug('Retrieved homepage products', [
                'featured_count' => count($featuredProducts),
                'new_arrivals_count' => count($newArrivals),
                'sale_products_count' => count($saleProducts)
            ]);
            // ------------------------------------------------
            
            // 创建新的结构化响应数据
            $structuredData = [
                'success' => true,
                'data' => [
                    // Banner 数据
                    'banners' => $banners,
                    
                    // Carousel 设置
                    'carousel' => [
                        'autoplay' => $this->getBoolValue($flatData['carousel_autoplay']),
                        'delay' => (int)$flatData['carousel_delay'],
                        'transition' => $flatData['carousel_transition'],
                        'show_navigation' => $this->getBoolValue($flatData['carousel_show_navigation']),
                        'show_indicators' => $this->getBoolValue($flatData['carousel_show_indicators'])
                    ],
                    
                    // Featured Products 设置
                    'featured_products' => [
                        'title' => $flatData['featured_products_banner_title'] ?? $flatData['featured_products_title'],
                        'subtitle' => $flatData['featured_products_banner_subtitle'] ?? $flatData['featured_products_subtitle'],
                        'button_text' => $flatData['featured_products_button_text'],
                        'button_link' => $flatData['featured_products_button_link'],
                        'products' => $featuredProducts // 添加产品数据
                    ],
                    
                
                    // New Products 设置
                    'new_products' => [
                        'title' => $flatData['new_products_title'],
                        'subtitle' => $flatData['new_products_subtitle'],
                        'button_text' => $flatData['new_products_button_text'],
                        'button_link' => $flatData['new_products_button_link'],
                        'products' => $newArrivals // 添加产品数据
                    ],
                    
                    // Sale Products 设置
                    'sale_products' => [
                        'title' => $flatData['sale_products_title'],
                        'subtitle' => $flatData['sale_products_subtitle'],
                        'button_text' => $flatData['sale_products_button_text'],
                        'button_link' => $flatData['sale_products_button_link'],
                        'products' => $saleProducts // 添加产品数据
                    ],
                    
                    // 一般设置
                    'settings' => [
                        'products_per_page' => (int)$flatData['products_per_page'],
                        'new_product_days' => (int)$flatData['new_product_days'],
                        'layout' => $flatData['layout'],
                        'show_brands' => $this->getBoolValue($flatData['show_brands']),
                        'show_promotion' => $this->getBoolValue($flatData['show_promotion']),
                        'auto_add_new_products' => $this->getBoolValue($flatData['auto_add_new_products']),
                        'auto_add_sale_products' => $this->getBoolValue($flatData['auto_add_sale_products'])
                    ]
                ]
            ];
            
            // 简化响应结构，只返回结构化数据，避免重复和混乱
            $responseData = $structuredData;
            
            // 记录最终响应数据
            Log::debug('Final homepage settings response', [
                'banner_count' => count($responseData['data']['banners']),
                'featured_products_count' => count($featuredProducts),
                'new_arrivals_count' => count($newArrivals),
                'sale_products_count' => count($saleProducts)
            ]);
            
            return response()->json($responseData);
        } catch (\Exception $e) {
            Log::error('Failed to fetch homepage settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // 发生错误时返回默认值
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch homepage settings',
                'data' => [
                'banners' => [],
                    'carousel' => [
                        'autoplay' => true,
                        'delay' => 5000,
                        'transition' => 'slide',
                        'show_navigation' => true,
                        'show_indicators' => true
                    ],
                    'featured_products' => [
                        'title' => 'Featured Products',
                        'subtitle' => 'Our carefully selected premium products known for exceptional quality',
                        'button_text' => 'View All Featured Products',
                        'button_link' => '/products?featured=true',
                        'products' => [] // 添加空产品数组
                    ],
                    'new_products' => [
                        'title' => 'New Arrivals',
                        'subtitle' => 'Check out our latest products',
                        'button_text' => 'View All New Arrivals',
                        'button_link' => '/products?new=true',
                        'products' => [] // 添加空产品数组
                    ],
                    'sale_products' => [
                        'title' => 'On Sale',
                        'subtitle' => 'Great deals on quality products',
                        'button_text' => 'View All Sale Items',
                        'button_link' => '/products?sale=true',
                        'products' => [] // 添加空产品数组
                    ],
                    'settings' => [
                        'products_per_page' => 12,
                        'new_product_days' => 30,
                        'layout' => 'standard',
                        'show_brands' => true,
                        'show_promotion' => true,
                        'auto_add_new_products' => true,
                        'auto_add_sale_products' => true
                    ]
                ]
            ]);
        }
    }
    
    /**
     * 获取轮播图数据
     * 
     * @return array
     */
    protected function getBanners(): array
    {
        try {
            // 尝试查询轮播图表
            $banners = DB::table('banners')
                ->leftJoin('media', 'banners.media_id', '=', 'media.id')
                ->where('banners.is_active', true)
                ->orderBy('banners.order')
                ->select([
                    'banners.id',
                    'banners.title',
                    'banners.subtitle',
                    'banners.button_text',
                    'banners.button_link',
                    'banners.order',
                    'banners.is_active',
                    'media.path as media_path'
                ])
                ->get();
            
            // 检查是否有结果
            if ($banners->isEmpty()) {
                Log::info('No active banners found in database');
                return [];
            }
            
            // 格式化轮播图数据
            $formattedBanners = [];
            foreach ($banners as $banner) {
                $imageUrl = $banner->media_path 
                    ? asset('storage/' . $banner->media_path) 
                    : 'https://placehold.co/1920x600/e6f7ff/0099cc?text=Banner+' . $banner->id;
                
                $formattedBanners[] = [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'subtitle' => $banner->subtitle,
                    'button_text' => $banner->button_text,
                    'button_link' => $banner->button_link,
                    'is_active' => (bool)$banner->is_active,
                    'order' => $banner->order,
                    'image_url' => $imageUrl,
                    'image' => $imageUrl // 为了前端兼容，保留image字段
                ];
            }
            
            Log::info('Retrieved banners from database', [
                'count' => count($formattedBanners)
            ]);
            
            return $formattedBanners;
        } catch (\Exception $e) {
            // 如果出现异常（例如表不存在），记录日志并返回空数组
            Log::error('Error fetching banners', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * 获取布尔值
     * 
     * @param mixed $value
     * @return bool
     */
    protected function getBoolValue($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            return $value === '1' || strtolower($value) === 'true';
        }
        
        return (bool)$value;
    }
    
    /**
     * 保存首页设置
     * 
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function saveHomepageSettings(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            Log::info('Saving homepage settings', [
                'data' => $request->all()
            ]);
            
            DB::beginTransaction();
            
            // 处理所有设置，确保设置表中有相应的记录
            $settings = $request->all();
            $group = 'homepage';
            
            // 跟踪实际更新了哪些设置
            $updatedSettings = [];
            
            // 特殊处理嵌套对象
            $nestedObjects = [];
            
            // 预处理嵌套对象，如carousel
            foreach ($settings as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $nestedObjects[$key] = $value;
                    
                    // 将嵌套对象的每个属性展平为单独的设置
                    foreach ($value as $nestedKey => $nestedValue) {
                        $fullKey = $key . '_' . $nestedKey;
                        $settings[$fullKey] = $nestedValue;
                    }
                    
                    // 保存一个JSON版本的完整对象
                    $settings[$key . '_json'] = json_encode($value);
                }
            }
            
            // 记录展平后的设置
            Log::debug('Flattened settings before saving', [
                'flattened_count' => count($settings),
                'nested_objects_count' => count($nestedObjects)
            ]);
            
            foreach ($settings as $key => $value) {
                // 跳过非设置字段
                if (in_array($key, ['success', 'data', 'message', '_token', '_method'])) {
                    continue;
                }
                
                // 处理嵌套对象，如果还有嵌套对象（未被预处理）
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                
                // 获取适当的数据类型
                $type = 'string';
                if (is_bool($value)) {
                    $type = 'boolean';
                    // 将布尔值转换为字符串
                    $value = $value ? 'true' : 'false';
                } elseif (is_string($value) && in_array(strtolower($value), ['true', 'false', '0', '1', 'yes', 'no', 'on', 'off'])) {
                    $type = 'boolean';
                    // 规范化布尔字符串值
                    $lowercase = strtolower($value);
                    $value = (in_array($lowercase, ['true', '1', 'yes', 'on'])) ? 'true' : 'false';
                } elseif (is_numeric($value) && !is_string($value)) {
                    $type = 'number';
                    // 确保数字被以字符串形式存储
                    $value = (string)$value;
                }
                
                // 生成此设置的标签
                $label = ucwords(str_replace('_', ' ', $key));
                
                // 检查是否是嵌套对象的属性
                foreach ($nestedObjects as $objectKey => $objectValue) {
                    $prefix = $objectKey . '_';
                    if (strpos($key, $prefix) === 0) {
                        $attributeName = substr($key, strlen($prefix));
                        $label = ucwords(str_replace('_', ' ', $objectKey)) . ' ' . ucwords(str_replace('_', ' ', $attributeName));
                        break;
                    }
                }
                
                // 创建或更新设置
                $setting = Setting::updateOrCreate(
                    ['group' => $group, 'key' => $key],
                    [
                        'value' => $value,
                        'type' => $type,
                        'label' => $label,
                        'description' => 'Homepage setting for ' . str_replace('_', ' ', $key),
                        'is_public' => true
                    ]
                );
                
                $updatedSettings[$key] = [
                    'value' => $value,
                    'type' => $type,
                    'label' => $label,
                    'wasRecentlyCreated' => $setting->wasRecentlyCreated
                ];
                
                // 清除此设置的缓存
                Cache::forget('setting:'.$key);
            }
            
            DB::commit();
            
            // 清除应用缓存
            Artisan::call('cache:clear');
            
            // 记录成功更新的内容
            Log::info('Successfully updated homepage settings', [
                'updated_settings' => array_keys($updatedSettings),
                'count' => count($updatedSettings)
            ]);
            
            // 返回更新后的设置
            return $this->getHomepageSettings();
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to save homepage settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save homepage settings: ' . $e->getMessage()
            ], 500);
        }
    }
} 