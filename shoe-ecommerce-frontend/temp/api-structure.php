<?php
header('Content-Type: application/json');

// 模拟API响应数据
$mockApiResponse = [
    'success' => true,
    'data' => [
        'featured_templates' => [
            [
                'id' => 1,
                'title' => 'Nike Air Max',
                'description' => 'Nike Air Max running shoes with enhanced comfort.',
                'type' => 'shoes',
                'category' => [
                    'id' => 1,
                    'name' => 'Running'
                ],
                'is_featured' => true,
                'is_new_arrival' => false,
                'is_sale' => true,
                'images' => [
                    [
                        'id' => 1,
                        'url' => 'https://example.com/images/nike-air-max.jpg',
                        'thumbnail' => 'https://example.com/images/nike-air-max-thumb.jpg'
                    ]
                ],
                'linked_products' => [
                    [
                        'id' => 101,
                        'sku' => 'NIKE-AM-001',
                        'name' => 'Nike Air Max - Black/White',
                        'price' => 129.99,
                        'original_price' => 159.99,
                        'discount_percentage' => 19,
                        'stock_quantity' => 25,
                        'relation_type' => 'direct',
                        'images' => [
                            [
                                'id' => 11,
                                'url' => 'https://example.com/images/nike-am-black-white.jpg',
                                'thumbnail' => 'https://example.com/images/nike-am-black-white-thumb.jpg'
                            ]
                        ],
                        'parameters' => [
                            'color' => 'Black/White',
                            'size' => '42'
                        ]
                    ],
                    [
                        'id' => 102,
                        'sku' => 'NIKE-AM-002',
                        'name' => 'Nike Air Max - Red/Black',
                        'price' => 139.99,
                        'original_price' => 159.99,
                        'discount_percentage' => 12,
                        'stock_quantity' => 15,
                        'relation_type' => 'direct',
                        'images' => [
                            [
                                'id' => 12,
                                'url' => 'https://example.com/images/nike-am-red-black.jpg',
                                'thumbnail' => 'https://example.com/images/nike-am-red-black-thumb.jpg'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'id' => 2,
                'title' => 'Adidas Ultraboost',
                'description' => 'Adidas Ultraboost for marathon runners.',
                'type' => 'shoes',
                'category' => [
                    'id' => 1,
                    'name' => 'Running'
                ],
                'is_featured' => true,
                'is_new_arrival' => true,
                'is_sale' => false,
                'images' => [
                    [
                        'id' => 2,
                        'url' => 'https://example.com/images/adidas-ultraboost.jpg',
                        'thumbnail' => 'https://example.com/images/adidas-ultraboost-thumb.jpg'
                    ]
                ],
                'linked_products' => [
                    [
                        'id' => 201,
                        'sku' => 'ADI-UB-001',
                        'name' => 'Adidas Ultraboost - Black',
                        'price' => 179.99,
                        'stock_quantity' => 30,
                        'relation_type' => 'direct',
                        'images' => [
                            [
                                'id' => 21,
                                'url' => 'https://example.com/images/adidas-ub-black.jpg',
                                'thumbnail' => 'https://example.com/images/adidas-ub-black-thumb.jpg'
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'new_arrival_templates' => [
            // 结构与featured_templates相同
        ],
        'sale_templates' => [
            // 结构与featured_templates相同
        ]
    ]
];

// 分析ProductTemplate结构
function analyzeTemplateStructure($templates) {
    $results = [];
    
    foreach ($templates as $index => $template) {
        $templateInfo = [
            'id' => $template['id'],
            'title' => $template['title'],
            'has_discount' => false,
            'discount_info' => [],
            'linked_products_count' => count($template['linked_products'] ?? []),
            'image_paths' => [],
            'flags' => [
                'is_featured' => $template['is_featured'] ?? false,
                'is_new_arrival' => $template['is_new_arrival'] ?? false,
                'is_sale' => $template['is_sale'] ?? false
            ]
        ];
        
        // 检查模板相关产品中的折扣信息
        $maxDiscount = 0;
        $minPrice = PHP_FLOAT_MAX;
        $maxPrice = 0;
        
        if (!empty($template['linked_products'])) {
            foreach ($template['linked_products'] as $product) {
                // 收集折扣信息
                if (isset($product['discount_percentage']) && $product['discount_percentage'] > 0) {
                    $templateInfo['has_discount'] = true;
                    if ($product['discount_percentage'] > $maxDiscount) {
                        $maxDiscount = $product['discount_percentage'];
                    }
                    
                    $discountInfo = [
                        'product_id' => $product['id'],
                        'product_name' => $product['name'],
                        'original_price' => $product['original_price'] ?? null,
                        'discount_price' => $product['price'],
                        'discount_percentage' => $product['discount_percentage']
                    ];
                    
                    $templateInfo['discount_info'][] = $discountInfo;
                }
                
                // 记录最高和最低价格
                if ($product['price'] < $minPrice) {
                    $minPrice = $product['price'];
                }
                if ($product['price'] > $maxPrice) {
                    $maxPrice = $product['price'];
                }
                
                // 收集图片路径
                if (!empty($product['images'])) {
                    foreach ($product['images'] as $image) {
                        $templateInfo['image_paths'][] = $image['url'];
                    }
                }
            }
        }
        
        $templateInfo['price_range'] = [
            'min' => $minPrice !== PHP_FLOAT_MAX ? $minPrice : null,
            'max' => $maxPrice > 0 ? $maxPrice : null
        ];
        
        $templateInfo['max_discount_percentage'] = $maxDiscount;
        
        // 检查模板自身图片
        if (!empty($template['images'])) {
            foreach ($template['images'] as $image) {
                $templateInfo['image_paths'][] = $image['url'];
            }
        }
        
        $results[$index] = $templateInfo;
    }
    
    return $results;
}

// 分析API响应
$templateAnalysis = [
    'featured_templates' => analyzeTemplateStructure($mockApiResponse['data']['featured_templates'])
];

// 输出结果
echo json_encode([
    'api_structure' => [
        'template_structure' => [
            'id' => 'Template ID (number)',
            'title' => 'Template title (string)',
            'description' => 'Template description (string)',
            'type' => 'Template type (string)',
            'category' => [
                'id' => 'Category ID (number)',
                'name' => 'Category name (string)'
            ],
            'is_featured' => 'Whether template is featured (boolean)',
            'is_new_arrival' => 'Whether template is new arrival (boolean)',
            'is_sale' => 'Whether template is on sale (boolean)',
            'images' => [
                [
                    'id' => 'Image ID (number)',
                    'url' => 'Image URL (string)',
                    'thumbnail' => 'Thumbnail URL (string)'
                ]
            ],
            'linked_products' => [
                [
                    'id' => 'Product ID (number)',
                    'sku' => 'Product SKU (string)',
                    'name' => 'Product name (string)',
                    'price' => 'Product price (number)',
                    'original_price' => 'Original price before discount (number, optional)',
                    'discount_percentage' => 'Discount percentage (number, optional)',
                    'stock_quantity' => 'Stock quantity (number)',
                    'relation_type' => 'Relation type (string)',
                    'images' => [
                        [
                            'id' => 'Image ID (number)',
                            'url' => 'Image URL (string)',
                            'thumbnail' => 'Thumbnail URL (string)'
                        ]
                    ]
                ]
            ]
        ]
    ],
    'template_analysis' => $templateAnalysis,
    'discount_location' => '折扣信息位于template.linked_products[].discount_percentage字段中',
    'image_location' => '图片位于template.images[].url或template.linked_products[].images[].url',
    'recommendations' => [
        '从linked_products找出最大折扣显示在模板卡片上',
        '需要展示价格范围，例如"From $xx.xx"或"$xx.xx - $yy.yy"',
        '显示is_new_arrival和is_sale标签到模板卡片',
        '根据折扣百分比显示折扣标签',
        '当模板有多个变体时，显示变体数量'
    ]
], JSON_PRETTY_PRINT); 