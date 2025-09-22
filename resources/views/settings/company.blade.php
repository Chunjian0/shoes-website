@extends('settings.index')

@section('settings-content')
<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Company settings</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">Basic information and invoice settings for managing the company</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <form action="{{ route('settings.company.update') }}" method="POST" class="divide-y divide-gray-200">
        @csrf
        @method('PUT')

        <div class="px-4 py-5 sm:p-6">
            <!-- company Logo -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700">Company Logo</label>
                @php
                    $logoId = $settings['company_logo'] ?? null;
                    $logo = $logoId ? \App\Models\Media::find($logoId) : null;
                    $images = $logo ? [['id' => $logo->id, 'url' => $logo->url, 'name' => $logo->name]] : [];
                @endphp
                <x-company-logo-uploader :images="$images" />
            </div>

            <!-- Company Name -->
            <div class="mb-6">
                <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name</label>
                <input type="text" name="company_name" id="company_name" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="{{ old('company_name', $settings['company_name'] ?? '') }}" required>
                @error('company_name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Company Address -->
            <div class="mb-6">
                <label for="company_address" class="block text-sm font-medium text-gray-700">Company Address</label>
                <textarea name="company_address" id="company_address" rows="3" 
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                          required>{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                @error('company_address')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Contact number -->
            <div class="mb-6">
                <label for="company_phone" class="block text-sm font-medium text-gray-700">Contact number</label>
                <input type="text" name="company_phone" id="company_phone" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="{{ old('company_phone', $settings['company_phone'] ?? '') }}" required>
                @error('company_phone')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-6">
                <label for="company_email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="company_email" id="company_email" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="{{ old('company_email', $settings['company_email'] ?? '') }}" required>
                @error('company_email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tax number -->
            <div class="mb-6">
                <label for="company_tax_number" class="block text-sm font-medium text-gray-700">Tax number</label>
                <input type="text" name="company_tax_number" id="company_tax_number" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="{{ old('company_tax_number', $settings['company_tax_number'] ?? '') }}" required>
                @error('company_tax_number')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Invoice prefix -->
            <div class="mb-6">
                <label for="invoice_prefix" class="block text-sm font-medium text-gray-700">Invoice prefix</label>
                <input type="text" name="invoice_prefix" id="invoice_prefix" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       value="{{ old('invoice_prefix', $settings['invoice_prefix'] ?? '') }}">
                <p class="mt-2 text-sm text-gray-500">Optional.Prefixes used to generate invoice numbers, for example:INV-</p>
                @error('invoice_prefix')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Invoice footer -->
            <div class="mb-6">
                <label for="invoice_footer" class="block text-sm font-medium text-gray-700">Invoice footer</label>
                <textarea name="invoice_footer" id="invoice_footer" rows="3" 
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('invoice_footer', $settings['invoice_footer'] ?? '') }}</textarea>
                <p class="mt-2 text-sm text-gray-500">Optional.Text displayed at the bottom of the invoice, for example: Thank you for your patronage!</p>
                @error('invoice_footer')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Save settings
            </button>
        </div>
    </form>
</div>
@endsection 