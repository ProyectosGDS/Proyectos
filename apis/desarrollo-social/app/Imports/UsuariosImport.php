<?php

namespace App\Imports;

use App\Models\adm_gds\usuarios;
use App\Rules\ValidateCui;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class UsuariosImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    public $errors = [];

    public function model(array $row) {
        
        $year = date('Y');

        return new usuarios([
            'cui' => $row['cui'],
            'nombre' => mb_strtoupper(trim($row['nombre'])),
            'dependencia_id' => $row['dependencia'],
            'perfil_id' => $row['perfil'],
            'password' => Hash::make('muniguate'.$year),
        ]);

    }

    public function rules() : array {
        return [
            'cui' => ['required', new ValidateCui, 'unique:usuarios,cui'],
            'nombre' => 'required',
            'dependencia' => 'required|exists:dependencias,id',
            'perfil' => 'required|exists:perfiles,id',
            
        ];
    }

    public function onFailure(...$failures) {
        foreach ($failures as $failure) {
            $errorData = [
                'errores'  => $failure->errors(),
                'valores'  => $failure->values(),
            ];
            Log::channel('error_insert_users')->error('Error en importación', $errorData);

            $this->errors[] = $errorData;
        }
    }
}
