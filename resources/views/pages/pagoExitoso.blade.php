@extends('template.template')

@section('tittle', 'Pago Exitoso')

@section('navbar')
    <div id="navbar"></div>
@endsection

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="mb-3">¡Pago Realizado con Éxito!</h2>
                        <p class="lead mb-4">
                            ¡Tu pago ha sido procesado correctamente!
                        </p>
                        <p class="mb-4">
                            Número de orden: <strong>#{{ $orden_id }}</strong>
                        </p>
                        <p class="text-muted mb-4">
                            Hemos enviado un comprobante a tu correo electrónico.
                        </p>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="/cliente/ordenes" class="btn btn-primary btn-lg px-4">
                                <i class="bi bi-bag-check me-2"></i> Ver mis órdenes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
