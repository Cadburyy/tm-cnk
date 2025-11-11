@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-dark">📊 Data Transaksi Item</h1>
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

    <form method="GET" action="{{ route('items.index') }}" class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light fw-bold">Pilihan Filter Data</div>
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
                                <input type="text" name="item_number_term" value="{{ $item_number_term }}" class="form-control form-control-sm" placeholder="Cari Item No">
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label">Item Group</label>
                                <input type="text" name="item_group_term" value="{{ $item_group_term }}" class="form-control form-control-sm" placeholder="Cari Grup">
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label">DEPT</label>
                                <input type="text" name="dept_term" value="{{ $dept_term }}" class="form-control form-control-sm" placeholder="Cari DEPT">
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label class="form-label">Item Description</label>
                                <input type="text" name="item_description_term" value="{{ $item_description_term }}" class="form-control form-control-sm" placeholder="Cari Deskripsi">
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label">Remarks</label>
                                <input type="text" name="remarks_term" value="{{ $remarks_term }}" class="form-control form-control-sm" placeholder="Cari Remarks">
                            </div>
                        </div>
                    @else
                        <div class="row g-3">
                            <div class="col-lg-6 col-md-12">
                                <label class="form-label fw-bold">Pilih Bulan & Tahun untuk Pivot Table</label>
                                <div class="dropdown">
                                    <button class="btn btn-outline-info dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        {{ count($pivot_months) > 0 ? count($pivot_months) . ' Bulan/Tahun terpilih' : 'Pilih Bulan/Tahun...' }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-start p-3" style="max-height: 400px; overflow-y: auto;">
                                        @forelse($distinctYears as $year)
                                            <li>
                                                <div class="dropdown-header fw-bold border-bottom mb-1">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="pivot_months[]" id="avg_{{ $year }}" value="AVG-{{ $year }}" @checked(in_array('AVG-' . $year, $pivot_months))>
                                                        <label class="form-check-label text-primary" for="avg_{{ $year }}">
                                                            Average Tahun {{ $year }} (Avg {{ substr($year, 2, 2) }})
                                                        </label>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="d-flex flex-wrap gap-2 ps-3 mb-2">
                                                @foreach($distinctYearMonths->get($year, collect()) as $ymValue)
                                                    <div class="form-check form-check-inline m-0">
                                                        <input class="form-check-input" type="checkbox" name="pivot_months[]" id="ym_{{ $ymValue }}" value="{{ $ymValue }}" @checked(in_array($ymValue, $pivot_months))>
                                                        <label class="form-check-label" for="ym_{{ $ymValue }}" style="width: 35px;">
                                                            {{ ['01' => 'Jan','02' => 'Feb','03' => 'Mar','04' => 'Apr','05' => 'Mei','06' => 'Jun','07' => 'Jul','08' => 'Agu','09' => 'Sep','10' => 'Okt','11' => 'Nov','12' => 'Des'][substr($ymValue,5,2)] }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </li>
                                        @empty
                                            <li><span class="dropdown-item-text text-muted">Tidak ada data efektif tanggal di database.</span></li>
                                        @endforelse
                                        <li><hr class="dropdown-divider"></li>
                                        <li><span class="dropdown-item-text text-muted">Klik di luar untuk menutup.</span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label">Item Number</label>
                                <input type="text" name="item_number_term" value="{{ $item_number_term }}" class="form-control form-control-sm" placeholder="Cari Item No">
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label">Item Group</label>
                                <input type="text" name="item_group_term" value="{{ $item_group_term }}" class="form-control form-control-sm" placeholder="Cari Grup">
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label">DEPT</label>
                                <input type="text" name="dept_term" value="{{ $dept_term }}" class="form-control form-control-sm" placeholder="Cari DEPT">
                            </div>

                            <div class="col-lg-6">
                                <label class="form-label">Item Description</label>
                                <input type="text" name="item_description_term" value="{{ $item_description_term }}" class="form-control form-control-sm" placeholder="Cari Deskripsi">
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label">Remarks</label>
                                <input type="text" name="remarks_term" value="{{ $remarks_term }}" class="form-control form-control-sm" placeholder="Cari Remarks">
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card-footer d-flex justify-content-end gap-2">
                    <button type="button" id="exportBtn" class="btn align-left btn-outline-primary">
                        <i class="fas fa-download me-1"></i> Download Selected XLSX
                    </button>

                    <input type="hidden" name="mode" value="{{ $mode }}">
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
        @if(Auth::check() && (method_exists(Auth::user(), 'hasRole') ? Auth::user()->hasRole('Admin') : (Auth::user()->is_admin ?? false)))
            <a href="{{ route('items.index', array_merge(request()->query(), ['mode' => 'details'])) }}" class="btn {{ $mode == 'details' ? 'btn-info text-white shadow-lg' : 'btn-outline-info' }}">
                <i class="fas fa-list-ul me-1"></i> Details (All Records)
            </a>
        @endif
        <a href="{{ route('items.index', array_merge(request()->query(), ['mode' => 'resume'])) }}" class="btn {{ $mode == 'resume' ? 'btn-info text-white shadow-lg' : 'btn-outline-info' }}">
            <i class="fas fa-table me-1"></i> Resume (Monthly Pivot)
        </a>
    </div>

    <form id="exportForm" method="POST" action="{{ route('items.exportSelected') }}">
        @csrf
        <input type="hidden" name="mode" id="exportMode" value="{{ $mode }}">
        @foreach($months as $m)
            <input type="hidden" name="months[]" value="{{ $m['key'] }}">
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
                                        <th class="text-nowrap">Item Number</th>
                                        <th class="text-nowrap">Item Description</th>
                                        <th class="text-nowrap">Effective Date</th>
                                        <th>Bulan</th>
                                        <th class="text-nowrap text-end">Loc Qty Change</th>
                                        <th>UOM</th>
                                        <th class="text-nowrap">Remarks</th>
                                        <th class="text-nowrap">Item Group</th>
                                        <th>DEPT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        <tr>
                                            <td><input type="checkbox" class="select-detail" value="{{ $item->id }}"></td>
                                            <td class="text-nowrap">{{ $item->item_number }}</td>
                                            <td style="max-width:250px;">{{ $item->item_description }}</td>
                                            <td class="text-nowrap">
                                                @if ($item->effective_date instanceof \DateTime || $item->effective_date instanceof \Carbon\Carbon)
                                                    {{ $item->effective_date->format('d/m/Y') }}
                                                @else
                                                    {{ \Carbon\Carbon::parse($item->effective_date)->format('d/m/Y') }}
                                                @endif
                                            </td>
                                            <td>{{ $item->bulan }}</td>
                                            <td class="text-end font-monospace {{ $item->loc_qty_change < 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                                {{ number_format($item->loc_qty_change, 2, ',', '.') }}
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
                                        <th class="text-nowrap">Item Description</th>
                                        <th class="text-nowrap">UOM</th>
                                        @if (count($months) > 0)
                                            @foreach($months as $m)
                                                <th class="text-nowrap text-center">{{ $m['label'] }}</th>
                                            @endforeach
                                        @endif
                                        <th class="text-nowrap text-center">Total Qty</th>
                                        <th class="text-nowrap">DEPT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($summary_rows as $row)
                                        <tr class="resume-row-clickable" data-item-key="{{ $row['item_number'] }}||{{ $row['item_description'] }}||{{ $row['unit_of_measure'] }}||{{ $row['dept'] }}" data-id-list="{{ $row['row_ids'] ?? '' }}" style="cursor: pointer;" title="Klik untuk melihat detail semua transaksi item ini">
                                            <td class="select-cell"><input type="checkbox" class="select-resume" value="{{ $row['row_ids'] ?? '' }}"></td>
                                            <td class="text-nowrap">{{ $row['item_number'] }}</td>
                                            <td style="max-width:300px;">{{ $row['item_description'] }}</td>
                                            <td>{{ $row['unit_of_measure'] }}</td>
                                            @if (count($months) > 0)
                                                @foreach($months as $m)
                                                    <td class="text-end font-monospace">{{ number_format($row['months'][$m['key']] ?? 0, 2, ',', '.') }}</td>
                                                @endforeach
                                            @endif
                                            <td class="text-end fw-bold font-monospace bg-light">{{ number_format($row['total'], 2, ',', '.') }}</td>
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

    <div class="modal fade" id="pivotDetailModal" tabindex="-1" aria-labelledby="pivotDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="pivotDetailModalLabel">Detail Transaksi Item (Resume)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="detail-loading" class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat data transaksi...</p>
                    </div>
                    <div id="detail-content" style="display: none;">
                        <p class="fw-bold mb-1">Item:</p>
                        <p class="mb-2 ps-2" id="detail-item-info"></p>
                        <p class="fw-bold mb-1">Total Kuantitas:</p>
                        <p class="mb-3 ps-2 fw-bold" id="detail-total-info"></p>
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
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
    const $detailModal = $('#pivotDetailModal');
    const $detailLoading = $('#detail-loading');
    const $detailContent = $('#detail-content');
    const $detailItemInfo = $('#detail-item-info');
    const $detailTotalInfo = $('#detail-total-info');
    const currentUrl = '{{ route('items.index') }}';
    const pivotMonths = @json($months ?? []);

    function formatQty(qty) {
        const n = parseFloat(qty) || 0;
        return n.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    $(document).on('click', '.resume-row-clickable td:not(.select-cell)', function(event) {
        const $row = $(this).closest('.resume-row-clickable');
        const itemKey = $row.data('item-key') || '';
        const idList = $row.data('id-list') || '';
        if (!idList) return;
        $detailContent.hide();
        $detailLoading.show();
        $detailModal.modal('show');
        const parts = itemKey.split('||');
        const itemNumber = parts[0] || '';
        const itemDesc = parts[1] || '';
        const uom = parts[2] || '';
        const dept = parts[3] || '';
        $detailItemInfo.text(itemNumber + ' - ' + itemDesc + ' (' + uom + ') - DEPT: ' + dept);
        $detailTotalInfo.text('');
        $('#detail-table-container').html('<table class="table table-striped table-bordered table-sm"><thead class="sticky-top bg-light"><tr><th>Remark (Month chosen)</th></tr></thead><tbody id="detail-table-body"></tbody></table>');
        $.ajax({
            url: currentUrl,
            type: 'GET',
            dataType: 'json',
            data: {
                action: 'pivot_row_details',
                item_key: itemKey,
                id_list: idList
            },
            success: function(response) {
                const displayKeys = pivotMonths.map(function(m){ return String(m.key); });
                const displayLabels = pivotMonths.map(function(m){ return String(m.label); });
                const monthKeys = displayKeys.filter(function(k){ return !k.startsWith('AVG-'); });
                const monthLabels = displayLabels.filter(function(_, i){ return !displayKeys[i].startsWith('AVG-'); });
                const avgKeys = displayKeys.filter(function(k){ return k.startsWith('AVG-'); });
                const avgLabels = displayLabels.filter(function(_, i){ return displayKeys[i].startsWith('AVG-'); });
                const groups = {};
                let grandTotal = 0;
                if (Array.isArray(response.details) && response.details.length > 0) {
                    response.details.forEach(function(detail) {
                        const remark = (detail.remarks || '').trim() || '(No Remark)';
                        const mkey = detail.effective_date ? detail.effective_date.slice(0,7) : (detail.bulan_key || detail.bulan || '');
                        const qty = parseFloat(detail.loc_qty_change) || 0;
                        if (!groups[remark]) groups[remark] = { months: {}, total: 0 };
                        groups[remark].months[mkey] = (groups[remark].months[mkey] || 0) + qty;
                        const year = String(mkey).slice(0,4);
                        groups[remark].annual_totals = groups[remark].annual_totals || {};
                        groups[remark].annual_months_set = groups[remark].annual_months_set || {};
                        groups[remark].annual_totals[year] = (groups[remark].annual_totals[year] || 0) + qty;
                        groups[remark].annual_months_set[year] = groups[remark].annual_months_set[year] || {};
                        if (mkey) groups[remark].annual_months_set[year][mkey] = true;
                        groups[remark].total += qty;
                        grandTotal += qty;
                    });
                    if (monthKeys.length > 0) {
                        let thead = '<tr><th>Remark (Month chosen)</th>';
                        monthLabels.forEach(function(label) { thead += '<th class="text-center text-nowrap">' + label + '</th>'; });
                        avgLabels.forEach(function(label) { thead += '<th class="text-center text-nowrap">' + label + '</th>'; });
                        thead += '<th class="text-end">Total</th></tr>';
                        let tbodyHtml = '';
                        Object.keys(groups).forEach(function(remark) {
                            const g = groups[remark];
                            tbodyHtml += '<tr><td style="min-width:220px;">' + escapeHtml(remark) + '</td>';
                            monthKeys.forEach(function(k) {
                                const val = g.months[k] || 0;
                                const cls = val < 0 ? 'text-danger' : 'text-success';
                                tbodyHtml += '<td class="text-end font-monospace ' + cls + '">' + formatQty(val) + '</td>';
                            });
                            avgKeys.forEach(function(avgKey) {
                                const year = avgKey.slice(4);
                                const annualTotal = (g.annual_totals && g.annual_totals[year]) ? g.annual_totals[year] : 0;
                                const distinctMonthsCount = (g.annual_months_set && g.annual_months_set[year]) ? Object.keys(g.annual_months_set[year]).length : 0;
                                const avgVal = distinctMonthsCount ? (annualTotal / distinctMonthsCount) : 0;
                                const cls = avgVal < 0 ? 'text-danger' : 'text-success';
                                tbodyHtml += '<td class="text-end font-monospace ' + cls + '">' + formatQty(avgVal) + '</td>';
                            });
                            tbodyHtml += '<td class="text-end fw-bold font-monospace bg-light">' + formatQty(g.total) + '</td></tr>';
                        });
                        const colspan = 1 + monthKeys.length + avgKeys.length;
                        const tfoot = '<tr><td colspan="' + colspan + '" class="text-end fw-bold">Grand total</td><td class="text-end fw-bold font-monospace bg-secondary text-white">' + formatQty(grandTotal) + '</td></tr>';
                        const tableHtml = '<table class="table table-striped table-bordered table-sm mb-0"><thead class="sticky-top bg-light">' + thead + '</thead><tbody>' + tbodyHtml + '</tbody><tfoot>' + tfoot + '</tfoot></table>';
                        $('#detail-table-container').html(tableHtml);
                    } else {
                        let tbodyHtml = '';
                        Object.keys(groups).forEach(function(remark) {
                            const g = groups[remark];
                            tbodyHtml += '<tr><td style="min-width:220px;">' + escapeHtml(remark) + '</td><td class="text-end fw-bold font-monospace bg-light">' + formatQty(g.total) + '</td></tr>';
                        });
                        const tfoot = '<tr><td class="text-end fw-bold">Grand total</td><td class="text-end fw-bold font-monospace bg-secondary text-white">' + formatQty(grandTotal) + '</td></tr>';
                        const tableHtml = '<table class="table table-striped table-bordered table-sm mb-0"><thead class="sticky-top bg-light"><tr><th>Remark</th><th class="text-end">Total</th></tr></thead><tbody>' + tbodyHtml + '</tbody><tfoot>' + tfoot + '</tfoot></table>';
                        $('#detail-table-container').html(tableHtml);
                    }
                    $detailTotalInfo.text(formatQty(grandTotal)).removeClass('text-danger text-success').addClass(grandTotal < 0 ? 'text-danger' : 'text-success');
                } else {
                    const emptyHtml = '<div class="text-center text-muted p-3">Tidak ada transaksi detail yang ditemukan.</div>';
                    $('#detail-table-container').html(emptyHtml);
                    $detailTotalInfo.text(formatQty(0)).removeClass('text-danger text-success').addClass('text-success');
                }
                $detailLoading.hide();
                $detailContent.show();
            },
            error: function() {
                $('#detail-table-container').html('<div class="text-center text-danger p-3">Gagal memuat data detail.</div>');
                $detailLoading.hide();
                $detailContent.show();
            }
        });
    });

    $('#select-all-details').on('change', function() {
        $('.select-detail').prop('checked', $(this).is(':checked'));
    });
    $('#select-all-resume').on('change', function() {
        $('.select-resume').prop('checked', $(this).is(':checked'));
    });
    $('#clearSelectionBtn').on('click', function() {
        $('.select-detail, .select-resume').prop('checked', false);
        $('#select-all-details, #select-all-resume').prop('checked', false);
    });

    $(document).on('click', '.select-resume', function(e) {
        e.stopPropagation();
    });

    $('#exportBtn').on('click', function() {
        const mode = $('#exportMode').val();
        let selected = [];
        if (mode === 'details') {
            $('.select-detail:checked').each(function() { selected.push($(this).val()); });
        } else {
            $('.select-resume:checked').each(function() { selected.push($(this).val()); });
        }
        if (selected.length === 0) {
            alert('Please select at least one row to export.');
            return;
        }
        $('#exportForm').find('input[name="selected_ids[]"]').remove();
        selected.forEach(function(val) {
            $('<input>').attr({type: 'hidden', name: 'selected_ids[]', value: val}).appendTo('#exportForm');
        });
        $('#exportForm')[0].submit();
    });

    function escapeHtml(unsafe) {
        return String(unsafe).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }
});
</script>
@endsection