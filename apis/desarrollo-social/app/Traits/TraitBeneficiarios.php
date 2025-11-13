<?php

namespace App\Traits;

use App\Models\adm_gds\datos_academicos;
use App\Models\adm_gds\datos_medicos;
use App\Models\adm_gds\domicilios;
use App\Models\adm_gds\beneficiarios;
use App\Models\adm_gds\renap_consultas;
use App\Models\adm_gds\renap_historial_consultas;
use App\Models\adm_gds\renap_tokens;
use App\Models\adm_gds\responsables;
use App\Rules\ValidateCui;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait TraitBeneficiarios
{

    // --------------- CREATE METHODS ---------------

    public $bagValidations = [];

    public function storeBeneficiario(Request $request) {

        $validations = Validator::make($request->all(), [
            'cui' => ['required', 'numeric', 'digits:13', new ValidateCui, 'unique:beneficiarios,cui'],
            'pasaporte' => 'nullable|string|max:45',
            'primer_nombre' => 'required|string|max:45',
            'segundo_nombre' => 'nullable|string|max:45',
            'primer_apellido' => 'required|string|max:45',
            'segundo_apellido' => 'nullable|string|max:45',
            'fecha_nacimiento' => 'required|date|date_format:Y-m-d|after:' . (date('Y') - 100) . '-12-31|before :' . date('Y-m-d'),
            'celular' => 'required|numeric|digits:8',
            'sexo' => 'required',
            'interlocutor' => 'nullable|numeric|digits:9',
            'correo' => 'nullable|email'
        ]);

        if ($validations->fails()) {
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $sequence = DB::getSequence();

        $beneficiario = beneficiarios::create([
            'cui'               => $request->cui,
            'primer_nombre'     => mb_strtoupper(trim($request->primer_nombre)),
            'segundo_nombre'    => mb_strtoupper(trim($request->segundo_nombre)) ?? null,
            'primer_apellido'   => mb_strtoupper(trim($request->primer_apellido)),
            'segundo_apellido'  => mb_strtoupper(trim($request->segundo_apellido)) ?? null,
            'fecha_nacimiento'  => $request->fecha_nacimiento,
            'sexo'              => $request->sexo,
            'pasaporte'         => $request->pasaporte ?? null,
            'etnia_id'          => $request->etnia_id ?? null,
            'estado_civil_id'   => $request->estado_civil_id ?? null,
            'interlocutor'      => $request->interlocutor ?? null,
            'celular'           => trim($request->celular),
            'correo'            => mb_strtoupper($request->correo),
            'estado'            => $request->estado ?? null,
            'codigo_alumno'     => intval(date('Y').$sequence->nextValue('COD_ALUMNO_SEQ')),
        ]);

        return $beneficiario;
    }

    public function storeDomicilio(Request $request, int $beneficiario_id)
    {

        $validations = Validator::make($request->all(), [
            'domicilio.direccion' => 'required|max:500',
            'domicilio.municipio_id' => 'required',
        ]);

        if ($validations->fails()) {
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

    public function storeDatosMedicos(Request $request, int $beneficiario_id)
    {

        $validations = Validator::make($request->all(), [
            'datos_medicos.enfermedades_alergias' => 'nullable|string|max:500',
            'datos_medicos.medicamentos' => 'nullable|string|max:500',
            'datos_medicos.dosis' => 'nullable|string|max:150',
        ]);

        if ($validations->fails()) {
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $datosMedicos = datos_medicos::create([
            'beneficiario_id' => $beneficiario_id,
            'enfermedades_alergias' => $request->datos_medicos['enfermedades_alergias'] ?? null,
            'medicamentos' => $request->datos_medicos['medicamentos'] ?? null,
            'dosis' => $request->datos_medicos['dosis'] ?? null,
            'tipo_sangre_id' => $request->datos_medicos['tipo_sangre_id'] ?? null,
        ]);

        return $datosMedicos;
    }

    public function storeResponsable(Request $request, int $beneficiario_id)
    {

        $validations = Validator::make($request->all(), [
            'responsable.cui' => ['required', 'numeric', 'digits:13', new ValidateCui],
            'responsable.nombres' => 'required|string|max:150',
            'responsable.apellidos' => 'required|string|max:150',
            'responsable.fecha_nacimiento' => 'required|date|date_format:Y-m-d|after:' . (date('Y') - 100) . '-12-31|before :' . date('Y-m-d'),
            'responsable.celular' => 'required|numeric|digits:8',
            'responsable.email' => 'nullable|email',
            'responsable.sexo' => 'required',
            'responsable.direccion' => 'nullable|string|max:200',
            'responsable.parentesco_id' => 'required',
        ]);

        if ($validations->fails()) {
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $responsable = responsables::create([
            'beneficiario_id' => $beneficiario_id,
            'cui' => $request->responsable['cui'] ?? null,
            'nombres' => strtoupper(trim($request->responsable['nombres'])),
            'apellidos' => strtoupper(trim($request->responsable['apellidos'])),
            'fecha_nacimiento' => $request->responsable['fecha_nacimiento'],
            'celular' => $request->responsable['celular'],
            'email' => $request->has('responsable.email') ? mb_strtoupper($request->responsable['email']) : null,
            'sexo' => $request->responsable['sexo'],
            'zona_id' => $request->responsable['zona_id'] ?? null,
            'direccion' => $request->responsable['direccion'] ?? null,
            'parentesco_id' => $request->responsable['parentesco_id'],
            'categoria' => 'R',
        ]);

        return $responsable;
    }

    public function storeEmergencia(Request $request, int $beneficiario_id)
    {

        $validations = Validator::make($request->all(), [
            'emergencia.cui' => ['nullable', 'numeric', 'digits:13', new ValidateCui],
            'emergencia.nombres' => 'required|string|max:150',
            'emergencia.apellidos' => 'required|string|max:150',
            'emergencia.fecha_nacimiento' => 'nullable|date|date_format:Y-m-d|after:' . (date('Y') - 100) . '-12-31|before :' . date('Y-m-d'),
            'emergencia.celular' => 'required|numeric|digits:8',
            'emergencia.email' => 'nullable|email',
            'emergencia.direccion' => 'nullable|string|max:200',
            'emergencia.sexo' => 'required',
            'emergencia.parentesco_id' => 'required',
        ]);

        if ($validations->fails()) {
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $emergencia = responsables::create([
            'beneficiario_id' => $beneficiario_id,
            'cui' => $request->emergencia['cui'] ?? null,
            'nombres' => mb_strtoupper(trim($request->emergencia['nombres'])),
            'apellidos' => mb_strtoupper(trim($request->emergencia['apellidos'])),
            'fecha_nacimiento' => $request->emergencia['fecha_nacimiento'] ?? null,
            'celular' => $request->emergencia['celular'],
            'email' => $request->has('emergencia.email') ? mb_strtoupper($request->emergencia['email']) : null,
            'sexo' => $request->emergencia['sexo'],
            'zona_id' => $request->emergencia['zona_id'] ?? null,
            'direccion' => $request->emergencia['direccion'] ?? null,
            'categoria' => 'E',
            'parentesco_id' => $request->emergencia['parentesco_id'],
        ]);

        return $emergencia;
    }

    public function storeDatosAcademicos(Request $request, int $beneficiario_id)
    {
        $validations = Validator($request->all(), [
            'datos_academicos.escolaridad_id' => 'required',
            'datos_academicos.tipo' => 'required',
        ]);

        if ($validations->fails()) {
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

    public function updateBeneficiario(Request $request, beneficiarios $beneficiario)
    {

        $validations = Validator::make($request->all(), [
            'cui' => ['required', 'numeric', 'digits:13', new ValidateCui, Rule::unique('beneficiarios', 'cui')->ignore($beneficiario->id)],
            'pasaporte' => 'nullable|string|max:45',
            'primer_nombre' => 'required|string|max:45',
            'segundo_nombre' => 'nullable|string|max:45',
            'primer_apellido' => 'required|string|max:45',
            'segundo_apellido' => 'nullable|string|max:45',
            'fecha_nacimiento' => 'required|date|date_format:Y-m-d|after:' . (date('Y') - 100) . '-12-31',
            'celular' => 'required|numeric|digits:8',
            'sexo' => 'required',
            'interlocutor' => 'nullable|numeric',
            'correo' => 'nullable|email'
        ]);

        if ($validations->fails()) {
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $beneficiario = $beneficiario->update([
            'cui'               => $request->cui,
            'primer_nombre'     => mb_strtoupper(trim($request->primer_nombre)),
            'segundo_nombre'    => mb_strtoupper(trim($request->segundo_nombre)) ?? null,
            'primer_apellido'   => mb_strtoupper(trim($request->primer_apellido)),
            'segundo_apellido'  => mb_strtoupper(trim($request->segundo_apellido)) ?? null,
            'fecha_nacimiento'  => $request->fecha_nacimiento,
            'sexo'              => $request->sexo,
            'pasaporte'         => $request->pasaporte ?? null,
            'etnia_id'          => $request->etnia_id ?? null,
            'estado_civil_id'   => $request->estado_civil_id ?? null,
            'interlocutor'      => $request->interlocutor ?? null,
            'celular'           => trim($request->celular),
            'correo'            => mb_strtoupper($request->correo),
            'estado'            => $request->estado ?? null,
            'deleted_at'        => $request->deleted_at
        ]);

        return $beneficiario;
    }

    public function updateDomicilio(Request $request, beneficiarios $beneficiario)
    {

        $validations = Validator::make($request->all(), [
            'domicilio.direccion' => 'required|max:500',
            'domicilio.municipio_id' => 'required',
        ]);

        if ($validations->fails()) {
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

    public function updateDatosMedicos(Request $request, beneficiarios $beneficiario)
    {

        $validations = Validator::make($request->all(), [
            'datos_medicos.enfermedades_alergias' => 'nullable|string|max:500',
            'datos_medicos.medicamentos' => 'nullable|string|max:500',
            'datos_medicos.dosis' => 'nullable|string|max:150',
        ]);

        if ($validations->fails()) {
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $datosMedicos = $beneficiario->datos_medicos()->update([
            'enfermedades_alergias' => $request->datos_medicos['enfermedades_alergias'] ?? null,
            'medicamentos' => $request->datos_medicos['medicamentos'] ?? null,
            'dosis' => $request->datos_medicos['dosis'] ?? null,
            'tipo_sangre_id' => $request->datos_medicos['tipo_sangre_id'] ?? null,
        ]);

        return $datosMedicos;
    }

    public function updateResponsable(Request $request, beneficiarios $beneficiario)
    {

        $validations = Validator::make($request->all(), [
            'responsable.cui' => ['nullable', 'numeric', 'digits:13', new ValidateCui],
            'responsable.nombres' => 'required|string|max:150',
            'responsable.apellidos' => 'required|string|max:150',
            'responsable.fecha_nacimiento' => 'required|date|date_format:Y-m-d|after:' . (date('Y') - 100) . '-12-31|before :' . date('Y-m-d'),
            'responsable.celular' => 'required|numeric|digits:8',
            'responsable.email' => 'nullable|email',
            'responsable.sexo' => 'required',
            'responsable.direccion' => 'nullable|string|max:200',
            'responsable.parentesco_id' => 'required',
        ]);

        if ($validations->fails()) {
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $responsable = $beneficiario->responsable()->update([
            'cui' => $request->responsable['cui'] ?? null,
            'nombres' => mb_strtoupper(trim($request->responsable['nombres'])),
            'apellidos' => mb_strtoupper(trim($request->responsable['apellidos'])),
            'fecha_nacimiento' => $request->responsable['fecha_nacimiento'],
            'celular' => $request->responsable['celular'],
            'email' => $request->has('responsable.email') ? mb_strtoupper($request->responsable['email']) : null,
            'sexo' => $request->responsable['sexo'],
            'zona_id' => $request->responsable['zona_id'] ?? null,
            'direccion' => $request->responsable['direccion'] ?? null,
            'parentesco_id' => $request->responsable['parentesco_id'],
            'categoria' => 'R',
        ]);

        return $responsable;
    }

    public function updateEmergencia(Request $request, beneficiarios $beneficiario)
    {

        $validations = Validator::make($request->all(), [
            'emergencia.cui' => ['nullable', 'numeric', 'digits:13', new ValidateCui],
            'emergencia.nombres' => 'required|string|max:150',
            'emergencia.apellidos' => 'required|string|max:150',
            'emergencia.fecha_nacimiento' => 'nullable|date|date_format:Y-m-d|after:' . (date('Y') - 100) . '-12-31|before :' . date('Y-m-d'),
            'emergencia.celular' => 'required|numeric|digits:8',
            'emergencia.email' => 'nullable|email',
            'emergencia.direccion' => 'nullable|string|max:200',
            'emergencia.sexo' => 'required',
            'emergencia.parentesco_id' => 'required',
        ]);

        if ($validations->fails()) {
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $emergencia = $beneficiario->emergencia()->update([
            'cui' => $request->emergencia['cui'] ?? null,
            'nombres' => mb_strtoupper(trim($request->emergencia['nombres'])),
            'apellidos' => mb_strtoupper(trim($request->emergencia['apellidos'])),
            'fecha_nacimiento' => $request->emergencia['fecha_nacimiento'] ?? null,
            'celular' => $request->emergencia['celular'],
            'email' => $request->has('emergencia.email') ? mb_strtoupper($request->emergencia['email']) : null,
            'sexo' => $request->emergencia['sexo'],
            'zona_id' => $request->emergencia['zona_id'] ?? null,
            'direccion' => $request->emergencia['direccion'] ?? null,
            'categoria' => 'E',
            'parentesco_id' => $request->emergencia['parentesco_id'],
        ]);

        return $emergencia;
    }

    public function updateDatosAcademicos(Request $request, beneficiarios $beneficiario)
    {

        $validations = Validator($request->all(), [
            'datos_academicos.escolaridad_id' => 'required',
            'datos_academicos.tipo' => 'required',
        ]);

        if ($validations->fails()) {
            $this->bagValidations = array_merge($this->bagValidations, $validations->errors()->toArray());
            return;
        }

        $datosAcademicos = $beneficiario->datos_academicos()->update([
            'escolaridad_id' => $request->datos_academicos['escolaridad_id'],
            'tipo' => $request->datos_academicos['tipo'],
            'establecimiento' => $request->has('datos_academicos.establecimiento') ? mb_strtoupper($request->datos_academicos['establecimiento']) : null,
            'titulo_carrera' => $request->has('datos_academicos.titulo_carrera') ? mb_strtoupper($request->datos_academicos['titulo_carrera']) : null,
        ]);

        return $datosAcademicos;
    }

    public function verifyRenapCui(string $cui): array {

        try {

            $cui = trim($cui);

            $consulta = renap_consultas::where('cui', $cui)->first();
            if ($consulta) {
                return ['data' => $consulta];
            }

            $tokenData = $this->tokenRenapGenerated();
            
            $token = $tokenData['token'] ?? null;

            if (!$token) {
                throw new \Exception("No se pudo obtener token de RENAP.");
            }

            // Consumir API RENAP
            $response = Http::withToken($token)->post(env('RENAP_URL_VERIFY_CUI', ''), [
                'busquedaCui' => ['cui' => $cui],
                'busquedaNombres' => [
                    'primerNombre' => null,
                    'segundoNombre' => null,
                    'primerApellido' => null,
                    'segundoApellido' => null,
                    'fechaNacimiento' => null
                ]
            ]);

            if (!$response->ok()) {
                throw new \Exception("Error al consultar RENAP: " . $response->body());
            }

            // Registrar historial
            renap_historial_consultas::create([
                'cui' => $cui,
                'usuario_id' => auth()->id(),
                'code_status_response' => $response['responseCode'],
                'message_response' => $response['mensaje'],
                'fecha_response' => $response['fecha'] ?? null,
                'hora_response' => $response['hora'] ?? null,
            ]);

            // Validaciones del mensaje
            $mensaje = $response['mensaje'] ?? '';
            $data = $response['data'][0] ?? null;

            if ($mensaje === 'No se encontraron resultados.') {
                if ($data && ($data['VALIDACION'] ?? null) === 'NO_HIT') {
                    throw new \Exception("CUI no válido.");
                }
            }

            if ($mensaje === 'Se muestran los resultados encontrados.' && $data) {
                renap_consultas::create([
                    'cui' => $data['CUI'] ?? null,
                    'primer_nombre' => $data['PRIMER_NOMBRE'] ?? null,
                    'segundo_nombre' => $data['SEGUNDO_NOMBRE'] ?? null,
                    'tercer_nombre' => $data['TERCER_NOMBRE'] ?? null,
                    'primer_apellido' => $data['PRIMER_APELLIDO'] ?? null,
                    'segundo_apellido' => $data['SEGUNDO_APELLIDO'] ?? null,
                    'apellido_casada' => $data['APELLIDO_CASADA'] ?? null,
                    'fecha_nacimiento' => $data['FECHA_NACIMIENTO'] ?? null,
                    'genero' => $data['GENERO'] ?? null,
                    'estado_civil' => $data['ESTADO_CIVIL'] ?? null,
                    'nacionalidad' => $data['NACIONALIDAD'] ?? null,
                    'pais_nacimiento' => $data['PAIS_NACIMIENTO'] ?? null,
                    'depto_nacimiento' => $data['DEPTO_NACIMIENTO'] ?? null,
                    'muni_nacimiento' => $data['MUNI_NACIMIENTO'] ?? null,
                    'vecindad' => $data['VECINDAD'] ?? null,
                    'orden_cedula' => $data['ORDEN_CEDULA'] ?? null,
                    'registro_cedula' => $data['REGISTRO_CEDULA'] ?? null,
                    'fecha_defuncion' => $data['FECHA_DEFUNCION'] ?? null,
                    'ocupacion' => $data['OCUPACION'] ?? null,
                    'fecha_vencimiento' => $data['FECHA_VENCIMIENTO'] ?? null,
                    'correlativo_dpi' => $data['CORRELATIVO_DPI'] ?? null,
                ]);

                return ['data' => $data];
            }

            throw new \Exception("Respuesta no esperada de RENAP: {$mensaje}");
        } catch (\Throwable $e) {
            Log::error("Error al verificar CUI RENAP: " . $e->getMessage());
            throw $e; // Se vuelve a lanzar para que el controlador lo capture si es necesario
        }
    }

    public function tokenRenapGenerated(): array {
        try {
            $token = $this->hasTokenGeneratedToday();

            if ($token) {
                return [
                    'token' => $token->token,
                    'token_expiry' => $token->token_expiry,
                ];
            }

            $response = Http::withHeaders([
                'user' => env('RENAP_USERNAME', ''),
                'pass' => env('RENAP_PASSWORD', ''),
            ])->post(env('RENAP_URL_TOKEN', ''));

            if (!$response->ok()) {
                throw new \Exception("Error al obtener token RENAP: " . $response->body());
            }

            $data = $response['data'] ?? null;

            if (!$data || !isset($data['token'], $data['expiracion'])) {
                throw new \Exception("Datos de token incompletos.");
            }

            renap_tokens::where('status',1)
                ->update([
                    'status' => 0
                ]);

            $tokenModel = renap_tokens::create([
                'token' => $data['token'],
                'token_expiry' => Carbon::createFromFormat('d/m/Y H:i:s', $data['expiracion'])->format('Y-m-d H:i:s'),
                'status' => 1,
            ]);

            return [
                'token' => $tokenModel->token,
                'token_expiry' => $tokenModel->token_expiry,
            ];
        } catch (\Throwable $e) {
            Log::error("Error al generar token RENAP: " . $e->getMessage());
            throw $e;
        }
    }

    public function hasTokenGeneratedToday(): ?renap_tokens {
        return renap_tokens::whereDate('created_at', now()->toDateString())
            ->where('status', 1)
            ->first();
    }
}
