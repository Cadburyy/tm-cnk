<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Item; 
use App\Models\Budget; 
use Carbon\Carbon;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $itemNumbers = Item::select('item_number')->distinct()
            ->union(Budget::select('item_number')->distinct())
            ->pluck('item_number');

        $dashboardData = [];

        $itemAggregates = Item::select('item_number', DB::raw('SUM(loc_qty_change) as total_qty'))
            ->groupBy('item_number')
            ->pluck('total_qty', 'item_number')
            ->toArray();

        $budgetAggregates = Budget::select('item_number', DB::raw('SUM(budget) as total_budget'))
            ->groupBy('item_number')
            ->pluck('total_budget', 'item_number')
            ->toArray();
            
        $itemDetailsCollection = Item::select('item_number', 'item_description', 'unit_of_measure')
            ->distinct()
            ->get(); 
            
        $itemDetails = $itemDetailsCollection->mapWithKeys(function ($item) {
            return [
                $item->item_number => [
                    'item_description' => $item->item_description,
                    'unit_of_measure' => $item->unit_of_measure,
                ]
            ];
        })->toArray();

        $monthlyData = [];
        $prefixes = [];

        foreach ($itemNumbers as $itemNumber) {
            $qty = $itemAggregates[$itemNumber] ?? 0;
            $budget = $budgetAggregates[$itemNumber] ?? 0;
            $combined = $qty + $budget;
            
            $dbDetail = $itemDetails[$itemNumber] ?? [];
            $detail = [
                'description' => $dbDetail['item_description'] ?? '', 
                'uom' => $dbDetail['unit_of_measure'] ?? '',
            ];

            $dashboardData[$itemNumber] = [
                'item_number' => $itemNumber,
                'total_qty' => (float)$qty,
                'total_budget' => (float)$budget,
                'combined_total' => (float)$combined,
                'is_fraud_deficit' => $combined < 0,
                'description' => $detail['description'],
                'uom' => $detail['uom'],
            ];

            $prefix = strtoupper(substr($itemNumber, 0, 4));
            if (strlen($prefix) === 4) {
                $prefixes[$prefix] = true;
            }
        }
        
        usort($dashboardData, function($a, $b) {
            return strcmp($a['item_number'], $b['item_number']);
        });
        
        $monthlyItems = Item::whereIn('item_number', $itemNumbers)
            ->select('item_number', DB::raw('DATE_FORMAT(effective_date, "%Y-%m") as month'), DB::raw('SUM(loc_qty_change) as qty'))
            ->groupBy('item_number', 'month')
            ->get();
            
        $monthlyBudgets = Budget::whereIn('item_number', $itemNumbers)
            ->select('item_number', DB::raw('DATE_FORMAT(effective_date, "%Y-%m") as month'), DB::raw('SUM(budget) as budget'))
            ->groupBy('item_number', 'month')
            ->get();
            
        foreach ($monthlyItems as $data) {
            $monthlyData[$data->item_number][$data->month]['qty'] = (float)$data->qty;
        }
        foreach ($monthlyBudgets as $data) {
            $monthlyData[$data->item_number][$data->month]['budget'] = (float)$data->budget;
        }

        return view('home', [
            'dashboardData' => array_values($dashboardData),
            'monthlyDataJson' => json_encode($monthlyData),
            'prefixes' => array_keys($prefixes),
        ]);
    }
}