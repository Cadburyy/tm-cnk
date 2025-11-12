<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() && $request->get('action') === 'pivot_row_details') {
            $id_list = $request->get('id_list');
            if (empty($id_list)) {
                return response()->json(['details' => []]);
            }
            $ids = array_filter(array_map('trim', explode(',', $id_list)));
            if (empty($ids)) {
                return response()->json(['details' => []]);
            }
            $details = Item::whereIn('id', $ids)
                ->orderBy('effective_date', 'asc')
                ->get([
                    'id',
                    'effective_date',
                    'bulan',
                    'loc_qty_change',
                    'remarks',
                    'item_number',
                    'item_description',
                    'unit_of_measure',
                    'dept'
                ]);
            $first_item = $details->first();
            $total_qty = $details->sum('loc_qty_change');
            return response()->json([
                'item_key' => ($first_item) ? $first_item->item_number . '||' . $first_item->item_description . '||' . $first_item->unit_of_measure . '||' . $first_item->dept : 'N/A',
                'total_qty' => $total_qty,
                'details' => $details,
            ]);
        }

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $raw_selections = $request->input('pivot_months', []);
        $mode = $request->input('mode', 'resume');
        if ($mode === 'details' && (!auth()->check() || !(method_exists(auth()->user(), 'hasRole') ? auth()->user()->hasRole('Admin') : (auth()->user()->is_admin ?? false)))) {
            $mode = 'resume';
        }
        $item_number_term = $request->input('item_number_term');
        $item_description_term = $request->input('item_description_term');
        $item_group_term = $request->input('item_group_term');
        $dept_term = $request->input('dept_term');

        $distinctDates = Item::select(DB::raw('DISTINCT YEAR(effective_date) as year, DATE_FORMAT(effective_date, "%Y-%m") as ym'))
            ->orderBy('year', 'desc')
            ->orderBy('ym', 'desc')
            ->get();

        $distinctYears = $distinctDates->pluck('year')->unique()->sortDesc()->values();
        $distinctYearMonths = $distinctDates->groupBy('year')->map(function ($items) {
            return $items->pluck('ym')->unique()->sort();
        });

        $query = Item::query();
        $query->orderBy('effective_date', 'desc');
        $query->orderBy('item_number', 'asc');

        if ($mode == 'details' && $start_date && $end_date) {
            $query->whereBetween('effective_date', [$start_date, $end_date]);
        }

        $selected_months = [];
        $selected_yearly = [];

        if ($mode == 'resume') {
            $raw_selections = array_filter((array)$raw_selections);
            foreach ($raw_selections as $selection) {
                if (str_starts_with($selection, 'YEARLY-')) {
                    $selected_yearly[] = str_replace('YEARLY-', '', $selection);
                } else {
                    $selected_months[] = $selection;
                }
            }
            if (!empty($selected_months) || !empty($selected_yearly)) {
                $query->where(function($q) use ($selected_months, $selected_yearly) {
                    foreach ($selected_months as $ym) {
                        $q->orWhere('effective_date', 'LIKE', $ym . '-%');
                    }
                    foreach ($selected_yearly as $year) {
                        $q->orWhereYear('effective_date', $year);
                    }
                });
            }
        }

        if ($item_number_term) {
            $query->where('item_number', 'LIKE', '%' . $item_number_term . '%');
        }
        if ($item_description_term) {
            $query->where('item_description', 'LIKE', '%' . $item_description_term . '%');
        }
        if ($item_group_term) {
            $query->where('item_group', 'LIKE', '%' . $item_group_term . '%');
        }
        if ($dept_term) {
            $query->where('dept', 'LIKE', '%' . $dept_term . '%');
        }

        $items = $query->get();

        $summary_rows = [];
        $months = [];

        if ($mode == 'resume') {
            $final_months = [];
            foreach ($selected_yearly as $yearEntry) {
                $parts = explode('|', $yearEntry);
                $year = $parts[0];
                $type = $parts[1] ?? 'total';
                if ($type === 'avg') {
                    $key = "YEARLY-{$year}|avg";
                    $final_months[$key] = ['key' => $key, 'label' => "Avg " . substr($year, 2, 2), 'type' => 'yearly_avg', 'year' => $year];
                } else {
                    $key = "YEARLY-{$year}|total";
                    $final_months[$key] = ['key' => $key, 'label' => "Total " . $year, 'type' => 'yearly_total', 'year' => $year];
                }
            }
            $temp_months = [];
            foreach ($selected_months as $ym) {
                try {
                    $date = Carbon::createFromFormat('Y-m', $ym);
                    $temp_months[$ym] = ['key' => $ym, 'label' => $date->format('M y'), 'type' => 'month', 'year' => $date->format('Y')];
                } catch (\Exception $e) {
                    continue;
                }
            }
            ksort($temp_months);
            $months = array_merge($final_months, $temp_months);
            foreach ($items as $item) {
                $year = Carbon::parse($item->effective_date)->format('Y');
                $month_year = Carbon::parse($item->effective_date)->format('Y-m');
                $key = $item->item_number . '||' . $item->item_description . '||' . $item->unit_of_measure . '||' . $item->dept;
                $qty = $item->loc_qty_change;
                $item_id = $item->id;
                $remarks = trim((string)$item->remarks);
                if (!isset($summary_rows[$key])) {
                    $summary_rows[$key] = [
                        'item_number' => $item->item_number,
                        'item_description' => $item->item_description,
                        'unit_of_measure' => $item->unit_of_measure,
                        'dept' => $item->dept,
                        'total' => 0,
                        'months' => [],
                        'row_ids' => [],
                        'annual_totals' => [],
                        'annual_months_count' => [],
                        'remarks_set' => [],
                    ];
                }
                $summary_rows[$key]['months'][$month_year] = ($summary_rows[$key]['months'][$month_year] ?? 0) + $qty;
                $summary_rows[$key]['total'] += $qty;
                $summary_rows[$key]['annual_totals'][$year] = ($summary_rows[$key]['annual_totals'][$year] ?? 0) + $qty;
                $summary_rows[$key]['annual_months_count'][$year][$month_year] = true;
                $summary_rows[$key]['row_ids'][] = $item_id;
                if ($remarks !== '') {
                    $summary_rows[$key]['remarks_set'][$remarks] = true;
                }
            }
            foreach ($summary_rows as $key => $row) {
                foreach ($selected_yearly as $yearEntry) {
                    $parts = explode('|', $yearEntry);
                    $year = $parts[0];
                    $type = $parts[1] ?? 'total';
                    $annual_total = $row['annual_totals'][$year] ?? 0;
                    if ($type === 'avg') {
                        $unique_months_in_data = count($row['annual_months_count'][$year] ?? []);
                        $yearly_key = "YEARLY-{$year}|avg";
                        $summary_rows[$key]['months'][$yearly_key] = ($unique_months_in_data > 0) ? ($annual_total / $unique_months_in_data) : 0;
                    } else {
                        $yearly_key = "YEARLY-{$year}|total";
                        $summary_rows[$key]['months'][$yearly_key] = $annual_total;
                    }
                }
                $summary_rows[$key]['row_ids'] = implode(',', array_unique($summary_rows[$key]['row_ids']));
                $summary_rows[$key]['remarks'] = implode(' | ', array_keys($summary_rows[$key]['remarks_set'] ?? []));
                unset($summary_rows[$key]['annual_totals']);
                unset($summary_rows[$key]['annual_months_count']);
                unset($summary_rows[$key]['remarks_set']);
            }
        }

        return view('items.index', [
            'items' => $items,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'pivot_months' => $raw_selections,
            'item_number_term' => $item_number_term,
            'item_description_term' => $item_description_term,
            'item_group_term' => $item_group_term,
            'dept_term' => $dept_term,
            'distinctYears' => $distinctYears,
            'distinctYearMonths' => $distinctYearMonths,
            'mode' => $mode,
            'summary_rows' => array_values($summary_rows),
            'months' => array_values($months),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv_files' => 'required|array',
            'csv_files.*' => 'mimes:csv,txt|max:10240',
        ]);

        $files = $request->file('csv_files');
        $uploadCount = 0;
        $allData = [];
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($files as $file) {
                $handle = fopen($file->getRealPath(), 'r');
                $firstLine = fgets($handle);
                $delimiter = ';';
                if (substr_count($firstLine, ',') > substr_count($firstLine, ';')) {
                    $delimiter = ',';
                }
                rewind($handle);
                $header = fgetcsv($handle, 0, $delimiter);
                if (!$header || count($header) < 9) {
                    fclose($handle);
                    $errors[] = "File {$file->getClientOriginalName()}: Invalid format. Expected 9 columns, got " . (is_array($header) ? count($header) : 0);
                    continue;
                }
                $batchData = [];
                $rowNumber = 1;
                while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                    $rowNumber++;
                    if (!is_array($row) || count($row) < 9 || empty(trim($row[0] ?? ''))) {
                        continue;
                    }
                    $row = array_map(function($field) {
                        if ($field === null) return '';
                        $decoded = mb_convert_encoding($field, 'UTF-8', 'UTF-8, ISO-8859-1');
                        return trim($decoded);
                    }, $row);
                    $qty_field = $row[4] ?? '0';
                    $qty_string = str_replace([' ',], ['',], $qty_field);
                    $qty_string = str_replace(',', '.', $qty_string);
                    $qty_val = (float) $qty_string;
                    try {
                        $effective_date = Carbon::createFromFormat('d/m/Y', trim($row[2]));
                    } catch (\Exception $e) {
                        $errors[] = "File {$file->getClientOriginalName()}, Row {$rowNumber}: Invalid date format '{$row[2]}'. Use d/m/Y";
                        continue;
                    }
                    $batchData[] = [
                        'item_number' => $row[0] ?? null,
                        'item_description' => $row[1] ?? null,
                        'effective_date' => $effective_date->format('Y-m-d'),
                        'bulan' => (int) ($row[3] ?? 0),
                        'loc_qty_change' => (float) $qty_val,
                        'unit_of_measure' => $row[5] ?? null,
                        'remarks' => $row[6] ?? null,
                        'item_group' => $row[7] ?? null,
                        'dept' => $row[8] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $uploadCount++;
                }
                fclose($handle);
                if (!empty($batchData)) {
                    $allData = array_merge($allData, $batchData);
                }
            }
            if (!empty($allData)) {
                Item::insert($allData);
            }
            DB::commit();
            $request->session()->forget('_old_input');
            $message = "{$uploadCount} records uploaded successfully!";
            if (!empty($errors)) {
                $message .= " Warnings: " . implode("; ", array_slice($errors, 0, 3));
            }
            return redirect()->route('items.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('items.index')->with('error', 'Data upload failed: ' . $e->getMessage());
        }
    }

    public function exportSelected(Request $request)
    {
        $mode = $request->input('mode', 'details');
        $selected = (array) $request->input('selected_ids', []);
        $pivot_months = (array) $request->input('pivot_months', []);

        if (empty($selected)) {
            return back()->with('error', 'No rows selected for export.');
        }

        try {
            ini_set('memory_limit', '512M');
            set_time_limit(300);

            if ($mode === 'details') {
                $ids = array_filter($selected);
                $items = Item::whereIn('id', $ids)->orderBy('effective_date', 'asc')->get();
                $filename = 'items_details_' . now()->format('Ymd_His') . '.csv';
                $response = new StreamedResponse(function() use ($items) {
                    $out = fopen('php://output', 'w');
                    fputcsv($out, ['Item Number','Item Description','Effective Date','Bulan','Loc Qty Change','UOM','Remarks','Item Group','DEPT']);
                    foreach ($items as $it) {
                        try {
                            $effective = Carbon::parse($it->effective_date)->format('d/m/Y');
                        } catch (\Exception $e) {
                            $effective = $it->effective_date;
                        }
                        fputcsv($out, [
                            $it->item_number,
                            $it->item_description,
                            $effective,
                            $it->bulan,
                            $it->loc_qty_change,
                            $it->unit_of_measure,
                            $it->remarks,
                            $it->item_group,
                            $it->dept,
                        ]);
                    }
                    fclose($out);
                });
                $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
                $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
                return $response;
            }

            if ($mode === 'resume') {
                $rows = [];
                foreach ($selected as $row_ids_str) {
                    if (trim($row_ids_str) === '') continue;
                    $ids = array_filter(array_map('trim', explode(',', $row_ids_str)));
                    if (empty($ids)) continue;
                    $items = Item::whereIn('id', $ids)->get();
                    if ($items->isEmpty()) continue;
                    $first = $items->first();
                    $rowData = [
                        'item_number' => $first->item_number,
                        'item_description' => $first->item_description,
                        'unit_of_measure' => $first->unit_of_measure,
                        'dept' => $first->dept,
                    ];
                    $remarksSet = [];
                    $total = 0;
                    
                    // Only process selected pivot months
                    foreach ($pivot_months as $mkey) {
                        if (str_starts_with($mkey, 'YEARLY-')) {
                            $parts = explode('|', str_replace('YEARLY-', '', $mkey));
                            $year = $parts[0];
                            $type = $parts[1] ?? 'total';
                            $yearItems = $items->filter(function($it) use ($year) {
                                return Carbon::parse($it->effective_date)->format('Y') === $year;
                            });
                            $annual_totals = $yearItems->sum('loc_qty_change');
                            if ($type === 'avg') {
                                $distinct_months = $yearItems->groupBy(function($it) {
                                    return Carbon::parse($it->effective_date)->format('Y-m');
                                })->count();
                                $val = $distinct_months ? ($annual_totals / $distinct_months) : 0;
                            } else {
                                $val = $annual_totals;
                            }
                            $rowData[$mkey] = $val;
                        } else {
                            $val = $items->filter(function($it) use ($mkey) {
                                return Carbon::parse($it->effective_date)->format('Y-m') === $mkey;
                            })->sum('loc_qty_change');
                            $rowData[$mkey] = $val;
                            $total += $val;
                        }
                        foreach ($items as $it) {
                            $r = trim((string)$it->remarks);
                            if ($r !== '') $remarksSet[$r] = true;
                        }
                    }
                    $rowData['total'] = $total;
                    $rowData['remarks'] = implode(' | ', array_keys($remarksSet));
                    $rows[] = $rowData;
                }
                
                $filename = 'items_resume_' . now()->format('Ymd_His') . '.csv';
                $response = new StreamedResponse(function() use ($rows, $pivot_months) {
                    $out = fopen('php://output', 'w');
                    $header = ['Item Number','Item Description','UOM'];
                    foreach ($pivot_months as $m) {
                        // Format header label
                        if (str_starts_with($m, 'YEARLY-')) {
                            $parts = explode('|', str_replace('YEARLY-', '', $m));
                            $year = $parts[0];
                            $type = $parts[1] ?? 'total';
                            $label = ($type === 'avg') ? "Avg $year" : "Total $year";
                            $header[] = $label;
                        } else {
                            try {
                                $date = Carbon::createFromFormat('Y-m', $m);
                                $header[] = $date->format('M y');
                            } catch (\Exception $e) {
                                $header[] = $m;
                            }
                        }
                    }
                    $header[] = 'Total Qty';
                    $header[] = 'Remarks';
                    $header[] = 'DEPT';
                    fputcsv($out, $header);
                    
                    foreach ($rows as $r) {
                        $line = [$r['item_number'] ?? '', $r['item_description'] ?? '', $r['unit_of_measure'] ?? ''];
                        foreach ($pivot_months as $m) {
                            $line[] = $r[$m] ?? 0;
                        }
                        $line[] = $r['total'] ?? 0;
                        $line[] = $r['remarks'] ?? '';
                        $line[] = $r['dept'] ?? '';
                        fputcsv($out, $line);
                    }
                    fclose($out);
                });
                $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
                $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
                return $response;
            }

            return back()->with('error', 'Invalid export mode.');
        } catch (\Throwable $e) {
            \Log::error('ExportSelected error: ' . $e->getMessage(), ['exception' => $e]);
            if ($request->ajax()) {
                return response()->json(['error' => 'Export failed. Check logs.'], 500);
            }
            return back()->with('error', 'Export failed. Check logs.');
        }
    }

    public function exportResumeDetail(Request $request)
    {
        $idLists = $request->input('id_lists');
        $monthsParam = $request->input('months');

        if (!$idLists) {
            return back()->with('error', 'Invalid export request');
        }

        $allIdLists = explode('||', $idLists);
        $allIds = [];
        foreach ($allIdLists as $list) {
            $ids = array_filter(array_map('trim', explode(',', $list)));
            $allIds = array_merge($allIds, $ids);
        }

        $allIds = array_unique($allIds);
        if (empty($allIds)) {
            return back()->with('error', 'No items to export');
        }

        $items = Item::whereIn('id', $allIds)->orderBy('effective_date', 'asc')->get();
        if ($items->isEmpty()) {
            return back()->with('error', 'No data found');
        }

        $months = !empty($monthsParam) ? explode(',', $monthsParam) : [];

        try {
            ini_set('memory_limit', '512M');
            set_time_limit(300);

            $filename = 'bulk_detail_transaksi_' . now()->format('Ymd_His') . '.csv';
            $response = new StreamedResponse(function() use ($items, $months) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Item Number','Item Description','Effective Date','Bulan','Loc Qty Change','UOM','Remarks','Item Group','DEPT']);
                foreach ($items as $it) {
                    try {
                        $effective = Carbon::parse($it->effective_date)->format('d/m/Y');
                    } catch (\Exception $e) {
                        $effective = $it->effective_date;
                    }
                    fputcsv($out, [
                        $it->item_number,
                        $it->item_description,
                        $effective,
                        $it->bulan,
                        intval($it->loc_qty_change),
                        $it->unit_of_measure,
                        $it->remarks,
                        $it->item_group,
                        $it->dept,
                    ]);
                }
                fclose($out);
            });

            $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
            return $response;
        } catch (\Throwable $e) {
            \Log::error('ExportResumeDetail error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Export failed. Check logs.');
        }
    }
}