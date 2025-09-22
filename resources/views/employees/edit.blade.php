@extends('layouts.app')

@section('title', 'Editing the staff')

@section('breadcrumb')
    <nav class="text-sm">
        <ol class="list-none p-0 inline-flex">
            <li class="flex items-center">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
                <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
            </li>
            <li class="flex items-center">
                <a href="{{ route('employees.index') }}" class="text-gray-500 hover:text-gray-700">Employee Management</a>
                <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
            </li>
            <li>
                <span class="text-gray-700">Editing the staff</span>
            </li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Editing the staff</h2>
        </div>

        <form action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data" class="p-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic information -->
                <div class="space-y-6">
                    <h3 class="text-lg font-medium text-gray-900">Basic information</h3>

                    <div>
                        <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee number</label>
                        <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id', $employee->employee_id) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('employee_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $employee->name) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $employee->email) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Telephone</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $employee->phone) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">password</label>
                        <input type="password" name="password" id="password"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Leave blank to indicate that the password is not modified">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Other information -->
                <div class="space-y-6">
                    <h3 class="text-lg font-medium text-gray-900">Other information</h3>

                    <div>
                        <label for="avatar" class="block text-sm font-medium text-gray-700">avatar</label>
                        <div class="mt-1">
                            @php
                                \Log::info('Employee profile data', [
                                    'employee_id' => $employee->id,
                                    'has_avatar' => $employee->avatar ? true : false,
                                    'avatar_id' => $employee->avatar?->id,
                                    'avatar_url' => $employee->avatar_url,
                                    'avatar_data' => $employee->avatar ? [
                                        'id' => $employee->avatar->id,
                                        'name' => 'Employee profile picture',
                                        'url' => $employee->avatar_url
                                    ] : null
                                ]);
                            @endphp
                            
                            <x-employee-avatar-uploader
                                :model-type="'App\\Models\\User'"
                                :model-id="$employee->id"
                                :max-files="1"
                                :images="$employee->avatar ? [[
                                    'id' => $employee->avatar->id,
                                    'name' => 'Employee profile picture',
                                    'url' => $employee->avatar_url
                                ]] : []"
                            />
                        </div>
                        @error('avatar')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="roles" class="block text-sm font-medium text-gray-700">Role</label>
                        <div class="mt-1 space-y-2">
                            @foreach($roles as $role)
                                <div class="flex items-center">
                                    <input type="checkbox" name="roles[]" id="role_{{ $role->id }}" value="{{ $role->id }}"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                        {{ in_array($role->id, old('roles', $employee->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
                                    <label for="role_{{ $role->id }}" class="ml-2 text-sm text-gray-700">{{ $role->name }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('roles')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="is_active" class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                {{ old('is_active', $employee->is_active) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">On-the-job</span>
                        </label>
                        @error('is_active')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end">
                <a href="{{ route('employees.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    keep
                </button>
            </div>
        </form>
    </div>
@endsection 