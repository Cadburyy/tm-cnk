@extends('layouts.app')

@section('content')
@php
    $user = Auth::user();
    $roleNames = $user ? $user->roles->pluck('name')->toArray() : [];
    $isAdmin = in_array('Admin', $roleNames);
    $istestOnly = in_array('test', $roleNames) && !$isAdmin;

    $dashboardData = $dashboardData ?? [];
    $monthlyDataJson = $monthlyDataJson ?? '[]';
    $prefixes = $prefixes ?? [];

    $fraudItems = array_filter($dashboardData, fn($data) => $data['is_fraud_deficit']);

    $fraudTotal = array_sum(array_column($fraudItems, 'combined_total')); 
    $nonFraudTotal = array_sum(array_column(array_filter($dashboardData, fn($data) => !$data['is_fraud_deficit']), 'combined_total'));
    $totalAll = $nonFraudTotal + abs($fraudTotal); 

    $chartLabels = ['Surplus/Healthy', 'Defisit/Fraud'];
    $chartData = [
        $totalAll > 0 ? ($nonFraudTotal / $totalAll) * 100 : 0, 
        $totalAll > 0 ? (abs($fraudTotal) / $totalAll) * 100 : 0
    ];
    $chartColors = ['#28a745', '#dc3545']; 
    
    $canRenderChart = count($dashboardData) > 0 && $totalAll > 0;
@endphp

<style>
    body, html {
        overflow-x: hidden;
        overflow-y: auto;
    }

    .card-link-hover:hover .card {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important; 
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }

    .card-link-hover .card {
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }

    .card {
        border-radius: 1rem;
        border: 1px solid #e9ecef; 
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075)!important;
    }

    .text-primary-dark {
        color: #0056b3;
    }
    
    .list-group-item-danger {
        color: #842029;
        background-color: #f8d7da;
        border-color: #f5c2c7;
    }
    .prefix-filter-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        max-height: 100px;
        overflow-y: auto;
        padding: 5px;
        border: 1px solid #dee2e6;
        border-radius: .25rem;
        margin-top: 10px;
    }
</style>

