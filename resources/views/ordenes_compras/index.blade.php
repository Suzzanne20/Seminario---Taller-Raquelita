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

        .estado-select {
            border: none;
            background: transparent;
            font-weight: 600;
            padding: 2px 4px;
            border-radius: 6px;
            cursor: pointer;
        }
        .estado-select:focus { outline: none; box-shadow: none; }
    </style>
@endpush

@section('content')
    <div class="container py-4">
        <div class="container"><br><br>
            <h1 class="text-center mb-4" style="color:#C24242;">Gestión de Órdenes de Compra</h1>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <a href="{{ route('ordenes_compras.create') }}" class="btn btn-theme" style="border-radius:12px; padding:.55rem 1rem;">
                <i class="bi bi-plus-lg me-1"></i> Nueva Orden de Compra
            </a>

            <form action="{{ route('ordenes_compras.index') }}" method="GET" class="d-flex align-items-center gap-2">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Buscar por número de orden o proveedor…" value="{{ request('q') }}">
                </div>
                <select name="estado" class="form-select" style="width:auto;">
                    <option value="">Todos los estados</option>
                    <option value="pendiente" {{ request('estado')=='pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="aprobada" {{ request('estado')=='aprobada' ? 'selected' : '' }}>Aprobada</option>
                    <option value="recibida" {{ request('estado')=='recibida' ? 'selected' : '' }}>Recibida</option>
                    <option value="cancelada" {{ request('estado')=='cancelada' ? 'selected' : '' }}>Cancelada</option>
                    <option value="finalizado" {{ request('estado')=='finalizado' ? 'selected' : '' }}>Finalizado</option>
                </select>
                <button class="btn btn-dark" type="submit" style="border-radius:12px;">Buscar</button>
            </form>
        </div>

        @if(session('success'))
            <div class="alert alert-success shadow-sm rounded-3">{{ session('success') }}</div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning shadow-sm rounded-3">{{ session('warning') }}</div>
        @endif

        <div class="table-responsive shadow-sm rounded-3">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                <tr>
                    <th>N° Orden</th>
                    <th>Fecha Orden</th>
                    <th>Proveedor</th>
                    <th>Estado</th>
                    <th>Total</th>
                    <th>Fecha Entrega</th>
                    <th class="text-center" style="width:200px">Acciones</th>
                </tr>
                </thead>

                <tbody>
                @forelse($ordenes as $orden)
                    <tr>
                        <td class="fw-semibold">{{ $orden->id }}</td>
                        <td>{{ \Carbon\Carbon::parse($orden->fecha_orden)->format('d/m/Y') }}</td>
                        <td>{{ $orden->proveedor->nombre ?? '—' }}</td>

                        <td>
                            @if($orden->estado !== 'finalizado')
                                <select class="estado-select" data-id="{{ $orden->id }}">
                                    <option value="pendiente" {{ $orden->estado=='pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="aprobada"  {{ $orden->estado=='aprobada'  ? 'selected' : '' }}>Aprobada</option>
                                    <option value="recibida"  {{ $orden->estado=='recibida'  ? 'selected' : '' }}>Recibida</option>
                                    <option value="cancelada" {{ $orden->estado=='cancelada' ? 'selected' : '' }}>Cancelada</option>
                                </select>
                            @else
                                <span class="badge px-2 py-1" style="background:#6c757d; color:#fff;">Finalizado</span>
                            @endif
                        </td>

                        <td class="fw-semibold">Q {{ number_format($orden->total, 2) }}</td>
                        <td>{{ $orden->fecha_entrega_esperada ? \Carbon\Carbon::parse($orden->fecha_entrega_esperada)->format('d/m/Y') : '—' }}</td>

                        <td class="text-center">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('ordenes_compras.show', $orden->id) }}" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @if($orden->estado !== 'finalizado')
                                    <a href="{{ route('ordenes_compras.edit', $orden->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <form action="{{ route('ordenes_compras.finalizar',$orden->id) }}" method="POST" class="form-finalizar-oc">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('ordenes_compras.destroy',$orden->id) }}" method="POST" class="form-delete-oc">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4">No hay órdenes de compra registradas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $ordenes->links() }}</div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function(){

            function colorEstado(select){
                const c = {
                    pendiente: {bg:'#ffc107', color:'#000'},
                    aprobada:  {bg:'#17a2b8', color:'#fff'},
                    recibida:  {bg:'#28a745', color:'#fff'},
                    cancelada: {bg:'#dc3545', color:'#fff'},
                    finalizado:{bg:'#6c757d', color:'#fff'}
                }[select.value] ?? {bg:'#fff', color:'#000'};

                select.style.background = c.bg;
                select.style.color = c.color;
                select.style.fontWeight = '600';
            }

            document.querySelectorAll('.estado-select').forEach(s=>colorEstado(s));

            document.addEventListener('change', function(e){
                if(e.target.classList.contains('estado-select')){
                    colorEstado(e.target);
                    const id = e.target.dataset.id;
                    const estado = e.target.value;

                    fetch(`/ordenes_compras/${id}/estado`, {
                        method:'PATCH',
                        headers:{
                            'Content-Type':'application/json',
                            'X-CSRF-TOKEN':'{{ csrf_token() }}'
                        },
                        body:JSON.stringify({estado})
                    });
                }
            });

            document.querySelectorAll('.form-delete-oc').forEach(form=>{
                form.addEventListener('submit', function(e){
                    e.preventDefault();
                    Swal.fire({
                        title:'¿Eliminar esta orden?',
                        icon:'warning',
                        showCancelButton:true,
                        confirmButtonColor:'#C24242',
                        cancelButtonColor:'#6c757d',
                        confirmButtonText:'Eliminar',
                        cancelButtonText:'Cancelar'
                    }).then(r=>{
                        if(r.isConfirmed) form.submit();
                    });
                });
            });

            document.querySelectorAll('.form-finalizar-oc').forEach(form=>{
                form.addEventListener('submit', function(e){
                    e.preventDefault();
                    Swal.fire({
                        title:'¿Finalizar esta orden?',
                        icon:'question',
                        showCancelButton:true,
                        confirmButtonColor:'#28a745',
                        cancelButtonColor:'#6c757d',
                        confirmButtonText:'Finalizar',
                        cancelButtonText:'Cancelar'
                    }).then(r=>{
                        if(r.isConfirmed) form.submit();
                    });
                });
            });

        });
    </script>

@endsection
