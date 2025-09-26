<?php

use Illuminate\Support\Facades\Route;
use App\Services\SapRfc as SAP;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/test-sap-rfc', function () {
    
    try {

         $params = [
            'INTERLOCUTOR' => '0100699339', 
            'OP_PRINCIPAL' => '4010',
            'OP_PARCIAL' => '0175',
            'OBJETO_CONTRATO' => 'EJ04',
            'VALOR' => '100.00  ',
            'PERIODO' => '0925',
            'FECHA_VENCIMIENTO' => '20250930',
            'DESCRIPCION' => 'lorem impsum',
            'LLAVE_RECONCILIACION' => 'Esc2025'
        ];

        // $params = [
        //     'TIPO_ESCUELA' => '1', 
        //     'ALUMNO' => '',
        //     'DPI' => '2733271000103',
        //     'PRIMER_NOMBRE' => 'NELSON',
        //     'SEGUNDO_NOMBRE' => 'OVIDIO',
        //     'PRIMER_APELLIDO' => 'VÁSQUEZ',
        //     'SEGUNDO_APELLIDO' => 'VENTURA',
        //     'FECHA_NACIMIENTO' => '19880623',
        //     'DIRECCION' => '2 CALLE 1-02 ANEXO RUEDITA',
        //     'ZONA' => '3',
        //     'PNOMBRE_ENCARGADO' => '',
        //     'SNOMBRE_ENCARGADO' => '',
        //     'PAPELLIDO_ENCARGADO' => '',
        //     'SAPELLIDO_ENCARGADO' => '',
        //     'FNACIMIENTO_ENCARGADO' => '19790106',
        //     'DPI_ENCARGADO' => ''
        // ];

        
            $result = SAP::rfc_name('Z_ZFUN_PSCD_00003_005')->params($params);
        // $result = SAP::rfc_name('Z_ZFUN_PSCD_00003_004')->params($params);
        
        return response($result);
        
    } catch (\Throwable $th) {
        return response($th->getMessage());
    }

});
