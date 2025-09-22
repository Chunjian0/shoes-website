@extends('layouts.app')

@section('title', 'Edit Quality Inspection')

@section('breadcrumb')
    <nav class="text-sm">
        <ol class="list-none p-0 inline-flex">
            <li class="flex items-center">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
                <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
            </li>
            <li class="flex items-center">
                <a href="{{ route('quality-inspections.index') }}" class="text-gray-500 hover:text-gray-700">Quality Inspection List</a>
                <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
            </li>
            <li>
                <span class="text-gray-700">Edit Quality Inspection</span>
            </li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-sm" x-data="inspectionForm()">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Edit Quality Inspection</h2>
        </div>

        <form action="{{ route('quality-inspections.update', $qualityInspection) }}" method="POST" class="p-4">
            @csrf
            @method('PUT')

            <!-- Basic information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Order</label>
                    <p class="text-gray-900">{{ $qualityInspection->purchase->purchase_number }}</p>
                </div>

                <div>
                    <label for="inspection_date" class="block text-sm font-medium text-gray-700 mb-1">Inspection date</label>
                    <input type="date" name="inspection_date" id="inspection_date" 
                           class="form-input w-full rounded-md border-gray-300"
                           value="{{ old('inspection_date', $qualityInspection->inspection_date->format('Y-m-d')) }}">
                    @error('inspection_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Inspection items -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Inspection items</h3>
                @foreach ($qualityInspection->items as $index => $item)
                    <div class="border rounded-md p-4 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Procurement Projects</label>
                                <p class="text-gray-900">{{ $item->purchaseItem->product->name }}</p>
                                <input type="hidden" name="items[{{ $index }}][purchase_item_id]" value="{{ $item->purchase_item_id }}">
                            </div>

                            <div>
                                <label for="items[{{ $index }}][inspected_quantity]" class="block text-sm font-medium text-gray-700 mb-1">Number of inspections</label>
                                <input type="number" name="items[{{ $index }}][inspected_quantity]" id="items[{{ $index }}][inspected_quantity]"
                                       class="form-input w-full rounded-md border-gray-300"
                                       value="{{ old("items.{$index}.inspected_quantity", $item->inspected_quantity) }}">
                                @error("items.{$index}.inspected_quantity")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="items[{{ $index }}][passed_quantity]" class="block text-sm font-medium text-gray-700 mb-1">Qualified quantity</label>
                                <input type="number" name="items[{{ $index }}][passed_quantity]" id="items[{{ $index }}][passed_quantity]"
                                       class="form-input w-full rounded-md border-gray-300"
                                       value="{{ old("items.{$index}.passed_quantity", $item->passed_quantity) }}">
                                @error("items.{$index}.passed_quantity")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="items[{{ $index }}][failed_quantity]" class="block text-sm font-medium text-gray-700 mb-1">Unqualified quantity</label>
                                <input type="number" name="items[{{ $index }}][failed_quantity]" id="items[{{ $index }}][failed_quantity]"
                                       class="form-input w-full rounded-md border-gray-300"
                                       value="{{ old("items.{$index}.failed_quantity", $item->failed_quantity) }}">
                                @error("items.{$index}.failed_quantity")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="items[{{ $index }}][defect_description]" class="block text-sm font-medium text-gray-700 mb-1">Defect description</label>
                                <textarea name="items[{{ $index }}][defect_description]" id="items[{{ $index }}][defect_description]"
                                          class="form-textarea w-full rounded-md border-gray-300"
                                          rows="2">{{ old("items.{$index}.defect_description", $item->defect_description) }}</textarea>
                                @error("items.{$index}.defect_description")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Remarks -->
            <div class="mb-6">
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">Remark</label>
                <textarea name="remarks" id="remarks" rows="3" 
                          class="form-textarea w-full rounded-md border-gray-300"
                          placeholder="Enter inspection remarks here">{{ old('remarks', $qualityInspection->remarks) }}</textarea>
                @error('remarks')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Inspection Images -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Inspection Images</label>
                <p class="text-sm text-gray-500 mb-4">Upload images of quality inspection details, defects or product conditions.</p>
                
                <x-quality-inspection-image-uploader
                    :model-id="$qualityInspection->id"
                    model-type="quality_inspections"
                    :max-files="5"
                    :images="$qualityInspection->media"
                />
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    keep
                </button>
            </div>
        </form>
    </div>
@endsection 