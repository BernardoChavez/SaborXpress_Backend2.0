<?php
namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller {
    public function store(Request $request) {
        $request->validate(['nombre' => 'required|string|max:50|unique:categoria,nombre'], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.max' => 'El nombre no puede superar :max caracteres.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',
        ]);
        $categoria = Categoria::create(['nombre' => trim($request->nombre)]);
        return response()->json($categoria, 201);
    }

    public function index() {
        return response()->json(Categoria::all(), 200);
    }

    public function show($id) {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        return response()->json($categoria, 200);
    }

    public function update(Request $request, $id) {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        $request->validate([
            'nombre' => 'required|string|max:50|unique:categoria,nombre,' . $categoria->id
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.max' => 'El nombre no puede superar :max caracteres.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',
        ]);
        $categoria->update(['nombre' => trim($request->nombre)]);

        return response()->json([
            'message' => 'Categoría actualizada',
            'categoria' => $categoria
        ], 200);
    }

    public function destroy($id) {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        $categoria->delete();

        return response()->json(['message' => 'Categoría eliminada correctamente'], 200);
    }
}