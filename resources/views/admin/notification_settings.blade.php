@extends('layouts.admin')

@section('title', 'Notification Settings')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Notification Settings
    </h2>
    
    <!-- Success message -->
    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif
    
    <!-- Error message -->
    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif
    
    <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md">
        <form action="{{ route('admin.settings.notifications.update') }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Low Stock Notifications</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Configure who receives notifications when products with low stock are removed from the homepage.
                </p>
                
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-base font-medium text-gray-900">Low Stock Product Removal Notification</h3>
                                </div>
                            </div>

                            <div>
                                <button type="button" class="text-blue-600 hover:text-blue-800 text-sm font-medium focus:outline-none toggle-recipients" data-type="low_stock">
                                    Select Recipients
                                    <svg class="inline-block ml-1 w-4 h-4 toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="recipient-selector hidden" id="selector-low_stock">
                            <div class="border border-gray-300 rounded-md shadow-sm overflow-y-auto max-h-60">
                                <div class="p-2 border-b border-gray-200 bg-gray-50 sticky top-0">
                                    <input type="text" class="recipient-search w-full p-2 border border-gray-300 rounded-md" placeholder="Search users..." data-type="low_stock">
                                </div>
                                <div class="user-list p-2" data-type="low_stock">
                                    @foreach($users as $user)
                                    <label class="user-item flex items-center p-2 hover:bg-gray-50 rounded-md cursor-pointer mb-1">
                                        <input type="checkbox" name="receivers[low_stock_notification_receivers][]" value="{{ $user->email }}" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        @if(in_array($user->email, $lowStockReceivers)) checked @endif>
                                        <div class="ml-3 flex items-center">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-blue-800 font-medium">{{ substr($user->name, 0, 1) }}</span>
                                            </div>
                                            <div class="ml-2">
                                                <p class="text-sm font-medium text-gray-700">{{ $user->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="mt-2">
                                <div class="flex space-x-2">
                                    <button type="button" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 select-all-btn" data-type="low_stock">
                                        Select All
                                    </button>
                                    <button type="button" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 deselect-all-btn" data-type="low_stock">
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 selected-recipients" id="selected-low_stock">
                            @if(count($lowStockReceivers) > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($lowStockReceivers as $email)
                                        <span class="inline-flex items-center bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                            {{ $email }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-sm text-gray-500 italic">No recipients selected</div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <label for="min_stock_threshold" class="block text-sm font-medium text-gray-700">Minimum Stock Threshold</label>
                    <div class="mt-1">
                        <input type="number" name="min_stock_threshold" id="min_stock_threshold" 
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            value="{{ $minStockThreshold }}" min="1" max="100">
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        Products with stock below this threshold will be automatically removed from the homepage.
                    </p>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle recipient selector
        document.querySelectorAll('.toggle-recipients').forEach(button => {
            button.addEventListener('click', function() {
                const type = this.getAttribute('data-type');
                const selector = document.getElementById(`selector-${type}`);
                selector.classList.toggle('hidden');
                this.querySelector('.toggle-icon').classList.toggle('transform');
                this.querySelector('.toggle-icon').classList.toggle('rotate-180');
            });
        });

        // Search functionality
        document.querySelectorAll('.recipient-search').forEach(input => {
            input.addEventListener('input', function() {
                const type = this.getAttribute('data-type');
                const searchTerm = this.value.toLowerCase();
                const userList = document.querySelector(`.user-list[data-type="${type}"]`);
                
                userList.querySelectorAll('.user-item').forEach(item => {
                    const name = item.querySelector('.text-gray-700').textContent.toLowerCase();
                    const email = item.querySelector('.text-gray-500').textContent.toLowerCase();
                    
                    if (name.includes(searchTerm) || email.includes(searchTerm)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // Select all button
        document.querySelectorAll('.select-all-btn').forEach(button => {
            button.addEventListener('click', function() {
                const type = this.getAttribute('data-type');
                const userList = document.querySelector(`.user-list[data-type="${type}"]`);
                
                userList.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    if (checkbox.closest('.user-item').style.display !== 'none') {
                        checkbox.checked = true;
                    }
                });
                
                updateSelectedRecipients(type);
            });
        });

        // Deselect all button
        document.querySelectorAll('.deselect-all-btn').forEach(button => {
            button.addEventListener('click', function() {
                const type = this.getAttribute('data-type');
                const userList = document.querySelector(`.user-list[data-type="${type}"]`);
                
                userList.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                updateSelectedRecipients(type);
            });
        });

        // Update selected recipients when checkbox changes
        document.querySelectorAll('.user-list input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const type = this.closest('.user-list').getAttribute('data-type');
                updateSelectedRecipients(type);
            });
        });

        // Function to update the selected recipients display
        function updateSelectedRecipients(type) {
            const userList = document.querySelector(`.user-list[data-type="${type}"]`);
            const selectedContainer = document.getElementById(`selected-${type}`);
            const checkedBoxes = userList.querySelectorAll('input[type="checkbox"]:checked');
            
            if (checkedBoxes.length > 0) {
                let html = '<div class="flex flex-wrap gap-2">';
                
                checkedBoxes.forEach(checkbox => {
                    const email = checkbox.value;
                    html += `<span class="inline-flex items-center bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                ${email}
                            </span>`;
                });
                
                html += '</div>';
                selectedContainer.innerHTML = html;
            } else {
                selectedContainer.innerHTML = '<div class="text-sm text-gray-500 italic">No recipients selected</div>';
            }
        }
    });
</script>
@endpush
@endsection 