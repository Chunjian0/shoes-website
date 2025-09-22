# 首页管理系统实现计划

## 1. 功能分析

根据提供的文档和现有代码，我们需要实现一个完整的首页管理系统，主要包括以下核心功能：

### 1.1 已实现的功能
- 首页部分管理（HomepageSection模型）：创建、编辑、删除、排序
- 首页设置管理
- 库存阈值设置和库存过滤
- 缓存管理

### 1.2 需要新增的功能
- **自动化管理功能**
  - 自动将新创建的产品添加到新品区域
  - 自动将打折产品添加到促销区域
  - 设定产品在新品区域显示的天数
  - 过期后自动从新品/促销区域移除产品

- **产品加载和过滤优化**
  - 一次性加载所有产品，前端进行过滤
  - 更灵活的产品筛选和排序功能
  - 支持按类别、状态、名称等多条件筛选

- **用户界面优化**
  - 更直观的产品管理界面
  - 拖拽排序替代上/下移动按钮
  - 更好的图片选择和预览功能
  - 用户友好的通知和提示

- **库存联动功能**
  - 首页产品库存自动过滤
  - 首页库存实时更新

- **首页更新通知功能**
  - 集成现有的通知系统与首页管理功能
  - 首页更新事件，触发相关人员通知
  - 特定条件自动通知功能（如低库存产品仍在首页展示）
  - 通知接收人配置界面，使用自定义select组件

## 2. 现有系统结构分析

### 2.1 核心服务类
- **HomepageService**: 提供首页内容管理相关功能
- **HomepageStockService**: 提供首页产品库存管理相关功能
- **HomepageCacheService**: 提供首页缓存管理相关功能

### 2.2 关键模型
- **HomepageSection**: 定义首页部分数据模型，类型包括hero, featured_products, new_arrivals, sale_products等
- **Product**: 产品模型，包含is_featured, is_new_arrival, is_sale等标记字段
- **Setting**: 设置模型，存储首页配置

### 2.3 数据结构
- **homepage_sections**: 存储首页各个区块的信息
- **products**: 存储产品信息，包含特色、新品、促销标记字段
- **settings**: 存储首页设置，如库存阈值、展示天数等

## 3. 开发任务拆分

### 任务1: 实现首页产品库存自动过滤系统
- 步骤1: 创建或修改HomepageStockService类，完善库存阈值管理功能
- 步骤2: 实现定时任务自动运行库存过滤
- 步骤3: 开发库存变化事件和监听器，实时更新首页产品状态
- 步骤4: 添加库存状态可视化标识

### 任务2: 实现首页更新自动通知系统
- 步骤1: 开发HomepageUpdateNotification类
- 步骤2: 实现HomepageUpdatedEvent事件类
- 步骤3: 创建事件监听器SendHomepageUpdateNotification
- 步骤4: 开发通知接收者配置界面
- 步骤5: 在关键操作处触发事件

### 任务3: 实现新品和促销产品自动化管理
- 步骤1: 开发AutomaticHomepageService服务类
- 步骤2: 实现新产品自动添加到新品区域的功能
- 步骤3: 实现特价产品自动添加到促销区域的功能
- 步骤4: 开发产品展示时间过期自动移除功能
- 步骤5: 创建管理界面设置自动化规则

### 任务4: 优化首页管理界面
- 步骤1: 实现产品加载和前端过滤功能
- 步骤2: 开发拖拽排序功能
- 步骤3: 改进图片选择和预览功能
- 步骤4: 优化用户通知和确认对话框

## 4. 开发优先级

1. 任务1: 实现首页产品库存自动过滤系统（高优先级）
2. 任务2: 实现首页更新自动通知系统（高优先级）
3. 任务3: 实现新品和促销产品自动化管理（中优先级）
4. 任务4: 优化首页管理界面（中优先级）

## 5. 当前系统存在的问题

1. **库存管理效率低下**：缺乏自动化机制，需要手动添加和移除低库存产品
2. **通知机制缺失**：缺乏首页更新的通知机制，管理员无法及时获知重要变更
3. **新品和促销产品管理繁琐**：需要手动添加和移除，工作量大且容易出错
4. **用户界面体验不佳**：现有界面缺乏直观性，操作流程繁琐

## 6. 技术实现参考

### 自动化管理功能
```php
// AutomaticHomepageService.php 示例代码
public function autoProcessNewProducts($days = null)
{
    $newProductDays = $days ?? $this->getNewProductDays();
    $products = Product::where('created_at', '>=', now()->subDays($newProductDays))
        ->where('is_active', true)
                ->where('is_new_arrival', false)
                ->get();
        
    foreach ($products as $product) {
        $product->is_new_arrival = true;
        $product->save();
    }
    
    return count($products);
}
```

### 事件系统
```php
// HomepageUpdatedEvent.php 示例代码
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

### 通知接收者配置界面
```html
<!-- 通知设置管理界面示例 -->
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
    </div>
</div>
``` 