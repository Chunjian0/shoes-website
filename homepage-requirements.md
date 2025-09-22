# 首页管理系统需求分析文档

## 1. 系统概述

首页管理系统是眼镜商城的重要组成部分，负责管理网站首页的内容展示，包括横幅设置、特色产品、新品和促销产品的管理。系统通过直观的用户界面，允许管理员配置首页各个区域的内容，提高网站的吸引力和促销效果。

## 2. 现有功能分析

### 2.1 系统架构

当前系统采用Laravel框架开发，前后端分离架构：
- 后端：PHP Laravel API提供数据服务
- 前端：JavaScript + Blade模板渲染用户界面
- 数据存储：MySQL数据库

### 2.2 功能模块

#### 2.2.1 横幅(Banner)管理
- 设置横幅标题和副标题
- 设置横幅按钮文本和链接
- 上传和更新横幅图片

#### 2.2.2 特色产品管理
- 从产品库中选择产品添加为特色产品
- 调整特色产品显示顺序
- 移除特色产品
- 设置产品显示图片

#### 2.2.3 新品管理
- 显示和管理新品列表
- 添加和移除新品
- 设置新品显示图片
- 支持产品搜索和分类筛选

#### 2.2.4 促销产品管理
- 显示和管理促销产品列表
- 添加和移除促销产品
- 设置促销产品显示图片
- 支持产品搜索和分类筛选

### 2.3 数据结构

#### 2.3.1 主要表结构
- `settings`: 存储首页设置，如横幅内容、展示天数等
- `products`: 产品信息，包含特色、新品、促销标记字段

#### 2.3.2 关键字段
- `banner_title`, `banner_subtitle`: 横幅文本内容
- `banner_button_text`, `banner_button_link`: 横幅按钮设置
- `banner_image`: 横幅图片路径
- `is_featured`, `featured_order`: 特色产品标记和排序
- `is_new_arrival`, `new_order`: 新品标记和排序
- `is_sale`, `sale_order`: 促销产品标记和排序
- `new_image_index`, `sale_image_index`: 产品图片索引

### 2.4 API接口

#### 2.4.1 Web路由
- `GET admin/homepage`: 显示管理界面
- `POST admin/homepage/update`: 更新首页设置
- `GET admin/homepage/new-arrivals`: 获取新品列表
- `GET admin/homepage/sale-products`: 获取促销产品列表
- `GET admin/homepage/available-products`: 获取可用产品列表

#### 2.4.2 API路由
- `GET api/homepage/settings`: 获取首页设置
- `GET api/homepage/featured-products`: 获取特色产品
- `POST api/homepage/featured-products`: 更新特色产品
- `DELETE api/homepage/featured-products/{id}`: 移除特色产品

## 3. 用户需求分析

### 3.1 核心用户需求

1. **自动化管理需求**
   - 自动将新创建的产品添加到新品区域
   - 自动将打折产品添加到促销区域
   - 设定产品在新品区域显示的天数
   - 过期后自动从新品/促销区域移除产品

2. **产品加载和过滤需求**
   - 一次性加载所有产品，前端进行过滤
   - 更灵活的产品筛选和排序功能
   - 支持按类别、状态、名称等多条件筛选

3. **用户界面优化需求**
   - 更直观的产品管理界面
   - 拖拽排序替代上/下移动按钮
   - 更好的图片选择和预览功能
   - 用户友好的通知和提示

### 3.2 用户痛点

1. **管理效率低下**
   - 需要手动添加和移除产品，工作量大
   - 当产品数量多时，管理繁琐且容易出错
   - 缺乏批量操作功能

2. **技术问题**
   - JavaScript错误导致功能不稳定
   - 产品加载慢，API调用次数多
   - 对大量产品的处理性能不佳

3. **用户体验问题**
   - 错误提示不友好，缺乏明确指引
   - 产品图片选择机制不直观
   - 界面响应速度慢，操作流程繁琐

## 4. 需求优先级和改进建议

### 4.1 高优先级需求

1. **修复现有功能缺陷**
   - 解决产品加载和显示中的JavaScript错误
   - 修复通知系统(Toast)显示问题
   - 确保新品和促销产品功能正常工作

2. **实现自动管理功能**
   - 开发新品自动添加和移除机制
   - 开发促销产品自动添加和移除机制
   - 创建定时任务执行管理操作

