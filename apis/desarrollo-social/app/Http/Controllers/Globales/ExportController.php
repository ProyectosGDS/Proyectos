<?php

namespace App\Http\Controllers\Globales;

use App\Exports\DataExportGeneric;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    public function exportExcel(Request $request) {
        $request->validate([
            'columns' => 'required',
            'data' => 'required',
        ]);

        try {
            
            return Excel::download(new DataExportGeneric($request->columns,$request->data),'export.xlsx');

        } catch (\Throwable $th) {
            
            return response($th->getMessage());
        }
    }

    public function exportPdf(Request $request) {
        $request->validate([
            'columns' => 'required',
            'data' => 'required',
        ]);

        try {
            
            $pdf = Pdf::loadView('Reports.PdfExportGeneric', [
                'columns' => $request->columns,
                'data' => $request->data,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('export.pdf');

        } catch (\Throwable $th) {
            
            return response($th->getMessage());
        }
    }
}
