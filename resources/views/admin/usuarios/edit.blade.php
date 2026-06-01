@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Editar Usuario: {{ $usuario->nombre }}</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.usuarios.update', $usuario->id_usuario) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="{{ $usuario->nombre }}" required>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ $usuario->email }}" required>
            </div>
            
            <div class="mb-3">
                <label for="username" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="username" name="username" value="{{ $usuario->username }}" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Nueva Contraseña (opcional)</label>
                <input type="password" class="form-control" id="password" name="password">
                <small class="text-muted">Dejar vacío para mantener la contraseña actual</small>
            </div>
            
            <div class="mb-3">
                <label for="id_rol" class="form-label">Rol</label>
                <select class="form-control" id="id_rol" name="id_rol" required>
                    @foreach($roles as $rol)
                        <option value="{{ $rol->id_rol }}" {{ $usuario->id_rol == $rol->id_rol ? 'selected' : '' }}>
                            {{ $rol->nombre_role }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection