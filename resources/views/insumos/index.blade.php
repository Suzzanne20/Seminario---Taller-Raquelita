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
  .pagination .page-item.active .page-link{ background:#535353; border-color:#1d1d1d;  color:#fff; }
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
  
  .search-clear {
    position: absolute;
    right: 40px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    z-index: 3;
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
</style>
@endpush

@section('content')
<div class="container py-4">
  {{-- Título --}}
  <div class="container"><br><br>
    <h1 class="text-center mb-4" style="color:#C24242;">Gestión de Insumos</h1>
  </div>

  {{-- Toolbar Compacta --}}
  <div class="toolbar-compact">
    <div class="toolbar-content">
      {{-- Botón Nuevo Registro --}}
      <a href="{{ route('insumos.create') }}" class="btn btn-theme" style="border-radius:12px; padding:.55rem 1rem;">
        <i class="bi bi-plus-lg me-1"></i> Registrar Insumo
      </a>
      
      <div class="toolbar-divider"></div>

      {{-- Filtro Tipo de Insumo --}}
      <div class="toolbar-group">
        <label class="form-label mb-0 fw-semibold me-2">Tipo:</label>
        <select name="tipo_insumo" class="form-select" id="tipoFilter" style="min-width: 180px;">
          <option value="">Todos</option>
          @foreach($tiposInsumo as $tipo)
            <option value="{{ $tipo->id }}" {{ request('tipo_insumo') == $tipo->id ? 'selected' : '' }}>
              {{ $tipo->nombre }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- Filtro Stock --}}
      <div class="toolbar-group">
        <label class="form-label mb-0 fw-semibold me-2">Stock:</label>
        <select name="stock" class="form-select" id="stockFilter" style="min-width: 150px;">
          <option value="">Todos</option>
          <option value="bajo" {{ request('stock') == 'bajo' ? 'selected' : '' }}>Stock bajo</option>
          <option value="normal" {{ request('stock') == 'normal' ? 'selected' : '' }}>Stock normal</option>
          <option value="sin_stock" {{ request('stock') == 'sin_stock' ? 'selected' : '' }}>Sin stock</option>
        </select>
      </div>

      <div class="toolbar-divider"></div>

      {{-- Búsqueda --}}
      <div class="search-group">
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre…"
                 value="{{ request('q') }}">
          <button id="clearSearchBtn" class="btn btn-outline-secondary" type="button">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
      </div>

      {{-- Botón Limpiar --}}
      <a href="{{ route('insumos.index') }}" class="btn btn-outline-secondary" style="border-radius:12px;">
        <i class="bi bi-arrow-clockwise me-1"></i> Limpiar
      </a>
    </div>
  </div>

  {{-- Mensajes --}}
  @if(session('success'))
    <div class="alert alert-success shadow-sm rounded-3">{{ session('success') }}</div>
  @endif

  {{-- Indicador de carga --}}
  <div id="loadingIndicator" class="loading-indicator">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Cargando...</span>
    </div>
    <span class="ms-2">Buscando insumos...</span>
  </div>

  {{-- Eliminación múltiple --}}
  <form id="bulkDeleteForm" action="{{ route('insumos.destroyMultiple') }}" method="POST">
    @csrf
    @method('DELETE')

    <div id="insumosTable" class="table-responsive shadow-sm rounded-3">
      @include('insumos.partials.table', ['insumos' => $insumos])
    </div>

    {{-- Botón eliminar seleccionados --}}
    <div class="d-flex justify-content-end mt-3">
      <button id="deleteMultipleBtn" type="button" class="btn btn-danger d-none">
        <i class="bi bi-trash me-1"></i> Eliminar seleccionados
      </button>
    </div>

  </form>

  {{-- Paginación --}}
  <div id="paginationContainer" class="mt-3">
    {{ $insumos->appends(request()->query())->links() }}
  </div>

{{-- Icons --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

{{-- JS para búsqueda en tiempo real y selección múltiple --}}
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const tipoFilter = document.getElementById('tipoFilter');
    const stockFilter = document.getElementById('stockFilter');
    const insumosTable = document.getElementById('insumosTable');
    const paginationContainer = document.getElementById('paginationContainer');
    const loadingIndicator = document.getElementById('loadingIndicator');
    
    // Variables para controlar la búsqueda
    let searchTimeout;
    const searchDelay = 500; // Milisegundos de espera después de escribir
    
    // Elementos para eliminación múltiple
    const selectAll = document.getElementById('selectAll');
    const deleteBtn = document.getElementById('deleteMultipleBtn');
    const formBulk = document.getElementById('bulkDeleteForm');
    
    // Función para realizar la búsqueda
    function performSearch() {
      const searchTerm = searchInput.value.trim();
      const tipoValue = tipoFilter.value;
      const stockValue = stockFilter.value;
      
      // Mostrar indicador de carga
      loadingIndicator.style.display = 'block';
      
      // Construir URL con parámetros
      const url = new URL('{{ route('insumos.index') }}');
      const params = new URLSearchParams();
      
      if (searchTerm) params.append('q', searchTerm);
      if (tipoValue) params.append('tipo_insumo', tipoValue);
      if (stockValue) params.append('stock', stockValue);
      
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
        const newTable = tempDiv.querySelector('#insumosTable');
        const newPagination = tempDiv.querySelector('#paginationContainer');
        
        // Actualizar el contenido
        if (newTable) insumosTable.innerHTML = newTable.innerHTML;
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
      // Reasignar eventos de checkboxes
      const newSelectAll = document.getElementById('selectAll');
      const newRowCheckboxes = document.querySelectorAll('.row-checkbox');
      
      if (newSelectAll) {
        newSelectAll.addEventListener('change', function() {
          newRowCheckboxes.forEach(cb => cb.checked = this.checked);
          toggleBulkBtn();
        });
      }
      
      newRowCheckboxes.forEach(cb => {
        cb.addEventListener('change', toggleBulkBtn);
      });
      
      // Reasignar eventos de eliminación individual
      const deleteForms = document.querySelectorAll('form[action*="destroy"]');
      deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
          const nombre = this.closest('tr').querySelector('td:nth-child(3)').textContent.trim();
          if (!confirm(`¿Eliminar el insumo ${nombre}?`)) {
            e.preventDefault();
          }
        });
      });
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
    tipoFilter.addEventListener('change', performSearch);
    stockFilter.addEventListener('change', performSearch);
    
    // Funciones para eliminación múltiple
    function toggleBulkBtn() {
      const any = document.querySelectorAll('.row-checkbox:checked').length > 0;
      deleteBtn.classList.toggle('d-none', !any);
    }
    
    // Asignar eventos iniciales para eliminación múltiple
    if (selectAll) {
      selectAll.addEventListener('change', function() {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
        toggleBulkBtn();
      });
    }
    
    document.querySelectorAll('.row-checkbox').forEach(cb => {
      cb.addEventListener('change', toggleBulkBtn);
    });
    
    if (deleteBtn) {
      deleteBtn.addEventListener('click', function() {
        if (confirm('¿Eliminar los insumos seleccionados?')) formBulk.submit();
      });
    }
    
    // Reasignar eventos iniciales
    reassignTableEvents();
  });
</script>
@endsection