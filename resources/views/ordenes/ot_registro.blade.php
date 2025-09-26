@extends('layouts.app')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width:576px){ .page-body { min-height:calc(100vh - 64px); } }

  .card-form { max-width:820px; border:1px solid #e9ecef; border-radius:14px; }
  .btn-theme { background:#9F3B3B; border-color:#9F3B3B; color:#fff; }
  .btn-theme:hover { background:#873131; border-color:#873131; color:#fff; }
  .form-control:focus { border-color:#c24242; box-shadow:0 0 0 .2rem rgba(194,66,66,.15); }
</style>
@endpush

@section('content')
<div class="container py-4">
  <h1 class="text-center mb-4" style="color:#C24242;">Nueva Orden de Trabajo</h1>

  <div class="d-flex justify-content-center">
    <div class="card card-form shadow-sm w-100">
      <div class="card-body p-4">
        <form action="{{ route('ordenes.store') }}" method="POST" novalidate>
          @csrf

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Vehículo (placa)</label>
              <select name="vehiculo_placa" class="form-select" required>
                <option value="">Seleccione…</option>
                @foreach($vehiculos as $v)
                  <option value="{{ $v->placa }}" @selected(old('vehiculo_placa')==$v->placa)>
                    {{ $v->placa }} — {{ $v->linea }} {{ $v->modelo }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Tipo de servicio</label>
              <select name="type_service_id" class="form-select" required>
                <option value="">Seleccione…</option>
                @foreach($servicios as $s)
                  <option value="{{ $s->id }}" @selected(old('type_service_id')==$s->id)>{{ $s->descripcion }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Kilometraje</label>
              <input type="number" name="kilometraje" class="form-control" value="{{ old('kilometraje') }}" required>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Próximo servicio (km)</label>
              <input type="number" name="proximo_servicio" class="form-control" value="{{ old('proximo_servicio') }}">
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Costo mano de obra (Q)</label>
              <input type="number" step="0.01" name="costo_mo" class="form-control" value="{{ old('costo_mo') }}">
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Descripción / Falla</label>
              <textarea name="descripcion" rows="3" class="form-control">{{ old('descripcion') }}</textarea>
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
@endsection
