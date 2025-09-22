# 通知历史页面优化计划 - 第2阶段

## 当前问题

1. ✅ Toggle Form 不可点击
2. ✅ 用户数据获取问题
3. ✅ NotificationViewer undefined 错误
4. ❌ Recipient Filter 自动关闭问题
5. ❌ Recipient Filter 响应慢
6. ✅ JavaScript 变量重复声明错误: `Uncaught SyntaxError: Failed to set the 'body' property on 'Document': Identifier 'newStyles' has already been declared`
7. ✅ Test Email Recipient 显示用户正确，但 Recipient Filter 不显示
8. ✅ 需要添加通知类型发送模板功能

## 优先级排序

1. ✅ NotificationViewer undefined 错误 (高优先级 - 阻止页面功能)
2. ✅ Toggle Form 点击问题 (高优先级 - 阻止测试邮件功能)
3. ✅ 用户数据获取问题 (高优先级 - 影响接收者选择)
4. ✅ JavaScript 变量重复声明错误 (高优先级 - 导致页面错误)
5. ✅ Recipient Filter 显示问题 (中优先级 - 影响用户体验)
6. ✅ 添加通知类型发送模板功能 (中优先级 - 新功能)
7. ❌ Recipient Filter 自动关闭问题 (低优先级 - 影响用户体验)
8. ❌ Recipient Filter 响应慢 (低优先级 - 影响用户体验)

## 任务1: 修复NotificationViewer未定义错误 ✓
### 问题分析
错误 `Uncaught ReferenceError: NotificationViewer is not defined` 表明在尝试创建 `NotificationViewer` 类的实例时，该类尚未定义。

通过检查代码，发现以下问题：
1. 在初始化函数 `initNotificationHistory` 中尝试创建 `NotificationViewer` 类的实例
2. 但 `NotificationViewer` 类的定义不存在或未正确加载
3. 在Livewire环境中，JavaScript的加载顺序可能与预期不同

