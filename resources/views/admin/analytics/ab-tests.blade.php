@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">A/B Testing Management</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.analytics.dashboard') }}">Analytics</a></li>
        <li class="breadcrumb-item active">A/B Tests</li>
    </ol>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-flask me-1"></i>
                        A/B Tests
                    </div>
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#createTestModal">
                        <i class="fas fa-plus me-1"></i> New Test
                    </button>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Variants</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Impressions</th>
                                    <th>Clicks</th>
                                    <th>CTR</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($abTests as $test)
                                <tr>
                                    <td>
                                        {{ $test->name }}
                                        <div class="small text-muted">{{ $test->description }}</div>
                                    </td>
                                    <td>
                                        @if($test->is_active && (!$test->end_date || $test->end_date >= now()))
                                            <span class="badge bg-success">Active</span>
                                        @elseif($test->is_active && $test->end_date < now())
                                            <span class="badge bg-secondary">Completed</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(is_array($test->variants))
                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#variantsModal{{ $test->id }}">
                                                {{ count($test->variants) }} variants
                                            </button>
                                        @else
                                            No variants
                                        @endif
                                    </td>
                                    <td>{{ $test->start_date->format('Y-m-d') }}</td>
                                    <td>{{ $test->end_date ? $test->end_date->format('Y-m-d') : 'Ongoing' }}</td>
                                    <td>{{ number_format($test->results->sum('impressions')) }}</td>
                                    <td>{{ number_format($test->results->sum('clicks')) }}</td>
                                    <td>
                                        @php
                                            $totalImpressions = $test->results->sum('impressions');
                                            $totalClicks = $test->results->sum('clicks');
                                            $ctr = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0;
                                        @endphp
                                        {{ number_format($ctr, 2) }}%
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#resultsModal{{ $test->id }}">
                                                Results
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editTestModal{{ $test->id }}">
                                                Edit
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Variants Modal -->
                                <div class="modal fade" id="variantsModal{{ $test->id }}" tabindex="-1" aria-labelledby="variantsModalLabel{{ $test->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="variantsModalLabel{{ $test->id }}">Test Variants: {{ $test->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Variant</th>
                                                                <th>Weight</th>
                                                                <th>Description</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($test->variants as $variant)
                                                            <tr>
                                                                <td>{{ $variant['name'] ?? $variant }}</td>
                                                                <td>{{ $variant['weight'] ?? 'N/A' }}</td>
                                                                <td>{{ $variant['description'] ?? 'N/A' }}</td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Results Modal -->
                                <div class="modal fade" id="resultsModal{{ $test->id }}" tabindex="-1" aria-labelledby="resultsModalLabel{{ $test->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="resultsModalLabel{{ $test->id }}">Test Results: {{ $test->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-4">
                                                    <div class="col-md-6">
                                                        <canvas id="resultsChart{{ $test->id }}" width="100%" height="200"></canvas>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <canvas id="ctrChart{{ $test->id }}" width="100%" height="200"></canvas>
                                                    </div>
                                                </div>
                                                
                                                <div class="table-responsive">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Variant</th>
                                                                <th>Impressions</th>
                                                                <th>Clicks</th>
                                                                <th>CTR</th>
                                                                <th>Conversions</th>
                                                                <th>Conversion Rate</th>
                                                                <th>Revenue</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($test->results as $result)
                                                            <tr>
                                                                <td>{{ $result->variant }}</td>
                                                                <td>{{ number_format($result->impressions) }}</td>
                                                                <td>{{ number_format($result->clicks) }}</td>
                                                                <td>{{ number_format($result->getCtr(), 2) }}%</td>
                                                                <td>{{ number_format($result->conversions) }}</td>
                                                                <td>{{ number_format($result->getConversionRate(), 2) }}%</td>
                                                                <td>¥{{ number_format($result->revenue, 2) }}</td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No A/B tests found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $abTests->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Test Modal -->
