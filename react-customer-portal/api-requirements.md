# 鞋类电商前端项目API需求分析

## 概述

本文档详细分析了鞋类电商前端项目所需的API接口，用于与后端Laravel系统进行交互。这些API接口将支持用户认证、产品浏览、购物车管理、订单处理等核心功能。

## 现有API分析

通过对现有系统的分析，我们发现系统已经实现了一些API接口，但主要面向管理后台使用，缺少专门为客户门户设计的API。现有的API主要集中在以下几个方面：

1. 用户认证
2. 产品管理
3. 客户管理
4. 供应商管理
5. 采购管理
6. 通知管理

然而，针对客户门户的一些关键API还需要开发或扩展，例如：

1. 客户注册和登录专用API
2. 产品搜索和筛选API
3. 购物车管理API
4. 订单创建和管理API
5. 支付处理API
6. 用户评价API

## 需要开发的API接口

### 1. 认证API

#### 1.1 客户注册

- **端点**: `/api/customer/register`
- **方法**: POST
- **描述**: 允许新客户注册账户
- **请求参数**:
  ```json
  {
    "name": "客户姓名",
    "email": "customer@example.com",
    "password": "密码",
    "password_confirmation": "确认密码",
    "contact_number": "联系电话",
    "address": "地址"
  }
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "注册成功",
    "data": {
      "token": "访问令牌",
      "customer": {
        "id": 1,
        "name": "客户姓名",
        "email": "customer@example.com",
        "contact_number": "联系电话",
        "created_at": "2023-01-01T00:00:00.000000Z"
      }
    }
  }
  ```

#### 1.2 客户登录

- **端点**: `/api/customer/login`
- **方法**: POST
- **描述**: 允许客户使用邮箱和密码登录
- **请求参数**:
  ```json
  {
    "email": "customer@example.com",
    "password": "密码"
  }
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "登录成功",
    "data": {
      "token": "访问令牌",
      "customer": {
        "id": 1,
        "name": "客户姓名",
        "email": "customer@example.com",
        "contact_number": "联系电话",
        "created_at": "2023-01-01T00:00:00.000000Z"
      }
    }
  }
  ```

#### 1.3 获取当前客户信息

- **端点**: `/api/customer/profile`
- **方法**: GET
- **描述**: 获取当前登录客户的详细信息
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "customer": {
        "id": 1,
        "name": "客户姓名",
        "email": "customer@example.com",
        "contact_number": "联系电话",
        "address": "地址",
        "birthday": "1990-01-01",
        "points": 100,
        "member_level": "普通会员",
        "created_at": "2023-01-01T00:00:00.000000Z"
      }
    }
  }
  ```

#### 1.4 更新客户信息

- **端点**: `/api/customer/profile`
- **方法**: PUT
- **描述**: 更新当前登录客户的信息
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **请求参数**:
  ```json
  {
    "name": "新姓名",
    "contact_number": "新联系电话",
    "address": "新地址",
    "birthday": "1990-01-01"
  }
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "个人信息已更新",
    "data": {
      "customer": {
        "id": 1,
        "name": "新姓名",
        "email": "customer@example.com",
        "contact_number": "新联系电话",
        "address": "新地址",
        "birthday": "1990-01-01",
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-02T00:00:00.000000Z"
      }
    }
  }
  ```

#### 1.5 修改密码

- **端点**: `/api/customer/password`
- **方法**: PUT
- **描述**: 允许客户修改密码
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **请求参数**:
  ```json
  {
    "current_password": "当前密码",
    "password": "新密码",
    "password_confirmation": "确认新密码"
  }
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "密码已更新"
  }
  ```

#### 1.6 忘记密码

- **端点**: `/api/customer/forgot-password`
- **方法**: POST
- **描述**: 发送密码重置链接到客户邮箱
- **请求参数**:
  ```json
  {
    "email": "customer@example.com"
  }
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "密码重置链接已发送到您的邮箱"
  }
  ```

#### 1.7 重置密码

- **端点**: `/api/customer/reset-password`
- **方法**: POST
- **描述**: 使用重置令牌重置密码
- **请求参数**:
  ```json
  {
    "token": "重置令牌",
    "email": "customer@example.com",
    "password": "新密码",
    "password_confirmation": "确认新密码"
  }
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "密码已重置"
  }
  ```

#### 1.8 登出

- **端点**: `/api/customer/logout`
- **方法**: POST
- **描述**: 使当前客户的访问令牌失效
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "已成功登出"
  }
  ```

