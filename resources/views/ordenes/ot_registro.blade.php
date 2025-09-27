@extends('layouts.app')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width:576px){ .page-body { min-height:calc(100vh - 64px); } }

  .card-form { max-width:820px; border:1px solid #e9ecef; border-radius:14px; }
  .btn-theme { background:#9F3B3B; border-color:#9F3B3B; color:#fff; }
  .btn-theme:hover { background:#873131; border-color:#873131; color:#fff; }
  .form-control:focus, .form-select:focus { border-color:#c24242; box-shadow:0 0 0 .2rem rgba(194,66,66,.15); }
  .is-invalid { border-color:#dc3545 !important; }
  .invalid-feedback{ display:block; }
</style>
@endpush

@section('content')
<div class="container py-4">
  <h1 class="text-center mb-4" style="color:#C24242;">Nueva Orden de Trabajo</h1>

  <div class="d-flex justify-content-center">
    <div class="card card-form shadow-sm w-100">
      <div class="card-body p-4">

        {{-- Errores globales --}}
        @if ($errors->any())
          <div class="alert alert-danger">
            <strong>Por favor corrige los errores:</strong>
            <ul class="mb-0">
              @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form action="{{ route('ordenes.store') }}" method="POST" novalidate>
          @csrf

          <div class="row g-3">
            {{-- Cotización aprobada (opcional) --}}
            <div class="col-12">
              <label class="form-label fw-semibold">Crear desde cotización aprobada (opcional)</label>
              <select name="cotizacion_id" id="cotizacion_id"
                      class="form-select @error('cotizacion_id') is-invalid @enderror">
                <option value="">— Sin cotización —</option>
                @foreach($cotizaciones as $c)
                  <option value="{{ $c->id }}" @selected(old('cotizacion_id') == $c->id)>
                    #{{ $c->id }} — {{ $c->servicio->descripcion ?? 'Servicio' }} — Total Q{{ number_format($c->total,2) }}
                  </option>
                @endforeach
              </select>
              @error('cotizacion_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              <small class="text-muted">Si eliges una cotización, la placa deja de ser obligatoria.</small>
            </div>

            {{-- Vehículo --}}
            <div class="col-md-6">
              <label class="form-label fw-semibold">Vehículo (placa)</label>
              <select name="vehiculo_placa" id="vehiculo_placa"
                      class="form-select @error('vehiculo_placa') is-invalid @enderror">
                <option value="">Seleccione…</option>
                @foreach($vehiculos as $v)
                  <option value="{{ $v->placa }}" @selected(old('vehiculo_placa')==$v->placa)>
                    {{ $v->placa }} — {{ $v->linea }} {{ $v->modelo }}
                  </option>
                @endforeach
              </select>
              @error('vehiculo_placa') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Tipo de servicio --}}
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tipo de servicio</label>
              <select name="type_service_id"
                      class="form-select @error('type_service_id') is-invalid @enderror" required>
                <option value="">Seleccione…</option>
                @foreach($servicios as $s)
                  <option value="{{ $s->id }}" @selected(old('type_service_id')==$s->id)>
                    {{ $s->descripcion }}
                  </option>
                @endforeach
              </select>
              @error('type_service_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Kilometraje</label>
              <input type="number" name="kilometraje" min="0"
                     class="form-control @error('kilometraje') is-invalid @enderror"
                     value="{{ old('kilometraje') }}">
              @error('kilometraje') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Próximo servicio (km)</label>
              <input type="number" name="proximo_servicio" min="0"
                     class="form-control @error('proximo_servicio') is-invalid @enderror"
                     value="{{ old('proximo_servicio') }}">
              @error('proximo_servicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Costo mano de obra (Q)</label>
              <input type="number" step="0.01" min="0" name="costo_mo"
                     class="form-control @error('costo_mo') is-invalid @enderror"
                     value="{{ old('costo_mo') }}">
              @error('costo_mo') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Descripción / Falla</label>
              <textarea name="descripcion" rows="3"
                        class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion') }}</textarea>
              @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button class="btn btn-theme"> <i class="bi bi-floppy"></i> Guardar</button>
            <a class="btn btn-outline-secondary" href="{{ route('ordenes.index') }}">Cancelar</a>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

{{-- JS: placa requerida solo si NO hay cotización --}}
@push('scripts')
<script>
  (function () {
    const cot = document.getElementById('cotizacion_id');
    const placa = document.getElementById('vehiculo_placa');
    function toggleRequired() {
      const hasCot = cot && cot.value !== '';
      if (placa) {
        placa.required = !hasCot;
      }
    }
    if (cot) {
      cot.addEventListener('change', toggleRequired);
      toggleRequired(); // al cargar con old()
    }
  })();
</script>
@endpush
@endsection