### 详细步骤
1. [✓] 在IIFE中定义NotificationViewer类
```javascript
(function() {
    // 跟踪初始化状态，防止在Livewire环境中重复初始化
    if (window.notificationHistoryInitialized) return;
    window.notificationHistoryInitialized = true;
    
    // 定义NotificationViewer类
    class NotificationViewer {
        constructor() {
            this.modal = document.getElementById('notification-modal');
            this.bindEvents();
        }
        
        bindEvents() {
            // 使用事件委托绑定View按钮点击事件
            document.addEventListener('click', (e) => {
                const viewBtn = e.target.closest('.view-notification');
                if (viewBtn) {
                    this.showNotification(viewBtn);
                }
            });
            
            // 绑定关闭按钮事件
            document.querySelectorAll('.close-modal').forEach(btn => {
                btn.addEventListener('click', () => this.hideModal());
            });
        }
        
        showNotification(button) {
            const id = button.getAttribute('data-id');
            const content = button.getAttribute('data-content');
            
            // 获取通知行数据
            const row = button.closest('tr');
            const subject = row.querySelector('td:nth-child(4)').textContent.trim();
            const recipient = row.querySelector('td:nth-child(3)').textContent.trim();
            const status = row.querySelector('td:nth-child(5) span').textContent.trim();
            
            // 填充模态框内容
            document.getElementById('modal-subject').textContent = subject;
            document.getElementById('modal-content').innerHTML = content;
            document.getElementById('modal-recipient').textContent = recipient;
            document.getElementById('modal-status').textContent = status;
            
            // 显示模态框
            this.showModal();
        }
        
        showModal() {
            document.body.classList.add('modal-open');
            this.modal.classList.remove('hidden');
            this.modal.classList.add('show');
        }
        
        hideModal() {
            this.modal.classList.remove('show');
            this.modal.classList.add('hidden');
            document.body.classList.remove('modal-open');
        }
    }
    
    // 定义RecipientFilter类
    class RecipientFilter {
        constructor() {
            this.bindEvents();
        }
        
        bindEvents() {
            // 绑定接收者筛选器事件
            document.querySelectorAll('.toggle-recipients').forEach(btn => {
                btn.addEventListener('click', this.toggleRecipientSelector);
            });
            
            // 绑定搜索事件
            document.querySelectorAll('.recipient-search').forEach(input => {
                input.addEventListener('input', this.handleSearch);
            });
            
            // 绑定全选/清空按钮事件
            document.querySelectorAll('.select-all-btn').forEach(btn => {
                btn.addEventListener('click', this.selectAll);
            });
            
            document.querySelectorAll('.deselect-all-btn').forEach(btn => {
                btn.addEventListener('click', this.deselectAll);
            });
            
            // 点击外部关闭下拉菜单
            document.addEventListener('click', this.handleOutsideClick.bind(this));
        }
        
        toggleRecipientSelector(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const type = this.getAttribute('data-type');
            const selector = document.getElementById(`selector-${type}`);
            
            if (selector) {
                selector.classList.toggle('hidden');
                
                // 切换图标旋转
                const icon = this.querySelector('.toggle-icon');
                if (icon) {
                    icon.style.transition = 'transform 0.2s ease';
                    icon.style.transform = selector.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
                }
            }
        }
        
        handleSearch() {
            const type = this.getAttribute('data-type');
            const query = this.value.toLowerCase();
            const userItems = document.querySelectorAll(`.user-list[data-type="${type}"] .user-item`);
            
            userItems.forEach(item => {
                const name = item.querySelector('.text-gray-700').textContent.toLowerCase();
                const email = item.querySelector('.text-gray-500').textContent.toLowerCase();
                
                if (name.includes(query) || email.includes(query)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        selectAll() {
            const type = this.getAttribute('data-type');
            const checkboxes = document.querySelectorAll(`.user-list[data-type="${type}"] input[type="checkbox"]`);
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
                // 触发change事件以更新选中状态
                checkbox.dispatchEvent(new Event('change'));
            });
        }
        
        deselectAll() {
            const type = this.getAttribute('data-type');
            const checkboxes = document.querySelectorAll(`.user-list[data-type="${type}"] input[type="checkbox"]`);
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
                // 触发change事件以更新选中状态
                checkbox.dispatchEvent(new Event('change'));
            });
        }
        
        handleOutsideClick(e) {
            const openSelectors = document.querySelectorAll('.recipient-selector:not(.hidden)');
            
            openSelectors.forEach(selector => {
                const toggleButton = document.querySelector(`.toggle-recipients[data-type="${selector.id.replace('selector-', '')}"]`);
                
                if (!selector.contains(e.target) && 
                    (!toggleButton || !toggleButton.contains(e.target))) {
                    selector.classList.add('hidden');
                    
                    // 重置图标
                    if (toggleButton) {
                        const icon = toggleButton.querySelector('.toggle-icon');
                        if (icon) {
                            icon.style.transform = 'rotate(0deg)';
                        }
                    }
                }
            });
        }
    }
    
    // 在DOM准备好或使用Livewire兼容事件初始化
    document.addEventListener('DOMContentLoaded', initNotificationHistory);
    document.addEventListener('livewire:load', initNotificationHistory);
    document.addEventListener('livewire:navigated', initNotificationHistory);
    
    function initNotificationHistory() {
        // 检查特定DOM实例是否已初始化
        const container = document.querySelector('.notification-history-container');
        if (container && container.dataset.initialized === 'true') return;
        if (container) container.dataset.initialized = 'true';
        
        console.log('Initializing notification history...');
        
        // 初始化通知查看器和接收者筛选器
        new NotificationViewer();
        new RecipientFilter();
        
        // 初始化Toggle Form按钮
        initToggleForm();
    }
    
    function initToggleForm() {
        const toggleBtn = document.getElementById('toggle-test-email');
        const container = document.querySelector('.test-email-container');
        
        if (toggleBtn && container) {
            toggleBtn.addEventListener('click', function() {
                container.classList.toggle('hidden');
            });
        }
    }
})();
```

