@extends('layouts.chat')

@section('title', 'Multichat')

@section('content')
<div class="container-fluid p-0 chat-container">
    <div class="row g-0 h-100">
        <!-- Sidebar de conversaciones -->
        <div class="col-md-4 border-end">
            <div class="p-3 bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-comments me-2"></i> Conversaciones
                </h5>
            </div>
            
            <div class="p-2 border-bottom">
                <select id="cuentaSelector" class="form-select form-select-sm">
                    <option value="">Todas las cuentas</option>
                    @foreach($cuentas as $cuenta)
                        <option value="{{ $cuenta->id_cuenta_ig }}">{{ $cuenta->username_ig }}</option>
                    @endforeach
                </select>
            </div>
            
            <div id="listaConversaciones" class="conversaciones-list">
                <div class="text-center text-muted p-3">Selecciona una cuenta</div>
            </div>
        </div>
        
        <!-- Área de chat -->
        <div class="col-md-8 d-flex flex-column">
            <div class="p-3 bg-primary text-white">
                <h5 class="mb-0" id="chatTitulo">
                    <i class="fas fa-user-circle me-2"></i> Selecciona una conversación
                </h5>
            </div>
            
            <div id="chatMensajes" class="mensajes-area p-3">
                <div class="text-center text-muted p-5">
                    <i class="fas fa-comment-dots fa-3x mb-3"></i>
                    <p>Selecciona una conversación para comenzar</p>
                </div>
            </div>
            
            <div class="p-3 border-top">
                <div class="input-group">
                    <input type="text" id="mensajeInput" class="form-control" placeholder="Escribe un mensaje...">
                    <button id="enviarBtn" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                <div class="mt-2">
                    <label for="imagenInput" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-image"></i> Adjuntar imagen
                    </label>
                    <input type="file" id="imagenInput" accept="image/*" style="display: none;">
                    <small class="text-muted ms-2">Máximo 5MB (JPG, PNG, GIF)</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para preview de imagen -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vista previa de la imagen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" alt="Preview" class="img-fluid">
                <div class="mt-3 text-muted">
                    <small>Esta imagen será enviada a: <strong id="previewUsuario"></strong></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarEnvio">
                    <i class="fas fa-paper-plane"></i> Enviar imagen
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let conversacionActual = null;
let pollingInterval = null;
let imagenPendiente = null;

