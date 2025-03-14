<?php

namespace App\Http\Controllers\ParticipacionCiudadana;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\carrusel_imagenes;
use Illuminate\Http\Request;

class CarouselController extends Controller
{
   public function index() {
        try {
            $imagenes = carrusel_imagenes::all();
            return response($imagenes);
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
   }
}
