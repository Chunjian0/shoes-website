@props(['item', 'price', 'item_id', 'showControls' => true])

<div class="cart-item group relative flex flex-col sm:flex-row items-start sm:items-center p-4 mb-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-all duration-300"
     data-id="{{ $item->id }}" 
     data-item-id="{{ $item_id }}" 
     data-price="{{ $price }}"
     >
    {{-- Checkbox --}}
    <div class="flex-shrink-0 mr-3 mb-3 sm:mb-0 self-center sm:self-start">
        <input type="checkbox" class="item-checkbox form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
    </div>

    {{-- 商品图片 --}}
    <div class="relative w-full sm:w-24 h-24 flex-shrink-0 mb-3 sm:mb-0 sm:mr-4 overflow-hidden rounded-md">
        @if(isset($item->product) && $item->product->getFirstMediaUrl('product_images'))
            <img 
                class="w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-110" 
                src="{{ $item->product->getFirstMediaUrl('product_images') }}" 
                alt="{{ $item->product->name }}"
                loading="lazy"
            >
        @else
            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        @endif
        
        {{-- Hover effect overlay --}}
        <div class="absolute inset-0 bg-blue-600 bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 pointer-events-none"></div>
    </div>
    
    {{-- 商品详情 --}}
    <div class="flex-grow">
        <div class="flex flex-col sm:flex-row justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-800 hover:text-blue-600 transition-colors">
                    {{ $item->product->name }}
                </h3>
                <div class="text-sm text-gray-500 mt-1">
                    @if($item->specifications)
                        @foreach($item->specifications as $key => $value)
                            <span class="inline-block mr-2 bg-gray-100 rounded px-2 py-0.5 text-xs">{{ $key }}: {{ $value }}</span>
                        @endforeach
                    @endif
                </div>
                <div class="mt-2">
                    <span class="text-sm font-medium text-gray-900">{{ number_format($item->price, 2) }} × {{ $item->quantity }}</span>
                </div>
            </div>
            
            <div class="mt-3 sm:mt-0 flex items-end sm:items-start flex-col">
                <div class="text-base font-bold text-gray-900 item-total">{{ number_format($item->price * $item->quantity, 2) }}</div>
                
                @if($showControls)
                <div class="mt-2 flex items-center">
                    {{-- 数量控制 --}}
                    <div class="flex items-center border border-gray-300 rounded-md overflow-hidden">
                        <button class="quantity-btn minus-btn w-8 h-8 bg-gray-100 flex items-center justify-center transition-colors hover:bg-gray-200" data-action="decrease">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </button>
                        <input type="number" 
                               class="quantity-input item-quantity-input w-12 h-8 text-center border-0 text-gray-700" 
                               value="{{ $item->quantity }}" 
                               min="1" 
                               max="99">
                        <button class="quantity-btn plus-btn w-8 h-8 bg-gray-100 flex items-center justify-center transition-colors hover:bg-gray-200" data-action="increase">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                    
                    {{-- 删除按钮 --}}
                    <button class="remove-item ml-2 w-8 h-8 bg-red-100 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition-colors duration-200 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    {{-- 加载指示器 (默认隐藏) --}}
    <div class="loading-indicator hidden absolute inset-0 bg-white bg-opacity-70 flex items-center justify-center">
        <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-600"></div>
    </div>
</div> 