@extends('layouts.app')

@section('content')
    <div class="container" style="padding:20px;">
        <h1 style="color:#C24242;">Gestión de Clientes</h1>

        <!-- Mensaje de éxito -->
        @if(session('success'))
            <div style="background:#D4EDDA; color:#155724; padding:10px; border-radius:8px; margin:10px 0;">
                {{ session('success') }}
            </div>
        @endif

        <!-- Botón para registrar -->
        <div style="margin:15px 0;">
            <a href="{{ route('clientes.create') }}"
               style="background:#9F3B3B; color:white; padding:8px 15px; border-radius:6px; text-decoration:none;">
                + Registrar Cliente
            </a>
        </div>

        <!-- Tabla de clientes -->
        <table style="width:100%; border-collapse:collapse; margin-top:20px;">
            <thead style="background:#1E1E1E; color:white;">
            <tr>
                <th style="padding:10px; text-align:left;">ID</th>
                <th style="padding:10px; text-align:left;">Nombre</th>
                <th style="padding:10px; text-align:left;">NIT</th>
                <th style="padding:10px; text-align:left;">Teléfono</th>
                <th style="padding:10px; text-align:left;">Dirección</th>
                <th style="padding:10px; text-align:center;">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @forelse($clientes as $cliente)
                <tr style="background:#F4EFEE; border-bottom:1px solid #ccc;">
                    <td style="padding:10px;">{{ $cliente->id }}</td>
                    <td style="padding:10px;">{{ $cliente->nombre }}</td>
                    <td style="padding:10px;">{{ $cliente->nit }}</td>
                    <td style="padding:10px;">{{ $cliente->telefono }}</td>
                    <td style="padding:10px;">{{ $cliente->direccion }}</td>
                    <td style="padding:10px; text-align:center;">
                        <div style="display:flex; gap:8px; justify-content:center;">
                            <a href="{{ route('clientes.edit', $cliente->id) }}"
                               style="background:#C24242; color:white; padding:6px 12px; border-radius:5px; text-decoration:none;">
                                Editar
                            </a>
                            <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('¿Seguro que deseas eliminar este cliente?')"
                                        style="background:#9F3B3B; color:white; padding:6px 12px; border:none; border-radius:5px; cursor:pointer;">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding:15px; text-align:center;">No hay clientes registrados.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
