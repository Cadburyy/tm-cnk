@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-dark">📊 Data Transaksi Barang</h1>
        <div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadCsvModal">
                <i class="fas fa-file-upload me-1"></i> Unggah CSV
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('items.index') }}" class="row mb-4" id="filterForm">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light fw-bold">Pilihan Filter Data Barang</div>
                <div class="card-body">
                    @if ($mode == 'details')
                        <div class="row g-3 mb-2">
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-bold">Start Date (Effective Date)</label>
                                <input type="date" name="start_date" value="{{ $start_date }}" class="form-control">
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-bold">End Date (Effective Date)</label>
                                <input type="date" name="end_date" value="{{ $end_date }}" class="form-control">
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label">Item Number</label>
                                <input list="itemNumbers" name="item_number_term" id="item-number-input"
                                    class="form-control form-control-sm" value="{{ $item_number_term }}" autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                                <datalist id="itemNumbers">
                                    @foreach($itemNumbers as $num)
                                        <option value="{{ $num }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label">Item Group</label>
                                <input list="itemGroups" name="item_group_term" id="item-group-input"
                                    class="form-control form-control-sm" value="{{ $item_group_term }}" autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                                <datalist id="itemGroups">
                                    @foreach($itemGroups as $group)
                                        <option value="{{ $group }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label">Departemen</label>
                                <input list="depts" name="dept_term" id="dept-input"
                                    class="form-control form-control-sm" value="{{ $dept_term }}" autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                                <datalist id="depts">
                                    @foreach($depts as $dept)
                                        <option value="{{ $dept }}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                    @else
                        <div class="row g-3">
                            <div class="col-lg-6 col-md-12">
                                <label class="form-label fw-bold">Yearly (Totals / Averages)</label>
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
                                    <div class="mt-2">
                                        <label class="form-label small mb-1">Mode</label>
                                        <select id="yearlyMode" class="form-control form-control-sm" name="yearly_mode">
                                            <option value="">--Pilih Mode--</option>
                                            <option value="total" {{ (isset($yearly_mode) && $yearly_mode == 'total') ? 'selected' : '' }}>Total</option>
                                            <option value="avg" {{ (isset($yearly_mode) && $yearly_mode == 'avg') ? 'selected' : '' }}>Average</option>
                                        </select>
                                        <div class="small text-muted mt-1">Pilih apakah menampilkan Total atau Rata-rata untuk tahun yang dipilih.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-6 col-md-12">
                                <label class="form-label fw-bold">Monthly (Pilih Bulan per Tahun)</label>
                                <div class="card p-3 h-100">
                                    <div class="row">
                                        <div class="col-5">
                                            <label class="form-label small mb-1">Pilih Tahun (untuk Bulanan)</label>
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
                                            <label class="form-label small mb-1">Pilih Bulan (per tahun)</label>
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
                        <div class="row g-3 mt-3">
                            <div class="col-lg-4">
                                <label class="form-label">Item Number</label>
                                <input list="itemNumbers" name="item_number_term" id="item-number-input"
                                    class="form-control form-control-sm" value="{{ $item_number_term }}" autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                                <datalist id="itemNumbers">
                                    @foreach($itemNumbers as $num)
                                        <option value="{{ $num }}">
                                    @endforeach
                                </datalist>
                            </div>
                            {{-- ADDED: Item Description Filter for Resume Mode --}}
                            <div class="col-lg-4">
                                <label class="form-label">Item Description</label>
                                <input type="text" name="item_description_term" class="form-control form-control-sm" value="{{ $item_description_term }}" autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Departemen</label>
                                <input list="depts" name="dept_term" id="dept-input"
                                    class="form-control form-control-sm" value="{{ $dept_term }}" autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                                <datalist id="depts">
                                    @foreach($depts as $dept)
                                        <option value="{{ $dept }}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                    @endif
                </div>
 
                <div class="card-footer d-flex justify-content-end gap-2">
                    <button type="button" id="exportBtn" class="btn btn-outline-primary">
                        <i class="fas fa-download me-1"></i> Download Selected CSV
                    </button>
 
                    <input type="hidden" name="mode" value="{{ $mode }}">
                    @foreach($pivot_months as $p)
                        <input type="hidden" name="pivot_months[]" value="{{ $p }}">
                    @endforeach
                    <button type="submit" class="btn btn-success shadow">
                        <i class="fas fa-search me-1"></i> Apply Filters
                    </button>
                    <a href="{{ route('items.index') }}" class="btn btn-outline-secondary shadow">
                        <i class="fas fa-undo me-1"></i> Reset Filter