3. **优化产品加载方式**
   - 实现一次性加载所有产品的API
   - 开发前端产品过滤和分页功能
   - 提高页面响应速度

### 4.2 中优先级需求

1. **界面与用户体验优化**
   - 重新设计产品选择器界面
   - 添加更清晰的状态指示器
   - 优化产品图片选择体验

### 4.3 低优先级需求

1. **高级功能扩展**
   - 产品展示规则的自定义设置
   - 与其他系统的集成(如库存、订单)
   - 前端代码的模块化重构

## 5. 功能完善与联动计划

基于对现有鞋类电商系统架构的理解，首页管理系统可以与多个系统功能进行联动，以提升用户体验和管理效率。以下是具体的功能完善与联动计划，按任务和步骤进行组织。

### 5.1 库存系统联动

**目标**：将首页产品展示与库存系统紧密结合，避免推广缺货产品。

#### 任务 5.1.1：首页产品库存自动过滤
- 步骤1：开发库存检查服务，设置最低展示库存阈值
- 步骤2：实现自动从首页移除低库存/无库存产品的功能
- 步骤3：为管理员提供库存阈值设置界面
- 步骤4：添加库存状态可视化标识，在管理页面显示产品库存状态

#### 任务 5.1.2：首页库存实时更新
- 步骤1：实现库存变动事件系统
- 步骤2：创建库存变动监听器，触发首页产品状态更新
- 步骤3：开发首页产品库存缓存刷新机制

### 5.3 活动与促销系统联动

**目标**：实现首页与营销活动系统的自动联动，提高促销效果。

#### 任务 5.3.1：促销活动自动同步
- 步骤1：开发活动管理与首页的集成接口
- 步骤2：实现新促销活动自动添加到首页促销区域的功能
- 步骤3：创建活动倒计时和库存状态实时显示功能
- 步骤4：设计活动产品在首页的突出显示样式


#### 任务 5.5.1：首页更新自动通知
- 步骤1：集成现有的通知系统与首页管理功能
- 步骤2：开发首页更新事件，触发相关人员通知
- 步骤3：实现特定条件自动通知功能（如低库存产品仍在首页展示）
- 步骤4：创建通知接收人配置界面，使用自定义select组件


### 5.6 技术架构优化

**目标**：优化首页管理系统的技术实现，提高系统性能和用户体验。

#### 任务 5.6.1：Livewire加载优化
- 步骤1：重构JavaScript初始化代码，使用IIFE和初始化标记
- 步骤2：实现基于turbolinks:load事件的页面初始化
- 步骤3：优化DOM元素选择器和事件绑定机制
- 步骤4：添加页面状态持久化功能，减少重新加载开销

#### 任务 5.6.2：Redis缓存系统集成
- 步骤1：设计首页数据缓存策略
- 步骤2：实现服务器端Redis缓存机制
- 步骤3：开发前端缓存与后端缓存的同步机制
- 步骤4：创建缓存预热和自动刷新功能

### 5.7 界面与体验优化

**目标**：提升首页管理系统的用户界面和交互体验。

#### 任务 5.7.1：用户友好的UI改进
- 步骤1：使用Tailwind CSS重新设计界面组件
- 步骤2：实现拖拽排序功能，替代上/下移动按钮
- 步骤3：开发更直观的产品选择器
- 步骤4：创建更友好的通知和确认对话框，替换所有alert和confirm

#### 任务 5.7.2：多设备预览功能
- 步骤1：开发响应式预览系统，支持桌面、平板和移动设备
- 步骤2：实现实时预览功能，编辑时同步显示效果
- 步骤3：创建不同设备的布局自动调整功能
- 步骤4：添加针对不同设备的性能测试工具

## 6. 实施优先级与时间线

以上功能完善与联动任务按以下优先级实施：

### 高优先级（1-2个月）
- 任务5.1.1：首页产品库存自动过滤
- 任务5.5.1：首页更新自动通知
- 任务5.6.1：Livewire加载优化
- 任务5.7.1：用户友好的UI改进

### 中优先级（3-4个月）
- 任务5.3.1：促销活动自动同步
- 任务5.6.2：Redis缓存系统集成

## 7. 技术实现指南

本节提供优先级最高任务的具体技术实现指南，包括代码示例、数据结构和实现思路。

### 7.1 任务5.1.1：首页产品库存自动过滤

