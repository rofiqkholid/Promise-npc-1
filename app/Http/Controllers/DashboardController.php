<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Dummy data to prevent view errors
        $stats = [
            'total_stock' => 0,
            'material_in' => 0,
            'material_out' => 0,
            'out_pp' => 0,
            'out_event' => 0,
            'out_trial' => 0,
        ];

        $filters = [
            'month_year' => date('Y-m'),
        ];

        $charts = [
            'stock_grouped' => [],
            'usage_model' => collect([]),
            'trendline' => [],
            'maker' => collect([]),
        ];

        $tables = [
            'balance' => [],
            'history' => [],
        ];

        return view('dashboard', compact('stats', 'filters', 'charts', 'tables'));
    }
}
