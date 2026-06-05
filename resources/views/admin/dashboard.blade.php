@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <h2>Dashboard</h2>
        <p>Bienvenido, {{ auth()->user()->nombre }}</p>
    </div>
</div>

<!-- Tarjetas de estadísticas -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Usuarios</h6>
                        <h2 class="mb-0">{{ $totalUsuarios }}</h2>
                        <small>Activos: {{ $usuariosActivos }}</small>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Cuentas IG</h6>
                        <h2 class="mb-0">{{ $totalCuentas }}</h2>
                        <small>Activas: {{ $cuentasActivas }}</small>
                    </div>
                    <i class="fab fa-instagram fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Conversaciones</h6>
                        <h2 class="mb-0">{{ $totalConversaciones }}</h2>
                        <small>Hoy: {{ $conversacionesHoy }}</small>
                    </div>
                    <i class="fas fa-comments fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Mensajes</h6>
                        <h2 class="mb-0">{{ $totalMensajes }}</h2>
                        <small>Hoy: {{ $mensajesHoy }}</small>
                    </div>
                    <i class="fas fa-envelope fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Últimas conversaciones -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-clock me-2"></i> Últimas conversaciones</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Cliente</th>
                                <th>Cuenta</th>
                                <th>Último mensaje</th>
                                <th>Actualizado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimasConversaciones as $conv)
                            <tr>
                                <td>{{ $conv->clienteInstagram->username_cliente ?? 'Desconocido' }}</td>
                                <td>{{ $conv->cuentaInstagram->username_ig ?? 'N/A' }}</td>
                                <td>{{ Str::limit($conv->ultimo_mensaje ?? 'Sin mensajes', 30) }}</td>
                                <td>{{ $conv->updated_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No hay conversaciones aún</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mensajes recientes -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-history me-2"></i> Mensajes recientes</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>De</th>
                                <th>Mensaje</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mensajesRecientes as $msg)
                            <tr>
                                <td>
                                    @if($msg->remitente_tipo === 'CLIENTE')
                                        <span class="badge bg-secondary">Cliente</span>
                                    @else
                                        <span class="badge bg-primary">Asesor</span>
                                    @endif
                                </td>
                                <td>{{ Str::limit($msg->texto_mensaje ?? '[Imagen]', 40) }}</td>
                                <td>{{ $msg->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No hay mensajes aún</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico de actividad semanal (opcional) -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-line me-2"></i> Actividad de mensajes (últimos 7 días)</h5>
            </div>
            <div class="card-body">
                <canvas id="mensajesChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Obtener datos de actividad para el gráfico
    $.get('/admin/estadisticas/actividad-semanal', function(data) {
        const ctx = document.getElementById('mensajesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.dias,
                datasets: [{
                    label: 'Mensajes',
                    data: data.mensajes,
                    borderColor: 'rgb(102, 126, 234)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.3,
                    fill: true
                }, {
                    label: 'Conversaciones',
                    data: data.conversaciones,
                    borderColor: 'rgb(118, 75, 162)',
                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    });
});
</script>
@endpush
@endsection