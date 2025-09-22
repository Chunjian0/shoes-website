<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Inventory details
            </h2>
            <div>
                <a href="{{ route('inventory.checks.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Return to the list
                </a>
                @if ($check->status === 'pending')
                    <form action="{{ route('inventory.checks.complete', $check) }}" method="POST" class="inline-block ml-2">
                        @csrf
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Are you sure you want to complete this inventory?')">
                            Complete inventory
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Inventory order numbers</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $check->check_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Check out dates</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $check->check_date->format('Y-m-d') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Inventory</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $check->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">state</p>
                            <p class="mt-1">
                                @if ($check->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @elseif ($check->status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Completed
                                    </span>
                                @endif
                            </p>
                        </div>
                        @if ($check->notes)
                            <div class="col-span-2">
                                <p class="text-sm font-medium text-gray-500">Remark</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $check->notes }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900">Inventory details</h3>
                        <div class="mt-4">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Product Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            System inventory
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actual inventory
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Number of differences
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Remark
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($check->items as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $item->product->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->system_count }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->actual_count }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if ($item->difference > 0)
                                                    <span class="text-green-600">+{{ $item->difference }}</span>
                                                @elseif ($item->difference < 0)
                                                    <span class="text-red-600">{{ $item->difference }}</span>
                                                @else
                                                    <span>{{ $item->difference }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->notes }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 