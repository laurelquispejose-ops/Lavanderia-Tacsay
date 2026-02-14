<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Enums\Cargo;
use Illuminate\Validation\Rules\Enum;

class EmpleadoAuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'Nombre' => 'required|string|max:255',
            'CorreoElectronico' => 'required|string|email|max:255|unique:empleados',
            'Telefono' => 'required|string|max:255',
            'Direccion' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'Cargo' => ['required', new Enum(Cargo::class)],
        ]);

        $empleado  = Empleado::create([
            'Nombre' => $request->Nombre,
            'CorreoElectronico' => $request->CorreoElectronico,
            'Telefono' => $request->Telefono,
            'Direccion' => $request->Direccion,
            'password' => Hash::make($request->password),
            'Cargo' => $request->Cargo
        ]);

        Auth::guard('empleados')->login($empleado);

        return response()->json(['message' => 'Empleado registrado exitosamente!'], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('correoElectronico', 'password');

        if (Auth::guard('empleados')->attempt([
            'CorreoElectronico' => $credentials['correoElectronico'],
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
        Auth::guard('empleados')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
