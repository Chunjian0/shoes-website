/**
 * Notification History JavaScript
 * 
 * This file contains the JavaScript code for the notification history page.
 */

// Global variables and initialization state tracking
window.notificationHistoryInitialized = window.notificationHistoryInitialized || false;

// Global class declaration
    class NotificationViewer {
        constructor() {
            this.modal = document.getElementById('notification-modal');
            this.bindEvents();
            console.log('NotificationViewer initialized');
        }
        
        bindEvents() {
        // Use event delegation to bind View button click events
            document.addEventListener('click', (e) => {
                const viewBtn = e.target.closest('.view-notification');
                if (viewBtn) {
                    this.showNotification(viewBtn);
                }
            });
            
        // Bind close button events
            document.querySelectorAll('.close-modal').forEach(btn => {
                btn.addEventListener('click', () => this.hideModal());
            });
        }
        
        showNotification(button) {
        if (!button || !this.modal) return;
        
            const id = button.getAttribute('data-id');
            const content = button.getAttribute('data-content');
            
        // Get notification row data
            const row = button.closest('tr');
        if (!row) return;
        
        const subject = row.querySelector('td:nth-child(4)')?.textContent.trim() || '';
        const recipient = row.querySelector('td:nth-child(3)')?.textContent.trim() || '';
        const status = row.querySelector('td:nth-child(5) span')?.textContent.trim() || '';
        
        // Fill modal content
        const modalSubject = document.getElementById('modal-subject');
        const modalContent = document.getElementById('modal-content');
        const modalRecipient = document.getElementById('modal-recipient');
        const modalStatus = document.getElementById('modal-status');
        
        if (modalSubject) modalSubject.textContent = subject;
        if (modalContent) modalContent.innerHTML = content;
        if (modalRecipient) modalRecipient.textContent = recipient;
        if (modalStatus) modalStatus.textContent = status;
        
        // Show modal
            this.showModal();
        }
        
        showModal() {
        if (!this.modal) return;
            document.body.classList.add('modal-open');
            this.modal.classList.remove('hidden');
            this.modal.classList.add('show');
        }
        
        hideModal() {
        if (!this.modal) return;
            this.modal.classList.remove('show');
            this.modal.classList.add('hidden');
            document.body.classList.remove('modal-open');
        }
    }
    
