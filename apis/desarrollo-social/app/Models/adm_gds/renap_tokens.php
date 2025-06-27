<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class renap_tokens extends Model
{
    const UPDATED_AT = null;

    protected $connection = 'gds';
    protected $table = 'RENAP_TOKENS';
    
    protected $fillable = [
        'token',
        'token_expiry',
        'status',
    ];
}
