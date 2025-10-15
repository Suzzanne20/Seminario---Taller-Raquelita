@extends('layouts.app')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width:576px){ .page-body { min-height:calc(100vh - 64px); } }

  .btn-theme{ background:#9F3B3B; border-color:#9F3B3B; color:#fff; }
  .btn-theme:hover{ background:#873131; border-color:#873131; color:#fff; }
  
  .pagination .page-link{ color:#1d1d1d; border-color:#e9ecef; }
  .pagination .page-link:hover{ color:#1d1d1d; background:#f8f9fa; border-color:#e9ecef; }
  .pagination .page-item.active .page-link{ background:#535353; border-color:#1d1d1d;  color:#fff; }
  .pagination .page-item.disabled .page-link{ color:#adb5bd; background:#f8f9fa; border-color:#e9ecef; }
  .pagination .page-link:focus{ box-shadow:0 0 0 .15rem rgba(159,59,59,.15); }

  /* Estilos para el switch personalizado */
  .form-check-input:checked {
    background-color: #9F3B3B;
    border-color: #9F3B3B;
  }
  .form-check-input:focus {
    border-color: #9F3B3B;
    box-shadow: 0 0 0 0.25rem rgba(159, 59, 59, 0.25);
  }

  /* Estilo para botones de acción rápida */
  .btn-action {
    transition: all 0.2s ease;
  }
  .btn-action:hover {
    transform: scale(1.1);
  }

/* Estilos para botones de SweetAlert personalizados */
.btn-theme-swal {
    background: #9F3B3B !important;
    border: 1px solid #9F3B3B !important;
    color: #fff !important;
    padding: 0.5rem 1.5rem !important;
    border-radius: 0.375rem !important;
    font-weight: 500 !important;
    font-size: 0.875rem !important;
    margin: 0 0.25rem !important;
}
.btn-theme-swal:hover {
    background: #873131 !important;
    border-color: #873131 !important;
    color: #fff !important;
}
.btn-secondary-swal {
    background: #6c757d !important;
    border: 1px solid #6c757d !important;
    color: #fff !important;
    padding: 0.5rem 1.5rem !important;
    border-radius: 0.375rem !important;
    font-weight: 500 !important;
    font-size: 0.875rem !important;
    margin: 0 0.25rem !important;
}
.btn-secondary-swal:hover {
    background: #5a6268 !important;
    border-color: #545b62 !important;
    color: #fff !important;
}
</style>
@endpush

@section('content')
<div class="container py-4">

  {{-- Título --}}
  <div class="container"><br><br>
    <h1 class="text-center mb-4" style="color:#C24242;">Gestión de Marcas</h1>
  </div>

  {{-- Toolbar: botón + buscador --}}
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex gap-2">
      <a href="{{ route('vehiculos.create') }}"
         class="btn btn-theme"
         style="border-radius:12px; padding:.55rem 1rem;">
        <i class="bi bi-plus-lg me-1"></i> Agregar Vehículo
      </a>
    </div>

    <form action="{{ route('marcas.index') }}" method="GET" class="d-flex align-items-center gap-2">
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Buscar por nombre de marca…"
               value="{{ request('q') }}">
      </div>
      <button class="btn btn-dark" type="submit" style="border-radius:12px;">Buscar</button>
    </form>
  </div>

  {{-- Mensajes --}}
  @if(session('success'))
    <div class="alert alert-success shadow-sm rounded-3">
      <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger shadow-sm rounded-3">
      <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    </div>
  @endif

  @if(session('info'))
    <div class="alert alert-info shadow-sm rounded-3">
      <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
    </div>
  @endif

  {{-- Tabla --}}
  <div class="table-responsive shadow-sm rounded-3">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Nombre de la Marca</th>
          <th class="text-center">Vehículos Asociados</th>
          <th class="text-center">Estado</th>
          <th class="text-center">Mostrar en Registro</th>
          <th class="text-center">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($marcas as $marca)
          <tr>
            <td class="fw-semibold">{{ $marca->id }}</td>
            <td>
              <strong>{{ $marca->nombre }}</strong>
              @if(!$marca->activo)
                <span class="badge bg-warning text-dark ms-2">Deshabilitada</span>
              @endif
            </td>
            <td class="text-center">
              <span class="badge bg-primary">{{ $marca->vehiculos_count }}</span>
            </td>
            <td class="text-center">
              @if($marca->activo)
                <span class="badge bg-success">Activa</span>
              @else
                <span class="badge bg-secondary">Inactiva</span>
              @endif
            </td>
            <td class="text-center">
              <form action="{{ route('marcas.toggle-registro', $marca->id) }}" method="POST" class="d-inline">
                @csrf
                <div class="form-check form-switch d-inline-block" style="transform: scale(1.2);">
                  <input class="form-check-input" type="checkbox" 
                         {{ $marca->mostrar_en_registro ? 'checked' : '' }}
                         onchange="this.form.submit()"
                         {{ $marca->activo ? 'disabled' : '' }}
                         title="{{ $marca->activo ? 'Las marcas activas siempre se muestran en el registro' : ($marca->mostrar_en_registro ? 'Ocultar esta marca al registrar vehículos' : 'Mostrar esta marca al registrar vehículos') }}"
                         id="switch-{{ $marca->id }}">
                </div>
              </form>
              @if($marca->activo)
                <div class="small text-muted mt-1">Siempre visible</div>
              @else
                <div class="small text-muted mt-1">
                  {{ $marca->mostrar_en_registro ? 'Visible en registro' : 'Oculta en registro' }}
                </div>
              @endif
            </td>
            <td class="text-center">
              <div class="d-inline-flex gap-2">
                {{-- Botón Activar/Desactivar --}}
                @if($marca->activo)
                  <form action="{{ route('marcas.desactivar', $marca->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-warning btn-action" title="Deshabilitar marca">
                      <i class="bi bi-pause-circle"></i>
                    </button>
                  </form>
                @else
                  <form action="{{ route('marcas.activar', $marca->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-success btn-action" title="Activar marca">
                      <i class="bi bi-play-circle"></i>
                    </button>
                  </form>
                @endif

                {{-- Botón Eliminar (solo si no tiene vehículos) --}}
                @if($marca->vehiculos_count == 0)
                  <button type="button" 
                          class="btn btn-sm btn-outline-danger btn-action" 
                          title="Eliminar permanentemente"
                          onclick="confirmarEliminacion({{ $marca->id }}, '{{ $marca->nombre }}')">
                    <i class="bi bi-trash"></i>
                  </button>
                @else
                  <button class="btn btn-sm btn-outline-secondary" disabled title="No se puede eliminar, tiene vehículos asociados">
                    <i class="bi bi-trash"></i>
                  </button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center py-4">
              <i class="bi bi-tags display-6 text-muted mb-3 d-block"></i>
              No hay marcas registradas
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Paginación --}}
  <div class="mt-3">
    {{ $marcas->links() }}
  </div>

  {{-- Información centrada --}}
  <div class="row justify-content-center mt-4">
    <div class="col-md-10">
      <div class="card text-center">
        <div class="card-header bg-light">
          <i class="bi bi-info-circle me-2"></i>Información sobre Estados de Marcas
        </div>
        <div class="card-body">
          <div class="row text-start">
            <div class="col-md-4">
              <h6 class="text-primary"><i class="bi bi-circle-fill text-success me-2"></i>Marcas Activas</h6>
              <p class="small text-muted mb-3">Siempre visibles en el registro y listado de vehículos.</p>
            </div>
            <div class="col-md-4">
              <h6 class="text-primary"><i class="bi bi-circle-fill text-warning me-2"></i>Marcas Deshabilitadas</h6>
              <p class="small text-muted mb-3">No aparecen en el listado principal de vehículos.</p>
            </div>
            <div class="col-md-4">
              <h6 class="text-primary"><i class="bi bi-toggle-on text-danger me-2"></i>Mostrar en Registro</h6>
              <p class="small text-muted mb-0">Permite que marcas deshabilitadas aparezcan al registrar nuevos vehículos (con advertencia).</p>
            </div>
          </div>
          <hr>
          <p class="mb-0 text-muted"><small><strong>Total de marcas:</strong> {{ $marcas->total() }} | 
          <strong>Activas:</strong> {{ $marcas->where('activo', true)->count() }} | 
          <strong>Deshabilitadas:</strong> {{ $marcas->where('activo', false)->count() }}</small></p>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Formulario oculto para eliminación --}}
<form id="formEliminarMarca" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
</form>

<script>
// Función para confirmar eliminación con SweetAlert2
function confirmarEliminacion(marcaId, marcaNombre) {
  Swal.fire({
    title: '¿Eliminar Marca?',
    html: `¿Estás seguro de que deseas eliminar permanentemente la marca <strong>"${marcaNombre}"</strong>?<br><br>
          <span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Esta acción no se puede deshacer.</span>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#9F3B3B',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    customClass: {
      popup: 'rounded-3',
      confirmButton: 'btn-theme-swal',
      cancelButton: 'btn-secondary-swal'
    },
    buttonsStyling: false
  }).then((result) => {
    if (result.isConfirmed) {
      // Configurar y enviar el formulario
      const form = document.getElementById('formEliminarMarca');
      form.action = `/marcas/${marcaId}`;
      form.submit();
    }
  });
}

// Quitar la confirmación del toggle del switch
document.addEventListener('DOMContentLoaded', function() {
    const switches = document.querySelectorAll('.form-check-input');
    switches.forEach(switchElement => {
        // Remover cualquier evento de confirmación previo
        switchElement.addEventListener('change', function(e) {
            // El formulario se envía automáticamente sin confirmación
        });
    });
});
</script>
@endsection