### 2. 产品API

#### 2.1 获取产品列表

- **端点**: `/api/products`
- **方法**: GET
- **描述**: 获取产品列表，支持分页、排序和筛选
- **请求参数**:
  - `page`: 页码（默认1）
  - `per_page`: 每页数量（默认12）
  - `sort_by`: 排序字段（name, price, created_at等）
  - `sort_direction`: 排序方向（asc, desc）
  - `category_id`: 按分类筛选
  - `brand`: 按品牌筛选
  - `price_min`: 最低价格
  - `price_max`: 最高价格
  - `search`: 搜索关键词
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "products": [
        {
          "id": 1,
          "name": "运动鞋A",
          "sku": "SP-001",
          "category_id": 1,
          "brand": "Nike",
          "model": "Air Max",
          "selling_price": 899.00,
          "description": "舒适的运动鞋",
          "images": ["url1.jpg", "url2.jpg"],
          "is_active": true,
          "parameters": {
            "color": "黑色",
            "material": "网布"
          },
          "created_at": "2023-01-01T00:00:00.000000Z"
        },
        // 更多产品...
      ],
      "pagination": {
        "total": 100,
        "per_page": 12,
        "current_page": 1,
        "last_page": 9,
        "from": 1,
        "to": 12
      }
    }
  }
  ```

#### 2.2 获取产品详情

- **端点**: `/api/products/{id}`
- **方法**: GET
- **描述**: 获取单个产品的详细信息
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "product": {
        "id": 1,
        "name": "运动鞋A",
        "sku": "SP-001",
        "barcode": "123456789",
        "category_id": 1,
        "category": {
          "id": 1,
          "name": "运动鞋",
          "description": "适合运动的鞋类"
        },
        "brand": "Nike",
        "model": "Air Max",
        "cost_price": 500.00,
        "selling_price": 899.00,
        "description": "舒适的运动鞋，适合日常跑步和健身",
        "images": ["url1.jpg", "url2.jpg", "url3.jpg"],
        "is_active": true,
        "parameters": {
          "color": "黑色",
          "material": "网布",
          "sole": "橡胶",
          "closure": "系带"
        },
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-02T00:00:00.000000Z"
      },
      "related_products": [
        {
          "id": 2,
          "name": "运动鞋B",
          "sku": "SP-002",
          "brand": "Nike",
          "selling_price": 799.00,
          "images": ["url1.jpg"]
        },
        // 更多相关产品...
      ]
    }
  }
  ```

#### 2.3 获取产品分类

- **端点**: `/api/product-categories`
- **方法**: GET
- **描述**: 获取所有产品分类
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "categories": [
        {
          "id": 1,
          "name": "运动鞋",
          "description": "适合运动的鞋类",
          "image": "url.jpg",
          "parent_id": null,
          "children": [
            {
              "id": 2,
              "name": "跑步鞋",
              "description": "专业跑步鞋",
              "image": "url.jpg",
              "parent_id": 1
            },
            // 更多子分类...
          ]
        },
        // 更多分类...
      ]
    }
  }
  ```

#### 2.4 获取分类参数

- **端点**: `/api/product-categories/{category_id}/parameters`
- **方法**: GET
- **描述**: 获取特定分类的参数定义，用于产品筛选
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "parameters": [
        {
          "id": 1,
          "name": "颜色",
          "type": "select",
          "options": ["黑色", "白色", "红色", "蓝色"]
        },
        {
          "id": 2,
          "name": "尺码",
          "type": "select",
          "options": ["35", "36", "37", "38", "39", "40", "41", "42", "43", "44", "45"]
        },
        {
          "id": 3,
          "name": "材质",
          "type": "select",
          "options": ["皮革", "网布", "帆布", "合成材料"]
        }
        // 更多参数...
      ]
    }
  }
  ```

#### 2.5 获取热门产品

