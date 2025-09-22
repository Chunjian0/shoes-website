@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Add payment history</h1>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Verification error!</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900">Purchase order information</h3>
                <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Purchase Order Number</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $purchase->purchase_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">supplier</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $purchase->supplier->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total payable</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ number_format($purchase->final_amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Payable amount</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ number_format($purchase->payments->sum('amount'), 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Remaining payable</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ number_format($purchase->final_amount - $purchase->payments->sum('amount'), 2) }}</dd>
                    </div>
                </dl>
            </div>

            <form action="{{ route('purchases.payments.store', $purchase) }}" method="POST">
                @csrf
                <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">

                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-gray-700">Payment date</label>
                        <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700">Payment amount</label>
                        <input type="number" name="amount" id="amount" value="{{ old('amount') }}" step="0.01" min="0.01"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment method</label>
                        <select name="payment_method" id="payment_method" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Please select a payment method</option>
                            @foreach (\App\Enums\PaymentMethod::cases() as $method)
                                <option value="{{ $method->value }}" {{ old('payment_method') == $method->value ? 'selected' : '' }}>
                                    {{ $method->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="reference_number" class="block text-sm font-medium text-gray-700">Reference number</label>
                        <input type="text" name="reference_number" id="reference_number" value="{{ old('reference_number') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mt-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700">Remark</label>
                    <textarea name="notes" id="notes" rows="3" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('purchases.show', $purchase) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        keep
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 