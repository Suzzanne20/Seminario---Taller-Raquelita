@extends('layouts.app')

@push('styles')
    <style>
        html, body { height: 100%; background: #f0f0f0 !important; }
        .page-body { min-height: calc(100vh - 72px); background: #f0f0f0 !important; color: #212529; }
        @media (max-width: 576px) {
            .page-body { min-height: calc(100vh - 64px); }
        }
        /* Estilos adicionales para los botones */
        .btn-action {
            background-color: #C24242;
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-action:hover {
            background-color: #9F3B3B;
        }
        .btn-delete {
            background-color: #9F3B3B;
        }
        .btn-delete:hover {
            background-color: #8C3030;
        }
    </style>
@endpush

@section('content')
    <div class="container" style="padding:20px;"> <br><br>

        <h1 style="color:#C24242;">Gestión de Tipos de Insumo</h1>

        <!-- Mensaje de éxito -->
        @if(session('success'))
            <div style="background:#D4EDDA; color:#155724; padding:10px; border-radius:8px; margin:10px 0;">
                {{ session('success') }}
            </div>
        @endif

        <!-- Botón para agregar nuevo registro -->
        <div style="margin:15px 0;">
            <a href="{{ route('tipo-insumos.create') }}" class="btn btn-primary">
                + Agregar nuevo registro
            </a>
        </div>

        <!-- Tabla de tipos de insumo -->
        <table style="width:100%; border-collapse:collapse; margin-top:20px;">
            <thead style="background:#1E1E1E; color:white;">
            <tr>
                <th style="padding:10px; text-align:left;">ID</th>
                <th style="padding:10px; text-align:left;">Nombre</th>
                <th style="padding:10px; text-align:center;">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @forelse($tiposInsumo as $type)
                <tr style="background:#F4EFEE; border-bottom:1px solid #000;">
                    <td style="padding:10px; border-bottom:1px solid #000;">{{ $type->id }}</td>
                    <td style="padding:10px; border-bottom:1px solid #000;">{{ $type->nombre }}</td>
                    <td style="padding:10px; text-align:center; border-bottom:1px solid #000;">
                        <div style="display:flex; gap:8px; justify-content:center;">
                            <a href="{{ route('tipo-insumos.edit', $type->id) }}" class="btn-action">
                                <i class="bi bi-pencil-square"></i>
                                Editar
                            </a>
                            <form action="{{ route('tipo-insumos.destroy', $type->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete">
                                    <i class="bi bi-trash"></i>
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="padding:15px; text-align:center;">No hay tipos de insumos registrados.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection
