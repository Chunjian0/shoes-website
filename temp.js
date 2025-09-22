// 璺熻釜鍒濆鍖栫姸鎬侊紝闃叉鍦↙ivewire鐜涓噸澶嶅垵濮嬪寲
let notificationHistoryInitialized = false;

// 瀹氫箟NotificationViewer绫?
class NotificationViewer {
    constructor() {
        this.modal = document.getElementById('notification-modal');
        this.bindEvents();
        console.log('NotificationViewer initialized');
    }
    
    bindEvents() {
        // 浣跨敤浜嬩欢濮旀墭缁戝畾View鎸夐挳鐐瑰嚮浜嬩欢
        document.addEventListener('click', (e) => {
            const viewBtn = e.target.closest('.view-notification');
            if (viewBtn) {
                this.showNotification(viewBtn);
            }
        });
        
        // 缁戝畾鍏抽棴鎸夐挳浜嬩欢
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', () => this.hideModal());
        });
    }
    
    showNotification(button) {
        const id = button.getAttribute('data-id');
        const content = button.getAttribute('data-content');
        
        // 鑾峰彇閫氱煡琛屾暟鎹?
        const row = button.closest('tr');
        const subject = row.querySelector('td:nth-child(4)').textContent.trim();
        const recipient = row.querySelector('td:nth-child(3)').textContent.trim();
        const status = row.querySelector('td:nth-child(5) span').textContent.trim();
        
        // 濉厖妯℃€佹鍐呭
        document.getElementById('modal-subject').textContent = subject;
        document.getElementById('modal-content').innerHTML = content;
        document.getElementById('modal-recipient').textContent = recipient;
        document.getElementById('modal-status').textContent = status;
        
        // 鏄剧ず妯℃€佹
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

// 瀹氫箟RecipientFilter绫?
class RecipientFilter {
    constructor() {
        this.bindEvents();
        this.populateUserLists();
        console.log('RecipientFilter initialized');
    }
    
    bindEvents() {
        // 缁戝畾鎺ユ敹鑰呯瓫閫夊櫒浜嬩欢
        document.querySelectorAll('.toggle-recipients').forEach(btn => {
            btn.addEventListener('click', this.toggleRecipientSelector);
        });
        
        // 缁戝畾鎼滅储浜嬩欢
        document.querySelectorAll('.recipient-search').forEach(input => {
            input.addEventListener('input', this.handleSearch);
        });
        
        // 缁戝畾鍏ㄩ€?娓呯┖鎸夐挳浜嬩欢
        document.querySelectorAll('.select-all-btn').forEach(btn => {
            btn.addEventListener('click', this.selectAll);
        });
        
        document.querySelectorAll('.deselect-all-btn').forEach(btn => {
            btn.addEventListener('click', this.deselectAll);
        });
        
        // 鐐瑰嚮澶栭儴鍏抽棴涓嬫媺鑿滃崟
        document.addEventListener('click', this.handleOutsideClick.bind(this));
        
        // 涓洪€夋嫨鍣ㄥ唴瀹规坊鍔犻樆姝㈠啋娉?
        document.querySelectorAll('.recipient-selector').forEach(selector => {
            selector.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
        
        // 娣诲姞澶嶉€夋鍜屽崟閫夋寜閽殑change浜嬩欢鐩戝惉
        document.addEventListener('change', (e) => {
            const checkbox = e.target.closest('input[type="checkbox"]');
            const radio = e.target.closest('input[type="radio"]');
            
            if (checkbox) {
                const type = checkbox.closest('.user-list').getAttribute('data-type');
                this.updateSelectedRecipients(type);
            }
            
            if (radio) {
                const type = radio.closest('.user-list').getAttribute('data-type');
                this.updateSelectedRecipient(type);
            }
        });
    }
    
    populateUserLists() {
        // 鑾峰彇娴嬭瘯閭欢鎺ユ敹鑰呭垪琛ㄤ腑鐨勭敤鎴?
        const testEmailUsers = document.querySelectorAll('.user-list[data-type="test_email_recipient"] .user-item');
        
        // 鑾峰彇鎺ユ敹鑰呯瓫閫夊櫒鐢ㄦ埛鍒楄〃瀹瑰櫒
        const recipientFilterList = document.querySelector('.user-list[data-type="recipient_filter"]');
        
        if (testEmailUsers.length > 0 && recipientFilterList) {
            // 娓呯┖鐜版湁鍐呭
            recipientFilterList.innerHTML = '';
            
            // 澶嶅埗鐢ㄦ埛鍒版帴鏀惰€呯瓫閫夊櫒
            testEmailUsers.forEach(user => {
                const clone = user.cloneNode(true);
                
                // 淇敼鍏嬮殕鍏冪礌锛屽皢鍗曢€夋寜閽敼涓哄閫夋
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
    
    toggleRecipientSelector(e) {
        // 闃绘浜嬩欢鍐掓场
        e.preventDefault();
        e.stopPropagation();
        
        const type = this.getAttribute('data-type');
        const selector = document.getElementById(`selector-${type}`);
        
        if (selector) {
            // 濡傛灉閫夋嫨鍣ㄥ凡缁忓彲瑙侊紝鐩存帴鍒囨崲闅愯棌鐘舵€?
            if (!selector.classList.contains('hidden')) {
                selector.classList.add('hidden');
                
                // 閲嶇疆鍥炬爣
                const icon = this.querySelector('.toggle-icon');
                if (icon) {
                    icon.style.transform = 'rotate(0deg)';
                }
                
                return;
            }
            
            // 闅愯棌鍏朵粬閫夋嫨鍣?
            document.querySelectorAll('.recipient-selector').forEach(el => {
                if (el.id !== `selector-${type}`) {
                    el.classList.add('hidden');
                    
                    // 閲嶇疆鍏朵粬鍥炬爣
                    const otherButton = document.querySelector(`.toggle-recipients[data-type="${el.id.replace('selector-', '')}"]`);
                    if (otherButton) {
                        const otherIcon = otherButton.querySelector('.toggle-icon');
                        if (otherIcon) {
                            otherIcon.style.transform = 'rotate(0deg)';
                        }
                    }
                }
            });
            
            // 鏄剧ず褰撳墠閫夋嫨鍣?
            selector.classList.remove('hidden');
            
            // 鏃嬭浆鍥炬爣
            const icon = this.querySelector('.toggle-icon');
            if (icon) {
                icon.style.transition = 'transform 0.2s ease';
                icon.style.transform = 'rotate(180deg)';
            }
        }
    }
    
    handleSearch() {
        const type = this.getAttribute('data-type');
        const query = this.value.toLowerCase();
        const userItems = document.querySelectorAll(`.user-list[data-type="${type}"] .user-item`);
        
        userItems.forEach(item => {
            const name = item.querySelector('.text-gray-700')?.textContent.toLowerCase() || '';
            const email = item.querySelector('.text-gray-500')?.textContent.toLowerCase() || '';
            
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
            // 瑙﹀彂change浜嬩欢浠ユ洿鏂伴€変腑鐘舵€?
            checkbox.dispatchEvent(new Event('change'));
        });
        
        // 鏇存柊宸查€夋嫨鐨勬帴鏀惰€呮樉绀?
        const recipientFilter = new RecipientFilter();
        recipientFilter.updateSelectedRecipients(type);
    }
    
    deselectAll() {
        const type = this.getAttribute('data-type');
        const checkboxes = document.querySelectorAll(`.user-list[data-type="${type}"] input[type="checkbox"]`);
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
            // 瑙﹀彂change浜嬩欢浠ユ洿鏂伴€変腑鐘舵€?
            checkbox.dispatchEvent(new Event('change'));
        });
        
        // 鏇存柊宸查€夋嫨鐨勬帴鏀惰€呮樉绀?
        const recipientFilter = new RecipientFilter();
        recipientFilter.updateSelectedRecipients(type);
    }
    
    handleOutsideClick(e) {
        const openSelectors = document.querySelectorAll('.recipient-selector:not(.hidden)');
        
        openSelectors.forEach(selector => {
            const toggleButton = document.querySelector(`.toggle-recipients[data-type="${selector.id.replace('selector-', '')}"]`);
            
            // 妫€鏌ョ偣鍑绘槸鍚﹀湪閫夋嫨鍣ㄥ唴閮ㄦ垨瑙﹀彂鎸夐挳涓?
            const isClickInside = selector.contains(e.target);
            const isClickOnButton = toggleButton && toggleButton.contains(e.target);
            
            // 濡傛灉鐐瑰嚮鍦ㄩ€夋嫨鍣ㄥ閮ㄤ笖涓嶆槸鍦ㄦ寜閽笂锛屽垯鍏抽棴閫夋嫨鍣?
            if (!isClickInside && !isClickOnButton) {
                selector.classList.add('hidden');
                
                // 閲嶇疆鍥炬爣
                if (toggleButton) {
                    const icon = toggleButton.querySelector('.toggle-icon');
                    if (icon) {
                        icon.style.transform = 'rotate(0deg)';
                    }
                }
            }
        });
    }
    
    // 鏇存柊宸查€夋嫨鐨勬帴鏀惰€呮樉绀猴紙鐢ㄤ簬澶嶉€夋锛?
    updateSelectedRecipients(type) {
        const selectedContainer = document.getElementById(`selected-${type}`);
        const checkboxes = document.querySelectorAll(`.user-list[data-type="${type}"] input[type="checkbox"]:checked`);
        
        if (selectedContainer) {
            if (checkboxes.length === 0) {
                selectedContainer.innerHTML = '<div class="text-sm text-gray-500 italic">No recipients selected</div>';
            } else {
                const selectedUsers = [];
                
                checkboxes.forEach(checkbox => {
                    const userItem = checkbox.closest('.user-item');
                    const name = userItem.querySelector('.text-gray-700')?.textContent.trim() || '';
                    selectedUsers.push(name);
                });
                
                selectedContainer.innerHTML = `
                    <div class="text-sm text-gray-700">
                        Selected ${checkboxes.length} recipient(s): 
                        <span class="font-medium">${selectedUsers.join(', ')}</span>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        ${Array.from(checkboxes).map(checkbox => {
                            const userItem = checkbox.closest('.user-item');
                            const name = userItem.querySelector('.text-gray-700')?.textContent.trim() || '';
                            return `
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    ${name}
                                </span>
                            `;
                        }).join('')}
                    </div>
                `;
            }
        }
    }
    
    // 鏇存柊宸查€夋嫨鐨勬帴鏀惰€呮樉绀猴紙鐢ㄤ簬鍗曢€夋寜閽級
    updateSelectedRecipient(type) {
        const selectedContainer = document.getElementById(`selected-${type}`);
        const radio = document.querySelector(`.user-list[data-type="${type}"] input[type="radio"]:checked`);
        
        if (selectedContainer) {
            if (!radio) {
                selectedContainer.innerHTML = '<div class="text-sm text-gray-500 italic">No recipient selected</div>';
            } else {
                const userItem = radio.closest('.user-item');
                const name = userItem.querySelector('.text-gray-700')?.textContent.trim() || '';
                const email = userItem.querySelector('.text-gray-500')?.textContent.trim() || '';
                
                selectedContainer.innerHTML = `
                    <div class="text-sm text-gray-700">
                        Selected recipient: 
                        <span class="font-medium">${name}</span>
                    </div>
                    <div class="mt-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            ${name}
                        </span>
                    </div>
                `;
            }
        }
    }
}

// 鍒濆鍖朤oggle Form鎸夐挳
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
    
    // 鍒濆鍖栨祴璇曢偖浠惰〃鍗曟彁浜?
    initTestEmailForm();
}

// 鍒濆鍖栨祴璇曢偖浠惰〃鍗曟彁浜?
function initTestEmailForm() {
    const form = document.getElementById('test-email-form');
    const resultContainer = document.getElementById('result');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // 鑾峰彇琛ㄥ崟鏁版嵁
            const recipient = document.querySelector('input[name="recipient"]:checked')?.value;
            const type = document.getElementById('template_type')?.value;
            const subject = document.getElementById('subject')?.value;
            const content = document.getElementById('content')?.value;
            const messageTemplateId = document.getElementById('message_template_id')?.value;
            
            // 楠岃瘉蹇呭～瀛楁
            if (!recipient) {
                showToast('Please select a recipient', 'error');
                return;
            }
            
            // 鏄剧ず鍙戦€佷腑鐘舵€?
            showToast('Sending test email...', 'info');
            
            // 鍑嗗琛ㄥ崟鏁版嵁
            const formData = new FormData();
            formData.append('recipient', recipient);
            formData.append('type', type);
            formData.append('subject', subject);
            formData.append('content', content);
            
            if (messageTemplateId) {
                formData.append('message_template_id', messageTemplateId);
            }
            
            // 鍙戦€丄JAX璇锋眰
            fetch('/api/test/send-email', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // 鏄剧ず缁撴灉
                if (data.success) {
                    showToast('Test email sent successfully', 'success');
                    // 鍒锋柊閫氱煡鍘嗗彶琛ㄦ牸
                    refreshNotificationTable();
                } else {
                    showToast('Failed to send test email: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error sending test email:', error);
                showToast('An error occurred while sending the test email', 'error');
            });
        });
    }
}

// 鏄剧ずToast閫氱煡
function showToast(message, type = 'info') {
    const alertId = type === 'success' ? 'success-alert' : 'error-alert';
    const alert = document.getElementById(alertId);
    
    if (alert) {
        // 鏇存柊娑堟伅鍐呭
        const messageElement = alert.querySelector('.text-sm');
        if (messageElement) {
            messageElement.textContent = message;
        }
        
        // 鏄剧ず閫氱煡
        alert.classList.remove('hidden');
        
        // 3绉掑悗鑷姩闅愯棌
        setTimeout(() => {
            alert.classList.add('hidden');
        }, 3000);
    } else {
        // 濡傛灉鎵句笉鍒伴瀹氫箟鐨勮鍛婃锛屽垱寤轰竴涓复鏃剁殑鎻愮ず妗?
        const tempAlert = document.createElement('div');
        tempAlert.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg ${type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'} z-50`;
        tempAlert.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    ${type === 'success' 
                        ? '<svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>'
                        : '<svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>'
                    }
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">${message}</p>
                </div>
            </div>
        `;
        document.body.appendChild(tempAlert);
        
        // 3绉掑悗绉婚櫎涓存椂鎻愮ず妗?
        setTimeout(() => {
            tempAlert.remove();
        }, 3000);
    }
}

// 鍒锋柊閫氱煡鍘嗗彶琛ㄦ牸
function refreshNotificationTable() {
    const table = document.getElementById('notification-table');
    
    if (table) {
        // 濡傛灉浣跨敤DataTables锛屽彲浠ョ洿鎺ュ埛鏂?
        if (typeof $.fn !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable('#notification-table')) {
            $('#notification-table').DataTable().ajax.reload();
        } else {
            // 鍚﹀垯锛岄噸鏂板姞杞介〉闈㈢殑閫氱煡鍘嗗彶閮ㄥ垎
            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTable = doc.getElementById('notification-table');
                    
                    if (newTable) {
                        table.innerHTML = newTable.innerHTML;
                    }
                })
                .catch(error => {
                    console.error('Error refreshing notification table:', error);
                });
        }
    }
}

// 娣诲姞妯℃澘閫夋嫨鍔熻兘
function initTemplateSelection() {
    const templateTypeSelect = document.getElementById('template_type');
    const templateSelect = document.getElementById('message_template_id');
    const subjectInput = document.getElementById('subject');
    const contentTextarea = document.getElementById('content');
    const templateVariablesContainer = document.getElementById('template-variables-container');
    const templateVariablesList = document.getElementById('template-variables-list');
    const previewBtn = document.getElementById('preview-email-btn');
    const previewModal = document.getElementById('preview-modal');
    
    if (templateTypeSelect && templateSelect) {
        // 褰撻€氱煡绫诲瀷鏀瑰彉鏃讹紝鍔犺浇鐩稿簲鐨勬ā鏉?
        templateTypeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            
            // 娓呯┖妯℃澘閫夋嫨鍣?
            templateSelect.innerHTML = '<option value="">Custom (No Template)</option>';
            
            // 鑾峰彇閫夊畾绫诲瀷鐨勬ā鏉?
            fetch(`/api/message-templates?type=${selectedType}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.templates.length > 0) {
                        // 濉厖妯℃澘閫夐」
                        data.templates.forEach(template => {
                            const option = document.createElement('option');
                            option.value = template.id;
                            option.textContent = template.name;
                            option.dataset.subject = template.subject;
                            option.dataset.content = template.content;
                            option.dataset.isDefault = template.is_default;
                            
                            templateSelect.appendChild(option);
                            
                            // 濡傛灉鏄粯璁ゆā鏉匡紝鑷姩閫変腑
                            if (template.is_default) {
                                option.selected = true;
                                // 瑙﹀彂change浜嬩欢浠ュ姞杞芥ā鏉垮唴瀹?
                                templateSelect.dispatchEvent(new Event('change'));
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Failed to load templates:', error);
                });
        });
        
        // 褰撻€夋嫨妯℃澘鏃讹紝鍔犺浇妯℃澘鍐呭
        templateSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                // 濉厖涓婚鍜屽唴瀹?
                subjectInput.value = selectedOption.dataset.subject || '';
                contentTextarea.value = selectedOption.dataset.content || '';
                
                // 鑾峰彇妯℃澘鍙橀噺
                fetch(`/api/message-templates/${selectedOption.value}/variables`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.variables) {
                            // 鏄剧ず鍙橀噺瀹瑰櫒
                            templateVariablesContainer.classList.remove('hidden');
                            
                            // 濉厖鍙橀噺鍒楄〃
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
                // 濡傛灉娌℃湁閫夋嫨妯℃澘锛屾竻绌哄唴瀹?
                subjectInput.value = 'Test Email';
                contentTextarea.value = 'This is a test email message from the system.';
                templateVariablesContainer.classList.add('hidden');
            }
        });
        
        // 鍒濆鍔犺浇妯℃澘
        templateTypeSelect.dispatchEvent(new Event('change'));
    }
    
    // 娣诲姞棰勮鎸夐挳鐐瑰嚮浜嬩欢
    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            showEmailPreview();
        });
        
        // 鍏抽棴棰勮妯℃€佹
        document.querySelectorAll('.close-preview-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                previewModal.classList.add('hidden');
                document.body.classList.remove('modal-open');
            });
        });
    }
}

