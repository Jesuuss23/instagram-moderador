@extends('layouts.app')

@section('title', 'Cuentas de Instagram')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Cuentas de Instagram Conectadas</h2>
    <a href="{{ route('admin.cuentas.conectar') }}" class="btn btn-primary">
        <i class="fab fa-instagram"></i> Conectar Nueva Cuenta
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if(session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
@endif

<div class="row">
    @forelse($cuentas as $cuenta)
        <div class="col-md-4 mb-4">
            <div class="card {{ $cuenta->activo ? '' : 'bg-secondary text-white' }}">
                <div class="card-body text-center">
                    @if($cuenta->foto_perfil_url)
                        <img src="{{ $cuenta->foto_perfil_url }}" class="rounded-circle mb-3" width="80" height="80">
                    @else
                        <i class="fab fa-instagram fa-4x mb-3"></i>
                    @endif
                    
                    <h5 class="card-title">{{ $cuenta->username_ig }}</h5>
                    <p class="card-text">{{ $cuenta->nombre_cuenta }}</p>
                    
                    <span class="badge {{ $cuenta->activo ? 'bg-success' : 'bg-danger' }}">
                        {{ $cuenta->activo ? 'Activa' : 'Inactiva' }}
                    </span>
                    
                    <div class="mt-3">
                        @if($cuenta->activo)
                            <form action="{{ route('admin.cuentas.destroy', $cuenta->id_cuenta_ig) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Desconectar esta cuenta?')">
                                    <i class="fas fa-unlink"></i> Desconectar
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.cuentas.reactivar', $cuenta->id_cuenta_ig) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-link"></i> Reactivar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                No hay cuentas de Instagram conectadas. 
                <a href="{{ route('admin.cuentas.conectar') }}">Conecta tu primera cuenta</a>
            </div>
        </div>
    @endforelse
</div>
@endsection