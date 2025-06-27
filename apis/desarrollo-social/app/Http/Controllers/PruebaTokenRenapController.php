<?php

namespace App\Http\Controllers;

use App\Traits\TraitBeneficiarios;
use Illuminate\Http\Request;

class PruebaTokenRenapController extends Controller
{
    use TraitBeneficiarios;

    public function tokenGenerate() {
        try {
            $token = $this->tokenRenapGenerated();
            return response($token);
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function verifyCui() {
        try {
            $data = $this->verifyRenapCui('2733271000101');
            return response($data);
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
