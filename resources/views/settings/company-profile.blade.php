@extends('layouts.settings')

@section('settings-content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page title -->
    <div class="mb-8">
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">Company profile settings</h2>
                <p class="mt-1 text-sm text-gray-500">Improve the company's basic information,This information will be used for invoices and other business documents.</p>
            </div>
        </div>
    </div>

    @if(session('warning'))
        <div class="mb-4 rounded-lg bg-yellow-50 p-4 border border-yellow-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">{{ session('warning') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 border border-green-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- LogoUpload card -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">companyLogo</h3>
                        </div>
                    </div>
                    
                    @php
                        Log::info('Logo Data preparation', [
                            'has_company' => isset($company),
                            'has_logo_path' => isset($company) && !empty($company->logo_path),
                            'logo_path' => $company->logo_path ?? null,
                            'logo_url' => $company->logo_url ?? null,
                        ]);

                        // Get Media Record
                        $logoMedia = null;
                        if ($company && $company->logo_path) {
                            $logoMedia = \App\Models\Media::where('path', $company->logo_path)
                                ->where('collection_name', 'logo')
                                ->first();
                            
                            Log::info('turn up Logo Media Record', [
                                'media_id' => $logoMedia?->id,
                                'media_path' => $logoMedia?->path,
                            ]);
                        }
                    @endphp
                    
                    <div class="mt-1">
                        <x-company-logo-uploader
                            :model-type="$modelType"
                            :model-id="$company->id"
                            :max-files="1"
                            :images="$logoMedia ? [
                                [
                                    'id' => $logoMedia->id,
                                    'name' => 'companyLogo',
                                    'url' => $company->logo_url
                                ]
                            ] : []"
                        />
                    </div>
                </div>
            </div>
        </div>

        <form class="lg:col-span-2 space-y-6" action="{{ route('settings.company-profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Basic information card -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Basic information</h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="company_name" class="block text-sm font-medium text-gray-700 required">Company Name</label>
                            <div class="mt-1">
                                <input type="text" name="company_name" id="company_name" 
                                    value="{{ old('company_name', $company->company_name ?? '') }}" required
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('company_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="registration_number" class="block text-sm font-medium text-gray-700 required">Registration number</label>
                            <div class="mt-1">
                                <input type="text" name="registration_number" id="registration_number" 
                                    value="{{ old('registration_number', $company->registration_number ?? '') }}" required
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('registration_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tax_number" class="block text-sm font-medium text-gray-700 required">Tax number</label>
                            <div class="mt-1">
                                <input type="text" name="tax_number" id="tax_number" 
                                    value="{{ old('tax_number', $company->tax_number ?? '') }}" required
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('tax_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact information card -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">contact information</h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 required">Contact number</label>
                            <div class="mt-1">
                                <input type="tel" name="phone" id="phone" 
                                    value="{{ old('phone', $company->phone ?? '') }}" required
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 required">Email</label>
                            <div class="mt-1">
                                <input type="email" name="email" id="email" 
                                    value="{{ old('email', $company->email ?? '') }}" required
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="website" class="block text-sm font-medium text-gray-700">Company website</label>
                            <div class="mt-1">
                                <input type="url" name="website" id="website" 
                                    value="{{ old('website', $company->website ?? '') }}"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('website')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address information card -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Address information</h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <label for="address" class="block text-sm font-medium text-gray-700 required">Detailed address</label>
                            <div class="mt-1">
                                <textarea name="address" id="address" rows="3" required
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('address', $company->address ?? '') }}</textarea>
                            </div>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="city" class="block text-sm font-medium text-gray-700 required">City</label>
                            <div class="mt-1">
                                <input type="text" name="city" id="city" 
                                    value="{{ old('city', $company->city ?? '') }}" required
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('city')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="state" class="block text-sm font-medium text-gray-700 required">State</label>
                            <div class="mt-1">
                                <input type="text" name="state" id="state" 
                                    value="{{ old('state', $company->state ?? '') }}" required
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('state')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="postal_code" class="block text-sm font-medium text-gray-700 required">post code</label>
                            <div class="mt-1">
                                <input type="text" name="postal_code" id="postal_code" 
                                    value="{{ old('postal_code', $company->postal_code ?? '') }}" required
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('postal_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label for="country" class="block text-sm font-medium text-gray-700 required">nation</label>
                            <div class="mt-1">
                                <input type="text" name="country" id="country" 
                                    value="{{ old('country', $company->country ?? '') }}" required
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('country')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save settings
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.required:after {
    content: " *";
    color: #EF4444;
}
</style>
@endsection 