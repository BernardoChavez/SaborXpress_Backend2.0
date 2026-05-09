<?php

namespace Modules\Paquete5Ventas\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Paquete5Ventas\Models\Caja;
use Modules\Paquete5Ventas\Models\Venta;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CajaController extends Controller
{
    /**
     * Obtener el estado de la caja actual para el usuario logueado.
     */
    public function getEstadoActual()
    {
        $usuarioId = Auth::id();
        $caja = Caja::where('id_usuario', $usuarioId)
                    ->where('estado', 'Abierta')
                    ->first();

        if (!$caja) {
            return response()->json([
                'abierta' => false,
                'caja' => null
            ]);
        }

        // Sumar ventas por método de pago
        $ventasEfectivo = Venta::where('id_caja', $caja->id)->where('estado', 'Pagado')->where('metodo_pago', 'Efectivo')->sum('monto_total');
        $ventasQR = Venta::where('id_caja', $caja->id)->where('estado', 'Pagado')->where('metodo_pago', 'QR')->sum('monto_total');

        return response()->json([
            'abierta' => true,
            'caja' => array_merge($caja->toArray(), [
                'ventas_efectivo' => (float)$ventasEfectivo,
                'ventas_qr' => (float)$ventasQR,
                'ventas_totales' => (float)($ventasEfectivo + $ventasQR)
            ])
        ]);
    }

    /**
     * CU16: Abrir caja con un monto inicial.
     */
    public function abrir(Request $request)
    {
        $request->validate([
            'monto_apertura' => 'required|numeric|min:0',
            'monto_apertura_qr' => 'required|numeric|min:0'
        ]);

        $usuarioId = Auth::id();

        // Verificar si ya tiene una caja abierta
        $existe = Caja::where('id_usuario', $usuarioId)->where('estado', 'Abierta')->exists();
        if ($existe) {
            return response()->json(['message' => 'Ya tienes una caja abierta.'], 400);
        }

        $caja = Caja::create([
            'id_usuario' => $usuarioId,
            'monto_apertura' => $request->monto_apertura,
            'monto_apertura_qr' => $request->monto_apertura_qr,
            'fecha_apertura' => Carbon::now(),
            'estado' => 'Abierta'
        ]);

        return response()->json([
            'message' => 'Caja abierta con éxito',
            'caja' => $caja
        ], 201);
    }

    /**
     * CU16: Cerrar caja y generar reporte comparativo.
     */
    public function cerrar(Request $request)
    {
        $request->validate([
            'monto_real' => 'required|numeric|min:0',
            'monto_real_qr' => 'required|numeric|min:0'
        ]);

        $usuarioId = Auth::id();
        $caja = Caja::where('id_usuario', $usuarioId)->where('estado', 'Abierta')->first();

        if (!$caja) {
            return response()->json(['message' => 'No hay una caja abierta para cerrar.'], 400);
        }

        // Calcular ventas por método
        $totalEfectivo = Venta::where('id_caja', $caja->id)->where('estado', 'Pagado')->where('metodo_pago', 'Efectivo')->sum('monto_total');
        $totalQR = Venta::where('id_caja', $caja->id)->where('estado', 'Pagado')->where('metodo_pago', 'QR')->sum('monto_total');

        $montoAperturaEfectivo = (float)$caja->monto_apertura;
        $montoAperturaQR = (float)($caja->monto_apertura_qr ?? 0);
        
        $saldoEsperadoEfectivo = $montoAperturaEfectivo + $totalEfectivo;
        $saldoEsperadoQR = $montoAperturaQR + $totalQR;
        
        $diferenciaEfectivo = $request->monto_real - $saldoEsperadoEfectivo;
        $diferenciaQR = $request->monto_real_qr - $saldoEsperadoQR;

        $caja->update([
            'monto_cierre' => $request->monto_real,
            'monto_cierre_qr' => $request->monto_real_qr,
            'fecha_cierre' => Carbon::now(),
            'estado' => 'Cerrada'
        ]);

        return response()->json([
            'message' => 'Caja cerrada con éxito',
            'reporte' => [
                'monto_apertura_efectivo' => $montoAperturaEfectivo,
                'monto_apertura_qr' => $montoAperturaQR,
                'ventas_efectivo' => $totalEfectivo,
                'ventas_qr' => $totalQR,
                'monto_esperado_fisico' => $saldoEsperadoEfectivo,
                'monto_real_fisico' => $request->monto_real,
                'diferencia_efectivo' => $diferenciaEfectivo,
                'monto_esperado_qr' => $saldoEsperadoQR,
                'monto_real_qr' => $request->monto_real_qr,
                'diferencia_qr' => $diferenciaQR,
                'total_ingresos' => ($request->monto_real - $montoAperturaEfectivo) + ($request->monto_real_qr - $montoAperturaQR),
                'observacion' => "Efectivo: " . ($diferenciaEfectivo == 0 ? 'OK' : $diferenciaEfectivo) . " | QR: " . ($diferenciaQR == 0 ? 'OK' : $diferenciaQR)
            ]
        ]);
    }
}
