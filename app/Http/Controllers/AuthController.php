<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Proceso el inicio de sesion con las credenciales del formulario.
     * Si el email o la contrasena son incorrectos, devuelvo un error generico
     * para no revelar si el email existe o no en la base de datos.
     */
    public function login(Request $request)
    {
        $credenciales = $request->only('email', 'password');

        if (Auth::attempt($credenciales)) {
            // Regenero la sesion para prevenir ataques de fijacion de sesion
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Registro una nueva patata (usuario) en la base de datos.
     *
     * Uso RegistroRequest para validar tanto los campos basicos como la whitelist
     * de partes del avatar. Antes esta validacion estaba inline en el controlador.
     */
    public function registrar(RegistroRequest $request)
    {
        $usuario = User::create([
            'name'               => $request->validated('name'),
            'email'              => $request->validated('email'),
            'password'           => Hash::make($request->validated('password')),
            'avatar_base'        => $request->validated('avatar_base'),
            'avatar_boca'        => $request->validated('avatar_boca'),
            'avatar_ojos'        => $request->validated('avatar_ojos'),
            'avatar_complemento' => $request->validated('avatar_complemento'),
        ]);

        // Hago login automatico para que no tenga que iniciar sesion manualmente
        Auth::login($usuario);

        // Si tengo verificacion de email activa, envio el correo de verificacion
        if ($usuario instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$usuario->hasVerifiedEmail()) {
            $usuario->sendEmailVerificationNotification();
        }

        return redirect()->to('/')->with('success', 'Bienvenida al club de las patatas lectoras.');
    }

    /**
     * Muestro el formulario de inicio de sesion.
     */
    public function mostrarLogin()
    {
        return view('auth.login');
    }

    /**
     * Muestro el formulario de registro.
     */
    public function mostrarRegistro()
    {
        return view('auth.registro');
    }

    /**
     * Cierro la sesion del usuario y limpio el token CSRF.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