## 任务2: 修复Toggle Form无法点击的问题 ✓
### 问题分析
点击"Toggle Form"按钮没有反应，无法显示测试邮件表单。通过检查代码，发现以下问题：

1. 没有为Toggle Form按钮添加事件监听器
2. 在Livewire环境中，事件监听器可能没有正确绑定
3. 可能存在JavaScript错误阻止了事件处理

### 详细步骤
1. [✓] 添加Toggle Form按钮的事件监听器
```javascript
function initToggleForm() {
    const toggleBtn = document.getElementById('toggle-test-email');
    const container = document.querySelector('.test-email-container');
    
    if (toggleBtn && container) {
        console.log('Adding click event to toggle form button');
        toggleBtn.addEventListener('click', function() {
            console.log('Toggle form button clicked');
            container.classList.toggle('hidden');
        });
    } else {
        console.log('Toggle form button or container not found', { toggleBtn, container });
    }
}
```

2. [✓] 确保在页面加载完成后初始化Toggle Form按钮
```javascript
// 在initNotificationHistory函数中添加
function initNotificationHistory() {
    // 其他初始化代码...
    
    // 初始化Toggle Form按钮
    initToggleForm();
}
```

## 任务3: 修复用户数据获取问题 ✓
### 问题分析
页面无法正确获取用户数据，可能是后端传递问题。通过检查代码，发现以下问题：

1. 在SettingsController的notificationHistory方法中，已经尝试获取用户数据：
```php
// Get all users for the recipient selector
$users = \App\Models\User::select('id', 'name', 'email')->get();
```

2. 但在视图中，可能存在条件渲染问题：
```php
@foreach($users ?? [] as $user)
```

3. 可能是由于异常处理导致用户数据未正确传递

### 详细步骤
1. [✓] 创建一个测试文件来检查用户数据是否正确传递
```php
// 创建文件: check_users.php
<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 获取所有用户
$users = \App\Models\User::select('id', 'name', 'email')->get();

echo "用户数量: " . $users->count() . "\n";
foreach ($users as $user) {
    echo "ID: {$user->id}, 名称: {$user->name}, 邮箱: {$user->email}\n";
}
```

2. [✓] 修改SettingsController中的用户数据获取逻辑
```php
// 修改前
$users = \App\Models\User::select('id', 'name', 'email')->get();

// 修改后
try {
    $users = \App\Models\User::select('id', 'name', 'email')->get();
    Log::info('Retrieved users for notification history: ' . $users->count());
} catch (\Exception $e) {
    Log::error('Failed to get users for notification history: ' . $e->getMessage());
    $users = collect();
}
```

3. [✓] 在视图中添加调试信息
```php
<!-- 添加到视图中 -->
@if(empty($users) || $users->isEmpty())
    <div class="bg-yellow-50 p-4 rounded-md mb-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">注意</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>未找到用户数据。这可能会影响接收者选择功能。</p>
                </div>
            </div>
        </div>
    </div>
@endif
```

## 任务4: 修复Recipient Filter自动关闭问题
### 问题分析
点击Recipient Filter后会自动关闭，通过检查代码和行为，发现以下可能的原因：

1. 事件冒泡可能导致点击事件传播到父元素，触发其他事件处理程序
2. 在Livewire环境中，DOM元素可能在页面刷新后重新创建，但事件监听器没有重新绑定
3. CSS过渡动画可能导致意外的状态变化

