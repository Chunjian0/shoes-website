@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Facades\DB;
    // Placeholder for data that would come from the controller
    $stockThreshold = $stockThreshold ?? 5; // Example default
    $featuredProducts = $featuredProducts ?? collect();
    $newProducts = $newProducts ?? collect();
    $saleProducts = $saleProducts ?? collect();
    // Banner data and settings would also be passed here
@endphp

<div class="container mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">
                Homepage Management
            </h1>
        {{-- Potential global actions button group --}}
        </div>

    {{-- Tabbed Interface Container --}}
    <div class="w-full">
        {{-- Tab Navigation --}}
        <div class="mb-4 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500" id="homepage-tabs" role="tablist">
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg transition-colors duration-150 ease-in-out" {{-- Default state needs to be set by JS --}}
                            id="global-settings-tab" type="button" role="tab" aria-controls="global-settings" aria-selected="false"> {{-- JS will set true for default --}}
                        Global Settings
                </button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg transition-colors duration-150 ease-in-out text-gray-500 border-transparent hover:text-gray-600 hover:border-gray-300"
                            id="featured-products-tab" type="button" role="tab" aria-controls="featured-products" aria-selected="false">
                    Featured Products
                </button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg transition-colors duration-150 ease-in-out text-gray-500 border-transparent hover:text-gray-600 hover:border-gray-300"
                             id="new-arrivals-tab" type="button" role="tab" aria-controls="new-arrivals" aria-selected="false">
                    New Arrivals
                </button>
                 </li>
                 <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg transition-colors duration-150 ease-in-out text-gray-500 border-transparent hover:text-gray-600 hover:border-gray-300"
                             id="sale-products-tab" type="button" role="tab" aria-controls="sale-products" aria-selected="false">
                    Sale Products
                </button>
                 </li>
                 <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg transition-colors duration-150 ease-in-out text-gray-500 border-transparent hover:text-gray-600 hover:border-gray-300"
                             id="banners-carousel-tab" type="button" role="tab" aria-controls="banners-carousel" aria-selected="false">
                         Banners & Carousel
                </button>
                 </li>
            </ul>
        </div>

        {{-- Tab Content Panes --}}
        <div id="homepage-tab-content">
            {{-- Global Settings Pane --}}
            <div id="global-settings" role="tabpanel" aria-labelledby="global-settings-tab" class="py-6">
                {{-- ================================================== --}}
                {{-- Stock Threshold Section (Now in Tab)            --}}
                {{-- ================================================== --}}
                <section id="stock-threshold" aria-labelledby="stock-threshold-heading">
                    {{-- Using a slightly less prominent card style for global settings --}}
                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-6">
                        <h2 id="stock-threshold-heading" class="text-lg font-semibold text-gray-800 mb-3">
                            Global Settings
                        </h2>
                        <div class="bg-white rounded-md border border-gray-200 p-4">
                            <h3 class="text-base font-medium text-gray-700 mb-1">Stock Threshold</h3>
                            <p class="text-sm text-gray-500 mb-4">
                                Products with stock below this threshold are automatically removed from homepage sections.
                            </p>
                            <form id="stock-threshold-form">
                                @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                                    <div class="sm:col-span-1">
                                        <label for="min_stock_threshold" class="sr-only">Minimum Stock Threshold</label>
                                        <input type="number" min="1" name="min_stock_threshold" id="min_stock_threshold" value="{{ $stockThreshold }}" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Enter threshold">
                                    </div>
                                    <div class="sm:col-span-2 flex flex-col sm:flex-row sm:items-center sm:justify-end sm:space-x-3">
                                        <button type="submit" id="save-threshold-btn" class="w-full sm:w-auto inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out mb-2 sm:mb-0 order-last sm:order-first">
                                            Update Threshold
                                        </button>
                                        <button type="button" id="run-filter-btn" class="w-full sm:w-auto inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                            Run Filter Now
                                        </button>
                                </div>
                                </div>
                            </form>
                            </div>
                            </div>
                </section>
                        </div>

            {{-- Featured Products Pane --}}
            <div id="featured-products" role="tabpanel" aria-labelledby="featured-products-tab" class="py-6 hidden">
                {{-- ================================================== --}}
                {{-- Featured Products Section (Now in Tab)          --}}
                {{-- ================================================== --}}
                <section id="featured-products-section" aria-labelledby="featured-products-heading" class="space-y-6">
                    {{-- Integrated Settings & List Management Card --}}
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <form id="featured-settings-form" class="space-y-4">
                                @csrf
                                {{-- Example Settings Integrated --}}
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Section Display Text</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="featured_title" class="block text-sm font-medium text-gray-700">Section Title</label>
                                    <input type="text" id="featured_title" name="settings[featured_title]" value="{{ $settings['featured_title'] ?? '' }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label for="featured_subtitle" class="block text-sm font-medium text-gray-700">Section Subtitle</label>
                                    <input type="text" id="featured_subtitle" name="settings[featured_subtitle]" value="{{ $settings['featured_subtitle'] ?? '' }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                {{-- Add Button Text and Link fields for consistency --}}
                                <div>
                                    <label for="featured_button_text" class="block text-sm font-medium text-gray-700">Button Text</label>
                                    <input type="text" id="featured_button_text" name="settings[featured_button_text]" value="{{ $settings['featured_button_text'] ?? '' }}" placeholder="e.g., Shop Featured" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label for="featured_button_link" class="block text-sm font-medium text-gray-700">Button Link</label>
                                    <input type="text" id="featured_button_link" name="settings[featured_button_link]" value="{{ $settings['featured_button_link'] ?? '' }}" placeholder="e.g., /collections/featured" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>
                                <div class="text-right">
                                    <button type="submit" class="inline-flex justify-center py-1.5 px-3 border border-transparent shadow-sm text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500">Save Section Settings</button>
                                    </div>
                            </form>
                                </div>

                        {{-- Product List Area --}}
                        <div class="p-6">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-2 sm:mb-0">Manage Featured Templates</h3>
                                <button type="button" data-type="featured" class="add-product-btn w-full sm:w-auto inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Add Templates
                            </button>
                </div>

                            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                            <th scope="col" class="w-16 px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Template</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parameters</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="w-28 px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="featured-products-sortable bg-white divide-y divide-gray-200">
                                        {{-- Use @forelse loop similar to previous version --}}
                            @forelse($featuredProducts as $product)
                                            {{-- Use table row structure from previous refactored version --}}
                                <tr id="featured-product-{{ $product->id }}" data-id="{{ $product->id }}" class="hover:bg-gray-50 transition-colors duration-150">
                                                {{-- Order Cell --}}
                                                <td class="w-16 px-4 py-3 whitespace-nowrap text-center cursor-move drag-handle">
                                                    <div class="flex items-center justify-center text-gray-400 hover:text-gray-600">
                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                        </div>
                                    </td>
                                                {{-- Template Cell --}}
                                                <td class="px-6 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                            {{-- Image Logic from previous refactor --}}
                                                            @if($product->images && is_array($product->images) && count($product->images) > 0 && isset($product->images[0]['url']))
                                                                <img class="h-10 w-10 rounded-md object-cover" src="{{ Storage::url($product->images[0]['url']) }}" alt="{{ $product->name }}">
                                                @else
                                                                <div class="h-10 w-10 rounded-md bg-gray-200 flex items-center justify-center">
                                                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                                            <div class="text-xs text-gray-500">{{ $product->category ? $product->category->name : 'No Category' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                                {{-- Parameters Cell --}}
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">
                                                    {{-- Parameter Logic from previous refactor --}}
                                        @if(is_array($product->parameters) && count($product->parameters) > 0)
                                                        <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ count($product->parameters) }} Parameters</span>
                                        @else
                                                        <span class="text-xs text-gray-400">None</span>
                                        @endif
                                    </td>
                                                {{-- Status Cell --}}
                                                <td class="px-6 py-3 whitespace-nowrap">
                                                    {{-- Status Logic from previous refactor --}}
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                                {{-- Actions Cell --}}
                                                <td class="w-28 px-6 py-3 whitespace-nowrap text-center text-sm font-medium">
                                                    {{-- Action Buttons from previous refactor --}}
                                                    <div class="flex items-center justify-center space-x-2">
                                                        <a href="{{ route('product-templates.show', $product->id) }}" target="_blank" class="p-1 text-gray-400 hover:text-blue-600 transition-colors duration-150 rounded-full hover:bg-blue-100" title="View Template"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></a><button type="button" class="remove-featured-product p-1 text-gray-400 hover:text-red-600 transition-colors duration-150 rounded-full hover:bg-red-100" data-id="{{ $product->id }}" title="Remove from Featured"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                            {{-- Empty state from previous refactor --}}
                                            <tr>
                                                <td colspan="5" class="px-6 py-10 whitespace-nowrap text-sm text-gray-500 text-center">
                                                    <div class="flex flex-col items-center">
                                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                        <p class="mt-1 font-medium">No featured product templates found.</p>
                                                        <p class="mt-2 text-xs text-gray-500">Click 'Add Templates' to feature them.</p>
                                                        {{-- Removed Add button from empty state, Add button is always visible above table --}}
                                                    </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                            <div class="mt-6 px-6 pb-4">
                                {{ $featuredProducts->links() }}
                </div>
            </div>
        </div>
                </section>
                        </div>

            {{-- New Arrivals Pane --}}
            <div id="new-arrivals" role="tabpanel" aria-labelledby="new-arrivals-tab" class="py-6 hidden">
                {{-- ================================================== --}}
                {{-- New Arrivals Section (Now in Tab)               --}}
                {{-- ================================================== --}}
                <section id="new-arrivals-section" aria-labelledby="new-arrivals-heading" class="space-y-6">
                     {{-- Integrated Settings & List Management Card --}}
                     <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                          <div class="p-6 border-b border-gray-200">
                              <form id="new-arrivals-settings-form" class="space-y-4">
                                @csrf
                                  {{-- New Arrivals Settings Fields --}}
                                  <h3 class="text-lg font-medium text-gray-900 mb-2">Section Display Text & Link</h3>
                                  <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                <div>
                            <label for="new_products_title" class="block text-sm font-medium text-gray-700">Section Title</label>
                                         <input type="text" id="new_products_title" name="settings[new_products_title]" value="{{ $settings['new_products_title'] ?? '' }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                            <label for="new_products_subtitle" class="block text-sm font-medium text-gray-700">Section Subtitle</label>
                                         <input type="text" id="new_products_subtitle" name="settings[new_products_subtitle]" value="{{ $settings['new_products_subtitle'] ?? '' }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label for="new_products_button_text" class="block text-sm font-medium text-gray-700">Button Text</label>
                                         <input type="text" id="new_products_button_text" name="settings[new_products_button_text]" value="{{ $settings['new_products_button_text'] ?? '' }}" placeholder="e.g., Shop New" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label for="new_products_button_link" class="block text-sm font-medium text-gray-700">Button Link</label>
                                         <input type="text" id="new_products_button_link" name="settings[new_products_button_link]" value="{{ $settings['new_products_button_link'] ?? '' }}" placeholder="e.g., /collections/new" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                  </div>
                                  <div class="text-right">
                                      <button type="submit" class="inline-flex justify-center py-1.5 px-3 border border-transparent shadow-sm text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500">Save Section Settings</button>
                                </div>
                            </form>
                </div>

                         {{-- New Arrivals List Area --}}
                          <div class="p-6">
                 <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
                                 <h3 class="text-lg font-medium text-gray-900 mb-2 sm:mb-0">Manage New Arrival Templates</h3>
                                  <button type="button" data-type="new-arrival" class="add-product-btn w-full sm:w-auto inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                     <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Add Templates
                    </button>
                </div>

                              <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                                         <tr>
                                             <th scope="col" class="w-16 px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Template</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parameters</th>
                                             <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th> {{-- Keep Created for New --}}
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                             <th scope="col" class="w-28 px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="new-arrivals-sortable bg-white divide-y divide-gray-200">
                            @forelse($newProducts as $product)
                                             {{-- Use table row structure from previous refactored version --}}
                                <tr id="new-arrival-product-{{ $product->id }}" data-id="{{ $product->id }}" class="hover:bg-gray-50 transition-colors duration-150">
                                                  {{-- Order Cell --}}
                                                  <td class="w-16 px-4 py-3 whitespace-nowrap text-center cursor-move drag-handle"><div class="flex items-center justify-center text-gray-400 hover:text-gray-600"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg></div></td>
                                                  {{-- Template Cell --}}
                                                  <td class="px-6 py-3 whitespace-nowrap"><div class="flex items-center"><div class="flex-shrink-0 h-10 w-10">@if($product->images && is_array($product->images) && count($product->images) > 0 && isset($product->images[0]['url']))<img class="h-10 w-10 rounded-md object-cover" src="{{ Storage::url($product->images[0]['url']) }}" alt="{{ $product->name }}">@else<div class="h-10 w-10 rounded-md bg-gray-200 flex items-center justify-center"><svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></div>@endif</div><div class="ml-4"><div class="text-sm font-medium text-gray-900">{{ $product->name }}</div><div class="text-xs text-gray-500">{{ $product->category ? $product->category->name : 'No Category' }}</div></div></div></td>
                                                  {{-- Parameters Cell --}}
                                                  <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">@if(is_array($product->parameters) && count($product->parameters) > 0)<span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ count($product->parameters) }} Parameters</span>@else<span class="text-xs text-gray-400">None</span>@endif</td>
                                                  {{-- Created Cell --}}
                                                  <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $product->created_at ? $product->created_at->format('M d, Y') : 'N/A' }}</td>
                                                  {{-- Status Cell --}}
                                                  <td class="px-6 py-3 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span></td>
                                                  {{-- Actions Cell --}}
                                                  <td class="w-28 px-6 py-3 whitespace-nowrap text-center text-sm font-medium"><div class="flex items-center justify-center space-x-2"><a href="{{ route('product-templates.show', $product->id) }}" target="_blank" class="p-1 text-gray-400 hover:text-blue-600 transition-colors duration-150 rounded-full hover:bg-blue-100" title="View Template"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></a><button type="button" class="remove-new-product p-1 text-gray-400 hover:text-red-600 transition-colors duration-150 rounded-full hover:bg-red-100" data-id="{{ $product->id }}" title="Remove from New Arrivals"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button></div></td>
                                </tr>
                            @empty
                                             {{-- Empty state for New Arrivals --}}
                                              <tr><td colspan="6" class="px-6 py-10 whitespace-nowrap text-sm text-gray-500 text-center"><div class="flex flex-col items-center"><svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg><p class="mt-1 font-medium">No new arrival templates found.</p><p class="mt-2 text-xs text-gray-500">Click 'Add Templates' to list them here.</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                              <div class="mt-6 px-6 pb-4">
                                  {{ $newProducts->links() }}
                </div>
            </div>
        </div>
                 </section>
                        </div>

            {{-- Sale Products Pane --}}
            <div id="sale-products" role="tabpanel" aria-labelledby="sale-products-tab" class="py-6 hidden">
                 {{-- ================================================== --}}
                 {{-- Sale Products Section (Now in Tab)              --}}
                 {{-- ================================================== --}}
                 <section id="sale-products-section" aria-labelledby="sale-products-heading" class="space-y-6">
                     {{-- Integrated Settings & List Management Card --}}
                     <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                          <div class="p-6 border-b border-gray-200">
                            <form id="sale-products-settings-form" class="space-y-4">
                                @csrf
                                  {{-- Sale Products Settings Fields --}}
                                  <h3 class="text-lg font-medium text-gray-900 mb-2">Section Display Text & Link</h3>
                                  <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                <div>
                            <label for="sale_products_title" class="block text-sm font-medium text-gray-700">Section Title</label>
                                          <input type="text" id="sale_products_title" name="settings[sale_products_title]" value="{{ $settings['sale_products_title'] ?? '' }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                            <label for="sale_products_subtitle" class="block text-sm font-medium text-gray-700">Section Subtitle</label>
                                          <input type="text" id="sale_products_subtitle" name="settings[sale_products_subtitle]" value="{{ $settings['sale_products_subtitle'] ?? '' }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label for="sale_products_button_text" class="block text-sm font-medium text-gray-700">Button Text</label>
                                          <input type="text" id="sale_products_button_text" name="settings[sale_products_button_text]" value="{{ $settings['sale_products_button_text'] ?? '' }}" placeholder="e.g., Shop Sale" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label for="sale_products_button_link" class="block text-sm font-medium text-gray-700">Button Link</label>
                                          <input type="text" id="sale_products_button_link" name="settings[sale_products_button_link]" value="{{ $settings['sale_products_button_link'] ?? '' }}" placeholder="e.g., /collections/sale" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                  </div>
                                  <div class="text-right">
                                      <button type="submit" class="inline-flex justify-center py-1.5 px-3 border border-transparent shadow-sm text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500">Save Section Settings</button>
                                </div>
                            </form>
                </div>

                          {{-- Sale Products List Area --}}
                           <div class="p-6">
                 <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
                                   <h3 class="text-lg font-medium text-gray-900 mb-2 sm:mb-0">Manage Sale Templates</h3>
                                   <button type="button" data-type="sale" class="add-product-btn w-full sm:w-auto inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                       <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Add Templates
                    </button>
                </div>

                               <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                                           <tr>
                                               <th scope="col" class="w-16 px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Template</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parameters</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                               <th scope="col" class="w-28 px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="sale-products-sortable bg-white divide-y divide-gray-200">
                            @forelse($saleProducts as $product)
                                               {{-- Use table row structure from previous refactored version --}}
                                <tr id="sale-product-{{ $product->id }}" data-id="{{ $product->id }}" class="hover:bg-gray-50 transition-colors duration-150">
                                                    {{-- Order Cell --}}
                                                    <td class="w-16 px-4 py-3 whitespace-nowrap text-center cursor-move drag-handle"><div class="flex items-center justify-center text-gray-400 hover:text-gray-600"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg></div></td>
                                                    {{-- Template Cell --}}
                                                    <td class="px-6 py-3 whitespace-nowrap"><div class="flex items-center"><div class="flex-shrink-0 h-10 w-10">@if($product->images && is_array($product->images) && count($product->images) > 0 && isset($product->images[0]['url']))<img class="h-10 w-10 rounded-md object-cover" src="{{ Storage::url($product->images[0]['url']) }}" alt="{{ $product->name }}">@else<div class="h-10 w-10 rounded-md bg-gray-200 flex items-center justify-center"><svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></div>@endif</div><div class="ml-4"><div class="text-sm font-medium text-gray-900">{{ $product->name }}</div><div class="text-xs text-gray-500">{{ $product->category ? $product->category->name : 'No Category' }}</div></div></div></td>
                                                    {{-- Parameters Cell --}}
                                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">@if(is_array($product->parameters) && count($product->parameters) > 0)<span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ count($product->parameters) }} Parameters</span>@else<span class="text-xs text-gray-400">None</span>@endif</td>
                                                    {{-- Status Cell --}}
                                                    <td class="px-6 py-3 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span></td>
                                                    {{-- Actions Cell --}}
                                                    <td class="w-28 px-6 py-3 whitespace-nowrap text-center text-sm font-medium"><div class="flex items-center justify-center space-x-2"><a href="{{ route('product-templates.show', $product->id) }}" target="_blank" class="p-1 text-gray-400 hover:text-blue-600 transition-colors duration-150 rounded-full hover:bg-blue-100" title="View Template"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></a><button type="button" class="remove-sale-product p-1 text-gray-400 hover:text-red-600 transition-colors duration-150 rounded-full hover:bg-red-100" data-id="{{ $product->id }}" title="Remove from Sale Products"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button></div></td>
                                </tr>
                            @empty
                                               {{-- Empty state for Sale Products --}}
                                               <tr><td colspan="5" class="px-6 py-10 whitespace-nowrap text-sm text-gray-500 text-center"><div class="flex flex-col items-center"><svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg><p class="mt-1 font-medium">No sale product templates found.</p><p class="mt-2 text-xs text-gray-500">Click 'Add Templates' to list them here.</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                               <div class="mt-6 px-6 pb-4">
                                   {{ $saleProducts->links() }}
                </div>
            </div>
        </div>
                  </section>
                    </div>

            {{-- Banners & Carousel Pane --}}
            <div id="banners-carousel" role="tabpanel" aria-labelledby="banners-carousel-tab" class="py-6 hidden">
                 {{-- ================================================== --}}
                 {{-- Banners & Carousel Section (Now in Tab)         --}}
                 {{-- ================================================== --}}
                 <section id="banners-section" aria-labelledby="banners-heading" class="space-y-6">
                     {{-- Banner Management Card --}}
                     <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                         <div class="p-6 border-b border-gray-200">
                              <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                  <div class="mb-3 sm:mb-0">
                            <h3 class="text-lg font-medium text-gray-900">Manage Banners</h3>
                                     <p class="text-sm text-gray-500 mt-1">Add, remove, and reorder banners displayed in the carousel.</p>
                    </div>
                                  <button type="button" id="add-banner-btn" class="w-full sm:w-auto inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                      <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Add New Banner
                    </button>
                </div>
                         </div>
                         <div class="p-6">
                              <p class="text-sm text-gray-600 mb-4">Drag and drop banners using the handle icon to reorder them.</p>
                              <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                                          <tr>
                                              <th scope="col" class="w-16 px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content</th>
                                              <th scope="col" class="w-28 px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                              <th scope="col" class="w-28 px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="banner-items" class="banner-sortable bg-white divide-y divide-gray-200">
                                           {{-- Initial Loading State from previous refactor --}}
                                          <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500"><div class="flex flex-col items-center justify-center"><svg class="animate-spin h-8 w-8 text-blue-500 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><p class="text-sm font-medium">Loading banners...</p></div></td></tr>
                                           {{-- JS will render banner rows here --}}
                        </tbody>
                    </table>
                              </div>
            </div>
        </div>

                     {{-- Carousel Settings Card (Still within the 'Banners & Carousel' tab) --}}
                     <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                            <div class="flex items-center mb-3 sm:mb-0">
                        <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
                    </div>
                        <div class="ml-4">
                             <h3 class="text-lg font-medium text-gray-900">Carousel Settings</h3>
                             <p class="text-sm text-gray-500 mt-1">Configure autoplay, navigation, and other carousel options.</p>
            </div>
        </div>
                            {{-- Simple toggle, no Alpine needed here as it's self-contained JS --}}
                            <button type="button" id="toggle-carousel-settings" class="w-full sm:w-auto inline-flex items-center justify-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                        <span id="toggle-text">Show Settings</span>
                                <svg class="ml-2 -mr-0.5 h-4 w-4 toggle-icon transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                </div>
                
                        <div id="carousel-settings-container" class="hidden border-t border-gray-200 pt-6 mt-4">
                            <form id="carousel-settings-form" class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        @include('admin.homepage.partials.carousel-settings')
                        <div class="md:col-span-2 mt-4 text-right">
                                     <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">Save Carousel Settings</button>
                </div>
                    </form>
        </div>
    </div>
                 </section>
</div>
        </div> {{-- End #homepage-tab-content --}}
    </div> {{-- End Alpine x-data container --}}
</div> {{-- End .container --}}

{{-- Include the Product Template Selection Modal --}}
@include('admin.homepage.partials.product-template-modal')

{{-- Include the Banner Modal --}}
@include('admin.homepage.partials.banner-modal')

@endsection

@push('styles')
<style>
.sortable-ghost {
    background-color: #DBEAFE; /* Tailwind bg-blue-100 */
    opacity: 0.5; /* Tailwind opacity-50 */
    border: 1px dashed #93C5FD; /* Optional: add a dashed border for better visibility */
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Declare variables in the top scope, accessible after DOMContentLoaded
    let productModal = null;
    let productModalTitle = null;
    let productModalSectionName = null;
    let productSearchInput = null;
    let productListContainer = null;
    let productListLoading = null;
    let productListTable = null;
    let productListBody = null;
    let productListEmptyState = null;
    let productListError = null;
    let addSelectedProductsBtn = null;
    let cancelProductModalBtn = null;
    let selectAllProductsCheckbox = null;

    // Simple Notification System (Keep)
    // window.NotificationSystem = { /* ... */ }; // Assume definition exists
    // function toggleButtonLoading(button, isLoading, originalHtmlContent = null) { /* ... */ } // Assume definition exists

    // --- Vanilla JS Tab Management ---
    const tabButtons = document.querySelectorAll('#homepage-tabs button[role="tab"]');
    const tabPanels = document.querySelectorAll('#homepage-tab-content > div[role="tabpanel"]');
    let initializedTabs = {}; // Track initialized tabs

    function initTabContent(tabName) {
        if (initializedTabs[tabName]) {
            console.log(`Tab ${tabName} already initialized.`);
            return; // Don't re-initialize
        }
        console.log(`Initializing content for tab: ${tabName}`);
        switch(tabName) {
            case 'global-settings':
                initGlobalSettings();
                break;
            case 'featured-products':
                initProductList('featured');
                break;
            case 'new-arrivals':
                initProductList('new-arrival');
                break;
            case 'sale-products':
                initProductList('sale');
                break;
            case 'banners-carousel':
                initBanners();
                break;
        }
        initializedTabs[tabName] = true; // Mark as initialized
    }

    function switchToTab(tabName) {
        if (!tabName) return;

        tabButtons.forEach(button => {
            const isCurrent = button.getAttribute('aria-controls') === tabName;
            button.setAttribute('aria-selected', isCurrent);
            // Adjust classes for active/inactive states
            button.classList.toggle('text-blue-600', isCurrent);
            button.classList.toggle('border-blue-600', isCurrent);
            button.classList.toggle('text-gray-500', !isCurrent);
            button.classList.toggle('border-transparent', !isCurrent);
            button.classList.toggle('hover:text-gray-600', !isCurrent);
            button.classList.toggle('hover:border-gray-300', !isCurrent);
            // Add/remove 'active' class if your CSS relies on it
            button.classList.toggle('active', isCurrent);
        });

        tabPanels.forEach(panel => {
            if (panel.id === tabName) {
                panel.classList.remove('hidden');
                initTabContent(tabName); // Initialize content when tab becomes visible
                } else {
                panel.classList.add('hidden');
            }
        });

        // Optional: Persist tab state using localStorage or URL hash
        // Example: window.location.hash = tabName;
        // Example: localStorage.setItem('homepage_active_tab', tabName);
    }

    // Add event listeners to tab buttons
        tabButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            const targetTab = button.getAttribute('aria-controls');
            switchToTab(targetTab);
        });
    });

    // --- Content Initialization Functions (Now Global/Scoped) ---

    function initGlobalSettings() {
        console.log('Initializing Global Settings...');
        const stockThresholdForm = document.getElementById('stock-threshold-form');
        if (stockThresholdForm && !stockThresholdForm.dataset.initialized) {
            stockThresholdForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                const saveBtn = e.target.querySelector('#save-threshold-btn');
                saveSettings(stockThresholdForm, '/admin/homepage/settings/threshold', saveBtn);
            });
        const runFilterBtn = document.getElementById('run-filter-btn');
        if (runFilterBtn) {
             runFilterBtn.addEventListener('click', () => {
                    console.warn('Run filter now - AJAX not implemented.');
                    // TODO: Implement AJAX POST to run filter
                });
            }
            stockThresholdForm.dataset.initialized = 'true';
        }
    }

    // MODIFIED: initProductList to fetch and render data
    function initProductList(type) {
        console.log(`Initializing/Refreshing Product List for type: ${type}`);
        const typeMap = {
            'featured': '.featured-products-sortable',
            'new-arrival': '.new-arrivals-sortable',
            'sale': '.sale-products-sortable'
        };
        const tbodySelector = typeMap[type];
        if (!tbodySelector) {
            console.error(`Invalid type provided to initProductList: ${type}`);
            return;
        }
        const tbodyElement = document.querySelector(tbodySelector);
        if (!tbodyElement) {
            console.error(`Tbody element not found for selector: ${tbodySelector}`);
            return;
        }

        // Clear existing content and show loading state
        tbodyElement.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Loading products...</td></tr>`; // Adjust colspan as needed
        // Optional: Disable sortable while loading
        if (sortableInstances[tbodySelector]) {
            sortableInstances[tbodySelector].option("disabled", true);
        }

        // Fetch product data for the section (start with page 1)
        const url = `/admin/homepage/section-products/${type}?page=1`; // Fetch first page
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success && Array.isArray(data.products)) {
                renderProductTableRows(tbodyElement, data.products, type);
                // TODO: Update pagination controls using data.pagination if needed
                // Initialize sortable *after* rendering
                initSortableContainer(tbodySelector, type); // Re-init or enable sortable
            } else {
                throw new Error(data.message || 'Invalid data format received.');
            }
        })
        .catch(error => {
            console.error(`Error fetching products for section ${type}:`, error);
            tbodyElement.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-red-500">Failed to load products.</td></tr>`; // Adjust colspan
        })
        .finally(() => {
             // Optional: Re-enable sortable if it was disabled
             if (sortableInstances[tbodySelector]) {
                 sortableInstances[tbodySelector].option("disabled", false);
             }
        });
    }

    // NEW: Function to render product rows in the main table
    function renderProductTableRows(tbodyElement, products, type) {
        tbodyElement.innerHTML = ''; // Clear loading/error message

        if (!products || products.length === 0) {
            let colspan = 5; // Default colspan
            if (type === 'new-arrival') colspan = 6; // New arrivals table has 6 columns
            tbodyElement.innerHTML = `
                <tr>
                    <td colspan="${colspan}" class="px-6 py-10 whitespace-nowrap text-sm text-gray-500 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                            <p class="mt-1 font-medium">No ${type.replace('-', ' ')} templates found.</p>
                            <p class="mt-2 text-xs text-gray-500">Add templates using the button above.</p>
                        </div>
                    </td>
                </tr>`;
            return;
        }

        products.forEach(product => {
            const row = document.createElement('tr');
            row.id = `${type}-product-${product.id}`; // e.g., featured-product-1
            row.dataset.id = product.id;
            row.classList.add('hover:bg-gray-50', 'transition-colors', 'duration-150');

            const categoryName = product.category ? product.category.name : 'No Category';
            const isActive = product.is_active;
            const statusClass = isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            const statusText = isActive ? 'Active' : 'Inactive';
            const paramCount = Array.isArray(product.parameters) ? product.parameters.length : 0; // Assuming parameters is array
            const removeButtonClass = `remove-${type}-product`; // e.g., remove-featured-product
             const removeButtonTitle = `Remove from ${type.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())}`;

            // --- Row HTML (needs to match the specific table structure for each type) ---
             let rowHTML = `
                 <td class="w-16 px-4 py-3 whitespace-nowrap text-center cursor-move drag-handle">
                     <div class="flex items-center justify-center text-gray-400 hover:text-gray-600"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg></div>
                 </td>
                 <td class="px-6 py-3 whitespace-nowrap">
                     <div class="flex items-center">
                         <div class="flex-shrink-0 h-10 w-10">
                             ${product.image_url && !product.image_url.endsWith('placeholder.png')
                                 ? `<img class="h-10 w-10 rounded-md object-cover" src="${product.image_url}" alt="${product.name || ''}">`
                                 : `<div class="h-10 w-10 rounded-md bg-gray-200 flex items-center justify-center"><svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>`
                             }
                         </div>
                         <div class="ml-4">
                             <div class="text-sm font-medium text-gray-900">${product.name || 'N/A'}</div>
                             <div class="text-xs text-gray-500">${categoryName}</div>
                         </div>
                     </div>
                 </td>
                 <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">
                     ${paramCount > 0
                         ? `<span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">${paramCount} Parameters</span>`
                         : `<span class="text-xs text-gray-400">None</span>`
                     }
                 </td>
            `;

            // Add 'Created' column specifically for New Arrivals
             if (type === 'new-arrival') {
                 rowHTML += `<td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">${product.created_at_formatted || 'N/A'}</td>`;
             }

             rowHTML += `
                 <td class="px-6 py-3 whitespace-nowrap">
                     <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">${statusText}</span>
                 </td>
                 <td class="w-28 px-6 py-3 whitespace-nowrap text-center text-sm font-medium">
                     <div class="flex items-center justify-center space-x-2">
                         <a href="/product-templates/${product.id}" target="_blank" class="p-1 text-gray-400 hover:text-blue-600 transition-colors duration-150 rounded-full hover:bg-blue-100" title="View Template"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></a>
                         <button type="button" class="${removeButtonClass} p-1 text-gray-400 hover:text-red-600 transition-colors duration-150 rounded-full hover:bg-red-100" data-id="${product.id}" title="${removeButtonTitle}"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                     </div>
                 </td>
             `;
            // --- End Row HTML ---

            row.innerHTML = rowHTML;
            tbodyElement.appendChild(row);
        });
    }

    function initBanners() {
        console.log('Initializing Banners & Carousel...');
        initSortableContainer('#banner-items', 'banner', '.drag-handle');
        setupCarouselToggle();
        loadBanners(); // Load banners when the tab is initialized
    }

    function setupCarouselToggle() {
        const btn = document.getElementById('toggle-carousel-settings');
        const container = document.getElementById('carousel-settings-container');
        const text = document.getElementById('toggle-text');
        const icon = btn ? btn.querySelector('.toggle-icon') : null;

        if (btn && container && text && icon && !btn.dataset.initialized) {
            btn.addEventListener('click', () => {
                const isHidden = container.classList.toggle('hidden');
                container.classList.toggle('pt-6', !isHidden);
                container.classList.toggle('mt-4', !isHidden);
                text.textContent = isHidden ? 'Show Settings' : 'Hide Settings';
                icon.classList.toggle('rotate-180', !isHidden);
            });
            const form = document.getElementById('carousel-settings-form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    saveSettings(form, '/admin/homepage/settings/carousel', form.querySelector('button[type="submit"]'));
                });
            }
            btn.dataset.initialized = 'true';
        }
    }

    // --- Shared Functions (Now Global/Scoped) ---
    const sortableInstances = {}; // Store Sortable instances to prevent re-init

    // Modify initSortableContainer slightly to handle re-initialization
    function initSortableContainer(selector, type, handleClass = '.drag-handle') {
        const container = document.querySelector(selector);
        if (!container) {
            console.warn(`Sortable container not found: ${selector}`);
            return;
        }
        // Destroy existing instance if it exists
        if (sortableInstances[selector]) {
            console.log(`Destroying existing sortable for: ${selector}`);
            sortableInstances[selector].destroy();
        }
        console.log(`Initializing sortable for: ${selector}`);
        sortableInstances[selector] = new Sortable(container, {
            animation: 150,
            handle: handleClass,
            ghostClass: 'sortable-ghost',
            disabled: false, // Ensure it's enabled initially
            onEnd: () => {
                updateOrder(type, container);
            }
        });
    }

    function updateOrder(type, container) {
        const items = Array.from(container.children).map(el => el.dataset.id).filter(id => id);
        
        // Determine the correct URL based on the type
        let orderUrl;
        if (type === 'banner') {
            orderUrl = '/admin/banners/update-order';
        } else if (['featured', 'new-arrival', 'sale'].includes(type)) {
            orderUrl = `/admin/homepage/update-${type}-order`; // Correct pattern for products
        } else {
            console.error(`Unknown type for updateOrder: ${type}`);
            return; // Don't proceed if type is unknown
        }
        
        console.warn(`Update ${type} order: [${items.join(', ')}] - Calling ${orderUrl}`);

        // Actual AJAX call
        fetch(orderUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json', // Indicate we expect JSON
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') // Get CSRF token
            },
            body: JSON.stringify({ ids: items }) // Send the array under the key 'ids'
        })
        .then(response => {
            // Check if the response is ok AND if it looks like JSON before parsing
            if (!response.ok) {
                // Try to read the response as text for more informative errors
                return response.text().then(text => {
                    console.error('Server Error Response Text:', text);
                    throw new Error(`HTTP error! status: ${response.status}`);
                });
            }
            // Check Content-Type before assuming JSON
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) {
                return response.json();
            } else {
                return response.text().then(text => {
                    console.error('Received non-JSON response:', text);
                    throw new Error('Received non-JSON response from server.');
                });
            }
        })
            .then(data => {
            if(data.success) {
                 window.NotificationSystem.Toast.success('Order updated successfully.'); // Use Toast
                } else {
                 window.NotificationSystem.Toast.error(data.message || 'Failed to update order.');
                 console.error('Failed to update order:', data);
            }
        })
        .catch(error => {
             window.NotificationSystem.Toast.error('Error updating order.');
             console.error('Error updating order:', error);
        });
    }

    function loadBanners() {
        console.log('Fetching banners...');
        const tbody = document.getElementById('banner-items');
        if (!tbody) return;

        tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-10 text-center text-gray-500"><div class="flex flex-col items-center justify-center"><svg class="animate-spin h-8 w-8 text-blue-500 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><p class="text-sm font-medium">Loading banners...</p></div></td></tr>`;

        fetch('/admin/banners/list') // <--- 修改 URL
            .then(response => {
                if (!response.ok) {
                    console.error('Fetch Error Response:', response);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                console.log('Fetch Success Response Status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Received banner data:', data);
                renderBanners(data.banners || data); // Handle potential wrapping like { banners: [...] }
            })
                .catch(error => {
                console.error('Error loading or processing banners:', error);
                tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-10 text-center text-red-500"><p>Error loading banners. Please check the console or try again later.<br><small>${error.message}</small></p></td></tr>`;
                window.NotificationSystem.Toast.error('Failed to load banners.');
            });
    }

    function renderBanners(banners) {
        const tbody = document.getElementById('banner-items');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (!banners || !Array.isArray(banners) || banners.length === 0) {
            console.log('No valid banner data to render.');
            tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-10 text-center text-gray-500"><p>No banners found. Click 'Add New Banner' to create one.</p></td></tr>`;
            // Ensure Sortable is destroyed if the list becomes empty after being populated
            if (sortableInstances['#banner-items']) {
                console.log('Destroying sortable for empty banner list.');
                sortableInstances['#banner-items'].destroy();
                delete sortableInstances['#banner-items'];
            }
            return;
        }

        console.log(`Rendering ${banners.length} banners.`);
        banners.forEach((banner, index) => {
                         const row = document.createElement('tr');
            row.id = `banner-${banner.id}`;
                         row.dataset.id = banner.id;
            row.classList.add('hover:bg-gray-50', 'transition-colors', 'duration-150');

            const isActive = banner.is_active;
            const statusText = isActive ? 'Active' : 'Inactive';
            const statusClass = isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            const toggleButtonText = isActive ? 'Deactivate' : 'Activate';
            const toggleButtonIcon = isActive
                ? `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>`
                : `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
            const toggleButtonClass = isActive ? 'text-yellow-600 hover:text-yellow-800 hover:bg-yellow-100' : 'text-green-600 hover:text-green-800 hover:bg-green-100';

            const escapeHtml = (unsafe) => {
                if (unsafe === null || typeof unsafe === 'undefined') return '';
                return String(unsafe)
                     .replace(/&/g, "&amp;")
                     .replace(/</g, "&lt;")
                     .replace(/>/g, "&gt;")
                     .replace(/"/g, "&quot;")
                     .replace(/'/g, "&#039;");
            };

                         row.innerHTML = `
                <td class="w-16 px-4 py-3 whitespace-nowrap text-center cursor-move drag-handle">
                    <div class="flex items-center justify-center text-gray-400 hover:text-gray-600"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg></div>
                </td>
                <td class="px-6 py-3 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-16">
                            ${banner.image_url
                                ? `<img class="h-10 w-16 rounded-md object-contain bg-gray-100" src="${escapeHtml(banner.image_url)}" alt="Banner ${banner.id}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">`
                                : ''}
                            <div class="h-10 w-16 rounded-md bg-gray-200 items-center justify-center text-gray-400" style="${banner.image_url ? 'display:none;' : 'display:flex;'}"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-3 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${escapeHtml(banner.title) || '-'}</div>
                    <div class="text-xs text-gray-500">${escapeHtml(banner.subtitle) || '-'}</div>
                    <div class="text-xs text-blue-500 truncate">${banner.link ? `<a href="${escapeHtml(banner.link)}" target="_blank" class="hover:underline">${escapeHtml(banner.link)}</a>` : '-'}</div>
                </td>
                <td class="w-28 px-6 py-3 whitespace-nowrap text-center">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">${statusText}</span>
                </td>
                <td class="w-28 px-6 py-3 whitespace-nowrap text-center text-sm font-medium">
                     <div class="flex items-center justify-center space-x-1">
                         <button type="button" class="edit-banner p-1 text-gray-400 hover:text-blue-600 transition-colors duration-150 rounded-full hover:bg-blue-100" data-id="${banner.id}" title="Edit Banner"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                         <button type="button" class="toggle-banner-active p-1 ${toggleButtonClass} transition-colors duration-150 rounded-full" data-id="${banner.id}" data-active="${isActive ? '1' : '0'}" title="${toggleButtonText}">${toggleButtonIcon}</button>
                         <button type="button" class="delete-banner p-1 text-gray-400 hover:text-red-600 transition-colors duration-150 rounded-full hover:bg-red-100" data-id="${banner.id}" title="Delete Banner"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                     </div>
                </td>
            `;
            tbody.appendChild(row);
        });

        // Initialize or re-initialize sortable for the populated list
        if (sortableInstances['#banner-items']) {
             console.log('Sortable already exists for banner items.');
        } else {
             console.log('Initializing sortable for banner items after render.');
             initSortableContainer('#banner-items', 'banner', '.drag-handle');
        }
    }

    function saveSettings(formElement, url, button) {
        console.log(`Saving settings via POST /admin/homepage/settings`);

        const formData = new FormData(formElement);
        toggleButtonLoading(button, true);

        fetch('/admin/homepage/settings', { // Use the unified endpoint
                method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Accept': 'application/json', // Expect JSON response
            },
            body: formData
        })
        .then(response => {
            // Check response status before trying to parse
            if (!response.ok) {
                // If not ok, try to parse error message if available, otherwise throw generic error
                return response.json().catch(() => {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }).then(errorData => {
                    throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
                .then(data => {
                    if (data.success) {
                window.NotificationSystem.Toast.success(data.message || 'Settings saved successfully.');
            } else {
                // Use the error message from the thrown error if available
                throw new Error(data.message || 'Failed to save settings.');
            }
                })
                .catch(error => {
            window.NotificationSystem.Toast.error(error.message || 'An error occurred while saving settings.');
            console.error('Save settings fetch error:', error);
        })
        .finally(() => {
            toggleButtonLoading(button, false);
        });
    }

    function addProductTemplate(type, productId, button) {
        console.warn(`Adding product template ${productId} to ${type} list - AJAX call not implemented.`);
        // TODO: AJAX POST to add product to list
        // On success, potentially reload the list or add row dynamically + reinit sortable
    }

    function removeProductTemplate(productId, type, button) {
        const typeName = type.replace('-', ' '); // e.g., 'new arrival'
        Swal.fire({
            title: 'Are you sure?',
            text: `Remove this product from the ${typeName} list?`, // Use SweetAlert
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                console.warn(`Removing product template ${productId} from ${type} list - AJAX call not implemented.`);
                // TODO: Implement AJAX DELETE request to `/admin/homepage/remove-product/${type}/${productId}`
                // On success:
                // document.getElementById(`${type}-product-${productId}`)?.remove();
                // window.NotificationSystem.Toast.success('Product removed.');
                // On failure:
                // window.NotificationSystem.Toast.error('Failed to remove product.');
            }
        });
    }

    function deleteBanner(bannerId, button) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Delete this banner permanently?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                console.warn(`Deleting banner ${bannerId} - AJAX call not implemented.`);
                // TODO: Implement AJAX DELETE request to `/admin/homepage/banners/${bannerId}`
                // On success:
                // document.getElementById(`banner-${bannerId}`)?.remove();
                // window.NotificationSystem.Toast.success('Banner deleted.');
                 // On failure:
                // window.NotificationSystem.Toast.error('Failed to delete banner.');
            }
        });
    }

    function toggleBannerActive(bannerId, makeActive, button) {
        const action = makeActive ? 'activate' : 'deactivate';
        console.warn(`Toggling banner ${bannerId} active status to ${makeActive} (${action}) - AJAX call not implemented.`);
        // TODO: Implement AJAX POST/PUT request to `/admin/homepage/banners/${bannerId}/toggle`
        // On success:
        // 1. Update button icon, title, class, data-active attribute
        // 2. Update status badge text and class
        // 3. window.NotificationSystem.Toast.success(`Banner ${action}d.`);
        // On failure:
        // window.NotificationSystem.Toast.error(`Failed to ${action} banner.`);
    }

    // --- Global Event Listeners (Delegation) ---
    document.body.addEventListener('click', function(event) {
        // Remove Product Buttons
        const removeButton = event.target.closest('.remove-featured-product, .remove-new-product, .remove-sale-product');
        if (removeButton) {
            const productId = removeButton.dataset.id;
            let type = '';
            if (removeButton.classList.contains('remove-featured-product')) type = 'featured';
            else if (removeButton.classList.contains('remove-new-product')) type = 'new-arrival';
            else if (removeButton.classList.contains('remove-sale-product')) type = 'sale';
            removeProductTemplate(productId, type, removeButton);
        }

        // Delete Banner Button
        const deleteBannerButton = event.target.closest('.delete-banner');
        if(deleteBannerButton) {
            const bannerId = deleteBannerButton.dataset.id;
            deleteBanner(bannerId, deleteBannerButton);
        }

        // Toggle Banner Status Button
        const toggleBannerButton = event.target.closest('.toggle-banner-active');
        if(toggleBannerButton) {
            const bannerId = toggleBannerButton.dataset.id;
            const currentActiveState = toggleBannerButton.dataset.active === '1';
            toggleBannerActive(bannerId, !currentActiveState, toggleBannerButton);
        }

        // Add Product Buttons (Modal Trigger)
        const addProductButton = event.target.closest('.add-product-btn');
        if (addProductButton) {
            const type = addProductButton.dataset.type;
            openProductModal(type);
        }

        // Add Banner Button (Modal Trigger)
        const addBannerButton = event.target.closest('#add-banner-btn');
        if (addBannerButton) {
            event.preventDefault();
            // Assuming a global function exists or needs to be created: openBannerModal(false);
            // This function likely needs to be defined in or included from banner-modal.blade.php
            if (typeof openBannerModal === 'function') {
                openBannerModal(false); // Open in create mode
                    } else {
                console.error('openBannerModal function is not defined. Check banner-modal.blade.php');
                window.NotificationSystem.Toast.error('Cannot open banner modal.');
            }
        }

        // Edit Banner Button (Modal Trigger)
        const editBannerButton = event.target.closest('.edit-banner');
        if (editBannerButton) {
            event.preventDefault();
            const bannerId = editBannerButton.dataset.id;
            console.log(`Edit banner button clicked for ID: ${bannerId}`);
            // Fetch banner data first
            // TODO: Add loading state UI indication
            fetch(`/admin/banners/${bannerId}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data && (data.banner || data)) { // Allow direct banner object or {banner: ...}
                        // Assuming a global function exists or needs to be created: openBannerModal(true, data.banner || data);
                        if (typeof openBannerModal === 'function') {
                             openBannerModal(true, data.banner || data);
                         } else {
                             console.error('openBannerModal function is not defined. Check banner-modal.blade.php');
                             window.NotificationSystem.Toast.error('Cannot open banner modal for editing.');
                         }
                    } else {
                        console.error('Invalid data received for banner:', data);
                        window.NotificationSystem.Toast.error('Could not load banner data.');
                    }
                })
                .catch(error => {
                    console.error('Error fetching banner data for edit:', error);
                    window.NotificationSystem.Toast.error('Could not load banner data.');
                });
                // TODO: Remove loading state UI indication
        }
    });

    // --- Form Submissions (Settings) ---
    document.querySelectorAll('form[id$="-settings-form"]').forEach(form => {
        if (!form.dataset.submitListenerAttached) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const section = form.id.replace('-settings-form', ''); // e.g., 'featured'
                let url;
                if (section === 'stock-threshold') {
                    url = '/admin/homepage/settings/threshold'; // Specific URL for threshold
                } else if (section === 'carousel') {
                    url = '/admin/homepage/settings/carousel'; // Specific URL for carousel
            } else {
                    url = `/admin/homepage/settings/${section}`; // Generic URL for others
                }
                saveSettings(form, url, form.querySelector('button[type="submit"]'));
            });
            form.dataset.submitListenerAttached = 'true';
        }
    });

    // --- Initial Page Load ---
    document.addEventListener('DOMContentLoaded', () => {
        // Determine initial tab (e.g., from URL hash or localStorage, default to 'global-settings')
        let initialTab = 'global-settings'; // Default
        // Example: Check hash
        // if (window.location.hash) {
        //     const hashTab = window.location.hash.substring(1);
        //     if (document.getElementById(hashTab)) { // Check if element exists
        //        initialTab = hashTab;
        //     }
        // }
        // Example: Check localStorage
        // const savedTab = localStorage.getItem('homepage_active_tab');
        // if (savedTab && document.getElementById(savedTab)) {
        //     initialTab = savedTab;
        // }

        console.log(`Initializing page with tab: ${initialTab}`);
        switchToTab(initialTab);

         // Add listener for banner saved event (Needs to be dispatched from banner-modal.js)
         document.body.addEventListener('banner-saved', () => {
             console.log('Banner saved event received, reloading banners...');
             // Ensure banner tab is active or simply reload data if visible
             const bannerPanel = document.getElementById('banners-carousel');
             if (bannerPanel && !bannerPanel.classList.contains('hidden')) {
                    loadBanners();
                } else {
                 // Optionally mark the tab as needing refresh if it's not visible
                 initializedTabs['banners-carousel'] = false; 
                 console.log('Banner tab not visible, marked for re-init.');
             }
         });

         // --- Assign Modal Elements after DOM is loaded ---
         productModal = document.getElementById('product-template-modal'); // Assign to top-level variable
         productModalTitle = document.getElementById('modal-title');
         productModalSectionName = document.getElementById('modal-section-name');
         productSearchInput = document.getElementById('product-search');
         productListContainer = document.getElementById('product-list-container');
         productListLoading = document.getElementById('product-list-loading');
         productListTable = document.getElementById('product-list-table');
         productListBody = document.getElementById('product-list-body');
         productListEmptyState = document.getElementById('product-list-empty-state');
         productListError = document.getElementById('product-list-error');
         addSelectedProductsBtn = document.getElementById('add-selected-products-btn');
         cancelProductModalBtn = document.getElementById('cancel-product-modal-btn');
         selectAllProductsCheckbox = document.getElementById('select-all-products');

         // --- Event Listeners for Product Modal ---
         // Now uses the top-level variables assigned above
         if (productModal) { // Check if modal element was found
             if(cancelProductModalBtn) cancelProductModalBtn.addEventListener('click', closeProductModal);
             if(addSelectedProductsBtn) addSelectedProductsBtn.addEventListener('click', handleAddSelectedProducts);

             // Close modal on background click
             productModal.addEventListener('click', (event) => {
                 if (event.target === productModal) { // Check if the click is on the backdrop itself
                     closeProductModal();
                 }
             });

             // Product search input
             if(productSearchInput) productSearchInput.addEventListener('input', () => {
                 clearTimeout(productSearchTimeout);
                 productSearchTimeout = setTimeout(filterProducts, 300); // Debounce search
             });

             // Checkbox delegation for enabling/disabling add button and updating select-all state
             if(productListBody) productListBody.addEventListener('change', (event) => {
                 if (event.target.classList.contains('product-select-checkbox')) {
                     updateAddButtonState(); // Call directly, uses top-level vars
                     updateSelectAllCheckboxState(); // Call directly, uses top-level vars
                 }
             });

             // Select/Deselect All Checkbox
             if(selectAllProductsCheckbox) selectAllProductsCheckbox.addEventListener('change', (event) => {
                 const isChecked = event.target.checked;
                 if (productListBody && addSelectedProductsBtn) {
                     const allCheckboxes = productListBody.querySelectorAll('.product-select-checkbox');
                     allCheckboxes.forEach(checkbox => {
                         checkbox.checked = isChecked;
                     });
                     updateAddButtonState(); // Call directly
                 }
             });
         } else {
             console.warn('Product modal element not found during DOMContentLoaded.');
         }

    });

    // Basic button loading state handler
    function toggleButtonLoading(button, isLoading, loadingText = 'Saving...') {
        if (!button) return;
        if (isLoading) {
            // Store original content if it hasn't been stored
            if (!button.dataset.originalContent) {
                button.dataset.originalContent = button.innerHTML;
            }
            button.disabled = true;
            button.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                ${loadingText}
            `;
            button.classList.add('cursor-not-allowed', 'opacity-75');
            } else {
            button.disabled = false;
            // Restore original content if it was stored
            if (button.dataset.originalContent) {
                button.innerHTML = button.dataset.originalContent;
                // Clear the stored content after restoring
                delete button.dataset.originalContent;
            }
            button.classList.remove('cursor-not-allowed', 'opacity-75');
        }
    }

    // --- Product Template Modal Elements ---
    // Moved inside DOMContentLoaded

    let currentProductModalType = null; // Kept global as it stores state across function calls
    let availableProducts = []; // Kept global as it stores state across function calls
    let productSearchTimeout = null; // Kept global for clearTimeout

    // --- Product Modal Functions ---

    function openProductModal(type) {
        // Use top-level variables (assigned in DOMContentLoaded)
        console.log(`Opening product modal for type: ${type}`);

        if (!productModal) {
            console.error('Product modal element not found (or not assigned yet). Check DOMContentLoaded.');
            window.NotificationSystem.Toast.error('Cannot open product modal.');
            return;
        }

        currentProductModalType = type;
        const sectionName = type.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase()); // Format type for display
        if(productModalSectionName) productModalSectionName.textContent = sectionName;

        // Reset modal state (check if elements exist before accessing properties)
        if(productSearchInput) productSearchInput.value = '';
        if(productListError) {
            productListError.classList.add('hidden');
            productListError.textContent = '';
        }
        if(productListBody) productListBody.innerHTML = ''; // Clear previous list
        if(productListEmptyState) productListEmptyState.classList.add('hidden');
        if(productListTable) productListTable.classList.add('hidden');
        if(productListLoading) productListLoading.classList.remove('hidden');
        if(addSelectedProductsBtn) addSelectedProductsBtn.disabled = true;
        if(selectAllProductsCheckbox) {
            selectAllProductsCheckbox.checked = false;
            selectAllProductsCheckbox.indeterminate = false;
            selectAllProductsCheckbox.disabled = true; // Disable until items load
        }

        productModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden'); // Prevent background scrolling

        fetchAvailableProducts(type);
    }

    function closeProductModal() {
        // Use top-level variable
        if (productModal) {
            productModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
        currentProductModalType = null;
        availableProducts = []; // Clear fetched products
    }

    function fetchAvailableProducts(type) {
        const url = `/admin/homepage/available-products/${type}`;
        console.log(`Fetching available products from: ${url}`);
        // Use top-level variables

        if(productListLoading) productListLoading.classList.remove('hidden');
        if(productListTable) productListTable.classList.add('hidden');
        if(productListError) productListError.classList.add('hidden');

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest' // Important for Laravel request detection
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Available products received:', data);
            if (data.success && Array.isArray(data.templates)) {
                availableProducts = data.templates;
                renderProductList(availableProducts); // Initial render with all products
            } else {
                throw new Error(data.message || 'Invalid data format received.');
            }
        })
        .catch(error => {
            console.error('Error fetching available products:', error);
            productListError.textContent = `Failed to load products: ${error.message}`;
            productListError.classList.remove('hidden');
            availableProducts = [];
            renderProductList([]); // Render empty state
        })
        .finally(() => {
            productListLoading.classList.add('hidden');
        });
    }

    function renderProductList(productsToRender) {
        // Uses top-level variables: productListBody, productListTable, productListEmptyState
        if (!productListBody || !productListTable || !productListEmptyState) {
            console.error('renderProductList: Required elements (body, table, empty) not found or assigned.');
            return;
        }
        productListBody.innerHTML = ''; // Clear existing rows
        productListTable.classList.remove('hidden');
        productListEmptyState.classList.add('hidden'); // Assume not empty initially

        if (!productsToRender || productsToRender.length === 0) {
            productListEmptyState.classList.remove('hidden');
            if(addSelectedProductsBtn) addSelectedProductsBtn.disabled = true;
            if(selectAllProductsCheckbox) {
                selectAllProductsCheckbox.checked = false;
                selectAllProductsCheckbox.indeterminate = false;
                selectAllProductsCheckbox.disabled = true; // Disable select-all if no items
            }
            return;
        }

        if(selectAllProductsCheckbox) selectAllProductsCheckbox.disabled = false; // Enable select-all if items exist

        productsToRender.forEach(product => {
            const row = document.createElement('tr');
            row.classList.add('hover:bg-gray-50');
            row.dataset.productId = product.id;

            const categoryName = product.category ? product.category.name : 'N/A';
            const imageUrl = product.images && product.images.length > 0 ? product.images[0].thumbnail || product.images[0].url : null;

            row.innerHTML = `
                <td class="w-10 px-4 py-2">
                    <input type="checkbox" name="selected_products[]" value="${product.id}" class="product-select-checkbox form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                </td>
                <td class="px-4 py-2 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            ${imageUrl
                                ? `<img class="h-10 w-10 rounded-md object-cover" src="${imageUrl}" alt="">`
                                : `<div class="h-10 w-10 rounded-md bg-gray-200 flex items-center justify-center text-gray-400"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>`
                            }
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900">${product.name || 'N/A'}</div>
                            <!-- REMOVED SKU DISPLAY -->
                            <!-- <div class="text-xs text-gray-500">${product.sku || 'No SKU'}</div> --> 
                        </div>
                    </div>
                </td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">${categoryName}</td>
            `;
            productListBody.appendChild(row);
        });
        updateAddButtonState(); // Uses top-level addSelectedProductsBtn, productListBody
        updateSelectAllCheckboxState(); // Uses top-level selectAllProductsCheckbox, productListBody
    }

    function filterProducts() {
        // Use top-level productSearchInput
        const searchTerm = productSearchInput ? productSearchInput.value.toLowerCase().trim() : '';
        const filteredProducts = availableProducts.filter(product => {
            const nameMatch = product.name && product.name.toLowerCase().includes(searchTerm);
            const skuMatch = product.sku && product.sku.toLowerCase().includes(searchTerm);
            return nameMatch || skuMatch;
        });
        renderProductList(filteredProducts);
    }

    // Uses top-level variables
    function updateAddButtonState() {
        if (!addSelectedProductsBtn || !productListBody) return;
        const selectedCheckboxes = productListBody.querySelectorAll('.product-select-checkbox:checked');
        addSelectedProductsBtn.disabled = selectedCheckboxes.length === 0;
    }

    // Uses top-level variables
    function updateSelectAllCheckboxState() {
        if (!selectAllProductsCheckbox || !productListBody) return;
        const allCheckboxes = productListBody.querySelectorAll('.product-select-checkbox');
        const checkedCheckboxes = productListBody.querySelectorAll('.product-select-checkbox:checked');

        if (allCheckboxes.length === 0) {
            selectAllProductsCheckbox.checked = false;
            selectAllProductsCheckbox.indeterminate = false;
            selectAllProductsCheckbox.disabled = true; // Disable if no items
        } else if (checkedCheckboxes.length === 0) {
            selectAllProductsCheckbox.checked = false;
            selectAllProductsCheckbox.indeterminate = false;
            selectAllProductsCheckbox.disabled = false;
        } else if (checkedCheckboxes.length === allCheckboxes.length) {
            selectAllProductsCheckbox.checked = true;
            selectAllProductsCheckbox.indeterminate = false;
            selectAllProductsCheckbox.disabled = false;
        } else {
            selectAllProductsCheckbox.checked = false;
            selectAllProductsCheckbox.indeterminate = true;
            selectAllProductsCheckbox.disabled = false;
        }
    }

    function handleAddSelectedProducts() {
        if (!currentProductModalType) return;

        // Use top-level variables
         if (!productListBody || !addSelectedProductsBtn) {
             console.error("Cannot find product list body or add button.");
             return;
         }

        const selectedCheckboxes = productListBody.querySelectorAll('.product-select-checkbox:checked');
        const selectedProductIds = Array.from(selectedCheckboxes).map(cb => cb.value);

        if (selectedProductIds.length === 0) {
            // Use a more user-friendly notification if available, otherwise alert
            if (window.NotificationSystem && window.NotificationSystem.Toast) {
                 window.NotificationSystem.Toast.warning('Please select at least one template.');
             } else {
                 alert('Please select at least one template.');
             }
            return;
        }

        const url = `/admin/homepage/add-products/${currentProductModalType}`;
        console.log(`Adding ${selectedProductIds.length} products to ${currentProductModalType} via ${url}`);

        toggleButtonLoading(addSelectedProductsBtn, true, 'Adding...');

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ product_ids: selectedProductIds })
        })
        .then(response => {
             if (!response.ok) {
                 return response.json().catch(() => {
                     throw new Error(`HTTP error! status: ${response.status}`);
                 }).then(errorData => {
                     throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                 });
             }
             return response.json();
        })
        .then(data => {
            if (data.success) {
                if (window.NotificationSystem && window.NotificationSystem.Toast) {
                     window.NotificationSystem.Toast.success(data.message || `${selectedProductIds.length} product(s) added successfully.`);
                 } else {
                     alert(`${selectedProductIds.length} product(s) added successfully.`);
                 }
                closeProductModal();
                // Simple reload for now. A better approach would be to update the list dynamically.
                 window.location.reload();
            } else {
                throw new Error(data.message || 'Failed to add products.');
            }
        })
        .catch(error => {
            if (window.NotificationSystem && window.NotificationSystem.Toast) {
                 window.NotificationSystem.Toast.error(error.message || 'An error occurred while adding products.');
             } else {
                 alert(`Error adding products: ${error.message}`);
             }
            console.error('Add products error:', error);
        })
        .finally(() => {
            toggleButtonLoading(addSelectedProductsBtn, false);
        });
    }

    // --- Event Listeners ---

    // Add listeners for modal controls
    if (productModal) {
        cancelProductModalBtn.addEventListener('click', closeProductModal);
        addSelectedProductsBtn.addEventListener('click', handleAddSelectedProducts);

        // Close modal on background click
        productModal.addEventListener('click', (event) => {
            if (event.target === productModal) { // Check if the click is on the backdrop itself
                closeProductModal();
            }
        });

        // Product search input
        productSearchInput.addEventListener('input', () => {
            clearTimeout(productSearchTimeout);
            productSearchTimeout = setTimeout(filterProducts, 300); // Debounce search
        });

        // Checkbox delegation for enabling/disabling add button and updating select-all state
        productListBody.addEventListener('change', (event) => {
            if (event.target.classList.contains('product-select-checkbox')) {
                updateAddButtonState();
                updateSelectAllCheckboxState();
            }
        });

        // Select/Deselect All Checkbox
        selectAllProductsCheckbox.addEventListener('change', (event) => {
            const isChecked = event.target.checked;
            const allCheckboxes = productListBody.querySelectorAll('.product-select-checkbox');
            allCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateAddButtonState();
        });
    }

    // Modify the global event listener for Add Product buttons
    document.body.addEventListener('click', function(event) {
        // ... existing remove/delete/toggle listeners ...

        // Remove Product Buttons
        const removeButton = event.target.closest('.remove-featured-product, .remove-new-product, .remove-sale-product');
        if (removeButton) {
            const productId = removeButton.dataset.id;
            let type = '';
            if (removeButton.classList.contains('remove-featured-product')) type = 'featured';
            else if (removeButton.classList.contains('remove-new-product')) type = 'new-arrival';
            else if (removeButton.classList.contains('remove-sale-product')) type = 'sale';
            removeProductTemplate(productId, type, removeButton);
        }

        // Delete Banner Button
        const deleteBannerButton = event.target.closest('.delete-banner');
        if(deleteBannerButton) {
            const bannerId = deleteBannerButton.dataset.id;
            deleteBanner(bannerId, deleteBannerButton);
        }

        // Toggle Banner Status Button
        const toggleBannerButton = event.target.closest('.toggle-banner-active');
        if(toggleBannerButton) {
            const bannerId = toggleBannerButton.dataset.id;
            const currentActiveState = toggleBannerButton.dataset.active === '1';
            toggleBannerActive(bannerId, !currentActiveState, toggleBannerButton);
        }

        // Add Product Buttons (Modal Trigger) - MODIFIED
        const addProductButton = event.target.closest('.add-product-btn');
        if (addProductButton) {
            const type = addProductButton.dataset.type;
            openProductModal(type); // <<<--- CALL THE NEW FUNCTION HERE
        }

        // Add Banner Button (Modal Trigger)
        const addBannerButton = event.target.closest('#add-banner-btn');
        if (addBannerButton) {
            event.preventDefault();
            if (typeof openBannerModal === 'function') {
                openBannerModal(false); // Open in create mode
            } else {
                console.error('openBannerModal function is not defined. Check banner-modal.blade.php');
                window.NotificationSystem.Toast.error('Cannot open banner modal.');
            }
        }

        // Edit Banner Button (Modal Trigger)
        const editBannerButton = event.target.closest('.edit-banner');
        if (editBannerButton) {
            event.preventDefault();
            const bannerId = editBannerButton.dataset.id;
            console.log(`Edit banner button clicked for ID: ${bannerId}`);
            fetch(`/admin/banners/${bannerId}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data && (data.banner || data)) { 
                        if (typeof openBannerModal === 'function') {
                             openBannerModal(true, data.banner || data);
                         } else {
                             console.error('openBannerModal function is not defined. Check banner-modal.blade.php');
                             window.NotificationSystem.Toast.error('Cannot open banner modal for editing.');
                         }
                    } else {
                        console.error('Invalid data received for banner:', data);
                        window.NotificationSystem.Toast.error('Could not load banner data.');
                    }
                })
                .catch(error => {
                    console.error('Error fetching banner data for edit:', error);
                    window.NotificationSystem.Toast.error('Could not load banner data.');
                });
        }
    });
    // ... rest of the script ...
</script> 
@endpush