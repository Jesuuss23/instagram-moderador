@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <h2>Dashboard</h2>
        <p>Bienvenido, {{ auth()->user()->nombre }}</p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Usuarios</h5>
                <p class="card-text fs-2">0</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Cuentas IG</h5>
                <p class="card-text fs-2">0</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Conversaciones</h5>
                <p class="card-text fs-2">0</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Mensajes</h5>
                <p class="card-text fs-2">0</p>
            </div>
        </div>
    </div>
</div>
@endsection