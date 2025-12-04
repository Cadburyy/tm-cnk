@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-dark">💸 Data Pengeluaran Outing</h1>
        @hasanyrole('AdminIT|Admin')
        <div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadCsvModal">
                <i class="fas fa-file-upload me-1"></i> Unggah CSV
            </button>
        </div>
        @endhasanyrole
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('outings.index') }}" class="row mb-4" id="filterForm">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light fw-bold">Filter Data Outing</div>
                <div class="card-body">
                    
                    @if ($mode == 'details')
                        <div class="row g-3 mb-2">
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-bold">Start Date</label>
                                <input type="date" name="start_date" value="{{ $start_date }}" class="form-control">
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-bold">End Date</label>
                                <input type="date" name="end_date" value="{{ $end_date }}" class="form-control">
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">Part</label>
                                <input list="listParts" name="desc_term" class="form-control form-control-sm" value="{{ $desc_term }}" autocomplete="off">
                                <datalist id="listParts">
                                    @if(isset($distinctParts))
                                        @foreach($distinctParts as $p) <option value="{{ $p }}"> @endforeach
                                    @endif
                                </datalist>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">Nama PT</label>
                                <input list="listPTs" name="pt_term" class="form-control form-control-sm" value="{{ $pt_term }}" autocomplete="off">
                                <datalist id="listPTs">
                                    @foreach($distinctPTs as $pt) <option value="{{ $pt }}"> @endforeach
                                </datalist>
                            </div>
                        </div>

                    @else
                        <div class="row g-3">
                            <div class="col-lg-6 col-md-12">
                                <label class="form-label fw-bold">Yearly (Total Only)</label>
                                <div class="card p-3 h-100">
                                    <div class="mb-2">
                                        <label class="form-label small mb-1">Pilih Tahun</label>
                                        <div class="dropdown" id="yearlyYearsDropdown">
                                            <button class="btn btn-outline-secondary btn-sm w-100 text-start dropdown-toggle" type="button" id="yearlyYearsBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span id="yearlyYearsLabel">Pilih Tahun</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2" style="max-height:200px; overflow-y:auto;" aria-labelledby="yearlyYearsBtn">
                                                <input type="hidden" name="yearly_years[]" id="yearlyYearsHidden">
                                                @foreach($distinctYears as $year)
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input yearly-year-checkbox" type="checkbox" id="year_yearly_{{ $year }}" value="{{ $year }}">
                                                        <label class="form-check-label small" for="year_yearly_{{ $year }}">{{ $year }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <label class="form-label fw-bold">Monthly</label>
                                <div class="card p-3 h-100">
                                    <div class="row">
                                        <div class="col-5">
                                            <label class="form-label small mb-1">Pilih Tahun</label>
                                            <div class="dropdown" id="monthlyYearsDropdown">
                                                <button class="btn btn-outline-secondary btn-sm w-100 text-start dropdown-toggle" type="button" id="monthlyYearsBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span id="monthlyYearsLabel">Pilih Tahun</span>
                                                </button>
                                                <div class="dropdown-menu w-100 p-2" style="max-height:200px; overflow-y:auto;" aria-labelledby="monthlyYearsBtn">
                                                    <input type="hidden" name="monthly_years_selected[]" id="monthlyYearsHidden">
                                                    @foreach($distinctYears as $year)
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input monthly-year-checkbox" type="checkbox" id="year_monthly_{{ $year }}" value="{{ $year }}">
                                                            <label class="form-check-label small" for="year_monthly_{{ $year }}">{{ $year }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-7">
                                            <label class="form-label small mb-1">Pilih Bulan</label>
                                            <div id="monthlyMonthsContainer" class="p-1" style="max-height:160px; overflow:auto; border:1px solid #e9ecef; border-radius:4px;">
                                                @foreach($distinctYearMonths as $yr => $mList)
                                                    <div class="monthly-year-group mb-2" data-year="{{ $yr }}" style="display:none;">
                                                        <div class="fw-bold small mb-1">{{ $yr }}</div>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @foreach($mList as $ym)
                                                                <div class="form-check">
                                                                    <input class="form-check-input monthly-month-checkbox" type="checkbox" id="month_{{ $ym }}" value="{{ $ym }}">
                                                                    <label class="form-check-label" for="month_{{ $ym }}">{{ \Carbon\Carbon::createFromFormat('Y-m', $ym)->format('M') }}</label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="small text-muted mt-1"><span id="months-selected-count">0 Bulan terpilih</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mt-4">
                            <div class="col-lg-6 col-md-6">
                                <label class="form-label">Part</label>
                                <input list="listParts" name="desc_term" class="form-control form-control-sm" value="{{ $desc_term }}" autocomplete="off">
                                <datalist id="listParts">
                                    @if(isset($distinctParts))
                                        @foreach($distinctParts as $p) <option value="{{ $p }}"> @endforeach
                                    @endif
                                </datalist>
                            </div>
                            <div class="col-lg-6 col-md-6">
                                <label class="form-label">Nama PT</label>
                                <input list="listPTs" name="pt_term" class="form-control form-control-sm" value="{{ $pt_term }}" autocomplete="off">
                                <datalist id="listPTs">
                                    @foreach($distinctPTs as $pt) <option value="{{ $pt }}"> @endforeach
                                </datalist>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card-footer d-flex justify-content-end gap-2">
                    @if ($mode == 'details')
                        @if(Auth::check() && (method_exists(Auth::user(), 'hasRole') ? Auth::user()->hasRole('Admin|AdminIT') : (Auth::user()->is_admin ?? false)))
                            <button type="button" id="bulkDeleteBtn" class="btn btn-danger shadow-sm">
                                <i class="fas fa-trash me-1"></i> Bulk Delete Selected
                            </button>
                        @endif
                    @endif
                    
                    <input type="hidden" name="mode" value="{{ $mode }}">
                    @foreach($raw_selections as $p)
                        <input type="hidden" name="pivot_months[]" value="{{ $p }}">
                    @endforeach

                    <button type="submit" class="btn btn-success shadow">
                        <i class="fas fa-search me-1"></i> Apply Filters
                    </button>
                    <a href="{{ route('outings.index') }}" class="btn btn-outline-secondary shadow">
                        <i class="fas fa-undo me-1"></i> Reset Filter
                    </a>
                </div>
            </div>
        </div>
    </form>

    <form id="bulkDeleteForm" method="POST" action="{{ route('outings.bulkDestroy') }}" style="display:none;">
        @csrf
        <div id="bulkDeleteIdsContainer"></div>
    </form>

    <div class="d-flex mb-3 gap-2">
        <a href="{{ route('outings.index', array_merge(request()->query(), ['mode' => 'resume'])) }}" class="btn {{ $mode == 'resume' ? 'btn-info text-white shadow-lg' : 'btn-outline-info' }}">
            <i class="fas fa-table me-1"></i> Resume (Monthly Pivot)
        </a>
        <a href="{{ route('outings.index', array_merge(request()->query(), ['mode' => 'details'])) }}" class="btn {{ $mode == 'details' ? 'btn-info text-white shadow-lg' : 'btn-outline-info' }}">
            <i class="fas fa-list-ul me-1"></i> Details (All Records)
        </a>
    </div>

    <div class="card shadow-lg">
        <div class="card-header bg-info text-black">
            @if ($mode == 'details')
                Hasil Data Outing - Details (Total {{ $outings->count() }} Records)
            @else
                Hasil Data Outing - Resume (Total {{ $outings->count() }} Records)
            @endif
        </div>
        <div class="card-body p-0">
            @if (($outings->isEmpty() && $mode == 'details') || ($mode == 'resume' && empty($summary_rows)))
                <p class="text-center text-muted p-4">Tidak ada data yang ditemukan.</p>
            @else
                <div class="table-responsive" style="max-height: 70vh;">
                    
                    @if ($mode == 'details')
                        <table class="table table-bordered table-striped table-hover table-sm mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th style="width:36px"><input type="checkbox" id="select-all-details"></th>
                                    <th class="text-nowrap bg-primary text-white">Part</th>
                                    <th class="text-center text-nowrap">Aksi</th>
                                    <th class="text-nowrap">Voucher</th>
                                    <th class="text-nowrap">Akun</th>
                                    <th>Nama</th>
                                    <th>Tanggal</th>
                                    <th>Nama PT</th>
                                    <th class="text-end">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($outings as $out)
                                    <tr>
                                        <td><input type="checkbox" class="select-detail" name="selected_ids[]" value="{{ $out->id }}"></td>
                                        <td class="fw-bold text-primary">{{ $out->part }}</td>
                                        <td class="text-nowrap text-center" style="width: 100px;">
                                            <a href="{{ route('outings.edit', $out->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                        </td>
                                        <td class="text-nowrap font-monospace small">{{ $out->voucher }}</td>
                                        <td class="text-nowrap small">{{ $out->akun }}</td>
                                        <td>
                                            <div class="fw-bold small">{{ $out->nama }}</div>
                                            <div class="text-muted small" style="font-size:0.8em;">{{ $out->keterangan }}</div>
                                        </td>
                                        <td class="text-nowrap">{{ $out->tanggal ? $out->tanggal->format('d/m/Y') : '-' }}</td>
                                        <td><div class="fw-bold">{{ $out->nama_pt }}</div></td>
                                        <td class="text-end font-monospace fw-bold">
                                            Rp. {{ number_format($out->nominal, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    @elseif ($mode == 'resume')
                        <table class="table table-bordered table-striped table-hover table-sm mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th style="width:30px;" class="text-center">+/-</th>
                                    <th class="text-nowrap bg-primary text-white">Part</th>
                                    @if (count($months) > 0)
                                        @foreach($months as $m)
                                            <th class="text-nowrap text-center" style="min-width:80px;">{{ $m['label'] }}</th>
                                        @endforeach
                                    @endif
                                    <th class="text-end" style="min-width:100px;">Total Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $groupedRows = collect($summary_rows)->groupBy(function($item) {
                                        return $item['part'];
                                    });
                                @endphp

                                @foreach($groupedRows as $groupKey => $rows)
                                    @php
                                        $first = $rows->first();
                                        $partName = $first['part'];
                                        $parentTotal = $rows->sum('total');
                                        
                                        $parentPivotValues = [];
                                        foreach($months as $m) {
                                            $parentPivotValues[$m['key']] = $rows->sum(function($r) use ($m) { 
                                                return $r['months'][$m['key']] ?? 0; 
                                            });
                                        }

                                        $allRowIds = $rows->pluck('row_ids')->map(fn($ids) => explode(',', $ids))->flatten()->unique()->implode(',');
                                        $uniqueId = md5($groupKey);
                                    @endphp

                                    <tr class="resume-row parent-row" style="background-color: #f8f9fa; cursor: pointer;" 
                                        data-id-list="{{ $allRowIds }}">
                                        
                                        <td class="text-center stop-propagation">
                                            <button type="button" class="btn btn-sm btn-outline-secondary toggle-child-btn" 
                                                data-target=".child-{{ $uniqueId }}">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </td>
                                        <td class="fw-bold text-primary">{{ $partName }}</td>
                                        
                                        @if (count($months) > 0)
                                            @foreach($months as $m)
                                                @php $val = $parentPivotValues[$m['key']] ?? 0; @endphp
                                                <td class="text-end font-monospace {{ $val < 0 ? 'text-danger' : '' }}">
                                                    Rp. {{ number_format($val, 2, ',', '.') }}
                                                </td>
                                            @endforeach
                                        @endif
                                        
                                        <td class="text-end fw-bold font-monospace bg-light">
                                            Rp. {{ number_format($parentTotal, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    
                                    @php
                                        $accountBreakdown = $rows->groupBy('akun');
                                    @endphp

                                    @foreach($accountBreakdown as $akunKey => $accRows)
                                        <tr class="child-row child-{{ $uniqueId }}" style="display:none; background-color: #fff;">
                                            <td></td>
                                            <td class="ps-4">
                                                <span class="badge bg-secondary me-2">Akun</span> 
                                                <span class="fw-bold text-dark">{{ $akunKey }}</span>
                                                <span class="text-muted small ms-2">({{ $accRows->first()['nama_pt'] }})</span>
                                            </td>
                                            
                                            @if (count($months) > 0)
                                                @foreach($months as $m)
                                                    @php 
                                                        $cVal = $accRows->sum(function($r) use ($m) { 
                                                            return $r['months'][$m['key']] ?? 0; 
                                                        });
                                                    @endphp
                                                    <td class="text-end font-monospace small text-muted">
                                                        {{ number_format($cVal, 2, ',', '.') }}
                                                    </td>
                                                @endforeach
                                            @endif

                                            <td class="text-end font-monospace small text-dark fw-bold">
                                                {{ number_format($accRows->sum('total'), 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach

                                @endforeach
                            </tbody>
                            <tfoot class="bg-warning">
                                <tr>
                                    <td colspan="2" class="text-end fw-bold text-dark">GRAND TOTAL</td>
                                    @if (count($months) > 0)
                                        @foreach($months as $m)
                                            <td class="text-end fw-bold font-monospace text-dark">
                                                Rp. {{ number_format($footer_totals[$m['key']] ?? 0, 2, ',', '.') }}
                                            </td>
                                        @endforeach
                                    @endif
                                    <td class="text-end fw-bold font-monospace text-dark">
                                        Rp. {{ number_format($grand_total, 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    @endif

                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="pivotDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl"> 
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Detail Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="detail-loading" class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="detail-content" style="display: none;">
                        <div class="mb-3">
                        </div>
                        
                        <div id="monthly-subtotals-container" class="mb-3 p-3 bg-white border rounded">
                            <h6 class="fw-bold mb-2 text-info"><i class="fas fa-calendar-alt me-1"></i> Monthly Subtotals (Filtered)</h6>
                            <div id="monthly-subtotals-list" class="d-flex flex-wrap gap-3">
                            </div>
                        </div>
                        
                        <div class="table-responsive" style="max-height: 50vh;">
                            <div id="detail-table-container"></div>
                        </div>
                        <div class="mt-3 text-end border-top pt-2">
                            <h5>Total Nominal: <span id="modal-total-nominal" class="fw-bold font-monospace"></span></h5>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadCsvModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Unggah File CSV Outing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('outings.store') }}" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf
                        <p class="text-muted small">Format CSV: NO; TANGGAL; AKUN; VOUCHER; NAMA; NAMA PT; PART; Keterangan; NOMINAL</p>
                        <div class="mb-3">
                            <label for="csv_files" class="form-label">Pilih File CSV (Boleh lebih dari satu)</label>
                            <input type="file" name="csv_files[]" multiple class="form-control" id="csv_files" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-1"></i> Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-2 fw-bold text-danger">Yakin hapus data terpilih?</p>
                    <p class="small text-muted mb-3">Aksi ini tidak dapat dibatalkan.</p>
                    <p class="mb-0 text-dark fw-bold" id="deleteRecordDesc"></p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteBtn">Hapus Permanen</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function formatNominalJS(value) {
        if (value === null || value === undefined || isNaN(value)) return 'Rp. 0,00';
        let val = parseFloat(value).toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        return 'Rp. ' + val;
    }

    function formatDateJS(dateString) {
        if (!dateString) return '-';
        let datePart = dateString;
        if (dateString.indexOf('T') > -1) {
            datePart = dateString.split('T')[0];
        }
        const parts = datePart.split('-');
        if (parts.length === 3) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        return datePart;
    }
    
    function formatMonthYear(ymString) {
        if (!ymString || ymString.length !== 7) return ymString;
        const [year, month] = ymString.split('-');
        const date = new Date(year, month - 1, 1);
        const options = { month: 'short', year: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
    }

    $(function() {
        const selectedPivot = @json($raw_selections ?? []);
        const mode = '{{ $mode }}';
        
        if (mode === 'resume') {
            $('.yearly-year-checkbox').on('change', function() {
                updateYearsLabel('.yearly-year-checkbox', '#yearlyYearsLabel');
                rebuildPivotHiddenInputs();
            });
            $('.monthly-year-checkbox').on('change', function(){
                updateYearsLabel('.monthly-year-checkbox', '#monthlyYearsLabel');
                syncMonthlyGroupsVisibility();
                rebuildPivotHiddenInputs();
                updateMonthsCount();
            });
            $(document).on('change', '.monthly-month-checkbox', function(){
                rebuildPivotHiddenInputs();
                updateMonthsCount();
            });

            (function syncFromServerPivot() {
                selectedPivot.forEach(function(p) {
                    if (String(p).startsWith('YEARLY-')) {
                        const year = String(p).replace('YEARLY-','').split('|')[0];
                        $('#year_yearly_' + year).prop('checked', true);
                    } else if (/^\d{4}-\d{2}$/.test(String(p))) {
                        const year = String(p).slice(0,4);
                        $('#month_' + p).prop('checked', true);
                        $('#year_monthly_' + year).prop('checked', true);
                    }
                });
                updateYearsLabel('.yearly-year-checkbox', '#yearlyYearsLabel');
                updateYearsLabel('.monthly-year-checkbox', '#monthlyYearsLabel');
                syncMonthlyGroupsVisibility();
                updateMonthsCount();
            })();
        }

        function updateYearsLabel(selector, labelId) {
            const checked = $(selector + ':checked');
            let label = 'Pilih Tahun';
            if (checked.length === 1) label = checked.val();
            else if (checked.length > 1) label = checked.length + ' Tahun terpilih';
            $(labelId).text(label);
        }

        function updateMonthsCount() {
            $('#months-selected-count').text(($('.monthly-month-checkbox:checked').length) + ' Bulan terpilih');
        }

        function syncMonthlyGroupsVisibility() {
            const selYears = $('.monthly-year-checkbox:checked').map(function(){ return $(this).val(); }).get() || [];
            $('.monthly-year-group').each(function(){
                const y = $(this).data('year') + '';
                if (selYears.indexOf(y) !== -1) $(this).show();
                else $(this).hide().find('.monthly-month-checkbox').prop('checked', false);
            });
        }

        function rebuildPivotHiddenInputs() {
            $('input[name="pivot_months[]"]', '#filterForm').remove();
            const yearlyYears = $('.yearly-year-checkbox:checked').map(function(){ return $(this).val(); }).get() || [];
            yearlyYears.forEach(function(y) {
                $('<input>').attr({type: 'hidden', name: 'pivot_months[]', value: 'YEARLY-' + y + '|total'}).appendTo('#filterForm');
            });
            const monthly = $('.monthly-month-checkbox:checked').map(function(){ return $(this).val(); }).get() || [];
            monthly.forEach(function(ym) {
                $('<input>').attr({type: 'hidden', name: 'pivot_months[]', value: ym}).appendTo('#filterForm');
            });
        }

        $(document).on('click', '.toggle-child-btn', function(e) {
            e.stopPropagation(); 
            const btn = $(this);
            const targetClass = btn.data('target');
            const icon = btn.find('i');
            const rows = $(targetClass);
            if (rows.is(':visible')) {
                rows.hide();
                icon.removeClass('fa-minus').addClass('fa-plus');
            } else {
                rows.show();
                icon.removeClass('fa-plus').addClass('fa-minus');
            }
        });

        $(document).on('click', '.stop-propagation', function(e) {
            e.stopPropagation();
        });

        $(document).on('click', '.parent-row', function(e) {
            if ($(e.target).closest('.toggle-child-btn').length) return;

            const row = $(this);
            const idList = row.data('id-list');
            
            const pivotSelections = $('input[name="pivot_months[]"]').map(function(){
                return $(this).val();
            }).get();

            $('#pivotDetailModal').modal('show');
            $('#detail-content').hide();
            $('#detail-loading').show();
            $('#monthly-subtotals-list').empty();

            $.ajax({
                url: '{{ route("outings.index") }}',
                data: { 
                    action: 'pivot_row_details', 
                    id_list: idList,
                    pivot_months: pivotSelections
                },
                success: function(res) {
                    $('#detail-loading').hide();
                    $('#detail-content').show();
                    $('#modal-total-nominal').text(formatNominalJS(res.total_nominal));

                    if (res.monthly_subtotals && Object.keys(res.monthly_subtotals).length > 0) {
                        let subtotalsHtml = '';
                        for (const ym in res.monthly_subtotals) {
                            if (res.monthly_subtotals.hasOwnProperty(ym)) {
                                const nominal = res.monthly_subtotals[ym];
                                const monthLabel = formatMonthYear(ym);
                                subtotalsHtml += `
                                    <div class="p-2 border rounded shadow-sm" style="background-color: #f7f7f7;">
                                        <div class="small text-muted fw-bold">${monthLabel}</div>
                                        <div class="fw-bold font-monospace text-dark" style="font-size:0.9em;">${formatNominalJS(nominal)}</div>
                                    </div>
                                `;
                            }
                        }
                        $('#monthly-subtotals-list').html(subtotalsHtml);
                    } else {
                        $('#monthly-subtotals-list').html('<div class="text-muted small">No monthly breakdowns found for selected period.</div>');
                    }
                    
                    if(res.details && res.details.length > 0) {
                        const first = res.details[0];
                        
                        let headerHtml = `
                            <div class="card bg-light mb-3 border-0">
                                <div class="card-body py-2">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <span class="text-muted small text-uppercase fw-bold">Part</span>
                                            <div class="fw-bold text-primary fs-5">${first.part || '-'}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="text-muted small text-uppercase fw-bold">Keterangan (Group)</span>
                                            <div class="fw-bold">${first.keterangan || '-'}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#detail-content .mb-3:first').html(headerHtml);

                        let html = '<table class="table table-sm table-striped table-bordered mb-0"><thead><tr class="bg-white"><th>Tanggal</th><th>Voucher</th><th>Akun</th><th>Nama</th><th>Nama PT</th><th class="text-end">Nominal</th></tr></thead><tbody>';
                        res.details.forEach(d => {
                            html += `<tr>
                                <td>${formatDateJS(d.tanggal)}</td>
                                <td class="font-monospace small">${d.voucher || '-'}</td>
                                <td class="small">${d.akun || '-'}</td>
                                <td>${d.nama || '-'}</td>
                                <td>${d.nama_pt || '-'}</td>
                                <td class="text-end fw-bold">${formatNominalJS(d.nominal)}</td>
                            </tr>`;
                        });
                        html += '</tbody></table>';
                        $('#detail-table-container').html(html);
                    } else {
                        $('#detail-table-container').html('<div class="text-muted text-center">No details found.</div>');
                    }
                },
                error: function() {
                    $('#detail-loading').hide();
                    $('#detail-content').show();
                    $('#detail-table-container').html('<div class="text-danger text-center">Failed to load details.</div>');
                    $('#monthly-subtotals-list').html('<div class="text-danger small">Failed to load monthly subtotals.</div>');
                }
            });
        });

        $('#select-all-details').on('click', function() {
            $('.select-detail').prop('checked', $(this).is(':checked'));
        });

        $(document).on('change', '.select-detail', function() {
            if (!$(this).is(':checked')) {
                $('#select-all-details').prop('checked', false);
            } else {
                if ($('.select-detail:checked').length === $('.select-detail').length) {
                    $('#select-all-details').prop('checked', true);
                }
            }
        });

        $('#bulkDeleteBtn').on('click', function(e) {
            e.preventDefault();
            const selected = $('.select-detail:checked').map(function(){ return $(this).val(); }).get();
            if (selected.length === 0) {
                console.warn('Pilih setidaknya satu baris untuk dihapus.');
                return; 
            }
            $('#bulkDeleteIdsContainer').empty();
            selected.forEach(function(val) {
                $('<input>').attr({ type: 'hidden', name: 'selected_ids[]', value: val }).appendTo('#bulkDeleteIdsContainer');
            });
            $('#deleteRecordDesc').text('Total ' + selected.length + ' records akan dihapus.');
            $('#deleteConfirmModal').modal('show');
        });

        $('#confirmDeleteBtn').on('click', function() {
            $('#deleteConfirmModal').modal('hide');
            $('#bulkDeleteForm').submit();
        });
    });
</script>
@endsection