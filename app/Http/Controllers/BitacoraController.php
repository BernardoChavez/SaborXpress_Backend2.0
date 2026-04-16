<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;

class BitacoraController extends Controller
{
    public function index()
    {
        return Bitacora::with('usuario.persona')
            ->orderByDesc('id')
            ->get();
    }
}
