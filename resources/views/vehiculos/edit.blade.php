@extends('layouts.app')

@push('styles')
<style>
  html, body { height: 100%; background:#f0f0f0 !important; }
  .page-body { min-height: calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width: 576px){ .page-body{ min-height: calc(100vh - 64px);} }

  .md-card{
    max-width: 900px;
    margin: 32px auto 64px;
    background:#fff;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
    padding: 28px;
  }
  .md-title{
    font-weight:700; color:#C24242; text-align:center; margin-bottom:18px;
  }

  .form-control, .form-select{
    border:none; border-bottom:2px solid #e6e6e6; border-radius:0; background:transparent; padding-left:0;
  }
  .form-control:focus, .form-select:focus{
    box-shadow:none; border-color:#3f51b5;
  }
  .form-label{ font-size:.9rem; color:#6b7280; }

  .btn-md-primary{ background:#9F3B3B; border:none; }
  .btn-md-secondary{ background:#e5e7eb; color:#111827; border:none; }

  /* Estilos para secciones de campos */
  .section-title {
    background: linear-gradient(135deg, #9F3B3B, #C24242);
    color: white;
    padding: 12px 16px;
    border-radius: 8px;
    margin: 25px 0 15px 0;
    font-weight: 600;
    font-size: 1.1rem;
  }
  .section-subtitle {
    background: #f8f9fa;
    color: #495057;
    padding: 8px 12px;
    border-radius: 6px;
    margin: 20px 0 10px 0;
    font-weight: 500;
    border-left: 4px solid #9F3B3B;
  }
</style>
@endpush

@section('content')
<div class="container">
  <div class="md-card">
    <h2 class="md-title"><i class="bi bi-pencil-square"></i> Editar Vehículo: {{ $vehiculo->placa }}</h2>

    {{-- Errores de validación --}}
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('vehiculos.update', $vehiculo->placa) }}" method="POST" novalidate>
      @csrf
      @method('PUT')

      {{-- SECCIÓN: INFORMACIÓN BÁSICA --}}
      <div class="section-title">
        <i class="bi bi-car-front me-2"></i>Información Básica del Vehículo
      </div>

      <div class="mb-3">
        <label class="form-label">Placa</label>
        <input type="text" class="form-control" value="{{ $vehiculo->placa }}" disabled>
        <small class="form-text text-muted">La placa no se puede modificar</small>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Marca</label>
          <select name="marca_id" class="form-select" required>
            <option value="" disabled>Seleccione una marca</option>
            @foreach ($marcas as $m)
              <option value="{{ $m->id }}"
                @selected(old('marca_id', $vehiculo->marca_id) == $m->id)>
                {{ $m->nombre }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Modelo</label>
          <input type="number" name="modelo"
                 value="{{ old('modelo', $vehiculo->modelo) }}"
                 class="form-control" min="1960" max="{{ date('Y') + 1 }}" required>
        </div>
      </div>

      <div class="row g-3 mt-1">
        <div class="col-md-6">
          <label class="form-label">Línea</label>
          <input type="text" name="linea"
                 value="{{ old('linea', $vehiculo->linea) }}"
                 class="form-control" maxlength="45" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Motor</label>
          <input type="text" name="motor"
                 value="{{ old('motor', $vehiculo->motor) }}"
                 class="form-control" maxlength="45" required>
        </div>
      </div>

      <div class="mt-3">
        <label class="form-label">Cilindraje</label>
        <input type="number" step="0.01" name="cilindraje"
               value="{{ old('cilindraje', $vehiculo->cilindraje) }}"
               class="form-control" min="0.1" required>
      </div>

      {{-- SECCIÓN: SISTEMA DE LUBRICACIÓN --}}
      <div class="section-title">
        <i class="bi bi-droplet me-2"></i>Sistema de Lubricación
      </div>

      <div class="section-subtitle">Aceite del Motor</div>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Cantidad Aceite Motor</label>
          <input type="text" name="cantidad_aceite_motor"
                 value="{{ old('cantidad_aceite_motor', $vehiculo->cantidad_aceite_motor) }}"
                 class="form-control" maxlength="45" placeholder="Ej: 4.2 litros">
        </div>
        <div class="col-md-4">
          <label class="form-label">Marca Aceite</label>
          <input type="text" name="marca_aceite"
                 value="{{ old('marca_aceite', $vehiculo->marca_aceite) }}"
                 class="form-control" maxlength="45" placeholder="Ej: Mobil 1">
        </div>
        <div class="col-md-4">
          <label class="form-label">Tipo Aceite</label>
          <input type="text" name="tipo_aceite"
                 value="{{ old('tipo_aceite', $vehiculo->tipo_aceite) }}"
                 class="form-control" maxlength="45" placeholder="Ej: 5W-30">
        </div>
      </div>

      <div class="row g-3 mt-2">
        <div class="col-md-6">
          <label class="form-label">Filtro Aceite</label>
          <input type="text" name="filtro_aceite"
                 value="{{ old('filtro_aceite', $vehiculo->filtro_aceite) }}"
                 class="form-control" maxlength="45" placeholder="Ej: PH3683">
        </div>
        <div class="col-md-6">
          <label class="form-label">Filtro Aire</label>
          <input type="text" name="filtro_aire"
                 value="{{ old('filtro_aire', $vehiculo->filtro_aire) }}"
                 class="form-control" maxlength="45" placeholder="Ej: A2024">
        </div>
      </div>

        {{-- SECCIÓN: CAJA DE CAMBIOS --}}
        <div class="section-title">
            <i class="bi bi-gear me-2"></i>Caja de Cambios
        </div>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Cantidad Aceite CC</label>
                <input type="text" name="cantidad_aceite_cc"
                      value="{{ old('cantidad_aceite_cc', $vehiculo->cantidad_aceite_cc) }}"
                      class="form-control" maxlength="45" placeholder="Ej: 2.5 litros">
            </div>
            <div class="col-md-3">
                <label class="form-label">Marca CC</label>
                <input type="text" name="marca_cc"
                      value="{{ old('marca_cc', $vehiculo->marca_cc) }}"
                      class="form-control" maxlength="45" placeholder="Ej: Castrol">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo Aceite CC</label>
                <input type="text" name="tipo_aceite_cc"
                      value="{{ old('tipo_aceite_cc', $vehiculo->tipo_aceite_cc) }}"
                      class="form-control" maxlength="45" placeholder="Ej: ATF">
            </div>
            <div class="col-md-3">
                <label class="form-label">Filtro Aceite CC</label>
                <input type="text" name="filtro_aceite_cc"
                      value="{{ old('filtro_aceite_cc', $vehiculo->filtro_aceite_cc) }}"
                      class="form-control" maxlength="45" placeholder="Ej: TF087">
            </div>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <label class="form-label">Filtro de Enfriador</label>
                <input type="text" name="filtro_de_enfriador"
                      value="{{ old('filtro_de_enfriador', $vehiculo->filtro_de_enfriador) }}"
                      class="form-control" maxlength="45" placeholder="Ej: CF123">
            </div>
            <div class="col-md-6">
                <label class="form-label">Tipo Caja</label>
                <input type="text" name="tipo_caja"
                      value="{{ old('tipo_caja', $vehiculo->tipo_caja) }}"
                      class="form-control" maxlength="45" placeholder="Ej: Automática 6 velocidades">
            </div>
        </div>

      {{-- SECCIÓN: DIFERENCIAL --}}
      <div class="section-title">
          <i class="bi bi-gear-fill me-2"></i>Diferencial
      </div>

      <div class="row g-3 justify-content-center">
          <div class="col-md-4">
              <label class="form-label">Cantidad Aceite Diferencial</label>
              <input type="text" name="cantidad_aceite_diferencial"
                    value="{{ old('cantidad_aceite_diferencial', $vehiculo->cantidad_aceite_diferencial) }}"
                    class="form-control" maxlength="45" placeholder="Ej: 1.2 litros">
          </div>
          <div class="col-md-4">
              <label class="form-label">Marca Aceite D</label>
              <input type="text" name="marca_aceite_d"
                    value="{{ old('marca_aceite_d', $vehiculo->marca_aceite_d) }}"
                    class="form-control" maxlength="45" placeholder="Ej: Shell">
          </div>
          <div class="col-md-4">
              <label class="form-label">Tipo Aceite D</label>
              <input type="text" name="tipo_aceite_d"
                    value="{{ old('tipo_aceite_d', $vehiculo->tipo_aceite_d) }}"
                    class="form-control" maxlength="45" placeholder="Ej: 80W-90">
          </div>
      </div>

      {{-- SECCIÓN: TRANSFER --}}
      <div class="section-title">
        <i class="bi bi-gear-wide me-2"></i>Transfer
      </div>

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Cantidad Aceite Transfer</label>
          <input type="text" name="cantidad_aceite_transfer"
                 value="{{ old('cantidad_aceite_transfer', $vehiculo->cantidad_aceite_transfer) }}"
                 class="form-control" maxlength="45" placeholder="Ej: 1.5 litros">
        </div>
        <div class="col-md-4">
          <label class="form-label">Marca Aceite T</label>
          <input type="text" name="marca_aceite_t"
                 value="{{ old('marca_aceite_t', $vehiculo->marca_aceite_t) }}"
                 class="form-control" maxlength="45" placeholder="Ej: Valvoline">
        </div>
        <div class="col-md-4">
          <label class="form-label">Tipo Aceite T</label>
          <input type="text" name="tipo_aceite_t"
                 value="{{ old('tipo_aceite_t', $vehiculo->tipo_aceite_t) }}"
                 class="form-control" maxlength="45" placeholder="Ej: 75W-140">
        </div>
      </div>

      {{-- SECCIÓN: FILTROS Y COMPONENTES --}}
      <div class="section-title">
        <i class="bi bi-funnel me-2"></i>Filtros y Componentes
      </div>

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Filtro Cabina</label>
          <input type="text" name="filtro_cabina"
                 value="{{ old('filtro_cabina', $vehiculo->filtro_cabina) }}"
                 class="form-control" maxlength="45" placeholder="Ej: CF072">
        </div>
        <div class="col-md-4">
          <label class="form-label">Filtro Diesel</label>
          <input type="text" name="filtro_diesel"
                 value="{{ old('filtro_diesel', $vehiculo->filtro_diesel) }}"
                 class="form-control" maxlength="45" placeholder="Ej: P550935">
        </div>
        <div class="col-md-4">
          <label class="form-label">Contra Filtro Diesel</label>
          <input type="text" name="contra_filtro_diesel"
                 value="{{ old('contra_filtro_diesel', $vehiculo->contra_filtro_diesel) }}"
                 class="form-control" maxlength="45" placeholder="Ej: WK731/6">
        </div>
      </div>

      {{-- SECCIÓN: FRENOS Y REPUESTOS MULTIPLES --}}
      <div class="section-title">
        <i class="bi bi-lightning-charge me-2"></i>Frenos Y Repuestos Multiples
      </div>

      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Candelas</label>
          <input type="text" name="candelas"
                 value="{{ old('candelas', $vehiculo->candelas) }}"
                 class="form-control" maxlength="45" placeholder="Ej: BKR6E-11">
        </div>
        <div class="col-md-3">
          <label class="form-label">Pastillas Delanteras</label>
          <input type="text" name="pastillas_delanteras"
                 value="{{ old('pastillas_delanteras', $vehiculo->pastillas_delanteras) }}"
                 class="form-control" maxlength="45" placeholder="Ej: P06065">
        </div>
        <div class="col-md-3">
          <label class="form-label">Pastillas Traseras</label>
          <input type="text" name="pastillas_traseras"
                 value="{{ old('pastillas_traseras', $vehiculo->pastillas_traseras) }}"
                 class="form-control" maxlength="45" placeholder="Ej: P06070">
        </div>
        <div class="col-md-3">
          <label class="form-label">Fajas</label>
          <input type="text" name="fajas"
                 value="{{ old('fajas', $vehiculo->fajas) }}"
                 class="form-control" maxlength="45" placeholder="Ej: 6PK2280">
        </div>
      </div>

      <div class="row g-3 mt-2">
        <div class="col-md-6">
          <label class="form-label">Aceite Hidráulico</label>
          <input type="text" name="aceite_hidraulico"
                 value="{{ old('aceite_hidraulico', $vehiculo->aceite_hidraulico) }}"
                 class="form-control" maxlength="45" placeholder="Ej: DOT 4">
        </div>
      </div>

      {{-- BOTONES DE ACCIÓN --}}
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-md-primary px-4 text-white">
          <i class="bi bi-check-lg me-1"></i>Actualizar Vehículo
        </button>
        <a href="{{ route('vehiculos.index') }}" class="btn btn-md-secondary px-4">
          <i class="bi bi-x-circle me-1"></i>Cancelar
        </a>
      </div>
    </form>
  </div>
</div>

<script>
// Prevenir caracteres no deseados en campos numéricos
document.addEventListener('DOMContentLoaded', function() {
  const numberInputs = document.querySelectorAll('input[type="number"]');
  numberInputs.forEach(input => {
    input.addEventListener('keydown', function(e) {
      // Prevenir 'e', 'E', '+', '-' en campos numéricos
      if (e.key === 'e' || e.key === 'E' || e.key === '+' || e.key === '-') {
        e.preventDefault();
      }
    });
  });
});
</script>
@endsection