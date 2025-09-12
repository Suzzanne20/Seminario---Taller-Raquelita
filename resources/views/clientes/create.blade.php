@extends('layouts.app')

@section('content')
    <div class="container" style="padding:20px;">
        <h1 style="color:#C24242;">Registrar Cliente</h1>

        <!-- Mostrar errores de validación -->
        @if ($errors->any())
            <div style="background:#F8D7DA; color:#721C24; padding:10px; border-radius:8px; margin-bottom:15px;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>⚠️ {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulario -->
        <div style="background:#F4EFEE; padding:20px; border-radius:12px; max-width:500px;">
            <form action="{{ route('clientes.store') }}" method="POST">
                @csrf

                <div style="margin-bottom:15px;">
                    <label for="nombre" style="font-weight:bold;">Nombre:</label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}"
                           style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;" required>
                </div>

                <div style="margin-bottom:15px;">
                    <label for="nit" style="font-weight:bold;">NIT:</label>
                    <input type="text" name="nit" id="nit" value="{{ old('nit') }}"
                           style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
                </div>

                <div style="margin-bottom:15px;">
                    <label for="telefono" style="font-weight:bold;">Teléfono:</label>
                    <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}"
                           style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;" required>
                </div>

                <div style="margin-bottom:15px;">
                    <label for="direccion" style="font-weight:bold;">Dirección:</label>
                    <input type="text" name="direccion" id="direccion" value="{{ old('direccion') }}"
                           style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
                </div>

                <button type="submit"
                        style="background:#9F3B3B; color:white; padding:10px 15px; border:none; border-radius:6px; cursor:pointer;">
                    Guardar
                </button>

                <a href="{{ route('clientes.index') }}"
                   style="margin-left:10px; color:#1E1E1E; text-decoration:none;">
                    Cancelar
                </a>
            </form>
        </div>
    </div>
@endsection
