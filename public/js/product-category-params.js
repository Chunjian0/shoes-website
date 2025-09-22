/**
 * Product Category Parameters API Handler
 */
(function() {
    'use strict';
    
    // Initialize flag to prevent multiple initializations
    let isInitialized = false;
    
    /**
     * Main initialization function
     */
    function init() {
        if (isInitialized) return;
        isInitialized = true;
        
        setupEventListeners();
    }
    
    /**
     * Setup event listeners
     */
    function setupEventListeners() {
        const categorySelect = document.getElementById('category_id');
        if (!categorySelect) return;
        
        // Remove existing event listener to prevent duplicates
        categorySelect.removeEventListener('change', handleCategoryChange);
        categorySelect.addEventListener('change', handleCategoryChange);
        
        // If category is already selected on page load, fetch parameters
        if (categorySelect.value) {
            fetchCategoryParameters(categorySelect.value);
        }
        
        // Reset isInitialized flag when leaving the page
        setTimeout(() => {
            isInitialized = false;
        }, 100);
    }
    
    /**
     * Handle category selection change
     * @param {Event} event - Change event
     */
    function handleCategoryChange(event) {
        const categoryId = event.target.value;
        if (!categoryId) {
            hideParametersCard();
            return;
        }
        
        fetchCategoryParameters(categoryId);
    }
    
    /**
     * Fetch category parameters via API
     * @param {string|number} categoryId - Category ID
     */
    function fetchCategoryParameters(categoryId) {
        fetch(`/api/product-categories/${categoryId}/parameters`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    renderParameters(data.data);
                } else {
                    console.error('Error fetching parameters:', data.message || 'Unknown error');
                    showToast('Error loading parameters', 'error');
                }
            })
            .catch(error => {
                console.error('Failed to fetch parameters:', error);
                showToast('Failed to load parameters', 'error');
            });
    }
    
    /**
     * Render parameters in the UI
     * @param {Array} parameters - Parameters to render
     */
    function renderParameters(parameters) {
        const parametersCard = document.getElementById('parameters-card');
        const parametersContainer = document.getElementById('parameters-container');
        
        if (!parametersCard || !parametersContainer) return;
        
        if (!parameters || parameters.length === 0) {
            hideParametersCard();
            return;
        }
        
        // Show the parameters card
        parametersCard.style.display = 'block';
        parametersContainer.innerHTML = '';
        
        // Render each parameter
        parameters.forEach(param => {
            const div = document.createElement('div');
            
            const labelDiv = document.createElement('div');
            labelDiv.className = 'flex items-center justify-between mb-2';
            
            const label = document.createElement('label');
            label.className = `block text-sm font-medium text-gray-700 ${param.is_required ? 'required-field' : ''}`;
            label.textContent = param.name;
            label.setAttribute('for', `parameters.${param.code}`);
            
            const typeSpan = document.createElement('span');
            typeSpan.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                param.type === 'select' ? 'bg-blue-100 text-blue-800' :
                param.type === 'number' ? 'bg-green-100 text-green-800' :
                'bg-gray-100 text-gray-800'
            }`;
            typeSpan.textContent = param.type === 'select' ? 'Selection' : 
                               param.type === 'number' ? 'Value' : 'text';
            
            labelDiv.appendChild(label);
            labelDiv.appendChild(typeSpan);
            div.appendChild(labelDiv);

            if (param.type === 'select') {
                const select = document.createElement('select');
                select.className = 'mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md';
                select.name = `parameters[${param.code}]`;
                select.id = `parameters.${param.code}`;
                select.required = param.is_required;

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Please select';
                select.appendChild(defaultOption);

                param.options.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option;
                    optionElement.textContent = option;
                    select.appendChild(optionElement);
                });

                div.appendChild(select);
            } else {
                const input = document.createElement('input');
                input.type = param.type;
                input.className = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm';
                input.name = `parameters[${param.code}]`;
                input.id = `parameters.${param.code}`;
                input.required = param.is_required;
                if (param.type === 'number') {
                    input.step = 'any';
                }
                div.appendChild(input);
            }

            const helpText = document.createElement('p');
            helpText.className = 'mt-2 text-xs text-gray-500';
            helpText.textContent = param.is_required ? 'This parameter is required' : 'Optional parameters';
            div.appendChild(helpText);

            parametersContainer.appendChild(div);
        });
    }
    
    /**
     * Hide the parameters card
     */
    function hideParametersCard() {
        const parametersCard = document.getElementById('parameters-card');
        if (parametersCard) {
            parametersCard.style.display = 'none';
        }
    }
    
    /**
     * Show toast notification
     * @param {string} message - Toast message
     * @param {string} type - Toast type (success, error, info)
     */
    function showToast(message, type = 'info') {
        // Check if we have a toast container
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'fixed bottom-5 right-5 z-50 flex flex-col space-y-2';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `p-3 rounded-lg shadow-lg transform transition-all duration-300 ease-in-out max-w-xs ${
            type === 'success' ? 'bg-green-50 border-l-4 border-green-400' :
            type === 'error' ? 'bg-red-50 border-l-4 border-red-400' :
            'bg-blue-50 border-l-4 border-blue-400'
        }`;
        
        // Add message
        toast.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 ${
                        type === 'success' ? 'text-green-400' :
                        type === 'error' ? 'text-red-400' :
                        'text-blue-400'
                    }" fill="currentColor" viewBox="0 0 20 20">
                        ${type === 'error' ? 
                            '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>' :
                            type === 'success' ?
                            '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>' :
                            '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>'
                        }
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm ${
                        type === 'success' ? 'text-green-700' :
                        type === 'error' ? 'text-red-700' :
                        'text-blue-700'
                    }">
                        ${message}
                    </p>
                </div>
            </div>
        `;
        
        // Add to container
        toastContainer.appendChild(toast);
        
        // Remove after timeout
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-x-full');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }
    
    // Listen for various page load events to initialize
    document.addEventListener('DOMContentLoaded', init);
    document.addEventListener('turbolinks:load', init);
    document.addEventListener('page:load', init); // Turbolinks classic
    document.addEventListener('turbo:load', init); // Turbo from Hotwire
    
    // If the document is already loaded, initialize immediately
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        init();
    }
    
    // Expose public methods if needed
    window.ProductCategoryParams = {
        init,
        fetchParameters: fetchCategoryParameters
    };
})(); 