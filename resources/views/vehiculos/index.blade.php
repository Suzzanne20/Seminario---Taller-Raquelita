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

  /* Estilos para tabla responsiva */
  .table-responsive {
    max-height: 75vh;
    overflow-y: auto;
  }
  
  .table th {
    position: sticky;
    top: 0;
    background: #343a40;
    z-index: 10;
  }

  /* Estilos para tooltips de información */
  .info-badge {
    cursor: pointer;
    transition: all 0.3s ease;
  }
  .info-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  }

  /* Estilos para modal de detalles */
  .detail-item {
    border-bottom: 1px solid #e9ecef;
    padding: 8px 0;
  }
  .detail-item:last-child {
    border-bottom: none;
  }
  .detail-label {
    font-weight: 600;
    color: #495057;
    min-width: 200px;
  }
  .detail-value {
    color: #212529;
  }

  /* Estilos para resultados de búsqueda rápida */
  .search-result-card {
    border-left: 4px solid #9F3B3B;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 15px;
  }
  .search-section {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 10px;
  }
  .search-section-title {
    color: #9F3B3B;
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 0.95rem;
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

    <form action="{{ route('vehiculos.index') }}" method="GET" class="d-flex align-items-center gap-2" id="searchForm">
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Buscar por placa, línea o marca…"
               value="{{ request('q') }}" id="searchInput">
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

  {{-- Resultados de Búsqueda Rápida (solo cuando se busca por placa exacta) --}}
  @if(request('q') && $vehiculos->count() == 1 && preg_match('/^[A-Za-z][0-9]{3}[A-Za-z]{3}$/i', request('q')))
    @php $vehiculo = $vehiculos->first(); @endphp
    <div class="search-result-card p-4 mb-4" id="printable-vehiculo">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <h4 class="text-primary mb-0">
          <i class="bi bi-car-front me-2"></i>Resultado de Búsqueda: {{ $vehiculo->placa }}
        </h4>
        <div class="d-flex gap-2">
          {{-- Botón de Imprimir --}}
          <button class="btn btn-sm btn-outline-success" onclick="imprimirVehiculo()">
            <i class="bi bi-printer me-1"></i>Imprimir
          </button>
          <a href="{{ route('vehiculos.edit', $vehiculo->placa) }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-pencil-square me-1"></i>Editar
          </a>
          <button type="button" 
                  class="btn btn-sm btn-outline-danger" 
                  onclick="confirmarEliminacionVehiculo('{{ $vehiculo->placa }}', '{{ $vehiculo->marca->nombre ?? 'N/A' }}', '{{ $vehiculo->linea }}', '{{ $vehiculo->modelo }}')">
            <i class="bi bi-trash me-1"></i>Eliminar
          </button>
        </div>
      </div>
      
      <div class="row">
        {{-- Información Básica --}}
        <div class="col-md-6">
          <div class="search-section">
            <div class="search-section-title">
              <i class="bi bi-car-front me-1"></i>Información Básica
            </div>
            <div class="row small">
              <div class="col-6"><strong>Marca:</strong> {{ $vehiculo->marca->nombre ?? '—' }}</div>
              <div class="col-6"><strong>Modelo:</strong> {{ $vehiculo->modelo ?? '—' }}</div>
              <div class="col-6"><strong>Línea:</strong> {{ $vehiculo->linea ?? '—' }}</div>
              <div class="col-6"><strong>Motor:</strong> {{ $vehiculo->motor ?? '—' }}</div>
              <div class="col-6"><strong>Cilindraje:</strong> {{ $vehiculo->cilindraje ?? '—' }}</div>
            </div>
          </div>
        </div>
        
        {{-- Sistema de Lubricación --}}
        <div class="col-md-6">
          @if($vehiculo->cantidad_aceite_motor || $vehiculo->marca_aceite || $vehiculo->tipo_aceite || $vehiculo->filtro_aceite || $vehiculo->filtro_aire)
          <div class="search-section">
            <div class="search-section-title">
              <i class="bi bi-droplet me-1"></i>Sistema de Lubricación
            </div>
            <div class="row small">
              @if($vehiculo->cantidad_aceite_motor)
                <div class="col-12"><strong>Aceite Motor:</strong> {{ $vehiculo->cantidad_aceite_motor }}</div>
              @endif
              @if($vehiculo->marca_aceite)
                <div class="col-12"><strong>Marca Aceite:</strong> {{ $vehiculo->marca_aceite }}</div>
              @endif
              @if($vehiculo->tipo_aceite)
                <div class="col-12"><strong>Tipo Aceite:</strong> {{ $vehiculo->tipo_aceite }}</div>
              @endif
              @if($vehiculo->filtro_aceite)
                <div class="col-12"><strong>Filtro Aceite:</strong> {{ $vehiculo->filtro_aceite }}</div>
              @endif
              @if($vehiculo->filtro_aire)
                <div class="col-12"><strong>Filtro Aire:</strong> {{ $vehiculo->filtro_aire }}</div>
              @endif
            </div>
          </div>
          @endif
        </div>
      </div>

      <div class="row">
        {{-- Caja de Cambios --}}
        <div class="col-md-6">
            @if($vehiculo->cantidad_aceite_cc || $vehiculo->marca_cc || $vehiculo->tipo_aceite_cc || $vehiculo->filtro_aceite_cc || $vehiculo->filtro_de_enfriador || $vehiculo->tipo_caja)
            <div class="search-section">
                <div class="search-section-title">
                    <i class="bi bi-gear me-1"></i>Caja de Cambios
                </div>
                <div class="row small">
                    @if($vehiculo->cantidad_aceite_cc)
                        <div class="col-12"><strong>Aceite CC:</strong> {{ $vehiculo->cantidad_aceite_cc }}</div>
                    @endif
                    @if($vehiculo->marca_cc)
                        <div class="col-12"><strong>Marca CC:</strong> {{ $vehiculo->marca_cc }}</div>
                    @endif
                    @if($vehiculo->tipo_aceite_cc)
                        <div class="col-12"><strong>Tipo Aceite CC:</strong> {{ $vehiculo->tipo_aceite_cc }}</div>
                    @endif
                    @if($vehiculo->filtro_aceite_cc)
                        <div class="col-12"><strong>Filtro Aceite CC:</strong> {{ $vehiculo->filtro_aceite_cc }}</div>
                    @endif
                    @if($vehiculo->filtro_de_enfriador)
                        <div class="col-12"><strong>Filtro Enfriador:</strong> {{ $vehiculo->filtro_de_enfriador }}</div>
                    @endif
                    @if($vehiculo->tipo_caja)
                        <div class="col-12"><strong>Tipo Caja:</strong> {{ $vehiculo->tipo_caja }}</div>
                    @endif
                </div>
            </div>
            @endif
        </div>
        
        {{-- Diferencial --}}
        <div class="col-md-6">
          @if($vehiculo->cantidad_aceite_diferencial || $vehiculo->marca_aceite_d || $vehiculo->tipo_aceite_d)
          <div class="search-section">
            <div class="search-section-title">
              <i class="bi bi-gear-fill me-1"></i>Diferencial
            </div>
            <div class="row small">
              @if($vehiculo->cantidad_aceite_diferencial)
                <div class="col-12"><strong>Aceite Diferencial:</strong> {{ $vehiculo->cantidad_aceite_diferencial }}</div>
              @endif
              @if($vehiculo->marca_aceite_d)
                <div class="col-12"><strong>Marca Aceite D:</strong> {{ $vehiculo->marca_aceite_d }}</div>
              @endif
              @if($vehiculo->tipo_aceite_d)
                <div class="col-12"><strong>Tipo Aceite D:</strong> {{ $vehiculo->tipo_aceite_d }}</div>
              @endif
            </div>
          </div>
          @endif
        </div>
      </div>

      <div class="row">
        {{-- Transfer --}}
        <div class="col-md-6">
          @if($vehiculo->cantidad_aceite_transfer || $vehiculo->marca_aceite_t || $vehiculo->tipo_aceite_t)
          <div class="search-section">
            <div class="search-section-title">
              <i class="bi bi-gear-wide me-1"></i>Transfer
            </div>
            <div class="row small">
              @if($vehiculo->cantidad_aceite_transfer)
                <div class="col-12"><strong>Aceite Transfer:</strong> {{ $vehiculo->cantidad_aceite_transfer }}</div>
              @endif
              @if($vehiculo->marca_aceite_t)
                <div class="col-12"><strong>Marca Aceite T:</strong> {{ $vehiculo->marca_aceite_t }}</div>
              @endif
              @if($vehiculo->tipo_aceite_t)
                <div class="col-12"><strong>Tipo Aceite T:</strong> {{ $vehiculo->tipo_aceite_t }}</div>
              @endif
            </div>
          </div>
          @endif
        </div>

        {{-- Componentes y Frenos --}}
        <div class="col-md-6">
          @if($vehiculo->pastillas_delanteras || $vehiculo->pastillas_traseras || $vehiculo->fajas || $vehiculo->candelas || $vehiculo->aceite_hidraulico)
          <div class="search-section">
            <div class="search-section-title">
              <i class="bi bi-lightning-charge me-1"></i>Frenos Y Repuestos Multiples
            </div>
            <div class="row small">
              @if($vehiculo->pastillas_delanteras)
                <div class="col-12"><strong>Pastillas Delanteras:</strong> {{ $vehiculo->pastillas_delanteras }}</div>
              @endif
              @if($vehiculo->pastillas_traseras)
                <div class="col-12"><strong>Pastillas Traseras:</strong> {{ $vehiculo->pastillas_traseras }}</div>
              @endif
              @if($vehiculo->fajas)
                <div class="col-12"><strong>Fajas:</strong> {{ $vehiculo->fajas }}</div>
              @endif
              @if($vehiculo->candelas)
                <div class="col-12"><strong>Candelas:</strong> {{ $vehiculo->candelas }}</div>
              @endif
              @if($vehiculo->aceite_hidraulico)
                <div class="col-12"><strong>Aceite Hidráulico:</strong> {{ $vehiculo->aceite_hidraulico }}</div>
              @endif
            </div>
          </div>
          @endif
        </div>
      </div>

      {{-- Filtros y Componentes --}}
      @if($vehiculo->filtro_cabina || $vehiculo->filtro_diesel || $vehiculo->contra_filtro_diesel)
      <div class="search-section">
        <div class="search-section-title">
          <i class="bi bi-funnel me-1"></i>Filtros y Componentes
        </div>
        <div class="row small">
          @if($vehiculo->filtro_cabina)
            <div class="col-md-4"><strong>Filtro Cabina:</strong> {{ $vehiculo->filtro_cabina }}</div>
          @endif
          @if($vehiculo->filtro_diesel)
            <div class="col-md-4"><strong>Filtro Diesel:</strong> {{ $vehiculo->filtro_diesel }}</div>
          @endif
          @if($vehiculo->contra_filtro_diesel)
            <div class="col-md-4"><strong>Contra Filtro Diesel:</strong> {{ $vehiculo->contra_filtro_diesel }}</div>
          @endif
        </div>
      </div>
      @endif
    </div>
  @else
    {{-- Tabla (solo se muestra cuando NO es búsqueda por placa exacta) --}}
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
            <th>Información Técnica</th>
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
              <td>
                <div class="d-flex flex-wrap gap-1">
                  {{-- Badge para Sistema de Lubricación --}}
                  @if($v->cantidad_aceite_motor || $v->marca_aceite || $v->tipo_aceite || $v->filtro_aceite || $v->filtro_aire)
                    @php
                      $aceiteInfo = [];
                      if($v->cantidad_aceite_motor) $aceiteInfo[] = "<strong>Aceite Motor:</strong> " . $v->cantidad_aceite_motor;
                      if($v->marca_aceite) $aceiteInfo[] = "<strong>Marca Aceite:</strong> " . $v->marca_aceite;
                      if($v->tipo_aceite) $aceiteInfo[] = "<strong>Tipo Aceite:</strong> " . $v->tipo_aceite;
                      if($v->filtro_aceite) $aceiteInfo[] = "<strong>Filtro Aceite:</strong> " . $v->filtro_aceite;
                      if($v->filtro_aire) $aceiteInfo[] = "<strong>Filtro Aire:</strong> " . $v->filtro_aire;
                      $aceiteTooltip = implode("<br>", $aceiteInfo);
                    @endphp
                    <span class="badge bg-info info-badge" 
                          data-bs-toggle="tooltip" 
                          data-bs-html="true"
                          title="{!! $aceiteTooltip !!}"
                          onclick="mostrarDetallesVehiculo('{{ $v->placa }}')">
                      <i class="bi bi-droplet me-1"></i>Lubricación
                    </span>
                  @endif

                  {{-- Badge para Caja de Cambios --}}
                    @if($v->cantidad_aceite_cc || $v->marca_cc || $v->tipo_aceite_cc || $v->filtro_aceite_cc || $v->filtro_de_enfriador || $v->tipo_caja)
                        @php
                            $cajaInfo = [];
                            if($v->cantidad_aceite_cc) $cajaInfo[] = "<strong>Aceite CC:</strong> " . $v->cantidad_aceite_cc;
                            if($v->marca_cc) $cajaInfo[] = "<strong>Marca CC:</strong> " . $v->marca_cc;
                            if($v->tipo_aceite_cc) $cajaInfo[] = "<strong>Tipo Aceite CC:</strong> " . $v->tipo_aceite_cc;
                            if($v->filtro_aceite_cc) $cajaInfo[] = "<strong>Filtro CC:</strong> " . $v->filtro_aceite_cc;
                            if($v->filtro_de_enfriador) $cajaInfo[] = "<strong>Filtro Enfriador:</strong> " . $v->filtro_de_enfriador;
                            if($v->tipo_caja) $cajaInfo[] = "<strong>Tipo Caja:</strong> " . $v->tipo_caja;
                            $cajaTooltip = implode("<br>", $cajaInfo);
                        @endphp
                        <span class="badge bg-warning info-badge" 
                              data-bs-toggle="tooltip" 
                              data-bs-html="true"
                              title="{!! $cajaTooltip !!}"
                              onclick="mostrarDetallesVehiculo('{{ $v->placa }}')">
                            <i class="bi bi-gear me-1"></i>Caja
                        </span>
                    @endif

                  {{-- Badge para Diferencial --}}
                  @if($v->cantidad_aceite_diferencial || $v->marca_aceite_d || $v->tipo_aceite_d)
                    @php
                      $diferencialInfo = [];
                      if($v->cantidad_aceite_diferencial) $diferencialInfo[] = "<strong>Aceite Diferencial:</strong> " . $v->cantidad_aceite_diferencial;
                      if($v->marca_aceite_d) $diferencialInfo[] = "<strong>Marca Aceite:</strong> " . $v->marca_aceite_d;
                      if($v->tipo_aceite_d) $diferencialInfo[] = "<strong>Tipo Aceite:</strong> " . $v->tipo_aceite_d;
                      $diferencialTooltip = implode("<br>", $diferencialInfo);
                    @endphp
                    <span class="badge bg-primary info-badge" 
                          data-bs-toggle="tooltip" 
                          data-bs-html="true"
                          title="{!! $diferencialTooltip !!}"
                          onclick="mostrarDetallesVehiculo('{{ $v->placa }}')">
                      <i class="bi bi-gear-fill me-1"></i>Diferencial
                    </span>
                  @endif

                  {{-- Badge para Transfer --}}
                  @if($v->cantidad_aceite_transfer || $v->marca_aceite_t || $v->tipo_aceite_t)
                    @php
                      $transferInfo = [];
                      if($v->cantidad_aceite_transfer) $transferInfo[] = "<strong>Aceite Transfer:</strong> " . $v->cantidad_aceite_transfer;
                      if($v->marca_aceite_t) $transferInfo[] = "<strong>Marca Aceite T:</strong> " . $v->marca_aceite_t;
                      if($v->tipo_aceite_t) $transferInfo[] = "<strong>Tipo Aceite T:</strong> " . $v->tipo_aceite_t;
                      $transferTooltip = implode("<br>", $transferInfo);
                    @endphp
                    <span class="badge bg-success info-badge" 
                          data-bs-toggle="tooltip" 
                          data-bs-html="true"
                          title="{!! $transferTooltip !!}"
                          onclick="mostrarDetallesVehiculo('{{ $v->placa }}')">
                      <i class="bi bi-gear-wide me-1"></i>Transfer
                    </span>
                  @endif

                  {{-- Badge para Filtros y Componentes --}}
                  @if($v->filtro_cabina || $v->filtro_diesel || $v->contra_filtro_diesel)
                    @php
                      $filtrosInfo = [];
                      if($v->filtro_cabina) $filtrosInfo[] = "<strong>Filtro Cabina:</strong> " . $v->filtro_cabina;
                      if($v->filtro_diesel) $filtrosInfo[] = "<strong>Filtro Diesel:</strong> " . $v->filtro_diesel;
                      if($v->contra_filtro_diesel) $filtrosInfo[] = "<strong>Contra Filtro:</strong> " . $v->contra_filtro_diesel;
                      $filtrosTooltip = implode("<br>", $filtrosInfo);
                    @endphp
                    <span class="badge bg-secondary info-badge" 
                          data-bs-toggle="tooltip" 
                          data-bs-html="true"
                          title="{!! $filtrosTooltip !!}"
                          onclick="mostrarDetallesVehiculo('{{ $v->placa }}')">
                      <i class="bi bi-funnel me-1"></i>Filtros
                    </span>
                  @endif

                  {{-- Badge para Sistema Eléctrico y Frenos --}}
                  @if($v->pastillas_delanteras || $v->pastillas_traseras || $v->candelas || $v->fajas || $v->aceite_hidraulico)
                    @php
                      $frenosInfo = [];
                      if($v->pastillas_delanteras) $frenosInfo[] = "<strong>Pastillas Del:</strong> " . $v->pastillas_delanteras;
                      if($v->pastillas_traseras) $frenosInfo[] = "<strong>Pastillas Tras:</strong> " . $v->pastillas_traseras;
                      if($v->candelas) $frenosInfo[] = "<strong>Candelas:</strong> " . $v->candelas;
                      if($v->fajas) $frenosInfo[] = "<strong>Fajas:</strong> " . $v->fajas;
                      if($v->aceite_hidraulico) $frenosInfo[] = "<strong>Aceite Hidráulico:</strong> " . $v->aceite_hidraulico;
                      $frenosTooltip = implode("<br>", $frenosInfo);
                    @endphp
                    <span class="badge bg-danger info-badge" 
                          data-bs-toggle="tooltip" 
                          data-bs-html="true"
                          title="{!! $frenosTooltip !!}"
                          onclick="mostrarDetallesVehiculo('{{ $v->placa }}')">
                      <i class="bi bi-lightning-charge me-1"></i>Frenos/RP
                    </span>
                  @endif

                  @if(!$v->cantidad_aceite_motor && !$v->cantidad_aceite_cc && !$v->cantidad_aceite_diferencial && 
                      !$v->cantidad_aceite_transfer && !$v->filtro_cabina && !$v->pastillas_delanteras)
                    <span class="text-muted small">Sin datos técnicos</span>
                  @endif
                </div>
              </td>
              <td class="text-center">
                <div class="d-inline-flex gap-2">
                  <button type="button" 
                          class="btn btn-sm btn-outline-info btn-action" 
                          title="Ver detalles completos"
                          onclick="mostrarDetallesVehiculo('{{ $v->placa }}')">
                    <i class="bi bi-eye"></i>
                  </button>
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
              <td colspan="8" class="text-center py-4">
                <i class="bi bi-car-front display-6 text-muted mb-3 d-block"></i>
                No hay vehículos registrados
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Paginación (solo se muestra cuando NO es búsqueda por placa exacta) --}}
    <div class="mt-3">
      {{ $vehiculos->links() }}
    </div>
  @endif