#### 7.1.1 数据库修改

添加库存阈值设置到settings表：

```sql
ALTER TABLE settings ADD COLUMN homepage_min_stock_threshold INT DEFAULT 5;
```

#### 7.1.2 服务层实现

创建库存检查服务：

```php
<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StockCheckService
{
    public function getMinStockThreshold()
    {
        return Cache::remember('homepage_min_stock_threshold', 3600, function () {
            return Setting::where('key', 'homepage_min_stock_threshold')->first()->value ?? 5;
        });
    }
    
    public function filterLowStockProducts()
    {
        $threshold = $this->getMinStockThreshold();
        $lowStockProducts = [];
        
        // 检查特色产品
        $featuredProducts = Product::where('is_featured', true)->get();
        foreach ($featuredProducts as $product) {
            if ($product->stock < $threshold) {
                $product->is_featured = false;
                $product->save();
                $lowStockProducts[] = $product;
                Log::info("Auto-removed featured product due to low stock: {$product->name}");
            }
        }
        
        // 检查新品
        $newProducts = Product::where('is_new_arrival', true)->get();
        foreach ($newProducts as $product) {
            if ($product->stock < $threshold) {
                $product->is_new_arrival = false;
                $product->save();
                $lowStockProducts[] = $product;
                Log::info("Auto-removed new arrival product due to low stock: {$product->name}");
            }
        }
        
        // 检查促销产品
        $saleProducts = Product::where('is_sale', true)->get();
        foreach ($saleProducts as $product) {
            if ($product->stock < $threshold) {
                $product->is_sale = false;
                $product->save();
                $lowStockProducts[] = $product;
                Log::info("Auto-removed sale product due to low stock: {$product->name}");
            }
        }
        
        return $lowStockProducts;
    }
}
```

#### 7.1.3 计划任务实现

在`App\Console\Kernel.php`中添加定时任务：

```php
protected function schedule(Schedule $schedule)
{
    // 每天凌晨2点执行库存检查
    $schedule->call(function () {
        app(StockCheckService::class)->filterLowStockProducts();
    })->dailyAt('02:00');
}
```

#### 7.1.4 管理界面实现

在管理界面添加库存阈值设置：

```html
<div class="card">
    <div class="card-header">
        <h3 class="card-title">库存阈值设置</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            <div class="form-group">
                <label for="homepage_min_stock_threshold">首页产品最低库存阈值</label>
                <input type="number" class="form-control" id="homepage_min_stock_threshold" 
                       name="homepage_min_stock_threshold" 
                       value="{{ $settings['homepage_min_stock_threshold'] ?? 5 }}">
                <small class="form-text text-muted">
                    库存低于此阈值的产品将自动从首页移除
                </small>
            </div>
            <button type="submit" class="btn btn-primary">保存设置</button>
        </form>
    </div>
</div>
```

### 7.2 任务5.5.1：首页更新自动通知

#### 7.2.1 数据库修改

创建通知设置表：

```sql
CREATE TABLE notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE notification_receivers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_setting_id INT,
    email VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_setting_id) REFERENCES notification_settings(id) ON DELETE CASCADE
);
```

#### 7.2.2 事件系统实现

创建首页更新事件：

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HomepageUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $updateType;
    public $updatedBy;
    public $data;

    public function __construct($updateType, $updatedBy, $data = [])
    {
        $this->updateType = $updateType;
        $this->updatedBy = $updatedBy;
        $this->data = $data;
    }
}
```

创建监听器：

```php
<?php

namespace App\Listeners;

use App\Events\HomepageUpdatedEvent;
use App\Models\NotificationSetting;
use App\Notifications\HomepageUpdatedNotification;
use Illuminate\Support\Facades\Notification;

