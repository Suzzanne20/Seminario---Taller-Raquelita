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

  /* Estilo para botones de acción rápida */
  .btn-action {
      transition: all 0.2s ease;
  }
  .btn-action:hover {
      transform: scale(1.1);
  }
</style>
@endpush

@section('content')
<div class="container py-4">

  {{-- Título --}}
  <div class="container"><br><br>
    <h1 class="text-center mb-4" style="color:#C24242;">Gestión de Vehículos</h1>
  </div>

  {{-- Toolbar: botón + buscador --}}
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="{{ route('vehiculos.create') }}"
       class="btn btn-theme"
       style="border-radius:12px; padding:.55rem 1rem;">
      <i class="bi bi-plus-lg me-1"></i> Agregar Vehículo
    </a>

    <form action="{{ route('vehiculos.index') }}" method="GET" class="d-flex align-items-center gap-2">
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Buscar por placa, línea o marca…"
               value="{{ request('q') }}">
      </div>
      <button class="btn btn-dark" type="submit" style="border-radius:12px;">Buscar</button>
    </form>
  </div>

  {{-- Mensaje --}}
  @if(session('success'))
    <div class="alert alert-success shadow-sm rounded-3">
      <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    </div>
  @endif

  @if(session('warning'))
    <div class="alert alert-warning shadow-sm rounded-3">
      <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
    </div>
  @endif

  {{-- Tabla --}}
  <div class="table-responsive shadow-sm rounded-3">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-dark">
        <tr>
          <th>Placa</th>
          <th>Marca</th>
          <th>Modelo</th>
          <th>Línea</th>
          <th>Motor</th>
          <th>Cilindraje</th>
          <th class="text-center">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($vehiculos as $v)
          <tr>
            <td class="fw-semibold">{{ $v->placa }}</td>
            <td>{{ $v->marca->nombre ?? '—' }}</td>
            <td>{{ $v->modelo ?? '—' }}</td>
            <td>{{ $v->linea ?? '—' }}</td>
            <td>{{ $v->motor ?? '—' }}</td>
            <td>{{ $v->cilindraje ?? '—' }}</td>
            <td class="text-center">
              <div class="d-inline-flex gap-2">
                <a href="{{ route('vehiculos.edit', $v->placa) }}" 
                   class="btn btn-sm btn-outline-primary btn-action" 
                   title="Editar vehículo">
                  <i class="bi bi-pencil-square"></i>
                </a>
                <button type="button" 
                        class="btn btn-sm btn-outline-danger btn-action" 
                        title="Eliminar vehículo"
                        onclick="confirmarEliminacionVehiculo('{{ $v->placa }}', '{{ $v->marca->nombre ?? 'N/A' }}', '{{ $v->linea }}', '{{ $v->modelo }}')">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center py-4">
              <i class="bi bi-car-front display-6 text-muted mb-3 d-block"></i>
              No hay vehículos registrados
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Paginación --}}
  <div class="mt-3">
    {{ $vehiculos->links() }}
  </div>
</div>

{{-- Formulario oculto para eliminación --}}
<form id="formEliminarVehiculo" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
</form>

<script>
// Función para confirmar eliminación de vehículo con SweetAlert2
function confirmarEliminacionVehiculo(placa, marca, linea, modelo) {
  Swal.fire({
    title: '¿Eliminar Vehículo?',
    html: `<div class="text-start">
            <p>¿Estás seguro de que deseas eliminar el siguiente vehículo?</p>
            <div class="alert alert-light border rounded-3 p-3">
              <div class="row">
                <div class="col-6">
                  <strong>Placa:</strong><br>
                  <span class="fs-5 fw-bold text-primary">${placa}</span>
                </div>
                <div class="col-6">
                  <strong>Marca:</strong><br>
                  ${marca}
                </div>
              </div>
              <div class="row mt-2">
                <div class="col-6">
                  <strong>Línea:</strong><br>
                  ${linea}
                </div>
                <div class="col-6">
                  <strong>Modelo:</strong><br>
                  ${modelo}
                </div>
              </div>
            </div>
            <div class="alert alert-warning border rounded-3 mt-3">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>Advertencia:</strong> Esta acción no se puede deshacer.
            </div>
          </div>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#9F3B3B',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="bi bi-trash me-2"></i>Sí, eliminar',
    cancelButtonText: '<i class="bi bi-x-circle me-2"></i>Cancelar',
    customClass: {
      popup: 'rounded-3',
      confirmButton: 'btn-theme-swal',
      cancelButton: 'btn-secondary-swal'
    },
    buttonsStyling: false,
    width: '600px'
  }).then((result) => {
    if (result.isConfirmed) {
      // Configurar y enviar el formulario
      const form = document.getElementById('formEliminarVehiculo');
      form.action = `/vehiculos/${placa}`;
      form.submit();
    }
  });
}
</script>
@endsection