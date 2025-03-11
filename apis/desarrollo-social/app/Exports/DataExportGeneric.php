<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DataExportGeneric implements FromView
{
    public $columns = [], $data = [];

    public function __construct($columns, $data) {
        $this->columns = $columns;
        $this->data = $data;
    }

    public function view() : View {
        $columns = $this->columns;
        $data = $this->data;
        return view('Reports.ExcelExportGeneric',compact('columns','data'));
    }
}
