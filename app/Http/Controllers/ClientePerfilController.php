<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Cliente;

class ClientePerfilController extends Controller
{
    /**
     * Actualiza el perfil del cliente autenticado
     */
    public function actualizar(Request $request)
    {
        // Validar datos básicos
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'password_actual' => 'nullable|string',
            'password_nuevo' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Obtener el cliente autenticado
        $cliente = Auth::guard('clientes')->user();

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no autenticado'
            ], 401);
        }

        // Verificar si el email ya está en uso por otro cliente
        $emailExistente = Cliente::where('correoElectronico', $request->email)
            ->where('idCliente', '!=', $cliente->idCliente)
            ->first();

        if ($emailExistente) {
            return response()->json([
                'success' => false,
                'message' => 'El correo electrónico ya está en uso por otro cliente'
            ], 422);
        }

        // Actualizar datos básicos
        $cliente->nombre = $request->nombre;
        $cliente->apellido = $request->apellido; // ahora persistimos apellido
        $cliente->correoElectronico = $request->email;
        $cliente->telefono = $request->telefono;
        $cliente->direccion = $request->direccion;

        // Si se proporciona contraseña actual y nueva, actualizar contraseña
        if ($request->filled('password_actual') && $request->filled('password_nuevo')) {
            // Verificar que la contraseña actual sea correcta
            if (!Hash::check($request->password_actual, $cliente->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta'
                ], 422);
            }

            // Actualizar contraseña
            $cliente->password = Hash::make($request->password_nuevo);
        }

        // Guardar cambios
        $cliente->save();

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente'
        ]);
    }

    /**
     * Obtiene las estadísticas de órdenes del cliente
     */
    public function estadisticas()
    {
        $cliente = Auth::guard('clientes')->user();

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no autenticado'
            ], 401);
        }

        // Obtener órdenes del cliente
        $ordenes = $cliente->ordenes;

        // Calcular estadísticas
        $estadisticas = [
            'total' => $ordenes->count(),
            'pendientes' => $ordenes->where('estado', 'pendiente')->count(),
            'enProceso' => $ordenes->whereIn('estado', ['en proceso lavado', 'en proceso planchado'])->count(),
            'finalizadas' => $ordenes->where('estado', 'finalizado')->count()
        ];

        return response()->json($estadisticas);
    }
}
