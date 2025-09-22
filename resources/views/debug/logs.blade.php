<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Debug Logs') }} ({{ $today }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">今日日志 ({{ count($logEntries) }} 条)</h3>
                        <p class="text-sm text-gray-500">最新日志显示在最上方</p>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button id="refresh-logs" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            刷新
                        </button>
                        
                        <div class="relative">
                            <input type="text" id="log-search" placeholder="搜索日志..." class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm py-2 px-4 block w-full">
                        </div>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <div id="log-entries" class="min-w-full">
                            @forelse($logEntries as $index => $entry)
                                <div class="log-entry p-4 {{ $index % 2 === 0 ? 'bg-gray-50' : 'bg-white' }} hover:bg-blue-50 border-b border-gray-200 transition-colors duration-150">
                                    <div class="font-mono text-xs whitespace-pre-wrap">{{ $entry }}</div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-gray-500">
                                    没有找到日志条目
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const refreshButton = document.getElementById('refresh-logs');
            const searchInput = document.getElementById('log-search');
            const logEntries = document.querySelectorAll('.log-entry');
            
            // 刷新日志
            refreshButton.addEventListener('click', function() {
                window.location.reload();
            });
            
            // 搜索功能
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                logEntries.forEach(entry => {
                    const text = entry.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        entry.style.display = '';
                        
                        // 高亮匹配文本
                        if (searchTerm.length > 0) {
                            const originalHtml = entry.innerHTML;
                            const highlightedHtml = originalHtml.replace(
                                new RegExp(searchTerm, 'gi'), 
                                match => `<mark class="bg-yellow-200 px-1 rounded">${match}</mark>`
                            );
                            entry.innerHTML = highlightedHtml;
                        }
                    } else {
                        entry.style.display = 'none';
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout> 