### 详细步骤
1. [ ] 修改toggleRecipientSelector函数，阻止事件冒泡
```javascript
toggleRecipientSelector(e) {
    // 阻止事件冒泡
    e.preventDefault();
    e.stopPropagation();
    
    const type = this.getAttribute('data-type');
    const selector = document.getElementById(`selector-${type}`);
    
    if (selector) {
        // 如果选择器已经可见，直接切换隐藏状态
        if (!selector.classList.contains('hidden')) {
            selector.classList.add('hidden');
            
            // 重置图标
            const icon = this.querySelector('.toggle-icon');
            if (icon) {
                icon.style.transform = 'rotate(0deg)';
            }
            
            return;
        }
        
        // 隐藏其他选择器
        document.querySelectorAll('.recipient-selector').forEach(el => {
            if (el.id !== `selector-${type}`) {
                el.classList.add('hidden');
                
                // 重置其他图标
                const otherButton = document.querySelector(`.toggle-recipients[data-type="${el.id.replace('selector-', '')}"]`);
                if (otherButton) {
                    const otherIcon = otherButton.querySelector('.toggle-icon');
                    if (otherIcon) {
                        otherIcon.style.transform = 'rotate(0deg)';
                    }
                }
            }
        });
        
        // 显示当前选择器
        selector.classList.remove('hidden');
        
        // 旋转图标
        const icon = this.querySelector('.toggle-icon');
        if (icon) {
            icon.style.transition = 'transform 0.2s ease';
            icon.style.transform = 'rotate(180deg)';
        }
    }
}
```

2. [ ] 为下拉菜单内容添加阻止冒泡
```javascript
// 在RecipientFilter构造函数中添加
constructor() {
    this.bindEvents();
    
    // 为选择器内容添加阻止冒泡
    document.querySelectorAll('.recipient-selector').forEach(selector => {
        selector.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
}
```

## 任务5: 修复Recipient Filter响应慢的问题
### 问题分析
点击Recipient Filter按钮后需要很长时间才有反应，可能的原因包括：

1. 异步加载用户数据时没有提供加载状态反馈
2. 用户数据加载可能很慢或存在性能问题
3. 事件处理程序中可能有阻塞操作

### 详细步骤
1. [ ] 添加加载状态指示器
```javascript
toggleRecipientSelector(e) {
    // 阻止事件冒泡
    e.preventDefault();
    e.stopPropagation();
    
    const type = this.getAttribute('data-type');
    const selector = document.getElementById(`selector-${type}`);
    
    if (selector) {
        // 如果选择器已经可见，直接切换隐藏状态
        if (!selector.classList.contains('hidden')) {
            selector.classList.add('hidden');
            return;
        }
        
        // 显示加载状态
        const userList = selector.querySelector(`.user-list[data-type="${type}"]`);
        if (userList) {
            userList.innerHTML = `
                <div class="flex justify-center items-center p-4">
                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="ml-2 text-sm text-gray-600">Loading users...</span>
                </div>
            `;
        }
        
        // 先显示选择器，再加载数据
        selector.classList.remove('hidden');
    }
}
```

