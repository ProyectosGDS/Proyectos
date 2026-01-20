<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class GenerarReporteExcel implements FromView
{
    public $rows, $columns; 

    public function __construct($rows, $columns) {
        $this->rows = $rows;
        $this->columns = $columns;
    }
    
    public function view() : View {
        return view('Reports.GenerarReporteExcel',['rows' => $this->rows, 'columns' => $this->columns]);
    }
}
