@extends('layouts.app')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:rgba(255, 255, 255, 0.144) !important; color:#212529; }
  @media (max-width:576px){ .page-body { min-height:calc(100vh - 64px); } }

  .btn-theme{ background:#9F3B3B; border-color:#9F3B3B; color:#fff; }
  .btn-theme:hover{ background:#873131; border-color:#873131; color:#fff; }

  .pagination .page-link{ color:#1d1d1d; border-color:#e9ecef; }
  .pagination .page-link:hover{ color:#1d1d1d; background:#f8f9fa; border-color:#e9ecef; }
  .pagination .page-item.active .page-link{ background:#535353; border-color:#1d1d1d; color:#fff; }
  .pagination .page-item.disabled .page-link{ color:#adb5bd; background:#f8f9fa; border-color:#e9ecef; }
  .pagination .page-link:focus{ box-shadow:0 0 0 .15rem rgba(159,59,59,.15); }

  /* Estilos para la toolbar compacta */
  .toolbar-compact {
    background: #fff;
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 1rem;
  }
  
  .toolbar-content {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
  }
  
  .toolbar-group {
    display: flex;
    gap: 0.5rem;
    align-items: center;
  }
  
  .toolbar-divider {
    width: 1px;
    height: 24px;
    background: #dee2e6;
    margin: 0 0.25rem;
  }
  
  .search-group {
    position: relative;
    flex: 1;
    min-width: 200px;
    max-width: 300px;
  }
  
  @media (max-width: 768px) {
    .toolbar-content {
      flex-direction: column;
      align-items: stretch;
    }
    
    .toolbar-group {
      justify-content: space-between;
    }
    
    .search-group {
      max-width: none;
    }
  }

  /* Estilos para el indicador de carga */
  .loading-indicator {
    display: none;
    text-align: center;
    padding: 1rem;
  }

  /* Estilos para tooltips personalizados */
  .tt-placa {
    --bs-tooltip-bg: #333;
    --bs-tooltip-color: #fff;
  }
</style>
@endpush

@section('content')
<div class="container py-4">

  <div class="container">
    <h1 class="text-center mb-4" style="color:#C24242;">Órdenes de Trabajo</h1>
  </div>

  {{-- Toolbar Compacta --}}
  <div class="toolbar-compact">
    <div class="toolbar-content">
      {{-- Botón Nueva Orden --}}
      <a href="{{ route('ordenes.create') }}" class="btn btn-theme" style="border-radius:12px; padding:.55rem 1rem;">
        <i class="bi bi-plus-lg me-1"></i> Nueva Orden
      </a>
      
      <div class="toolbar-divider"></div>

      {{-- Filtro Tipo de Servicio --}}
      <div class="toolbar-group">
        <label class="form-label mb-0 fw-semibold me-2">Servicio:</label>
        <select name="servicio" class="form-select" id="servicioFilter" style="min-width: 180px;">
          <option value="">Todos</option>
          {{-- Aquí deberías cargar los tipos de servicio disponibles --}}
          @foreach($servicios as $servicio)
            <option value="{{ $servicio->id }}" {{ request('servicio') == $servicio->id ? 'selected' : '' }}>
              {{ $servicio->descripcion ?? $servicio->nombre }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- Filtro Estado --}}
      <div class="toolbar-group">
        <label class="form-label mb-0 fw-semibold me-2">Estado:</label>
        <select name="estado" class="form-select" id="estadoFilter" style="min-width: 180px;">
          <option value="">Todos</option>
          @foreach($estados as $e)
            <option value="{{ $e->id }}" {{ (string)$e->id === request('estado') ? 'selected' : '' }}>
              {{ $e->nombre }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="toolbar-divider"></div>

      {{-- Búsqueda por Placa --}}
      <div class="search-group">
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input type="text" id="searchInput" class="form-control" placeholder="Buscar por placa…"
                 value="{{ request('q') }}">
          <button id="clearSearchBtn" class="btn btn-outline-secondary" type="button">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
      </div>

      {{-- Botón Limpiar --}}
      <a href="{{ route('ordenes.index') }}" class="btn btn-outline-secondary" style="border-radius:12px;">
        <i class="bi bi-arrow-clockwise me-1"></i> Limpiar
      </a>
    </div>
  </div>

  {{-- Indicador de carga --}}
  <div id="loadingIndicator" class="loading-indicator">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Cargando...</span>
    </div>
    <span class="ms-2">Buscando órdenes...</span>
  </div>

  {{-- Tabla de órdenes --}}
  <div id="ordenesTable" class="table-responsive shadow-sm rounded-3">
    @include('ordenes.partials.table', ['ordenes' => $ordenes])
  </div>

  {{-- Paginación --}}
  <div id="paginationContainer" class="mt-3">
    {{ $ordenes->appends(request()->query())->links() }}
  </div>
</div>

{{-- Icons --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

{{-- JS para búsqueda en tiempo real --}}
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const servicioFilter = document.getElementById('servicioFilter');
    const estadoFilter = document.getElementById('estadoFilter');
    const ordenesTable = document.getElementById('ordenesTable');
    const paginationContainer = document.getElementById('paginationContainer');
    const loadingIndicator = document.getElementById('loadingIndicator');
    
    // Variables para controlar la búsqueda
    let searchTimeout;
    const searchDelay = 500; // Milisegundos de espera después de escribir
    
    // Función para realizar la búsqueda
    function performSearch() {
      const searchTerm = searchInput.value.trim();
      const servicioValue = servicioFilter.value;
      const estadoValue = estadoFilter.value;
      
      // Mostrar indicador de carga
      loadingIndicator.style.display = 'block';
      
      // Construir URL con parámetros
      const url = new URL('{{ route('ordenes.index') }}');
      const params = new URLSearchParams();
      
      if (searchTerm) params.append('q', searchTerm);
      if (servicioValue) params.append('servicio', servicioValue);
      if (estadoValue) params.append('estado', estadoValue);
      
      // Realizar petición AJAX
      fetch(`${url}?${params.toString()}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.text())
      .then(html => {
        // Crear un elemento temporal para parsear el HTML
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        
        // Extraer la tabla y la paginación
        const newTable = tempDiv.querySelector('#ordenesTable');
        const newPagination = tempDiv.querySelector('#paginationContainer');
        
        // Actualizar el contenido
        if (newTable) ordenesTable.innerHTML = newTable.innerHTML;
        if (newPagination) paginationContainer.innerHTML = newPagination.innerHTML;
        
        // Reasignar eventos a los nuevos elementos de la tabla
        reassignTableEvents();
        
        // Ocultar indicador de carga
        loadingIndicator.style.display = 'none';
      })
      .catch(error => {
        console.error('Error en la búsqueda:', error);
        loadingIndicator.style.display = 'none';
      });
    }
    
    // Función para reasignar eventos a los elementos de la tabla después de una actualización
    function reassignTableEvents() {
      // Reasignar eventos de eliminación
      const deleteForms = document.querySelectorAll('form.js-del');
      deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
          e.preventDefault();
          
          const title = this.dataset.title || '¿Eliminar?';
          const text = this.dataset.text || 'Esta acción no se puede deshacer.';

          Swal.fire({
            title: title,
            html: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            focusCancel: true,
            customClass: {
              confirmButton: 'btn btn-danger',
              cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
          }).then((result) => {
            if (result.isConfirmed) {
              this.submit();
            }
          });
        });
      });

      // Inicializar tooltips
      const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltips.forEach(el => new bootstrap.Tooltip(el));
    }
    
    // Evento de entrada en el campo de búsqueda
    searchInput.addEventListener('input', function() {
      // Limpiar timeout anterior
      clearTimeout(searchTimeout);
      
      // Establecer nuevo timeout
      searchTimeout = setTimeout(performSearch, searchDelay);
    });
    
    // Evento para limpiar búsqueda
    clearSearchBtn.addEventListener('click', function() {
      searchInput.value = '';
      performSearch();
    });
    
    // Eventos para filtros
    servicioFilter.addEventListener('change', performSearch);
    estadoFilter.addEventListener('change', performSearch);
    
    // Reasignar eventos iniciales
    reassignTableEvents();
  });
</script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Contenedor para inyectar el modal --}}
<div id="qe_container" data-edit-url-template="{{ route('ordenes.edit', '__ID__') }}"></div>

@push('scripts')
<script>
(function() {
  // Abrir modal al hacer clic en el lápiz
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.js-edit-ot');
    if (!btn) return;

    const cont = document.getElementById('qe_container');
    const tpl  = cont.dataset.editUrlTemplate;
    const url  = tpl.replace('__ID__', btn.dataset.id);

    const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
    if (!resp.ok) { alert('No se pudo cargar el editor.'); return; }

    cont.innerHTML = await resp.text();
    const modalEl  = document.getElementById('otQuickModal');
    const modal    = bootstrap.Modal.getOrCreateInstance(modalEl);

    if (window.initQuickEditOT) window.initQuickEditOT(modalEl);

    modalEl.addEventListener('hidden.bs.modal', () => { cont.innerHTML = ''; }, { once:true });
    modal.show();
  });
})();
</script>
@endpush

@endsection