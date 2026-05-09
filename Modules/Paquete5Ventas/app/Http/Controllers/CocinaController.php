<?php
 
namespace Modules\Paquete5Ventas\Http\Controllers;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Paquete5Ventas\Models\Comanda;
 
class CocinaController extends Controller
{
    /**
     * Listar comandas pendientes y en preparación
     */
    public function index()
    {
        return Comanda::with(['venta.detalles.producto'])
            ->whereIn('estado', ['Pendiente', 'En preparación', 'Listo'])
            ->orderBy('created_at', 'asc')
            ->get();
    }
 
    /**
     * Actualizar estado de la comanda (CU22)
     */
    public function updateEstado(Request $request, $id)
    {
        $comanda = Comanda::findOrFail($id);
        $comanda->update(['estado' => $request->estado]);
 
        return response()->json(['message' => 'Estado actualizado', 'comanda' => $comanda]);
    }
}