- **端点**: `/api/products/featured`
- **方法**: GET
- **描述**: 获取推荐或热门产品
- **请求参数**:
  - `limit`: 返回数量（默认8）
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "products": [
        {
          "id": 1,
          "name": "运动鞋A",
          "sku": "SP-001",
          "brand": "Nike",
          "selling_price": 899.00,
          "images": ["url1.jpg"]
        },
        // 更多产品...
      ]
    }
  }
  ```

#### 2.6 获取新品

- **端点**: `/api/products/new-arrivals`
- **方法**: GET
- **描述**: 获取最新上架的产品
- **请求参数**:
  - `limit`: 返回数量（默认8）
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "products": [
        {
          "id": 1,
          "name": "运动鞋A",
          "sku": "SP-001",
          "brand": "Nike",
          "selling_price": 899.00,
          "images": ["url1.jpg"],
          "created_at": "2023-01-01T00:00:00.000000Z"
        },
        // 更多产品...
      ]
    }
  }
  ```

#### 2.7 搜索产品

- **端点**: `/api/products/search`
- **方法**: GET
- **描述**: 搜索产品
- **请求参数**:
  - `query`: 搜索关键词
  - `page`: 页码（默认1）
  - `per_page`: 每页数量（默认12）
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "products": [
        {
          "id": 1,
          "name": "运动鞋A",
          "sku": "SP-001",
          "brand": "Nike",
          "selling_price": 899.00,
          "images": ["url1.jpg"]
        },
        // 更多产品...
      ],
      "pagination": {
        "total": 20,
        "per_page": 12,
        "current_page": 1,
        "last_page": 2,
        "from": 1,
        "to": 12
      }
    }
  }
  ```

#### 2.8 获取产品库存状态

- **端点**: `/api/products/{id}/stock`
- **方法**: GET
- **描述**: 获取产品的库存状态
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "in_stock": true,
      "quantity": 15,
      "available_sizes": ["38", "39", "40", "41", "42"]
    }
  }
  ```

### 3. 购物车API

#### 3.1 获取购物车

- **端点**: `/api/cart`
- **方法**: GET
- **描述**: 获取当前用户的购物车
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "cart": {
        "id": 1,
        "customer_id": 1,
        "store_id": 1,
        "subtotal": 1698.00,
        "tax_amount": 0.00,
        "discount_amount": 0.00,
        "total": 1698.00,
        "item_count": 2,
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-02T00:00:00.000000Z",
        "items": [
          {
            "id": 1,
            "cart_id": 1,
            "product_id": 1,
            "quantity": 1,
            "price": 899.00,
            "subtotal": 899.00,
            "specifications": {
              "color": "黑色",
              "size": "42"
            },
            "product": {
              "id": 1,
              "name": "运动鞋A",
              "sku": "SP-001",
              "brand": "Nike",
              "images": ["url1.jpg"]
            }
          },
          {
            "id": 2,
            "cart_id": 1,
            "product_id": 2,
            "quantity": 1,
            "price": 799.00,
            "subtotal": 799.00,
            "specifications": {
              "color": "白色",
              "size": "40"
            },
            "product": {
              "id": 2,
              "name": "运动鞋B",
              "sku": "SP-002",
              "brand": "Adidas",
              "images": ["url1.jpg"]
            }
          }
        ]
      }
    }
  }
  ```

#### 3.2 添加商品到购物车

- **端点**: `/api/cart/items`
- **方法**: POST
- **描述**: 添加商品到购物车
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **请求参数**:
  ```json
  {
    "product_id": 1,
    "quantity": 1,
    "specifications": {
      "color": "黑色",
      "size": "42"
    }
  }
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "商品已添加到购物车",
    "data": {
      "cart_item": {
        "id": 1,
        "cart_id": 1,
        "product_id": 1,
        "quantity": 1,
        "price": 899.00,
        "subtotal": 899.00,
        "specifications": {
          "color": "黑色",
          "size": "42"
        },
        "product": {
          "id": 1,
          "name": "运动鞋A",
          "sku": "SP-001",
          "brand": "Nike",
          "images": ["url1.jpg"]
        }
      },
      "cart": {
        "subtotal": 899.00,
        "tax_amount": 0.00,
        "discount_amount": 0.00,
        "total": 899.00,
        "item_count": 1
      }
    }
  }
  ```

#### 3.3 更新购物车商品

- **端点**: `/api/cart/items/{id}`
- **方法**: PUT
- **描述**: 更新购物车中的商品数量或规格
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **请求参数**:
  ```json
  {
    "quantity": 2,
    "specifications": {
      "color": "黑色",
      "size": "43"
    }
  }
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "购物车已更新",
    "data": {
      "cart_item": {
        "id": 1,
        "cart_id": 1,
        "product_id": 1,
        "quantity": 2,
        "price": 899.00,
        "subtotal": 1798.00,
        "specifications": {
          "color": "黑色",
          "size": "43"
        }
      },
      "cart": {
        "subtotal": 1798.00,
        "tax_amount": 0.00,
        "discount_amount": 0.00,
        "total": 1798.00,
        "item_count": 2
      }
    }
  }
  ```

#### 3.4 删除购物车商品

- **端点**: `/api/cart/items/{id}`
- **方法**: DELETE
- **描述**: 从购物车中删除商品
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "商品已从购物车中移除",
    "data": {
      "cart": {
        "subtotal": 799.00,
        "tax_amount": 0.00,
        "discount_amount": 0.00,
        "total": 799.00,
        "item_count": 1
      }
    }
  }
  ```