2. [ ] 优化用户数据加载
```javascript
// 添加缓存机制
let cachedUsers = null;

// 优化用户数据加载
async function loadUsers(type) {
    // 如果已经有缓存的用户数据，直接使用
    if (cachedUsers) {
        populateUserList(type, cachedUsers);
        return;
    }
    
    try {
        // 从页面中获取用户数据
        const userItems = document.querySelectorAll(`.user-list[data-type="${type}"] .user-item`);
        if (userItems.length > 0) {
            // 已经有数据，不需要重新加载
            return;
        }
        
        // 从DOM中提取用户数据
        const users = [];
        document.querySelectorAll('.user-item').forEach(item => {
            const name = item.querySelector('.text-gray-700')?.textContent.trim() || '';
            const email = item.querySelector('.text-gray-500')?.textContent.trim() || '';
            if (email) {
                users.push({ name, email });
            }
        });
        
        if (users.length > 0) {
            cachedUsers = users;
            populateUserList(type, users);
        } else {
            // 如果DOM中没有用户数据，显示错误消息
            const userList = document.querySelector(`.user-list[data-type="${type}"]`);
            if (userList) {
                userList.innerHTML = `
                    <div class="text-center p-4 text-red-500">
                        No users found. Please refresh the page.
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error('Error loading users:', error);
        const userList = document.querySelector(`.user-list[data-type="${type}"]`);
        if (userList) {
            userList.innerHTML = `
                <div class="text-center p-4 text-red-500">
                    Failed to load users. Please try again.
                </div>
            `;
        }
    }
}
```

## 任务6: 修复JavaScript变量重复声明错误
### 问题分析
在Livewire环境中，页面部分刷新时JavaScript代码重复执行，导致`newStyles`变量重复声明。

### 解决方案
1. 将样式添加逻辑移入IIFE内部
2. 添加检查，确保样式只添加一次
3. 使用更安全的方式添加样式

### 实现
```javascript
(function() {
    // 跟踪初始化状态，防止在Livewire环境中重复初始化
    if (window.notificationHistoryInitialized) return;
    window.notificationHistoryInitialized = true;
    
    // 添加样式
    function addStyles() {
        // 检查是否已添加样式
        if (document.getElementById('notification-history-styles')) return;
        
        const styleElement = document.createElement('style');
        styleElement.id = 'notification-history-styles';
        styleElement.textContent = `
            .recipient-selector {
                transition: transform 0.2s ease, opacity 0.2s ease !important;
                transform: translateY(-10px);
                opacity: 0;
            }
            
            .recipient-selector:not(.hidden) {
                transform: translateY(0);
                opacity: 1;
            }
            
            .toggle-icon {
                transition: transform 0.2s ease !important;
            }
            
            .modal-open {
                overflow: hidden;
            }
            
            #notification-modal {
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.2s ease-out, visibility 0.2s ease-out;
            }
            
            #notification-modal.show {
                opacity: 1;
                visibility: visible;
            }
            
            #notification-modal .relative {
                transform: scale(0.95);
                transition: transform 0.2s ease-out;
            }
            
            #notification-modal.show .relative {
                transform: scale(1);
            }
        `;
        document.head.appendChild(styleElement);
    }
    
    // 在初始化函数中调用添加样式
    function initNotificationHistory() {
        // ... 其他初始化代码 ...
        
        // 添加样式
        addStyles();
        
        // ... 其他初始化代码 ...
    }
    
    // ... 其他代码 ...
})();
```

## 任务7: 修复Recipient Filter显示问题
### 问题分析
Recipient Filter下拉菜单中没有正确显示用户数据，而Test Email Recipient显示正常。

### 解决方案
1. 确保RecipientFilter类正确初始化
2. 在页面加载时动态填充Recipient Filter下拉菜单
3. 使用与Test Email Recipient相同的数据源

### 实现
```javascript
class RecipientFilter {
    constructor() {
        this.bindEvents();
        this.populateUserLists();
        console.log('RecipientFilter initialized');
    }
    
    bindEvents() {
        // ... 现有事件绑定代码 ...
    }
    
    populateUserLists() {
        // 获取测试邮件接收者列表中的用户
        const testEmailUsers = document.querySelectorAll('.user-list[data-type="test_email_recipient"] .user-item');
        
        // 获取接收者筛选器用户列表容器
        const recipientFilterList = document.querySelector('.user-list[data-type="recipient_filter"]');
        
        if (testEmailUsers.length > 0 && recipientFilterList) {
            // 清空现有内容
            recipientFilterList.innerHTML = '';
            
            // 复制用户到接收者筛选器
            testEmailUsers.forEach(user => {
                const clone = user.cloneNode(true);
                
                // 修改克隆元素，将单选按钮改为复选框
                const radio = clone.querySelector('input[type="radio"]');
                if (radio) {
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = 'receivers[recipient_filter][]';
                    checkbox.value = radio.value;
                    checkbox.className = 'form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500';
                    
                    radio.parentNode.replaceChild(checkbox, radio);
                }
                
                recipientFilterList.appendChild(clone);
            });
            
            console.log('Populated recipient filter with users:', testEmailUsers.length);
        } else {
            console.log('No users found to populate recipient filter');
        }
    }
    
    // ... 其他方法 ...
}
```

## 任务8: 添加通知类型发送模板功能
### 问题分析
需要添加功能，使通知类型能够使用`http://localhost:2268/settings/message-templates`中的模板发送邮件。

### 解决方案
1. 添加模板选择功能
2. 集成MessageTemplateController的API方法
3. 动态加载模板内容

