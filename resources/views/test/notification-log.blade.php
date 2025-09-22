@extends('layouts.app')

@section('title', 'Test Notification Log')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-900 mb-4">Test Email Notification Logging</h1>
                <p class="mb-6 text-gray-600">Use this form to send a test email that will be logged in the notification history.</p>
                
                <form id="test-email-form" class="space-y-6">
                    <div>
                        <label for="recipient" class="block text-sm font-medium text-gray-700">Recipient Email</label>
                        <input type="email" name="recipient" id="recipient" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    </div>
                    
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">Notification Type</label>
                        <select name="type" id="type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="test_mail">Test Mail</option>
                            <option value="system_alert">System Alert</option>
                            <option value="product_created">Product Created</option>
                            <option value="inventory_alert">Inventory Alert</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700">Email Content</label>
                        <textarea name="content" id="content" rows="5" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">This is a test email message from the system.</textarea>
                    </div>
                    
                    <div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Send Test Email
                        </button>
                    </div>
                </form>
                
                <div id="result" class="mt-6 p-4 hidden"></div>
                
                <div class="mt-8">
                    <a href="{{ route('notification-history') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        View Notification History
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('test-email-form');
    const result = document.getElementById('result');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        result.innerHTML = `
            <div class="flex items-center text-blue-600">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Sending test email...
            </div>
        `;
        result.classList.remove('hidden');
        result.classList.remove('bg-green-50', 'text-green-700', 'bg-red-50', 'text-red-700');
        result.classList.add('bg-blue-50', 'text-blue-700');
        
        // Collect form data
        const formData = new FormData(form);
        
        // Send AJAX request
        fetch('{{ route("test.email") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            result.classList.remove('bg-blue-50', 'text-blue-700');
            
            if (data.success) {
                result.classList.add('bg-green-50', 'text-green-700');
                result.innerHTML = `
                    <div class="flex">
                        <svg class="h-5 w-5 text-green-400 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-medium">Success!</p>
                            <p class="text-sm">${data.message}</p>
                            <p class="text-sm mt-2">Check the <a href="{{ route('notification-history') }}" class="underline">notification history</a> to see the logged email.</p>
                        </div>
                    </div>
                `;
            } else {
                result.classList.add('bg-red-50', 'text-red-700');
                result.innerHTML = `
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-medium">Error!</p>
                            <p class="text-sm">${data.message}</p>
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            result.classList.remove('bg-blue-50', 'text-blue-700');
            result.classList.add('bg-red-50', 'text-red-700');
            result.innerHTML = `
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="font-medium">Error!</p>
                        <p class="text-sm">An error occurred while sending the test email.</p>
                    </div>
                </div>
            `;
            console.error('Error:', error);
        });
    });
});
</script>
@endpush 