@props([
    'stats' => [],
    'chart_data' => [],
    'period' => 'week'
])

<div class="cart-analytics bg-white rounded-lg border border-gray-200 shadow-sm mb-6">
    <div class="p-4 border-b border-gray-200">
        <h4 class="text-lg font-medium text-gray-900">购物车分析</h4>
    </div>
    
    {{-- 统计卡片 --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4">
        {{-- 活跃购物车 --}}
        <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
            <div class="flex flex-col">
                <span class="text-xs font-medium text-blue-600 uppercase tracking-wider">活跃购物车</span>
                <span class="mt-1 text-2xl font-bold text-blue-800">{{ $stats['active_carts'] ?? 0 }}</span>
                
                @if(isset($stats['active_carts_change']))
                    <span class="mt-1 flex items-center text-xs font-medium {{ $stats['active_carts_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        @if($stats['active_carts_change'] >= 0)
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                            </svg>
                            +{{ $stats['active_carts_change'] }}%
                        @else
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                            </svg>
                            {{ $stats['active_carts_change'] }}%
                        @endif
                    </span>
                @endif
            </div>
        </div>
        
        {{-- 废弃购物车 --}}
        <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-100">
            <div class="flex flex-col">
                <span class="text-xs font-medium text-yellow-600 uppercase tracking-wider">废弃购物车</span>
                <span class="mt-1 text-2xl font-bold text-yellow-800">{{ $stats['abandoned_carts'] ?? 0 }}</span>
                
                @if(isset($stats['abandoned_rate']))
                    <span class="mt-1 text-xs text-yellow-700">
                        废弃率: {{ $stats['abandoned_rate'] }}%
                    </span>
                @endif
            </div>
        </div>
        
        {{-- 平均购物车价值 --}}
        <div class="bg-green-50 rounded-lg p-3 border border-green-100">
            <div class="flex flex-col">
                <span class="text-xs font-medium text-green-600 uppercase tracking-wider">平均购物车价值</span>
                <span class="mt-1 text-2xl font-bold text-green-800">${{ number_format($stats['avg_cart_value'] ?? 0, 2) }}</span>
                
                @if(isset($stats['avg_value_change']))
                    <span class="mt-1 flex items-center text-xs font-medium {{ $stats['avg_value_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        @if($stats['avg_value_change'] >= 0)
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                            </svg>
                            +{{ $stats['avg_value_change'] }}%
                        @else
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                            </svg>
                            {{ $stats['avg_value_change'] }}%
                        @endif
                    </span>
                @endif
            </div>
        </div>
        
        {{-- 平均商品数 --}}
        <div class="bg-purple-50 rounded-lg p-3 border border-purple-100">
            <div class="flex flex-col">
                <span class="text-xs font-medium text-purple-600 uppercase tracking-wider">平均商品数</span>
                <span class="mt-1 text-2xl font-bold text-purple-800">{{ number_format($stats['avg_items'] ?? 0, 1) }}</span>
                
                @if(isset($stats['popular_item']))
                    <span class="mt-1 text-xs text-purple-700">
                        热门: {{ $stats['popular_item'] }}
                    </span>
                @endif
            </div>
        </div>
    </div>
    
    {{-- 图表区域 --}}
    <div class="p-4 border-t border-gray-200">
        <div class="flex justify-between mb-4">
            <h5 class="text-sm font-medium text-gray-700">购物车趋势</h5>
            
            <div class="flex space-x-2">
                <button type="button" class="period-btn px-2 py-1 text-xs font-medium rounded {{ $period == 'week' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}" data-period="week">
                    周
                </button>
                <button type="button" class="period-btn px-2 py-1 text-xs font-medium rounded {{ $period == 'month' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}" data-period="month">
                    月
                </button>
                <button type="button" class="period-btn px-2 py-1 text-xs font-medium rounded {{ $period == 'year' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}" data-period="year">
                    年
                </button>
            </div>
        </div>
        
        <div class="w-full h-60" id="cart-trend-chart"></div>
    </div>
    
    {{-- 热门商品 --}}
    <div class="p-4 border-t border-gray-200">
        <h5 class="text-sm font-medium text-gray-700 mb-3">热门购物车商品</h5>
        
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            商品
                        </th>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            出现频率
                        </th>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            平均数量
                        </th>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            转化率
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($stats['popular_items'] ?? [] as $item)
                        <tr>
                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $item['name'] }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['frequency'] }}%
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($item['avg_quantity'], 1) }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                {{ $item['conversion_rate'] }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-4 text-sm text-gray-500 text-center">
                                暂无数据
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- 废弃购物车分析 --}}
    <div class="p-4 border-t border-gray-200">
        <h5 class="text-sm font-medium text-gray-700 mb-3">废弃购物车分析</h5>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                <h6 class="text-xs font-medium text-gray-600 mb-2">废弃原因</h6>
                <div class="space-y-2">
                    @forelse($stats['abandon_reasons'] ?? [] as $reason => $percentage)
                        <div>
                            <div class="flex justify-between text-xs text-gray-700">
                                <span>{{ $reason }}</span>
                                <span>{{ $percentage }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">暂无数据</p>
                    @endforelse
                </div>
            </div>
            
            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                <h6 class="text-xs font-medium text-gray-600 mb-2">废弃时间点</h6>
                <div class="space-y-2">
                    @forelse($stats['abandon_stages'] ?? [] as $stage => $percentage)
                        <div>
                            <div class="flex justify-between text-xs text-gray-700">
                                <span>{{ $stage }}</span>
                                <span>{{ $percentage }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                <div class="bg-yellow-500 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">暂无数据</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@once
    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.3/dist/apexcharts.min.css">
    @endpush
    
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.3/dist/apexcharts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 初始化图表
            initCartTrendChart();
            
            // 时间段选择
            const periodButtons = document.querySelectorAll('.period-btn');
            
            periodButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const period = this.dataset.period;
                    
                    // 更新按钮样式
                    periodButtons.forEach(btn => {
                        btn.classList.remove('bg-blue-100', 'text-blue-800');
                        btn.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                    });
                    
                    this.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                    this.classList.add('bg-blue-100', 'text-blue-800');
                    
                    // 获取新数据并更新图表
                    fetchChartData(period);
                });
            });
        });
        
        /**
         * 初始化购物车趋势图表
         */
        function initCartTrendChart() {
            const chartData = @json($chart_data);
            
            if(!chartData || !chartData.labels || chartData.labels.length === 0) {
                document.getElementById('cart-trend-chart').innerHTML = '<div class="flex h-full items-center justify-center text-gray-500">暂无图表数据</div>';
                return;
            }
            
            const options = {
                series: [
                    {
                        name: '活跃购物车',
                        data: chartData.active_carts || []
                    },
                    {
                        name: '废弃购物车',
                        data: chartData.abandoned_carts || []
                    },
                    {
                        name: '平均价值',
                        data: chartData.avg_value || []
                    }
                ],
                chart: {
                    height: 240,
                    type: 'line',
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: false
                    }
                },
                colors: ['#3B82F6', '#F59E0B', '#10B981'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    },
                },
                xaxis: {
                    categories: chartData.labels || [],
                    labels: {
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontSize: '12px'
                },
                markers: {
                    size: 4,
                    hover: {
                        size: 6
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false
                }
            };

            const chart = new ApexCharts(document.getElementById('cart-trend-chart'), options);
            chart.render();
            
            // 保存chart实例以便后续更新
            window.cartTrendChart = chart;
        }
        
        /**
         * 获取图表数据
         */
        function fetchChartData(period) {
            fetch(`/api/cart/analytics?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    if(window.cartTrendChart) {
                        // 更新图表数据
                        window.cartTrendChart.updateOptions({
                            series: [
                                {
                                    name: '活跃购物车',
                                    data: data.chart_data.active_carts || []
                                },
                                {
                                    name: '废弃购物车',
                                    data: data.chart_data.abandoned_carts || []
                                },
                                {
                                    name: '平均价值',
                                    data: data.chart_data.avg_value || []
                                }
                            ],
                            xaxis: {
                                categories: data.chart_data.labels || []
                            }
                        });
                        
                        // 更新统计卡片数据
                        updateStatCards(data.stats);
                    }
                })
                .catch(error => {
                    console.error('Error fetching chart data:', error);
                });
        }
        
        /**
         * 更新统计卡片
         */
        function updateStatCards(stats) {
            // 可以根据实际需要更新DOM中的统计数据
            // 这里只是一个示例
            const elements = {
                active_carts: document.querySelector('.text-blue-800'),
                abandoned_carts: document.querySelector('.text-yellow-800'),
                avg_cart_value: document.querySelector('.text-green-800'),
                avg_items: document.querySelector('.text-purple-800')
            };
            
            if(stats.active_carts !== undefined && elements.active_carts) {
                elements.active_carts.textContent = stats.active_carts;
            }
            
            if(stats.abandoned_carts !== undefined && elements.abandoned_carts) {
                elements.abandoned_carts.textContent = stats.abandoned_carts;
            }
            
            if(stats.avg_cart_value !== undefined && elements.avg_cart_value) {
                elements.avg_cart_value.textContent = '$' + parseFloat(stats.avg_cart_value).toFixed(2);
            }
            
            if(stats.avg_items !== undefined && elements.avg_items) {
                elements.avg_items.textContent = parseFloat(stats.avg_items).toFixed(1);
            }
            
            // 更新其他元素...
        }
    </script>
    @endpush
@endonce 