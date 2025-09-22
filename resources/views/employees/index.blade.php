@extends('layouts.app')

@section('title', 'Employee Management')

@section('breadcrumb')
    <nav class="text-sm">
        <ol class="list-none p-0 inline-flex">
            <li class="flex items-center">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
                <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
            </li>
            <li>
                <span class="text-gray-700">Employee Management</span>
            </li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Employee List</h2>
            @can('create', App\Models\User::class)
                <a href="{{ route('employees.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    Add employees
                </a>
            @endcan
        </div>

        <div class="p-4">
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">avatar</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee number</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Email</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Telephone</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">state</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">operate</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($employees as $employee)
                            <tr>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    @if ($employee->avatar_url)
                                        <img src="{{ $employee->avatar_url }}" alt="{{ $employee->name }}" class="h-8 w-8 rounded-full">
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-500 text-sm">{{ substr($employee->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $employee->employee_id }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $employee->name }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900 hidden md:table-cell">
                                    <span class="truncate block max-w-[200px]" title="{{ $employee->email }}">
                                        {{ $employee->email }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900 hidden lg:table-cell">
                                    {{ $employee->phone }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($employee->roles as $role)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $employee->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $employee->is_active ? 'On-the-job' : 'Leaving' }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex space-x-2">
                                        @can('view', $employee)
                                            <a href="{{ route('employees.show', $employee) }}" class="text-indigo-600 hover:text-indigo-900">Check</a>
                                        @endcan
                                        @can('update', $employee)
                                            <a href="{{ route('employees.edit', $employee) }}" class="text-indigo-600 hover:text-indigo-900">edit</a>
                                        @endcan
                                        @can('delete', $employee)
                                            <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this employee?')">
                                                    delete
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No data yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $employees->links() }}
            </div>
        </div>
    </div>
@endsection 