#### 3.5 清空购物车

- **端点**: `/api/cart/clear`
- **方法**: POST
- **描述**: 清空购物车中的所有商品
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "购物车已清空",
    "data": {
      "cart": {
        "subtotal": 0.00,
        "tax_amount": 0.00,
        "discount_amount": 0.00,
        "total": 0.00,
        "item_count": 0
      }
    }
  }
  ```

#### 3.6 应用优惠券

- **端点**: `/api/cart/apply-coupon`
- **方法**: POST
- **描述**: 应用优惠券到购物车
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **请求参数**:
  ```json
  {
    "coupon_code": "SUMMER20"
  }
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "优惠券已应用",
    "data": {
      "coupon": {
        "code": "SUMMER20",
        "discount_type": "percentage",
        "discount_value": 20.00,
        "expires_at": "2023-12-31T23:59:59.000000Z"
      },
      "cart": {
        "subtotal": 1698.00,
        "tax_amount": 0.00,
        "discount_amount": 339.60,
        "total": 1358.40,
        "item_count": 2
      }
    }
  }
  ```

#### 3.7 移除优惠券

- **端点**: `/api/cart/remove-coupon`
- **方法**: POST
- **描述**: 从购物车中移除优惠券
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "优惠券已移除",
    "data": {
      "cart": {
        "subtotal": 1698.00,
        "tax_amount": 0.00,
        "discount_amount": 0.00,
        "total": 1698.00,
        "item_count": 2
      }
    }
  }
  ```

#### 3.8 获取购物车计数

