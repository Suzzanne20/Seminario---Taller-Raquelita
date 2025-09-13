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

@section('content')
    <div class="container" style="padding:20px;"><br><br>
        <h1 style="color:#C24242;">Editar Cliente</h1>

        @if ($errors->any())
            <div style="background:#F8D7DA; color:#721C24; padding:10px; border-radius:8px; margin-bottom:15px;">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>⚠️ {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div style="background:#F4EFEE; padding:20px; border-radius:12px; max-width:500px;">
            <form action="{{ route('clientes.update', $cliente->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div style="margin-bottom:15px;">
                    <label for="nombre" style="font-weight:bold;">Nombre:</label>
                    <input type="text" id="nombre" name="nombre"
                           value="{{ old('nombre', $cliente->nombre) }}"
                           style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;" required>
                </div>

                <div style="margin-bottom:15px;">
                    <label for="nit" style="font-weight:bold;">NIT:</label>
                    <input type="text" id="nit" name="nit"
                           value="{{ old('nit', $cliente->nit) }}"
                           style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
                </div>

                <div style="margin-bottom:15px;">
                    <label for="telefono" style="font-weight:bold;">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono"
                           value="{{ old('telefono', $cliente->telefono) }}"
                           style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;" required>
                </div>

                <div style="margin-bottom:15px;">
                    <label for="direccion" style="font-weight:bold;">Dirección:</label>
                    <input type="text" id="direccion" name="direccion"
                           value="{{ old('direccion', $cliente->direccion) }}"
                           style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
                </div>

                <button type="submit"
                        style="background:#9F3B3B; color:white; padding:10px 15px; border:none; border-radius:6px; cursor:pointer;">
                    Actualizar
                </button>

                <a href="{{ route('clientes.index') }}"
                   style="margin-left:10px; color:#1E1E1E; text-decoration:none;">
                    Cancelar
                </a>
            </form>
        </div>
    </div>
@endsection
