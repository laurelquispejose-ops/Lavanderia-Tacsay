<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    // Obtener todos los clientes
    public function index()
    {
        return Cliente::all();
    }


    // Guardar un nuevo cliente
    public function store(Request $request)
    {
        $cliente = Cliente::create($request->all());
        return response()->json($cliente, 201);
    }

    // Obtener un cliente por ID
    public function show($id)
    {
        return Cliente::findOrFail($id);
    }

    // Actualizar un cliente
    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->update($request->all());
        return response()->json($cliente, 200);
    }

    // Eliminar un cliente
    public function destroy($id)
    {
        Cliente::destroy($id);
        return response()->json(null, 204);
    }
}