- **端点**: `/api/cart/count`
- **方法**: GET
- **描述**: 获取购物车中的商品数量，用于显示在导航栏
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "count": 2
    }
  }
  ```

### 4. 订单API

#### 4.1 创建订单

- **端点**: `/api/orders`
- **方法**: POST
- **描述**: 从购物车创建订单
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **请求参数**:
  ```json
  {
    "payment_method": "credit_card",
    "shipping_address": {
      "recipient_name": "张三",
      "phone": "13800138000",
      "province": "广东省",
      "city": "深圳市",
      "district": "南山区",
      "address": "科技园路1号",
      "postal_code": "518000"
    },
    "billing_address": {
      "recipient_name": "张三",
      "phone": "13800138000",
      "province": "广东省",
      "city": "深圳市",
      "district": "南山区",
      "address": "科技园路1号",
      "postal_code": "518000"
    },
    "coupon_code": "SUMMER20",
    "notes": "请在工作日送货"
  }
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "订单创建成功",
    "data": {
      "order": {
        "id": 1,
        "order_number": "ORD-20230101-0001",
        "customer_id": 1,
        "store_id": 1,
        "subtotal": 1698.00,
        "tax_amount": 0.00,
        "discount_amount": 339.60,
        "total_amount": 1358.40,
        "status": "pending",
        "payment_status": "pending",
        "payment_method": "credit_card",
        "shipping_address": {
          "recipient_name": "张三",
          "phone": "13800138000",
          "province": "广东省",
          "city": "深圳市",
          "district": "南山区",
          "address": "科技园路1号",
          "postal_code": "518000"
        },
        "billing_address": {
          "recipient_name": "张三",
          "phone": "13800138000",
          "province": "广东省",
          "city": "深圳市",
          "district": "南山区",
          "address": "科技园路1号",
          "postal_code": "518000"
        },
        "notes": "请在工作日送货",
        "created_at": "2023-01-01T00:00:00.000000Z",
        "items": [
          {
            "id": 1,
            "sales_order_id": 1,
            "product_id": 1,
            "product_name": "运动鞋A",
            "product_sku": "SP-001",
            "quantity": 1,
            "unit_price": 899.00,
            "discount_amount": 179.80,
            "tax_rate": 0.00,
            "tax_amount": 0.00,
            "subtotal": 719.20,
            "specifications": {
              "color": "黑色",
              "size": "42"
            }
          },
          {
            "id": 2,
            "sales_order_id": 1,
            "product_id": 2,
            "product_name": "运动鞋B",
            "product_sku": "SP-002",
            "quantity": 1,
            "unit_price": 799.00,
            "discount_amount": 159.80,
            "tax_rate": 0.00,
            "tax_amount": 0.00,
            "subtotal": 639.20,
            "specifications": {
              "color": "白色",
              "size": "40"
            }
          }
        ],
        "payment_url": "https://example.com/pay/1"
      }
    }
  }
  ```

#### 4.2 获取订单列表

- **端点**: `/api/orders`
- **方法**: GET
- **描述**: 获取当前用户的订单列表
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **请求参数**:
  - `page`: 页码（默认1）
  - `per_page`: 每页数量（默认10）
  - `status`: 订单状态筛选（可选）
  - `start_date`: 开始日期（可选）
  - `end_date`: 结束日期（可选）
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "orders": [
        {
          "id": 1,
          "order_number": "ORD-20230101-0001",
          "customer_id": 1,
          "total_amount": 1358.40,
          "status": "processing",
          "payment_status": "paid",
          "payment_method": "credit_card",
          "created_at": "2023-01-01T00:00:00.000000Z",
          "item_count": 2
        },
        {
          "id": 2,
          "order_number": "ORD-20230102-0001",
          "customer_id": 1,
          "total_amount": 899.00,
          "status": "completed",
          "payment_status": "paid",
          "payment_method": "alipay",
          "created_at": "2023-01-02T00:00:00.000000Z",
          "item_count": 1
        }
        // 更多订单...
      ],
      "pagination": {
        "total": 5,
        "per_page": 10,
        "current_page": 1,
        "last_page": 1,
        "from": 1,
        "to": 5
      }
    }
  }
  ```

#### 4.3 获取订单详情

- **端点**: `/api/orders/{id}`
- **方法**: GET
- **描述**: 获取订单详情
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "order": {
        "id": 1,
        "order_number": "ORD-20230101-0001",
        "customer_id": 1,
        "store_id": 1,
        "subtotal": 1698.00,
        "tax_amount": 0.00,
        "discount_amount": 339.60,
        "total_amount": 1358.40,
        "status": "processing",
        "payment_status": "paid",
        "payment_method": "credit_card",
        "shipping_address": {
          "recipient_name": "张三",
          "phone": "13800138000",
          "province": "广东省",
          "city": "深圳市",
          "district": "南山区",
          "address": "科技园路1号",
          "postal_code": "518000"
        },
        "billing_address": {
          "recipient_name": "张三",
          "phone": "13800138000",
          "province": "广东省",
          "city": "深圳市",
          "district": "南山区",
          "address": "科技园路1号",
          "postal_code": "518000"
        },
        "notes": "请在工作日送货",
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T01:00:00.000000Z",
        "items": [
          {
            "id": 1,
            "sales_order_id": 1,
            "product_id": 1,
            "product_name": "运动鞋A",
            "product_sku": "SP-001",
            "quantity": 1,
            "unit_price": 899.00,
            "discount_amount": 179.80,
            "tax_rate": 0.00,
            "tax_amount": 0.00,
            "subtotal": 719.20,
            "specifications": {
              "color": "黑色",
              "size": "42"
            },
            "product": {
              "id": 1,
              "images": ["url1.jpg"]
            }
          },
          {
            "id": 2,
            "sales_order_id": 1,
            "product_id": 2,
            "product_name": "运动鞋B",
            "product_sku": "SP-002",
            "quantity": 1,
            "unit_price": 799.00,
            "discount_amount": 159.80,
            "tax_rate": 0.00,
            "tax_amount": 0.00,
            "subtotal": 639.20,
            "specifications": {
              "color": "白色",
              "size": "40"
            },
            "product": {
              "id": 2,
              "images": ["url1.jpg"]
            }
          }
        ],
        "payments": [
          {
            "id": 1,
            "sales_order_id": 1,
            "amount": 1358.40,
            "payment_method": "credit_card",
            "status": "completed",
            "transaction_id": "TRX123456",
            "paid_at": "2023-01-01T00:30:00.000000Z"
          }
        ],
        "e_invoice": {
          "id": 1,
          "sales_order_id": 1,
          "invoice_number": "INV-20230101-0001",
          "status": "issued",
          "download_url": "https://example.com/invoices/INV-20230101-0001.pdf",
          "created_at": "2023-01-01T00:35:00.000000Z"
        }
      }
    }
  }
  ```

#### 4.4 取消订单

- **端点**: `/api/orders/{id}/cancel`
- **方法**: POST
- **描述**: 取消未处理的订单
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **请求参数**:
  ```json
  {
    "reason": "找到更合适的产品"
  }
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "订单已取消",
    "data": {
      "order": {
        "id": 1,
        "order_number": "ORD-20230101-0001",
        "status": "cancelled",
        "updated_at": "2023-01-01T02:00:00.000000Z"
      }
    }
  }
  ```

#### 4.5 获取订单状态

- **端点**: `/api/orders/{id}/status`
- **方法**: GET
- **描述**: 获取订单当前状态
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "order_status": "processing",
      "payment_status": "paid",
      "updated_at": "2023-01-01T01:00:00.000000Z"
    }
  }
  ```

