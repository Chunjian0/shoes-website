@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Product Analytics Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Product Analytics</li>
    </ol>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-calendar me-1"></i>
                        Date Range
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('admin.analytics.dashboard', ['range' => '7d']) }}" class="btn btn-sm {{ $dates['range'] == '7d' ? 'btn-primary' : 'btn-outline-secondary' }}">7 Days</a>
                        <a href="{{ route('admin.analytics.dashboard', ['range' => '30d']) }}" class="btn btn-sm {{ $dates['range'] == '30d' ? 'btn-primary' : 'btn-outline-secondary' }}">30 Days</a>
                        <a href="{{ route('admin.analytics.dashboard', ['range' => '90d']) }}" class="btn btn-sm {{ $dates['range'] == '90d' ? 'btn-primary' : 'btn-outline-secondary' }}">90 Days</a>
                        <a href="{{ route('admin.analytics.dashboard', ['range' => 'month']) }}" class="btn btn-sm {{ $dates['range'] == 'month' ? 'btn-primary' : 'btn-outline-secondary' }}">This Month</a>
                        <a href="{{ route('admin.analytics.dashboard', ['range' => 'year']) }}" class="btn btn-sm {{ $dates['range'] == 'year' ? 'btn-primary' : 'btn-outline-secondary' }}">This Year</a>
                    </div>
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#customDateRange">
                        Custom Range
                    </button>
                </div>
                <div class="card-body collapse" id="customDateRange">
                    <form action="{{ route('admin.analytics.dashboard') }}" method="get" class="row">
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $dates['start'] }}">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $dates['end'] }}">
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Apply</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">{{ number_format($overallStats->total_impressions ?? 0) }}</h2>
                            <div>Total Impressions</div>
                        </div>
                        <div>
                            <i class="fas fa-eye fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span>Displayed to users</span>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">{{ number_format($overallStats->total_clicks ?? 0) }}</h2>
                            <div>Total Clicks</div>
                        </div>
                        <div>
                            <i class="fas fa-mouse-pointer fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span>User interactions</span>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">{{ number_format($overallStats->ctr ?? 0, 2) }}%</h2>
                            <div>Click-Through Rate</div>
                        </div>
                        <div>
                            <i class="fas fa-percentage fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span>Engagement level</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-area me-1"></i>
                    Daily Traffic Overview
                </div>
                <div class="card-body">
                    <canvas id="trafficChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Device Distribution
                </div>
                <div class="card-body">
                    <canvas id="deviceChart" width="100%" height="50"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Section Performance
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Section</th>
                                    <th>Impressions</th>
                                    <th>Clicks</th>
                                    <th>CTR</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sectionStats as $section)
                                <tr>
                                    <td>{{ $section->section_type ?? 'Unknown' }}</td>
                                    <td>{{ number_format($section->impressions) }}</td>
                                    <td>{{ number_format($section->clicks) }}</td>
                                    <td>{{ number_format($section->ctr, 2) }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-fire me-1"></i>
                    Popular Products
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Impressions</th>
                                    <th>Clicks</th>
                                    <th>CTR</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($popularProducts as $productStat)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.analytics.product', $productStat->product_id) }}">
                                            {{ $productStat->product->name ?? 'Unknown Product' }}
                                        </a>
                                    </td>
                                    <td>{{ number_format($productStat->total_impressions) }}</td>
                                    <td>{{ number_format($productStat->total_clicks) }}</td>
                                    <td>{{ number_format($productStat->ctr, 2) }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.8.0/dist/chart.min.js"></script>
<script>
    // Traffic Chart
    var ctx = document.getElementById("trafficChart");
    var trafficChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['dates'] ?? []) !!},
            datasets: [{
                label: "Impressions",
                lineTension: 0.3,
                backgroundColor: "rgba(2,117,216,0.2)",
                borderColor: "rgba(2,117,216,1)",
                pointRadius: 5,
                pointBackgroundColor: "rgba(2,117,216,1)",
                pointBorderColor: "rgba(255,255,255,0.8)",
                pointHoverRadius: 5,
                pointHoverBackgroundColor: "rgba(2,117,216,1)",
                pointHitRadius: 50,
                pointBorderWidth: 2,
                data: {!! json_encode($chartData['impressions'] ?? []) !!},
                yAxisID: 'y',
            },
            {
                label: "Clicks",
                lineTension: 0.3,
                backgroundColor: "rgba(40,167,69,0.2)",
                borderColor: "rgba(40,167,69,1)",
                pointRadius: 5,
                pointBackgroundColor: "rgba(40,167,69,1)",
                pointBorderColor: "rgba(255,255,255,0.8)",
                pointHoverRadius: 5,
                pointHoverBackgroundColor: "rgba(40,167,69,1)",
                pointHitRadius: 50,
                pointBorderWidth: 2,
                data: {!! json_encode($chartData['clicks'] ?? []) !!},
                yAxisID: 'y1',
            }],
        },
        options: {
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    ticks: {
                        min: 0,
                        maxTicksLimit: 5
                    },
                    grid: {
                        color: "rgba(0, 0, 0, .125)",
                    },
                    position: 'left',
                },
                y1: {
                    position: 'right',
                    ticks: {
                        min: 0,
                        maxTicksLimit: 5
                    },
                    grid: {
                        display: false,
                    },
                }
            },
            plugins: {
                legend: {
                    display: true
                }
            }
        }
    });

    // Device Chart
    var deviceCtx = document.getElementById("deviceChart");
    var deviceData = {!! json_encode($deviceStats->pluck('count')->toArray() ?? []) !!};
    var deviceLabels = {!! json_encode($deviceStats->pluck('device_type')->toArray() ?? []) !!};
    
    var deviceChart = new Chart(deviceCtx, {
        type: 'doughnut',
        data: {
            labels: deviceLabels,
            datasets: [{
                data: deviceData,
                backgroundColor: ['#007bff', '#28a745', '#ffc107'],
                hoverBackgroundColor: ['#0056b3', '#218838', '#e0a800'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            },
            cutout: '70%',
        },
    });
</script>
@endsection 