// 鏄剧ず閭欢棰勮
function showEmailPreview() {
    const subject = document.getElementById('subject').value;
    const content = document.getElementById('content').value;
    const previewModal = document.getElementById('preview-modal');
    const previewSubject = document.getElementById('preview-subject');
    const previewContent = document.getElementById('preview-content');
    
    if (previewModal && previewSubject && previewContent) {
        // 濉厖棰勮鍐呭
        previewSubject.textContent = subject;
        previewContent.innerHTML = content;
        
        // 鏄剧ず妯℃€佹
        previewModal.classList.remove('hidden');
        document.body.classList.add('modal-open');
        
        // 澶勭悊妯℃澘鍙橀噺鐨勬樉绀?
        const templateVariables = document.querySelectorAll('#template-variables-list code');
        if (templateVariables.length > 0) {
            // 楂樹寒鏄剧ず妯℃澘鍙橀噺
            templateVariables.forEach(variable => {
                const variableName = variable.textContent;
                const regex = new RegExp(variableName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
                
                // 鍦ㄩ瑙堝唴瀹逛腑楂樹寒鍙橀噺
                previewContent.innerHTML = previewContent.innerHTML.replace(
                    regex,
                    `<span class="px-1 bg-yellow-100 text-yellow-800 rounded">${variableName}</span>`
                );
            });
        }
    }
}

// 娣诲姞鏍峰紡
function addStyles() {
    // 妫€鏌ユ槸鍚﹀凡娣诲姞鏍峰紡
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

// 鍒濆鍖栧嚱鏁?
function initNotificationHistory() {
    // 妫€鏌ョ壒瀹欴OM瀹炰緥鏄惁宸插垵濮嬪寲
    const container = document.querySelector('.notification-history-container');
    if (container && container.dataset.initialized === 'true') return;
    if (container) container.dataset.initialized = 'true';
    
    console.log('Initializing notification history...');
    
    // 娣诲姞鏍峰紡
    addStyles();
    
    // 鍒濆鍖栭€氱煡鏌ョ湅鍣ㄥ拰鎺ユ敹鑰呯瓫閫夊櫒
    new NotificationViewer();
    new RecipientFilter();
    
    // 鍒濆鍖朤oggle Form鎸夐挳
    initToggleForm();
    
    // 鍒濆鍖栨ā鏉块€夋嫨
    initTemplateSelection();
}

// 鍦―OM鍑嗗濂芥垨浣跨敤Livewire鍏煎浜嬩欢鍒濆鍖?
document.addEventListener('DOMContentLoaded', initNotificationHistory);
document.addEventListener('turbolinks:load', initNotificationHistory);
document.addEventListener('livewire:load', initNotificationHistory);

// 灏嗗垵濮嬪寲鐘舵€佽缃负true
notificationHistoryInitialized = true; 
