@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Product Analytics: {{ $product->name }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.analytics.dashboard') }}">Analytics</a></li>
        <li class="breadcrumb-item active">{{ $product->name }}</li>
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
                        <a href="{{ route('admin.analytics.product', ['productId' => $product->id, 'range' => '7d']) }}" class="btn btn-sm {{ $dates['range'] == '7d' ? 'btn-primary' : 'btn-outline-secondary' }}">7 Days</a>
                        <a href="{{ route('admin.analytics.product', ['productId' => $product->id, 'range' => '30d']) }}" class="btn btn-sm {{ $dates['range'] == '30d' ? 'btn-primary' : 'btn-outline-secondary' }}">30 Days</a>
                        <a href="{{ route('admin.analytics.product', ['productId' => $product->id, 'range' => '90d']) }}" class="btn btn-sm {{ $dates['range'] == '90d' ? 'btn-primary' : 'btn-outline-secondary' }}">90 Days</a>
                        <a href="{{ route('admin.analytics.product', ['productId' => $product->id, 'range' => 'month']) }}" class="btn btn-sm {{ $dates['range'] == 'month' ? 'btn-primary' : 'btn-outline-secondary' }}">This Month</a>
                        <a href="{{ route('admin.analytics.product', ['productId' => $product->id, 'range' => 'year']) }}" class="btn btn-sm {{ $dates['range'] == 'year' ? 'btn-primary' : 'btn-outline-secondary' }}">This Year</a>
                    </div>
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#customDateRange">
                        Custom Range
                    </button>
                </div>
                <div class="card-body collapse" id="customDateRange">
                    <form action="{{ route('admin.analytics.product', $product->id) }}" method="get" class="row">
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

    <div class="row mb-4">
        <div class="col-lg-3">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-box me-1"></i>
                    Product Details
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($product->thumbnail)
                            <img src="{{ asset('storage/' . $product->thumbnail) }}" alt="{{ $product->name }}" class="img-fluid rounded mb-3" style="max-height: 150px;">
                        @else
                            <div class="bg-light rounded mb-3 py-5">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <h5 class="text-primary">{{ $product->name }}</h5>
                        <p class="text-muted">SKU: {{ $product->sku }}</p>
                    </div>
                    <div class="mb-1">
                        <div class="d-flex justify-content-between">
                            <span>Price:</span>
                            <span class="font-weight-bold">¥{{ number_format($product->price, 2) }}</span>
                        </div>
                    </div>
                    <div class="mb-1">
                        <div class="d-flex justify-content-between">
                            <span>Current Stock:</span>
                            <span class="font-weight-bold">{{ $product->stock }}</span>
                        </div>
                    </div>
                    <div class="mb-1">
                        <div class="d-flex justify-content-between">
                            <span>Status:</span>
                            <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-primary">Edit Product</a>
                </div>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="mb-0">{{ number_format($overallStats['total_impressions'] ?? 0) }}</h2>
                                    <div>Total Impressions</div>
                                </div>
                                <div>
                                    <i class="fas fa-eye fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="mb-0">{{ number_format($overallStats['total_clicks'] ?? 0) }}</h2>
                                    <div>Total Clicks</div>
                                </div>
                                <div>
                                    <i class="fas fa-mouse-pointer fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="mb-0">{{ number_format($overallStats['ctr'] ?? 0, 2) }}%</h2>
                                    <div>Click-Through Rate</div>
                                </div>
                                <div>
                                    <i class="fas fa-percentage fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-area me-1"></i>
                    Performance Over Time
                </div>
                <div class="card-body">
                    <canvas id="productChart" width="100%" height="30"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Performance by Section
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
                                @forelse($sectionStats as $section => $data)
                                <tr>
                                    <td>{{ $section ?: 'Unknown' }}</td>
                                    <td>{{ number_format($data['impressions']) }}</td>
                                    <td>{{ number_format($data['clicks']) }}</td>
                                    <td>{{ number_format($data['ctr'], 2) }}%</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No section data available</td>
                                </tr>
                                @endforelse
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
    var ctx = document.getElementById("productChart");
    var productChart = new Chart(ctx, {
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
</script>
@endsection 