<div class="modal fade" id="createTestModal" tabindex="-1" aria-labelledby="createTestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createTestModalLabel">Create New A/B Test</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.analytics.ab-tests.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Test Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date (Optional)</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">
                            Active
                        </label>
                    </div>
                    
                    <h6 class="mt-4 mb-3">Test Variants</h6>
                    <div id="variants-container">
                        <div class="variant-row row mb-2">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="variants[0][name]" placeholder="Variant Name" required>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="variants[0][weight]" placeholder="Weight" value="1" min="1" required>
                            </div>
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="variants[0][description]" placeholder="Description">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger remove-variant-btn" disabled>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-variant-btn">
                        <i class="fas fa-plus me-1"></i> Add Variant
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Test</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.8.0/dist/chart.min.js"></script>
<script>
    // Initialize charts for each A/B test
    @foreach($abTests as $test)
        // Prepare data
        var labels{{ $test->id }} = [];
        var impressions{{ $test->id }} = [];
        var clicks{{ $test->id }} = [];
        var ctr{{ $test->id }} = [];

        @foreach($test->results as $result)
            labels{{ $test->id }}.push("{{ $result->variant }}");
            impressions{{ $test->id }}.push({{ $result->impressions }});
            clicks{{ $test->id }}.push({{ $result->clicks }});
            ctr{{ $test->id }}.push({{ $result->getCtr() }});
        @endforeach

        // Results chart
        var resultsCtx{{ $test->id }} = document.getElementById("resultsChart{{ $test->id }}");
        var resultsChart{{ $test->id }} = new Chart(resultsCtx{{ $test->id }}, {
            type: 'bar',
            data: {
                labels: labels{{ $test->id }},
                datasets: [
                    {
                        label: 'Impressions',
                        backgroundColor: 'rgba(2,117,216,0.5)',
                        borderColor: 'rgba(2,117,216,1)',
                        borderWidth: 1,
                        data: impressions{{ $test->id }},
                    },
                    {
                        label: 'Clicks',
                        backgroundColor: 'rgba(40,167,69,0.5)',
                        borderColor: 'rgba(40,167,69,1)',
                        borderWidth: 1,
                        data: clicks{{ $test->id }},
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Impressions & Clicks'
                    }
                }
            }
        });

        // CTR chart
        var ctrCtx{{ $test->id }} = document.getElementById("ctrChart{{ $test->id }}");
        var ctrChart{{ $test->id }} = new Chart(ctrCtx{{ $test->id }}, {
            type: 'bar',
            data: {
                labels: labels{{ $test->id }},
                datasets: [
                    {
                        label: 'CTR (%)',
                        backgroundColor: 'rgba(255,193,7,0.5)',
                        borderColor: 'rgba(255,193,7,1)',
                        borderWidth: 1,
                        data: ctr{{ $test->id }},
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Click-Through Rate (%)'
                    }
                }
            }
        });
    @endforeach

    // Handle variant creation in the modal
    document.addEventListener('DOMContentLoaded', function() {
        const variantsContainer = document.getElementById('variants-container');
        const addVariantBtn = document.getElementById('add-variant-btn');
        let variantIndex = 0;

        addVariantBtn.addEventListener('click', function() {
            variantIndex++;
            const newVariant = document.createElement('div');
            newVariant.className = 'variant-row row mb-2';
            newVariant.innerHTML = `
                <div class="col-md-4">
                    <input type="text" class="form-control" name="variants[${variantIndex}][name]" placeholder="Variant Name" required>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="variants[${variantIndex}][weight]" placeholder="Weight" value="1" min="1" required>
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" name="variants[${variantIndex}][description]" placeholder="Description">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-variant-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            variantsContainer.appendChild(newVariant);

            // First variant's remove button should be enabled now
            if (variantIndex === 1) {
                document.querySelector('.variant-row:first-child .remove-variant-btn').disabled = false;
            }

            // Add event listener to the new remove button
            newVariant.querySelector('.remove-variant-btn').addEventListener('click', function() {
                newVariant.remove();
                reindexVariants();
            });
        });

        // Event delegation for removing variants
        variantsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-variant-btn')) {
                e.target.closest('.variant-row').remove();
                reindexVariants();
            }
        });

        // Function to reindex variant inputs after removal
        function reindexVariants() {
            const rows = variantsContainer.querySelectorAll('.variant-row');
            variantIndex = rows.length - 1;
            
            // If only one variant remains, disable its remove button
            if (rows.length === 1) {
                rows[0].querySelector('.remove-variant-btn').disabled = true;
            } else {
                rows[0].querySelector('.remove-variant-btn').disabled = false;
            }
            
            // Update indices of all variants
            rows.forEach((row, index) => {
                row.querySelectorAll('input').forEach(input => {
                    const name = input.getAttribute('name');
                    const newName = name.replace(/variants\[\d+\]/, `variants[${index}]`);
                    input.setAttribute('name', newName);
                });
            });
        }
    });
</script>
@endsection 