<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ClienteAuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'correoElectronico' => 'required|string|email|max:255|unique:clientes',
            'telefono' => 'required|string|max:255',
            'direccion' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $cliente  = Cliente::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'correoElectronico' => $request->correoElectronico,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'password' => Hash::make($request->password),
        ]);

        Auth::guard('clientes')->login($cliente);

        return response()->json(['message' => 'Cliente registrado exitosamente!'], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('correoElectronico', 'password');

        if (Auth::guard('clientes')->attempt([
            'correoElectronico' => $credentials['correoElectronico'],
            'password' => $credentials['password']
        ])) {

            $request->session()->regenerate();

            return response()->json(['message' => 'Login correcto'], 200);
        }

        return response()->json([
            'error' => 'Las credenciales son incorrectas o el usuario no existe.'
        ], 401);
    }


    public function logout(Request $request)
    {
        Auth::guard('clientes')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
