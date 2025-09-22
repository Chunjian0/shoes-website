@push('scripts')
<script>
    // 初始化函数
    function initVariantOptionsManager() {
        // 获取DOM元素
        const addOptionForm = document.getElementById('add-option-form');
        const optionNameInput = document.getElementById('option_name');
        const sortOrderInput = document.getElementById('sort_order');
        const optionsList = document.getElementById('variant-options-list');
        const optionTemplate = document.getElementById('option-template');
        
        // 如果页面没有找到这些元素，说明我们不在变体选项管理页面
        if (!addOptionForm || !optionsList) return;
        
        // 添加选项的表单提交处理
        addOptionForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // 获取表单数据
            const formData = new FormData(addOptionForm);
            
            try {
                // 发送AJAX请求
                const response = await fetch(addOptionForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    // 创建新选项元素
                    const newOption = optionTemplate.content.cloneNode(true);
                    
                    // 填充数据
                    newOption.querySelector('.option-name').textContent = formData.get('option_name');
                    newOption.querySelector('.option-sort-order').textContent = formData.get('sort_order');
                    
                    // 设置删除链接
                    const deleteForm = newOption.querySelector('.delete-option-form');
                    deleteForm.action = deleteForm.action.replace('__ID__', data.option.id);
                    deleteForm.querySelector('input[name="option_id"]').value = data.option.id;
                    
                    // 添加进入动画
                    const optionItem = newOption.querySelector('.option-item');
                    optionItem.classList.add('animate-fade-in');
                    
                    // 添加到列表
                    optionsList.appendChild(newOption);
                    
                    // 重置表单
                    optionNameInput.value = '';
                    sortOrderInput.value = '0';
                    
                    // 显示成功消息
                    if (window.Toast) {
                        window.Toast.success('Variant option added successfully');
                    } else {
                        alert('Variant option added successfully');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                if (window.Toast) {
                    window.Toast.error('Failed to add variant option');
                } else {
                    alert('Failed to add variant option');
                }
            }
        });
        
        // 删除选项的处理
        optionsList.addEventListener('click', function(e) {
            const deleteButton = e.target.closest('.delete-option-button');
            if (!deleteButton) return;
            
            e.preventDefault();
            
            const form = deleteButton.closest('.delete-option-form');
            const optionName = deleteButton.closest('.option-item').querySelector('.option-name').textContent;
            
            if (window.ConfirmationDialog) {
                window.ConfirmationDialog.danger(
                    'Confirm Delete',
                    `Are you sure you want to delete variant option "${optionName}"? This action cannot be undone.`,
                    function() {
                        form.submit();
                    },
                    {
                        confirmBtnText: 'Delete',
                    }
                );
            } else {
                if (confirm(`Are you sure you want to delete variant option "${optionName}"? This action cannot be undone.`)) {
                    form.submit();
                }
            }
        });
    }
    
    // 监听DOM加载完成事件
    document.addEventListener('DOMContentLoaded', initVariantOptionsManager);
    
    // 支持Turbolinks
    document.addEventListener('turbolinks:load', initVariantOptionsManager);
</script>
@endpush 