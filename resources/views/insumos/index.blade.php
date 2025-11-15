@extends('layouts.app')

@push('styles')
    <style>
        html, body { height:100%; background:#f0f0f0 !important; }
        .page-body { min-height:calc(100vh - 72px); background:rgba(255, 255, 255, 0.144) !important; color:#212529; }
        @media (max-width:576px){ .page-body { min-height:calc(100vh - 64px); } }

        .btn-theme{ background:#9F3B3B; border-color:#9F3B3B; color:#fff; }
        .btn-theme:hover{ background:#873131; border-color:#873131; color:#fff; }

        .pagination .page-link{ color:#1d1d1d; border-color:#e9ecef; }
        .pagination .page-link:hover{ color:#1d1d1d; background:#f8f9fa; border-color:#e9ecef; }
        .pagination .page-item.active .page-link{ background:#535353; border-color:#1d1d1d;  color:#fff; }
        .pagination .page-item.disabled .page-link{ color:#adb5bd; background:#f8f9fa; border-color:#e9ecef; }
        .pagination .page-link:focus{ box-shadow:0 0 0 .15rem rgba(159,59,59,.15); }
    </style>
@endpush

@section('content')
    <div class="container py-4">
        <div class="container"><br><br>
            <h1 class="text-center mb-4" style="color:#C24242;">Gestión de Insumos</h1>
        </div>

        {{-- Toolbar --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <a href="{{ route('insumos.create') }}" class="btn btn-theme" style="border-radius:12px; padding:.55rem 1rem;">
                <i class="bi bi-plus-lg me-1"></i> Registrar Insumo
            </a>

            <form action="{{ route('insumos.index') }}" method="GET" class="d-flex align-items-center gap-2">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Buscar por nombre…" value="{{ request('q') }}">
                </div>
                <button class="btn btn-dark" type="submit" style="border-radius:12px;">Buscar</button>
            </form>
        </div>

        @if(session('success'))
            <div class="alert alert-success shadow-sm rounded-3">{{ session('success') }}</div>
        @endif

        {{-- Eliminación múltiple --}}
        <form id="bulkDeleteForm" action="{{ route('insumos.destroyMultiple') }}" method="POST">
            @csrf
            @method('DELETE')

            <div class="table-responsive shadow-sm rounded-3">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                    <tr>
                        <th class="text-center" style="width:42px">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Costo</th>
                        <th>Stock</th>
                        <th>Stock Mínimo</th>
                        <th>Descripción</th>
                        <th>Tipo Insumo</th>
                        <th>Precio</th>
                        <th class="text-center" style="width:130px">Acciones</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($insumos as $i)
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" name="ids[]" value="{{ $i->id }}" class="row-checkbox">
                            </td>
                            <td class="fw-semibold">{{ $i->id }}</td>
                            <td>{{ $i->nombre }}</td>
                            <td>{{ number_format($i->costo, 2) }}</td>
                            <td>{{ $i->stock }}</td>
                            <td>{{ $i->stock_minimo }}</td>
                            <td>{{ $i->descripcion }}</td>
                            <td>{{ $i->tipoInsumo->nombre ?? '—' }}</td>
                            <td>{{ number_format($i->precio, 2) }}</td>

                            <td class="text-center">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('insumos.edit', $i->id) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    @role('admin')
                                    <form action="{{ route('insumos.destroy', $i->id) }}"
                                          method="POST"
                                          class="form-delete-single">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endrole

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">No hay insumos registrados.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Botón eliminar seleccionados --}}
            <div class="d-flex justify-content-end mt-3">
                <button id="deleteMultipleBtn" type="button" class="btn btn-danger d-none">
                    <i class="bi bi-trash me-1"></i> Eliminar seleccionados
                </button>
            </div>

        </form>

        <div class="mt-3">
            {{ $insumos->links() }}
        </div>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


        {{-- SweetAlert2 --}}
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        {{-- Selección múltiple --}}
        <script>
            const selectAll = document.getElementById('selectAll');
            const deleteBtn = document.getElementById('deleteMultipleBtn');
            const formBulk  = document.getElementById('bulkDeleteForm');

            function toggleBulkBtn(){
                const any = document.querySelectorAll('.row-checkbox:checked').length > 0;
                deleteBtn.classList.toggle('d-none', !any);
            }

            document.addEventListener('change', e=>{
                if(e.target.id === 'selectAll'){
                    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = e.target.checked);
                    toggleBulkBtn();
                }
                if(e.target.classList.contains('row-checkbox')) toggleBulkBtn();
            });

            deleteBtn.addEventListener('click', ()=>{
                Swal.fire({
                    title: '¿Eliminar los insumos seleccionados?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#C24242',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((r)=>{
                    if(r.isConfirmed) formBulk.submit();
                });
            });
        </script>

        {{-- Eliminación individual --}}
        <script>
            document.querySelectorAll('.form-delete-single').forEach(form => {
                form.addEventListener('submit', function(e){
                    e.preventDefault();

                    let nombre = form.closest('tr').querySelector('td:nth-child(3)').innerText;

                    Swal.fire({
                        title: '¿Eliminar este insumo?',
                        html: `<b>${nombre}</b>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#C24242',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((r)=>{
                        if(r.isConfirmed) form.submit();
                    });
                });
            });
        </script>

@endsection