#### 4.6 获取订单发票

- **端点**: `/api/orders/{id}/invoice`
- **方法**: GET
- **描述**: 获取订单发票信息
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "invoice": {
        "id": 1,
        "sales_order_id": 1,
        "invoice_number": "INV-20230101-0001",
        "status": "issued",
        "download_url": "https://example.com/invoices/INV-20230101-0001.pdf",
        "created_at": "2023-01-01T00:35:00.000000Z",
        "updated_at": "2023-01-01T00:35:00.000000Z"
      }
    }
  }
  ```

#### 4.7 申请退货

- **端点**: `/api/orders/{id}/return`
- **方法**: POST
- **描述**: 申请订单退货
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **请求参数**:
  ```json
  {
    "items": [
      {
        "sales_order_item_id": 1,
        "quantity": 1,
        "reason": "尺码不合适"
      }
    ],
    "return_method": "pickup",
    "notes": "请尽快处理"
  }
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "退货申请已提交",
    "data": {
      "return_request": {
        "id": 1,
        "sales_order_id": 1,
        "status": "pending",
        "return_method": "pickup",
        "notes": "请尽快处理",
        "created_at": "2023-01-05T00:00:00.000000Z",
        "items": [
          {
            "id": 1,
            "return_request_id": 1,
            "sales_order_item_id": 1,
            "quantity": 1,
            "reason": "尺码不合适",
            "status": "pending"
          }
        ]
      }
    }
  }
  ```

### 5. 支付API

#### 5.1 创建支付

- **端点**: `/api/payments`
- **方法**: POST
- **描述**: 为订单创建支付
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **请求参数**:
  ```json
  {
    "order_id": 1,
    "payment_method": "credit_card",
    "amount": 1358.40,
    "return_url": "https://example.com/payment/success",
    "cancel_url": "https://example.com/payment/cancel"
  }
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "payment": {
        "id": 1,
        "sales_order_id": 1,
        "amount": 1358.40,
        "payment_method": "credit_card",
        "status": "pending",
        "created_at": "2023-01-01T00:15:00.000000Z"
      },
      "payment_url": "https://payment-gateway.com/pay/xyz123"
    }
  }
  ```

#### 5.2 获取支付状态

- **端点**: `/api/payments/{id}`
- **方法**: GET
- **描述**: 获取支付状态
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "payment": {
        "id": 1,
        "sales_order_id": 1,
        "amount": 1358.40,
        "payment_method": "credit_card",
        "status": "completed",
        "transaction_id": "TRX123456",
        "paid_at": "2023-01-01T00:30:00.000000Z",
        "created_at": "2023-01-01T00:15:00.000000Z",
        "updated_at": "2023-01-01T00:30:00.000000Z"
      }
    }
  }
  ```

#### 5.3 支付回调

