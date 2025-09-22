<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Product inventory history - {{ $product->name }}
            </h2>
            <div>
                <a href="{{ url()->previous() }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    return
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-4 gap-4 mb-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Product Name</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $product->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Product Category</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $product->category->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Current inventory</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $product->inventory_count }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Minimum inventory</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $product->min_stock }}</p>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900">Inventory change record</h3>
                        <div class="mt-4">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            time
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            type
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            quantity
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            source
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            batch number
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Validity period
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Library location
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($records as $record)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->created_at->format('Y-m-d H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if ($record->type === 'in')
                                                    <span class="text-green-600">Into the warehouse</span>
                                                @else
                                                    <span class="text-red-600">Out of the warehouse</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if ($record->type === 'in')
                                                    <span class="text-green-600">+{{ $record->quantity }}</span>
                                                @else
                                                    <span class="text-red-600">-{{ $record->quantity }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @switch($record->source_type)
                                                    @case('purchase')
                                                        Purchase and warehouse
                                                        @break
                                                    @case('sale')
                                                        Sales out of warehouse
                                                        @break
                                                    @case('loss')
                                                        Inventory loss
                                                        @break
                                                    @case('check')
                                                        Inventory points
                                                        @break
                                                    @default
                                                        {{ $record->source_type }}
                                                @endswitch
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->batch_number ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->expiry_date?->format('Y-m-d') ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->location ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="mt-4">
                                {{ $records->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 