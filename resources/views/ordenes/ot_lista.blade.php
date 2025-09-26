@extends('layouts.app')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width:576px){ .page-body { min-height:calc(100vh - 64px); } }
</style>
@endpush

@section('content')
<div class="container" style="padding:20px;">
  <br><br>

  <h1 style="color:#C24242;">Órdenes de Trabajo</h1>

  @if(session('success'))
    <div style="background:#D4EDDA; color:#155724; padding:10px; border-radius:8px; margin:10px 0;">
      {{ session('success') }}
    </div>
  @endif

  <div style="margin:15px 0;">
    <a href="{{ route('ordenes.create') }}"
       style="background:#9F3B3B; color:#fff; padding:8px 15px; border-radius:6px; text-decoration:none;">
      + Nueva Orden
    </a>
  </div>

  <table style="width:100%; border-collapse:collapse; margin-top:20px;">
    <thead style="background:#1E1E1E; color:white;">
      <tr>
        <th style="padding:10px; text-align:left;">ID</th>
        <th style="padding:10px; text-align:left;">Fecha</th>
        <th style="padding:10px; text-align:left;">Placa</th>
        <th style="padding:10px; text-align:left;">Tipo de Servicio</th>
        <th style="padding:10px; text-align:left;">Kilometraje</th>
        <th style="padding:10px; text-align:left;">Próx. Servicio</th>
        <th style="padding:10px; text-align:left;">Estado</th>
        <th style="padding:10px; text-align:center;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($ordenes as $ot)
        <tr style="background:#F4EFEE; border-bottom:1px solid #000;">
          <td style="padding:10px; border-bottom:1px solid #000;">{{ $ot->id }}</td>
          <td style="padding:10px; border-bottom:1px solid #000;">
            {{ optional($ot->fecha_creacion)->format('d/m/Y H:i') }}
          </td>
          <td style="padding:10px; border-bottom:1px solid #000;">
            {{ $ot->vehiculo->placa ?? '—' }}
          </td>
          <td style="padding:10px; border-bottom:1px solid #000;">
            {{ $ot->tipoServicio->descripcion ?? '—' }}
          </td>
          <td style="padding:10px; border-bottom:1px solid #000;">{{ $ot->kilometraje }}</td>
          <td style="padding:10px; border-bottom:1px solid #000;">{{ $ot->proximo_servicio }}</td>
          <td style="padding:10px; border-bottom:1px solid #000;">
            {{ $ot->estadoActual?->estado?->nombre ?? '—' }}
          </td>
          <td style="padding:10px; text-align:center; border-bottom:1px solid #000;">
            <div style="display:flex; gap:8px; justify-content:center;">
              <a href="{{ route('ordenes.edit', $ot->id) }}"
                 style="background:#C24242; color:#fff; padding:6px 12px; border-radius:5px; text-decoration:none;">
                <i class="bi bi-pencil-square"></i> Editar
              </a>
              <form action="{{ route('ordenes.destroy', $ot->id) }}" method="POST"
                    onsubmit="return confirm('¿Eliminar la orden #{{ $ot->id }}?')">
                @csrf @method('DELETE')
                <button type="submit"
                        style="background:#9F3B3B; color:#fff; padding:6px 12px; border:none; border-radius:5px; cursor:pointer;">
                  <i class="bi bi-trash"></i> Eliminar
                </button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8" style="padding:15px; text-align:center;">No hay órdenes registradas.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