- **端点**: `/api/payments/callback`
- **方法**: POST
- **描述**: 支付网关回调接口
- **请求参数**: 根据支付网关的要求
- **响应**: 根据支付网关的要求

### 6. 用户评价API

#### 6.1 提交产品评价

- **端点**: `/api/products/{id}/reviews`
- **方法**: POST
- **描述**: 提交产品评价
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **请求参数**:
  ```json
  {
    "rating": 5,
    "title": "非常满意的购物体验",
    "content": "鞋子质量很好，穿着舒适，物流也很快，非常满意！",
    "order_id": 1,
    "images": ["base64_encoded_image_1", "base64_encoded_image_2"]
  }
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "评价提交成功",
    "data": {
      "review": {
        "id": 1,
        "product_id": 1,
        "customer_id": 1,
        "order_id": 1,
        "rating": 5,
        "title": "非常满意的购物体验",
        "content": "鞋子质量很好，穿着舒适，物流也很快，非常满意！",
        "images": ["url1.jpg", "url2.jpg"],
        "status": "published",
        "created_at": "2023-01-10T00:00:00.000000Z"
      }
    }
  }
  ```

#### 6.2 获取产品评价

- **端点**: `/api/products/{id}/reviews`
- **方法**: GET
- **描述**: 获取产品的评价列表
- **请求参数**:
  - `page`: 页码（默认1）
  - `per_page`: 每页数量（默认10）
  - `sort_by`: 排序字段（created_at, rating等）
  - `sort_direction`: 排序方向（asc, desc）
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "reviews": [
        {
          "id": 1,
          "product_id": 1,
          "customer_name": "张三",
          "rating": 5,
          "title": "非常满意的购物体验",
          "content": "鞋子质量很好，穿着舒适，物流也很快，非常满意！",
          "images": ["url1.jpg", "url2.jpg"],
          "created_at": "2023-01-10T00:00:00.000000Z"
        },
        {
          "id": 2,
          "product_id": 1,
          "customer_name": "李四",
          "rating": 4,
          "title": "不错的产品",
          "content": "质量不错，就是发货有点慢",
          "images": [],
          "created_at": "2023-01-09T00:00:00.000000Z"
        }
        // 更多评价...
      ],
      "summary": {
        "average_rating": 4.5,
        "total_reviews": 2,
        "rating_distribution": {
          "5": 1,
          "4": 1,
          "3": 0,
          "2": 0,
          "1": 0
        }
      },
      "pagination": {
        "total": 2,
        "per_page": 10,
        "current_page": 1,
        "last_page": 1,
        "from": 1,
        "to": 2
      }
    }
  }
  ```

#### 6.3 获取我的评价

- **端点**: `/api/customer/reviews`
- **方法**: GET
- **描述**: 获取当前用户的评价列表
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **请求参数**:
  - `page`: 页码（默认1）
  - `per_page`: 每页数量（默认10）
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "reviews": [
        {
          "id": 1,
          "product_id": 1,
          "product_name": "运动鞋A",
          "product_image": "url1.jpg",
          "order_id": 1,
          "rating": 5,
          "title": "非常满意的购物体验",
          "content": "鞋子质量很好，穿着舒适，物流也很快，非常满意！",
          "images": ["url1.jpg", "url2.jpg"],
          "status": "published",
          "created_at": "2023-01-10T00:00:00.000000Z"
        }
        // 更多评价...
      ],
      "pagination": {
        "total": 1,
        "per_page": 10,
        "current_page": 1,
        "last_page": 1,
        "from": 1,
        "to": 1
      }
    }
  }
  ```

#### 6.4 删除评价

- **端点**: `/api/customer/reviews/{id}`
- **方法**: DELETE
- **描述**: 删除用户自己的评价
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "评价已删除"
  }
  ```

### 7. 地址管理API

#### 7.1 获取地址列表

- **端点**: `/api/customer/addresses`
- **方法**: GET
- **描述**: 获取当前用户的地址列表
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "data": {
      "addresses": [
        {
          "id": 1,
          "customer_id": 1,
          "recipient_name": "张三",
          "phone": "13800138000",
          "province": "广东省",
          "city": "深圳市",
          "district": "南山区",
          "address": "科技园路1号",
          "postal_code": "518000",
          "is_default": true,
          "created_at": "2023-01-01T00:00:00.000000Z"
        },
        {
          "id": 2,
          "customer_id": 1,
          "recipient_name": "张三",
          "phone": "13800138000",
          "province": "广东省",
          "city": "广州市",
          "district": "天河区",
          "address": "天河路1号",
          "postal_code": "510000",
          "is_default": false,
          "created_at": "2023-01-02T00:00:00.000000Z"
        }
      ]
    }
  }
  ```

