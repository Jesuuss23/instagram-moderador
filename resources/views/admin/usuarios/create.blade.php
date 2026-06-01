@extends('layouts.app')

@section('title', 'Crear Usuario')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Crear Nuevo Usuario</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.usuarios.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                       id="nombre" name="nombre" required>
                @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                       id="email" name="email" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            
            <div class="mb-3">
                <label for="username" class="form-label">Usuario</label>
                <input type="text" class="form-control @error('username') is-invalid @enderror" 
                       id="username" name="username" required>
                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                       id="password" name="password" required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            
            <div class="mb-3">
                <label for="id_rol" class="form-label">Rol</label>
                <select class="form-control @error('id_rol') is-invalid @enderror" id="id_rol" name="id_rol" required>
                    <option value="">Seleccionar Rol</option>
                    @foreach($roles as $rol)
                        <option value="{{ $rol->id_rol }}">{{ $rol->nombre_role }}</option>
                    @endforeach
                </select>
                @error('id_rol') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection