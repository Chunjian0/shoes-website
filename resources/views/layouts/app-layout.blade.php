@extends('layouts.app')

@section('content')
    <div class="py-6">
        @if(isset($header))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        {{ $header }}
                    </div>
                </div>
            </div>
        @endif

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            {{ $slot }}
        </div>
    </div>
@endsection 