#### 7.2 添加地址

- **端点**: `/api/customer/addresses`
- **方法**: POST
- **描述**: 添加新地址
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **请求参数**:
  ```json
  {
    "recipient_name": "张三",
    "phone": "13800138000",
    "province": "广东省",
    "city": "深圳市",
    "district": "南山区",
    "address": "科技园路1号",
    "postal_code": "518000",
    "is_default": true
  }
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "地址添加成功",
    "data": {
      "address": {
        "id": 1,
        "customer_id": 1,
        "recipient_name": "张三",
        "phone": "13800138000",
        "province": "广东省",
        "city": "深圳市",
        "district": "南山区",
        "address": "科技园路1号",
        "postal_code": "518000",
        "is_default": true,
        "created_at": "2023-01-01T00:00:00.000000Z"
      }
    }
  }
  ```

#### 7.3 更新地址

- **端点**: `/api/customer/addresses/{id}`
- **方法**: PUT
- **描述**: 更新地址
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **请求参数**:
  ```json
  {
    "recipient_name": "张三",
    "phone": "13800138001",
    "province": "广东省",
    "city": "深圳市",
    "district": "南山区",
    "address": "科技园路2号",
    "postal_code": "518000",
    "is_default": true
  }
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "地址更新成功",
    "data": {
      "address": {
        "id": 1,
        "customer_id": 1,
        "recipient_name": "张三",
        "phone": "13800138001",
        "province": "广东省",
        "city": "深圳市",
        "district": "南山区",
        "address": "科技园路2号",
        "postal_code": "518000",
        "is_default": true,
        "updated_at": "2023-01-02T00:00:00.000000Z"
      }
    }
  }
  ```

#### 7.4 删除地址

- **端点**: `/api/customer/addresses/{id}`
- **方法**: DELETE
- **描述**: 删除地址
- **请求头**: 
  ```
  Authorization: Bearer {token}
  ```
- **响应**:
  ```json
  {
    "status": "success",
    "message": "地址删除成功"
  }
  ```

## 缺失功能分析

通过对现有系统的分析，我们发现以下功能需要在后端系统中开发或完善，以支持前端客户门户：

1. **客户认证API**：需要开发专门的客户注册、登录和认证API，与现有的管理员认证系统分开。

2. **产品搜索和筛选API**：现有的产品API主要面向管理后台，需要开发更适合客户使用的产品搜索和筛选API，支持多种筛选条件和排序方式。

3. **购物车API**：需要开发完整的购物车管理API，支持添加、更新、删除购物车商品等操作。

4. **地址管理API**：需要开发客户地址管理API，支持添加、更新、删除地址等操作。

5. **支付集成API**：需要开发支付网关集成API，支持多种支付方式。

6. **用户评价API**：需要开发产品评价API，支持客户提交评价和查看评价。

7. **个性化推荐API**：可以考虑开发基于用户行为的产品推荐API。

## 实施建议

1. **API版本控制**：为所有API添加版本控制，确保未来API变更不会影响现有客户端。

2. **统一响应格式**：所有API应使用统一的响应格式，包括状态码、消息和数据。

3. **认证机制**：使用JWT或OAuth2进行客户认证，确保API安全。

4. **数据验证**：对所有API请求进行严格的数据验证，防止恶意请求。

5. **错误处理**：实现统一的错误处理机制，提供清晰的错误消息。

6. **API文档**：使用Swagger或其他工具生成API文档，方便前端开发人员使用。

7. **性能优化**：对频繁访问的API进行缓存优化，提高响应速度。

## 结论

本文档详细分析了鞋类电商前端项目所需的API接口，包括认证、产品、购物车、订单、支付、评价和地址管理等方面。通过实现这些API接口，可以为客户提供完整的在线购物体验。

在实施过程中，需要注意API的安全性、性能和可维护性，确保系统能够稳定运行并支持未来的功能扩展。同时，应该与前端开发团队密切合作，确保API设计满足前端需求。 