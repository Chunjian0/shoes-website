<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Product Template Details') }}: {{ $productTemplate->name }}
            </h2>
            <div class="flex space-x-2 flex-wrap justify-end items-center">
                <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/products/{{ $productTemplate->id }}" target="_blank" 
                   class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded inline-flex items-center text-sm">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    {{ __('View on Storefront') }}
                </a>
                
                @if($productTemplate->promo_page_url)
                <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}{{ $productTemplate->promo_page_url }}" target="_blank" 
                   class="bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded inline-flex items-center text-sm">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    {{ __('View Promo Page') }}
                </a>
                @endif

                <a href="{{ route('products.index', ['link_template' => $productTemplate->id]) }}" 
                   class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center text-sm">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m-8 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    {{ __('Link Existing Products') }}
                </a>
                <a href="{{ route('product-templates.edit', $productTemplate) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center text-sm">
                   <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('product-templates.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center text-sm">
                   <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z" clip-rule="evenodd" /></svg>
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Tab Navigation -->
            <div class="bg-white rounded-t-lg border-b">
                <nav class="-mb-px flex" aria-label="Tabs">
                    <button id="info-btn" class="tab-btn tab-active px-6 py-3 font-medium text-sm" data-target="info">{{ __('Information') }}</button>
                    <button id="products-btn" class="tab-btn px-6 py-3 font-medium text-sm" data-target="products">{{ __('Products') }} ({{ $products->total() }})</button>
                </nav>
            </div>

            <!-- Overview Tab (默认显示) -->
            <div id="info" class="tab-content">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Basic Information Card -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Basic Information') }}</h3>
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Name') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $productTemplate->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Category') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $productTemplate->category->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Brand') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $productTemplate->brand ?: __('Not specified') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Model') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $productTemplate->model ?: __('Not specified') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Base Price') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($productTemplate->base_price, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                                    <dd class="mt-1 text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $productTemplate->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $productTemplate->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Created') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $productTemplate->created_at->format('Y-m-d H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Last Updated') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $productTemplate->updated_at->format('Y-m-d H:i') }}</dd>
                                </div>

                                <!-- Added Promo Page URL -->
                                @if($productTemplate->promo_page_url)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Promo Page URL') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <a href="{{ url($productTemplate->promo_page_url) }}" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline">
                                            {{ $productTemplate->promo_page_url }}
                                        </a>
                                    </dd>
                                </div>
                                @endif
                                <!-- End Added Promo Page URL -->
                            </dl>
                        </div>
                        </div>

                    <!-- Description Card -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg lg:col-span-2">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Description') }}</h3>
                            <div class="prose max-w-none">
                                @if($productTemplate->description)
                                    <div class="product-description-container" style="max-height:800px; overflow-y:auto;">
                                        {!! $productTemplate->description !!}
                                    </div>
                                @else
                                    <p class="text-gray-500 italic">{{ __('No description provided.') }}</p>
                                @endif
                            </div>
                        </div>
                        </div>

                    <!-- Images Card -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg lg:col-span-3">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Images') }}</h3>
                            @if(($productTemplate->media && $productTemplate->media->count() > 0) || !empty($productTemplate->images))
                                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                    @if($productTemplate->media && $productTemplate->media->count() > 0)
                                        @foreach($productTemplate->media as $index => $media)
                                            <div class="relative group">
                                                <div class="aspect-w-1 aspect-h-1 overflow-hidden rounded-lg bg-gray-100">
                                                    <img src="{{ Storage::url($media->path) }}" alt="{{ $media->name ?? 'Image ' . ($index + 1) }}" class="object-cover" loading="lazy">
                                                </div>
                                                <div class="mt-1 text-xs text-center text-gray-500 truncate">
                                                    {{ $media->name ?? 'Image ' . ($index + 1) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    @elseif(!empty($productTemplate->images))
                                    @foreach($productTemplate->images as $index => $image)
                                        <div class="relative group">
                                            <div class="aspect-w-1 aspect-h-1 overflow-hidden rounded-lg bg-gray-100">
                                                @if(is_array($image) && isset($image['url']))
                                                        <img src="{{ Storage::url($image['url']) }}" alt="{{ $image['name'] ?? 'Image ' . ($index + 1) }}" class="object-cover" loading="lazy">
                                                @elseif(is_string($image))
                                                        @if(strpos($image, 'http') === 0)
                                                            <img src="{{ $image }}" alt="Image {{ ($index + 1) }}" class="object-cover" loading="lazy">
                                                        @else
                                                            <img src="{{ Storage::url($image) }}" alt="Image {{ ($index + 1) }}" class="object-cover" loading="lazy">
                                                        @endif
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                                            <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="mt-1 text-xs text-center text-gray-500 truncate">
                                                    @if(is_array($image) && isset($image['name']))
                                                        {{ $image['name'] }}
                                                    @else
                                                        Image {{ ($index + 1) }}
                                                @endif
                                                </div>
                                        </div>
                                    @endforeach
                                    @endif
                                </div>
                            @else
                                <p class="text-gray-500 italic">{{ __('No images uploaded.') }}</p>
                            @endif
                        </div>
                                            </div>

                    <!-- Parameters Card -->
                    @if(!empty($productTemplate->parameters))
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg lg:col-span-3">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">{{ __('Parameters') }}</h3>
                                    <a href="{{ route('products.index', ['link_template' => $productTemplate->id]) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                        <svg class="-ml-1 mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m-8 6H4m0 0l4 4m-4-4l4-4"></path>
                                        </svg>
                                        {{ __('Link Existing Products') }}
                                    </a>
                                </div>

                                <!-- 参数组合部分 -->
                                <div>
                                    <h4 class="text-lg font-semibold mb-2">Parameter Combinations</h4>
                                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead>
                                                    <tr>
                                        @if(is_array($productTemplate->parameters))
                                            @foreach($productTemplate->parameters as $parameter)
                                                                @if(isset($parameter['name']))
                                                                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                    {{ $parameter['name'] }}
                                                                </th>
                                                                    @endif
                                        @endforeach
                                                        @endif
                                                        <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Status
                                                        </th>
                                                        <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Actions
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200" id="param-combinations">
                                                    <tr>
                                                        <td colspan="{{ count($productTemplate->parameters) + 2 }}" class="px-4 py-3 text-center text-sm text-gray-500">
                                                            <div class="flex justify-center items-center py-4">
                                                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                                </svg>
                                                                Generating parameter combinations...
                                </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                            @endif
                </div>
            </div>

            <!-- Products Tab -->
            <div id="products" class="tab-content hidden">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Linked Products') }}</h3>
                            <a href="{{ route('products.index', ['link_template' => $productTemplate->id]) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring focus:ring-green-300 disabled:opacity-25 transition">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m-8 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                {{ __('Link Existing Products') }}
                            </a>
                        </div>

                        @if($productTemplate->linkedProducts->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr>
                                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">{{ __('SKU') }}</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">{{ __('Parameter Group') }}</th>
                                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">{{ __('Price') }}</th>
                                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">{{ __('Stock') }}</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($productTemplate->linkedProducts as $product)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            @if(isset($product->images) && is_array($product->images) && count($product->images) > 0)
                                                                @if(is_array($product->images[0]) && isset($product->images[0]['url']))
                                                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ Storage::url($product->images[0]['url']) }}" alt="{{ $product->name }}">
                                                                @elseif(is_string($product->images[0]))
                                                                    @if(strpos($product->images[0], 'http') === 0)
                                                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ $product->images[0] }}" alt="{{ $product->name }}">
                                                                    @else
                                                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ Storage::url($product->images[0]) }}" alt="{{ $product->name }}">
                                                                    @endif
                                                                @endif
                                                            @else
                                                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                    </svg>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                                            <div class="text-xs text-gray-500">{{ $product->brand }} {{ $product->model }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->sku }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    @if($product->pivot->parameter_group)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ $product->pivot->parameter_group }}
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400 italic">{{ __('Default') }}</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    @if($product->is_sale && $product->discount_percentage > 0)
                                                        <span class="line-through text-gray-400">{{ number_format($product->selling_price, 2) }}</span>
                                                        <span class="text-green-600 font-semibold">{{ number_format($product->selling_price * (1 - $product->discount_percentage / 100), 2) }}</span>
                                                    @else
                                                        {{ number_format($product->selling_price, 2) }}
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($product->stock <= 0)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            {{ __('Out of stock') }}
                                                        </span>
                                                    @elseif($product->stock < $product->min_stock)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            {{ $product->stock }} {{ __('(Low)') }}
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            {{ $product->stock }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ $product->is_active ? __('Active') : __('Inactive') }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex space-x-3">
                                                        <a href="{{ route('products.show', $product) }}" class="text-indigo-600 hover:text-indigo-900">
                                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                            </svg>
                                                        </a>
                                                        <form action="{{ route('product-templates.link-product') }}" method="POST" class="inline-block unlink-product-form" onsubmit="return false;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="template_id" value="{{ $productTemplate->id }}">
                                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                            @if($product->pivot->parameter_group)
                                                                <input type="hidden" name="parameter_group" value="{{ $product->pivot->parameter_group }}">
                                                            @endif
                                                            <button type="button" class="text-red-600 hover:text-red-900 unlink-product-btn">
                                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No products') }}</h3>
                                <p class="mt-1 text-sm text-gray-500">{{ __('Link products to this template to get started.') }}</p>
                                <div class="mt-6">
                                    <a href="{{ route('products.index', ['link_template' => $productTemplate->id]) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m-8 6H4m0 0l4 4m-4-4l4-4"></path>
                                        </svg>
                                        {{ __('Link Existing Products') }}
                                    </a>
                                </div>
                            </div>
                            @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // IIFE以避免全局变量污染和重复初始化
        (function() {
            let isInitialized = false;
            let templatePageLoaded = false;
            
            // 主初始化函数 - 所有页面功能初始化的入口点
            function initializeTemplatePage() {
                if (isInitialized) return;
                console.log('Initializing product template page...');
                isInitialized = true;
                
                // 初始化标签切换
                initializeTabs();
                
                // 初始化图片预览
                initializeImageGallery();
                
                // 加载参数组合
                if (!templatePageLoaded) {
                    loadParameterCombinations();
                    templatePageLoaded = true;
                }
                
                // 初始化解除链接按钮
                setupUnlinkButtons();
            }
            
            // 初始化标签切换
            function initializeTabs() {
                const tabBtns = document.querySelectorAll('.tab-btn');
                if (!tabBtns.length) return;
                
                tabBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        // 移除所有标签的活动状态
                        tabBtns.forEach(b => {
                            b.classList.remove('tab-active');
                            b.classList.remove('border-indigo-500', 'text-indigo-600');
                            b.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                        });
                        
                        // 为当前标签添加活动状态
                        this.classList.add('tab-active');
                        this.classList.add('border-indigo-500', 'text-indigo-600');
                        this.classList.remove('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                        
                        // 隐藏所有标签内容
                        document.querySelectorAll('.tab-content').forEach(content => {
                            content.classList.add('hidden');
                        });
                        
                        // 显示目标标签内容
                        const targetId = this.getAttribute('data-target');
                        const targetContent = document.getElementById(targetId);
                        if (targetContent) {
                            targetContent.classList.remove('hidden');
                        }
                    });
                });
            }
            
            // 初始化图片库
            function initializeImageGallery() {
                const imageContainers = document.querySelectorAll('.aspect-w-1.aspect-h-1 img');
                if (!imageContainers.length) return;
                
                // 为每个图片添加懒加载属性和加载状态处理
                imageContainers.forEach(img => {
                    // 避免重复初始化
                    if (img.hasAttribute('data-initialized')) return;
                    
                    // 标记为已初始化
                    img.setAttribute('data-initialized', 'true');
                    
                    // 添加懒加载属性
                    img.setAttribute('loading', 'lazy');
                    
                    // 保存原始源
                    const originalSrc = img.getAttribute('src');
                    
                    // 添加图片加载事件处理
                    img.addEventListener('error', function() {
                        // 图片加载错误时显示替代图像
                        console.warn('Image failed to load:', originalSrc);
                        this.src = '/images/placeholder.jpg'; // 确保有一个默认占位图
                        this.classList.add('bg-gray-100');
                        
                        // 添加错误指示器
                        const container = this.closest('.aspect-w-1.aspect-h-1');
                        if (container) {
                            const errorBadge = document.createElement('div');
                            errorBadge.className = 'absolute top-0 right-0 p-1 bg-red-500 text-white text-xs rounded-bl';
                            errorBadge.innerHTML = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                            container.appendChild(errorBadge);
                        }
                    });
                    
                    // 添加加载完成事件处理
                    img.addEventListener('load', function() {
                        this.classList.add('transition-opacity', 'duration-300', 'opacity-100');
                        this.classList.remove('opacity-0');
                        
                        // 移除任何可能存在的加载指示器
                        const container = this.closest('.aspect-w-1.aspect-h-1');
                        if (container) {
                            const loader = container.querySelector('.image-loader');
                            if (loader) loader.remove();
                        }
                    });
                    
                    // 初始设置为透明并添加加载指示器
                    img.classList.add('opacity-0');
                    const container = img.closest('.aspect-w-1.aspect-h-1');
                    if (container) {
                        const loader = document.createElement('div');
                        loader.className = 'image-loader absolute inset-0 flex items-center justify-center';
                        loader.innerHTML = '<svg class="animate-spin h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                        container.appendChild(loader);
                    }
                    
                    // 触发图片加载
                    img.src = originalSrc;
                });
                
                // 图片点击事件 - 放大查看
                imageContainers.forEach(img => {
                    img.addEventListener('click', function() {
                        const imageSrc = this.getAttribute('src');
                        const imageAlt = this.getAttribute('alt') || 'Product image';
                        
                        Swal.fire({
                            imageUrl: imageSrc,
                            imageAlt: imageAlt,
                            width: 'auto',
                            padding: '1em',
                            showConfirmButton: false,
                            showCloseButton: true,
                            backdrop: `rgba(0,0,0,0.9)`
                        });
                    });
                    
                    // 添加鼠标悬停样式
                    img.style.cursor = 'pointer';
                    const container = img.closest('.relative.group');
                    if (container) {
                        const overlay = document.createElement('div');
                        overlay.className = 'absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition-opacity duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100';
                        overlay.innerHTML = '<svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>';
                        container.appendChild(overlay);
                    }
                });
            }
            
            // 加载参数组合
            function loadParameterCombinations() {
                const templateId = '{{ $productTemplate->id }}';
                if (!templateId) return;
                
                const combinationsTable = document.getElementById('param-combinations');
                if (!combinationsTable) return;
                
                // 显示加载状态
                const loadingContent = `
                    <tr>
                        <td colspan="100%" class="px-4 py-3 text-center text-sm text-gray-500">
                            <div class="flex justify-center items-center py-4">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Loading parameter combinations...
                            </div>
                        </td>
                    </tr>
                `;
                combinationsTable.innerHTML = loadingContent;
                
                // 从备用文件获取所有参数组合
                fetch(`/param_debug.php?template_id=${templateId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Failed to load parameter combinations: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Loaded parameter combinations:', data);
                    
                    if (data.status === 'error') {
                        throw new Error(data.message || 'Unknown error loading parameter combinations');
                    }
                    
                    // 取得组合数据
                    const combinations = data.combinations || [];
                    
                    // 创建表格内容
                    let tableContent = '';
                    
                    if (combinations.length === 0) {
                        tableContent = `
                            <tr>
                                <td colspan="100%" class="px-4 py-3 text-center text-sm text-gray-500">
                                    No parameter combinations found.
                                </td>
                            </tr>
                        `;
                    } else {
                        combinations.forEach(combo => {
                            const paramVals = combo.parameter_values || {};
                            const isLinked = combo.is_linked || false;
                            const product = combo.product || null;
                            
                            let row = '<tr class="hover:bg-gray-50">';
                            
                            // 为每个参数添加一个单元格
                            Object.values(paramVals).forEach(value => {
                                row += `
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        ${value}
                                    </td>
                                `;
                            });
                            
                            // 状态单元格
                            row += `
                                <td class="px-4 py-3">
                                    ${isLinked && product ? `
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Linked to ${product.name}
                                        </span>
                                    ` : `
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Not linked
                                        </span>
                                    `}
                                </td>
                            `;
                            
                            // 操作单元格
                            row += `
                                <td class="px-4 py-3 text-sm text-right">
                                    <div class="flex justify-end space-x-2">
                                        <a href="${isLinked && product ? `/products/${product.id}/edit` : '#'}" class="${isLinked && product ? 'text-indigo-600 hover:text-indigo-900' : 'text-gray-400 cursor-not-allowed'}" ${!isLinked || !product ? 'aria-disabled="true"' : ''}>
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                        <a href="/products?link_template=${templateId}&parameter_combo=${encodeURIComponent(combo.parameter_group_string)}" class="text-green-600 hover:text-green-900">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                        </a>
                                        ${isLinked && product ? `
                                            <button type="button" class="unlink-parameter-combo-btn text-red-600 hover:text-red-900" 
                                                data-template-id="${templateId}" 
                                                data-parameter-group="${combo.parameter_group_string}">
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        ` : ''}
                                        </div>
                                </td>
                            `;
                            
                            row += '</tr>';
                            tableContent += row;
                        });
                    }
                    
                    // 更新表格内容
                    combinationsTable.innerHTML = tableContent;
                    
                    // 重新设置解除链接按钮
                    setupUnlinkButtons();
                    
                    // 显示成功通知
                    const toast = Swal.mixin({
                        toast: true,
                        position: 'bottom-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    
                    toast.fire({
                        icon: 'success',
                        title: `Loaded ${combinations.length} parameter combinations`
                    });
                })
                .catch(error => {
                    console.error('Failed to load parameter combinations:', error);
                    
                    // 显示错误内容
                    combinationsTable.innerHTML = `
                        <tr>
                            <td colspan="100%" class="px-4 py-3 text-center text-sm text-red-500">
                                <div class="py-4">
                                    <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-red-800">Error Loading Combinations</h3>
                                    <p class="mt-1 text-sm text-red-500">${error.message || 'Unknown error'}</p>
                                    <div class="mt-3">
                                        <button type="button" onclick="window.location.reload()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none">
                                            Retry
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                    
                    // 显示错误通知
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to load parameter combinations',
                        text: error.message || 'Unknown error occurred'
                    });
                });
            }
            
            // 设置解除链接按钮
            function setupUnlinkButtons() {
                // 设置参数组合的解除链接按钮
                const unlinkComboBtns = document.querySelectorAll('.unlink-parameter-combo-btn');
                unlinkComboBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const templateId = this.getAttribute('data-template-id');
                        const parameterGroup = this.getAttribute('data-parameter-group');
                        
                        // 显示确认对话框
                        Swal.fire({
                            title: 'Unlink Confirmation',
                            text: 'Are you sure you want to unlink this product from this parameter combination?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, unlink it',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#ef4444',
                            cancelButtonColor: '#6b7280',
                            customClass: {
                                confirmButton: 'text-white',
                                cancelButton: 'text-white'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                unlinkParameterCombo(templateId, parameterGroup, this.closest('tr'));
                            }
                        });
                    });
                });
            }
            
            // 解除参数组合链接
            function unlinkParameterCombo(templateId, parameterGroup, row) {
                if (!templateId || !parameterGroup) return;
                
                // 如果提供了行元素，添加加载状态
                if (row) {
                    row.classList.add('opacity-50');
                }
                
                // 显示加载通知
                const loadingToast = Swal.mixin({
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: false,
                    didOpen: (toast) => {
                        Swal.showLoading();
                    }
                });
                
                loadingToast.fire({
                    title: 'Unlinking...'
                });
                
                // 发送解除链接请求
                fetch('{{ route("product-templates.unlink-parameter-combo") }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        template_id: templateId,
                        parameter_group: parameterGroup
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Update UI display
                    if (data.status === 'success') {
                        // Reload combination list
                        loadParameterCombinations();
                        
                        // Show success message
                        Swal.fire({
                            toast: true,
                            position: 'bottom-end',
                            icon: 'success',
                            title: data.message || 'Parameter combination unlinked successfully',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } else {
                        if (row) row.classList.remove('opacity-50');
                        
                        // Show error message
                        Swal.fire({
                            toast: true,
                            position: 'bottom-end',
                            icon: 'error',
                            title: data.message || 'Failed to unlink. Please try again later.',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (row) row.classList.remove('opacity-50');
                    
                    // Show error message
                    Swal.fire({
                        toast: true,
                        position: 'bottom-end',
                        icon: 'error',
                        title: 'Failed to unlink. Please try again later.',
                        showConfirmButton: false,
                        timer: 3000
                    });
                });
            }
            
            // 多重事件监听，确保在各种情况下都能正确初始化
            const initEvents = ['DOMContentLoaded', 'turbolinks:load', 'turbo:load', 'page:load', 'livewire:load'];
            initEvents.forEach(eventName => {
                document.removeEventListener(eventName, initializeTemplatePage);
                document.addEventListener(eventName, initializeTemplatePage);
            });
            
            // 如果文档已加载，立即初始化
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                initializeTemplatePage();
            }
        })();
    </script>

    <style>
        /* SweetAlert2 custom styles */
        .swal2-confirm {
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
            color: white !important;
        }
        
        .swal2-cancel {
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
            color: white !important;
        }
    </style>
    @endpush
</x-app-layout> 