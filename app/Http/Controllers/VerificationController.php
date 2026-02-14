<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailVerification;


class VerificationController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required'
        ]);

        $verification = EmailVerification::where('user_id', $request->user_id)
            ->where('code', $request->code)
            ->first();

        if ($verification) {
            $user = \App\Models\User::find($request->user_id);
            $user->email_verified_at = now();
            $user->save();

            // Elimina el código luego de verificar
            $verification->delete();

            return response()->json(['message' => 'Correo verificado con éxito.']);
        }

        return response()->json(['message' => 'Código inválido o expirado.'], 422);
    }
}