class SendHomepageUpdateNotification
{
    public function handle(HomepageUpdatedEvent $event)
    {
        $notificationSetting = NotificationSetting::where('type', $event->updateType)
            ->with('receivers')
            ->first();
            
        if ($notificationSetting && count($notificationSetting->receivers) > 0) {
            $emails = $notificationSetting->receivers->pluck('email')->toArray();
            Notification::route('mail', $emails)
                ->notify(new HomepageUpdatedNotification($event->updateType, $event->updatedBy, $event->data));
        }
    }
}
```

#### 7.2.3 控制器实现

更新HomepageController中的方法：

```php
public function updateFeaturedProducts(Request $request)
{
    // 现有产品更新逻辑...
    
    // 触发事件
    event(new HomepageUpdatedEvent('featured_products', auth()->user()->email, [
        'count' => count($request->products),
        'action' => 'update'
    ]));
    
    return response()->json(['success' => true]);
}
```

#### 7.2.4 通知接收者配置界面

创建通知设置管理界面：

```html
<div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
    <div class="p-4">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <h3 class="text-base font-medium text-gray-900">首页更新通知</h3>
                </div>
            </div>
            <div>
                <button type="button" class="text-blue-600 hover:text-blue-800 text-sm font-medium focus:outline-none toggle-recipients" data-type="homepage_updated">
                    选择接收人
                    <svg class="inline-block ml-1 w-4 h-4 toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
        </div>
        <!-- 接收者选择部分 -->
        <!-- 已经选择的接收者 -->
    </div>
</div>
```

### 7.3 任务5.6.1：Livewire加载优化

#### 7.3.1 JavaScript优化

修改Livewire组件的JavaScript初始化代码：

```javascript
// 使用IIFE和初始化标记
(function() {
    // 存储已初始化的状态
    if (window.homepageManagerInitialized) return;
    
    // 监听多种页面加载事件
    function initHomepageManager() {
        if (window.homepageManagerInitialized) return;
        
        // 初始化产品选择器
        initProductSelector();
        
        // 初始化拖拽排序
        initDragSort();
        
        // 标记为已初始化
        window.homepageManagerInitialized = true;
        console.log('Homepage manager initialized');
    }
    
    // 监听各种可能的加载事件
    document.addEventListener('DOMContentLoaded', initHomepageManager);
    document.addEventListener('turbolinks:load', initHomepageManager);
    document.addEventListener('livewire:load', initHomepageManager);
    
    // 如果DOM已经加载完成，立即初始化
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(initHomepageManager, 1);
    }
    
    // 产品选择器初始化函数
    function initProductSelector() {
        // 安全地获取DOM元素
        const selectors = document.querySelectorAll('.product-selector');
        if (!selectors.length) return;
        
        selectors.forEach(selector => {
            // 避免重复初始化
            if (selector.dataset.initialized === 'true') return;
            
            // 初始化代码
            // ...
            
            // 标记为已初始化
            selector.dataset.initialized = 'true';
        });
    }
    
    // 拖拽排序初始化函数
    function initDragSort() {
        // 实现代码
        // ...
    }
})();
```

#### 7.3.2 Livewire组件优化

优化Livewire组件加载：

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class FeaturedProductsManager extends Component
{
    public $products = [];
    public $isLoading = true;
    
    // 使用mount而不是在render中加载数据
    public function mount()
    {
        $this->loadProducts();
    }
    
    public function loadProducts()
    {
        $this->isLoading = true;
        
        // 使用缓存减少数据库查询
        $this->products = Cache::remember('featured_products', 60, function () {
            return Product::where('is_featured', true)
                ->orderBy('featured_order')
                ->get()
                ->toArray();
        });
        
        $this->isLoading = false;
    }
    
    public function render()
    {
        return view('livewire.featured-products-manager');
    }
    
    // 当组件在Livewire环境中更新时
    public function updated($name, $value)
    {
        // 清除缓存，确保数据一致性
        Cache::forget('featured_products');
    }
}
```

### 7.4 任务5.7.1：用户友好的UI改进

#### 7.4.1 友好的通知提示组件

替换所有alert和confirm：

```javascript
// Toast通知系统
const ToastSystem = {
    success: function(message, duration = 3000) {
        this.show(message, 'success', duration);
    },
    
    error: function(message, duration = 5000) {
        this.show(message, 'error', duration);
    },
    
    show: function(message, type, duration) {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg max-w-sm transform transition-all duration-300 ease-in-out translate-y-0 opacity-100 ${
            type === 'success' ? 'bg-green-50 border-l-4 border-green-400' : 'bg-red-50 border-l-4 border-red-400'
        }`;
        
        toast.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    ${type === 'success' 
                        ? '<svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>'
                        : '<svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>'
                    }
                </div>
                <div class="ml-3">
                    <p class="text-sm ${type === 'success' ? 'text-green-800' : 'text-red-800'}">${message}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button class="inline-flex rounded-md p-1.5 ${type === 'success' ? 'text-green-500 hover:bg-green-100' : 'text-red-500 hover:bg-red-100'} focus:outline-none">
                            <span class="sr-only">关闭</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // 添加点击关闭事件
        toast.querySelector('button').addEventListener('click', function() {
            removeToast();
        });
        
        // 设置自动消失
        setTimeout(removeToast, duration);
        
        function removeToast() {
            toast.classList.replace('translate-y-0', '-translate-y-12');
            toast.classList.replace('opacity-100', 'opacity-0');
            
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }
    }
};

