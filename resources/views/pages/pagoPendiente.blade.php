@extends('template.template')

@section('tittle', 'Pago Pendiente')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h3 class="mb-0">Pago en Proceso</h3>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-clock fa-5x text-warning"></i>
                    </div>
                    <h4>Tu pago está siendo procesado</h4>
                    <p class="lead">
                        El pago para la orden #{{ $orden_id }} está pendiente de confirmación.
                    </p>
                    <p>
                        Recibirás una notificación cuando el pago sea confirmado. Este proceso puede tomar unos minutos.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('cliente.ordenes') }}" class="btn btn-primary">
                            Ver mis órdenes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
