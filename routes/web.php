<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteAuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EmpleadoAuthController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\OrdenController;

// Redirige al login por defecto
Route::get('/', function () {
    return redirect('/login');
});

// RUTAS DE LOGIN Y REGISTRO PARA CLIENTES (públicas)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
});

Route::middleware(['web'])->group(function () {
    Route::post('/empleado/login', [EmpleadoAuthController::class, 'login']);
    Route::post('/cliente/login', [ClienteAuthController::class, 'login']);
    
    // Rutas para MercadoPago
    Route::get('/pago/exitoso', [\App\Http\Controllers\MercadoPagoController::class, 'pagoExitoso'])->name('pago.exitoso');
    Route::get('/pago/error', [\App\Http\Controllers\MercadoPagoController::class, 'pagoError'])->name('pago.error');
    Route::get('/pago/pendiente', [\App\Http\Controllers\MercadoPagoController::class, 'pagoPendiente'])->name('pago.pendiente');
});

// LOGIN DE EMPLEADOS

Route::post('/empleados/register', [EmpleadoAuthController::class, 'register'])->name('register');
/* Route::post('/empleado/login', [EmpleadoAuthController::class, 'login']); */
Route::post('/empleado/logout', [EmpleadoAuthController::class, 'logout'])->name('empleado.logout');

// LOGIN DE CLIENTES
Route::post('/register', [ClienteAuthController::class, 'register'])->name('register');
/* Route::post('/cliente/login', [ClienteAuthController::class, 'login']); */
Route::post('/cliente/logout', [ClienteAuthController::class, 'logout'])->name('cliente.logout');

// RUTAS PROTEGIDAS PARA EMPLEADOS
Route::middleware(['auth:empleados'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('pages/dashboard');
    });

    Route::get('/ordenes/crear', function () {
        return view('pages/prendaForm');
    });

    Route::get('/ordenes', function () {
        return view('pages/listaOrdenes');
    });

    Route::get('/admin/clientes', function () {
        return view('pages/adminClientes');
    });

    Route::get('/api/user', function () {
        return response()->json(Auth::guard('empleados')->user());
    });

    // Rutas sólo para administradores
    Route::middleware(['admin.empleado'])->group(function () {
        Route::get('/empleado/register', function () {
            return view('auth/registerEmpleado');
        });

        Route::get('/admin/empleados', function () {
            return view('pages/adminEmpleados');
        });
    });
});

// RUTAS PROTEGIDAS PARA CLIENTES
Route::middleware(['auth:clientes'])->group(function () {
    Route::get('/cliente/ordenes', function () {
        return view('pages/ordenesCliente');
    });

    Route::get('/cliente/perfil', function () {
        return view('pages/perfilCliente');
    });

    Route::get('/cliente/api/user', function () {
        return response()->json(Auth::guard('clientes')->user());
    });
    
    // Listado de órdenes del cliente autenticado (API para la vista de Mis Órdenes)
    Route::get('/api/cliente/ordenes', function () {
        $cliente = Auth::guard('clientes')->user();
        $ordenes = App\Models\Orden::with(['detalles.prenda', 'pago'])
            ->where('IdCliente', $cliente->idCliente)
            ->orderByDesc('IdOrden')
            ->get()
            ->map(function ($orden) {
                return [
                    'id' => $orden->IdOrden,
                    'estado' => $orden->Estado,
                    'fecha_entrada' => $orden->FechaEntrada ?? $orden->FechaOrden,
                    'fecha_entrega' => $orden->FechaEntrega,
                    'metodo_pago' => optional($orden->pago)->MetodoPago,
                    'estado_pago' => optional($orden->pago)->Estado,
                    'total' => $orden->PrecioTotal,
                    'detalle_orden' => $orden->detalles->map(function ($d) {
                        return [
                            'nombre_prenda' => optional($d->prenda)->nombre,
                            'cantidad' => $d->Cantidad,
                            'peso' => $d->Peso,
                            'subtotal' => null
                        ];
                    }),
                ];
            });

        return response()->json($ordenes);
    });
    
    // Rutas para el perfil del cliente
    Route::post('/api/cliente/perfil/actualizar', [App\Http\Controllers\ClientePerfilController::class, 'actualizar']);
    Route::get('/api/cliente/ordenes/estadisticas', [App\Http\Controllers\ClientePerfilController::class, 'estadisticas']);
    
    // Rutas para el pago con MercadoPago
    Route::get('/cliente/pago/exito', [App\Http\Controllers\MercadoPagoController::class, 'pagoExitoso'])->name('pago.exitoso');
    Route::get('/cliente/pago/error', [App\Http\Controllers\MercadoPagoController::class, 'pagoError'])->name('pago.error');
    Route::get('/cliente/pago/pendiente', [App\Http\Controllers\MercadoPagoController::class, 'pagoPendiente'])->name('pago.pendiente');
    Route::get('/cliente/pago/{orden_id}', function ($orden_id) {
        return view('pages.pagoOrden', ['orden_id' => $orden_id]);
    });
    Route::get('/api/cliente/orden/{id}', function ($id) {
        $orden = App\Models\Orden::with(['detalles.prenda', 'pago'])->findOrFail($id);
        return response()->json($orden);
    });


});