// RecipientFilter class definition
    class RecipientFilter {
        constructor() {
            this.bindEvents();
            this.populateUserLists();
            console.log('RecipientFilter initialized');
        }
        
        bindEvents() {
        // Bind recipient filter events
            document.querySelectorAll('.toggle-recipients').forEach(btn => {
                btn.addEventListener('click', this.toggleRecipientSelector);
            });
            
        // Bind search events
            document.querySelectorAll('.recipient-search').forEach(input => {
                input.addEventListener('input', this.handleSearch);
            });
            
        // Bind select all/deselect all button events
            document.querySelectorAll('.select-all-btn').forEach(btn => {
                btn.addEventListener('click', this.selectAll);
            });
            
            document.querySelectorAll('.deselect-all-btn').forEach(btn => {
                btn.addEventListener('click', this.deselectAll);
            });
            
        // Close dropdown menu when clicking outside
            document.addEventListener('click', this.handleOutsideClick.bind(this));
            
        // Prevent event bubbling for selector content
            document.querySelectorAll('.recipient-selector').forEach(selector => {
                selector.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });
            
        // Add change event listeners for checkboxes and radio buttons
            document.addEventListener('change', (e) => {
                const checkbox = e.target.closest('input[type="checkbox"]');
                const radio = e.target.closest('input[type="radio"]');
                
                if (checkbox) {
                const type = checkbox.closest('.user-list')?.getAttribute('data-type');
                if (type) this.updateSelectedRecipients(type);
                }
                
                if (radio) {
                const type = radio.closest('.user-list')?.getAttribute('data-type');
                if (type) this.updateSelectedRecipient(type);
                }
            });
        }
        
        populateUserLists() {
        // Get users from test email recipients list
            const testEmailUsers = document.querySelectorAll('.user-list[data-type="test_email_recipient"] .user-item');
            
        // Get recipient filter user list container
            const recipientFilterList = document.querySelector('.user-list[data-type="recipient_filter"]');
            
            if (testEmailUsers.length > 0 && recipientFilterList) {
            // Clear existing content
                recipientFilterList.innerHTML = '';
                
            // Copy users to recipient filter
                testEmailUsers.forEach(user => {
                    const clone = user.cloneNode(true);
                    
                // Modify cloned element, change radio button to checkbox
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
        // Prevent event bubbling
            e.preventDefault();
            e.stopPropagation();
            
            const type = this.getAttribute('data-type');
            const selector = document.getElementById(`selector-${type}`);
            
            if (selector) {
            // If selector is already visible, directly switch hidden state
                if (!selector.classList.contains('hidden')) {
                    selector.classList.add('hidden');
                    
                // Reset icon
                    const icon = this.querySelector('.toggle-icon');
                    if (icon) {
                        icon.style.transform = 'rotate(0deg)';
                    }
                    
                    return;
                }
                
            // Hide other selectors
                document.querySelectorAll('.recipient-selector').forEach(el => {
                    if (el.id !== `selector-${type}`) {
                        el.classList.add('hidden');
                        
                    // Reset other icons
                        const otherButton = document.querySelector(`.toggle-recipients[data-type="${el.id.replace('selector-', '')}"]`);
                        if (otherButton) {
                            const otherIcon = otherButton.querySelector('.toggle-icon');
                            if (otherIcon) {
                                otherIcon.style.transform = 'rotate(0deg)';
                            }
                        }
                    }
                });
                
            // Show current selector
                selector.classList.remove('hidden');
                
            // Rotate icon
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
            // Trigger change event to update selected state
                checkbox.dispatchEvent(new Event('change'));
            });
        }
        
        deselectAll() {
            const type = this.getAttribute('data-type');
            const checkboxes = document.querySelectorAll(`.user-list[data-type="${type}"] input[type="checkbox"]`);
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            // Trigger change event to update selected state
                checkbox.dispatchEvent(new Event('change'));
            });
        }
        
        handleOutsideClick(e) {
            const openSelectors = document.querySelectorAll('.recipient-selector:not(.hidden)');
            
            openSelectors.forEach(selector => {
                const toggleButton = document.querySelector(`.toggle-recipients[data-type="${selector.id.replace('selector-', '')}"]`);
                
            // Check if click is inside selector or on button
                const isClickInside = selector.contains(e.target);
                const isClickOnButton = toggleButton && toggleButton.contains(e.target);
                
            // If click outside and not on button, close selector
                if (!isClickInside && !isClickOnButton) {
                    selector.classList.add('hidden');
                    
                // Reset icon
                    if (toggleButton) {
                        const icon = toggleButton.querySelector('.toggle-icon');
                        if (icon) {
                            icon.style.transform = 'rotate(0deg)';
                        }
                    }
                }
            });
        }
        
    // Update selected recipients display (for checkboxes)
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
        
    // Update selected recipients display (for radio buttons)
        updateSelectedRecipient(type) {
            const selectedContainer = document.getElementById(`selected-${type}`);
            const radio = document.querySelector(`.user-list[data-type="${type}"] input[type="radio"]:checked`);
            
            if (selectedContainer) {
                if (!radio) {
                    selectedContainer.innerHTML = '<div class="text-sm text-gray-500 italic">No recipient selected</div>';
                } else {
                    const userItem = radio.closest('.user-item');
                    const name = userItem.querySelector('.text-gray-700')?.textContent.trim() || '';
                    
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
    
// Show Toast notification
    function showToast(message, type = 'info') {
        const alertId = type === 'success' ? 'success-alert' : 'error-alert';
        const alert = document.getElementById(alertId);
        
        if (alert) {
        // Update message content
            const messageElement = alert.querySelector('.text-sm');
            if (messageElement) {
                messageElement.textContent = message;
            }
            
        // Show notification
            alert.classList.remove('hidden');
            
        // Auto-hide after 3 seconds
            setTimeout(() => {
                alert.classList.add('hidden');
            }, 3000);
        } else {
        // If predefined alert is not found, create a temporary tooltip
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
            
        // Remove temporary tooltip after 3 seconds
            setTimeout(() => {
                tempAlert.remove();
            }, 3000);
        }
    }
    
// Initialize function
function initNotificationHistory() {
    // If already initialized, return directly
    if (window.notificationHistoryInitialized) return;
    
    // Check if specific DOM instance is already initialized
        const container = document.querySelector('.notification-history-container');
        if (container && container.dataset.initialized === 'true') return;
        if (container) container.dataset.initialized = 'true';
        
        console.log('Initializing notification history...');
        
    // Add styles
        addStyles();
        
    // Initialize notification viewer and recipient filter
    window.notificationViewer = new NotificationViewer();
    window.recipientFilter = new RecipientFilter();
    
    // Initialize Toggle Form button
        initToggleForm();
        
    // Initialize template selection
        initTemplateSelection();
    
    // Mark as initialized
    window.notificationHistoryInitialized = true;
    }
    
// Add styles
    function addStyles() {
    // Check if styles are already added
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
    
// Refresh notification history table
function refreshNotificationTable() {
    const table = document.getElementById('notification-table');
    
    if (table) {
        // If using DataTables, refresh directly
        if (typeof $.fn !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable('#notification-table')) {
            $('#notification-table').DataTable().ajax.reload();
        } else {
            // Otherwise, reload notification history section of the page
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

// Initialize Toggle Form button
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
    
    // Initialize test email form submission
    initTestEmailForm();
}

// Initialize test email form submission
function initTestEmailForm() {
    const form = document.getElementById('test-email-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const recipient = document.querySelector('input[name="recipient"]:checked')?.value;
            const type = document.getElementById('template_type')?.value;
            const subject = document.getElementById('subject')?.value;
            const content = document.getElementById('content')?.value;
            const messageTemplateId = document.getElementById('message_template_id')?.value;
            
            // Validate required fields
            if (!recipient) {
                showToast('Please select a recipient', 'error');
                return;
            }
            
            // Show sending status
            showToast('Sending test email...', 'info');
            
            // Prepare form data
            const formData = new FormData();
            formData.append('recipient', recipient);
            formData.append('type', type);
            formData.append('subject', subject);
            formData.append('content', content);
            
            if (messageTemplateId) {
                formData.append('message_template_id', messageTemplateId);
            }
            
            // Send AJAX request
            fetch('/api/test/send-email', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Show result
                if (data.success) {
                    showToast('Test email sent successfully', 'success');
                    // Refresh notification history table
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

// Template selection functionality
function initTemplateSelection() {
    const templateTypeSelect = document.getElementById('template_type');
    const templateSelect = document.getElementById('message_template_id');
    const subjectInput = document.getElementById('subject');
    const contentTextarea = document.getElementById('content');
    const templateVariablesContainer = document.getElementById('template-variables-container');
    const templateVariablesList = document.getElementById('template-variables-list');
    const previewBtn = document.getElementById('preview-email-btn');
    const previewModal = document.getElementById('preview-modal');
    
    if (!templateTypeSelect || !templateSelect) {
        return; // If elements do not exist, return directly
    }
    
    // When notification type changes, load corresponding template
    templateTypeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        
        // Clear template selector
        templateSelect.innerHTML = '<option value="">Custom (No Template)</option>';
        
        // Get selected type templates
        fetch(`/api/message-templates?type=${selectedType}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.templates.length > 0) {
                    // Fill template options
                    data.templates.forEach(template => {
                        const option = document.createElement('option');
                        option.value = template.id;
                        option.textContent = template.name;
                        option.dataset.subject = template.subject;
                        option.dataset.content = template.content;
                        option.dataset.isDefault = template.is_default;
                        
                        templateSelect.appendChild(option);
                        
                        // If default template, auto select
                        if (template.is_default) {
                            option.selected = true;
                            // Trigger change event to load template content
                            templateSelect.dispatchEvent(new Event('change'));
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Failed to load templates:', error);
            });
    });
    
    // When template is selected, load template content
    templateSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            // Fill subject and content
            subjectInput.value = selectedOption.dataset.subject || '';
            contentTextarea.value = selectedOption.dataset.content || '';
            
            // Get template variables
            fetch(`/api/message-templates/${selectedOption.value}/variables`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.variables && templateVariablesContainer) {
                        // Show variable container
                        templateVariablesContainer.classList.remove('hidden');
                        
                        // Fill variable list
                        if (templateVariablesList) {
                            templateVariablesList.innerHTML = '';
                            Object.entries(data.variables).forEach(([variable, description]) => {
                                const div = document.createElement('div');
                                div.className = 'p-1';
                                div.innerHTML = `<code>${variable}</code>: ${description}`;
                                templateVariablesList.appendChild(div);
                            });
                        }
                    } else if (templateVariablesContainer) {
                        templateVariablesContainer.classList.add('hidden');
                    }
                })
                .catch(error => {
                    console.error('Failed to load template variables:', error);
                    if (templateVariablesContainer) {
                        templateVariablesContainer.classList.add('hidden');
                    }
                });
        } else if (subjectInput && contentTextarea) {
            // If no template selected, clear content
            subjectInput.value = 'Test Email';
            contentTextarea.value = 'This is a test email message from the system.';
            if (templateVariablesContainer) {
                templateVariablesContainer.classList.add('hidden');
            }
        }
    });
    
    // Initial load templates
    templateTypeSelect.dispatchEvent(new Event('change'));
    
    // Add preview button click event
    if (previewBtn) {
        previewBtn.addEventListener('click', showEmailPreview);
        
        // Close preview modal
        document.querySelectorAll('.close-preview-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                if (previewModal) {
                    previewModal.classList.add('hidden');
                    document.body.classList.remove('modal-open');
                }
            });
        });
    }
}

// Show email preview
function showEmailPreview() {
    const subject = document.getElementById('subject')?.value || '';
    const content = document.getElementById('content')?.value || '';
    const previewModal = document.getElementById('preview-modal');
    const previewSubject = document.getElementById('preview-subject');
    const previewContent = document.getElementById('preview-content');
    
    if (previewModal && previewSubject && previewContent) {
        // Fill preview content
        previewSubject.textContent = subject;
        previewContent.innerHTML = content;
        
        // Show modal
        previewModal.classList.remove('hidden');
        document.body.classList.add('modal-open');
        
        // Handle template variable display
        const templateVariables = document.querySelectorAll('#template-variables-list code');
        if (templateVariables.length > 0) {
            // Highlight template variables
            templateVariables.forEach(variable => {
                const variableName = variable.textContent;
                const regex = new RegExp(variableName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
                
                // Highlight variables in preview content
                previewContent.innerHTML = previewContent.innerHTML.replace(
                    regex,
                    `<span class="px-1 bg-yellow-100 text-yellow-800 rounded">${variableName}</span>`
                );
            });
        }
    }
}

// Add multiple event listeners to ensure correct initialization in various environments
    document.addEventListener('DOMContentLoaded', initNotificationHistory);
    document.addEventListener('turbolinks:load', initNotificationHistory);
    document.addEventListener('livewire:load', initNotificationHistory);

// Additional support for Livewire environment
// Listen for Livewire page update events
if (typeof window.Livewire !== 'undefined') {
    document.addEventListener('livewire:update', function() {
        // Reinitialize components, but keep global initialization state
        const container = document.querySelector('.notification-history-container');
        if (container) container.dataset.initialized = 'false';
        initNotificationHistory();
    });
} 