### 实现
```javascript
// 添加模板选择功能
function initTemplateSelection() {
    const templateTypeSelect = document.getElementById('template_type');
    const templateSelect = document.getElementById('message_template_id');
    const subjectInput = document.getElementById('subject');
    const contentTextarea = document.getElementById('content');
    const templateVariablesContainer = document.getElementById('template-variables-container');
    const templateVariablesList = document.getElementById('template-variables-list');
    
    if (templateTypeSelect && templateSelect) {
        // 当通知类型改变时，加载相应的模板
        templateTypeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            
            // 清空模板选择器
            templateSelect.innerHTML = '<option value="">Custom (No Template)</option>';
            
            // 获取选定类型的模板
            fetch(`/api/message-templates?type=${selectedType}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.templates.length > 0) {
                        // 填充模板选项
                        data.templates.forEach(template => {
                            const option = document.createElement('option');
                            option.value = template.id;
                            option.textContent = template.name;
                            option.dataset.subject = template.subject;
                            option.dataset.content = template.content;
                            option.dataset.isDefault = template.is_default;
                            
                            templateSelect.appendChild(option);
                            
                            // 如果是默认模板，自动选中
                            if (template.is_default) {
                                option.selected = true;
                                // 触发change事件以加载模板内容
                                templateSelect.dispatchEvent(new Event('change'));
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Failed to load templates:', error);
                });
        });
        
        // 当选择模板时，加载模板内容
        templateSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                // 填充主题和内容
                subjectInput.value = selectedOption.dataset.subject || '';
                contentTextarea.value = selectedOption.dataset.content || '';
                
                // 获取模板变量
                fetch(`/api/message-templates/${selectedOption.value}/variables`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.variables) {
                            // 显示变量容器
                            templateVariablesContainer.classList.remove('hidden');
                            
                            // 填充变量列表
                            templateVariablesList.innerHTML = '';
                            Object.entries(data.variables).forEach(([variable, description]) => {
                                const div = document.createElement('div');
                                div.className = 'p-1';
                                div.innerHTML = `<code>${variable}</code>: ${description}`;
                                templateVariablesList.appendChild(div);
                            });
                        } else {
                            templateVariablesContainer.classList.add('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Failed to load template variables:', error);
                        templateVariablesContainer.classList.add('hidden');
                    });
            } else {
                // 如果没有选择模板，清空内容
                subjectInput.value = 'Test Email';
                contentTextarea.value = 'This is a test email message from the system.';
                templateVariablesContainer.classList.add('hidden');
            }
        });
        
        // 初始加载模板
        templateTypeSelect.dispatchEvent(new Event('change'));
    }
}

// 在初始化函数中调用
function initNotificationHistory() {
    // ... 其他初始化代码 ...
    
    // 初始化模板选择
    initTemplateSelection();
    
    // ... 其他初始化代码 ...
}
```

## 注意事项
- 每个任务完成后进行测试，确保不会影响其他功能
- 保持现有的过滤功能和查看通知详情功能
- 使用IIFE和初始化标记避免Livewire环境中的JavaScript冲突
- 确保所有UI元素符合系统的设计风格
- 遵循最佳实践，避免代码重复

## 技术注意事项
1. Livewire使用部分页面刷新技术，需要特别注意JavaScript的初始化时机
2. 使用事件委托处理动态添加的DOM元素
3. 避免全局变量污染，使用IIFE或模块模式组织代码
4. 使用防抖和节流技术优化事件处理
5. 缓存DOM元素和API响应，减少不必要的操作和请求

## 测试计划
1. 测试NotificationViewer未定义错误是否已解决 ✓
2. 测试Toggle Form按钮是否可以正常点击 ✓
3. 测试用户数据是否正确加载 ✓
4. 测试Recipient Filter是否能正常打开和关闭
5. 测试Recipient Filter的响应速度和用户体验
6. 测试在Livewire环境中的表现
7. 测试页面刷新后功能是否正常
8. 测试模态框内容的正确渲染 