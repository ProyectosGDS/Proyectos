<?php

use Illuminate\Support\Facades\Route;
use App\Services\Sap\SapRfc as SAP;

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

        //  $params = [
        //     'INTERLOCUTOR' => '0100699379', 
        //     'OP_PRINCIPAL' => '4010',
        //     'OP_PARCIAL' => '0175',
        //     'OBJETO_CONTRATO' => 'EJ01',
        //     'VALOR' => '1500.00',
        //     'PERIODO' => '1025',
        //     'FECHA_VENCIMIENTO' => '20251030',
        //     'DESCRIPCION' => 'TESTIN CASACA 123 TABLITA',
        //     'LLAVE_RECONCILIACION' => 'OCT2025'
        // ];

        // $params = [
        //     'TIPO_ESCUELA' => '2', 
        //     'ALUMNO' => '20251000102',
        //     'DPI' => '1580124960101',
        //     'PRIMER_NOMBRE' => 'LESBIA',
        //     'SEGUNDO_NOMBRE' => 'AZUCENA',
        //     'PRIMER_APELLIDO' => 'POROJ',
        //     'SEGUNDO_APELLIDO' => 'ERNANDEZ',
        //     'FECHA_NACIMIENTO' => '19730716',
        //     'DIRECCION' => '16 AVENIDA B 12-80 GERONA',
        //     'ZONA' => '1'
        // ];

        // $params = [
        //     'TIPO_ESCUELA' => '1', 
        //     'ALUMNO' => '202510000013',
        //     'DPI' => '3508885820102',
        //     'PRIMER_NOMBRE' => 'JOEL',
        //     'SEGUNDO_NOMBRE' => 'ENRRIQUE',
        //     'PRIMER_APELLIDO' => 'MORALES',
        //     'SEGUNDO_APELLIDO' => 'PEREZ',
        //     'FECHA_NACIMIENTO' => '20150301',
        //     'DIRECCION' => '21 AVENIDA 2-20',
        //     'ZONA' => 'ZONA 6',
        //     'PNOMBRE_ENCARGADO' => 'MARIO',
        //     'SNOMBRE_ENCARGADO' => 'ENRRIQUE',
        //     'PAPELLIDO_ENCARGADO' => 'MORALES',
        //     'SAPELLIDO_ENCARGADO' => 'PEREZ',
        //     'FNACIMIENTO_ENCARGADO' => '19680125',
        //     'DPI_ENCARGADO' => '2733271000108'
        // ];

        
            // $result = SAP::rfc_name('Z_ZFUN_PSCD_00003_005')->params($params);
        // $result = SAP::rfc_name('Z_ZFUN_PSCD_00003_004')->params($params);


        
        // var_dump($result);
        
    } catch (\Throwable $th) {
        return response($th->getMessage());
    }

});