// 确认对话框
const ConfirmDialog = {
    show: function(options) {
        const defaults = {
            title: '确认操作',
            message: '您确定要执行此操作吗？',
            confirmText: '确认',
            cancelText: '取消',
            confirmCallback: () => {},
            cancelCallback: () => {}
        };
        
        const settings = {...defaults, ...options};
        
        // 创建对话框元素
        const dialog = document.createElement('div');
        dialog.className = 'fixed inset-0 z-50 overflow-y-auto';
        dialog.setAttribute('role', 'dialog');
        dialog.setAttribute('aria-modal', 'true');
        
        dialog.innerHTML = `
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">${settings.title}</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">${settings.message}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm" id="confirm-button">
                            ${settings.confirmText}
                        </button>
                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" id="cancel-button">
                            ${settings.cancelText}
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(dialog);
        
        // 绑定事件
        dialog.querySelector('#confirm-button').addEventListener('click', function() {
            document.body.removeChild(dialog);
            settings.confirmCallback();
        });
        
        dialog.querySelector('#cancel-button').addEventListener('click', function() {
            document.body.removeChild(dialog);
            settings.cancelCallback();
        });
        
        // 点击背景关闭
        dialog.querySelector('.bg-opacity-75').addEventListener('click', function() {
            document.body.removeChild(dialog);
            settings.cancelCallback();
        });
    }
};

// 替换alert
window.alert = function(message) {
    ToastSystem.info(message);
};

// 使用示例 - 替换confirm
function deleteProduct(id) {
    // 替换 if(confirm('确定要删除吗？')) {
    ConfirmDialog.show({
        title: '删除产品',
        message: '您确定要从首页删除此产品吗？此操作无法撤销。',
        confirmCallback: function() {
            // 执行删除操作
            axios.delete(`/api/homepage/featured-products/${id}`)
                .then(response => {
                    ToastSystem.success('产品已成功从首页移除');
                    // 刷新列表
                    window.Livewire.emit('refreshFeaturedProducts');
                })
                .catch(error => {
                    ToastSystem.error('删除失败：' + (error.response?.data?.message || '未知错误'));
                });
        }
    });
}
```

#### 7.4.2 拖拽排序实现

实现拖拽排序替代上/下移动按钮：

```javascript
function initDragSort() {
    const sortableList = document.querySelector('.sortable-products');
    if (!sortableList) return;
    
    // 避免重复初始化
    if (sortableList.dataset.sortableInitialized === 'true') return;
    
    // 使用Sortable.js库
    new Sortable(sortableList, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'bg-blue-50',
        onEnd: function(evt) {
            // 获取新的排序
            const items = Array.from(sortableList.querySelectorAll('[data-product-id]'));
            const productIds = items.map(item => item.dataset.productId);
            
            // 发送到服务器更新排序
            axios.post('/api/homepage/featured-products/reorder', {
                product_ids: productIds
            })
            .then(response => {
                ToastSystem.success('产品顺序已更新');
            })
            .catch(error => {
                ToastSystem.error('更新顺序失败：' + (error.response?.data?.message || '未知错误'));
                // 如果失败，刷新页面恢复原始顺序
                window.Livewire.emit('refreshFeaturedProducts');
            });
        }
    });
    
    sortableList.dataset.sortableInitialized = 'true';
}
```

## 8. 结论与下一步

首页管理系统是眼镜商城的核心功能之一，直接影响用户体验和销售转化。通过实施提出的改进方案，可以显著提高系统的自动化程度、易用性和稳定性，减轻管理人员的工作负担，同时提升网站的整体用户体验和商业价值。

建议从修复当前系统的关键问题开始，逐步实施自动化管理功能，最后完善用户体验和高级功能，以保证系统的稳定性和可用性。 