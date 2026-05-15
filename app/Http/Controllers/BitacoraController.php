<?php

/**
 * APARTADO: AUDITORÍA TÉCNICA (BACKEND)
 * CONTROLADOR: BitacoraController.php
 * FUNCIÓN: Proporciona los datos del registro de auditoría. Permite al Administrador
 *          revisar el historial de acciones y el rendimiento de las peticiones.
 */

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
