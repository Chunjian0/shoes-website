<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // <-- Import Auth
use Illuminate\Database\Eloquent\Model; // For disabling/enabling events
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\ProductCategory;
use App\Models\CategoryParameter;
use App\Models\ProductTemplate;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Customer; // <-- 添加 Customer 模型
use App\Services\PurchaseService; // Assuming PurchaseService is resolvable via app()
use Illuminate\Support\Str; // Need Str for SKU generation
use Illuminate\Support\Facades\Schema; // <-- Import Schema
use Illuminate\Support\Facades\File;    // <-- Add File Facade
use Illuminate\Support\Facades\Storage; // <-- Add Storage Facade
use App\Models\Banner; // <-- 添加 Banner 模型
// Assuming Spatie MediaLibrary is used:
// use Spatie\\MediaLibrary\\MediaCollections\\Models\\Media; 

class ShoeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // echo "[INFO] Starting ShoeDataSeeder..." . PHP_EOL;
        Model::unguard(); // Allow mass assignment
        Model::withoutEvents(function () { // Disable model events during seeding

            DB::beginTransaction();
            try {
                // 1. Get or Create Warehouse
                $warehouse = Warehouse::firstOrCreate(
                    ['code' => 'MAIN_WH'],
                    [
                        'name' => 'Main Warehouse',
                        'address' => '123 Warehouse St, Logistics City',
                        'contact_phone' => '123-456-7890',
                        'status' => true,
                        'is_store' => 1,
                        // 'is_store' => false // Removed this line as column doesn't exist
                    ]
                );
                // echo "[INFO] Warehouse ensured - " . json_encode(['id' => $warehouse->id, 'name' => $warehouse->name]) . PHP_EOL;

                // 1.5 Create Customer (Ensure warehouse exists first for store_id)
                $customerData = [
                    'name' => 'asdasda',
                    'ic_number' => null, // Set IC number if available
                    'birthday' => null, // Set birthday if available 'YYYY-MM-DD'
                    'contact_number' => null, // Set contact number if available
                    'email' => 'ruienlim11@gmail.com', // Use email as unique identifier
                    'customer_password' => '$2y$12$Vh4WO0wOc6I4IUgEXSY1Ke.QP5OTndG4x1EG0GTTwtLqnZmFKGrTy', // Use the pre-hashed password
                    'address' => null, // Set address if available
                    'points' => 0.00,
                    'last_login_ip' => null,
                    'remarks' => null,
                    'tags' => null,
                    'last_visit_at' => null,
                    'member_level' => 'normal',
                    'store_id' => $warehouse->id, // Link to the created warehouse/store
                ];
                $customer = Customer::updateOrCreate(
                    ['email' => $customerData['email']], // Find by email
                    $customerData // Data to create or update with
                );
                // echo "[INFO] Customer ensured - " . json_encode(['id' => $customer->id, 'email' => $customer->email]) . PHP_EOL;

                // 2. Create Suppliers
                $suppliersData = [
                    ['code' => 'SUP-NIKE', 'name' => 'Nike Official Distributor', 'contact_person' => 'Manager Li', 'email' => 'nike@example.com', 'phone' => '13800138001', 'is_active' => true],
                    ['code' => 'SUP-ADI', 'name' => 'Adidas Wholesale', 'contact_person' => 'Director Zhang', 'email' => 'adidas@example.com', 'phone' => '13800138002', 'is_active' => true],
                    ['code' => 'SUP-GENSHOE', 'name' => 'General Footwear Factory', 'contact_person' => 'Factory Head Wang', 'email' => 'general@example.com', 'phone' => '13800138003', 'is_active' => true],
                    ['code' => 'SUP-FASHION', 'name' => 'Fashion Trends Import', 'contact_person' => 'CEO Zhao', 'email' => 'fashion@example.com', 'phone' => '13800138004', 'is_active' => true],
                    ['code' => 'SUP-LOCALSPORT', 'name' => 'Local Sports Co.', 'contact_person' => 'Boss Sun', 'email' => 'local@example.com', 'phone' => '13800138005', 'is_active' => true],
                ];
                $suppliers = collect(); // Use a collection to store created suppliers
                foreach ($suppliersData as $data) {
                    $supplier = Supplier::updateOrCreate(
                        ['code' => $data['code']],
                        $data
                    );
                    $suppliers->push($supplier);
                    // echo "[INFO] Supplier ensured - " . json_encode(['id' => $supplier->id, 'name' => $supplier->name]) . PHP_EOL;
                }

                // 3. Create Product Categories
                $categoriesData = [
                    ['code' => 'running', 'name' => 'Running Shoes', 'description' => 'Professional footwear suitable for running.', 'is_active' => true],
                    ['code' => 'sneakers', 'name' => 'Sneakers', 'description' => 'Comfortable casual shoes for daily wear.', 'is_active' => true],
                    ['code' => 'sandals', 'name' => 'Sandals', 'description' => 'Open-type footwear for summer or warm climates.', 'is_active' => true],
                    ['code' => 'boots', 'name' => 'Boots', 'description' => 'Footwear covering the ankle or higher.', 'is_active' => true],
                    ['code' => 'formal', 'name' => 'Formal Shoes', 'description' => 'Leather shoes suitable for formal occasions.', 'is_active' => true],
                ];
                $categories = collect();
                foreach ($categoriesData as $data) {
                    $category = ProductCategory::updateOrCreate(
                        ['code' => $data['code']],
                        $data
                    );
                    $categories->put($data['code'], $category); // Use code as key for easy access
                    // echo "[INFO] Category ensured - " . json_encode(['id' => $category->id, 'name' => $category->name]) . PHP_EOL;
                }

                // 4. Create Category Parameters
                $commonParams = [
                    ['name' => 'Material', 'code' => 'material', 'type' => 'select', 'options' => ['Mesh', 'Leather', 'Canvas', 'Synthetic Leather', 'Rubber'], 'is_required' => true, 'sort_order' => 10],
                    ['name' => 'Color', 'code' => 'color', 'type' => 'select', 'options' => ['Black', 'White', 'Grey', 'Blue', 'Red', 'Beige', 'Brown'], 'is_required' => true, 'sort_order' => 20],
                    ['name' => 'Size', 'code' => 'size', 'type' => 'select', 'options' => ['EU 38', 'EU 39', 'EU 40', 'EU 41', 'EU 42', 'EU 43', 'EU 44', 'EU 45'], 'is_required' => true, 'sort_order' => 30],
                ];

                $categorySpecificParams = [
                    'running' => [
                        ['name' => 'Sole Thickness (mm)', 'code' => 'sole_thickness_mm', 'type' => 'number', 'description' => 'Unit: millimeters (mm)', 'is_required' => false, 'sort_order' => 40],
                        ['name' => 'Suitable Surface', 'code' => 'surface', 'type' => 'select', 'options' => ['Road', 'Trail', 'Track'], 'is_required' => false, 'sort_order' => 50],
                    ],
                    'sneakers' => [
                        ['name' => 'Style', 'code' => 'style', 'type' => 'select', 'options' => ['Retro', 'Skate', 'High-top', 'Low-top', 'Fashion'], 'is_required' => false, 'sort_order' => 40],
                    ],
                    'sandals' => [
                        ['name' => 'Type', 'code' => 'sandal_type', 'type' => 'select', 'options' => ['Flip-flop', 'Slipper', 'Sport Sandal', 'Clog'], 'is_required' => true, 'sort_order' => 40],
                    ],
                    'boots' => [
                        ['name' => 'Shaft Height', 'code' => 'shaft_height', 'type' => 'select', 'options' => ['Ankle', 'Mid-calf', 'Knee-high'], 'is_required' => false, 'sort_order' => 40],
                        ['name' => 'Heel Type', 'code' => 'heel_type', 'type' => 'select', 'options' => ['Flat', 'Block', 'Stiletto', 'Wedge'], 'is_required' => false, 'sort_order' => 50],
                    ],
                    'formal' => [
                        ['name' => 'Toe Shape', 'code' => 'toe_shape', 'type' => 'select', 'options' => ['Round', 'Pointed', 'Square'], 'is_required' => false, 'sort_order' => 40],
                        ['name' => 'Closure', 'code' => 'closure', 'type' => 'select', 'options' => ['Lace-up', 'Buckle', 'Loafer', 'Slip-on'], 'is_required' => false, 'sort_order' => 50],
                    ],
                ];

                foreach ($categories as $categoryCode => $category) {
                    // Add common parameters
                    foreach ($commonParams as $paramData) {
                        CategoryParameter::updateOrCreate(
                            ['category_id' => $category->id, 'code' => $paramData['code']],
                            array_merge($paramData, ['category_id' => $category->id, 'is_active' => true])
                        );
                    }
                    // Add category-specific parameters
                    if (isset($categorySpecificParams[$categoryCode])) {
                        foreach ($categorySpecificParams[$categoryCode] as $paramData) {
                            CategoryParameter::updateOrCreate(
                                ['category_id' => $category->id, 'code' => $paramData['code']],
                                array_merge($paramData, ['category_id' => $category->id, 'is_active' => true])
                            );
                        }
                    }
                    // echo "[INFO] Parameters ensured for category - " . json_encode(['id' => $category->id, 'name' => $category->name]) . PHP_EOL;
                }

                $productTemplatesData = [
                    // Running Shoes
                    [
                        'category_code' => 'running',
                        'name' => 'Nike Air Zoom Pegasus 41 (Sail/Cream)',
                        'brand' => 'Nike',
                        'base_price' => 130,
                        'tagline' => 'Your Responsive Workhorse with Wings.',
                        'intro' => 'Experience the familiar, just-for-you feel of the Pegasus, now updated for an even smoother, more responsive ride. The Pegasus 41 continues its legacy as a trusted daily trainer.',
                        'promo_page_url' => '/promo/nike-pegasus-41',
                        'features' => [
                            ['icon' => '💨', 'title' => 'Enhanced Energy Return', 'text' => 'Full-length ReactX foam midsole provides a significant boost in energy return.'],
                            ['icon' => '⚡', 'title' => 'Springy Cushioning', 'text' => 'Forefoot and heel Zoom Air units work in tandem with ReactX foam.'],
                            ['icon' => '🌐', 'title' => 'Breathable Comfort', 'text' => 'Engineered mesh upper is lighter and more breathable.'],
                            ['icon' => '🏃', 'title' => 'Durable Traction', 'text' => 'Classic waffle-inspired rubber outsole ensures reliable traction.'],
                        ],
                        'description' => '', // Placeholder, will be generated below
                        'is_featured' => true, // <-- 添加 is_featured 标记

                        // --- Default Parameters (Example - Please review and complete for all templates) ---
                        'default_parameters' => [
                            'material' => 'Engineered Mesh',
                            'sole_thickness_mm' => 28, // Example value
                            'surface' => 'Road',
                        ],
                        // --- End Default Parameters ---

                        // --- Start Image Mapping ---
                        'template_gallery' => [ // Key images for the template
                            'hero_cream' => '75a5c43e_d10a_4185_ba42_5669fbc8023c.png', // Cream side
                            'feature_sole_cream' => 'd3dff828_00ac_472e_a238_ffc5025057a1.png', // Cream sole
                        ],
                        'variants' => [
                            [
                                'color' => 'Sail/Light Cream',
                                'sizes' => ['EU 39', 'EU 40', 'EU 41', 'EU 42'],
                                'image_map_by_color' => [
                                    'side' => '75a5c43e_d10a_4185_ba42_5669fbc8023c.png',
                                    'top' => '9f898214_4d44_462c_820c_c02939ac32c8.png',
                                    'sole' => 'd3dff828_00ac_472e_a238_ffc5025057a1.png',
                                    'upper' => '641069c2_8d4d_4a92_b106_947869622aff.jpg',
                                    'side_pair' => '62ab7484_307c_44f2_8eb0_18b9f805ab01.png',
                                    'detail' => 'cedc7992_a424_4e0f_8697_d70783bb5e61.png',
                                    'mesh_detail' => 'fd0385c7_9643_4d24_aae9_bd0057bddb25.jpg',
                                    'glow_top' => '2e7b9f0f_38e9_4b9a_9b65_c736ace4923a.jpg',
                                    'glow_back' => '3c08c4bd_3c13_4d59_b2d0_c6b4028ed9b9.jpg',
                                    'glow_front_pair' => '3de96545_315f_4491_a44d_5153a157af65.jpg',
                                    'glow_top_alt' => '556024de_d222_4ce6_9fd6_7f91915ee72a.jpg',
                                    'glow_side_alt' => '524d33cf_8536_415d_b5b1_6692a4767ef5.jpg',
                                ]
                            ],
                        ]
                        // --- End Image Mapping ---
                    ],
                    [
                        'category_code' => 'running',
                        'name' => 'Nike Air Zoom Pegasus 41 (Volt)',
                        'brand' => 'Nike',
                        'base_price' => 130,
                        'tagline' => 'Your Responsive Workhorse with Wings.',
                        'intro' => 'Experience the familiar, just-for-you feel of the Pegasus, now updated for an even smoother, more responsive ride. The Pegasus 41 continues its legacy as a trusted daily trainer.',
                        'promo_page_url' => '/promo/nike-pegasus-41-volt',
                        'features' => [
                            ['icon' => '💨', 'title' => 'Enhanced Energy Return', 'text' => 'Full-length ReactX foam midsole provides a significant boost in energy return.'],
                            ['icon' => '⚡', 'title' => 'Springy Cushioning', 'text' => 'Forefoot and heel Zoom Air units work in tandem with ReactX foam.'],
                            ['icon' => '🌐', 'title' => 'Breathable Comfort', 'text' => 'Engineered mesh upper is lighter and more breathable.'],
                            ['icon' => '🏃', 'title' => 'Durable Traction', 'text' => 'Classic waffle-inspired rubber outsole ensures reliable traction.'],
                        ],
                        'description' => '',
                        'default_parameters' => [
                            'material' => 'Engineered Mesh',
                            'sole_thickness_mm' => 28,
                            'surface' => 'Road',
                        ],
                        'template_gallery' => [
                            'hero_volt' => '0ff8b4a8_0ba7_4ead_99bd_fa5a9c6a162a.jpg',
                            'feature_sole_volt' => '0959a21f_c11f_464c_a31f_12a57db39127.png',
                            'side_dark' => '5475e62f_b06b_4e62_afaa_a7b8f491da26.jpg',
                        ],
                        'variants' => [
                            [
                                'color' => 'Volt/Black',
                                'sizes' => ['EU 41', 'EU 42', 'EU 43'],
                                'image_map_by_color' => [
                                    'side' => '0ff8b4a8_0ba7_4ead_99bd_fa5a9c6a162a.jpg',
                                    'top' => '1ab5c313_f33d_4ac1_931d_78d87a7eee50.jpg',
                                    'sole' => '0959a21f_c11f_464c_a31f_12a57db39127.png',
                                    'detail' => '1e99718a_518c_4ce2_95b7_4162b7e8cc98.jpg',
                                    'side_alt' => '74a83c4a_1a8c_4ef1_be7a_b05aa77bcd36.jpg',
                                    'mesh_detail' => '36231ea5_8f73_4d01_85dd_cc0f56aa24c9.jpg',
                                    'back' => 'db34e9a4_a5a5_49db_8338_6b9b21abcbeb.jpg',
                                    'sole_alt' => '3238b7d4_8adc_45b0_8302_2002b1315467.jpg',
                                    'side_dark' => '5475e62f_b06b_4e62_afaa_a7b8f491da26.jpg',
                                    'side_pair' => '05a25571_398d_4411_ac52_bf72d3d6e1d0.jpg',
                                    'other_angle' => 'db9fdbaf_7704_4442_a196_472b932e41ea.jpg',
                                ]
                            ],
                        ]
                    ],
                    [
                        'category_code' => 'running', 
                        'name' => 'Adidas Adizero Adios Pro 3',
                        'brand' => 'Adidas',
                        'base_price' => 250,
                        'tagline' => 'Record-Breaking Speed, Engineered.',
                        'intro' => 'Push your limits with the Adizero Adios Pro 3, designed for elite runners seeking maximum efficiency and energy return on race day.',
                        'promo_page_url' => '/promo/adidas-adios-pro-3',
                        'default_parameters' => [
                            'material' => 'Lightweight Mesh',
                            'surface' => 'Road',
                        ],
                        'template_gallery' => [
                            'hero' => 'ADIZERO_ADIOS_PRO_3_Shoes_Black_IG6439_HM1.jpg',
                            'angle1' => 'ADIZERO_ADIOS_PRO_3_Shoes_Black_IG6439_HM3_hover.jpg',
                            'pair' => 'ADIZERO_ADIOS_PRO_3_Shoes_Black_IG6439_HM8.jpg',
                        ],
                        'features' => [
                             ['icon' => '🚀', 'title' => 'Lightstrike Pro Foam', 'text' => 'Two layers of resilient Lightstrike Pro foam provide cushioning and energy return.'],
                             ['icon' => '⚡', 'title' => 'ENERGYRODS 2.0', 'text' => 'Carbon-infused ENERGYRODS deliver lightweight stiffness for a snappy, propulsive ride.'],
                             ['icon' => '👟', 'title' => 'Ultralight Upper', 'text' => 'Minimalist mesh upper offers targeted support and breathability.'],
                             ['icon' => '🔒', 'title' => 'Continental™ Rubber Outsole', 'text' => 'Provides extraordinary grip in wet and dry conditions.'],
                        ],
                        'description' => '',
                        'variants' => [
                            [
                                'color' => 'Core Black/Carbon/Solar Green',
                                'sizes' => ['EU 40', 'EU 41', 'EU 42', 'EU 43', 'EU 44'],
                                'image_map_by_color' => [
                                    'hero' => 'ADIZERO_ADIOS_PRO_3_Shoes_Black_IG6439_HM1.jpg',
                                    'angle1' => 'ADIZERO_ADIOS_PRO_3_Shoes_Black_IG6439_HM3_hover.jpg',
                                    'angle2' => 'ADIZERO_ADIOS_PRO_3_Shoes_Black_IG6439_HM4.jpg',
                                    'pair1' => 'ADIZERO_ADIOS_PRO_3_Shoes_Black_IG6439_HM5.jpg',
                                    'pair2' => 'ADIZERO_ADIOS_PRO_3_Shoes_Black_IG6439_HM6.jpg',
                                    'angle3' => 'ADIZERO_ADIOS_PRO_3_Shoes_Black_IG6439_HM7.jpg',
                                    'pair3' => 'ADIZERO_ADIOS_PRO_3_Shoes_Black_IG6439_HM8.jpg',
                                ]
                            ]
                        ]
                    ],
                    // Sneakers
                    [
                        'category_code' => 'sneakers', 
                        'name' => 'Nike Air Force 1 \'07',
                        'brand' => 'Nike',
                        'base_price' => 110,
                        'tagline' => 'The Legend Lives On.',
                        'intro' => 'Crisp leather, bold details, and the perfect amount of flash make this \'82 hoops original shine. The Nike Air Force 1 \'07 is everything you know best.',
                        'promo_page_url' => '/promo/nike-af1-07',
                        'default_parameters' => [
                            'material' => 'Leather',
                            'style' => 'Low-top',
                        ],
                        'template_gallery' => [
                            'hero_white' => "AIR+FORCE+1+'07 (6).jpg",
                            'hero_black' => "AIR+FORCE+1+'07 (7).jpg",
                            'detail_white' => "AIR+FORCE+1+'07 (4).jpg",
                            'detail_black' => "AIR+FORCE+1+'07 (14).jpg",
                        ],
                        'features' => [
                            ['icon' => '🏀', 'title' => 'Iconic Style', 'text' => 'Classic silhouette that transcends trends.'],
                            ['icon' => '💨', 'title' => 'Nike Air Cushioning', 'text' => 'Originally designed for performance hoops, provides lasting comfort.'],
                            ['icon' => '👟', 'title' => 'Durable Construction', 'text' => 'Stitched overlays and premium leather ensure durability.'],
                            ['icon' => '⚪', 'title' => 'Clean Look', 'text' => 'Versatile design pairs well with almost anything.'],
                        ],
                        'description' => '',
                        'variants' => [
                            [
                                'color' => 'White',
                                'sizes' => ['EU 38', 'EU 39', 'EU 40', 'EU 41', 'EU 42', 'EU 43', 'EU 44'],
                                'image_map_by_color' => [
                                    'top' => "AIR+FORCE+1+'07 (1).jpg",
                                    'pair' => "AIR+FORCE+1+'07 (2).jpg",
                                    'back_pair' => "AIR+FORCE+1+'07 (3).jpg",
                                    'detail_toe' => "AIR+FORCE+1+'07 (4).jpg",
                                    'detail_heel' => "AIR+FORCE+1+'07 (5).jpg",
                                    'side' => "AIR+FORCE+1+'07 (6).jpg",
                                ]
                            ],
                            [
                                'color' => 'Black',
                                'sizes' => ['EU 40', 'EU 41', 'EU 42', 'EU 43'],
                                'image_map_by_color' => [
                                    'side' => "AIR+FORCE+1+'07 (7).jpg",
                                    'on_foot_1' => "AIR+FORCE+1+'07 (8).jpg",
                                    'on_foot_2' => "AIR+FORCE+1+'07 (8) (1).jpg",
                                    'sole' => "AIR+FORCE+1+'07 (9).jpg",
                                    'side_alt' => "AIR+FORCE+1+'07 (10).jpg",
                                    'top' => "AIR+FORCE+1+'07 (11).jpg",
                                    'pair' => "AIR+FORCE+1+'07 (12).jpg",
                                    'back_pair' => "AIR+FORCE+1+'07 (13).jpg",
                                    'detail_heel' => "AIR+FORCE+1+'07 (14).jpg",
                                ]
                            ]
                        ]
                    ],
                    [
                        'category_code' => 'sneakers', 
                        'name' => 'Adidas Superstar',
                        'brand' => 'Adidas',
                        'base_price' => 100,
                        'tagline' => 'The Original Shell Toe.',
                        'intro' => 'Born on the basketball court, adopted by hip-hop royalty, and now a streetwear staple. The adidas Superstar shoe is an icon recognized globally.',
                        'default_parameters' => [
                            'material' => 'Leather',
                            'style' => 'Low-top',
                        ],
                        'promo_page_url' => '/promo/adidas-superstar',
                        'template_gallery' => [
                            'hero_green' => 'SUPERSTAR_II_Green_JI3076_01_00_standard.jpg',
                            'hero_blue' => 'SUPERSTAR_II_Blue_JI0145_01_00_standard.jpg',
                            'sole_green' => 'SUPERSTAR_II_Green_JI3076_03_standard.jpg',
                            'sole_blue' => 'SUPERSTAR_II_Blue_JI0145_03_standard.jpg',
                        ],
                        'features' => [
                            ['icon' => '⭐', 'title' => 'Classic Shell Toe', 'text' => 'The instantly recognizable rubber shell toe for style and protection.'],
                            ['icon' => '👟', 'title' => 'Leather Upper', 'text' => 'Smooth leather upper for a premium look and feel.'],
                            ['icon' => '📶', 'title' => 'Herringbone Outsole', 'text' => 'Provides reliable traction.'],
                            ['icon' => '🎶', 'title' => 'Cultural Icon', 'text' => 'Worn by legends across sports, music, and fashion.'],
                        ],
                        'description' => '',
                        'is_featured' => true, // <-- 添加 is_featured 标记
                        'variants' => [
                            [
                                'color' => 'White/Green',
                                'sizes' => ['EU 39', 'EU 40', 'EU 41', 'EU 42', 'EU 43'],
                                'image_map_by_color' => [
                                    'side' => 'SUPERSTAR_II_Green_JI3076_01_00_standard.jpg',
                                    'top_hover' => 'SUPERSTAR_II_Green_JI3076_02_standard_hover.jpg',
                                    'sole' => 'SUPERSTAR_II_Green_JI3076_03_standard.jpg',
                                    'side_angle' => 'SUPERSTAR_II_Green_JI3076_04_standard.jpg',
                                ]
                            ],
                            [
                                'color' => 'White/Blue',
                                'sizes' => ['EU 40', 'EU 41', 'EU 42', 'EU 43', 'EU 44'],
                                'image_map_by_color' => [
                                    'side' => 'SUPERSTAR_II_Blue_JI0145_01_00_standard.jpg',
                                    'top_hover' => 'SUPERSTAR_II_Blue_JI0145_02_standard_hover.jpg',
                                    'sole' => 'SUPERSTAR_II_Blue_JI0145_03_standard.jpg',
                                    'side_angle' => 'SUPERSTAR_II_Blue_JI0145_04_standard.jpg',
                                ]
                            ]
                        ]
                    ],
                    // Sandals (Simplified Features)
                    [
                        'category_code' => 'sandals', 
                        'name' => 'Havaianas Top Flip-flop',
                         'brand' => 'Havaianas',
                         'base_price' => 20,
                         'tagline' => 'The Original. The Best.',
                         'intro' => 'Simple, durable, and comfortable. The Havaianas Top is the essential flip-flop for beach days and casual wear.',
                         'default_parameters' => [
                            'material' => 'Rubber',
                            'sandal_type' => 'Flip-flop',
                         ],
                         'promo_page_url' => '/promo/havaianas-top',
                         'template_gallery' => [
                             'hero_white' => '4000029_0001_A__2.jpg',
                             'hero_blue' => '4000029_2711_A__2_2fb28641-4cb2-4f66-9dfd-591b8d58a423.jpg',
                             'pair_white' => '4000029_0001_C__2.jpg',
                             'pair_blue' => '4000029_2711_C__2_6c543ef7-1cf2-47ca-a238-3370627f9640.jpg',
                         ],
                        'features' => [
                             ['icon' => '☀️', 'title' => 'Classic Comfort', 'text' => 'Signature textured rice pattern footbed.'],
                             ['icon' => '💧', 'title' => 'Water-Resistant', 'text' => 'Perfect for the pool or beach.'],
                             ['icon' => '🌈', 'title' => 'Vibrant Colors', 'text' => 'Available in a wide range of colors.'],
                         ],
                        'description' => '', // Placeholder
                         'variants' => [
                            [
                                'color' => 'White',
                                'sizes' => ['EU 38', 'EU 40', 'EU 42', 'EU 44'],
                                'image_map_by_color' => [
                                    'side_angle' => '4000029_0001_A__2.jpg',
                                    'side' => '4000029_0001_B__2.jpg',
                                    'top_pair' => '4000029_0001_C__2.jpg',
                                    'pair_angle' => '4000029_0001_D__2.jpg',
                                ]
                            ],
                            [
                                'color' => 'Marine Blue',
                                'sizes' => ['EU 39', 'EU 41', 'EU 43'],
                                'image_map_by_color' => [
                                    'side_angle' => '4000029_2711_A__2_2fb28641-4cb2-4f66-9dfd-591b8d58a423.jpg',
                                    'side' => '4000029_2711_B__2_fefadf09-3691-4685-aa4d-1995f00e9a4b.jpg',
                                    'top_pair' => '4000029_2711_C__2_6c543ef7-1cf2-47ca-a238-3370627f9640.jpg',
                                    'pair_angle' => '4000029_2711_D__2_2cc796d0-f207-49b1-bd85-38f9a948fb89.jpg',
                                ]
                            ]
                        ]
                    ],
                    [
                        'category_code' => 'sandals', 
                        'name' => 'Crocs Classic Clog',
                        'brand' => 'Crocs',
                        'base_price' => 50,
                        'tagline' => 'Comfort That\'s All Your Own.', // Escaped apostrophe
                        'intro' => 'Incredibly light and fun to wear, the Crocs Classic Clog offers lightweight Iconic Crocs Comfort™, a color for every personality, and an ongoing invitation to be comfortable in your own shoes.',
                        'default_parameters' => [
                            'material' => 'Croslite',
                            'sandal_type' => 'Clog',
                        ],
                        'promo_page_url' => '/promo/crocs-classic-clog', // Added promo URL
                        'template_gallery' => [ // Added gallery
                            'hero_yellow' => '10001_78Z_1.jpg',
                            'hero_blue' => '10001_4WK_1.jpg',
                            'pair_yellow' => '10001_78Z_2.jpg',
                            'pair_blue' => '10001_4WK_2.jpg',
                        ],
                        'features' => [
                             ['icon' => '🐊', 'title' => 'Iconic Comfort', 'text' => 'Lightweight, flexible, 360-degree comfort.'],
                             ['icon' => '💨', 'title' => 'Ventilation Ports', 'text' => 'Add breathability and help shed water.'],
                             ['icon' => '🧼', 'title' => 'Easy to Clean', 'text' => 'Quick to dry.'],
                             ['icon' => '🎨', 'title' => 'Customizable', 'text' => 'Pivoting heel straps and Jibbitz™ charm compatibility.'],
                        ],
                        'description' => '', // Placeholder
                        'variants' => [
                            [ // Replaced with Butter variant
                                'color' => 'Butter', // 10001_78Z series
                                'sizes' => ['EU 38', 'EU 39', 'EU 40', 'EU 41'],
                                'image_map_by_color' => [
                                    'side' => '10001_78Z_1.jpg',
                                    'pair' => '10001_78Z_2.jpg',
                                    'top_pair' => '10001_78Z_3.jpg',
                                    'sole' => '10001_78Z_4.jpg',
                                    'angle' => '10001_78Z_5.jpg',
                                    'back' => '10001_78Z_6.jpg',
                                ]
                            ],
                            [ // Replaced with Atmosphere variant
                                'color' => 'Atmosphere', // 10001_4WK series
                                'sizes' => ['EU 39', 'EU 40', 'EU 41', 'EU 42', 'EU 43'],
                                'image_map_by_color' => [
                                    'side' => '10001_4WK_1.jpg',
                                    'pair' => '10001_4WK_2.jpg',
                                    'top_pair' => '10001_4WK_3.jpg',
                                    'sole' => '10001_4WK_4.jpg',
                                    'angle' => '10001_4WK_5.jpg',
                                    'back' => '10001_4WK_6.jpg',
                                ]
                            ]
                        ]
                    ],
                    // Boots (Simplified Features)
                    [
                        'category_code' => 'boots', 
                        'name' => 'Blundstone Classic 550 Chelsea Boot',
                        'brand' => 'Blundstone',
                        'base_price' => 210,
                        'tagline' => 'Everywhere Life Takes Me.',
                        'intro' => 'Comfortable right out of the box, these authentic leather boots go with everything – jeans, skirts, pants, and shorts.',
                        'default_parameters' => [
                            'material' => 'Leather',
                            'shaft_height' => 'Ankle',
                            'heel_type' => 'Flat',
                        ],
                         'promo_page_url' => '/promo/blundstone-550', // Added Promo URL
                         'template_gallery' => [ // Added template gallery
                            'hero_black' => '587_1-min_1.jpg',
                            'hero_brown' => '585_1_5.jpg',
                            'detail_black' => '587_4-min_1.jpg',
                            'on_foot_brown' => '585_om2m.jpg',
                         ],
                         'features' => [
                             ['icon' => '🥾', 'title' => 'Premium Leather', 'text' => 'Durable and water-resistant upper.'],
                             ['icon' => '☁️', 'title' => 'Shock Absorption', 'text' => 'XRD® Technology in the heel for comfort.'],
                             ['icon' => '🇦🇺', 'title' => 'Iconic Design', 'text' => 'Classic Chelsea boot style with pull tabs.'],
                         ],
                        'description' => '', // Placeholder
                        'variants' => [
                            [ // Updated Black Leather
                                'color' => 'Black Leather', // 587 series
                                'sizes' => ['EU 40', 'EU 41', 'EU 42', 'EU 43'],
                                'image_map_by_color' => [
                                    'pair_side' => '587_1-min_1.jpg',
                                    'pair_back' => '587_2-min_1.jpg',
                                    'detail' => '587_4-min_1.jpg',
                                    'on_foot' => '587_m.jpg',
                                    // Add more mappings if needed from 587 files
                                ]
                            ],
                            [ // Updated Stout Brown Leather
                                'color' => 'Stout Brown Leather', // 585 series
                                'sizes' => ['EU 41', 'EU 42', 'EU 43', 'EU 44'], // Added sizes
                                'image_map_by_color' => [
                                    'single' => '585_1_5.jpg',
                                    'pair_back' => '585_2_3.jpg',
                                    'pair_side' => '585_4_1.jpg',
                                    'detail' => '585_5_1.jpg',
                                    'on_foot' => '585_om2m.jpg',
                                ]
                            ]
                        ]
                    ],
                     [
                        'category_code' => 'boots', 
                        'name' => 'Dr. Martens 1460 Smooth Leather Boot',
                        'brand' => 'Dr. Martens',
                        'base_price' => 170,
                         'tagline' => 'Built To Last.',
                         'intro' => 'The original Dr. Martens boot. Instantly recognizable, and built with decades of rebellious self-expression woven into its DNA.',
                         'promo_page_url' => '/promo/dr-martens-1460', // Added React App Promo URL
                         'default_parameters' => [
                            'material' => 'Smooth Leather',
                            'shaft_height' => 'Ankle',
                            'heel_type' => 'Flat',
                        ],
                        'template_gallery' => [
                            'hero_black' => '11822006.jpg',
                            'hero_white' => '11822100.jpg',
                            'hero_red' => '11822600.jpg',
                            'detail_stitch_black' => '11822006 (7).jpg',
                        ],
                         'features' => [
                             ['icon' => '🟡', 'title' => 'Signature Style', 'text' => 'Yellow welt stitching, grooved sides, heel-loop.'],
                             ['icon' => '💪', 'title' => 'Durable Leather', 'text' => 'Smooth leather is famously stiff at first, molds to your feet.'],
                             ['icon' => '💨', 'title' => 'AirWair™ Sole', 'text' => 'Air-cushioned, oil and fat resistant, good abrasion resistance.'],
                         ],
                        'description' => '', // Placeholder
                        'is_featured' => true, // <-- 添加 is_featured 标记
                         'variants' => [
                            [
                                'color' => 'Black Smooth',
                                'sizes' => ['EU 39', 'EU 40', 'EU 41', 'EU 42', 'EU 43', 'EU 44'],
                                'image_map_by_color' => [
                                    'side' => '11822006.jpg',
                                    'detail_laces' => '11822006 (1).jpg',
                                    'on_foot_1' => '11822006 (2).jpg',
                                    'on_foot_2' => '11822006 (3).jpg',
                                    'pair' => '11822006 (4).jpg',
                                    'top' => '11822006 (5).jpg',
                                    'sole' => '11822006 (6).jpg',
                                    'detail_heel_stitch' => '11822006 (7).jpg',
                                ]
                            ],
                            [
                                'color' => 'White Smooth',
                                'sizes' => ['EU 38', 'EU 39', 'EU 40', 'EU 41', 'EU 42'],
                                'image_map_by_color' => [
                                    'side' => '11822100.jpg',
                                    'on_foot_1' => '11822100 (1).jpg',
                                    'detail_heel' => '11822100 (2).jpg',
                                    'pair_1' => '11822100 (3).jpg',
                                    'pair_2' => '11822100 (4).jpg',
                                    'side_alt' => '11822100 (5).jpg',
                                    'detail_laces' => '11822100 (6).jpg',
                                    'on_foot_2' => '11822100 (7).jpg',
                                ]
                            ],
                            [
                                'color' => 'Cherry Red Smooth',
                                'sizes' => ['EU 40', 'EU 41', 'EU 42', 'EU 43'],
                                'image_map_by_color' => [
                                    'side' => '11822600.jpg',
                                    'on_foot_1' => '11822600 (1).jpg',
                                    'detail_heel_stitch' => '11822600 (2).jpg',
                                    'on_foot_2' => '11822600 (3).jpg',
                                    'pair_1' => '11822600 (4).jpg',
                                    'pair_2' => '11822600 (5).jpg',
                                ]
                            ]
                         ]
                    ],
                    // Formal Shoes (Simplified Features)
                    [
                        'category_code' => 'formal', 
                        'name' => 'Allen Edmonds Strand Oxford',
                        'brand' => 'Allen Edmonds',
                        'base_price' => 395,
                         'tagline' => 'An American Original.',
                         'intro' => 'A timeless cap-toe balmoral oxford with intricate broguing details. Perfect for professional settings or elevating casual wear.',
                         'default_parameters' => [
                            'material' => 'Calfskin Leather',
                            'toe_shape' => 'Round',
                            'closure' => 'Lace-up',
                        ],
                         'promo_page_url' => '/promo/allen-edmonds-strand', // Added missing Promo URL
                         'template_gallery' => [ // Added missing template gallery
                            'hero_black' => 'ec4001553_single_feed1000.jpg',
                            'hero_walnut' => 'ec4000391_single_feed1000.jpg',
                            'detail_black' => 'ec4001553_detail_feed1000.jpg',
                            'sole_walnut' => 'ec4000391_bottom_feed1000.jpg',
                         ],
                         'features' => [
                             ['icon' => '🇺🇸', 'title' => 'Made in USA', 'text' => 'Handcrafted quality.'],
                             ['icon' => '🧵', 'title' => 'Goodyear Welt', 'text' => 'Allows for resoling, extending the life of the shoe.'],
                             ['icon' => '👞', 'title' => 'Premium Calfskin', 'text' => 'Rich leather upper.'],
                             ['icon' => '✨', 'title' => 'Timeless Style', 'text' => 'Classic cap-toe oxford design.'],
                         ],
                        'description' => '', // Placeholder
                         'variants' => [
                            [
                                'color' => 'Black Custom Calf', // ec4001553 series
                                'sizes' => ['EU 40', 'EU 41', 'EU 42', 'EU 43'],
                                'image_map_by_color' => [
                                    'single' => 'ec4001553_single_feed1000.jpg',
                                    'right' => 'ec4001553_right_feed1000.jpg',
                                    'detail' => 'ec4001553_detail_feed1000.jpg',
                                    'bottom' => 'ec4001553_bottom_feed1000.jpg',
                                ]
                            ],
                            [
                                'color' => 'Walnut Calf', // ec4000391 series
                                'sizes' => ['EU 41', 'EU 42'],
                                'image_map_by_color' => [
                                    'single' => 'ec4000391_single_feed1000.jpg',
                                    'right' => 'ec4000391_right_feed1000.jpg',
                                    'detail' => 'ec4000391_detail_feed1000.jpg',
                                    'bottom' => 'ec4000391_bottom_feed1000.jpg',
                                ]
                            ]
                        ]
                    ],
                    [
                        'category_code' => 'formal', 
                        'name' => 'G.H. Bass Weejuns Larson Penny Loafer',
                        'brand' => 'G.H. Bass & Co.',
                        'base_price' => 175,
                         'tagline' => 'The Original Penny Loafer.',
                         'intro' => 'The quintessential penny loafer. Hand-sewn moccasin construction and polished leather create a timeless look for any occasion.',
                         'default_parameters' => [
                            'material' => 'Polished Leather',
                            'toe_shape' => 'Round',
                            'closure' => 'Loafer',
                        ],
                         'promo_page_url' => '/promo/gh-bass-larson', // Added promo URL
                         'template_gallery' => [ // Added gallery
                            'hero_darkbrown' => 'GHBBA00324_15_BAZ1W002_DARKBROWN.jpg',
                            'hero_whiskey' => 'GHBBA00823_65_BAZ1W002_WSK_HERO_3f0908f2-9523-4a0e-a347-00aa577aea0f.jpg',
                            'detail_darkbrown' => 'GHBBA00324_15_BAZ1W002_DARKBROWN_v6.jpg',
                            'detail_whiskey' => 'GHBBA00823_65_BAZ1W002_WSK_v5_1fa0697b-925d-4b4c-9698-02ae953ed279.jpg',
                         ],
                         'features' => [
                             ['icon' => '🪙', 'title' => 'Classic Penny Keeper', 'text' => 'Iconic strap detail.'],
                             ['icon' => '🧵', 'title' => 'Handsewn Construction', 'text' => 'Traditional moccasin stitching.'],
                             ['icon' => '✨', 'title' => 'Polished Leather', 'text' => 'Provides a refined look.'],
                         ],
                        'description' => '', // Placeholder
                        'variants' => [
                            [ // Replaced with Dark Brown
                                'color' => 'Dark Brown Polished Leather', // GHBBA00324 series
                                'sizes' => ['EU 40', 'EU 41', 'EU 42', 'EU 43'],
                                'image_map_by_color' => [
                                    'pair' => 'GHBBA00324_15_BAZ1W002_DARKBROWN.jpg',
                                    'side' => 'GHBBA00324_15_BAZ1W002_DARKBROWN_v2.jpg',
                                    'top' => 'GHBBA00324_15_BAZ1W002_DARKBROWN_v3.jpg',
                                    'back_pair' => 'GHBBA00324_15_BAZ1W002_DARKBROWN_v4.jpg',
                                    'sole_pair' => 'GHBBA00324_15_BAZ1W002_DARKBROWN_v5.jpg',
                                    'detail' => 'GHBBA00324_15_BAZ1W002_DARKBROWN_v6.jpg',
                                ]
                            ],
                            [ // Replaced with Whiskey
                                'color' => 'Whiskey Polished Leather', // GHBBA00823 series
                                'sizes' => ['EU 41', 'EU 42', 'EU 43', 'EU 44'],
                                'image_map_by_color' => [
                                    'hero' => 'GHBBA00823_65_BAZ1W002_WSK_HERO_3f0908f2-9523-4a0e-a347-00aa577aea0f.jpg',
                                    'side' => 'GHBBA00823_65_BAZ1W002_WSK_v2_1943b26e-8732-44db-99de-d4dc8f9db392.jpg',
                                    'back_pair' => 'GHBBA00823_65_BAZ1W002_WSK_v3_b1d97e32-a71e-42e6-a863-4d37ea57182e.jpg',
                                    'sole_pair' => 'GHBBA00823_65_BAZ1W002_WSK_v4_7d774dc8-6c81-4b94-936d-235a73b7f5c5.jpg',
                                    'detail' => 'GHBBA00823_65_BAZ1W002_WSK_v5_1fa0697b-925d-4b4c-9698-02ae953ed279.jpg',
                                    'on_foot' => 'GHBBA00823_65_BAZ1W002_WSK_on_b6f81b04-ab37-4e06-ab54-0cd61b4ff8f5.jpg',
                                ]
                            ]
                        ]
                    ],
                    // Added Templates (Simplified Features)
                    [
                        'category_code' => 'running', 
                        'name' => 'ASICS GEL-Kayano 30',
                        'brand' => 'ASICS',
                        'base_price' => 160,
                         'tagline' => 'Adaptive Stability. Premium Comfort.', // Updated tagline
                         'intro' => 'Experience adaptive stability and premium comfort like never before. The GEL-Kayano 30 features the innovative 4D GUIDANCE SYSTEM™ for a supportive and smooth run.',
                         'default_parameters' => [
                            'material' => 'Engineered Knit', // Updated material
                            'surface' => 'Road',
                        ],
                         'promo_page_url' => '/promo/asics-gel-kayano-30', // Added promo URL
                         'template_gallery' => [ // Added gallery based on filenames
                            'hero_blue' => '1011B548_404_SR_LT_GLB.jpg', // Blue Left Side
                            'hero_orange' => '1011B548_601_SR_LT_GLB.jpg', // Orange Left Side
                            'sole_blue' => '1011B548_404_SB_BT_GLB.jpg', // Blue Sole
                            'sole_orange' => '1011B548_601_SB_BT_GLB.jpg', // Orange Sole
                         ],
                         'features' => [
                            ['icon' => '🛡️', 'title' => '4D GUIDANCE SYSTEM™', 'text' => 'Creates adaptive stability for a balanced stride.'],
                            ['icon' => '☁️', 'title' => 'PureGEL™ Technology', 'text' => 'Provides lightweight cushioning and softer landings.'],
                            ['icon' => '💨', 'title' => 'FF BLAST™ PLUS ECO', 'text' => 'Bio-based cushioning delivers cloud-like comfort.'],
                            ['icon' => '👟', 'title' => 'Engineered Knit Upper', 'text' => 'Improves breathability and provides a comfortable foothold.'], // Updated feature
                         ],
                        'description' => '', // Placeholder, will be generated
                        'is_featured' => true, // <-- 添加 is_featured 标记
                        'variants' => [
                            [ // Updated Blue color variant with image map
                                'color' => 'Island Blue/Sun Peach', // Updated color name based on visual
                                'sizes' => ['EU 39', 'EU 40', 'EU 41', 'EU 42', 'EU 43'],
                                'image_map_by_color' => [
                                    'side_left' => '1011B548_404_SR_LT_GLB.jpg',
                                    'side_right' => '1011B548_404_SR_RT_GLB.jpg',
                                    'pair_front' => '1011B548_404_SB_FR_GLB.jpg',
                                    'pair_fly' => '1011B548_404_SB_FL_GLB.jpg',
                                    'back' => '1011B548_404_SB_BK_GLB.jpg',
                                    'top_insole' => '1011B548_404_SB_TP_GLB.jpg',
                                    'bottom_sole' => '1011B548_404_SB_BT_GLB.jpg',
                                ]
                            ],
                            [ // Updated Orange/Red color variant with image map
                                'color' => 'Spice Latte/Black', // Updated color name based on visual
                                'sizes' => ['EU 40', 'EU 41', 'EU 42'],
                                'image_map_by_color' => [
                                    'side_left' => '1011B548_601_SR_LT_GLB.jpg',
                                    'side_right' => '1011B548_601_SR_RT_GLB.jpg',
                                    'pair_front' => '1011B548_601_SB_FR_GLB.jpg',
                                    'pair_fly' => '1011B548_601_SB_FL_GLB.jpg',
                                    'back' => '1011B548_601_SB_BK_GLB.jpg',
                                    'top_insole' => '1011B548_601_SB_TP_GLB.jpg',
                                    'bottom_sole' => '1011B548_601_SB_BT_GLB.jpg',
                                ]
                            ],
                        ]
                    ],
                     [
                        'category_code' => 'sneakers', 
                        'name' => 'Converse Chuck Taylor All Star Classic',
                        'brand' => 'Converse',
                        'base_price' => 65,
                         'tagline' => 'Forever Classic.',
                         'intro' => 'The undisputed icon. The Chuck Taylor All Star Classic features a timeless silhouette recognized around the world.',
                         'default_parameters' => [
                             'material' => 'Canvas',
                             'style' => 'High-top',
                         ],
                         'promo_page_url' => '/promo/converse-chuck-taylor',
                         'template_gallery' => [
                             'hero_black' => '0885-CON568497C000009-1.jpg',
                             'hero_white' => '0885-CON568498C000009-1.jpg',
                             'sole_black' => '0885-CON568497C000009-6.jpg',
                             'sole_white' => '0885-CON568498C000009-6.jpg',
                         ],
                         'features' => [
                             ['icon' => '⭐', 'title' => 'Iconic Silhouette', 'text' => 'High-top or low-top canvas design.'],
                             ['icon' => '👟', 'title' => 'Canvas Upper', 'text' => 'Lightweight and durable.'],
                             ['icon' => '👁️‍🗨️', 'title' => 'Medial Eyelets', 'text' => 'Enhance airflow.'],
                             ['icon' => '®️', 'title' => 'All Star Patch', 'text' => 'Classic ankle patch (high-top).'],
                         ],
                         'description' => '',
                         'variants' => [
                             [
                                 'color' => 'Black',
                                 'sizes' => ['EU 38', 'EU 39', 'EU 40', 'EU 41', 'EU 42', 'EU 43'],
                                 'image_map_by_color' => [
                                     'side' => '0885-CON568497C000009-1.jpg',
                                     'side_angle' => '0885-CON568497C000009-2.jpg',
                                     'pair' => '0885-CON568497C000009-3.jpg',
                                     'top' => '0885-CON568497C000009-4.jpg',
                                     'back' => '0885-CON568497C000009-5.jpg',
                                     'sole' => '0885-CON568497C000009-6.jpg',
                                 ]
                             ],
                             [
                                 'color' => 'Optical White',
                                 'sizes' => ['EU 38', 'EU 39', 'EU 40', 'EU 41', 'EU 42'],
                                 'image_map_by_color' => [
                                     'side' => '0885-CON568498C000009-1.jpg',
                                     'side_angle' => '0885-CON568498C000009-2.jpg',
                                     'pair' => '0885-CON568498C000009-3.jpg',
                                     'top' => '0885-CON568498C000009-4.jpg',
                                     'back' => '0885-CON568498C000009-5.jpg',
                                     'sole' => '0885-CON568498C000009-6.jpg',
                                 ]
                             ]
                         ]
                     ],
                ];

                // --- Start: Pre-process Product Template Categorization --- 
                // echo "[INFO] Pre-processing product template categorization..." . PHP_EOL;
                $saleTemplates = ['Adidas Adizero Adios Pro 3', 'Nike Air Force 1 \'07', 'Nike Air Zoom Pegasus 41 (Sail/Cream)'];
                $newArrivalTemplates = ['Nike Air Zoom Pegasus 41 (Volt)', 'ASICS GEL-Kayano 30', 'Blundstone Classic 550 Chelsea Boot'];
                $featuredTemplates = ['Adidas Superstar', 'Dr. Martens 1460 Smooth Leather Boot', 'Converse Chuck Taylor All Star Classic', 'Allen Edmonds Strand Oxford'];

                foreach ($productTemplatesData as $key => $templateData) {
                    $templateName = $templateData['name'];
                    $isSale = false;
                    $isNew = false;
                    $isFeatured = false;

                    // 1. Check Sale
                    if (in_array($templateName, $saleTemplates)) {
                        $isSale = true;
                    }
                    // 2. Check New Arrival (only if not Sale)
                    elseif (in_array($templateName, $newArrivalTemplates)) {
                        $isNew = true;
                    }
                    // 3. Check Featured (only if not Sale or New)
                    elseif (in_array($templateName, $featuredTemplates)) {
                        $isFeatured = true;
                    }

                    // Assign back to the array, ensuring others are false if not explicitly set
                    $productTemplatesData[$key]['is_sale'] = $isSale;
                    $productTemplatesData[$key]['is_new_arrival'] = $isNew;
                    $productTemplatesData[$key]['is_featured'] = $isFeatured;

                     // Log the categorization for verification
                     // echo "[DEBUG] Categorizing '{$templateName}': Sale={$isSale}, New={$isNew}, Featured={$isFeatured}" . PHP_EOL;
                }
                // echo "[INFO] Finished pre-processing categorization." . PHP_EOL;
                // --- End: Pre-process Product Template Categorization ---

                $allCreatedProductIds = collect(); // To store all created product SKU IDs

                // Pre-fetch all category parameters, keyed by category ID
                $allCategoryParams = CategoryParameter::orderBy('sort_order')->get()->groupBy('category_id');

                // Generate descriptions before the loop (using original data before categorization flags were added)
                // Note: Descriptions are generated based on the *content* fields, not the boolean flags.
                $descriptions = [];
                foreach ($productTemplatesData as $key => $templateData) {
                    $descriptions[$key] = $this->generateRichHtmlDescription($templateData); 
                }

                $createdTemplates = collect(); // Store created templates {name => id}

                // Now create templates using the processed data
                foreach ($productTemplatesData as $key => $templateData) {
                    $category = $categories->get($templateData['category_code']);
                    if (!$category) {
                        echo "[WARN] Category not found for template, skipping - " . json_encode(['category_code' => $templateData['category_code'], 'template_name' => $templateData['name']]) . PHP_EOL;
                        continue;
                    }

                    // Get parameters for this specific category
                    $categoryParamsDefinition = $allCategoryParams->get($category->id, collect());

                    $parameterOptions = [
                        'Color' => [],
                        'Size' => [],
                    ];
                    $allColors = [];
                    $allSizes = [];
                    if (isset($templateData['variants']) && is_array($templateData['variants'])) {
                        foreach ($templateData['variants'] as $variant) {
                            if (isset($variant['color'])) { $allColors[] = $variant['color']; }
                            if (isset($variant['sizes']) && is_array($variant['sizes'])) {
                                $allSizes = array_merge($allSizes, $variant['sizes']);
                            }
                        }
                    }
                    $parameterOptions['Color'] = array_values(array_unique($allColors));
                    $parameterOptions['Size'] = array_values(array_unique($allSizes));
                    sort($parameterOptions['Size']);

                    $templateGalleryMap = $templateData['template_gallery'] ?? [];
                    $variantsWithImageMaps = collect($templateData['variants'] ?? [])->mapWithKeys(function ($variant) {
                        return [$variant['color'] => $variant['image_map_by_color'] ?? []];
                    });

                    // Create Product Template using processed flags
                    $template = ProductTemplate::updateOrCreate(
                        ['name' => $templateData['name']],
                        [
                            'category_id' => $category->id,
                            'description' => $descriptions[$key], // Use pre-generated description
                            'parameters' => $parameterOptions,
                            'images' => null,
                            'store_id' => null,
                            'is_active' => true,
                            'is_featured' => $templateData['is_featured'], // Use processed flag
                            'is_new_arrival' => $templateData['is_new_arrival'], // Use processed flag
                            'is_sale' => $templateData['is_sale'], // Use processed flag
                            'promo_page_url' => $templateData['promo_page_url'] ?? null,
                            'template_gallery' => $templateData['template_gallery'] ?? [],
                        ]
                    );
                    $createdTemplates->put($template->name, $template->id); // Store name and ID
                    // echo "[INFO] Template ensured - " . json_encode(['id' => $template->id, 'name' => $template->name, 'sale' => $template->is_sale, 'new' => $template->is_new_arrival, 'featured' => $template->is_featured]) . PHP_EOL;

                    // --- Process Template Gallery Images (Manual DB Insert) ---
                    $this->processAndAttachImagesManual(
                        $template,
                        $templateGalleryMap,
                        'product_templates/' . $template->id,
                        'template_gallery',
                        'usage' // Custom property key for usage (e.g., 'hero_volt')
                    );
                    // --- End Process Template Gallery Images (Manual DB Insert) ---

                    // Create Product SKUs (Variants)
                    if (isset($templateData['variants']) && is_array($templateData['variants'])) { // Check added here too
                         // ... (Existing Variant/Product SKU creation logic - INCLUDING discount logic based on is_sale) ...
                          foreach ($templateData['variants'] as $variant) {
                            $color = $variant['color'];
                            $imageMapForColor = $variantsWithImageMaps->get($color, []); // Get image map for this specific color

                            foreach ($variant['sizes'] as $size) {
                                $brand = $templateData['brand'] ?? 'Default Brand'; 
                                $basePrice = $templateData['base_price'] ?? 100; 
                                
                                $skuCode = strtoupper(Str::slug($brand . '-' . $templateData['name'] . '-' . $color . '-' . $size));
                                $productName = $templateData['name'] . ' - ' . $color . ' - ' . $size;
                                $costPrice = $basePrice * 0.6; 
                                $sellingPrice = $basePrice; 

                                // --- Determine if discount applies based on template's is_sale flag --- 
                                $discountPercentage = 0.00;
                                $productIsSale = $template->is_sale; // Use the flag from the created template
                                if ($productIsSale) {
                                    // Apply a standard discount for sale items, e.g., 15%
                                    $discountPercentage = 15.00; 
                                } else {
                                     // Check specific templates for non-sale discounts if needed (legacy)
                                     // Example: apply 5% discount only to Pegasus 41 even if not in 'Sale' section
                                      if (str_contains($templateData['name'], 'Nike Air Zoom Pegasus 41')) {
                                          // $discountPercentage = 5.00; 
                                          // $productIsSale = true; // Mark as sale if giving discount?
                                          // Decide if this legacy logic is still needed
                                      }
                                }
                                // --- End Determine if discount applies ---

                                $productParams = [];
                                $defaultParamsFromTemplate = $templateData['default_parameters'] ?? [];

                                foreach ($categoryParamsDefinition as $paramDef) {
                                    $paramCode = $paramDef->code;
                                    $paramValue = null;

                                    if ($paramCode === 'color') {
                                        $paramValue = $color; // Use variant specific color
                                    } elseif ($paramCode === 'size') {
                                        $paramValue = $size; // Use variant specific size
                                    } elseif (isset($defaultParamsFromTemplate[$paramCode])) {
                                        $paramValue = $defaultParamsFromTemplate[$paramCode]; // Use default from template data
                                    }

                                    // Only include the parameter if it has a value OR if it's optional (we decided to only include if value exists)
                                    if ($paramValue !== null) {
                                        $productParams[$paramCode] = $paramValue;
                                    } elseif ($paramDef->is_required) {
                                        // Required parameter has no value from variant or default - Log a warning
                                        echo "[WARN] Required parameter '{$paramCode}' missing value for product SKU: {$skuCode} (Template: {$templateData['name']})" . PHP_EOL;
                                    }
                                }

                                $product = Product::updateOrCreate(
                                    ['sku' => $skuCode],
                                    [
                                        'name' => $productName,
                                        'category_id' => $category->id,
                                        'template_id' => $template->id,
                                        'brand' => $brand,
                                        'cost_price' => $costPrice,
                                        'selling_price' => $sellingPrice,
                                        'description' => "Variant: {$color} / {$size}", 
                                        'parameters' => $productParams, 
                                        'is_active' => true,
                                        'inventory_count' => 0,
                                        'min_stock' => 10,
                                        'images' => null, 
                                        'discount_percentage' => $discountPercentage, // Use calculated discount
                                        'is_sale' => $productIsSale, // Use flag from template
                                        'discount_start_date' => null, 
                                        'discount_end_date' => null,   
                                    ]
                                );
                                $allCreatedProductIds->push($product->id);

                                // --- Link Product to Template with Parameter Group ---
                                $variantDefiningParamCodes = ['color', 'size']; // Codes defining a variant
                                $parameterGroupParts = [];

                                // Find the CategoryParameter models for 'color' and 'size' for this category
                                // $categoryParamsDefinition is already available from the outer loop
                                $paramDefinitions = $categoryParamsDefinition->whereIn('code', $variantDefiningParamCodes)->keyBy('code');

                                foreach ($variantDefiningParamCodes as $code) {
                                    // Use the $productParams array which holds the actual values for this SKU
                                    if (isset($productParams[$code]) && $paramDefinitions->has($code)) {
                                        $paramName = $paramDefinitions->get($code)->name; // Get the capitalized Name (e.g., 'Color')
                                        $paramValue = $productParams[$code];
                                        $parameterGroupParts[] = $paramName . '=' . $paramValue; // Use capitalized Name
                                    } else {
                                         // Optional: Log a warning if a defining param is missing from productParams or definition
                                        // echo "[WARN] Defining parameter '{$code}' or its definition not found for SKU {$product->sku} parameter group generation." . PHP_EOL;
                                    }
                                }

                                // Creates string like "Color=Black;Size=EU 41"
                                $parameterGroupString = implode(';', $parameterGroupParts);


                                $template->linkedProducts()->syncWithoutDetaching([
                                    $product->id => ['parameter_group' => $parameterGroupString]
                                ]);
                                // echo "[INFO] Linked product to template ..."; // Reduced logging frequency
                                // --- End Link Product ---

                                // --- Process Product SKU Images (Manual DB Insert) ---
                                $this->processAndAttachImagesManual(
                                    $product,
                                    $imageMapForColor, // Use the map specific to this color
                                    'products/' . $product->id,
                                    'product_images',
                                    'view_angle' // Custom property key for view angle (e.g., 'side')
                                );
                                // --- End Process Product SKU Images (Manual DB Insert) ---
                            }
                        }
                    }
                     // echo "[INFO] Processed Template and Variants - " . json_encode(['template_id' => $template->id, 'name' => $template->name]) . PHP_EOL; // Redundant log
                } // End foreach productTemplatesData (Template/Variant Creation Loop)

                // Fetch all created templates again to ensure we have them all if needed elsewhere
                // $allCreatedTemplates = ProductTemplate::whereIn('id', $createdTemplates->values())->get()->keyBy('name'); // Moved collection population inside loop
                $allCreatedTemplates = ProductTemplate::whereIn('id', $createdTemplates->values())->get()->keyBy('name'); // Use the collected IDs

                // 7. Associate Products with Suppliers (Pivot table)
                $allProducts = Product::whereIn('id', $allCreatedProductIds)->get();
                foreach ($allProducts as $product) {
                    // Randomly select 1 to 3 suppliers for each product
                    $selectedSuppliers = $suppliers->random(rand(1, min(3, $suppliers->count())));
                    $syncData = [];
                    foreach ($selectedSuppliers as $supplier) {
                        // Use product's cost_price as the base for supplier purchase price
                        $purchasePrice = $product->cost_price ?? ($product->selling_price * 0.5); // Fallback if cost_price is null
                        // Add a slight variation per supplier
                        $variation = 1 + (rand(-5, 5) / 100); // +/- 5%
                        $supplierPurchasePrice = round($purchasePrice * $variation, 2);

                        $syncData[$supplier->id] = [
                            'purchase_price' => $supplierPurchasePrice,
                            'supplier_product_code' => $product->sku . '-' . $supplier->code, // Example supplier code
                            'tax_rate' => 10.00, // Example tax rate
                            'min_order_quantity' => 10, // Example MOQ
                            'lead_time' => rand(3, 14), // Example lead time
                            'is_preferred' => ($selectedSuppliers->count() == 1) || ($supplier->id == $selectedSuppliers->first()->id) // Mark first as preferred if multiple
                        ];
                    }
                    $product->suppliers()->sync($syncData); // Use sync to manage associations
                   // echo "[INFO] Associated suppliers for product ..."; // Reduced logging
                }
                // echo "[INFO] Associated suppliers for all products." . PHP_EOL;

                // 8. Create and Approve Purchase Orders (using PurchaseService)
                $purchaseService = app(PurchaseService::class); // Ensure PurchaseService is resolved
                $purchaseOrders = collect(); // Use collect to store created POs
                $productsToOrder = Product::with('suppliers')->whereIn('id', $allCreatedProductIds)->get(); // Get products with supplier info

                // Prepare items grouped by supplier
                $supplierOrderItems = [];
                foreach ($productsToOrder as $product) {
                    $supplierForThisProduct = $product->suppliers->first(); // Get the preferred supplier (or first one)
                    if (!$supplierForThisProduct) {
                        echo "[WARN] Product has no associated supplier, skipping for PO - " . json_encode(['product_id' => $product->id]) . PHP_EOL;
                        continue;
                    }
                    $supplierId = $supplierForThisProduct->id;
                    $pivotData = $product->suppliers()->find($supplierId)->pivot; // Get pivot data

                    if (!isset($supplierOrderItems[$supplierId])) {
                        $supplierOrderItems[$supplierId] = [];
                    }

                    $supplierOrderItems[$supplierId][] = [
                        'product_id' => $product->id,
                        'supplier_id' => $supplierId,
                        'quantity' => rand(30, 50),
                        'unit_price' => $pivotData->purchase_price,
                        'tax_rate' => $pivotData->tax_rate ?? 10.00,
                    ];
                }

                // --- Start Temporary Auth Block ---
                 // ... (existing code remains the same) ...
                 $adminUserId = 1; // Assuming user with ID 1 exists and is suitable
                 $loggedIn = false;
                 if (Schema::hasTable('users') && DB::table('users')->where('id', $adminUserId)->exists()) {
                    Auth::loginUsingId($adminUserId);
                    $loggedIn = true;
                    // $this->command->line("Temporarily logged in user ID: $adminUserId for PO creation and receipt."); // DEBUG
                 } else {
                      $this->command->warn("User ID $adminUserId not found or users table missing, activity logs might fail or skip user ID."); // DEBUG
                 }
                // --- End Temporary Auth Block Start ---

                // Create one PO per supplier
                foreach ($supplierOrderItems as $supplierId => $items) {
                     // ... (existing code remains the same) ...
                     if (empty($items)) continue;

                     $purchaseData = [
                         'warehouse_id' => $warehouse->id,
                         'purchase_date' => now()->format('Y-m-d'),
                         'notes' => 'Auto-generated by ShoeDataSeeder for Supplier ' . $supplierId,
                         'items' => $items,
                         'supplier_shipping_fee' => [$supplierId => rand(50, 150)],
                         'supplier_notes' => [$supplierId => 'Auto-generated PO']
                         // No need to pass 'supplier_id' separately if items contain it
                     ];
 
                     try {
                         // $this->command->line("Attempting to create PO for supplier: $supplierId"); // DEBUG
                         $purchase = $purchaseService->createPurchase($purchaseData);
                         // echo "[INFO] Purchase Order created - " . json_encode(['id' => $purchase->id, 'supplier_id' => $supplierId]) . PHP_EOL;
                         // $this->command->line(">>> PO Created: ID {$purchase->id}"); // DEBUG
 
                         $purchaseService->approvePurchase($purchase);
                         $purchaseOrders->push($purchase->refresh());
                         // echo "[INFO] Purchase Order approved - " . json_encode(['id' => $purchase->id]) . PHP_EOL;
                         // $this->command->line(">>> PO Approved: ID {$purchase->id}"); // DEBUG
 
                     } catch (\Exception $e) {
                          echo "[ERROR] Failed to create or approve PO for supplier - " . json_encode([
                              'supplier_id' => $supplierId,
                              'error' => $e->getMessage(),
                              'data' => $purchaseData,
                              'trace' => $e->getTraceAsString() // Optionally log trace for debugging
                          ]) . PHP_EOL;
                          $this->command->error("!!! FAILED to create/approve PO for Supplier $supplierId: {$e->getMessage()}"); // DEBUG
                     }
                }


                // 9. Confirm Receipt (using PurchaseService, skipping inspection)
                // ... (existing code remains the same) ...
                // $this->command->line("Processing receipt confirmation for " . $purchaseOrders->count() . " approved POs..."); // DEBUG
                foreach ($purchaseOrders as $purchase) {
                    $receivedQuantities = [];
                    foreach ($purchase->items as $item) {
                        $receivedQuantities[$item->id] = $item->quantity;
                    }

                    if (!empty($receivedQuantities)) {
                        try {
                            // $this->command->line("Attempting to confirm receipt for PO: {$purchase->id}"); // DEBUG
                            $purchaseService->confirmReceived($purchase, $receivedQuantities, true); // Assuming confirmReceived handles potential inspection logic based on its implementation
                            // echo "[INFO] Purchase Order receipt confirmed/processed - " . json_encode(['id' => $purchase->id]) . PHP_EOL;
                            // $this->command->line(">>> Receipt Confirmation Process Initiated/Completed for PO: {$purchase->id}"); // DEBUG
                        } catch (\Exception $e) {
                            // Check for specific duplicate entry error related to QualityInspection
                            if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), 'quality_inspections')) {
                                echo "[WARN] Duplicate QualityInspection entry skipped during receipt confirmation for PO - " . json_encode([
                                    'purchase_id' => $purchase->id,
                                    'error' => $e->getMessage(),
                                ]) . PHP_EOL;
                                $this->command->warn("!!! Skipped duplicate QualityInspection entry for PO {$purchase->id}");
                            } else {
                                // Log other errors normally
                                echo "[ERROR] Failed to confirm receipt for PO - " . json_encode([
                                    'purchase_id' => $purchase->id,
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString()
                                ]) . PHP_EOL;
                                $this->command->error("!!! FAILED to confirm receipt for PO {$purchase->id}: {$e->getMessage()}"); // DEBUG
                            }
                        }
                    }
                }

                
                // --- Start: Create Banners ---
                // echo "[INFO] Starting Banner creation..." . PHP_EOL;
                $bannersData = [
                    [
                        'title' => 'ASICS GEL-Kayano 30: Stability Meets Comfort',
                        'subtitle' => 'Experience the adaptive 4D Guidance System™.',
                        'button_text' => 'Discover Kayano 30',
                        'template_name' => 'ASICS GEL-Kayano 30', // Target template name
                        'source_image' => '1011B548_404_SR_LT_GLB.jpg', // Kayano Blue Side
                        'order' => 1,
                    ],
                    [
                        'title' => 'Nike Pegasus 41: Your Responsive Workhorse',
                        'subtitle' => 'Updated ReactX foam for enhanced energy return.',
                        'button_text' => 'Shop Pegasus 41',
                        'template_name' => 'Nike Air Zoom Pegasus 41 (Sail/Cream)',
                        'source_image' => '75a5c43e_d10a_4185_ba42_5669fbc8023c.png', // Pegasus Cream Side
                        'order' => 2,
                    ],
                    [
                        'title' => 'Adidas Superstar: The Original Shell Toe',
                        'subtitle' => 'A streetwear icon born on the court.',
                        'button_text' => 'View Superstars',
                        'template_name' => 'Adidas Superstar',
                        'source_image' => 'SUPERSTAR_II_Green_JI3076_01_00_standard.jpg', // Superstar Green Side
                        'order' => 3,
                    ],
                    [
                        'title' => 'Dr. Martens 1460: Built To Last',
                        'subtitle' => 'The original boot, embodying rebellious self-expression.',
                        'button_text' => 'Explore 1460',
                        'template_name' => 'Dr. Martens 1460 Smooth Leather Boot',
                        'source_image' => '11822006.jpg', // 1460 Black Side
                        'order' => 4,
                    ],
                    [
                        'title' => 'Nike Air Force 1 \'07: The Legend Lives On',
                        'subtitle' => 'Crisp leather, bold details, timeless style.',
                        'button_text' => 'Get Your AF1',
                        'template_name' => 'Nike Air Force 1 \'07', // Correctly escaped apostrophe
                        'source_image' => 'AIR+FORCE+1+\'07 (6).jpg', // Correctly escaped apostrophe in filename
                        'order' => 5,
                    ],
                ];

                $bannerModelType = '';
                try {
                     // Get the morph class alias if available, otherwise use class name
                    $bannerInstance = new Banner(); // Create temporary instance
                    $bannerModelType = $bannerInstance->getMorphClass();
                } catch (\Exception $e) {
                     echo "[WARN] Could not get morph class for Banner, falling back to class name. Error: {$e->getMessage()}" . PHP_EOL;
                     $bannerModelType = Banner::class; // Fallback
                }


                $publicImagePath = public_path('images');
                $storageDisk = Storage::disk('public');
                $bannerTargetDir = 'banners'; // Target directory relative to storage/app/public

                if (!$storageDisk->exists($bannerTargetDir)) {
                    $storageDisk->makeDirectory($bannerTargetDir);
                    // echo "[INFO] Created banner directory in public storage: {$bannerTargetDir}" . PHP_EOL;
                }

                foreach ($bannersData as $index => $data) {
                    // Ensure template exists before proceeding
                    if (!$allCreatedTemplates->has($data['template_name'])) {
                         echo "[WARN] Template '{$data['template_name']}' not found for banner '{$data['title']}', skipping." . PHP_EOL;
                         continue;
                    }
                    $template = $allCreatedTemplates->get($data['template_name']);
                    $buttonLink = "/products/{$template->id}"; // Use found template ID

                    $sourceFilename = $data['source_image'];
                    $sourcePath = $publicImagePath . DIRECTORY_SEPARATOR . $sourceFilename;

                    if (!File::exists($sourcePath)) {
                        echo "[WARN] Banner source image not found, skipping banner '{$data['title']}'. Source: {$sourcePath}" . PHP_EOL;
                        continue;
                    }

                    $originalExtension = File::extension($sourceFilename);
                    $safeTitleSlug = Str::slug($data['title']);
                    $targetFilename = "banner_{$index}_{$safeTitleSlug}_" . time() . '_' . Str::random(4) . '.' . $originalExtension; // Added more randomness
                     if (strlen($targetFilename) > 250) {
                        $targetFilename = substr("banner_{$index}_{$safeTitleSlug}", 0, 230) . '_' . Str::random(4) . '.' . $originalExtension;
                        echo "[WARN] Banner filename truncated: {$targetFilename}" . PHP_EOL;
                    }

                    $targetRelativePath = $bannerTargetDir . '/' . $targetFilename; 
                    $targetFullPath = storage_path('app/public/' . $targetRelativePath); 

                    $media_id = null;
                    $banner_id = null;
                    $mediaRecordForUpdate = null; // To hold the media record ID for update

                    try {
                        // 1. Copy Image
                        if (!File::exists($targetFullPath)) {
                             if (!File::copy($sourcePath, $targetFullPath)) {
                                echo "[ERROR] Failed to copy banner image. Source: {$sourcePath}, Dest: {$targetFullPath}" . PHP_EOL;
                                continue; 
                            }
                            // echo "[INFO] Copied banner image: {$targetRelativePath}" . PHP_EOL;
                        } else {
                            // echo "[INFO] Banner image already exists, skipping copy: {$targetRelativePath}" . PHP_EOL;
                        }

                        // 2. Check if media record exists for this file (less likely now with unique names)
                         $existingMedia = DB::table('media')
                                     ->where('disk', 'public')
                                     ->where('file_name', $targetFilename)
                                     ->where('collection_name', 'banner_image') // Check collection too
                                     ->first();

                         if ($existingMedia) {
                             echo "[WARN] Media record already exists for this banner file. Reusing ID: {$existingMedia->id}. File: {$targetFilename}" . PHP_EOL;
                             $media_id = $existingMedia->id;
                             // If it's not linked, we'll link it later
                             if (!$existingMedia->model_id) {
                                 $mediaRecordForUpdate = $media_id;
                             } elseif ($existingMedia->model_type === $bannerModelType) {
                                // Already linked to a banner, maybe log it
                                echo "[WARN] Reused media record {$media_id} is already linked to banner ID {$existingMedia->model_id}" . PHP_EOL;
                             } else {
                                 // Linked to something else? Problem!
                                 echo "[ERROR] Reused media record {$media_id} is linked to a different model type: {$existingMedia->model_type}" . PHP_EOL;
                                 continue; // Skip this banner
                             }
                         } else {
                             // 3. Manually Insert Media Record
                             $mimeType = File::mimeType($targetFullPath);
                             $size = File::size($targetFullPath);
                             $now = now();
                             $nameForDb = pathinfo($targetFilename, PATHINFO_FILENAME);

                            // --- Simplified Insert --- 
                             $media_id = DB::table('media')->insertGetId([
                                 'model_type' => $bannerModelType, 
                                 'model_id' => 0, // Placeholder ID
                                 'collection_name' => 'banner_image',
                                 'name' => $nameForDb,
                                 'file_name' => $targetFilename,
                                 'mime_type' => $mimeType,
                                 'disk' => 'public',
                                 'path' => $targetRelativePath, // <-- 添加 path 字段
                                 // 'conversions_disk' => 'public', // Only if column exists
                                 'size' => $size,
                                 // 'manipulations' => json_encode([]), // Only if column exists
                                 'custom_properties' => json_encode([]), // Keep custom props if needed
                                 // 'generated_conversions' => json_encode([]), // Only if column exists
                                 // 'responsive_images' => json_encode([]), // Only if column exists
                                 // 'order_column' => null, // Only if column exists
                                 'created_at' => $now,
                                 'updated_at' => $now,
                                 // Add 'uuid' => Str::uuid()->toString() ONLY if the column exists 
                             ]);
                             $mediaRecordForUpdate = $media_id; // Mark this new record for update
                             // echo "[INFO] Inserted media record for banner, Temp Media ID: {$media_id}" . PHP_EOL;
                         }

                        // 4. Create Banner Record
                        if ($media_id) {
                            // Ensure Banner model has 'media_id' in its $fillable array!
                             $banner = Banner::create([
                                'title' => $data['title'],
                                'subtitle' => $data['subtitle'],
                                'button_text' => $data['button_text'],
                                'button_link' => $buttonLink,
                                'media_id' => $media_id, // Assign the media ID
                                'order' => $data['order'],
                                'is_active' => true,
                            ]);
                            $banner_id = $banner->id;
                            // Log success immediately
                            echo "[SUCCESS] Created Banner record, Banner ID: {$banner_id}, Title: '{$data['title']}', Media ID: {$media_id}" . PHP_EOL;

                            // 5. Update Media Record with Banner ID if needed
                            if ($banner_id && $mediaRecordForUpdate) { 
                                 DB::table('media')->where('id', $mediaRecordForUpdate)->update(['model_id' => $banner_id, 'model_type' => $bannerModelType]);
                                 // echo "[INFO] Updated media record {$mediaRecordForUpdate} with banner model ID: {$banner_id} and type: {$bannerModelType}" . PHP_EOL;
                            }
                        } else {
                            echo "[ERROR] Failed to get media_id, cannot create banner '{$data['title']}'" . PHP_EOL;
                        }

                    } catch (\Illuminate\Database\QueryException $qe) {
                         echo "[ERROR] Query Exception processing banner '{$data['title']}'. Error: {$qe->getMessage()}" . PHP_EOL;
                         // Specifically check for fillable attribute error (though create usually throws MassAssignmentException)
                         if (str_contains($qe->getMessage(), 'Unknown column') || str_contains($qe->getMessage(), 'doesn\'t have a default value')) {
                            echo "[HINT] Check if 'banners' table schema is correct and 'media_id' column exists." . PHP_EOL;
                         }
                    } catch (\Illuminate\Database\Eloquent\MassAssignmentException $mae) {
                         echo "[ERROR] Mass Assignment Exception processing banner '{$data['title']}'. Error: {$mae->getMessage()}" . PHP_EOL;
                         echo "[HINT] Ensure 'title', 'subtitle', 'button_text', 'button_link', 'media_id', 'order', 'is_active' are in the Banner model's \$fillable array." . PHP_EOL;
                    } catch (\Exception $e) {
                         echo "[ERROR] General Exception processing banner '{$data['title']}'. Error: {$e->getMessage()}" . PHP_EOL;
                         echo "[ERROR] Trace: " . $e->getTraceAsString() . PHP_EOL;
                    }
                } // End foreach bannersData
                // echo "[INFO] Finished Banner creation process." . PHP_EOL;
                // --- End: Create Banners ---


                // --- Start Logout Block ---
                // ... (existing code remains the same) ...
                if ($loggedIn) {
                    Auth::logout();
                    // $this->command->line("Logged out temporary user."); 
                }
                // --- End Logout Block ---

                // $this->command->line(">>> Committing transaction..."); // DEBUG
                DB::commit();
                // echo "[INFO] ShoeDataSeeder finished successfully." . PHP_EOL;
                $this->command->info('Shoe data seeding completed successfully.');

            } catch (\Exception $e) {
                DB::rollBack();
                 echo "[FATAL ERROR] ShoeDataSeeder failed and rolled back: " . $e->getMessage() . " - " . json_encode([
                    'trace' => $e->getTraceAsString(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]) . PHP_EOL;
                $this->command->error('Shoe data seeding failed: ' . $e->getMessage());
            }
        });
        Model::reguard(); // Re-enable mass assignment guard
    }

    /**
     * Generate a rich HTML description skeleton for a product template.
     *
     * @param array $templateData Data for the product template.
     * @return string Generated HTML string.
     */
    private function generateRichHtmlDescription(array $templateData): string
    {
        $name = htmlspecialchars($templateData['name'] ?? 'Product');
        $slug = Str::slug($name);
        $tagline = htmlspecialchars($templateData['tagline'] ?? '');
        $intro = htmlspecialchars($templateData['intro'] ?? 'Discover this amazing product.');
        $features = $templateData['features'] ?? [];
        $heroImageUrl = $templateData['hero_image_url'] ?? null;
        $designTitle = htmlspecialchars($templateData['design_philosophy_title'] ?? '');
        $designText = htmlspecialchars($templateData['design_philosophy_text'] ?? '');

        // Build Hero Section HTML
        $heroStyle = $heroImageUrl ? "background-image: url('{$heroImageUrl}'); background-size: cover; background-position: center;" : '';
        $heroContent = "";
        if ($heroImageUrl) {
            // Add overlay for text readability on image background
            $heroContent = <<<HTML
            <div class="product-hero-overlay"> 
                <h2>{$name}</h2>
                <p class="tagline">{$tagline}</p>
            </div>
            HTML;
        } else {
            // Simple text hero if no image
             $heroContent = <<<HTML
             <h2>{$name}</h2>
             <p class="tagline">{$tagline}</p>
             HTML;
        }
        $heroHtml = "<div class=\"product-hero-section\" style=\"{$heroStyle}\">{$heroContent}</div>";

        // Build Features Section HTML
        $featuresHtml = '';
        if (!empty($features)) {
            $featuresHtml .= '<div class="features-section-title"><h4>Key Features</h4></div>'; // Added title for features
            $featuresHtml .= '<ul class="features-grid">';
            foreach ($features as $feature) {
                $icon = htmlspecialchars($feature['icon'] ?? '⭐');
                $title = htmlspecialchars($feature['title'] ?? 'Feature');
                $text = htmlspecialchars($feature['text'] ?? '');
                $featuresHtml .= <<<HTML
                    <li class="feature-item"> 
                        <span class="feature-icon">{$icon}</span>
                        <div class="feature-content">
                            <h4>{$title}</h4>
                            <p>{$text}</p>
                        </div>
                    </li>
                HTML;
            }
            $featuresHtml .= '</ul>';
        }

        // Build Design Philosophy Section HTML
        $designHtml = '';
        if (!empty($designTitle) || !empty($designText)) {
            $designHtml = '<div class="product-design-philosophy-section">';
            if (!empty($designTitle)) {
                $designHtml .= "<h3>{$designTitle}</h3>";
            }
            if (!empty($designText)) {
                $designHtml .= "<p>{$designText}</p>";
            }
            $designHtml .= '</div>';
        }

        // Combine all sections
        return <<<HTML
        <div id="product-desc-{$slug}" class="product-description-rich"> 
            {$heroHtml}
            <div class="product-content-section">
                <p class="intro-paragraph">{$intro}</p>
                {$designHtml} 
                {$featuresHtml} 
            </div>
        </div>
        HTML;
    }

    /**
     * Processes image mappings, copies files, and inserts records directly into the media table.
     *
     * @param Model $model The model instance (Product or ProductTemplate).
     * @param array $imageMap Associative array (e.g., ['usage' => 'filename.jpg'] or ['view_angle' => 'filename.jpg']).
     * @param string $targetDirectory The base directory within storage/app/public/ to store images.
     * @param string $collectionName The desired collection name.
     * @param string $customPropertyKey Key for custom property (e.g., 'usage' or 'view_angle').
     */
    private function processAndAttachImagesManual(
        Model $model,
        array $imageMap,
        string $targetDirectory,
        string $collectionName,
        string $customPropertyKey
    ): void {
        $publicImagePath = public_path('images');
        $storageDisk = Storage::disk('public'); // Use the 'public' disk

        // Determine the model type string for the media table and logging
        $modelTypeString = 'unknown'; // Default value
        if ($model instanceof \App\Models\Product) { // Corrected namespace separator
            // Try to use the morph map alias if defined, otherwise fallback
            try {
                $modelTypeString = $model->getMorphClass();
            } catch (\Exception $e) { // Corrected namespace separator
                 echo "[WARN] Could not get morph class for Product, falling back to 'products'. Error: {$e->getMessage()}" . PHP_EOL;
                 $modelTypeString = 'products'; // Fallback alias
            }
        } elseif ($model instanceof \App\Models\ProductTemplate) { // Corrected namespace separator
            // Use the fully qualified class name for ProductTemplate
            $modelTypeString = \App\Models\ProductTemplate::class; // Corrected usage
        } else {
            // Fallback to class name for any other model types
            $modelTypeString = get_class($model);
        }


        foreach ($imageMap as $propertyValue => $sourceFilename) {
            $sourcePath = $publicImagePath . DIRECTORY_SEPARATOR . $sourceFilename;

            if (!File::exists($sourcePath)) {
                echo "[WARN] Source image not found, skipping. - " . json_encode([
                    'model_type' => $modelTypeString, // Use the determined string
                    'model_id' => $model->id,
                    'source_file' => $sourceFilename,
                    'expected_path' => $sourcePath,
                ]) . PHP_EOL;
                continue;
            }

            // Create target directory if it doesn't exist
            if (!$storageDisk->exists($targetDirectory)) {
                $storageDisk->makeDirectory($targetDirectory);
                // echo "[INFO] Created directory in public storage. - " . json_encode(['directory' => $targetDirectory]) . PHP_EOL;
            }

            // Check if the model belongs to the specific templates needing simplified names
            $needsSimplification = false;
            $modelName = '';
            // Ensure we check based on the actual model type passed
            if ($model instanceof \App\Models\Product && $model->template) { // Corrected namespace separator
                 $modelName = $model->template->name; // Check template name for product
                 if (str_contains($modelName, 'Adidas Adizero Adios Pro 3') ||
                    str_contains($modelName, 'Nike Air Force 1 \'07') || // Corrected escaped single quote
                    str_contains($modelName, 'Dr. Martens 1460 Smooth Leather Boot') ||
                    str_contains($modelName, 'G.H. Bass Weejuns Larson Penny Loafer')) // Added G.H. Bass
                 {
                    $needsSimplification = true;
                }
            } elseif ($model instanceof \App\Models\ProductTemplate) { // Corrected namespace separator
                $modelName = $model->name; // Check template name directly
                 if (str_contains($modelName, 'Adidas Adizero Adios Pro 3') ||
                     str_contains($modelName, 'Nike Air Force 1 \'07') || // Corrected escaped single quote
                     str_contains($modelName, 'Dr. Martens 1460 Smooth Leather Boot') ||
                     str_contains($modelName, 'G.H. Bass Weejuns Larson Penny Loafer')) // Added G.H. Bass
                 {
                    $needsSimplification = true;
                }
            }


            if ($needsSimplification) {
                $originalExtension = File::extension($sourceFilename);
                // Generate a more robust slug including model name context if possible
                $modelSlugPart = Str::slug($modelName ?: 'template-' . $model->id); // Add model name context
                $baseName = Str::slug("{$modelSlugPart}-{$customPropertyKey}-{$propertyValue}");

                $targetFilename = $baseName . '.' . $originalExtension;
                 // Truncate if filename is too long (e.g., > 255 characters)
                if (strlen($targetFilename) > 250) {
                     $targetFilename = substr($baseName, 0, 240) . '.' . $originalExtension; // Adjust length limit
                     echo "[WARN] Simplified filename truncated due to length: {$targetFilename} for source: {$sourceFilename}" . PHP_EOL;
                } else {
                     echo "[DEBUG] Simplified filename generated: {$targetFilename} for source: {$sourceFilename}" . PHP_EOL;
                }
            } else {
                // Use original basename for other models
                $targetFilename = basename($sourceFilename);
            }


            $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $targetFilename;
            $targetFullPath = storage_path('app/public/' . $targetPath);

            try {
                // Check if the target file already exists
                if (!File::exists($targetFullPath)) {
                    // Copy the file only if it doesn't exist
                    if (!File::copy($sourcePath, $targetFullPath)) {
                        echo "[ERROR] Failed to copy image file. - " . json_encode([
                            'source' => $sourcePath,
                            'destination' => $targetFullPath
                        ]) . PHP_EOL;
                        continue; // Skip adding media if copy failed
                    }
                    // echo "[INFO] Copied image file successfully. - " . json_encode([
                    //     'source' => $sourcePath,
                    //     'destination' => $targetFullPath
                    // ]) . PHP_EOL;
                } else {
                    // echo "[INFO] Target image file already exists, skipping copy. - " . json_encode([
                    //      'target_path' => $targetFullPath
                    // ]) . PHP_EOL;
                }

                // --- Start Manual Media Table Insert ---
                $mimeType = File::mimeType($targetFullPath);
                $size = File::size($targetFullPath);
                $now = now();

                // Use the determined target filename (original or simplified)
                $nameForDb = pathinfo($targetFilename, PATHINFO_FILENAME);

                // Check if a media record already exists for this exact file and model/collection
                 $existingMedia = DB::table('media')->where('model_type', $modelTypeString)
                                     ->where('model_id', $model->id)
                                     ->where('collection_name', $collectionName)
                                     ->where('file_name', $targetFilename) // Check against the specific filename
                                     ->first();

                if ($existingMedia) {
                    echo "[INFO] Media record already exists, skipping insert. - " . json_encode([
                        'model_type' => $modelTypeString,
                        'model_id' => $model->id,
                        'collection' => $collectionName,
                        'file' => $targetFilename,
                    ]) . PHP_EOL;
                } else {
                    DB::table('media')->insert([
                        'model_type' => $modelTypeString, // Use the determined string (e.g., App\Models\ProductTemplate)
                        'model_id' => $model->id,
                        'collection_name' => $collectionName,
                        'name' => $nameForDb,
                        'file_name' => $targetFilename, // Full filename
                        'mime_type' => $mimeType,
                        'disk' => 'public', // Assuming storage on public disk
                        'path' => $targetPath, // Relative path within the disk
                        'size' => $size,
                        'custom_properties' => json_encode([$customPropertyKey => $propertyValue]), // Store custom props as JSON
                        'created_at' => $now,
                        'updated_at' => $now,
                        // Add other columns if your media table has them (e.g., temp_id = null)
                        'temp_id' => null, // Explicitly set temp_id to null
                    ]);
                    // --- End Manual Media Table Insert ---

                    // echo "[INFO] Inserted media record for model. - " . json_encode([
                    //     'model_type' => $modelTypeString, // Use the determined string
                    //     'model_id' => $model->id,
                    //     'collection' => $collectionName,
                    //     'file' => $targetFilename,
                    //     'custom_property' => [$customPropertyKey => $propertyValue],
                    //     'path' => $targetPath
                    // ]) . PHP_EOL;
                }


            } catch (\Exception $e) { // Corrected namespace separator
                echo "[ERROR] Failed to process file or insert media record for model. - " . json_encode([
                    'model_type' => $modelTypeString, // Use the determined string
                    'model_id' => $model->id,
                    'source_file' => $sourceFilename,
                    'target_path' => $targetPath,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString() // Add trace for detailed debugging
                ]) . PHP_EOL;
                 // Optionally attempt to clean up the copied file if media attachment failed
                 // Be cautious with deleting if the file might be shared or used by another process
                 // if (!$existingMedia && File::exists($targetFullPath)) {
                 //     File::delete($targetFullPath);
                 // }
            }
        }
    }
}

