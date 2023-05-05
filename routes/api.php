<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puede registrar rutas API para su aplicación. 
| Estas rutas las carga el RouteServiceProvider y todas ellas 
| se asignarán al grupo de middleware "api".
|
*/


/*
 *
 *Crear un nuevo Cliente
 *
*/

// POST Endpoint: /crearcliente
Route::post('/crearcliente', [ClienteController::class, 'crearCliente']);


/**
 *  
 * Muestra una lista de todos los clientes + fecha probable de muerte
 * 
*/

// GET Endpoint: /listclientes
Route::get('/listclientes', [ClienteController::class, 'listarClientes']);


/**
 * 
 * Muestra el Promedio de Edad entre todos los clientes
 * Muestra la Desviación estándar entre las edades de todos los clientes
 * 
*/

// GET Endpoint: /kpideclientes
Route::get('/kpideclientes', [ClienteController::class, 'obtenerPromedioYDesviacion']);