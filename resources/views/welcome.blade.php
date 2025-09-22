<!DOCTYPE html>
@php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

// 辅助函数：确保图片URL包含正确的前缀
function ensureValidImageUrl($url) {
    if (empty($url)) {
        return "";
    }
    
    // 如果已经是完整URL (以http://或https://开头)，直接返回
    if (preg_match('/^https?:\/\//', $url)) {
        return $url;
    }
    
    // 处理app_models_settings路径，确保有storage/前缀
    if (strpos($url, 'app_models_settings') !== false) {
        // 如果不是以storage/开头
        if (strpos($url, 'storage/') !== 0) {
            $url = 'storage/' . ltrim($url, '/');
        }
        
        return asset($url);
    }
    
    // 确保路径以storage/开头
    if (strpos($url, 'storage/') !== 0) {
        $url = 'storage/' . ltrim($url, '/');
    }
    
    // 返回完整URL
    return asset($url);
}

$sections = \App\Models\HomepageSection::where('is_active', true)
    ->orderBy('position')
    ->get();
    
// Log sections for debugging
\Illuminate\Support\Facades\Log::debug('Homepage sections loaded:', [
    'count' => $sections->count(),
    'sections' => $sections->map(fn($s) => [
        'id' => $s->id,
        'type' => $s->type,
        'title' => $s->title,
        'content_keys' => is_array($s->content) ? array_keys($s->content) : null
    ])
]);
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Swiper CSS for carousel -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />

        <!-- Styles -->
        <style>
            /* ! tailwindcss v3.2.4 | MIT License | https://tailwindcss.com */*,::after,::before{box-sizing:border-box;border-width:0;border-style:solid;border-color:#e5e7eb}::after,::before{--tw-content:''}html{line-height:1.5;-webkit-text-size-adjust:100%;-moz-tab-size:4;tab-size:4;font-family:Figtree, sans-serif;font-feature-settings:normal}body{margin:0;line-height:inherit}hr{height:0;color:inherit;border-top-width:1px}abbr:where([title]){-webkit-text-decoration:underline dotted;text-decoration:underline dotted}h1,h2,h3,h4,h5,h6{font-size:inherit;font-weight:inherit}a{color:inherit;text-decoration:inherit}b,strong{font-weight:bolder}code,kbd,pre,samp{font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;font-size:1em}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sub{bottom:-.25em}sup{top:-.5em}table{text-indent:0;border-color:inherit;border-collapse:collapse}button,input,optgroup,select,textarea{font-family:inherit;font-size:100%;font-weight:inherit;line-height:inherit;color:inherit;margin:0;padding:0}button,select{text-transform:none}[type=button],[type=reset],[type=submit],button{-webkit-appearance:button;background-color:transparent;background-image:none}:-moz-focusring{outline:auto}:-moz-ui-invalid{box-shadow:none}progress{vertical-align:baseline}::-webkit-inner-spin-button,::-webkit-outer-spin-button{height:auto}[type=search]{-webkit-appearance:textfield;outline-offset:-2px}::-webkit-search-decoration{-webkit-appearance:none}::-webkit-file-upload-button{-webkit-appearance:button;font:inherit}summary{display:list-item}blockquote,dd,dl,figure,h1,h2,h3,h4,h5,h6,hr,p,pre{margin:0}fieldset{margin:0;padding:0}legend{padding:0}menu,ol,ul{list-style:none;margin:0;padding:0}textarea{resize:vertical}input::placeholder,textarea::placeholder{opacity:1;color:#9ca3af}[role=button],button{cursor:pointer}:disabled{cursor:default}audio,canvas,embed,iframe,img,object,svg,video{display:block;vertical-align:middle}img,video{max-width:100%;height:auto}[hidden]{display:none}*, ::before, ::after{--tw-border-spacing-x:0;--tw-border-spacing-y:0;--tw-translate-x:0;--tw-translate-y:0;--tw-rotate:0;--tw-skew-x:0;--tw-skew-y:0;--tw-scale-x:1;--tw-scale-y:1;--tw-pan-x: ;--tw-pan-y: ;--tw-pinch-zoom: ;--tw-scroll-snap-strictness:proximity;--tw-ordinal: ;--tw-slashed-zero: ;--tw-numeric-figure: ;--tw-numeric-spacing: ;--tw-numeric-fraction: ;--tw-ring-inset: ;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-color:rgb(59 130 246 / 0.5);--tw-ring-offset-shadow:0 0 #0000;--tw-ring-shadow:0 0 #0000;--tw-shadow:0 0 #0000;--tw-shadow-colored:0 0 #0000;--tw-blur: ;--tw-brightness: ;--tw-contrast: ;--tw-grayscale: ;--tw-hue-rotate: ;--tw-invert: ;--tw-saturate: ;--tw-sepia: ;--tw-drop-shadow: ;--tw-backdrop-blur: ;--tw-backdrop-brightness: ;--tw-backdrop-contrast: ;--tw-backdrop-grayscale: ;--tw-backdrop-hue-rotate: ;--tw-backdrop-invert: ;--tw-backdrop-opacity: ;--tw-backdrop-saturate: ;--tw-backdrop-sepia: }::-webkit-backdrop{--tw-border-spacing-x:0;--tw-border-spacing-y:0;--tw-translate-x:0;--tw-translate-y:0;--tw-rotate:0;--tw-skew-x:0;--tw-skew-y:0;--tw-scale-x:1;--tw-scale-y:1;--tw-pan-x: ;--tw-pan-y: ;--tw-pinch-zoom: ;--tw-scroll-snap-strictness:proximity;--tw-ordinal: ;--tw-slashed-zero: ;--tw-numeric-figure: ;--tw-numeric-spacing: ;--tw-numeric-fraction: ;--tw-ring-inset: ;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-color:rgb(59 130 246 / 0.5);--tw-ring-offset-shadow:0 0 #0000;--tw-ring-shadow:0 0 #0000;--tw-shadow:0 0 #0000;--tw-shadow-colored:0 0 #0000;--tw-blur: ;--tw-brightness: ;--tw-contrast: ;--tw-grayscale: ;--tw-hue-rotate: ;--tw-invert: ;--tw-saturate: ;--tw-sepia: ;--tw-drop-shadow: ;--tw-backdrop-blur: ;--tw-backdrop-brightness: ;--tw-backdrop-contrast: ;--tw-backdrop-grayscale: ;--tw-backdrop-hue-rotate: ;--tw-backdrop-invert: ;--tw-backdrop-opacity: ;--tw-backdrop-saturate: ;--tw-backdrop-sepia: }::backdrop{--tw-border-spacing-x:0;--tw-border-spacing-y:0;--tw-translate-x:0;--tw-translate-y:0;--tw-rotate:0;--tw-skew-x:0;--tw-skew-y:0;--tw-scale-x:1;--tw-scale-y:1;--tw-pan-x: ;--tw-pan-y: ;--tw-pinch-zoom: ;--tw-scroll-snap-strictness:proximity;--tw-ordinal: ;--tw-slashed-zero: ;--tw-numeric-figure: ;--tw-numeric-spacing: ;--tw-numeric-fraction: ;--tw-ring-inset: ;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-color:rgb(59 130 246 / 0.5);--tw-ring-offset-shadow:0 0 #0000;--tw-ring-shadow:0 0 #0000;--tw-shadow:0 0 #0000;--tw-shadow-colored:0 0 #0000;--tw-blur: ;--tw-brightness: ;--tw-contrast: ;--tw-grayscale: ;--tw-hue-rotate: ;--tw-invert: ;--tw-saturate: ;--tw-sepia: ;--tw-drop-shadow: ;--tw-backdrop-blur: ;--tw-backdrop-brightness: ;--tw-backdrop-contrast: ;--tw-backdrop-grayscale: ;--tw-backdrop-hue-rotate: ;--tw-backdrop-invert: ;--tw-backdrop-opacity: ;--tw-backdrop-saturate: ;--tw-backdrop-sepia: }.relative{position:relative}.mx-auto{margin-left:auto;margin-right:auto}.mx-6{margin-left:1.5rem;margin-right:1.5rem}.ml-4{margin-left:1rem}.mt-16{margin-top:4rem}.mt-6{margin-top:1.5rem}.mt-4{margin-top:1rem}.-mt-px{margin-top:-1px}.mr-1{margin-right:0.25rem}.flex{display:flex}.inline-flex{display:inline-flex}.grid{display:grid}.h-16{height:4rem}.h-7{height:1.75rem}.h-6{height:1.5rem}.h-5{height:1.25rem}.min-h-screen{min-height:100vh}.w-auto{width:auto}.w-16{width:4rem}.w-7{width:1.75rem}.w-6{width:1.5rem}.w-5{width:1.25rem}.max-w-7xl{max-width:80rem}.shrink-0{flex-shrink:0}.scale-100{--tw-scale-x:1;--tw-scale-y:1;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.grid-cols-1{grid-template-columns:repeat(1, minmax(0, 1fr))}.items-center{align-items:center}.justify-center{justify-content:center}.gap-6{gap:1.5rem}.gap-4{gap:1rem}.self-center{align-self:center}.rounded-lg{border-radius:0.5rem}.rounded-full{border-radius:9999px}.bg-gray-100{--tw-bg-opacity:1;background-color:rgb(243 244 246 / var(--tw-bg-opacity))}.bg-white{--tw-bg-opacity:1;background-color:rgb(255 255 255 / var(--tw-bg-opacity))}.bg-red-50{--tw-bg-opacity:1;background-color:rgb(254 242 242 / var(--tw-bg-opacity))}.bg-dots-darker{background-image:url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E")}.from-gray-700\/50{--tw-gradient-from:rgb(55 65 81 / 0.5);--tw-gradient-to:rgb(55 65 81 / 0);--tw-gradient-stops:var(--tw-gradient-from), var(--tw-gradient-to)}.via-transparent{--tw-gradient-to:rgb(0 0 0 / 0);--tw-gradient-stops:var(--tw-gradient-from), transparent, var(--tw-gradient-to)}.bg-center{background-position:center}.stroke-red-500{stroke:#ef4444}.stroke-gray-400{stroke:#9ca3af}.p-6{padding:1.5rem}.px-6{padding-left:1.5rem;padding-right:1.5rem}.text-center{text-align:center}.text-right{text-align:right}.text-xl{font-size:1.25rem;line-height:1.75rem}.text-sm{font-size:0.875rem;line-height:1.25rem}.font-semibold{font-weight:600}.leading-relaxed{line-height:1.625}.text-gray-600{--tw-text-opacity:1;color:rgb(75 85 99 / var(--tw-text-opacity))}.text-gray-900{--tw-text-opacity:1;color:rgb(17 24 39 / var(--tw-text-opacity))}.text-gray-500{--tw-text-opacity:1;color:rgb(107 114 128 / var(--tw-text-opacity))}.underline{-webkit-text-decoration-line:underline;text-decoration-line:underline}.antialiased{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.shadow-2xl{--tw-shadow:0 25px 50px -12px rgb(0 0 0 / 0.25);--tw-shadow-colored:0 25px 50px -12px var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)}.shadow-gray-500\/20{--tw-shadow-color:rgb(107 114 128 / 0.2);--tw-shadow:var(--tw-shadow-colored)}.transition-all{transition-property:all;transition-timing-function:cubic-bezier(0.4, 0, 0.2, 1);transition-duration:150ms}.selection\:bg-red-500 *::selection{--tw-bg-opacity:1;background-color:rgb(239 68 68 / var(--tw-bg-opacity))}.selection\:text-white *::selection{--tw-text-opacity:1;color:rgb(255 255 255 / var(--tw-text-opacity))}.selection\:bg-red-500::selection{--tw-bg-opacity:1;background-color:rgb(239 68 68 / var(--tw-bg-opacity))}.selection\:text-white::selection{--tw-text-opacity:1;color:rgb(255 255 255 / var(--tw-text-opacity))}.hover\:text-gray-900:hover{--tw-text-opacity:1;color:rgb(17 24 39 / var(--tw-text-opacity))}.hover\:text-gray-700:hover{--tw-text-opacity:1;color:rgb(55 65 81 / var(--tw-text-opacity))}.focus\:rounded-sm:focus{border-radius:0.125rem}.focus\:outline:focus{outline-style:solid}.focus\:outline-2:focus{outline-width:2px}.focus\:outline-red-500:focus{outline-color:#ef4444}.group:hover .group-hover\:stroke-gray-600{stroke:#4b5563}.z-10{z-index: 10}@media (prefers-reduced-motion: no-preference){.motion-safe\:hover\:scale-\[1\.01\]:hover{--tw-scale-x:1.01;--tw-scale-y:1.01;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}}@media (prefers-color-scheme: dark){.dark\:bg-gray-900{--tw-bg-opacity:1;background-color:rgb(17 24 39 / var(--tw-bg-opacity))}.dark\:bg-gray-800\/50{background-color:rgb(31 41 55 / 0.5)}.dark\:bg-red-800\/20{background-color:rgb(153 27 27 / 0.2)}.dark\:bg-dots-lighter{background-image:url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E")}.dark\:bg-gradient-to-bl{background-image:linear-gradient(to bottom left, var(--tw-gradient-stops))}.dark\:stroke-gray-600{stroke:#4b5563}.dark\:text-gray-400{--tw-text-opacity:1;color:rgb(156 163 175 / var(--tw-text-opacity))}.dark\:text-white{--tw-text-opacity:1;color:rgb(255 255 255 / var(--tw-text-opacity))}.dark\:shadow-none{--tw-shadow:0 0 #0000;--tw-shadow-colored:0 0 #0000;box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)}.dark\:ring-1{--tw-ring-offset-shadow:var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);--tw-ring-shadow:var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);box-shadow:var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000)}.dark\:ring-inset{--tw-ring-inset:inset}.dark\:ring-white\/5{--tw-ring-color:rgb(255 255 255 / 0.05)}.dark\:hover\:text-white:hover{--tw-text-opacity:1;color:rgb(255 255 255 / var(--tw-text-opacity))}.group:hover .dark\:group-hover\:stroke-gray-400{stroke:#9ca3af}}@media (min-width: 640px){.sm\:fixed{position:fixed}.sm\:top-0{top:0px}.sm\:right-0{right:0px}.sm\:ml-0{margin-left:0px}.sm\:flex{display:flex}.sm\:items-center{align-items:center}.sm\:justify-center{justify-content:center}.sm\:justify-between{justify-content:space-between}.sm\:text-left{text-align:left}.sm\:text-right{text-align:right}}@media (min-width: 768px){.md\:grid-cols-2{grid-template-columns:repeat(2, minmax(0, 1fr))}}@media (min-width: 1024px){.lg\:gap-8{gap:2rem}.lg\:p-8{padding:2rem}}
        </style>
    </head>
    <body class="antialiased">
        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
            @if (Route::has('login'))
                <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                    @auth
                        <a href="{{ url('/') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Home</a>
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="max-w-7xl mx-auto p-6 lg:p-8">
                @php
                    $settings = DB::table('settings')
                        ->where('group', 'homepage')
                        ->get()
                        ->pluck('value', 'key')
                        ->toArray();
                @endphp

                <!-- Dynamic Sections from Homepage Sections -->
                @foreach($sections as $section)
                    @if($section->type == 'banner')
                <!-- Banner Section -->
                        <div class="w-full mb-12 relative overflow-hidden rounded-lg shadow-lg" 
                            style="background-color: {{ $section->background_color ?? 'transparent' }}; 
                                   color: {{ $section->text_color ?? 'inherit' }}; 
                                   padding: {{ $section->padding ?? '0' }};" 
                            class="{{ $section->custom_class ?? '' }}">
                            
                            @php
                                $images = $section->content['images'] ?? [];
                                $settings = $section->content['settings'] ?? [];
                                $showAsCarousel = isset($settings['show_as_carousel']) && $settings['show_as_carousel'] && count($images) > 1;
                                
                                // Log banner content for debugging
                                \Illuminate\Support\Facades\Log::debug('Banner content details:', [
                                    'section_id' => $section->id,
                                    'images_count' => count($images),
                                    'show_as_carousel' => $showAsCarousel,
                                    'first_image' => $images[0] ?? null,
                                    'settings' => $settings,
                                    'raw_content' => $section->content
                                ]);
                            @endphp
                            
                            @if($showAsCarousel)
                                <!-- Carousel Display -->
                                <div class="banner-carousel swiper-container" data-autoplay="{{ $settings['autoplay'] ?? 'true' }}" data-delay="{{ $settings['delay'] ?? 5000 }}">
                                    <div class="swiper-wrapper">
                                        @foreach($images as $image)
                                            <div class="swiper-slide">
                                                <img src="{{ ensureValidImageUrl($image['image']) }}" alt="{{ $image['title'] ?? 'Banner' }}" class="w-full h-auto object-cover">
                    <div class="absolute inset-0 bg-gradient-to-r from-black/50 to-transparent flex items-center">
                        <div class="px-8 py-6 text-white max-w-lg">
                                                        <h1 class="text-3xl md:text-4xl font-bold mb-2">{{ $image['title'] ?? $section->title }}</h1>
                                                        <p class="text-lg mb-4">{{ $image['subtitle'] ?? $section->subtitle }}</p>
                                                        <a href="{{ $image['button_link'] ?? '/products' }}" class="inline-block px-6 py-2 bg-white text-gray-900 font-medium rounded-md hover:bg-gray-100 transition-colors">
                                                            {{ $image['button_text'] ?? 'Shop Now' }}
                            </a>
                        </div>
                    </div>
                </div>
                                        @endforeach
                                    </div>
                                    
                                    @if($settings['show_navigation'] ?? true)
                                        <div class="swiper-button-next"></div>
                                        <div class="swiper-button-prev"></div>
                @endif
                
                                    @if($settings['show_indicators'] ?? true)
                                        <div class="swiper-pagination"></div>
                                    @endif
                                </div>
                            @elseif(!empty($images))
                                <!-- Single Banner Display -->
                                <img src="{{ ensureValidImageUrl($images[0]['image']) }}" alt="{{ $images[0]['title'] ?? 'Banner' }}" class="w-full h-auto object-cover">
                                <div class="absolute inset-0 bg-gradient-to-r from-black/50 to-transparent flex items-center">
                                    <div class="px-8 py-6 text-white max-w-lg">
                                        <h1 class="text-3xl md:text-4xl font-bold mb-2">{{ $images[0]['title'] ?? $section->title }}</h1>
                                        <p class="text-lg mb-4">{{ $images[0]['subtitle'] ?? $section->subtitle }}</p>
                                        <a href="{{ $images[0]['button_link'] ?? '/products' }}" class="inline-block px-6 py-2 bg-white text-gray-900 font-medium rounded-md hover:bg-gray-100 transition-colors">
                                            {{ $images[0]['button_text'] ?? 'Shop Now' }}
                                        </a>
                                    </div>
                                </div>
                            @else
                                <!-- 没有图片时的备用显示 -->
                                <div class="py-16 text-center bg-gray-100">
                                    <h2 class="text-xl text-gray-500">{{ $section->title }}</h2>
                                    <p class="text-gray-400">{{ $section->subtitle ?? 'Banner content not available' }}</p>
                                </div>
                            @endif
                </div>
                    @elseif($section->type == 'featured_products')
                        <!-- Featured Products Section -->
                        <div class="py-10 mb-12"
                             style="background-color: {{ $section->background_color ?? 'transparent' }}; 
                                    color: {{ $section->text_color ?? 'inherit' }}; 
                                    padding: {{ $section->padding ?? '0' }};" 
                             class="{{ $section->custom_class ?? '' }}">
                            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                                <div class="text-center mb-8">
                                    <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">{{ $section->title }}</h2>
                                    @if($section->subtitle)
                                        <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500">{{ $section->subtitle }}</p>
                                    @endif
                                </div>

                                <div class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-4 xl:gap-x-8">
                                    @php
                                        $productIds = $section->content['product_ids'] ?? [];
                                        $products = \App\Models\Product::whereIn('id', $productIds)->get();
                                        
                                        // Log featured products for debugging
                                        \Illuminate\Support\Facades\Log::debug('Featured products:', [
                                            'section_id' => $section->id,
                                            'product_ids' => $productIds,
                                            'products_found' => $products->count(),
                                            'content' => $section->content
                                        ]);
                                    @endphp
                                    
                                    @foreach($products as $product)
                                        <div class="group relative">
                                            <div class="w-full min-h-80 bg-gray-200 aspect-w-1 aspect-h-1 rounded-md overflow-hidden group-hover:opacity-75 lg:h-80 lg:aspect-none">
                                                <img src="{{ $product->featured_image }}" alt="{{ $product->name }}" class="w-full h-full object-center object-cover lg:w-full lg:h-full">
                            </div>
                                            <div class="mt-4 flex justify-between">
                                                <div>
                                                    <h3 class="text-sm text-gray-700">
                                                        <a href="/products/{{ $product->id }}">
                                                            <span aria-hidden="true" class="absolute inset-0"></span>
                                                            {{ $product->name }}
                                                        </a>
                                                    </h3>
                                                    <p class="mt-1 text-sm text-gray-500">{{ $product->category->name ?? 'Category' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @elseif($section->type == 'sale_products')
                        <!-- Sale Products Section -->
                        <div class="py-10 mb-12 bg-red-50"
                             style="background-color: {{ $section->background_color ?? '#fef2f2' }}; 
                                    color: {{ $section->text_color ?? 'inherit' }}; 
                                    padding: {{ $section->padding ?? '0' }};" 
                             class="{{ $section->custom_class ?? '' }}">
                            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                                <div class="text-center mb-8">
                                    <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">{{ $section->title }}</h2>
                                    @if($section->subtitle)
                                        <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500">{{ $section->subtitle }}</p>
                                    @endif
                                </div>

                                <div class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-4 xl:gap-x-8">
                                    @php
                                        $productIds = $section->content['product_ids'] ?? [];
                                        $products = \App\Models\Product::whereIn('id', $productIds)->get();
                                        
                                        // Log sale products for debugging
                                        \Illuminate\Support\Facades\Log::debug('Sale products:', [
                                            'section_id' => $section->id,
                                            'product_ids' => $productIds,
                                            'products_found' => $products->count(),
                                            'content' => $section->content
                                        ]);

                                        // 遍历产品并设置折扣信息
                                        foreach ($products as $product) {
                                            // 检查产品是否有折扣字段
                                            $product->has_discount = false;
                                            
                                            // 通过 discount_percentage 检查折扣
                                            if (isset($product->discount_percentage) && $product->discount_percentage > 0) {
                                                $product->has_discount = true;
                                                $product->discount_type = 'percentage';
                                                $product->discount_value = $product->discount_percentage;
                                            }
                                            
                                            // 确保价格字段存在
                                            if (!isset($product->price) && isset($product->selling_price)) {
                                                $product->price = $product->selling_price;
                                            }
                                            
                                            // 记录产品折扣信息
                                            \Illuminate\Support\Facades\Log::debug('Product discount info:', [
                                                'product_id' => $product->id,
                                                'product_name' => $product->name,
                                                'price' => $product->price ?? $product->selling_price,
                                                'has_discount' => $product->has_discount,
                                                'discount_type' => $product->discount_type ?? null,
                                                'discount_value' => $product->discount_value ?? null
                                            ]);
                                        }
                                    @endphp
                                    
                                    @foreach($products as $product)
                                        <div class="group relative">
                                            <div class="w-full min-h-80 bg-gray-200 aspect-w-1 aspect-h-1 rounded-md overflow-hidden group-hover:opacity-75 lg:h-80 lg:aspect-none">
                                                @if($product->has_discount)
                                                    <div class="absolute top-0 right-0 bg-red-500 text-white px-2 py-1 text-xs font-bold">
                                                        @if($product->discount_type == 'percentage')
                                                            {{ $product->discount_value }}% OFF
                                                        @else
                                                            ${{ $product->discount_value }} OFF
                                                        @endif
                                                    </div>
                                                @endif
                                                <img src="{{ $product->featured_image }}" alt="{{ $product->name }}" class="w-full h-full object-center object-cover lg:w-full lg:h-full">
                            </div>
                                            <div class="mt-4 flex justify-between">
                                                <div>
                                                    <h3 class="text-sm text-gray-700">
                                                        <a href="/products/{{ $product->id }}">
                                                            <span aria-hidden="true" class="absolute inset-0"></span>
                                                            {{ $product->name }}
                                                        </a>
                                                    </h3>
                                                    <p class="mt-1 text-sm text-gray-500">{{ $product->category->name ?? 'Category' }}</p>
                                                </div>
                            <div>
                                                    @if($product->has_discount)
                                                        <p class="text-sm font-medium text-gray-900">
                                                            <span class="line-through text-gray-500">${{ number_format($product->price, 2) }}</span>
                                                            @if($product->discount_type == 'percentage')
                                                                ${{ number_format($product->price * (1 - $product->discount_value / 100), 2) }}
                                                            @else
                                                                ${{ number_format($product->price - $product->discount_value, 2) }}
                                                            @endif
                                                        </p>
                                                    @else
                                                        <p class="text-sm font-medium text-gray-900">${{ number_format($product->price ?? $product->selling_price, 2) }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach

                <div class="flex justify-center mt-16 px-0 sm:items-center sm:justify-between">
                    <div class="text-center text-sm sm:text-left">
                        &nbsp;
                    </div>

                    <div class="text-center text-sm text-gray-500 dark:text-gray-400 sm:text-right sm:ml-0">
                        Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                    </div>
                </div>
            </div>
        </div>

        <!-- Swiper JS -->
        <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
        
        <!-- Banner Carousel JS -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // 初始化所有轮播
                const carousels = document.querySelectorAll('.banner-carousel');
                
                carousels.forEach(carousel => {
                    const autoplay = carousel.dataset.autoplay === 'true';
                    const delay = parseInt(carousel.dataset.delay) || 5000;
                    
                    new Swiper(carousel, {
                        // 基础配置
                        loop: true,
                        slidesPerView: 1,
                        
                        // 自动播放
                        autoplay: autoplay ? {
                            delay: delay,
                            disableOnInteraction: false,
                        } : false,
                        
                        // 导航箭头
                        navigation: {
                            nextEl: '.swiper-button-next',
                            prevEl: '.swiper-button-prev',
                        },
                        
                        // 分页指示器
                        pagination: {
                            el: '.swiper-pagination',
                            clickable: true,
                        },
                        
                        // 过渡效果
                        effect: 'slide',
                        
                        // 懒加载图片
                        lazy: true,
                        
                        // 可访问性
                        a11y: true,
                    });
                    
                    console.log('Carousel initialized:', {
                        autoplay: autoplay,
                        delay: delay
                    });
                });
                
                // Debug log
                console.log('Found ' + carousels.length + ' carousels on the page');
            });
        </script>
    </body>
</html>
