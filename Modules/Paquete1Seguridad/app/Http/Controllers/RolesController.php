<?php

namespace Modules\Paquete1Seguridad\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Paquete1Seguridad\Models\Rol;
use Modules\Paquete1Seguridad\Models\Paquete;
use Modules\Paquete1Seguridad\Models\PermisoRol;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{
    // Obtener todos los roles con sus permisos formateados
    public function index()
    {
        $roles = Rol::with('permisos.casoUso.paquete')->get();
        return response()->json($roles, 200);
    }

    // Obtener la estructura completa de Paquetes -> Casos de Uso para dibujar la UI
    public function getEstructura()
    {
        $paquetes = Paquete::with('casosUso')->get();
        return response()->json($paquetes, 200);
    }

    // Crear un nuevo rol con sus permisos
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:roles,nombre|max:50',
            'permisos' => 'array'
        ]);

        return DB::transaction(function () use ($request) {
            $rol = Rol::create(['nombre' => $request->nombre]);

            if ($request->has('permisos')) {
                foreach ($request->permisos as $permiso) {
                    PermisoRol::create([
                        'id_rol' => $rol->id,
                        'id_caso_uso' => $permiso['id_caso_uso'],
                        'puede_ver' => $permiso['puede_ver'] ?? false,
                        'puede_crear' => $permiso['puede_crear'] ?? false,
                        'puede_editar' => $permiso['puede_editar'] ?? false,
                        'puede_eliminar' => $permiso['puede_eliminar'] ?? false,
                    ]);
                }
            }

            return response()->json(['message' => 'Rol creado exitosamente', 'rol' => $rol->load('permisos')], 201);
        });
    }

    // Mostrar un rol específico
    public function show($id)
    {
        $rol = Rol::with('permisos.casoUso.paquete')->find($id);
        if (!$rol) return response()->json(['message' => 'Rol no encontrado'], 404);
        return response()->json($rol, 200);
    }

    // Actualizar un rol y sus permisos
    public function update(Request $request, $id)
    {
        $rol = Rol::find($id);
        if (!$rol) return response()->json(['message' => 'Rol no encontrado'], 404);

        $request->validate([
            'nombre' => 'sometimes|required|string|max:50|unique:roles,nombre,' . $id,
            'permisos' => 'array'
        ]);

        return DB::transaction(function () use ($request, $rol) {
            if ($request->has('nombre')) {
                $rol->update(['nombre' => $request->nombre]);
            }

            if ($request->has('permisos')) {
                // Borrar permisos anteriores y recrear
                PermisoRol::where('id_rol', $rol->id)->delete();
                
                foreach ($request->permisos as $permiso) {
                    PermisoRol::create([
                        'id_rol' => $rol->id,
                        'id_caso_uso' => $permiso['id_caso_uso'],
                        'puede_ver' => $permiso['puede_ver'] ?? false,
                        'puede_crear' => $permiso['puede_crear'] ?? false,
                        'puede_editar' => $permiso['puede_editar'] ?? false,
                        'puede_eliminar' => $permiso['puede_eliminar'] ?? false,
                    ]);
                }
            }

            return response()->json(['message' => 'Rol actualizado exitosamente', 'rol' => $rol->fresh('permisos.casoUso.paquete')], 200);
        });
    }

    // Eliminar un rol
    public function destroy($id)
    {
        $rol = Rol::find($id);
        if (!$rol) return response()->json(['message' => 'Rol no encontrado'], 404);

        $rol->delete();
        return response()->json(['message' => 'Rol eliminado correctamente'], 200);
    }
}
