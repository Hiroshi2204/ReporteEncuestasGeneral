<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReporteDocenteController;
use App\Http\Controllers\ReportePDFController;
use App\Http\Controllers\TrabajadorController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|


Route::get('/', function () {
    return view('welcome');
});
*/

// ✅ Mostrar listado de escuelas
Route::get('/reportes/escuelas', [ReportePDFController::class, 'listaEscuelas'])->name('reportes.escuelas');

// ✅ Descargar PDF por escuela
Route::get('/reportes/escuela/{cod}', [ReportePDFController::class, 'reportePorEscuela'])->name('reportes.por_escuela');

// ✅ Descargar PDF por escuela orden de merito general
Route::get('reportes/escuela/general/{cod}', [ReportePDFController::class, 'reporteGeneral']);

// REPORTE DE QUIENES HICIERON LA ENCUESTA Y QUIENES NO
Route::get('/reporte-encuestas', [ReportePDFController::class, 'index'])->name('reporte.encuestas');
Route::get('/reporte-escuela/{nombre}', [ReportePDFController::class, 'reporteEscuela'])->name('reporte.escuela');
Route::get('/reporte-escuela/{nombre}/pdf', [ReportePDFController::class, 'reportePDF'])->name('reporte.escuela.pdf');




Route::get('/reportes/generar', [ReportePDFController::class, 'generarPorEscuelas']);




Route::group(['middleware' => ['cors']], function () {
});
//Route::group(['middleware' => ['cors']], function () {
//Route::group(['middleware' => ['jwt.verify', 'cors']], function () {
Route::middleware(['web'])->group(function () {
    // Rutas aquí
});
Route::group(['middleware' => ['jwt.verify', 'cors']], function () {

    /*
    // Actualizar contraseña
    Route::put('password/update', 'AuthController@updatePassword');

    //Trabajador
    Route::post('trabajador/create','TrabajadorController@create');
    Route::put('trabajador/update/{id_trabajador}','TrabajadorController@update');
    Route::delete('trabajador/delete/{id_trabajador}','TrabajadorController@delete');
    Route::get('trabajador/get','TrabajadorController@get');

    //Persona
    Route::get('persona/show','PersonaController@getShow');
    Route::get('persona/get','PersonaController@get');
    Route::post('persona/store','PersonaController@store');
    Route::post('persona/update/{id}','PersonaController@update');
    Route::delete('persona/delete/{id}','PersonaController@delete');
    Route::delete('persona/destroy/{id}','PersonaController@destroy');
    //Producto
    Route::post('producto/create', 'ProductoController@create');
    Route::put('producto/update/{id}', 'ProductoController@update');
    Route::delete('producto/delete/{id}', 'ProductoController@delete');
    Route::get('producto/get', 'ProductoController@get');
    Route::post('producto/asignar/{id_producto}', 'ProductoController@asignar_almacen');
    //Salida y Entrada de Productos
    Route::post('producto/exportacion/{id_producto}', 'ProductoController@salida_productos');
    Route::post('producto/importar/{id_producto}', 'ProductoController@entrada_productos');

    //Reportes PDF's
    Route::get('/producto/reporte/entrada', 'ReportePDFController@reporte_equipos_entrada');
    Route::get('/producto/reporte/stock', 'ReportePDFController@reporte_equipos_stock');
    Route::get('/producto/reporte/salida', 'ReportePDFController@reporte_equipos_salida');
*/
});
