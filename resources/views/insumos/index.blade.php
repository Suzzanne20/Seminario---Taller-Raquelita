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
        .btn-multiple-delete {
            background-color: #D32F2F;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            display: none; /* Se oculta por defecto */
        }
        .btn-multiple-delete:hover {
            background-color: #B71C1C;
        }
    </style>
@endpush

@section('content')
    <div class="container" style="padding:20px;"> <br><br>

        <h1 style="color:#C24242;">Gestión de Insumos</h1>

        <!-- Mensaje de éxito -->
        @if(session('success'))
            <div style="background:#D4EDDA; color:#155724; padding:10px; border-radius:8px; margin:10px 0;">
                {{ session('success') }}
            </div>
        @endif

        <!-- Controles de la tabla -->
        <div style="margin:15px 0; display:flex; justify-content:space-between; align-items:center;">
            <a href="{{ route('insumos.create') }}" class="btn btn-primary" style="background:#C24242; border-color:#C24242;">
                + Registrar Insumo
            </a>
            <button id="deleteMultipleBtn" class="btn btn-multiple-delete">
                Eliminar seleccionados
            </button>
        </div>

        <!-- Formulario para eliminación masiva -->
        <form id="bulkDeleteForm" action="{{ route('insumos.destroyMultiple') }}" method="POST">
            @csrf
            @method('DELETE')
            <table style="width:100%; border-collapse:collapse; margin-top:20px;">
                <thead style="background:#1E1E1E; color:white;">
                <tr>
                    <th style="padding:10px; text-align:center;">
                        <input type="checkbox" id="selectAll">
                    </th>
                    <th style="padding:10px; text-align:left;">ID</th>
                    <th style="padding:10px; text-align:left;">Nombre</th>
                    <th style="padding:10px; text-align:left;">Costo</th>
                    <th style="padding:10px; text-align:left;">Stock</th>
                    <th style="padding:10px; text-align:left;">Stock Mínimo</th>
                    <th style="padding:10px; text-align:left;">Descripción</th>
                    <th style="padding:10px; text-align:left;">Tipo Insumo</th>
                    <th style="padding:10px; text-align:left;">Precio</th>
                    <th style="padding:10px; text-align:center;">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse($insumo as $insumo)
                    <tr style="background:#F4EFEE; border-bottom:1px solid #000;">
                        <td style="padding:10px; text-align:center; border-bottom:1px solid #000;">
                            <input type="checkbox" name="ids[]" value="{{ $insumo->id }}" class="row-checkbox">
                        </td>
                        <td style="padding:10px; border-bottom:1px solid #000;">{{ $insumo->id }}</td>
                        <td style="padding:10px; border-bottom:1px solid #000;">{{ $insumo->nombre }}</td>
                        <td style="padding:10px; border-bottom:1px solid #000;">{{ $insumo->costo }}</td>
                        <td style="padding:10px; border-bottom:1px solid #000;">{{ $insumo->stock }}</td>
                        <td style="padding:10px; border-bottom:1px solid #000;">{{ $insumo->stock_minimo }}</td>
                        <td style="padding:10px; border-bottom:1px solid #000;">{{ $insumo->descripcion }}</td>
                        <td style="padding:10px; border-bottom:1px solid #000;">{{ $insumo->tipoInsumo->nombre ?? 'N/A' }}</td>
                        <td style="padding:10px; border-bottom:1px solid #000;">{{ $insumo->precio }}</td>
                        <td style="padding:10px; text-align:center; border-bottom:1px solid #000;">
                            <div style="display:flex; gap:8px; justify-content:center;">
                                <a href="{{ route('insumos.edit', $insumo->id) }}"
                                   class="btn-action">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('insumos.destroy', $insumo->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este insumo?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="padding:15px; text-align:center;">No hay insumos registrados.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script>
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.row-checkbox');
        const deleteMultipleBtn = document.getElementById('deleteMultipleBtn');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
            toggleDeleteButton();
        });

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', toggleDeleteButton);
        });

        deleteMultipleBtn.addEventListener('click', function() {
            if (confirm('¿Estás seguro de que deseas eliminar los insumos seleccionados?')) {
                bulkDeleteForm.submit();
            }
        });

        function toggleDeleteButton() {
            const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
            if (checkedCount > 0) {
                deleteMultipleBtn.style.display = 'inline-block';
            } else {
                deleteMultipleBtn.style.display = 'none';
            }
        }
    </script>
@endsection
