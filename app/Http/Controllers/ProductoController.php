<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index() {
        return Producto::with('categoria')->get();
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'precio_venta' => 'required|numeric|gt:0',
            'id_categoria' => 'required|exists:categoria,id'
        ], [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.max' => 'El nombre no puede superar :max caracteres.',
            'precio_venta.required' => 'El precio es obligatorio.',
            'precio_venta.numeric' => 'El precio debe ser un número.',
            'precio_venta.gt' => 'El precio debe ser mayor que 0.',
            'id_categoria.required' => 'Selecciona una categoría.',
            'id_categoria.exists' => 'La categoría seleccionada no existe.',
        ]);

        return Producto::create($validated);
    }

    public function show($id) {
        $producto = Producto::with('categoria')->find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        return response()->json($producto, 200);
    }

    public function update(Request $request, $id) {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
            'precio_venta' => 'sometimes|required|numeric|gt:0',
            'id_categoria' => 'sometimes|required|exists:categoria,id'
        ], [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.max' => 'El nombre no puede superar :max caracteres.',
            'precio_venta.required' => 'El precio es obligatorio.',
            'precio_venta.numeric' => 'El precio debe ser un número.',
            'precio_venta.gt' => 'El precio debe ser mayor que 0.',
            'id_categoria.required' => 'Selecciona una categoría.',
            'id_categoria.exists' => 'La categoría seleccionada no existe.',
        ]);

        $producto->update($validated);

        return response()->json([
            'message' => 'Producto actualizado',
            'producto' => $producto->fresh('categoria')
        ], 200);
    }

    public function destroy($id) {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $producto->delete();

        return response()->json(['message' => 'Producto eliminado correctamente'], 200);
    }
}