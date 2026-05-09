<?php
namespace Modules\Paquete1Seguridad\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Modules\Paquete1Seguridad\Models\Autenticacion;
use Modules\Paquete1Seguridad\Models\CodigoRecuperacion;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PasswordRecoveryController extends Controller
{
    public function sendCode(Request $request)
    {
        $request->validate([
            'correo' => 'required|email'
        ]);

        $user = Autenticacion::with('persona')->where('correo', $request->correo)->first();

        if (!$user) {
            // Retornamos 200 aunque no exista para no revelar qué correos están registrados (Seguridad)
            return response()->json(['message' => 'Si el correo existe, se ha enviado un código de recuperación.'], 200);
        }

        // Generar código de 6 dígitos
        $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Guardar en BD (expira en 15 minutos)
        CodigoRecuperacion::create([
            'id_persona' => $user->id_persona,
            'codigo' => $codigo,
            'expira_el' => Carbon::now()->addMinutes(15)
        ]);

        // Enviar por correo
        $nombre = $user->persona ? $user->persona->nombre : 'Usuario';
        
        try {
            Mail::raw("Hola $nombre,\n\nTu código de recuperación para SaborXpress es: $codigo\n\nEste código expirará en 15 minutos.\n\nSi no solicitaste esto, ignora este correo.", function ($message) use ($user) {
                $message->to($user->correo)
                        ->subject('Código de Recuperación - SaborXpress');
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al enviar el correo. Verifica tu configuración de .env'], 500);
        }

        return response()->json(['message' => 'Si el correo existe, se ha enviado un código de recuperación.'], 200);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'codigo' => 'required|string|size:6'
        ]);

        $user = Autenticacion::where('correo', $request->correo)->first();
        if (!$user) return response()->json(['message' => 'Código inválido o expirado'], 400);

        $record = CodigoRecuperacion::where('id_persona', $user->id_persona)
            ->where('codigo', $request->codigo)
            ->where('expira_el', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Código inválido o expirado'], 400);
        }

        // Retornamos un token temporal o simplemente un OK para que pase a la pantalla de nueva contraseña
        return response()->json(['message' => 'Código verificado correctamente', 'token_temporal' => $record->codigo], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'codigo' => 'required|string|size:6',
            'nueva_contrasena' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()]
        ]);

        $user = Autenticacion::where('correo', $request->correo)->first();
        if (!$user) return response()->json(['message' => 'Datos inválidos'], 400);

        $record = CodigoRecuperacion::where('id_persona', $user->id_persona)
            ->where('codigo', $request->codigo)
            ->where('expira_el', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Código inválido o expirado'], 400);
        }

        // Actualizar contraseña
        $user->contrasena = Hash::make($request->nueva_contrasena);
        
        // Desbloquear si estaba bloqueado (CU4)
        $user->intentos_fallidos = 0;
        $user->bloqueado_hasta = null;
        $user->save();

        // Eliminar todos los códigos de este usuario para que no se puedan reusar
        CodigoRecuperacion::where('id_persona', $user->id_persona)->delete();

        return response()->json(['message' => 'Contraseña actualizada exitosamente. Ya puedes iniciar sesión.'], 200);
    }
}

