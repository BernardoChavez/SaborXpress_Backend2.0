<?php

namespace Modules\Paquete4Inventarios\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Paquete4Inventarios\Models\InventarioBruto;
use Modules\Paquete4Inventarios\Models\InventarioProcesado;
use Modules\Paquete4Inventarios\Models\FichaTransformacion;
use Modules\Paquete4Inventarios\Models\Receta;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    // --- CU12: Inventario Bruto ---
    public function indexBruto()
    {
        return InventarioBruto::all();
    }

    public function storeBruto(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'stock' => 'required|numeric|min:0',
            'unidad_medida' => 'required|string|max:20',
            'stock_minimo' => 'required|numeric|min:0'
        ]);
        return InventarioBruto::create($validated);
    }

    public function updateBruto(Request $request, $id)
    {
        $item = InventarioBruto::findOrFail($id);
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'stock' => 'required|numeric|min:0',
            'unidad_medida' => 'required|string|max:20',
            'stock_minimo' => 'required|numeric|min:0'
        ]);
        $item->update($validated);
        return $item;
    }

    // --- CU13: Inventario Procesado ---
    public function indexProcesado()
    {
        return InventarioProcesado::all();
    }

    public function storeProcesado(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'stock' => 'required|numeric|min:0',
            'unidad_medida' => 'required|string|max:20',
            'stock_minimo' => 'required|numeric|min:0'
        ]);
        return InventarioProcesado::create($validated);
    }

    public function updateProcesado(Request $request, $id)
    {
        $item = InventarioProcesado::findOrFail($id);
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'stock' => 'required|numeric|min:0',
            'unidad_medida' => 'required|string|max:20',
            'stock_minimo' => 'required|numeric|min:0'
        ]);
        $item->update($validated);
        return $item;
    }

    public function indexFichas()
    {
        return FichaTransformacion::with(['bruto', 'procesado'])->get();
    }

    public function indexRecetas()
    {
        return Receta::with(['producto', 'procesado'])->get();
    }

    // --- CU38: Transformación (Bruto -> Procesado) ---
    public function transformar(Request $request)
    {
        $request->validate([
            'id_bruto' => 'required|exists:inventario_bruto,id',
            'id_procesado' => 'required|exists:inventario_procesado,id',
            'cantidad_bruto' => 'required|numeric|min:0.01'
        ]);

        return DB::transaction(function () use ($request) {
            $bruto = InventarioBruto::findOrFail($request->id_bruto);
            $procesado = InventarioProcesado::findOrFail($request->id_procesado);

            // Buscar la ficha de transformación
            $ficha = FichaTransformacion::where('id_bruto', $bruto->id)
                                        ->where('id_procesado', $procesado->id)
                                        ->first();

            if (!$ficha) {
                return response()->json(['message' => 'No existe una ficha de transformación definida para estos elementos.'], 400);
            }

            if ($bruto->stock < $request->cantidad_bruto) {
                return response()->json(['message' => 'Stock insuficiente en inventario bruto.'], 400);
            }

            // Calcular cuánto procesado se genera
            // Regla: cantidad_bruto (enviada) * (cantidad_procesado_ficha / cantidad_bruto_ficha)
            $factor = $ficha->cantidad_procesado / $ficha->cantidad_bruto;
            $generado = $request->cantidad_bruto * $factor;

            // Actualizar stocks
            $bruto->decrement('stock', $request->cantidad_bruto);
            $procesado->increment('stock', $generado);

            return response()->json([
                'message' => 'Transformación completada',
                'descontado_bruto' => $request->cantidad_bruto . ' ' . $bruto->unidad_medida,
                'generado_procesado' => $generado . ' ' . $procesado->unidad_medida
            ]);
        });
    }

    // --- CU38: Gestión de Fichas y Recetas ---
    public function storeFicha(Request $request)
    {
        $validated = $request->validate([
            'id_bruto' => 'required|exists:inventario_bruto,id',
            'id_procesado' => 'required|exists:inventario_procesado,id',
            'cantidad_bruto' => 'required|numeric|min:0.01',
            'cantidad_procesado' => 'required|numeric|min:0.01'
        ]);
        return FichaTransformacion::updateOrCreate(
            ['id_bruto' => $validated['id_bruto'], 'id_procesado' => $validated['id_procesado']],
            $validated
        );
    }

    public function storeReceta(Request $request)
    {
        $validated = $request->validate([
            'id_producto' => 'required|exists:producto,id',
            'id_procesado' => 'required|exists:inventario_procesado,id',
            'cantidad' => 'required|numeric|min:0.01'
        ]);
        return Receta::updateOrCreate(
            ['id_producto' => $validated['id_producto'], 'id_procesado' => $validated['id_procesado']],
            $validated
        );
    }
    
    public function getRecetas($id_producto)
    {
        return Receta::with('procesado')->where('id_producto', $id_producto)->get();
    }
}
