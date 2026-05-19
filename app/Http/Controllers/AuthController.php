<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Maneja el inicio de sesión de usuarios existentes.
     */
    public function login(Request $request)
    {
        $credenciales = $request->only('email', 'password');

        if (Auth::attempt($credenciales)) {
            // Regeneramos la sesión para mayor seguridad
            $request->session()->regenerate();

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email'); // Esto mantiene el email que el usuario escribió para que no tenga que volver a teclearlo
    }

    /**
     * Registra una nueva patata (usuario) en la base de datos.
     */
    public function registrar(Request $request)
    {
        // 1. Validación de los datos recibidos
        $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email',
            'password'           => 'required|min:6',
            'avatar_base'        => 'required',
            'avatar_boca'        => 'required',
            'avatar_ojos'        => 'required',
            'avatar_complemento' => 'required',
        ], [
            'email.unique' => '¡Esta patata ya tiene dueño! El correo ya está registrado.',
            'required'     => '¡Tu patata no puede nacer incompleta! Elige todas las opciones.'
        ]);

        // 2. Creación del usuario
        // NOTA: No concatenamos '.png' ni 'Relleno.png' porque el formulario ya envía el nombre completo.
        $usuario = User::create([
            'name'               => $request->name,
            'email'              => $request->email,
            'password'           => Hash::make($request->password),
            'avatar_base'        => $request->avatar_base,
            'avatar_boca'        => $request->avatar_boca,
            'avatar_ojos'        => $request->avatar_ojos,
            'avatar_complemento' => $request->avatar_complemento,
        ]);

        // 3. Logueamos automáticamente al usuario recién creado
        Auth::login($usuario);

        return redirect()->to('/')->with('success', '¡Bienvenida al club de las patatas lectoras!');
    }

    public function mostrarLogin()
    {
        // Cambiamos 'login' por 'auth.login'
        return view('auth.login');
    }

    public function mostrarRegistro()
    {
        // Cambiamos 'registro' por 'auth.registro'
        return view('auth.registro');
    }

    /**
     * Cierra la sesión y limpia la información del navegador.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
