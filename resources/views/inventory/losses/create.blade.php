<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            New inventory loss report
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('inventory.losses.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="loss_date" class="block text-sm font-medium text-gray-700">Loss date</label>
                            <input type="date" name="loss_date" id="loss_date" value="{{ old('loss_date', date('Y-m-d')) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('loss_date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="reason" class="block text-sm font-medium text-gray-700">Reason for reporting damage</label>
                            <input type="text" name="reason" id="reason" value="{{ old('reason') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('reason')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Remark</label>
                            <textarea name="notes" id="notes" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Report damage products</h3>
                            <div class="mt-4 space-y-4" id="items-container">
                                <!-- The product list will be passedJavaScriptDynamically added -->
                            </div>
                            <button type="button" id="add-item" class="mt-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Add products
                            </button>
                            @error('items')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end mt-6">
                            <a href="{{ route('inventory.losses.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </a>
                            <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                keep
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const products = @json($products);
        let itemCount = 0;

        document.getElementById('add-item').addEventListener('click', function() {
            const container = document.getElementById('items-container');
            const itemHtml = `
                <div class="flex items-center gap-4 item-row">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700">merchandise</label>
                        <select name="items[${itemCount}][product_id]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Please select a product</option>
                            ${products.map(product => `
                                <option value="${product.id}" data-inventory="${product.inventory_count}">
                                    ${product.name} (Current inventory: ${product.inventory_count})
                                </option>
                            `).join('')}
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700">Losses reported</label>
                        <input type="number" name="items[${itemCount}][quantity]" required min="1"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700">Remark</label>
                        <input type="text" name="items[${itemCount}][notes]"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="flex items-end">
                        <button type="button" class="remove-item mb-1 text-red-600 hover:text-red-900">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = itemHtml;
            const itemElement = tempDiv.firstElementChild;
            
            // Add and delete event listener
            itemElement.querySelector('.remove-item').addEventListener('click', function() {
                itemElement.remove();
            });

            // Add quantity verification
            const quantityInput = itemElement.querySelector('input[type="number"]');
            const productSelect = itemElement.querySelector('select');
            
            productSelect.addEventListener('change', function() {
                const option = this.options[this.selectedIndex];
                const inventory = parseInt(option.dataset.inventory);
                quantityInput.max = inventory;
            });
            
            quantityInput.addEventListener('input', function() {
                const option = productSelect.options[productSelect.selectedIndex];
                const inventory = parseInt(option.dataset.inventory);
                if (this.value > inventory) {
                    this.value = inventory;
                }
            });
            
            container.appendChild(itemElement);
            itemCount++;
        });
    </script>
    @endpush
</x-app-layout> 