@extends('layouts.app')

@section('title', 'Quality inspection details')

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
                <span class="text-gray-700">Quality inspection details</span>
            </li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Quality inspection details</h2>
            <div class="flex space-x-2">
                @if ($qualityInspection->status === 'pending')
                    <a href="{{ route('quality-inspections.edit', $qualityInspection) }}" 
                       class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        edit
                    </a>
                    <form action="{{ route('quality-inspections.approve', $qualityInspection) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            pass
                        </button>
                    </form>
                    <form action="{{ route('quality-inspections.reject', $qualityInspection) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                            reject
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="p-4">
            <!-- Basic information -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Basic information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <span class="text-sm text-gray-500">Inspection status</span>
                        <p class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                bg-{{ $qualityInspection->status->color() }}-100 
                                text-{{ $qualityInspection->status->color() }}-800">
                                {{ $qualityInspection->status->label() }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Inspection number</span>
                        <p class="mt-1">{{ $qualityInspection->inspection_number }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Purchase Order Number</span>
                        <p class="mt-1">{{ $qualityInspection->purchase->purchase_number }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Inspection date</span>
                        <p class="mt-1">{{ $qualityInspection->inspection_date->format('Y-m-d') }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Inspectors</span>
                        <p class="mt-1">{{ $qualityInspection->inspector->name }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Pass rate</span>
                        <p class="mt-1">{{ $qualityInspection->pass_rate }}%</p>
                    </div>
                </div>
            </div>

            <!-- Inspection items -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Inspection items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number of inspections</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qualified quantity</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unqualified quantity</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Defect description</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($qualityInspection->items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->purchaseItem->product->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->inspected_quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->passed_quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->failed_quantity }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $item->defect_description }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Remark -->
            @if ($qualityInspection->remarks)
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-700 mb-4">Remark</h3>
                    <p class="text-sm text-gray-600">{{ $qualityInspection->remarks }}</p>
                </div>
            @endif
            
            <!-- Inspection Images -->
            @if($qualityInspection->media && $qualityInspection->media->count() > 0)
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-700 mb-4">Inspection Images</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($qualityInspection->media as $media)
                            <div class="relative group">
                                <div class="aspect-w-1 aspect-h-1 rounded-lg overflow-hidden bg-gray-100 shadow-sm group-hover:shadow-md transition-all duration-200">
                                    <img src="{{ $media->url }}" alt="Inspection Image" class="object-cover w-full h-full transform group-hover:scale-105 transition-transform duration-200">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <div class="absolute bottom-0 left-0 right-0 p-3">
                                            <p class="text-xs text-white truncate">{{ $media->name }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection 