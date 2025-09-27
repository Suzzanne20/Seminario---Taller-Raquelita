@extends('layouts.app')

@push('styles')
<style>
  html, body { height: 100%; background: #f0f0f0 !important; }
  .page-body { min-height: calc(100vh - 72px); background: #f0f0f0 !important; color: #212529; }
  @media (max-width: 576px) {
    .page-body { min-height: calc(100vh - 64px); }
  }
</style>
@endpush
@section('title','Lista de inspecciones')

@section('content')
<style>
  .wrap{max-width:1100px;margin:16px auto;padding:0 16px}
  table{width:100%;border-collapse:collapse;background:#fff}
  th,td{padding:10px;border-bottom:1px solid #eee;text-align:left}
  th{background:#fafafa}
  .actions{display:flex;gap:8px;flex-wrap:wrap}
  .btn{border:1px solid #ddd;background:#fff;padding:6px 10px;border-radius:8px;text-decoration:none}
  .btn.danger{border-color:#e74c3c;color:#e74c3c}
</style>

<div class="wrap">
  @if (session('ok'))
    <div style="background:#e9f7ef;border:1px solid #2ecc71;color:#1e8449;padding:8px 10px;border-radius:8px;margin-bottom:10px;">
      {{ session('ok') }}
    </div>
  @endif
  @if (session('error'))
    <div style="background:#fdecea;border:1px solid #e74c3c;color:#922b21;padding:8px 10px;border-radius:8px;margin-bottom:10px;">
      {{ session('error') }}
    </div>
  @endif

  <div style="display:flex;justify-content:space-between;align-items:center;margin:8px 0 12px">
    <h2 style="margin:0">Inspecciones</h2>
    <a class="btn" href="{{ route('inspecciones.create') }}">➕ Nueva inspección</a>
  </div>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Fecha</th>
        <th>Placa</th>
        <th>Tipo</th>
        <th>Observaciones</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $r)
        <tr>
          <td>{{ $r->id }}</td>
          <td>{{ optional($r->fecha_creacion)->format('Y-m-d H:i') }}</td>
          <td>{{ $r->vehiculo_placa }}</td>
          <td>{{ $r->type_vehiculo_id }}</td>
          <td style="max-width:320px">{{ \Illuminate\Support\Str::limit($r->observaciones, 80) }}</td>
          <td>
            <div class="actions">
              <a class="btn" href="{{ route('inspecciones.show', $r) }}">👁 Ver</a>
              <a class="btn" href="{{ route('inspecciones.edit', $r) }}">✏️ Editar</a>
              <form action="{{ route('inspecciones.destroy', $r) }}" method="POST"
                    onsubmit="return confirm('¿Eliminar la inspección #{{ $r->id }}?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn danger">🗑 Eliminar</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" style="color:#888">No hay inspecciones registradas.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div style="margin-top:12px">
    {{ $items->links() }}
  </div>
</div>
@endsection