</div>

{{-- Formulario oculto para eliminación --}}
<form id="formEliminarVehiculo" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
</form>

<!-- Modal para detalles del vehículo -->
<div class="modal fade" id="detallesModal" tabindex="-1" aria-labelledby="detallesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detallesModalLabel">Detalles Completos del Vehículo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="detallesModalBody">
        <!-- Los detalles se cargarán aquí via JavaScript -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
// Variable global para controlar la ventana de impresión
let ventanaImpresion = null;

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

// Función mejorada para imprimir los datos del vehículo
function imprimirVehiculo() {
    // Mostrar loading
    const loadingAlert = Swal.fire({
        title: 'Preparando impresión...',
        text: 'Generando formato de impresión',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Obtener todos los datos del vehículo directamente del HTML
    const getVehiculoData = () => {
        // Obtener datos del elemento HTML en lugar de variables PHP
        const printableElement = document.getElementById('printable-vehiculo');
        if (!printableElement) {
            console.error('No se encontró el elemento printable-vehiculo');
            return null;
        }

        // Función auxiliar para extraer texto de elementos
        const getTextFromSelector = (selector) => {
            const element = printableElement.querySelector(selector);
            return element ? element.textContent.trim() : '—';
        };

        // Función auxiliar para extraer datos de secciones específicas
        const getSectionData = (sectionTitle) => {
            const sections = printableElement.querySelectorAll('.search-section');
            for (let section of sections) {
                const titleElement = section.querySelector('.search-section-title');
                if (titleElement && titleElement.textContent.includes(sectionTitle)) {
                    const data = {};
                    const items = section.querySelectorAll('.row.small > div');
                    items.forEach(item => {
                        const text = item.textContent.trim();
                        if (text.includes(':')) {
                            const [label, value] = text.split(':').map(s => s.trim());
                            // Convertir label a clave válida
                            const key = label.toLowerCase()
                                .replace(/\s+/g, '_')
                                .replace(/[^a-z0-9_]/g, '')
                                .replace('filtro_enfriador', 'filtro_de_enfriador'); // Corrección específica
                            data[key] = value;
                        }
                    });
                    return data;
                }
            }
            return {};
        };

        // Extraer datos básicos
        const headerElement = printableElement.querySelector('h4');
        const placa = headerElement ? headerElement.textContent.replace('Resultado de Búsqueda:', '').trim() : '—';

        // Extraer datos de todas las secciones
        const basicData = getSectionData('Información Básica');
        const lubricacionData = getSectionData('Sistema de Lubricación');
        const cajaData = getSectionData('Caja de Cambios');
        const diferencialData = getSectionData('Diferencial');
        const transferData = getSectionData('Transfer');
        const frenosData = getSectionData('Frenos Y Repuestos Multiples');
        const filtrosData = getSectionData('Filtros y Componentes');

        return {
            placa: placa,
            marca: basicData.marca || '—',
            modelo: basicData.modelo || '—',
            linea: basicData.línea || '—',
            motor: basicData.motor || '—',
            cilindraje: basicData.cilindraje || '—',
            
            // Sistema de Lubricación
            cantidad_aceite_motor: lubricacionData.aceite_motor || '—',
            marca_aceite: lubricacionData.marca_aceite || '—',
            tipo_aceite: lubricacionData.tipo_aceite || '—',
            filtro_aceite: lubricacionData.filtro_aceite || '—',
            filtro_aire: lubricacionData.filtro_aire || '—',
            
            // Caja de Cambios
            cantidad_aceite_cc: cajaData.aceite_cc || '—',
            marca_cc: cajaData.marca_cc || '—',
            tipo_aceite_cc: cajaData.tipo_aceite_cc || '—',
            filtro_aceite_cc: cajaData.filtro_aceite_cc || '—',
            filtro_de_enfriador: cajaData.filtro_de_enfriador || '—',
            tipo_caja: cajaData.tipo_caja || '—',
            
            // Diferencial
            cantidad_aceite_diferencial: diferencialData.aceite_diferencial || '—',
            marca_aceite_d: diferencialData.marca_aceite_d || '—',
            tipo_aceite_d: diferencialData.tipo_aceite_d || '—',
            
            // Transfer
            cantidad_aceite_transfer: transferData.aceite_transfer || '—',
            marca_aceite_t: transferData.marca_aceite_t || '—',
            tipo_aceite_t: transferData.tipo_aceite_t || '—',
            
            // Filtros y Componentes
            filtro_cabina: filtrosData.filtro_cabina || '—',
            filtro_diesel: filtrosData.filtro_diesel || '—',
            contra_filtro_diesel: filtrosData.contra_filtro_diesel || '—',
            
            // Sistema Eléctrico y Frenos
            pastillas_delanteras: frenosData.pastillas_delanteras || '—',
            pastillas_traseras: frenosData.pastillas_traseras || '—',
            candelas: frenosData.candelas || '—',
            fajas: frenosData.fajas || '—',
            aceite_hidraulico: frenosData.aceite_hidráulico || '—'
        };
    };

    const data = getVehiculoData();
    
    if (!data) {
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudieron obtener los datos del vehículo para imprimir',
            confirmButtonColor: '#9F3B3B'
        });
        return;
    }

    // Función para crear items de información
    const createInfoItem = (label, value) => {
        if (value && value !== '—') {
            return `<div class="info-item">
                <span class="info-label">${label}:</span>
                <span class="info-value">${value}</span>
            </div>`;
        }
        return '';
    };

    // Crear contenido optimizado para impresión
    const contenidoImpresion = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Ficha Técnica - ${data.placa}</title>
            <meta charset="UTF-8">
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
                
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: 'Inter', sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background: #fff;
                    padding: 25px;
                }
                
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    padding-bottom: 20px;
                    border-bottom: 3px solid #9F3B3B;
                }
                
                .header h1 {
                    color: #9F3B3B;
                    font-size: 28px;
                    font-weight: 700;
                    margin-bottom: 5px;
                }
                
                .header .placa {
                    background: #9F3B3B;
                    color: white;
                    display: inline-block;
                    padding: 8px 20px;
                    border-radius: 8px;
                    font-size: 20px;
                    font-weight: 600;
                    letter-spacing: 2px;
                }
                
                .info-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 20px;
                    margin-bottom: 25px;
                }
                
                .info-card {
                    background: #f8f9fa;
                    border: 1px solid #e9ecef;
                    border-radius: 12px;
                    padding: 20px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                }
                
                .info-card h3 {
                    color: #9F3B3B;
                    font-size: 16px;
                    font-weight: 600;
                    margin-bottom: 15px;
                    padding-bottom: 8px;
                    border-bottom: 2px solid #9F3B3B;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .info-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 8px 0;
                    border-bottom: 1px solid #e9ecef;
                }
                
                .info-item:last-child {
                    border-bottom: none;
                }
                
                .info-label {
                    font-weight: 500;
                    color: #495057;
                    font-size: 13px;
                }
                
                .info-value {
                    font-weight: 400;
                    color: #212529;
                    font-size: 13px;
                    text-align: right;
                    max-width: 60%;
                }
                
                .section-title {
                    background: linear-gradient(135deg, #9F3B3B, #C24242);
                    color: white;
                    padding: 12px 20px;
                    border-radius: 8px;
                    margin: 25px 0 15px 0;
                    font-weight: 600;
                    font-size: 16px;
                }
                
                .footer {
                    text-align: center;
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 2px solid #e9ecef;
                    color: #6c757d;
                    font-size: 12px;
                }
                
                .print-date {
                    margin-top: 10px;
                    font-style: italic;
                }
                
                .no-print {
                    text-align: center;
                    margin-top: 30px;
                    padding: 20px;
                }
                
                .print-btn {
                    background: #9F3B3B;
                    color: white;
                    border: none;
                    padding: 12px 24px;
                    border-radius: 6px;
                    font-size: 14px;
                    cursor: pointer;
                    margin-right: 10px;
                    transition: background 0.3s;
                }
                
                .print-btn:hover {
                    background: #873131;
                }
                
                .close-btn {
                    background: #6c757d;
                    color: white;
                    border: none;
                    padding: 12px 24px;
                    border-radius: 6px;
                    font-size: 14px;
                    cursor: pointer;
                    transition: background 0.3s;
                }
                
                .close-btn:hover {
                    background: #5a6268;
                }
                
                @media print {
                    body {
                        padding: 15px;
                    }
                    
                    .no-print {
                        display: none !important;
                    }
                    
                    .info-card {
                        break-inside: avoid;
                    }
                    
                    .header {
                        margin-bottom: 20px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>FICHA TÉCNICA DE VEHÍCULO</h1>
                <div class="placa">${data.placa}</div>
            </div>
            
            <div class="section-title">
                INFORMACIÓN BÁSICA DEL VEHÍCULO
            </div>
            
            <div class="info-grid">
                <div class="info-card">
                    <h3>📋 Datos Generales</h3>
                    ${createInfoItem('Placa', data.placa)}
                    ${createInfoItem('Marca', data.marca)}
                    ${createInfoItem('Modelo', data.modelo)}
                    ${createInfoItem('Línea', data.linea)}
                    ${createInfoItem('Motor', data.motor)}
                    ${createInfoItem('Cilindraje', data.cilindraje)}
                </div>
                
                <div class="info-card">
                    <h3>🛢️ Sistema de Lubricación</h3>
                    ${createInfoItem('Aceite Motor', data.cantidad_aceite_motor)}
                    ${createInfoItem('Marca Aceite', data.marca_aceite)}
                    ${createInfoItem('Tipo Aceite', data.tipo_aceite)}
                    ${createInfoItem('Filtro Aceite', data.filtro_aceite)}
                    ${createInfoItem('Filtro Aire', data.filtro_aire)}
                </div>
                
                <div class="info-card">
                    <h3>⚙️ Caja de Cambios</h3>
                    ${createInfoItem('Aceite CC', data.cantidad_aceite_cc)}
                    ${createInfoItem('Marca CC', data.marca_cc)}
                    ${createInfoItem('Tipo Aceite CC', data.tipo_aceite_cc)}
                    ${createInfoItem('Filtro Aceite CC', data.filtro_aceite_cc)}
                    ${createInfoItem('Filtro Enfriador', data.filtro_de_enfriador)}
                    ${createInfoItem('Tipo Caja', data.tipo_caja)}
                </div>
                
                <div class="info-card">
                    <h3>🔧 Diferencial</h3>
                    ${createInfoItem('Aceite Diferencial', data.cantidad_aceite_diferencial)}
                    ${createInfoItem('Marca Aceite D', data.marca_aceite_d)}
                    ${createInfoItem('Tipo Aceite D', data.tipo_aceite_d)}
                </div>
                
                <div class="info-card">
                    <h3>🔄 Transfer</h3>
                    ${createInfoItem('Aceite Transfer', data.cantidad_aceite_transfer)}
                    ${createInfoItem('Marca Aceite T', data.marca_aceite_t)}
                    ${createInfoItem('Tipo Aceite T', data.tipo_aceite_t)}
                </div>
                
                <div class="info-card">
                    <h3>🔍 Filtros y Componentes</h3>
                    ${createInfoItem('Filtro Cabina', data.filtro_cabina)}
                    ${createInfoItem('Filtro Diesel', data.filtro_diesel)}
                    ${createInfoItem('Contra Filtro Diesel', data.contra_filtro_diesel)}
                </div>
                
                <div class="info-card">
                    <h3>⚡ Frenos Y Respuestos Multiples</h3>
                    ${createInfoItem('Pastillas Delanteras', data.pastillas_delanteras)}
                    ${createInfoItem('Pastillas Traseras', data.pastillas_traseras)}
                    ${createInfoItem('Candelas', data.candelas)}
                    ${createInfoItem('Fajas', data.fajas)}
                    ${createInfoItem('Aceite Hidráulico', data.aceite_hidraulico)}
                </div>
            </div>
            
            <div class="footer">
                <p>Sistema de Gestión de Vehículos - Taller Mecánico</p>
                <p class="print-date">Generado el ${new Date().toLocaleDateString('es-ES', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })}</p>
            </div>
            
            <div class="no-print">
                <button class="print-btn" onclick="window.print()">
                    🖨️ Imprimir
                </button>
                <button class="close-btn" onclick="window.close()">
                    ❌ Cerrar
                </button>
            </div>

            <script>
                // Auto-imprimir después de un breve delay
                setTimeout(() => {
                    window.print();
                }, 500);

                // Cerrar ventana después de imprimir (en algunos navegadores)
                window.onafterprint = function() {
                    setTimeout(() => {
                        window.close();
                    }, 1000);
                };
            <\/script>
        </body>
        </html>
    `;

    // Usar setTimeout para asegurar que el SweetAlert se cierre
    setTimeout(() => {
        // Cerrar el loading
        Swal.close();
        
        // Abrir ventana de impresión
        ventanaImpresion = window.open('', '_blank', 'width=1000,height=700,scrollbars=yes');
        ventanaImpresion.document.write(contenidoImpresion);
        ventanaImpresion.document.close();

        // Enfocar la ventana de impresión
        setTimeout(() => {
            if (ventanaImpresion) {
                ventanaImpresion.focus();
            }
        }, 100);
        
    }, 500);

}

// Función para mostrar detalles completos del vehículo
function mostrarDetallesVehiculo(placa) {
  // Mostrar loading
  Swal.fire({
    title: 'Cargando...',
    text: 'Obteniendo información del vehículo',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  // Hacer solicitud AJAX para obtener los detalles
  fetch(`/vehiculos/${placa}/detalles`)
    .then(response => {
      if (!response.ok) {
        throw new Error('Error al cargar los detalles');
      }
      return response.json();
    })
    .then(data => {
      Swal.close();
      
      // Construir el contenido del modal
      const contenido = `
        <div class="row">
          <div class="col-md-6">
            <h6 class="text-primary mb-3"><i class="bi bi-car-front me-2"></i>Información Básica</h6>
            <div class="detail-item">
              <span class="detail-label">Placa:</span>
              <span class="detail-value fw-bold">${data.placa}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Marca:</span>
              <span class="detail-value">${data.marca}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Modelo:</span>
              <span class="detail-value">${data.modelo}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Línea:</span>
              <span class="detail-value">${data.linea}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Motor:</span>
              <span class="detail-value">${data.motor}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Cilindraje:</span>
              <span class="detail-value">${data.cilindraje}</span>
            </div>
          </div>
          
          <div class="col-md-6">
            <h6 class="text-success mb-3"><i class="bi bi-droplet me-2"></i>Sistema de Lubricación</h6>
            <div class="detail-item">
              <span class="detail-label">Aceite Motor:</span>
              <span class="detail-value">${data.cantidad_aceite_motor || '—'} ${data.marca_aceite ? '(' + data.marca_aceite + ')' : ''}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Tipo Aceite:</span>
              <span class="detail-value">${data.tipo_aceite || '—'}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Filtro Aceite:</span>
              <span class="detail-value">${data.filtro_aceite || '—'}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Filtro Aire:</span>
              <span class="detail-value">${data.filtro_aire || '—'}</span>
            </div>
          </div>
        </div>
        
        <div class="row mt-4">
          <div class="col-md-6">
              <h6 class="text-warning mb-3"><i class="bi bi-gear me-2"></i>Caja de Cambios</h6>
              <div class="detail-item">
                  <span class="detail-label">Aceite CC:</span>
                  <span class="detail-value">${data.cantidad_aceite_cc || '—'} ${data.marca_cc ? '(' + data.marca_cc + ')' : ''}</span>
              </div>
              <div class="detail-item">
                  <span class="detail-label">Tipo Aceite CC:</span>
                  <span class="detail-value">${data.tipo_aceite_cc || '—'}</span>
              </div>
              <div class="detail-item">
                  <span class="detail-label">Filtro Aceite CC:</span>
                  <span class="detail-value">${data.filtro_aceite_cc || '—'}</span>
              </div>
                  <div class="detail-item">
                      <span class="detail-label">Filtro de Enfriador:</span>
                      <span class="detail-value">${data.filtro_de_enfriador || '—'}</span>
                  </div>
              <div class="detail-item">
                  <span class="detail-label">Tipo Caja:</span>
                  <span class="detail-value">${data.tipo_caja || '—'}</span>
              </div>
          </div>
          
          <div class="col-md-6">
            <h6 class="text-info mb-3"><i class="bi bi-gear-fill me-2"></i>Diferencial</h6>
            <div class="detail-item">
              <span class="detail-label">Aceite Diferencial:</span>
              <span class="detail-value">${data.cantidad_aceite_diferencial || '—'}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Marca Aceite D:</span>
              <span class="detail-value">${data.marca_aceite_d || '—'}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Tipo Aceite D:</span>
              <span class="detail-value">${data.tipo_aceite_d || '—'}</span>
            </div>
          </div>
        </div>

        <!-- SECCIÓN TRANSFER -->
        ${(data.cantidad_aceite_transfer || data.marca_aceite_t || data.tipo_aceite_t) ? `
        <div class="row mt-4">
          <div class="col-12">
            <h6 class="text-danger mb-3"><i class="bi bi-gear-wide me-2"></i>Transfer</h6>
            <div class="row">
              ${data.cantidad_aceite_transfer ? `
                <div class="col-md-4 detail-item">
                  <span class="detail-label">Cantidad Aceite:</span>
                  <span class="detail-value">${data.cantidad_aceite_transfer}</span>
                </div>
              ` : ''}
              ${data.marca_aceite_t ? `
                <div class="col-md-4 detail-item">
                  <span class="detail-label">Marca Aceite:</span>
                  <span class="detail-value">${data.marca_aceite_t}</span>
                </div>
              ` : ''}
              ${data.tipo_aceite_t ? `
                <div class="col-md-4 detail-item">
                  <span class="detail-label">Tipo Aceite:</span>
                  <span class="detail-value">${data.tipo_aceite_t}</span>
                </div>
              ` : ''}
            </div>
          </div>
        </div>
        ` : ''}
        
        <!-- SECCIÓN FILTROS Y COMPONENTES -->
        ${(data.filtro_cabina || data.filtro_diesel || data.contra_filtro_diesel) ? `
        <div class="row mt-4">
          <div class="col-12">
            <h6 class="text-secondary mb-3"><i class="bi bi-funnel me-2"></i>Filtros y Componentes</h6>
            <div class="row">
              ${data.filtro_cabina ? `
                <div class="col-md-4 detail-item">
                  <span class="detail-label">Filtro Cabina:</span>
                  <span class="detail-value">${data.filtro_cabina}</span>
                </div>
              ` : ''}
              ${data.filtro_diesel ? `
                <div class="col-md-4 detail-item">
                  <span class="detail-label">Filtro Diesel:</span>
                  <span class="detail-value">${data.filtro_diesel}</span>
                </div>
              ` : ''}
              ${data.contra_filtro_diesel ? `
                <div class="col-md-4 detail-item">
                  <span class="detail-label">Contra Filtro Diesel:</span>
                  <span class="detail-value">${data.contra_filtro_diesel}</span>
                </div>
              ` : ''}
            </div>
          </div>
        </div>
        ` : ''}
        
        <!-- FRENOS Y RESPUESTOS MULTIPLES -->
        ${(data.pastillas_delanteras || data.pastillas_traseras || data.candelas || data.fajas || data.aceite_hidraulico) ? `
        <div class="row mt-4">
          <div class="col-12">
            <h6 class="text-dark mb-3"><i class="bi bi-lightning-charge me-2"></i>Frenos Y Repuestos Multiples</h6>
            <div class="row">
              ${data.pastillas_delanteras ? `
                <div class="col-md-4 detail-item">
                  <span class="detail-label">Pastillas Delanteras:</span>
                  <span class="detail-value">${data.pastillas_delanteras}</span>
                </div>
              ` : ''}
              ${data.pastillas_traseras ? `
                <div class="col-md-4 detail-item">
                  <span class="detail-label">Pastillas Traseras:</span>
                  <span class="detail-value">${data.pastillas_traseras}</span>
                </div>
              ` : ''}
              ${data.candelas ? `
                <div class="col-md-4 detail-item">
                  <span class="detail-label">Candelas:</span>
                  <span class="detail-value">${data.candelas}</span>
                </div>
              ` : ''}
              ${data.fajas ? `
                <div class="col-md-4 detail-item">
                  <span class="detail-label">Fajas:</span>
                  <span class="detail-value">${data.fajas}</span>
                </div>
              ` : ''}
              ${data.aceite_hidraulico ? `
                <div class="col-md-4 detail-item">
                  <span class="detail-label">Aceite Hidráulico:</span>
                  <span class="detail-value">${data.aceite_hidraulico}</span>
                </div>
              ` : ''}
            </div>
          </div>
        </div>
        ` : ''}
      `;

      document.getElementById('detallesModalBody').innerHTML = contenido;
      
      // Mostrar el modal
      const modal = new bootstrap.Modal(document.getElementById('detallesModal'));
      modal.show();
    })
    .catch(error => {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudieron cargar los detalles del vehículo',
        confirmButtonColor: '#9F3B3B'
      });
    });
}

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Auto-enfocar el campo de búsqueda
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.focus();
    // Seleccionar el texto si ya tiene un valor
    if (searchInput.value) {
      searchInput.select();
    }
  }
});

// Búsqueda en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    let timeoutId;

    // Función para realizar búsqueda automática
    function realizarBusquedaAutomatica() {
        const query = searchInput.value.trim();
        
        // Solo buscar si hay al menos 3 caracteres o está vacío (para limpiar)
        if (query.length >= 3 || query.length === 0) {
            // Usar Fetch API para búsqueda en tiempo real
            const url = new URL('{{ route('vehiculos.index') }}', window.location.origin);
            if (query) {
                url.searchParams.set('q', query);
            }

            fetch(url)
                .then(response => response.text())
                .then(html => {
                    // Crear un documento temporal para extraer la tabla
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTable = doc.querySelector('.table-responsive');
                    const newPagination = doc.querySelector('.pagination');
                    
                    // Reemplazar solo la tabla y paginación
                    if (newTable) {
                        const currentTable = document.querySelector('.table-responsive');
                        if (currentTable) {
                            currentTable.innerHTML = newTable.innerHTML;
                        }
                    }
                    
                    if (newPagination) {
                        const currentPagination = document.querySelector('.pagination');
                        if (currentPagination && newPagination.innerHTML.trim()) {
                            currentPagination.innerHTML = newPagination.innerHTML;
                        } else if (currentPagination) {
                            currentPagination.innerHTML = '';
                        }
                    }
                    
                    // Re-inicializar tooltips
                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                })
                .catch(error => {
                    console.error('Error en búsqueda automática:', error);
                });
        }
    }

    // Event listener para input
    searchInput.addEventListener('input', function() {
        // Clear previous timeout
        clearTimeout(timeoutId);
        
        // Set new timeout (debounce)
        timeoutId = setTimeout(realizarBusquedaAutomatica, 500);
    });

    // Prevenir envío del formulario si es búsqueda en tiempo real
    searchForm.addEventListener('submit', function(e) {
        const query = searchInput.value.trim();
        
        // Si la búsqueda ya se mostró en tiempo real, prevenir envío duplicado
        if (query.length >= 3) {
            e.preventDefault();
            // Forzar recarga completa para mostrar resultados de búsqueda
            window.location.href = `{{ route('vehiculos.index') }}?q=${encodeURIComponent(query)}`;
        }
    });

    // Auto-enfocar y seleccionar texto
    if (searchInput) {
        searchInput.focus();
        if (searchInput.value) {
            searchInput.select();
        }
    }
});

</script>
@endsection