<div class="container d-flex flex-column justify-content-center py-5">
    <h2 class="text-center mb-5">Welcome, {{ $user->name }}</h2>
    
    <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center mt-3 mb-5">
        <div class="col-md-4">
            <a href="{{ route('items.index') }}" class="text-decoration-none card-link-hover">
                <div class="card h-100 text-center shadow-sm p-3">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-box-open fa-3x mb-2 text-info"></i>
                        <h5 class="card-title"><strong>Manage Items</strong></h5>
                        <p class="card-text text-muted"><strong>Manage and track product requests.</strong></p>
                    </div>
                </div>
            </a>
        </div>
        
        @hasanyrole('AdminIT|Admin')
        <div class="col-md-4">
            <a href="{{ route('budget.index') }}" class="text-decoration-none card-link-hover">
                <div class="card h-100 text-center shadow-sm p-3">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-dollar-sign fa-3x mb-2 text-success"></i>
                        <h5 class="card-title"><strong>Manage Budget</strong></h5>
                        <p class="card-text text-muted"><strong>View and allocate financial resources.</strong></p>
                    </div>
                </div>
            </a>
        </div>
        @endhasanyrole
    </div>

    <hr class="my-4">

    <div class="row justify-content-center">
        <div class="col-12 text-center mb-4">
            <h3 class="text-primary-dark">Item Transaction & Budget Analysis Dashboard</h3>
        </div>
        
        @if($canRenderChart)
            <div class="col-md-6 mb-4">
                <div class="card shadow p-4 h-100">
                    <h5 class="card-title text-center">Kombinasi Total Pemakaian & Budget</h5>
                    <p class="card-text text-center small text-muted">Klik bagian <span class="text-danger">Defisit/Fraud</span> untuk melihat detail bulanan.</p>
                    <div class="chart-container" style="height: 350px;">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card shadow p-4 h-100">
                    <h5 class="card-title text-center">Defisit/Fraud Items</h5>
                    
                    @if(count($prefixes) > 0)
                    <div class="mb-3">
                        <label class="form-label small fw-bold mb-1">Filter Item</label>
                        <div class="prefix-filter-container" id="prefixFilterContainer">
                            @foreach($prefixes as $prefix)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input prefix-filter-checkbox" type="checkbox" id="prefix_{{ $prefix }}" value="{{ $prefix }}">
                                    <label class="form-check-label small" for="prefix_{{ $prefix }}">{{ $prefix }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <div style="max-height: 400px; overflow-y: auto;">
                        <ul class="list-group list-group-flush" id="fraudItemList">
                        </ul>
                        <p id="noFraudItemsMsg" class="text-center text-success mt-3" style="display: none;">Semua item yang cocok memiliki surplus! 🎉</p>
                    </div>
                </div>
            </div>

        @else
            <div class="col-12 text-center p-5">
                <p class="text-muted">Tidak ada data transaksi atau budget yang cukup untuk membuat dashboard analisis.</p>
                <p class="small">Silakan unggah data Item dan Budget terlebih dahulu.</p>
            </div>
        @endif
        
    </div>

    @if (session('status'))
        <div class="alert alert-success text-center mt-5" role="alert">
            {{ session('status') }}
        </div>
    @endif
</div>

<div class="modal fade" id="barChartModal" tabindex="-1" aria-labelledby="barChartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="barChartModalLabel">
                    Monthly Detail: 
                    <span id="barChartItemNumber"></span>
                    <br><small id="barChartItemDetails" class="text-white-50"></small>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="chart-container" style="height: 400px;">
                    <canvas id="barChart"></canvas>
                </div>
                <p class="mt-3 small text-muted text-center">Kuantitas Item (Qty) [Absolut] vs. Budget (per bulan).</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
    const DASHBOARD_DATA = @json($dashboardData);
    const MONTHLY_DATA = JSON.parse('{!! $monthlyDataJson !!}');
    
    const DASHBOARD_MAP = DASHBOARD_DATA.reduce((map, item) => {
        map[item.item_number] = item;
        return map;
    }, {});
    
    const FRAUD_ITEMS_RAW = DASHBOARD_DATA.filter(item => item.is_fraud_deficit);
    
    const pieData = {
        labels: @json($chartLabels),
        datasets: [{
            data: @json($chartData),
            backgroundColor: @json($chartColors),
            hoverOffset: 15,
            borderWidth: 2,
        }]
    };
    
    const tooltipFraudItems = {
        'Surplus/Healthy': 'Total surplus items (Qty + Budget > 0)',
        'Defisit/Fraud': 'Total deficit items (Qty + Budget < 0)',
    };

    let currentBarChart = null;

    function formatNumber(number) {
        return Math.round(number).toLocaleString('id-ID');
    }

    function createBarChart(itemNumber) {
        const itemMonthlyData = MONTHLY_DATA[itemNumber];
        const itemDetail = DASHBOARD_MAP[itemNumber];
        if (!itemMonthlyData || !itemDetail) return;
        
        const allMonths = Object.keys(itemMonthlyData).sort();
        
        let qtyData = [];
        let budgetData = [];
        
        allMonths.forEach(month => {
            const data = itemMonthlyData[month] || {};
            qtyData.push(Math.abs(data.qty || 0)); 
            budgetData.push(data.budget || 0);
        });

        const barCtx = document.getElementById('barChart');

        if (currentBarChart) {
            currentBarChart.destroy();
        }

        currentBarChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: allMonths.map(m => {
                    const [year, month] = m.split('-');
                    return new Date(year, month - 1).toLocaleString('id-ID', { month: 'short', year: '2-digit' });
                }),
                datasets: [
                    {
                        label: 'Pemakaian (Transaksi)',
                        data: qtyData,
                        backgroundColor: '#dc3545', 
                        yAxisID: 'y',
                    },
                    {
                        label: 'Budget Allocated',
                        data: budgetData,
                        backgroundColor: '#28a745', 
                        yAxisID: 'y',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { stacked: false, title: { display: true, text: 'Bulan' } },
                    y: { 
                        stacked: false,
                        beginAtZero: true,
                        title: { display: true, text: 'Kuantitas/Budget' },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                                }
                                return label;
                            },
                            afterLabel: function(context) {
                                return tooltipFraudItems[context.label] || '';
                            }
                        }
                    }
                }
            }
        });

        $('#barChartItemNumber').text(itemNumber);
        $('#barChartItemDetails').text(`Desc: ${itemDetail.description} | UOM: ${itemDetail.uom}`); 
        $('#barChartModal').modal('show');
    }
    
    function initializeCharts() {
        const pieCtx = document.getElementById('pieChart');
        if (pieCtx && pieData.datasets[0].data.some(d => d > 0)) {
            new Chart(pieCtx, {
                type: 'pie',
                data: pieData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: { size: 14 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    return `${label}: ${value.toFixed(1)}%`;
                                },
                                afterLabel: function(context) {
                                    return tooltipFraudItems[context.label] || '';
                                }
                            }
                        }
                    },
                }
            });
        }
    }
    
    function renderFraudList(selectedPrefixes) {
        let listHtml = '';
        const $listContainer = $('#fraudItemList');
        const $noItemsMsg = $('#noFraudItemsMsg');
        let filteredCount = 0;

        FRAUD_ITEMS_RAW.forEach(item => {
            const prefix = item.item_number.substring(0, 4).toUpperCase();
            
            if (selectedPrefixes.length === 0 || selectedPrefixes.includes(prefix)) {
                const shortDescription = item.description ? item.description.substring(0, 10) : '';
                
                listHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center list-group-item-danger py-2">
                        <span class="me-auto">
                            ${item.item_number} 
                            <small class="text-muted">(${shortDescription}${item.description.length > 10 ? '...' : ''})</small>
                        </span>
                        <span class="fw-bold text-danger me-3">${formatNumber(item.combined_total)}</span>
                        <button class="btn btn-sm btn-danger view-item-detail" data-item-number="${item.item_number}" type="button">
                            <i class="fas fa-chart-bar"></i> Trend
                        </button>
                    </li>
                `;
                filteredCount++;
            }
        });

        $listContainer.html(listHtml);
        
        if (filteredCount === 0) {
            $noItemsMsg.show();
        } else {
            $noItemsMsg.hide();
        }
    }

    $(document).ready(function() {
        if (@json($canRenderChart)) {
              initializeCharts();
        }
        
        renderFraudList([]);

        $(document).on('change', '.prefix-filter-checkbox', function() {
            const selected = $('.prefix-filter-checkbox:checked').map(function(){ 
                return $(this).val(); 
            }).get();
            renderFraudList(selected);
        });

        $(document).on('click', '.view-item-detail', function() {
            const itemNumber = $(this).data('item-number');
            createBarChart(itemNumber);
        });
    });
</script>

@endsection