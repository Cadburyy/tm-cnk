<?php

namespace App\Http\Controllers;

use App\Models\Outing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OutingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() && $request->get('action') === 'pivot_row_details') {
            $id_list = $request->get('id_list');
            if (empty($id_list)) return response()->json(['details' => []]);

            $ids = array_filter(array_map('trim', explode(',', $id_list)));
            $details = Outing::whereIn('id', $ids)
                ->orderBy('tanggal', 'asc')
                ->get();
            
            $total_nominal = $details->sum('nominal');

            return response()->json([
                'details' => $details,
                'total_nominal' => $total_nominal
            ]);
        }

        $distinctVouchers = Outing::select('voucher')->distinct()->orderBy('voucher')->pluck('voucher');
        $distinctPTs = Outing::select('nama_pt')->distinct()->orderBy('nama_pt')->pluck('nama_pt');
        $distinctParts = Outing::select('part')->distinct()->orderBy('part')->pluck('part');
        $distinctAkuns = Outing::select('akun')->distinct()->orderBy('akun')->pluck('akun');

        $distinctDates = Outing::select(DB::raw('DISTINCT YEAR(tanggal) as year, DATE_FORMAT(tanggal, "%Y-%m") as ym'))
            ->orderBy('year', 'desc')
            ->orderBy('ym', 'desc')
            ->get();

        $distinctYears = $distinctDates->pluck('year')->unique()->sortDesc()->values();
        $distinctYearMonths = $distinctDates->groupBy('year')->map(function ($items) {
            return $items->pluck('ym')->unique()->sort();
        });

        $mode = $request->input('mode', 'resume');
        
        if ($mode === 'details' && (!auth()->check() || !(method_exists(auth()->user(), 'hasRole') ? auth()->user()->hasRole('Admin|AdminIT') : (auth()->user()->is_admin ?? false)))) {
            $mode = 'resume';
        }

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        
        $voucher_term = $request->input('voucher_term');
        $pt_term = $request->input('pt_term');
        $desc_term = $request->input('desc_term');
        $akun_term = $request->input('akun_term');
        
        $raw_selections = $request->input('pivot_months', []);
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
        }

        $query = Outing::query();
        $query->orderBy('tanggal', 'desc');

        if ($voucher_term) $query->where('voucher', 'LIKE', '%' . $voucher_term . '%');
        if ($pt_term) $query->where('nama_pt', 'LIKE', '%' . $pt_term . '%');
        if ($akun_term) $query->where('akun', 'LIKE', '%' . $akun_term . '%');
        
        if ($desc_term) {
            $query->where(function($q) use ($desc_term) {
                $q->where('nama', 'LIKE', '%' . $desc_term . '%')
                  ->orWhere('part', 'LIKE', '%' . $desc_term . '%')
                  ->orWhere('keterangan', 'LIKE', '%' . $desc_term . '%');
            });
        }

        if ($mode == 'details') {
            if ($start_date) $query->where('tanggal', '>=', $start_date);
            if ($end_date) $query->where('tanggal', '<=', $end_date);
        } elseif ($mode == 'resume') {
            if (!empty($selected_months) || !empty($selected_yearly)) {
                $query->where(function($q) use ($selected_months, $selected_yearly) {
                    foreach ($selected_months as $ym) {
                        $q->orWhere('tanggal', 'LIKE', $ym . '-%');
                    }
                    foreach ($selected_yearly as $yearEntry) {
                        $year = explode('|', $yearEntry)[0];
                        $q->orWhereYear('tanggal', $year);
                    }
                });
            }
        }

        $outings = $query->get();

        $summary_rows = [];
        $months = [];

        if ($mode == 'resume') {
            $final_months = [];
            $yearly_mode = $request->input('yearly_mode', 'total'); 

            foreach ($selected_yearly as $yearEntry) {
                $parts = explode('|', $yearEntry);
                $year = $parts[0];
                $type = $parts[1] ?? $yearly_mode; 
                $key = "YEARLY-{$year}|{$type}";
                $label = ($type === 'avg') ? "Avg " . substr($year, 2, 2) : "Total " . $year;
                $final_months[$key] = ['key' => $key, 'label' => $label, 'type' => 'yearly', 'year' => $year];
            }

            $temp_months = [];
            foreach ($selected_months as $ym) {
                try {
                    $date = Carbon::createFromFormat('Y-m', $ym);
                    $temp_months[$ym] = ['key' => $ym, 'label' => $date->format('M y'), 'type' => 'month', 'year' => $date->format('Y')];
                } catch (\Exception $e) { continue; }
            }
            ksort($temp_months);
            $months = array_merge($final_months, $temp_months);

            foreach ($outings as $out) {
                $year = Carbon::parse($out->tanggal)->format('Y');
                $month_year = Carbon::parse($out->tanggal)->format('Y-m');
                
                // Keep granular grouping for calculation correctness
                $part = $out->part ?? 'NA';
                $ket = $out->keterangan ?? 'NA';
                $akun = $out->akun ?? 'NA';
                $pt = $out->nama_pt ?? '-';
                
                $key = $part . '||' . $ket . '||' . $akun . '||' . $pt;
                
                $nominal = $out->nominal;
                $out_id = $out->id;

                if (!isset($summary_rows[$key])) {
                    $summary_rows[$key] = [
                        'part' => $part,
                        'keterangan' => $ket,
                        'akun' => $akun,
                        'nama_pt' => $pt,
                        'total' => 0,
                        'months' => [],
                        'row_ids' => [],
                        'annual_totals' => [],
                        'annual_months_count' => [],
                    ];
                }

                $summary_rows[$key]['months'][$month_year] = ($summary_rows[$key]['months'][$month_year] ?? 0) + $nominal;
                $summary_rows[$key]['total'] += $nominal;
                $summary_rows[$key]['annual_totals'][$year] = ($summary_rows[$key]['annual_totals'][$year] ?? 0) + $nominal;
                $summary_rows[$key]['annual_months_count'][$year][$month_year] = true;
                $summary_rows[$key]['row_ids'][] = $out_id;
            }

            foreach ($summary_rows as $key => $row) {
                foreach ($selected_yearly as $yearEntry) {
                    $parts = explode('|', $yearEntry);
                    $year = $parts[0];
                    $type = $parts[1] ?? $yearly_mode;
                    $annual_total = $row['annual_totals'][$year] ?? 0;
                    
                    if ($type === 'avg') {
                        $unique_months = count($row['annual_months_count'][$year] ?? []);
                        $val = ($unique_months > 0) ? ($annual_total / $unique_months) : 0;
                    } else {
                        $val = $annual_total;
                    }
                    
                    $yearly_key = "YEARLY-{$year}|{$type}";
                    $summary_rows[$key]['months'][$yearly_key] = $val;
                }
                $summary_rows[$key]['row_ids'] = implode(',', array_unique($summary_rows[$key]['row_ids']));
                
                unset($summary_rows[$key]['annual_totals']);
                unset($summary_rows[$key]['annual_months_count']);
            }
        }

        return view('outings.index', compact(
            'outings', 
            'mode',
            'start_date', 
            'end_date', 
            'voucher_term', 
            'pt_term', 
            'desc_term', 
            'akun_term',
            'distinctVouchers',
            'distinctPTs',
            'distinctParts',
            'distinctAkuns',
            'distinctYears',
            'distinctYearMonths',
            'summary_rows',
            'months',
            'raw_selections'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv_files' => 'required|array',
            'csv_files.*' => 'mimes:csv,txt|max:10240',
        ]);

        $files = $request->file('csv_files');
        $uploadCount = 0;

        DB::beginTransaction();
        try {
            foreach ($files as $file) {
                $handle = fopen($file->getRealPath(), 'r');
                $firstLine = fgets($handle);
                $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
                rewind($handle);

                fgetcsv($handle, 0, $delimiter); 

                while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                    if (count($row) < 9) continue;

                    $clean = fn($val) => trim(mb_convert_encoding($val ?? '', 'UTF-8', 'ISO-8859-1'));

                    try {
                        $date = Carbon::parse($clean($row[1]));
                    } catch (\Exception $e) {
                        $date = now();
                    }

                    $rawNominal = $clean($row[8]);
                    $nominal = (float) str_replace(['.', ','], ['', '.'], $rawNominal);
                    if (strpos($rawNominal, ',') === false) {
                         $nominal = (float) str_replace('.', '', $rawNominal);
                    }

                    Outing::create([
                        'tanggal'    => $date->format('Y-m-d'),
                        'akun'       => $clean($row[2]),
                        'voucher'    => $clean($row[3]),
                        'nama'       => $clean($row[4]),
                        'nama_pt'    => $clean($row[5]),
                        'part'       => $clean($row[6]),
                        'keterangan' => $clean($row[7]),
                        'nominal'    => $nominal,
                    ]);
                    $uploadCount++;
                }
                fclose($handle);
            }
            DB::commit();
            return redirect()->route('outings.index')->with('success', "Uploaded {$uploadCount} rows successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit(Outing $outing)
    {
        if (!auth()->check() || !(method_exists(auth()->user(), 'hasRole') ? auth()->user()->hasRole('Admin|AdminIT') : (auth()->user()->is_admin ?? false))) {
             return redirect()->route('outings.index')->with('error', 'Unauthorized access.');
        }
        return view('outings.edit', compact('outing'));
    }

    public function update(Request $request, Outing $outing)
    {
        if (!auth()->check() || !(method_exists(auth()->user(), 'hasRole') ? auth()->user()->hasRole('Admin|AdminIT') : (auth()->user()->is_admin ?? false))) {
             return redirect()->route('outings.index')->with('error', 'Unauthorized access.');
        }

        $data = $request->validate([
            'tanggal' => 'required|date',
            'nominal' => 'required|numeric',
            'voucher' => 'nullable|string',
            'nama_pt' => 'nullable|string',
            'part'    => 'nullable|string',
            'keterangan' => 'nullable|string',
            'nama'    => 'nullable|string',
            'akun'    => 'nullable|string',
        ]);
        
        $outing->update($data);
        return redirect()->route('outings.index')->with('success', 'Data updated.');
    }

    public function destroy(Outing $outing)
    {
        if (!auth()->check() || !(method_exists(auth()->user(), 'hasRole') ? auth()->user()->hasRole('Admin|AdminIT') : (auth()->user()->is_admin ?? false))) {
            return back()->with('error', 'Unauthorized access.');
        }

        $outing->delete();
        return back()->with('success', 'Data deleted.');
    }

    public function bulkDestroy(Request $request)
    {
        if (!auth()->check() || !(method_exists(auth()->user(), 'hasRole') ? auth()->user()->hasRole('Admin|AdminIT') : (auth()->user()->is_admin ?? false))) {
             return back()->with('error', 'Unauthorized access.');
        }

        $selected = (array) $request->input('selected_ids', []);
        
        if (empty($selected)) {
            return back()->with('error', 'No items selected.');
        }

        DB::beginTransaction();
        try {
            $deletedCount = Outing::whereIn('id', $selected)->delete();
            DB::commit();
            
            return back()->with('success', "Successfully deleted {$deletedCount} records.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk delete failed: ' . $e->getMessage());
        }
    }
}