@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-bold mb-4">DOM Loading & JavaScript Test Page</h1>
                
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-2">Test Results</h2>
                    <div id="test-results" class="p-4 bg-gray-100 rounded-lg">
                        <p>Test results will appear here...</p>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-2">jQuery & Select2 Test</h2>
                    <div class="mb-4">
                        <label for="test-select" class="block text-sm font-medium text-gray-700">Test Select2</label>
                        <select id="test-select" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" multiple>
                            <option value="1">Option 1</option>
                            <option value="2">Option 2</option>
                            <option value="3">Option 3</option>
                            <option value="4">Option 4</option>
                            <option value="5">Option 5</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-2">Custom Selector Test</h2>
                    <div class="mb-4">
                        <button type="button" id="toggle-custom-selector" class="text-blue-600 hover:text-blue-800 text-sm font-medium focus:outline-none">
                            Toggle Custom Selector
                            <svg class="inline-block ml-1 w-4 h-4 toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div id="custom-selector" class="mt-2 hidden">
                            <div class="border border-gray-300 rounded-md shadow-sm overflow-y-auto max-h-60">
                                <div class="p-2 border-b border-gray-200 bg-gray-50 sticky top-0">
                                    <input type="text" id="custom-search" class="w-full p-2 border border-gray-300 rounded-md" placeholder="Search items...">
                                </div>
                                <div class="custom-list p-2">
                                    @for ($i = 1; $i <= 5; $i++)
                                    <label class="custom-item flex items-center p-2 hover:bg-gray-50 rounded-md cursor-pointer mb-1">
                                        <input type="checkbox" name="custom_items[]" value="{{ $i }}" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-700">Item {{ $i }}</span>
                                        </div>
                                    </label>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <button id="run-tests" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Run Tests
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-checkbox {
        appearance: none;
        -webkit-appearance: none;
        padding: 0;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
        display: inline-block;
        vertical-align: middle;
        background-origin: border-box;
        user-select: none;
        height: 1rem;
        width: 1rem;
        color: #3b82f6;
        background-color: #fff;
        border: 1px solid #d1d5db;
        border-radius: 0.25rem;
    }
    
    .form-checkbox:checked {
        background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
        background-color: currentColor;
        border-color: transparent;
        background-position: center;
        background-repeat: no-repeat;
        background-size: 100% 100%;
    }
</style>
@endpush

