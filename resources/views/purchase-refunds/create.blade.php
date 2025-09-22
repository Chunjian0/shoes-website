@extends('layouts.app')

@section('title', 'New refund')

@section('breadcrumb')
    <nav class="text-sm">
        <ol class="list-none p-0 inline-flex">
            <li class="flex items-center">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
                <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
            </li>
            <li class="flex items-center">
                <a href="{{ route('purchase-returns.show', $purchaseReturn) }}" class="text-gray-500 hover:text-gray-700">Purchase return details</a>
                <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
            </li>
            <li>
                <span class="text-gray-700">New refund</span>
            </li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">New refund</h2>
        </div>

        <div class="p-4">
            <!-- Return information -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Return information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <span class="text-sm text-gray-500">Return number</span>
                        <p class="mt-1">{{ $purchaseReturn->return_number }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Return amount</span>
                        <p class="mt-1">{{ number_format($purchaseReturn->total_amount + $purchaseReturn->total_tax_amount, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Refunded amount</span>
                        <p class="mt-1">{{ number_format($purchaseReturn->refunded_amount, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Remaining amount of refund</span>
                        <p class="mt-1">{{ number_format($purchaseReturn->remaining_amount, 2) }}</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('purchase-refunds.store') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="purchase_return_id" value="{{ $purchaseReturn->id }}">

                <!-- Refund information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="refund_date" class="block text-sm font-medium text-gray-700 mb-1">Refund date</label>
                        <input type="date" name="refund_date" id="refund_date" 
                               class="form-input w-full rounded-md border-gray-300"
                               value="{{ old('refund_date', date('Y-m-d')) }}">
                        @error('refund_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Refund amount</label>
                        <input type="number" name="amount" id="amount" 
                               class="form-input w-full rounded-md border-gray-300"
                               value="{{ old('amount') }}"
                               step="0.01"
                               max="{{ $purchaseReturn->remaining_amount }}">
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment method</label>
                        <select name="payment_method" id="payment_method" class="form-select w-full rounded-md border-gray-300">
                            <option value="">Please select the payment method</option>
                            <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank transfer</option>
                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>cash</option>
                            <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        </select>
                        @error('payment_method')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">Reference number</label>
                        <input type="text" name="reference_number" id="reference_number" 
                               class="form-input w-full rounded-md border-gray-300"
                               value="{{ old('reference_number') }}">
                        @error('reference_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Remark -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Remark</label>
                    <textarea name="notes" id="notes" rows="3" 
                              class="form-textarea w-full rounded-md border-gray-300">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        keep
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection 