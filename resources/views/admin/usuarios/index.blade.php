@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Usuarios</h2>
    <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Usuario
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->id_usuario }}</td>
                    <td>{{ $usuario->nombre }}</td>
                    <td>{{ $usuario->email }}</td>
                    <td>{{ $usuario->username }}</td>
                    <td>{{ $usuario->rol->nombre_role }}</td>
                    <td>
                        <button class="btn btn-sm {{ $usuario->activo ? 'btn-success' : 'btn-secondary' }} toggle-activo" 
                                data-id="{{ $usuario->id_usuario }}">
                            {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                        </button>
                    </td>
                    <td>
                        <a href="{{ route('admin.usuarios.edit', $usuario->id_usuario) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.usuarios.destroy', $usuario->id_usuario) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este usuario?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.toggle-activo').click(function() {
        var id = $(this).data('id');
        var btn = $(this);
        $.ajax({
            url: '/admin/usuarios/' + id + '/toggle',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.activo) {
                    btn.removeClass('btn-secondary').addClass('btn-success').text('Activo');
                } else {
                    btn.removeClass('btn-success').addClass('btn-secondary').text('Inactivo');
                }
            },
            error: function(xhr) {
                alert('Error al cambiar el estado');
            }
        });
    });
});
</script>
@endpush
@endsection