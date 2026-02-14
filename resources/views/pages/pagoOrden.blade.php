@extends('template.template')

@section('tittle', 'Pago de Orden')

@section('navbar')
    <div id="navbar"></div>
@endsection

@section('content')
    <main id='app'>
        <pago_orden :orden_id="{{ $orden_id }}" />
    </main>
@endsection
