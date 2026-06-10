<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Añado AuthorizesRequests para poder usar $this->authorize() en todos los controladores
    use AuthorizesRequests;
}
