<?php

namespace Modules\Paquete5Ventas\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Paquete5Ventas\Models\Venta;
use Modules\Paquete5Ventas\Models\VentaDetalle;
use Modules\Paquete5Ventas\Models\Caja;
use Modules\Paquete4Inventarios\Models\Receta;
use Modules\Paquete5Ventas\Models\Comanda;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VentaController extends Controller
{
    /**
     * CU17: Registrar pedido (POS)
     */
    public function store(Request $request)
    {
        $request->validate([
            'metodo_pago' => 'required|in:Efectivo,QR',
            'tipo_entrega' => 'required|in:Mesa,Llevar',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto' => 'required|exists:producto,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric',
        ]);

        $usuarioId = Auth::id();

        // VALIDACIÓN CU16: Caja abierta
        $caja = Caja::where('id_usuario', $usuarioId)->where('estado', 'Abierta')->first();
        if (!$caja) {
            return response()->json(['message' => 'Debes abrir caja antes de realizar una venta.'], 403);
        }

        return DB::transaction(function () use ($request, $caja, $usuarioId) {
            
            // Calcular monto total
            $montoTotal = collect($request->detalles)->sum(fn($d) => $d['cantidad'] * $d['precio_unitario']);

            // Generar número de pedido correlativo del día (simplificado)
            $nroPedido = Venta::whereDate('created_at', today())->count() + 1;

            // Crear Venta
            $venta = Venta::create([
                'id_caja' => $caja->id,
                'id_usuario' => $usuarioId,
                'monto_total' => $montoTotal,
                'metodo_pago' => $request->metodo_pago,
                'codigo_qr' => $request->codigo_qr ?? null,
                'tipo_entrega' => $request->tipo_entrega,
                'estado' => 'Pagado', // En este flujo simplificado, el POS cobra inmediatamente
                'nro_pedido' => $nroPedido
            ]);

            // Crear Detalles y realizar descargo (CU32)
            foreach ($request->detalles as $det) {
                VentaDetalle::create([
                    'id_venta' => $venta->id,
                    'id_producto' => $det['id_producto'],
                    'cantidad' => $det['cantidad'],
                    'precio_unitario' => $det['precio_unitario'],
                    'subtotal' => $det['cantidad'] * $det['precio_unitario']
                ]);

                // CU32: Descargo automático de stock basado en recetas
                $this->descargarStock($det['id_producto'], $det['cantidad']);
            }

            // CU20: Crear Comanda para Cocina
            Comanda::create([
                'id_venta' => $venta->id,
                'estado' => 'Pendiente',
                'area' => 'Cocina' // Podría derivarse del tipo de producto en un futuro
            ]);

            return response()->json([
                'message' => 'Venta registrada con éxito',
                'nro_pedido' => $nroPedido,
                'venta' => $venta->load('detalles.producto')
            ], 201);
        });
    }

    /**
     * CU32: Lógica de descargo automático
     */
    private function descargarStock($idProducto, $cantidadVendida)
    {
        $recetas = Receta::where('id_producto', $idProducto)->get();

        foreach ($recetas as $receta) {
            $insumo = $receta->procesado;
            if ($insumo) {
                $cantidadADescontar = $receta->cantidad * $cantidadVendida;
                $insumo->decrement('stock', $cantidadADescontar);
            }
        }
    }

    public function index()
    {
        return Venta::with(['detalles.producto', 'usuario'])->latest()->take(50)->get();
    }
}
