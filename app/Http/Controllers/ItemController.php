<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource (Index or Pivot).
     */
    public function index(Request $request)
    {
        if ($request->ajax() && $request->get('action') === 'pivot_row_details') {
            $id_list = $request->get('id_list');
            $item_key = $request->get('item_key');

            if (empty($id_list) || empty($item_key)) {
                return response()->json(['details' => [], 'budget_data' => []]);
            }

            $ids = array_filter(array_map('trim', explode(',', $id_list)));
            $item_number = explode('||', $item_key)[0] ?? null;

            if (empty($ids)) {
                return response()->json(['details' => [], 'budget_data' => []]);
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

            $budget_data = [];
            if ($item_number) {
                $budgets = \App\Models\Budget::where('item_number', $item_number)
                    ->get(['effective_date', 'budget']);
                
                $budgets->each(function($b) use (&$budget_data) {
                    $month_year = Carbon::parse($b->effective_date)->format('Y-m');
                    $budget_data[$month_year] = ($budget_data[$month_year] ?? 0) + $b->budget;
                });
            }

            $first_item = $details->first();
            $total_qty = $details->sum('loc_qty_change');
            
            return response()->json([
                'item_key' => ($first_item) ? $first_item->item_number . '||' . $first_item->item_description . '||' . $first_item->unit_of_measure . '||' . $first_item->dept : 'N/A',
                'total_qty' => $total_qty,
                'details' => $details,
                'budget_data' => $budget_data,
            ]);
        }

        $itemNumbers = DB::table('items')
            ->select('item_number')
            ->distinct()
            ->whereNotNull('item_number')
            ->where('item_number', '!=', '')
            ->orderBy('item_number')
            ->pluck('item_number');

        $itemGroups = DB::table('items')
            ->select('item_group')
            ->distinct()
            ->whereNotNull('item_group')
            ->where('item_group', '!=', '')
            ->orderBy('item_group')
            ->pluck('item_group');
            
        $depts = DB::table('items')
            ->select('dept')
            ->distinct()
            ->whereNotNull('dept')
            ->where('dept', '!=', '')
            ->orderBy('dept')
            ->pluck('dept');

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $raw_selections = $request->input('pivot_months', []);
        $mode = $request->input('mode', 'resume');

        if ($mode === 'details' && (!auth()->check() || !(method_exists(auth()->user(), 'hasRole') ? auth()->user()->hasRole('Admin|AdminIT') : (auth()->user()->is_admin ?? false)))) {
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
        $query->orderBy('item_number', 'asc');
        $query->orderBy('effective_date', 'desc');

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
                    foreach ($selected_yearly as $yearEntry) {
                        $year = explode('|', $yearEntry)[0];
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
            $yearly_mode = $request->input('yearly_mode', 'total'); 

            foreach ($selected_yearly as $yearEntry) {
                $parts = explode('|', $yearEntry);
                $year = $parts[0];
                $type = $parts[1] ?? $yearly_mode; 
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
                    $type = $parts[1] ?? $yearly_mode;
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
            'itemNumbers' => $itemNumbers,
            'itemGroups' => $itemGroups,
            'depts' => $depts,
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
                    $qty_string = str_replace([' '], [''], $qty_field);
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

    /**
     * Delete a single resource.
     */
    public function destroy($id)
    {
        if (!auth()->check() || !(method_exists(auth()->user(), 'hasRole') ? auth()->user()->hasRole('Admin|AdminIT') : (auth()->user()->is_admin ?? false))) {
            return back()->with('error', 'Unauthorized access.');
        }
        $deleted = Item::destroy($id);
        if ($deleted) {
            return back()->with('success', 'Item transaction deleted successfully.');
        }
        return back()->with('error', 'Failed to delete record.');
    }

    /**
     * Bulk delete resources.
     */
    public function bulkDestroy(Request $request)
    {
        if (!auth()->check() || !(method_exists(auth()->user(), 'hasRole') ? auth()->user()->hasRole('Admin|AdminIT') : (auth()->user()->is_admin ?? false))) {
            return back()->with('error', 'Unauthorized access for bulk delete.');
        }

        $selected = (array) $request->input('selected_ids', []);
        
        if (empty($selected)) {
            return back()->with('error', 'No items selected for deletion.');
        }

        $all_ids_to_delete = [];
        foreach ($selected as $row_ids_str) {
            $ids = array_filter(array_map('trim', explode(',', $row_ids_str)));
            $all_ids_to_delete = array_merge($all_ids_to_delete, $ids);
        }
        $all_ids_to_delete = array_unique($all_ids_to_delete);
        
        DB::beginTransaction();
        try {
            $deletedCount = Item::whereIn('id', $all_ids_to_delete)->delete();
            DB::commit();
            
            if ($deletedCount > 0) {
                return back()->with('success', "Successfully deleted {$deletedCount} records.");
            }
            return back()->with('error', 'Failed to delete any selected records.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Item Bulk Delete Failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Bulk deletion failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (!auth()->check() || !(method_exists(auth()->user(), 'hasRole') ? auth()->user()->hasRole('Admin|AdminIT') : (auth()->user()->is_admin ?? false))) {
             return redirect()->route('items.index')->with('error', 'Unauthorized access.');
        }
        $item = Item::findOrFail($id);
        return view('items.edit', compact('item'));
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->check() || !(method_exists(auth()->user(), 'hasRole') ? auth()->user()->hasRole('Admin|AdminIT') : (auth()->user()->is_admin ?? false))) {
             return redirect()->route('items.index')->with('error', 'Unauthorized access.');
        }
        $request->validate([
             'item_number' => 'required',
             'loc_qty_change' => 'required|numeric',
        ]);
        
        $item = Item::findOrFail($id);
        $item->update($request->all());
        
        return redirect()->route('items.index')->with('success', 'Item transaction updated successfully.');
    }
}