@php
    $userRole = auth()->user()->rol->nombre_role;
@endphp

<div class="bg-dark text-white vh-100" style="width: 280px;">
    <div class="p-3">
        <h5 class="text-center mb-4">Menú Principal</h5>
        <ul class="nav nav-pills flex-column">
            @if($userRole === 'Administrador')
                <li class="nav-item mb-2">
                    <a class="nav-link text-white" href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link text-white" href="{{ route('admin.usuarios.index') }}">
                        <i class="fas fa-users me-2"></i> Usuarios
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link text-white" href="#">
                        <i class="fas fa-robot me-2"></i> IA
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link text-white" href="#">
                        <i class="fas fa-chart-line me-2"></i> Estadísticas
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link text-white" href="{{ route('admin.cuentas') }}">
                        <i class="fab fa-instagram me-2"></i> Cuentas IG
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link text-white" href="#">
                        <i class="fas fa-cog me-2"></i> Ajustes
                    </a>
                </li>
            @endif
            
            <li class="nav-item mb-2 mt-3">
                <a class="nav-link text-white bg-primary" href="{{ route('multichat.index') }}">
                    <i class="fas fa-comments me-2"></i> Multichat
                </a>
            </li>
        </ul>
    </div>
</div>