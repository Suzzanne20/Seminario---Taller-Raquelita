@extends('layouts.app')

@push('styles')
<style>
<<<<<<< HEAD
  html, body { height: 100%; background: #f0f0f0 !important; }
  .page-body { min-height: calc(100vh - 72px); background: #f0f0f0 !important; color: #212529; }
  @media (max-width: 576px) {
    .page-body { min-height: calc(100vh - 64px); }
  }
=======
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width:576px){ .page-body { min-height:calc(100vh - 64px); } }
>>>>>>> 84d704f517b0af64c0f8e9bf76d0897bd1bf3f96
</style>
@endpush

@section('content')
    <div class="container"><br><br>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold" style="color:#1E1E1E;">Cotizaciones</h3>
            <a href="{{ route('cotizaciones.create') }}"
               class="btn"
               style="background-color:#C24242; color:#fff; border-radius:20px; padding:8px 18px;">
                Nueva cotización
            </a>
        </div>

        @if(session('ok'))
            <div class="alert alert-success shadow-sm" style="border-radius:12px;">
                {{ session('ok') }}
            </div>
        @endif

        <div class="table-responsive shadow-sm rounded">
            <table class="table align-middle text-center">
                <thead style="background-color:#9F3B3B; color:#fff;">
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Servicio</th>
                    <th>Total (Q)</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse($cotizaciones as $c)
                    <tr>
                        <td>{{ $c->id }}</td>
                        <td>
                            {{ $c->fecha_creacion
                                ? \Carbon\Carbon::parse($c->fecha_creacion)->format('Y-m-d H:i')
                                : '' }}
                        </td>
                        <td>{{ $c->descripcion }}</td>
                        <td>{{ $c->servicio?->descripcion }}</td>
                        <td><strong>Q{{ number_format($c->total, 2) }}</strong></td>
                        <td class="text-end">
                            <a href="{{ route('cotizaciones.show',$c->id) }}"
                               class="btn btn-sm"
                               style="background-color:#1E1E1E; color:#fff; border-radius:10px; margin-right:4px;">
                                Ver
                            </a>
                            <a href="{{ route('cotizaciones.edit',$c->id) }}"
                               class="btn btn-sm"
                               style="background-color:#B5747D; color:#fff; border-radius:10px; margin-right:4px;">
                                Editar
                            </a>
                            <form action="{{ route('cotizaciones.destroy',$c->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('¿Eliminar cotización #{{ $c->id }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm"
                                        style="background-color:#C24242; color:#fff; border-radius:10px;">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Sin cotizaciones registradas</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $cotizaciones->links() }}
        </div>
    </div>
@endsection
