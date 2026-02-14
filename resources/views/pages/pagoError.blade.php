@extends('template.template')

@section('tittle', 'Error de Pago')

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
                            <i class="bi bi-exclamation-circle-fill text-danger" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="mb-3">Error en el Pago</h2>
                        <p class="lead mb-4">
                            {{ $mensaje }}
                        </p>
                        <p class="mb-4">
                            Número de orden: <strong>#{{ $orden_id }}</strong>
                        </p>
                        <p class="text-muted mb-4">
                            Por favor, intenta nuevamente o contacta con nuestro servicio de atención al cliente.
                        </p>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="/cliente/ordenes" class="btn btn-secondary me-md-2">
                                <i class="bi bi-arrow-left me-2"></i> Volver a mis órdenes
                            </a>
                            <a href="/cliente/pago/{{ $orden_id }}" class="btn btn-primary">
                                <i class="bi bi-credit-card me-2"></i> Intentar pagar nuevamente
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
