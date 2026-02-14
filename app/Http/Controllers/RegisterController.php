<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmailVerification;
use App\Notifications\SendVerificationCode;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $code = rand(100000, 999999);

        EmailVerification::create([
            'user_id' => $user->id,
            'code' => $code,
        ]);

        $user->notify(new SendVerificationCode($code));

        return response()->json([
            'message' => 'Usuario registrado. Verifica tu correo con el código enviado.',
        ]);
    }
}
