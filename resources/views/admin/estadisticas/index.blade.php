@extends('layouts.app')

@section('title', 'Estadísticas Instagram')

@section('content')
<style>
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .post-card {
        cursor: pointer;
        transition: all 0.2s;
    }
    .post-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .rank-item {
        display: flex;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #eee;
    }
    .rank-number {
        width: 30px;
        font-weight: bold;
        color: #667eea;
    }
</style>

<div class="row">
    <div class="col-12">
        <h2>Estadísticas de Instagram</h2>
        <p>Análisis detallado de rendimiento por cuenta</p>
    </div>
</div>

<!-- Selector de cuenta -->
<div class="row mt-3">
    <div class="col-md-4">
        <label class="form-label">Seleccionar cuenta</label>
        <select id="cuentaSelect" class="form-select">
            <option value="">Selecciona una cuenta...</option>
            @foreach($cuentas as $cuenta)
                <option value="{{ $cuenta->id_cuenta_ig }}">{{ $cuenta->username_ig }}</option>
            @endforeach
        </select>
        
    </div>
</div>

<!-- Loading -->
<div id="loading" class="text-center mt-5 d-none">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
    <p class="mt-2">Obteniendo datos de Instagram...</p>
</div>

<!-- Contenido de estadísticas -->
<div id="statsContent" class="mt-4" style="display: none;"></div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#cuentaSelect').change(function() {
        const cuentaId = $(this).val();
        if (cuentaId) {
            cargarEstadisticas(cuentaId);
        } else {
            $('#statsContent').hide().empty();
        }
    });
    
    function cargarEstadisticas(cuentaId) {
        $('#loading').removeClass('d-none');
        $('#statsContent').hide().empty();
        
        $.get('/admin/estadisticas/cuenta/' + cuentaId, function(response) {
            if (response.success) {
                renderEstadisticas(response);
                $('#statsContent').show();
            } else {
                mostrarError(response.error);
            }
        }).fail(function(xhr) {
            mostrarError('Error al cargar las estadísticas');
        }).always(function() {
            $('#loading').addClass('d-none');
        });
    }
    
    function renderEstadisticas(data) {
        const profile = data.profile;
        const posts = data.posts;
        const rankings = data.rankings;
        
        let html = `
            <!-- Perfil -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fab fa-instagram me-2"></i> ${profile.username}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <img src="${profile.profile_picture}" class="rounded-circle mb-3" width="120" height="120">
                            <h5>${profile.name}</h5>
                            <p class="text-muted">${profile.biography || 'Sin biografía'}</p>
                        </div>
                        <div class="col-md-9">
                            <div class="row text-center">
                                <div class="col-4">
                                    <h3>${formatNumber(profile.followers)}</h3>
                                    <small>Seguidores</small>
                                </div>
                                <div class="col-4">
                                    <h3>${formatNumber(profile.following)}</h3>
                                    <small>Siguiendo</small>
                                </div>
                                <div class="col-4">
                                    <h3>${formatNumber(profile.total_posts)}</h3>
                                    <small>Publicaciones</small>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row">
                                <div class="col-6">
                                    <strong>Alcance 30 días:</strong>
                                    <p class="text-success">${formatNumber(profile.insights.reach || 0)} cuentas únicas</p>
                                </div>
                                <div class="col-6">
                                    <strong>Impresiones 30 días:</strong>
                                    <p class="text-primary">${formatNumber(profile.insights.impressions || 0)} vistas totales</p>
                                </div>
                                <div class="col-6">
                                    <strong>Visitas al perfil:</strong>
                                    <p>${formatNumber(profile.insights.profile_views || 0)}</p>
                                </div>
                                <div class="col-6">
                                    <strong>Clics en enlace:</strong>
                                    <p>${formatNumber(profile.insights.website_clicks || 0)}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Rankings -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card stat-card">
                        <div class="card-header bg-success text-white">
                            <h5><i class="fas fa-heart me-2"></i> Top 5 más gustados</h5>
                        </div>
                        <div class="card-body p-0">
                            ${renderRanking(rankings.top_likes, 'like_count', '❤️')}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card">
                        <div class="card-header bg-info text-white">
                            <h5><i class="fas fa-comment me-2"></i> Top 5 más comentados</h5>
                        </div>
                        <div class="card-body p-0">
                            ${renderRanking(rankings.top_comments, 'comments_count', '💬')}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card">
                        <div class="card-header bg-warning text-white">
                            <h5><i class="fas fa-eye me-2"></i> Top 5 mayor alcance</h5>
                        </div>
                        <div class="card-body p-0">
                            ${renderRanking(rankings.top_impressions, 'impressions', '👁️')}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Últimas publicaciones -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-images me-2"></i> Últimas publicaciones</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        ${posts.map(post => renderPost(post)).join('')}
                    </div>
                </div>
            </div>
        `;
        
        $('#statsContent').html(html);
    }
    
    function renderRanking(posts, metric, icon) {
        if (!posts || posts.length === 0) {
            return '<div class="p-3 text-center text-muted">Sin datos</div>';
        }
        
        return posts.map((post, index) => `
            <div class="rank-item" onclick="window.open('${post.permalink}', '_blank')">
                <div class="rank-number">#${index + 1}</div>
                <div class="flex-grow-1">
                    <small class="text-muted">${post.media_type}</small>
                    <div class="fw-bold">${truncate(post.caption || 'Sin texto', 50)}</div>
                    <small>${icon} ${formatNumber(post[metric])}</small>
                </div>
                <div class="ms-2">
                    ${post.thumbnail_url ? `<img src="${post.thumbnail_url}" width="50" height="50" style="object-fit:cover; border-radius:8px;">` : ''}
                </div>
            </div>
        `).join('');
    }
    
    function renderPost(post) {
        const mediaContent = post.media_type === 'VIDEO' 
            ? `<video src="${post.media_url}" class="img-fluid rounded" style="max-height: 200px; width:100%; object-fit:cover;" controls></video>`
            : `<img src="${post.media_url}" class="img-fluid rounded" style="max-height: 200px; width:100%; object-fit:cover;">`;
        
        return `
            <div class="col-md-4 mb-3">
                <div class="card post-card" onclick="window.open('${post.permalink}', '_blank')">
                    <div class="position-relative">
                        ${mediaContent}
                        <span class="position-absolute top-0 end-0 m-2 badge bg-dark">${post.media_type}</span>
                    </div>
                    <div class="card-body p-2">
                        <div class="row text-center small">
                            <div class="col-4">
                                <i class="fas fa-heart text-danger"></i> ${formatNumber(post.like_count)}
                            </div>
                            <div class="col-4">
                                <i class="fas fa-comment text-primary"></i> ${formatNumber(post.comments_count)}
                            </div>
                            <div class="col-4">
                                <i class="fas fa-eye text-info"></i> ${formatNumber(post.impressions)}
                            </div>
                        </div>
                        <small class="text-muted">${new Date(post.timestamp).toLocaleDateString('es')}</small>
                    </div>
                </div>
            </div>
        `;
    }
    
    function formatNumber(num) {
        if (!num) return '0';
        if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
        if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
        return num.toString();
    }
    
    function truncate(text, maxLength) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }
    
    function mostrarError(mensaje) {
        $('#statsContent').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i> ${mensaje}
            </div>
        `).show();
    }
});
</script>
@endpush
@endsection