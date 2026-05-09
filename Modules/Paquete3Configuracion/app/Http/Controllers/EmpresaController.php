<?php

namespace Modules\Paquete3Configuracion\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Paquete3Configuracion\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function show()
    {
        $empresa = Empresa::first();
        if (!$empresa) {
            $empresa = Empresa::create([
                'nombre' => 'SaborXpress',
                'nit' => '123456789',
                'direccion' => 'Av. Principal #123',
                'telefono' => '70000000',
                'correo' => 'contacto@saborxpress.com',
                'moneda' => 'Bs.'
            ]);
        }
        return response()->json($empresa, 200);
    }

    public function update(Request $request)
    {
        $empresa = Empresa::first();
        if (!$empresa) {
            $empresa = Empresa::create(['nombre' => 'SaborXpress']);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
            'nit' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:200',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:100',
            'moneda' => 'sometimes|required|string|max:10'
        ]);

        $empresa->update($validated);

        return response()->json([
            'message' => 'Datos de empresa actualizados',
            'empresa' => $empresa
        ], 200);
    }
}

