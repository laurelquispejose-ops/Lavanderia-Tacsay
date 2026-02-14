<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DniController extends Controller
{
    public function consultarDni(Request $request)
    {
        $request->validate([
            'numero' => 'required|digits:8',
        ]);

        $numero = $request->numero;
        $token = config('services.reniec.token') ?: 'sk_12876.vqjhJAnKHZiQZzMDl2QChY4aoqkKsKNz';
        $apiUrl = config('services.reniec.url') ?: 'https://api.decolecta.com/v1/reniec/dni?numero={numero}';
        $url = str_replace('{numero}', $numero, $apiUrl);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $normalized = [
                    'nombres' => $data['nombres'] ?? $data['first_name'] ?? '',
                    'apellidoPaterno' => $data['apellidoPaterno'] ?? $data['first_last_name'] ?? '',
                    'apellidoMaterno' => $data['apellidoMaterno'] ?? $data['second_last_name'] ?? '',
                    'documento' => $data['documento'] ?? $data['document_number'] ?? $numero,
                ];
                return response()->json($normalized);
            }

            if ($response->status() === 401) {
                return response()->json([
                    'error' => 'Token inválido o faltante para el proveedor DNI',
                    'details' => $response->body()
                ], 401);
            }

            return response()->json([
                'error' => 'No se pudo encontrar información con ese DNI',
                'details' => $response->body()
            ], $response->status() ?: 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al consultar el DNI',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