@push('scripts')
<script>
// Test Manager
if (typeof window.TestManager === 'undefined') {
    window.TestManager = {
        results: [],
        
        init: function() {
            this.setupEventListeners();
            this.logResult('Test Manager initialized');
        },
        
        setupEventListeners: function() {
            // Run tests button
            const runTestsBtn = document.getElementById('run-tests');
            if (runTestsBtn) {
                runTestsBtn.addEventListener('click', () => this.runTests());
            }
            
            // Toggle custom selector
            const toggleBtn = document.getElementById('toggle-custom-selector');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => this.toggleCustomSelector());
            }
            
            // Custom search
            const searchInput = document.getElementById('custom-search');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => this.filterCustomItems(e.target.value));
            }
        },
        
        toggleCustomSelector: function() {
            const selector = document.getElementById('custom-selector');
            const toggleBtn = document.getElementById('toggle-custom-selector');
            
            if (selector) {
                selector.classList.toggle('hidden');
                
                if (toggleBtn) {
                    const icon = toggleBtn.querySelector('.toggle-icon');
                    if (icon) {
                        icon.classList.toggle('rotate-180');
                    }
                }
            }
        },
        
        filterCustomItems: function(query) {
            const items = document.querySelectorAll('.custom-item');
            query = query.toLowerCase();
            
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(query)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        },
        
        runTests: function() {
            this.results = [];
            this.logResult('Starting tests...');
            
            // Test jQuery availability
            this.testJQuery();
            
            // Test Select2 availability
            this.testSelect2();
            
            // Test DOM manipulation
            this.testDOMManipulation();
            
            // Test custom selector
            this.testCustomSelector();
            
            // Display final results
            this.displayResults();
        },
        
        testJQuery: function() {
            try {
                if (typeof $ !== 'undefined') {
                    this.logResult('✅ jQuery is available: ' + $.fn.jquery);
                } else {
                    this.logResult('❌ jQuery is NOT available');
                }
            } catch (error) {
                this.logResult('❌ Error testing jQuery: ' + error.message);
            }
        },
        
        testSelect2: function() {
            try {
                if (typeof $ !== 'undefined' && $.fn.select2) {
                    this.logResult('✅ Select2 is available');
                    
                    // Try to initialize Select2
                    try {
                        $('#test-select').select2({
                            placeholder: 'Select options',
                            allowClear: true
                        });
                        this.logResult('✅ Select2 initialized successfully');
                    } catch (initError) {
                        this.logResult('❌ Error initializing Select2: ' + initError.message);
                    }
                } else {
                    this.logResult('❌ Select2 is NOT available');
                }
            } catch (error) {
                this.logResult('❌ Error testing Select2: ' + error.message);
            }
        },
        
        testDOMManipulation: function() {
            try {
                // Create a test element
                const testDiv = document.createElement('div');
                testDiv.id = 'test-dom-element';
                testDiv.textContent = 'Test DOM Element';
                document.body.appendChild(testDiv);
                
                // Check if element was added
                const addedElement = document.getElementById('test-dom-element');
                if (addedElement) {
                    this.logResult('✅ DOM manipulation successful');
                    // Clean up
                    document.body.removeChild(addedElement);
                } else {
                    this.logResult('❌ DOM manipulation failed');
                }
            } catch (error) {
                this.logResult('❌ Error testing DOM manipulation: ' + error.message);
            }
        },
        
        testCustomSelector: function() {
            try {
                // Test toggle functionality
                const selector = document.getElementById('custom-selector');
                if (selector) {
                    const wasHidden = selector.classList.contains('hidden');
                    this.toggleCustomSelector();
                    const isNowHidden = selector.classList.contains('hidden');
                    
                    if (wasHidden !== isNowHidden) {
                        this.logResult('✅ Custom selector toggle works');
                    } else {
                        this.logResult('❌ Custom selector toggle failed');
                    }
                    
                    // Reset to hidden state
                    if (!isNowHidden) {
                        this.toggleCustomSelector();
                    }
                } else {
                    this.logResult('❌ Custom selector element not found');
                }
                
                // Test search functionality
                const searchInput = document.getElementById('custom-search');
                if (searchInput) {
                    // Simulate search for "Item 1"
                    searchInput.value = "Item 1";
                    const event = new Event('input', { bubbles: true });
                    searchInput.dispatchEvent(event);
                    
                    // Check if filtering worked
                    const items = document.querySelectorAll('.custom-item');
                    let visibleCount = 0;
                    items.forEach(item => {
                        if (item.style.display !== 'none') {
                            visibleCount++;
                        }
                    });
                    
                    if (visibleCount === 1) {
                        this.logResult('✅ Custom selector search works');
                    } else {
                        this.logResult(`❌ Custom selector search failed (${visibleCount} items visible)`);
                    }
                    
                    // Reset search
                    searchInput.value = "";
                    searchInput.dispatchEvent(event);
                } else {
                    this.logResult('❌ Custom search input not found');
                }
            } catch (error) {
                this.logResult('❌ Error testing custom selector: ' + error.message);
            }
        },
        
        logResult: function(message) {
            this.results.push(message);
            console.log(message);
        },
        
        displayResults: function() {
            const resultsContainer = document.getElementById('test-results');
            if (resultsContainer) {
                resultsContainer.innerHTML = this.results.map(result => 
                    `<div class="py-1">${result}</div>`
                ).join('');
            }
        }
    };
}

// Initialize on DOM content loaded
document.addEventListener('DOMContentLoaded', function() {
    if (window.TestManager) {
        window.TestManager.init();
    }
});

// Also initialize on window load as a fallback
window.addEventListener('load', function() {
    if (window.TestManager && !window.TestManager.initialized) {
        window.TestManager.init();
    }
});
</script>
@endpush 