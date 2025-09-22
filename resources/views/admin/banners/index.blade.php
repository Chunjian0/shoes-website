@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Banner Management</h1>
                    <a href="{{ route('admin.banners.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add New Banner
                    </a>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <div class="bg-white shadow-md rounded my-6">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="w-20 py-3 px-4 uppercase font-semibold text-sm text-gray-600 border-b">Order</th>
                                <th class="w-32 py-3 px-4 uppercase font-semibold text-sm text-gray-600 border-b">Image</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-gray-600 border-b">Title/Subtitle</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-gray-600 border-b">Button</th>
                                <th class="w-24 py-3 px-4 uppercase font-semibold text-sm text-gray-600 border-b">Status</th>
                                <th class="w-48 py-3 px-4 uppercase font-semibold text-sm text-gray-600 border-b">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="banner-list" class="banner-sortable">
                            @forelse($banners as $banner)
                                <tr data-id="{{ $banner->id }}" class="hover:bg-gray-50 banner-item">
                                    <td class="py-3 px-4 border-b cursor-move">
                                        <div class="flex items-center">
                                            <span class="mr-2 text-gray-500">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                                </svg>
                                            </span>
                                            <span>{{ $banner->order }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 border-b">
                                        @if($banner->media)
                                            <img src="{{ $banner->getImageUrl() }}" alt="{{ $banner->title }}" class="w-24 h-auto object-cover rounded">
                                        @else
                                            <div class="w-24 h-16 bg-gray-200 flex items-center justify-center rounded">
                                                <span class="text-gray-500 text-xs">No Image</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 border-b">
                                        <div class="font-medium text-gray-900">{{ $banner->title }}</div>
                                        <div class="text-gray-500 text-sm">{{ $banner->subtitle }}</div>
                                    </td>
                                    <td class="py-3 px-4 border-b">
                                        @if($banner->button_text)
                                            <div class="font-medium text-gray-900">{{ $banner->button_text }}</div>
                                            <div class="text-gray-500 text-sm truncate max-w-xs">{{ $banner->button_link }}</div>
                                        @else
                                            <span class="text-gray-500 text-sm">No button</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 border-b">
                                        <button 
                                            class="toggle-status px-2 py-1 text-xs rounded-full {{ $banner->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}"
                                            data-id="{{ $banner->id }}"
                                            data-active="{{ $banner->is_active ? 'true' : 'false' }}"
                                        >
                                            {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>
                                    <td class="py-3 px-4 border-b text-center">
                                        <div class="flex space-x-2 justify-center">
                                            <a href="{{ route('admin.banners.edit', $banner) }}" class="text-blue-500 hover:text-blue-700">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this banner?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 px-4 border-b text-center text-gray-500">
                                        No banners found. <a href="{{ route('admin.banners.create') }}" class="text-blue-500 hover:underline">Create your first banner</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 初始化拖拽排序
        const bannerList = document.getElementById('banner-list');
        if (bannerList && bannerList.children.length > 1) {
            new Sortable(bannerList, {
                animation: 150,
                handle: '.cursor-move',
                ghostClass: 'bg-blue-100',
                onEnd: function() {
                    saveNewOrder();
                }
            });
        }
        
        // 切换状态按钮
        document.querySelectorAll('.toggle-status').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const active = this.dataset.active === 'true';
                
                fetch(`/admin/banners/${id}/toggle-active`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 更新按钮状态
                        this.dataset.active = data.is_active ? 'true' : 'false';
                        this.textContent = data.is_active ? 'Active' : 'Inactive';
                        this.className = `toggle-status px-2 py-1 text-xs rounded-full ${data.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
                        
                        // 显示成功消息
                        const toast = document.createElement('div');
                        toast.className = 'fixed top-4 right-4 p-4 bg-green-500 text-white rounded shadow-lg z-50';
                        toast.textContent = data.message;
                        document.body.appendChild(toast);
                        
                        setTimeout(() => {
                            toast.remove();
                        }, 3000);
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the banner status.');
                });
            });
        });
        
        // 保存新的排序
        function saveNewOrder() {
            const items = document.querySelectorAll('.banner-item');
            const ids = Array.from(items).map(item => item.dataset.id);
            
            fetch('/admin/banners/update-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ ids: ids })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 更新显示的排序号
                    items.forEach((item, index) => {
                        const orderCell = item.querySelector('td:first-child span:last-child');
                        if (orderCell) {
                            orderCell.textContent = index + 1;
                        }
                    });
                    
                    // 显示成功消息
                    const toast = document.createElement('div');
                    toast.className = 'fixed top-4 right-4 p-4 bg-green-500 text-white rounded shadow-lg z-50';
                    toast.textContent = data.message;
                    document.body.appendChild(toast);
                    
                    setTimeout(() => {
                        toast.remove();
                    }, 3000);
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the banner order.');
            });
        }
    });
</script>
@endpush
@endsection 