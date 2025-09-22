# 首页管理系统 - 通知功能开发规划

## 1. 需求分析

根据提供的文档，我们需要开发首页管理系统的通知功能，主要实现：

- 首页更新自动通知
- 使用自定义select组件选择通知接收人
- 替换alert和confirm为更友好的UI组件
- 确保与Livewire兼容

## 2. 现有资源分析

### 数据库表结构
- 通知设置表(notification_settings): 存储不同类型的通知设置
- 通知接收人表(notification_receivers): 存储每种通知类型的接收人

### 可复用组件
- 自定义select组件: 文档中提供了样板代码
- Toast通知组件: 可替代alert
- 确认对话框组件: 可替代confirm

### 文档中的相关功能
- 首页更新事件系统
- 通知发送机制
- 用户友好的UI组件

## 3. 开发任务细分

### 任务3.1: 设计并实现通知系统后端
- 3.1.1: 创建通知类型管理服务
- 3.1.2: 实现通知接收人管理服务
- 3.1.3: 开发通知发送服务
- 3.1.4: 实现首页更新事件系统

### 任务3.2: 开发通知管理UI
- 3.2.1: 创建通知设置页面
- 3.2.2: 实现通知接收人选择组件
- 3.2.3: 开发通知接收人搜索功能
- 3.2.4: 实现已选接收人显示区域

### 任务3.3: 开发用户友好的通知UI组件
- 3.3.1: 实现Toast通知系统
- 3.3.2: 开发确认对话框组件
- 3.3.3: 替换系统中的alert和confirm

### 任务3.4: 集成与测试
- 3.4.1: 将通知系统与首页管理功能关联
- 3.4.2: 测试各种通知场景
- 3.4.3: 优化Livewire加载问题

## 4. 技术实现方案

### 4.1 服务层设计

#### NotificationSettingService
处理通知设置的管理，包括：
- 获取所有通知类型
- 获取指定类型的通知设置
- 更新通知设置
- 获取通知接收人

#### NotificationService
处理通知的发送，包括：
- 根据通知类型获取接收人
- 发送通知邮件
- 记录通知历史

### 4.2 控制器设计

#### NotificationSettingController
提供通知设置的管理接口，包括：
- 显示通知设置页面
- 更新通知接收人
- 获取用户列表（用于选择接收人）

### 4.3 UI组件设计

#### Toast通知系统
- 成功/错误/信息三种状态
- 自动消失
- 可手动关闭
- 多条通知堆叠显示

#### 确认对话框
- 自定义标题和消息
- 自定义确认和取消按钮文本
- 回调函数处理确认和取消事件

#### 接收人选择组件
- 可折叠的用户列表
- 搜索功能
- 全选/清空功能
- 已选用户显示区域

### 4.4 JavaScript实现

#### Toast通知模块
```javascript
// 示例代码结构
const ToastSystem = {
  show: function(message, type, duration) {
    // 创建并显示通知
  },
  success: function(message) {
    this.show(message, 'success');
  },
  error: function(message) {
    this.show(message, 'error');
  }
};
```

#### 确认对话框模块
```javascript
// 示例代码结构
const ConfirmDialog = {
  show: function(options) {
    // 创建并显示对话框
  }
};
```

#### 接收人选择器模块
```javascript
// 示例代码结构
function initRecipientSelectors() {
  // 初始化所有接收人选择器
  // 绑定切换显示事件
  // 绑定搜索功能
  // 绑定全选/清空功能
}

function updateSelectedRecipients(type) {
  // 更新已选接收人显示
}
```

## 5. 文件结构规划

### 控制器
- `app/Http/Controllers/NotificationSettingController.php`

### 服务
- `app/Services/NotificationSettingService.php`
- `app/Services/NotificationService.php`

### 视图
- `resources/views/admin/notifications/settings.blade.php`
- `resources/views/components/notification-receiver-selector.blade.php`

### JavaScript
- `public/js/notification-system.js`

## 6. 开发步骤详细规划

### 步骤1: 创建通知服务层
- 创建NotificationSettingService
- 创建NotificationService
- 实现获取和更新通知设置的方法
- 实现获取通知接收人的方法

### 步骤2: 创建控制器
- 创建NotificationSettingController
- 实现显示设置页面的方法
- 实现更新通知接收人的方法
- 实现获取用户列表的方法

### 步骤3: 创建通知设置页面
- 创建settings.blade.php
- 添加通知类型列表
- 为每种通知类型添加接收人选择器
- 使用自定义select组件

### 步骤4: 实现JavaScript功能
- 创建Toast通知系统
- 创建确认对话框系统
- 实现接收人选择器功能
- 实现搜索和过滤功能

### 步骤5: 集成与首页管理系统
- 在首页更新操作中触发通知事件
- 确保通知在各种操作后正确发送
- 替换现有的alert和confirm

### 步骤6: 测试与优化
- 测试通知设置的保存和加载
- 测试通知发送功能
- 优化Livewire加载问题
- 解决可能的冲突

## 7. UI设计规划

### 通知设置页面
- 页面标题和说明
- 通知类型列表，每种类型包含：
  - 图标和标题
  - 接收人选择按钮
  - 折叠式接收人选择器
  - 已选接收人显示区域

### Toast通知
- 右上角显示
- 成功状态：绿色背景
- 错误状态：红色背景
- 包含图标、消息和关闭按钮

### 确认对话框
- 居中显示模态框
- 包含标题、消息
- 确认和取消按钮
- 背景遮罩可点击关闭

## 8. 与现有系统的集成点

- 首页管理操作后触发通知
- 低库存首页产品检测后触发通知
- 各种更新操作使用Toast通知替代alert
- 删除操作使用确认对话框替代confirm

## 9. 特别注意事项

- 确保JavaScript代码与Livewire兼容
- 使用IIFE和初始化标记防止重复初始化
- 避免使用Alpine.js，使用原生JavaScript
- 确保Toast通知在各种场景下正确显示
- 保证接收人选择器在各种屏幕尺寸下正常工作 