<?php

namespace App\Traits;

use App\Models\adm_gds\datos_academicos;
use App\Models\adm_gds\datos_medicos;
use App\Models\adm_gds\domicilios;
use App\Models\adm_gds\beneficiarios;
use App\Models\adm_gds\responsables;
use App\Rules\ValidateCui;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait TraitBeneficiarios {

     // --------------- CREATE METHODS ---------------

    public $bagValidations = [];

    public function storeBeneficiario(Request $request) {

        $validations = Validator::make($request->all(),[
            'cui' => ['required','numeric','digits:13',new ValidateCui,'unique:beneficiarios,cui'],
            'pasaporte' => 'nullable|string|max:45',
            'primer_nombre' => 'required|string|max:45',
            'segundo_nombre' => 'nullable|string|max:45',
            'primer_apellido' => 'required|string|max:45',
            'segundo_apellido' => 'nullable|string|max:45',
            'fecha_nacimiento' => 'required|date|date_format:Y-m-d|after:'.(date('Y') - 100).'-12-31|before :'.date('Y-m-d'),
            'celular' => 'required|numeric|digits:8',
            'sexo' => 'required',
            'interlocutor' => 'nullable|numeric|digits:9',
            'correo' => 'nullable|email'
        ]);

        if($validations->fails()){
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $beneficiario = beneficiarios::create([
            'cui'               => $request->cui,
            'primer_nombre'     => ucfirst(strtolower(trim($request->primer_nombre))),
            'segundo_nombre'    => ucfirst(strtolower(trim($request->segundo_nombre))) ?? null,
            'primer_apellido'   => ucfirst(strtolower(trim($request->primer_apellido))),
            'segundo_apellido'  => ucfirst(strtolower(trim($request->segundo_apellido))) ?? null,
            'fecha_nacimiento'  => $request->fecha_nacimiento,
            'sexo'              => $request->sexo,
            'pasaporte'         => $request->pasaporte ?? null,
            'etnia_id'          => $request->etnia_id ?? null,
            'estado_civil_id'   => $request->estado_civil_id ?? null,
            'interlocutor'      => $request->interlocutor ?? null,
            'celular'           => trim($request->celular),
            'correo'            => strtolower($request->correo),
            'estado'            => $request->estado ?? null
        ]);

        return $beneficiario;

    }

    public function storeDomicilio (Request $request, int $beneficiario_id) {

        $validations = Validator::make($request->all(),[
            'domicilio.direccion' => 'required|max:500',
            'domicilio.municipio_id' => 'required',
        ]);

        if($validations->fails()){
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $domicilio = domicilios::create([
            'beneficiario_id' => $beneficiario_id,
            'direccion' => $request->domicilio['direccion'],
            'zona_id' => $request->domicilio['zona_id'] ?? null,
            'grupo_zona_id' => $request->domicilio['grupo_zona_id'] ?? null,
            'municipio_id'  => $request->domicilio['municipio_id'],
        ]);

        return $domicilio;
    }

    public function storeDatosMedicos(Request $request, int $beneficiario_id) {

        $validations = Validator::make($request->all(),[
            'datos_medicos.enfermedades_alergias' => 'nullable|string|max:500',
            'datos_medicos.medicamentos' => 'nullable|string|max:500',
            'datos_medicos.dosis' => 'nullable|string|max:150',
        ]);

        if($validations->fails()){
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $datosMedicos = datos_medicos::create([
            'beneficiario_id' => $beneficiario_id,
            'enfermedades_alergias' => $request->datos_medicos['enfermedades_alergias'] ?? null ,
            'medicamentos' => $request->datos_medicos['medicamentos'] ?? null ,
            'dosis' => $request->datos_medicos['dosis'] ?? null,
            'tipo_sangre_id' => $request->datos_medicos['tipo_sangre_id'] ?? null,
        ]);
        
        return $datosMedicos;
        
    }

    public function storeResponsable(Request $request, int $beneficiario_id) {

        $validations = Validator::make($request->all(),[
            'responsable.cui' => ['nullable','numeric','digits:13', new ValidateCui],
            'responsable.nombre' => 'required|string|max:150',
            'responsable.celular' => 'required|numeric|digits:8',
            'responsable.email' => 'nullable|email',
            'responsable.sexo' => 'required',
            'responsable.direccion' => 'nullable|string|max:200',
            'responsable.parentesco_id' => 'required',
        ]);

        if($validations->fails()){
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $responsable = responsables::create([
            'beneficiario_id' => $beneficiario_id,
            'cui' => $request->responsable['cui'] ?? null,
            'nombre' => strtoupper(trim($request->responsable['nombre'])),
            'celular' => $request->responsable['celular'],
            'email' => $request->has('responsable.email') ? strtolower($request->responsable['email']) : null,
            'sexo' => $request->responsable['sexo'],
            'zona_id' => $request->responsable['zona_id'] ?? null,
            'direccion' => $request->responsable['direccion'] ?? null,
            'parentesco_id' => $request->responsable['parentesco_id'],
            'categoria' => 'R',
        ]);

        return $responsable;

    }

    public function storeEmergencia(Request $request, int $beneficiario_id) {

        $validations = Validator::make($request->all(),[
            'emergencia.cui' => ['nullable','numeric','digits:13',new ValidateCui],
            'emergencia.nombre' => 'required|string|max:150',
            'emergencia.celular' => 'required|numeric|digits:8',
            'emergencia.email' => 'nullable|email',
            'emergencia.direccion' => 'nullable|string|max:200',
            'emergencia.sexo' => 'required',
            'emergencia.parentesco_id' => 'required',
        ]);

        if($validations->fails()){
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $emergencia = responsables::create([
            'beneficiario_id' => $beneficiario_id,
            'cui' => $request->emergencia['cui'] ?? null,
            'nombre' => strtoupper(trim($request->emergencia['nombre'])),
            'celular' => $request->emergencia['celular'],
            'email' => $request->has('emergencia.email') ? strtolower($request->emergencia['email']) : null,
            'sexo' => $request->emergencia['sexo'],
            'zona_id' => $request->emergencia['zona_id'] ?? null,
            'direccion' => $request->emergencia['direccion'] ?? null,
            'categoria' => 'E',
            'parentesco_id' => $request->emergencia['parentesco_id'],
        ]);

        return $emergencia;

    }

    public function storeDatosAcademicos(Request $request, int $beneficiario_id) {
        $validations = Validator($request->all(),[
            'datos_academicos.escolaridad_id' => 'required',
            'datos_academicos.tipo' => 'required',
        ]);

        if($validations->fails()){
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $datosAcademicos = datos_academicos::create([
            'beneficiario_id' => $beneficiario_id,
            'escolaridad_id' => $request->datos_academicos['escolaridad_id'],
            'tipo' => $request->datos_academicos['tipo'],
            'establecimiento' => $request->has('datos_academicos.establecimiento') ? mb_strtoupper($request->datos_academicos['establecimiento']) : null,
            'titulo_carrera' => $request->has('datos_academicos.titulo_carrera') ? mb_strtoupper($request->datos_academicos['titulo_carrera']) : null,
        ]);

        return $datosAcademicos;
    
    }

    // --------------- UPDATE METHODS ---------------

    public function updateBeneficiario(Request $request, beneficiarios $beneficiario) {

        $validations = Validator::make($request->all(),[
            'cui' => ['required','numeric','digits:13',new ValidateCui,Rule::unique('beneficiarios','cui')->ignore($beneficiario->id)],
            'pasaporte' => 'nullable|string|max:45',
            'primer_nombre' => 'required|string|max:45',
            'segundo_nombre' => 'nullable|string|max:45',
            'primer_apellido' => 'required|string|max:45',
            'segundo_apellido' => 'nullable|string|max:45',
            'fecha_nacimiento' => 'required|date|date_format:Y-m-d|after:'.(date('Y') - 100).'-12-31',
            'celular' => 'required|numeric|digits:8',
            'sexo' => 'required',
            'interlocutor' => 'nullable|numeric|digits:10',
            'correo' => 'nullable|email'
        ]);

        if($validations->fails()){
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $beneficiario = $beneficiario->update([
            'cui'               => $request->cui,
            'primer_nombre'     => ucfirst(strtolower(trim($request->primer_nombre))),
            'segundo_nombre'    => ucfirst(strtolower(trim($request->segundo_nombre))) ?? null,
            'primer_apellido'   => ucfirst(strtolower(trim($request->primer_apellido))),
            'segundo_apellido'  => ucfirst(strtolower(trim($request->segundo_apellido))) ?? null,
            'fecha_nacimiento'  => $request->fecha_nacimiento,
            'sexo'              => $request->sexo,
            'pasaporte'         => $request->pasaporte ?? null,
            'etnia_id'          => $request->etnia_id ?? null,
            'estado_civil_id'   => $request->estado_civil_id ?? null,
            'interlocutor'      => $request->interlocutor ?? null,
            'celular'           => trim($request->celular),
            'correo'            => strtolower($request->correo),
            'estado'            => $request->estado ?? null
        ]);

        return $beneficiario;

    }

    public function updateDomicilio (Request $request, beneficiarios $beneficiario) {

        $validations = Validator::make($request->all(),[
            'domicilio.direccion' => 'required|max:500',
            'domicilio.municipio_id' => 'required',
        ]);

        if($validations->fails()){
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $domicilios = $beneficiario->domicilio()->update([
            'direccion' => $request->domicilio['direccion'],
            'zona_id' => $request->domicilio['zona_id'] ?? null,
            'grupo_zona_id' => $request->domicilio['grupo_zona_id'] ?? null,
            'municipio_id'  => $request->domicilio['municipio_id'],
        ]);

        return $domicilios;
    }

    public function updateDatosMedicos(Request $request, beneficiarios $beneficiario) {

        $validations = Validator::make($request->all(),[
            'datos_medicos.enfermedades_alergias' => 'nullable|string|max:500',
            'datos_medicos.medicamentos' => 'nullable|string|max:500',
            'datos_medicos.dosis' => 'nullable|string|max:150',
        ]);

        if($validations->fails()){
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $datosMedicos = $beneficiario->datos_medicos()->update([
            'enfermedades_alergias' => $request->datos_medicos['enfermedades_alergias'] ?? null ,
            'medicamentos' => $request->datos_medicos['medicamentos'] ?? null ,
            'dosis' => $request->datos_medicos['dosis'] ?? null,
            'tipo_sangre_id' => $request->datos_medicos['tipo_sangre_id'] ?? null,
        ]);

        return $datosMedicos;

    }

    public function updateResponsable(Request $request, beneficiarios $beneficiario) {

        $validations = Validator::make($request->all(),[
            'responsable.cui' => ['nullable','numeric','digits:13', new ValidateCui],
            'responsable.nombre' => 'required|string|max:150',
            'responsable.celular' => 'required|numeric|digits:8',
            'responsable.email' => 'nullable|email',
            'responsable.sexo' => 'required',
            'responsable.direccion' => 'nullable|string|max:200',
            'responsable.parentesco_id' => 'required',
        ]);

        if($validations->fails()){
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $responsable = $beneficiario->responsable()->update([
            'cui' => $request->responsable['cui'] ?? null,
            'nombre' => strtoupper(trim($request->responsable['nombre'])),
            'celular' => $request->responsable['celular'],
            'email' => $request->has('responsable.email') ? strtolower($request->responsable['email']) : null,
            'sexo' => $request->responsable['sexo'],
            'zona_id' => $request->responsable['zona_id'] ?? null,
            'direccion' => $request->responsable['direccion'] ?? null,
            'parentesco_id' => $request->responsable['parentesco_id'],
            'categoria' => 'R',
        ]);

        return $responsable;

    }

    public function updateEmergencia(Request $request, beneficiarios $beneficiario) {

        $validations = Validator::make($request->all(),[
            'emergencia.cui' => ['nullable','numeric','digits:13',new ValidateCui],
            'emergencia.nombre' => 'required|string|max:150',
            'emergencia.celular' => 'required|numeric|digits:8',
            'emergencia.email' => 'nullable|email',
            'emergencia.direccion' => 'nullable|string|max:200',
            'emergencia.sexo' => 'required',
            'emergencia.parentesco_id' => 'required',
        ]);

        if($validations->fails()){
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $emergencia = $beneficiario->emergencia()->update([
            'cui' => $request->emergencia['cui'] ?? null,
            'nombre' => strtoupper(trim($request->emergencia['nombre'])),
            'celular' => $request->emergencia['celular'],
            'email' => $request->has('emergencia.email') ? strtolower($request->emergencia['email']) : null,
            'sexo' => $request->emergencia['sexo'],
            'zona_id' => $request->emergencia['zona_id'] ?? null,
            'direccion' => $request->emergencia['direccion'] ?? null,
            'categoria' => 'E',
            'parentesco_id' => $request->emergencia['parentesco_id'],
        ]);

        return $emergencia;

    }

    public function updateDatosAcademicos(Request $request, beneficiarios $beneficiario) {

        $validations = Validator($request->all(),[
            'datos_academicos.escolaridad_id' => 'required',
            'datos_academicos.tipo' => 'required',
        ]);

        if($validations->fails()){
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $datosAcademicos = $beneficiario->datos_academicos()->update([
            'escolaridad_id' => $request->datos_academicos['escolaridad_id'],
            'tipo' => $request->datos_academicos['tipo'],
            'establecimiento' => $request->has('datos_academicos.establecimiento') ? strtoupper($request->datos_academicos['establecimiento']) : null,
            'titulo_carrera' => $request->has('datos_academicos.titulo_carrera') ? strtoupper($request->datos_academicos['titulo_carrera']) : null,
        ]);

        return $datosAcademicos;
    
    }
}