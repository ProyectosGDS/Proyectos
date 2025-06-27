<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class renap_historial_consultas extends Model
{
    const UPDATED_AT = null;

    protected $connection = 'gds';
    protected $table = 'RENAP_HISTORIAL_CONSULTAS';

    protected $fillable = [
        'cui',
        'usuario_id',
        'code_status_response',
        'message_response',
        'fecha_response',
        'hora_response',
    ];
}