</a>
                </div>
            </div>
        </div>
    </form>

    <div class="d-flex mb-3 gap-2">
        <a href="{{ route('items.index', array_merge(request()->query(), ['mode' => 'resume'])) }}" class="btn {{ $mode == 'resume' ? 'btn-info text-white shadow-lg' : 'btn-outline-info' }}">
            <i class="fas fa-table me-1"></i> Resume (Monthly Pivot)
        </a>
        @if(Auth::check() && (method_exists(Auth::user(), 'hasRole') ? Auth::user()->hasRole('Admin') : (Auth::user()->is_admin ?? false)))
            <a href="{{ route('items.index', array_merge(request()->query(), ['mode' => 'details'])) }}" class="btn {{ $mode == 'details' ? 'btn-info text-white shadow-lg' : 'btn-outline-info' }}">
                <i class="fas fa-list-ul me-1"></i> Details (All Records)
            </a>
        @endif
    </div>

    <form id="exportForm" method="POST" action="{{ route('items.exportSelected') }}">
        @csrf
        <input type="hidden" name="mode" id="exportMode" value="{{ $mode }}">
        @foreach($pivot_months as $p)
            <input type="hidden" name="pivot_months[]" value="{{ $p }}">
        @endforeach

        <div class="card shadow-lg">
            <div class="card-header bg-info text-black">
                @if ($mode == 'details')
                    Hasil Data Transaksi - Details (Total {{ $items->count() }} Records)
                @else
                    Hasil Data Transaksi - Resume (Total {{ count($summary_rows) }} Items)
                @endif
            </div>
            <div class="card-body p-0">
                @if (($items->isEmpty() && $mode == 'details') || ($mode == 'resume' && empty($summary_rows)))
                    <p class="text-center text-muted p-4">Tidak ada data transaksi yang ditemukan berdasarkan filter yang diterapkan.</p>
                @else
                    <div class="table-responsive" style="max-height: 70vh;">
                        @if ($mode == 'details')
                            <table class="table table-bordered table-striped table-hover table-sm mb-0">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th style="width:36px"><input type="checkbox" id="select-all-details"></th>
                                        <th class="text-nowrap">Aksi</th>
                                        <th class="text-nowrap">Item Number</th>
                                        <th class="text-nowrap bg-primary text-white">Item Description</th>
                                        <th class="text-nowrap">Effective Date</th>
                                        <th>Bulan</th>
                                        <th class="text-nowrap text-end">Loc Qty Change</th>
                                        <th>UOM</th>
                                        <th class="text-nowrap">Remarks</th>
                                        <th class="text-nowrap">Item Group</th>
                                        <th>Departemen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        <tr>
                                            <td><input type="checkbox" class="select-detail" value="{{ $item->id }}"></td>
                                            {{-- ADDED: Action Buttons for Admin --}}
                                            <td class="text-nowrap">
                                                @if(Auth::check() && (method_exists(Auth::user(), 'hasRole') ? auth()->user()->hasRole('Admin') : (auth()->user()->is_admin ?? false)))
                                                    <a href="{{ route('items.edit', $item->id) }}" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i></a>
                                                    <form action="{{ route('items.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-record-id="{{ $item->id }}" data-record-desc="Item #{{ $item->id }} ({{ $item->item_number }})"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                @endif
                                            </td>
                                            <td class="text-nowrap">{{ $item->item_number }}</td>
                                            <td style="max-width:250px; background-color: #e7f1ff;">{{ $item->item_description }}</td>
                                            <td class="text-nowrap">
                                                @if ($item->effective_date instanceof \DateTime || $item->effective_date instanceof \Carbon\Carbon)
                                                    {{ $item->effective_date->format('d/m/Y') }}
                                                @else
                                                    {{ \Carbon\Carbon::parse($item->effective_date)->format('d/m/Y') }}
                                                @endif
                                            </td>
                                            <td>{{ $item->bulan }}</td>
                                            <td class="text-end font-monospace {{ $item->loc_qty_change < 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                                {{ intval($item->loc_qty_change) }}
                                            </td>
                                            <td>{{ $item->unit_of_measure }}</td>
                                            <td style="max-width:200px; word-wrap:break-word;">{{ $item->remarks }}</td>
                                            <td>{{ $item->item_group }}</td>
                                            <td>{{ $item->dept }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @elseif ($mode == 'resume')
                            <table class="table table-bordered table-striped table-hover table-sm mb-0">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th style="width:36px"><input type="checkbox" id="select-all-resume"></th>
                                        <th class="text-nowrap">Item Number</th>
                                        <th class="text-nowrap bg-primary text-white">Item Description</th>
                                        <th class="text-nowrap">UOM</th>
                                        @if (count($months) > 0)
                                            @foreach($months as $m)
                                                <th class="text-nowrap text-center" style="min-width:80px;">{{ $m['label'] }}</th>
                                            @endforeach
                                        @endif
                                        <th class="text-nowrap text-center" style="min-width:90px;">Total Qty</th>
                                        <th class="text-nowrap">Departemen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($summary_rows as $row)
                                        <tr class="resume-row-clickable" data-item-key="{{ $row['item_number'] }}||{{ $row['item_description'] }}||{{ $row['unit_of_measure'] }}||{{ $row['dept'] }}" data-id-list="{{ $row['row_ids'] ?? '' }}" style="cursor: pointer;" title="Klik untuk melihat detail">
                                            <td class="select-cell"><input type="checkbox" class="select-resume" value="{{ $row['row_ids'] ?? '' }}"></td>
                                            <td class="text-nowrap">{{ $row['item_number'] }}</td>
                                            <td style="max-width:300px; background-color: #e7f1ff;">{{ $row['item_description'] }}</td>
                                            <td>{{ $row['unit_of_measure'] }}</td>
                                            @if (count($months) > 0)
                                                @foreach($months as $m)
                                                    @php $val = intval($row['months'][$m['key']] ?? 0); @endphp
                                                    <td class="text-end font-monospace {{ $val < 0 ? 'text-danger fw-bold' : 'text-success' }}" style="min-width:80px;">{{ $val }}</td>
                                                @endforeach
                                            @endif
                                            @php $totalVal = intval($row['total']); @endphp
                                            <td class="text-end fw-bold font-monospace bg-light {{ $totalVal < 0 ? 'text-danger' : 'text-success' }}" style="min-width:90px;">{{ $totalVal }}</td>
                                            <td class="text-nowrap">{{ $row['dept'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </form>

    <div class="modal fade" id="uploadCsvModal" tabindex="-1" aria-labelledby="uploadCsvModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="uploadCsvModalLabel">Unggah File CSV Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf
                        <p class="text-muted">Pastikan file Anda menggunakan <strong>semicolon (;) </strong>sebagai delimiter dan format tanggal d/m/Y.</p>
                        <div class="mb-3">
                            <label for="csv_files" class="form-label">Pilih File CSV (Boleh lebih dari satu)</label>
                            <input type="file" name="csv_files[]" multiple class="form-control" id="csv_files" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-1"></i> Upload & Proses Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Change #3: Modal Size Changed to modal-xl --}}
    <div class="modal fade" id="pivotDetailModal" tabindex="-1" aria-labelledby="pivotDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl"> 
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="pivotDetailModalLabel">Detail Transaksi Item + Budget</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="detail-loading" class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat data transaksi dan budget...</p>
                    </div>
                    <div id="detail-content" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <p class="fw-bold mb-1">Item:</p>
                                <p class="mb-2 ps-2" id="detail-item-info"></p>
                                <p class="fw-bold mb-1">Combined Total (Qty + Budget):</p>
                                <p class="mb-0 ps-2 fw-bold" id="detail-total-info"></p>
                            </div>
                            {{-- Change #4: Download Button --}}
                            <div>
                                <button id="downloadModalDataBtn" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-download me-1"></i> Download Detail CSV
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 50vh;">
                            <div id="detail-table-container"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    {{-- ADDED: Custom Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteConfirmModalLabel"><i class="fas fa-exclamation-triangle me-2"></i> Konfirmasi Penghapusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-2 fw-bold text-danger">Anda yakin ingin menghapus data ini?</p>
                    <p class="small text-muted mb-3">Aksi ini tidak dapat dibatalkan. Data yang dihapus:</p>
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
// Global variable to hold the form reference
let deleteFormToSubmit = null;

$(document).on('click', '.delete-btn', function(e) {
    e.preventDefault();
    
    // Store the form element
    deleteFormToSubmit = $(this).closest('form');
    
    // Get description data from the button's data attributes
    const recordDesc = $(this).data('record-desc') || 'Record ID: ' + $(this).data('record-id');
    
    // Update modal content
    $('#deleteRecordDesc').text(recordDesc);
    
    // Show modal
    $('#deleteConfirmModal').modal('show');
});

$('#confirmDeleteBtn').on('click', function() {
    if (deleteFormToSubmit) {
        // Hide modal before submission
        $('#deleteConfirmModal').modal('hide');
        // Submit the stored form
        deleteFormToSubmit.submit();
    }
});
</script>

<script>
    // Utility function to convert table HTML to CSV string and download
    function exportTableToCSV(tableId, itemKey, delimiter = ';') {
        const $table = $('#' + tableId);
        let csv = '';
        const itemInfo = itemKey.split('||');
        
        // 1. Add Item Info Header
        csv += `"Item Number";"${itemInfo[0]}"\n`;
        csv += `"Item Description";"${itemInfo[1]}"\n`;
        csv += `"UOM";"${itemInfo[2]}"\n`;
        csv += `"DEPT";"${itemInfo[3]}"\n`;
        csv += '\n';

        // Helper to extract text and sanitize
        const sanitize = (text) => {
            // Remove thousand separators, remove leading/trailing spaces, replace line breaks
            let cleaned = text.replace(/(\r\n|\n|\r)/gm, " ").trim();
            // Handle numerical values (removes ID locale formatting for numbers)
            let match = cleaned.match(/^-?[\d.,\s]+$/);
            if (match) {
                // Remove ID locale thousand separators (.), replace comma decimal (,) with dot
                cleaned = cleaned.replace(/\./g, '').replace(/,/g, '.');
            }
            return `"${cleaned.replace(/"/g, '""')}"`;
        };

        // 2. Extract Table Headers (thead)
        $table.find('thead th').each(function() {
            csv += sanitize($(this).text()) + delimiter;
        });
        csv = csv.slice(0, -1) + '\n';

        // 3. Extract Table Body (tbody)
        $table.find('tbody tr').each(function() {
            $(this).find('td').each(function() {
                csv += sanitize($(this).text()) + delimiter;
            });
            csv = csv.slice(0, -1) + '\n';
        });

        // 4. Extract Table Footer (tfoot)
        $table.find('tfoot tr').each(function() {
            $(this).find('td').each(function() {
                csv += sanitize($(this).text()) + delimiter;
            });
            csv = csv.slice(0, -1) + '\n';
        });

        const filename = 'Resume_Detail_' + itemInfo[0] + '_' + new Date().toISOString().slice(0, 10) + '.csv';
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        
        if (navigator.msSaveBlob) { // IE 10+
            navigator.msSaveBlob(blob, filename);
        } else {
            const link = document.createElement('a');
            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', filename);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
    }

$(function() {
    const selectedPivot = @json($pivot_months ?? []);
    const distinctYearMonths = @json($distinctYearMonths ?? []);
    const mode = '{{ $mode }}';

    function updateYearsLabel(selector, labelId) {
        const checked = $(selector + ':checked');
        let label = 'Pilih Tahun';
        if (checked.length === 1) {
            label = checked.val();
        } else if (checked.length > 1) {
            label = checked.length + ' Tahun terpilih';
        }
        $(labelId).text(label);
    }

    function updateMonthsCount() {
        const count = ($('.monthly-month-checkbox:checked').length) || 0;
        $('#months-selected-count').text(count + ' Bulan terpilih');
    }

    function rebuildPivotHiddenInputs() {
        $('input[name="pivot_months[]"]').remove();

        const yearlyYears = $('.yearly-year-checkbox:checked').map(function(){ return $(this).val(); }).get() || [];
        const yearlyMode = $('#yearlyMode').val() || 'total';
        yearlyYears.forEach(function(y) {
            const val = 'YEARLY-' + y + '|' + yearlyMode;
            $('<input>').attr({type: 'hidden', name: 'pivot_months[]', value: val}).appendTo('#filterForm');
            $('<input>').attr({type: 'hidden', name: 'pivot_months[]', value: val}).appendTo('#exportForm');
        });

        const monthly = $('.monthly-month-checkbox:checked').map(function(){ return $(this).val(); }).get() || [];
        monthly.forEach(function(ym) {
            $('<input>').attr({type: 'hidden', name: 'pivot_months[]', value: ym}).appendTo('#filterForm');
            $('<input>').attr({type: 'hidden', name: 'pivot_months[]', value: ym}).appendTo('#exportForm');
        });
    }

    function syncMonthlyGroupsVisibility() {
        const selYears = $('.monthly-year-checkbox:checked').map(function(){ return $(this).val(); }).get() || [];
        $('.monthly-year-group').each(function(){
            const y = $(this).data('year') + '';
            if (selYears.indexOf(y) !== -1) $(this).show();
            else $(this).hide().find('.monthly-month-checkbox').prop('checked', false);
        });
    }

    if (mode === 'resume') {
        $('input[name="item_number_term"]').on('input', function() { $(this).val($(this).val().toUpperCase()); });
        $('input[name="item_description_term"]').on('input', function() { $(this).val($(this).val().toUpperCase()); });

        $('.yearly-year-checkbox').on('change', function() {
            updateYearsLabel('.yearly-year-checkbox', '#yearlyYearsLabel');
            rebuildPivotHiddenInputs();
        });
        $('#yearlyMode').on('change', function() {
            rebuildPivotHiddenInputs();
        });
        $('.dropdown-menu').on('click', function (e) {
            e.stopPropagation();
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

        function syncFromServerPivot() {
            const yearlyYears = [];
            const monthlyYears = [];
            const monthVals = [];
            let yearlyMode = 'total';

            selectedPivot.forEach(function(p) {
                if (String(p).startsWith('YEARLY-')) {
                    const parts = String(p).replace('YEARLY-','').split('|');
                    const y = parts[0];
                    yearlyYears.push(y);
                    if (parts.length > 1) {
                        yearlyMode = parts[1];
                    }
                } else if (/^\d{4}-\d{2}$/.test(String(p))) {
                    monthVals.push(String(p));
                }
            });

            yearlyYears.forEach(function(y) {
                $('#year_yearly_' + y).prop('checked', true);
            });
            $('#yearlyMode').val(yearlyMode);
            updateYearsLabel('.yearly-year-checkbox', '#yearlyYearsLabel');

            monthVals.forEach(function(m){
                const year = m.slice(0,4);
                if (monthlyYears.indexOf(year) === -1) {
                    monthlyYears.push(year);
                }
                $('#month_' + m).prop('checked', true);
            });
            monthlyYears.forEach(function(y){
                 $('#year_monthly_' + y).prop('checked', true);
            });
            updateYearsLabel('.monthly-year-checkbox', '#monthlyYearsLabel');

            syncMonthlyGroupsVisibility();
            rebuildPivotHiddenInputs();
            updateMonthsCount();
        }
        syncFromServerPivot();

        const $detailModal = $('#pivotDetailModal');
        const currentUrl = '{{ route('items.index') }}';
        const pivotMonths = @json($months ?? []);
        
        function formatQty(qty) { 
            const n = parseFloat(qty) || 0; 
            // Use minimumFractionDigits to handle averages cleanly, but keep it minimal
            return n.toLocaleString('id-ID', { minimumFractionDigits: (n % 1 === 0 ? 0 : 2), maximumFractionDigits: 2 }); 
        }

        function getColorClass(qty) {
            // Updated to use text-success for positive, text-danger fw-bold for negative
            return qty < 0 ? 'text-danger fw-bold' : 'text-success';
        }

        function escapeHtml(unsafe) { 
            return String(unsafe)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;"); 
        }
        
        $(document).on('click', '.resume-row-clickable td:not(.select-cell)', function(event) {
            const $row = $(this).closest('.resume-row-clickable');
            const itemKey = $row.data('item-key') || '';
            const idList = $row.data('id-list') || '';
            let budgetDataCache = {}; 
            let itemQtyDataCache = {}; 

            if (!idList) return;
            $('#detail-content').hide();
            $('#detail-loading').show();
            $detailModal.modal('show');
            const parts = itemKey.split('||');
            const itemNumber = parts[0] || '';
            const itemDesc = parts[1] || '';
            const uom = parts[2] || '';
            const dept = parts[3] || '';
            $('#detail-item-info').text(itemNumber + ' - ' + itemDesc + ' (' + uom + ') - DEPT: ' + dept);
            $('#detail-total-info').text('');
            $('#downloadModalDataBtn').data('id-list', idList).data('item-key', itemKey); 

            $('#detail-table-container').html('<table class="table table-striped table-bordered table-sm"><thead class="sticky-top bg-light"><tr><th>Remark</th></tr></thead><tbody id="detail-table-body"></tbody></table>');
            
            $.ajax({
                url: currentUrl,
                type: 'GET',
                dataType: 'json',
                data: { action: 'pivot_row_details', item_key: itemKey, id_list: idList },
                success: function(response) {
                    
                    const displayKeys = pivotMonths.map(function(m){ return String(m.key); });
                    const displayLabels = pivotMonths.map(function(m){ return String(m.label); });
                    const monthKeys = displayKeys.filter(function(k){ return !k.startsWith('YEARLY-'); });
                    const monthLabels = displayLabels.filter(function(_, i){ return !displayKeys[i].startsWith('YEARLY-'); });
                    const yearlyKeys = displayKeys.filter(function(k){ return k.startsWith('YEARLY-'); });
                    const yearlyLabels = displayLabels.filter(function(_, i){ return displayKeys[i].startsWith('YEARLY-'); });
                    
                    const isMonthlyFilterActive = monthKeys.length > 0;
                    
                    const groups = {}; // Item transaction grouped by remark
                    let grandItemQtyTotal = 0;
                    
                    if (Array.isArray(response.details) && response.details.length > 0) {
                        response.details.forEach(function(detail) {
                            const remark = (detail.remarks || '').trim() || '(No Remark)';
                            const mkey = detail.effective_date ? detail.effective_date.slice(0,7) : '';
                            const qty = parseFloat(detail.loc_qty_change) || 0; // Use float for precision
                            
                            if (!itemQtyDataCache[mkey]) itemQtyDataCache[mkey] = { qty: 0, annual_months_set: {} };
                            itemQtyDataCache[mkey].qty += qty;
                            itemQtyDataCache[mkey].annual_months_set[mkey] = true;

                            if (!groups[remark]) groups[remark] = { months: {}, total: 0, annual_totals: {}, annual_months_set: {} };
                            groups[remark].months[mkey] = (groups[remark].months[mkey] || 0) + qty;
                            const year = String(mkey).slice(0,4);
                            groups[remark].annual_totals[year] = (groups[remark].annual_totals[year] || 0) + qty;
                            groups[remark].annual_months_set[year] = groups[remark].annual_months_set[year] || {};
                            if (mkey) groups[remark].annual_months_set[year][mkey] = true;
                            groups[remark].total += qty;
                            grandItemQtyTotal += qty;
                        });
                    }

                    const budgetByMonth = response.budget_data || {};
                    let grandBudgetTotal = 0;
                    let annualBudgetTotals = {};
                    let annualBudgetMonthsCount = {};
                    
                    Object.keys(budgetByMonth).forEach(function(mkey) {
                        const budgetVal = parseFloat(budgetByMonth[mkey]) || 0;
                        const year = String(mkey).slice(0,4);
                        grandBudgetTotal += budgetVal;
                        annualBudgetTotals[year] = (annualBudgetTotals[year] || 0) + budgetVal;
                        annualBudgetMonthsCount[year] = annualBudgetMonthsCount[year] || {};
                        annualBudgetMonthsCount[year][mkey] = true;
                        budgetDataCache[mkey] = { budget: budgetVal, annual_months_set: {} };
                        budgetDataCache[mkey].annual_months_set[mkey] = true;
                    });
                    
                    const grandCombinedTotal = grandItemQtyTotal + grandBudgetTotal;
                    
                    // --- FIX HERE: Separate logic for NEGATIVE total vs. comparison total ---
                    let totalColorClass;
                    if (grandCombinedTotal < 0) {
                        // If the combined total is negative, it MUST be red.
                        totalColorClass = 'text-danger fw-bold'; 
                    } else if (grandCombinedTotal > 0) {
                        // If positive, apply the Budget vs Qty comparison logic
                        totalColorClass = (grandBudgetTotal > grandItemQtyTotal) ? 'text-success' : 
                                            (grandBudgetTotal < grandItemQtyTotal) ? 'text-danger fw-bold' : 'text-success'; // If equal or budget > qty, use green/success
                    } else {
                        // Total is zero
                        totalColorClass = ''; 
                    }
                    // ------------------------------------------------------------------------
                    
                    if (grandItemQtyTotal !== 0 || grandBudgetTotal !== 0) {

                        // START: Filtered Monthly/Yearly Display

                        const isAnyFilterActive = monthKeys.length > 0 || yearlyKeys.length > 0;

                        if (isAnyFilterActive) {
                            let thead = '<tr><th>Remark / Source</th>';
                            monthLabels.forEach(function(label) { thead += '<th class="text-center text-nowrap">' + escapeHtml(label) + '</th>'; });
                            yearlyLabels.forEach(function(label) { thead += '<th class="text-center text-nowrap">' + escapeHtml(label) + '</th>'; });
                            thead += '<th class="text-end">Total</th></tr>';
                            let tbodyHtml = '';
                            
                            // Calculate combined totals for the combined row (footer)
                            let monthlyCombinedTotals = {};
                            let yearlyCombinedTotals = {};
                            
                            // --- Calculate Monthly Totals (Item Qty + Budget) ---
                            monthKeys.forEach(function(k) {
                                const itemVal = itemQtyDataCache[k] ? itemQtyDataCache[k].qty : 0;
                                const budgetVal = budgetDataCache[k] ? budgetDataCache[k].budget : 0;
                                monthlyCombinedTotals[k] = itemVal + budgetVal;
                            });

                            // --- Calculate Yearly Totals (Item Qty + Budget) ---
                            yearlyKeys.forEach(function(yearlyKey) {
                                const keyParts = yearlyKey.replace('YEARLY-', '').split('|');
                                const year = keyParts[0];
                                const type = keyParts[1] || 'total';
                                
                                // Aggregate Item Qty for the year
                                let totalItemForYear = Object.values(groups).reduce((acc, g) => acc + (g.annual_totals[year] || 0), 0);
                                // Get Budget Total for the year
                                let totalBudgetForYear = annualBudgetTotals[year] || 0;
                                let totalVal = totalItemForYear + totalBudgetForYear;
                                
                                if (type === 'avg') {
                                    // Calculate distinct months count based on both data sets
                                    let distinctMonthsSet = {};
                                    Object.values(groups).forEach(g => {
                                        Object.keys(g.annual_months_set[year] || {}).forEach(m => distinctMonthsSet[m] = true);
                                    });
                                    Object.keys(budgetDataCache).forEach(mkey => {
                                        if (mkey.startsWith(year)) distinctMonthsSet[mkey] = true;
                                    });
                                    
                                    const distinctMonthsCount = Object.keys(distinctMonthsSet).length;
                                    totalVal = distinctMonthsCount ? (totalVal / distinctMonthsCount) : 0;
                                }
                                yearlyCombinedTotals[yearlyKey] = totalVal;
                            });

                            
                            // --- Item Transaction (Grouped by Remark) Rows ---
                            Object.keys(groups).forEach(function(remark) {
                                const g = groups[remark];
                                tbodyHtml += '<tr><td style="min-width:220px; font-style: italic; color: #555;">' + escapeHtml(remark) + '</td>';
                                let rowTotal = 0;
                                
                                monthKeys.forEach(function(k) {
                                    const val = g.months[k] || 0;
                                    rowTotal += val;
                                    const cls = getColorClass(val); // Use consistent color function
                                    tbodyHtml += '<td class="text-end font-monospace ' + cls + '">' + formatQty(val) + '</td>';
                                });

                                yearlyKeys.forEach(function(yearlyKey) {
                                    const keyParts = yearlyKey.replace('YEARLY-', '').split('|');
                                    const year = keyParts[0];
                                    const type = keyParts[1] || 'total';
                                    const annualTotal = (g.annual_totals && g.annual_totals[year]) ? g.annual_totals[year] : 0;
                                    let val = annualTotal;
                                    if (type === 'avg') {
                                        const distinctMonthsCount = (g.annual_months_set && g.annual_months_set[year]) ? Object.keys(g.annual_months_set[year]).length : 0;
                                        val = distinctMonthsCount ? (annualTotal / distinctMonthsCount) : 0;
                                    }
                                    
                                    const cls = getColorClass(val); // Use consistent color function
                                    tbodyHtml += '<td class="text-end font-monospace ' + cls + '">' + formatQty(val) + '</td>';
                                });

                                // FIX: Calculate row total using selected keys, prioritizing yearly if monthly is empty
                                if (isMonthlyFilterActive) {
                                    // If monthly active, rowTotal is already calculated by the loop above (only iterating monthKeys)
                                } else if (yearlyKeys.length > 0) {
                                    // If only yearly active, sum the yearly total columns (based on selected years)
                                    rowTotal = yearlyKeys.reduce((totalAcc, yk) => {
                                         const yearOnly = yk.replace('YEARLY-', '').split('|')[0];
                                         return totalAcc + (g.annual_totals[yearOnly] || 0); 
                                    }, 0);
                                } else {
                                     // Default to grand item total if no pivot is active (shouldn't happen here if isAnyFilterActive is true)
                                     rowTotal = g.total;
                                }
                                
                                // REMOVED bg-light class from here
                                tbodyHtml += '<td class="text-end fw-bold font-monospace ' + getColorClass(rowTotal) + '">' + formatQty(rowTotal) + '</td></tr>';
                            });
                            
                            // --- Item Qty Totals Row (Aggregated) ---
                            // REMOVED bg-warning-subtle class
                            let itemQtyTotalRow = '<tr><td class="fw-bold">TOTAL ITEM QTY (MONTHS)</td>';
                            
                            monthKeys.forEach(function(k) {
                                const val = itemQtyDataCache[k] ? itemQtyDataCache[k].qty : 0;
                                itemQtyTotalRow += '<td class="text-end fw-bold font-monospace ' + getColorClass(val) + '">' + formatQty(val) + '</td>';
                            });
                            
                            yearlyKeys.forEach(function(yearlyKey) {
                                const keyParts = yearlyKey.replace('YEARLY-', '').split('|');
                                const year = keyParts[0];
                                const type = keyParts[1] || 'total';
                                
                                let annualTotal = Object.values(groups).reduce((acc, g) => acc + (g.annual_totals[year] || 0), 0);
                                let val = annualTotal;

                                if (type === 'avg') {
                                    let distinctMonthsSet = {};
                                    Object.values(groups).forEach(g => {
                                        Object.keys(g.annual_months_set[year] || {}).forEach(m => distinctMonthsSet[m] = true);
                                    });
                                    const distinctMonthsCount = Object.keys(distinctMonthsSet).length;
                                    val = distinctMonthsCount ? (annualTotal / distinctMonthsCount) : 0;
                                }
                                itemQtyTotalRow += '<td class="text-end fw-bold font-monospace ' + getColorClass(val) + '">' + formatQty(val) + '</td>';
                            });
                            
                            // REMOVED bg-dark text-white class
                            itemQtyTotalRow += `<td class="text-end fw-bold font-monospace ${getColorClass(grandItemQtyTotal)}">${formatQty(grandItemQtyTotal)}</td></tr>`;
                            tbodyHtml += itemQtyTotalRow;


                            // --- Budget Totals Row (Aggregated) ---
                            if (grandBudgetTotal !== 0) {
                                // REMOVED bg-primary-subtle class
                                let budgetTotalRow = '<tr><td class="fw-bold text-nowrap">TOTAL BUDGET (MONTHS)</td>';
                                
                                monthKeys.forEach(function(k) {
                                    const val = budgetDataCache[k] ? budgetDataCache[k].budget : 0;
                                    const cls = getColorClass(val);
                                    budgetTotalRow += '<td class="text-end font-monospace ' + cls + '">' + formatQty(val) + '</td>';
                                });
                                
                                yearlyKeys.forEach(function(yearlyKey) {
                                    const keyParts = yearlyKey.replace('YEARLY-', '').split('|');
                                    const year = keyParts[0];
                                    const type = keyParts[1] || 'total';
                                    const annualTotal = annualBudgetTotals[year] || 0;
                                    let val = annualTotal;
                                    if (type === 'avg') {
                                        const distinctMonthsCount = (annualBudgetMonthsCount[year] ? Object.keys(annualBudgetMonthsCount[year]).length : 0);
                                        val = distinctMonthsCount ? (annualTotal / distinctMonthsCount) : 0;
                                    }
                                    const cls = getColorClass(val);
                                    budgetTotalRow += '<td class="text-end font-monospace ' + cls + '">' + formatQty(val) + '</td>';
                                });

                                // REMOVED bg-dark text-white class
                                budgetTotalRow += `<td class="text-end fw-bold font-monospace ${getColorClass(grandBudgetTotal)}">${formatQty(grandBudgetTotal)}</td></tr>`;
                                tbodyHtml += budgetTotalRow;
                            }


                            // Calculate Combined Total Row (Footer)
                            // REMOVED bg-warning class
                            let combinedTotalRow = '<tr><td class="fw-bold">Combined Monthly/Annual Total</td>';
                            
                            monthKeys.forEach(function(k) {
                                const val = monthlyCombinedTotals[k] || 0;
                                combinedTotalRow += '<td class="text-end fw-bold font-monospace ' + getColorClass(val) + '">' + formatQty(val) + '</td>';
                            });
                            
                            yearlyKeys.forEach(function(k) {
                                const val = yearlyCombinedTotals[k] || 0;
                                combinedTotalRow += '<td class="text-end fw-bold font-monospace ' + getColorClass(val) + '">' + formatQty(val) + '</td>';
                            });
                            
                            // REMOVED bg-secondary text-white class
                            combinedTotalRow += '<td class="text-end fw-bold font-monospace ' + getColorClass(grandCombinedTotal) + '">' + formatQty(grandCombinedTotal) + '</td></tr>';
                            
                            // Added ID for client-side export
                            const tableHtml = '<table id="detailTable" class="table table-striped table-bordered table-sm mb-0"><thead class="sticky-top bg-light">' + thead + '</thead><tbody>' + tbodyHtml + '</tbody><tfoot>' + combinedTotalRow + '</tfoot></table>';
                            
                            $('#detail-table-container').html(tableHtml);

                        } else {
                            // START: Simplified Totals Display (All Time - No Filter)
                            let thead = '<tr><th>Remark / Source</th><th class="text-end">Total Qty</th></tr>';
                            let tbodyHtml = '';
                            
                            Object.keys(groups).forEach(function(remark) {
                                const g = groups[remark];
                                const itemCls = getColorClass(g.total);
                                tbodyHtml += '<tr><td style="min-width:220px; font-style: italic; color: #555;">' + escapeHtml(remark) + '</td>';
                                tbodyHtml += '<td class="text-end fw-bold font-monospace ' + itemCls + '">' + formatQty(g.total) + '</td></tr>';
                            });

                            // NEW FIX: Add Total Item Qty (All Time)
                            // REMOVED bg-warning-subtle class
                            const grandItemQtyCls = getColorClass(grandItemQtyTotal);
                            tbodyHtml += '<tr><td>TOTAL ITEM QTY (All Time)</td>';
                            tbodyHtml += '<td class="text-end fw-bold font-monospace ' + grandItemQtyCls + '">' + formatQty(grandItemQtyTotal) + '</td></tr>';
                            
                            // Budget Row
                            if (grandBudgetTotal !== 0) {
                                // REMOVED bg-primary-subtle class
                                const budgetCls = getColorClass(grandBudgetTotal);
                                tbodyHtml += '<tr><td>Total Budget Allocated (ADDITION)</td>';
                                tbodyHtml += '<td class="text-end fw-bold font-monospace ' + budgetCls + '">' + formatQty(grandBudgetTotal) + '</td></tr>';
                            }

                            const grandCls = getColorClass(grandCombinedTotal);
                            // REMOVED bg-warning class
                            let combinedTotalRow = '<tr><td>Combined Grand Total (All Time)</td>';
                            // REMOVED bg-dark text-white class
                            combinedTotalRow += '<td class="text-end fw-bold font-monospace ' + grandCls + '">' + formatQty(grandCombinedTotal) + '</td></tr>';

                            // Added ID for client-side export
                            const tableHtml = '<table id="detailTable" class="table table-striped table-bordered table-sm mb-0"><thead class="sticky-top bg-light">' + thead + '</thead><tbody>' + tbodyHtml + '</tbody><tfoot>' + combinedTotalRow + '</tfoot></table>';

                            $('#detail-table-container').html(tableHtml);
                            // END: Simplified Totals Display
                        }
                    } else {
                        const emptyHtml = '<div class="text-center text-muted p-3">Tidak ada transaksi detail maupun data budget yang ditemukan.</div>';
                        $('#detail-table-container').html(emptyHtml);
                        $('#detail-total-info').text(formatQty(0)).removeClass('text-danger text-success').addClass('text-success');
                    }
                    
                    $('#detail-total-info').text(formatQty(grandCombinedTotal)).removeClass('text-danger text-success fw-bold').addClass(totalColorClass);
                    
                    $('#detail-loading').hide();
                    $('#detail-content').show();
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'Gagal memuat data detail. Cek log server untuk detail.';
                    try {
                        const err = JSON.parse(xhr.responseText);
                        errorMessage = err.error || errorMessage;
                    } catch (e) {}
                    $('#detail-table-container').html('<div class="text-center text-danger p-3">' + errorMessage + '</div>');
                    $('#detail-loading').hide();
                    $('#detail-content').show();
                }
            });
        });

        // UPDATED: Download Button Handler now uses client-side export
        $('#downloadModalDataBtn').on('click', function() {
            const itemKey = $(this).data('item-key');
            if (!$('#detailTable').length) {
                alert('Tidak ada data tabel yang tersedia untuk diunduh.');
                return;
            }
            // Use the client-side function to export the visible table
            exportTableToCSV('detailTable', itemKey, ';'); 
        });
    }

    $('#select-all-details').on('change', function() { $('.select-detail').prop('checked', $(this).is(':checked')); });
    $('#select-all-resume').on('change', function() { $('.select-resume').prop('checked', $(this).is(':checked')); });
    $(document).on('click', '.select-resume', function(e) { e.stopPropagation(); });

    $('#exportBtn').on('click', function() {
        if (mode === 'details') {
            const selected = $('.select-detail:checked').map(function(){ return $(this).val(); }).get();
            if (selected.length === 0) { alert('Please select at least one row to export.'); return; }
            $('#exportForm').find('input[name="selected_ids[]"]').remove();
            selected.forEach(function(val){ $('<input>').attr({type:'hidden', name:'selected_ids[]', value: val}).appendTo('#exportForm'); });
            $('#exportForm')[0].submit();
        } else {
            const selected = $('.select-resume:checked').map(function(){ return $(this).val(); }).get();
            if (selected.length === 0) { alert('Pilih setidaknya satu baris untuk diunduh.'); return; }
            const monthsParam = [];
            const hiddenPivot = $('input[name="pivot_months[]"]').map(function(){ return $(this).val(); }).get();
            hiddenPivot.forEach(function(h){ if (!h.startsWith('YEARLY-')) monthsParam.push(h); });
            const params = { id_lists: selected.join('||'), months: monthsParam.join(',') };
            window.location.href = '{{ route('items.exportResumeDetail') }}?' + $.param(params);
        }
    });
});
</script>
@endsection