@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Payment record details</h1>
        <div class="space-x-2">
            @can('delete', $payment)
                <form action="{{ route('purchases.payments.destroy', [$purchase, $payment]) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger" onclick="return confirm('Are you sure you want to delete this payment history?')">
                        Delete records
                    </button>
                </form>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic information</h3>
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Payment order number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $payment->payment_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Purchase Order Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $purchase->purchase_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">supplier</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $purchase->supplier->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Payment date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $payment->payment_date->format('Y-m-d') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Payment amount</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ number_format($payment->amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Payment method</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $payment->payment_method->label() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Reference number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $payment->reference_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Creation time</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $payment->created_at->format('Y-m-d H:i') }}</dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Purchase order information</h3>
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Purchase status</dt>
                            <dd class="mt-1">
                                <span class="status-badge status-{{ $purchase->purchase_status->value }}">
                                    {{ $purchase->purchase_status->label() }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Payment Status</dt>
                            <dd class="mt-1">
                                <span class="status-badge status-{{ $purchase->payment_status->value }}">
                                    {{ $purchase->payment_status->label() }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Order amount</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ number_format($purchase->final_amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Payable amount</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ number_format($purchase->payments->sum('amount'), 2) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if ($payment->notes)
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Remark</h3>
                    <div class="bg-gray-50 rounded p-4">
                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $payment->notes }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('purchases.show', $purchase) }}" class="btn-secondary">
            Return to purchase order
        </a>
    </div>
</div>
@endsection 