$(document).ready(function() {
    
    $('#cuentaSelector').change(function() {
        cargarConversaciones();
    });
    
    function cargarConversaciones() {
        let cuentaId = $('#cuentaSelector').val();
        let url = '/multichat/conversaciones' + (cuentaId ? '?cuenta_id=' + cuentaId : '');
        
        $.get(url, function(data) {
            let html = '';
            if (data.length === 0) {
                html = '<div class="text-center text-muted p-3">No hay conversaciones</div>';
            } else {
                data.forEach(conv => {
                    let username = conv.cliente_instagram?.username_cliente || 'Usuario';
                    let activa = (conversacionActual === conv.id_conversacion) ? 'activa' : '';
                    let ultimo = conv.ultimo_mensaje || 'Sin mensajes';
                    if (ultimo.length > 30) ultimo = ultimo.substring(0, 30) + '...';
                    
                    html += `
                        <div class="p-3 border-bottom hover-conversacion ${activa}" onclick="cargarMensajes(${conv.id_conversacion})">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <strong>${escapeHtml(username)}</strong><br>
                                    <small class="text-muted">${escapeHtml(ultimo)}</small>
                                </div>
                                <small class="text-muted">${new Date(conv.updated_at).toLocaleTimeString()}</small>
                            </div>
                        </div>
                    `;
                });
            }
            $('#listaConversaciones').html(html);
        });
    }
    
    window.cargarMensajes = function(conversacionId) {
        conversacionActual = conversacionId;
        
        $('.hover-conversacion').removeClass('activa');
        $(`[onclick="cargarMensajes(${conversacionId})"]`).addClass('activa');
        
        $.get('/multichat/mensajes/' + conversacionId, function(data) {
            let html = '';
            if (data.length === 0) {
                html = '<div class="text-center text-muted">No hay mensajes aún</div>';
            } else {
                data.forEach(msg => {
                    let esCliente = msg.remitente_tipo === 'CLIENTE';
                    let clase = esCliente ? 'mensaje-cliente' : 'mensaje-cuenta';
                    let alineacion = esCliente ? 'text-start' : 'text-end';
                    
                    let contenido = '';
                    if (msg.tipo_contenido === 'image') {
                        contenido = `<a href="${msg.media_url}" target="_blank" class="${esCliente ? 'text-primary' : 'text-white'}">
                                        <i class="fas fa-image me-1"></i> Ver imagen
                                     </a>`;
                    } else {
                        contenido = escapeHtml(msg.texto_mensaje || '[Archivo]');
                    }
                    
                    html += `
                        <div class="mb-2 ${alineacion}">
                            <div class="d-inline-block p-2 rounded ${clase}" style="max-width: 70%;">
                                ${contenido}<br>
                                <small class="${esCliente ? 'text-muted' : 'text-white-50'}" style="font-size: 10px;">
                                    ${new Date(msg.fecha_envio).toLocaleTimeString()}
                                </small>
                            </div>
                        </div>
                    `;
                });
            }
            $('#chatMensajes').html(html);
            $('#chatMensajes').scrollTop($('#chatMensajes')[0].scrollHeight);
        });
        
        if (pollingInterval) clearInterval(pollingInterval);
        pollingInterval = setInterval(function() {
            if (conversacionActual) {
                $.get('/multichat/mensajes/' + conversacionActual, function(data) {
                    let currentCount = $('#chatMensajes .mb-2').length;
                    if (data.length > currentCount) {
                        let html = '';
                        data.forEach(msg => {
                            let esCliente = msg.remitente_tipo === 'CLIENTE';
                            let clase = esCliente ? 'mensaje-cliente' : 'mensaje-cuenta';
                            let alineacion = esCliente ? 'text-start' : 'text-end';
                            
                            let contenido = '';
                            if (msg.tipo_contenido === 'image') {
                                contenido = `<a href="${msg.media_url}" target="_blank" class="${esCliente ? 'text-primary' : 'text-white'}">
                                                <i class="fas fa-image me-1"></i> Ver imagen
                                            </a>`;
                            } else {
                                contenido = escapeHtml(msg.texto_mensaje || '[Archivo]');
                            }
                            
                            html += `
                                <div class="mb-2 ${alineacion}">
                                    <div class="d-inline-block p-2 rounded ${clase}" style="max-width: 70%;">
                                        ${contenido}<br>
                                        <small class="${esCliente ? 'text-muted' : 'text-white-50'}" style="font-size: 10px;">
                                            ${new Date(msg.fecha_envio).toLocaleTimeString()}
                                        </small>
                                    </div>
                                </div>
                            `;
                        });
                        $('#chatMensajes').html(html);
                        $('#chatMensajes').scrollTop($('#chatMensajes')[0].scrollHeight);
                    }
                });
            }
        }, 3000);
    };
    
    function enviarMensaje() {
        if (!conversacionActual) {
            alert('Selecciona una conversación primero');
            return;
        }
        
        let mensaje = $('#mensajeInput').val().trim();
        if (!mensaje) return;
        
        $.ajax({
            url: '/multichat/enviar',
            type: 'POST',
            data: {
                conversacion_id: conversacionActual,
                mensaje: mensaje,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#mensajeInput').val('');
                    cargarMensajes(conversacionActual);
                    cargarConversaciones();
                }
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.error || 'No se pudo enviar'));
            }
        });
    }
    
    function enviarImagen(file) {
        if (!conversacionActual) {
            alert('Selecciona una conversación primero');
            return;
        }
        
        let formData = new FormData();
        formData.append('conversacion_id', conversacionActual);
        formData.append('imagen', file);
        formData.append('_token', '{{ csrf_token() }}');
        
        $.ajax({
            url: '/multichat/enviar-imagen',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    cargarMensajes(conversacionActual);
                    cargarConversaciones();
                }
            },
            error: function(xhr) {
                alert('Error al enviar imagen: ' + (xhr.responseJSON?.error || 'Error desconocido'));
            }
        });
    }
    
    // Preview de imagen antes de enviar
    $('#imagenInput').change(function(e) {
        if (e.target.files.length > 0 && conversacionActual) {
            const file = e.target.files[0];
            const reader = new FileReader();
            
            reader.onload = function(event) {
                $('#previewImage').attr('src', event.target.result);
                
                // Obtener el username del cliente para mostrar en preview
                const clienteDiv = $('#listaConversaciones .activa strong').text();
                $('#previewUsuario').text(clienteDiv);
                
                imagenPendiente = file;
                $('#previewModal').modal('show');
            };
            
            reader.readAsDataURL(file);
            $(this).val(''); // Limpiar input para que permita seleccionar la misma imagen nuevamente
        } else if (!conversacionActual) {
            alert('Primero selecciona una conversación');
            $(this).val('');
        }
    });
    
    // Confirmar envío de imagen
    $('#confirmarEnvio').click(function() {
        if (imagenPendiente) {
            enviarImagen(imagenPendiente);
            $('#previewModal').modal('hide');
            imagenPendiente = null;
            $('#previewImage').attr('src', '');
        }
    });
    
    $('#enviarBtn').click(enviarMensaje);
    
    $('#mensajeInput').keypress(function(e) {
        if (e.which === 13) enviarMensaje();
    });
    
    $('label[for="imagenInput"]').click(function() {
        if (!conversacionActual) {
            alert('Primero selecciona una conversación');
        }
    });
    
    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
});
</script>
@endpush
@endsection