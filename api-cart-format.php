<?php
/**
 * Cart API Response Format Specification
 * 
 * This file defines the expected API response format for cart operations
 * in the Optic System e-commerce platform.
 */

header('Content-Type: application/json');

// Example cart response structure with minimal required data
$cartResponseFormat = [
    'success' => true,
    'message' => 'Cart retrieved successfully.',
    'data' => [
        'carts' => [
            [
                'id' => 1,
                'name' => 'Main Shopping Cart',
                'type' => 'default',
                'is_default' => true,
                'items' => [
                    [
                        'id' => 101,
                        'product_id' => 5, // Only product_id is needed - frontend will fetch current price
                        'quantity' => 2,
                        'size' => 'M',
                        'color' => 'Blue'
                    ],
                    [
                        'id' => 102,
                        'product_id' => 7,
                        'quantity' => 1,
                        'size' => 'L',
                        'color' => 'Black'
                    ],
                ],
                'item_count' => 2,
                'total' => 0 // Calculated on frontend using current prices
            ],
            [
                'id' => 2,
                'name' => 'Wishlist',
                'type' => 'wishlist',
                'is_default' => false,
                'items' => [
                    [
                        'id' => 201,
                        'product_id' => 12,
                        'quantity' => 1,
                        'size' => null,
                        'color' => null
                    ],
                ],
                'item_count' => 1,
                'total' => 0 // Not relevant for wishlist
            ],
            [
                'id' => 3,
                'name' => 'Save for Later',
                'type' => 'saveforlater',
                'is_default' => false,
                'items' => [],
                'item_count' => 0,
                'total' => 0
            ]
        ],
        'active_cart_id' => 1,
    ]
];

// Example response format for add to cart operation
$addToCartResponseFormat = [
    'success' => true,
    'message' => 'Product added to cart successfully.',
    'data' => [
        'cart_id' => 1,
        'item' => [
            'id' => 103,
            'product_id' => 8,
            'quantity' => 1,
            'size' => 'S',
            'color' => 'Red'
        ]
    ]
];

// Display JSON examples for reference
echo json_encode([
    'get_cart_response' => $cartResponseFormat,
    'add_to_cart_response' => $addToCartResponseFormat
], JSON_PRETTY_PRINT); 