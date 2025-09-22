# API目录

本文档记录系统中使用的所有API调用、返回格式和功能描述，用于优化页面加载速度时参考。

## 购物车相关API

### 1. 获取购物车数据

**调用函数:** `getCart`/`apiService.cart.get`

**路径:** `/cart`或`/cart/{cart_id}`

**参数:**
```typescript
{
  cart_id?: number | string;
  type?: string;
}
```

**返回格式:**
```json
{
  "success": true,
  "message": "Cart items retrieved successfully",
  "data": {
    "carts": [
      {
        "id": 21,
        "name": "sdsadasa",
        "type": "default",
        "is_default": 0,
        "items": [
          {
            "id": 31,
            "product_id": 1,
            "name": "Zeiss 1.67 Lens - Updated Apjny",
            "sku": "ZEISS-TEST-ZEqCwXPX",
            "quantity": 14,
            "price": 329.99,
            "subtotal": 4619.860000000001,
            "original_price": null,
            "selling_price": "329.99",
            "discount_percentage": "5.00",
            "specifications": {
              "color": "red",
              "size": "big",
              "template_id": 1
            },
            "parameter_group": "color=red;size =big",
            "images": [
              "http://localhost:2268/storage/productss/1/images/v5cHuymMFkWZazJxNWOyW8pHgG0l5q4euzVJy3tx.png",
              "http://localhost:2268/storage/productss/1/images/ykAKL8bOSCmdDyVjwfP12dBdWuydGNG8lVuR681T.png"
            ],
            "template": {
              "id": 1,
              "name": "test product",
              "category": {
                "id": 1,
                "name": "Lens"
              },
              "parameters": [
                {
                  "name": "color",
                  "values": [
                    "red",
                    "yellow"
                  ]
                },
                {
                  "name": "size",
                  "values": [
                    "big",
                    "small"
                  ]
                }
              ]
            },
            "parameters": {
              "spherical-degree-67f108f8a5b67": "1.00",
              "pillar-67f108f8a619a": "0.50",
              "refractive-rate-67f108f8a69a3": "1.67",
              "lens-type-67f108f8a6e82": "Advance"
            }
          }
        ],
        "total": 6869.860000000001,
        "item_count": 2
      }
    ],
    "total": 6869.860000000001,
    "cart_count": 1
  }
}
```

**功能描述:** 获取购物车数据，包括购物车中的商品、总价、商品数量等。

### 2. 更新购物车商品数量

**调用函数:** `apiService.cart.update`

**路径:** `/cart/{item_id}`

**方法:** PUT

**参数:**
```typescript
{
  quantity: number;
}
```

**返回格式:** 与获取购物车数据相同

**功能描述:** 更新购物车中特定商品的数量。

### 3. 移除购物车商品

**调用函数:** `apiService.cart.remove`/`handleRemoveItem`

**路径:** `/cart/{item_id}`

**方法:** DELETE

**返回格式:** 与获取购物车数据相同

**功能描述:** 从购物车中移除特定商品。

### 4. 清空购物车

**调用函数:** `apiService.cart.clear`/`handleClearCart`

**路径:** `/cart/clear`

**方法:** POST

**返回格式:**
```json
{
  "success": true,
  "message": "Cart cleared successfully",
  "data": null
}
```

**功能描述:** 清空整个购物车。

## 产品相关API

### 1. 获取模板数据

**调用函数:** `apiService.templates.getById`

**路径:** `/templates/{template_id}`

**方法:** GET

**返回格式:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "test product",
    "description": "...",
    "category": {
      "id": 1,
      "name": "Lens"
    },
    "parameters": [
      {
        "name": "color",
        "values": ["red", "yellow"]
      },
      {
        "name": "size",
        "values": ["big", "small"]
      }
    ],
    "images": [...],
    "is_active": true,
    "is_featured": true,
    "is_new_arrival": true,
    "is_sale": false,
    "created_at": "...",
    "updated_at": "...",
    "linked_products": [...]
  }
}
```

**功能描述:** 获取特定模板ID的详细数据。

### 2. 获取产品列表

**调用函数:** 待确认

**路径:** `/products`

**方法:** GET

**参数:**
```typescript
{
  category_id?: number;
  template_id?: number;
  featured?: boolean;
  new_arrival?: boolean;
  on_sale?: boolean;
  page?: number;
  limit?: number;
}
```

**返回格式:** 待确认

**功能描述:** 获取产品列表，可以根据不同条件进行筛选。

### 3. 获取产品详情

**调用函数:** 待确认

**路径:** `/products/{product_id}`

**方法:** GET

**返回格式:** 待确认

**功能描述:** 获取特定产品的详细信息。

## 认证相关API

### 1. CSRF令牌

**调用函数:** `csrfService.getCsrfToken`

**路径:** `/sanctum/csrf-cookie`

**方法:** GET

**返回格式:** 无特定JSON返回，通过Cookie设置CSRF令牌

**功能描述:** 获取CSRF令牌，用于后续API请求的安全验证。

### 2. 用户认证

**调用函数:** `authService`相关方法

**路径:** 待确认

**方法:** 待确认

**返回格式:** 待确认

**功能描述:** 用户登录、注册、注销等功能。

## 其他API

待补充...

## 性能优化注意事项

在优化这些API调用时，需要注意以下几点：

1. 保持API的功能完整性，确保返回数据结构不变
2. 缓存策略应该考虑数据实时性要求
3. 购物车数据通常需要较高的实时性
4. 产品和模板数据可以进行更长时间的缓存
5. 所有优化不应破坏现有的业务逻辑 