@extends('layouts.app')

@section('title','Usuarios')

@php
    $roles = $roles ?? \Spatie\Permission\Models\Role::orderBy('name')->get();
@endphp

@push('styles')
    <style>
        :root{
            --brand-primary:#B23940; --brand-accent:#D44A52;
            --brand-bg:#F3EEEE; --brand-muted:#B06B74; --brand-dark:#000;
        }

        .container-narrow{ max-width: 1100px; margin: 32px auto 0; }

        .page-title{
            font-weight: 800; color:#2b2b2b; letter-spacing:.2px;
        }

        .control-38{ height: 38px; border-radius: 10px; }

        .form-control.control-38,
        .form-select.control-38{
            border-color:#e6c0c4;
        }
        .form-control.control-38:focus,
        .form-select.control-38:focus{
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 .18rem rgba(178,57,64,.15);
        }

        .btn-filter{
            background: linear-gradient(180deg,var(--brand-primary),var(--brand-accent));
            color:#fff; border:0;
        }

        .btn-brand{
            background: linear-gradient(180deg,var(--brand-primary),var(--brand-accent));
            color:#fff; border:0;
        }

        .table-compact th, .table-compact td{ padding:.5rem .65rem; }
        .table-compact{ font-size:.95rem; }
        .card-hero{ border-top:4px solid var(--brand-primary); }

        @media (max-width: 768px){
            .filter-bar{ width:100%; justify-content:flex-end; flex-wrap:wrap; margin-top: 20px;}
            .filter-bar form{ width:100%; justify-content:flex-end; flex-wrap:wrap; }
            .filter-bar .form-control{ min-width: 240px; }
        }
    </style>
@endpush

@section('content')
    <div class="container-narrow my-5">

        @if(session('ok'))  <div class="alert alert-success">{{ session('ok') }}</div> @endif
        @if(session('err')) <div class="alert alert-danger">{{ session('err') }}</div> @endif


        <div class="d-flex align-items-center justify-content-between mt-5 mb-3 flex-wrap gap-2">

            <h2 class="page-title m-0">Usuarios</h2>

            <div class="filter-bar d-flex align-items-center gap-2">
                <form id="filterForm" method="GET" class="d-flex align-items-center gap-2 mt-3">
                    <input name="q" value="{{ request('q') }}"
                           class="form-control control-38"
                           placeholder="Buscador por nombre o correo">

                    <select name="role" class="form-select control-38">
                        <option value="">Filtrar por rol</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" @selected(request('role')===$role->name)>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-filter control-38 px-3">Filtrar</button>

                    <button class="btn btn-brand control-38 px-3"
                            data-bs-toggle="modal" data-bs-target="#modalCreate">
                        <i class="bi bi-person-plus me-1"></i>
                    </button>
                </form>


            </div>
        </div>

        <div class="card card-hero shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm table-compact align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($users as $u)
                            <tr>
                                <td>{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>
                                    @foreach($u->roles as $r)
                                        <span class="badge role-badge me-1">
                                            <i class="bi bi-shield-lock me-1"></i>{{ $r->name }}
                                        </span>
                                    @endforeach
                                    @if($u->roles->isEmpty())
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button
                                        class="btn btn-sm btn-outline-secondary me-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEdit"
                                        data-id="{{ $u->id }}"
                                        data-name="{{ $u->name }}"
                                        data-email="{{ $u->email }}"
                                        data-role="{{ optional($u->roles->first())->name }}"
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <form action="{{ route('users.destroy',$u) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar a {{ $u->name }}?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Aún no hay usuarios.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(method_exists($users,'links'))
                <div class="card-footer bg-white">{{ $users->links() }}</div>
            @endif
        </div>
    </div>

    {{-- CREAR --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-end">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Nuevo usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="{{ route('users.store') }}" method="POST" class="needs-validation" novalidate>
                    @csrf

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <div class="fw-bold mb-1">Corrige los siguientes campos:</div>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-info py-2">
                        <small>
                            Requisitos de contraseña: mínimo 8 caracteres, incluir mayúsculas, minúsculas,
                            números y símbolos. No usar contraseñas comprometidas.
                        </small>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control"
                                       autocomplete="new-password"
                                       placeholder="Contraseña"
                                       title="Mínimo 8, con mayúsculas, minúsculas, números y símbolos" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmación</label>
                                <input type="password" name="password_confirmation" class="form-control"
                                       autocomplete="new-password"
                                       placeholder="Confirmar contraseña" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rol</label>
                                <select name="role" class="form-select" required>
                                    <option value="" disabled selected>Selecciona un rol…</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" @selected(request('role')===$role->name)>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger mt-3">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-brand" type="submit">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- EDITAR --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-end">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="editForm" method="POST" class="needs-validation" novalidate>
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input id="editName" type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo</label>
                                <input id="editEmail" type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nueva contraseña (opcional)</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmación</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rol</label>
                                <select id="editRole" name="role" class="form-select" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-text mt-2">Si no cambias la contraseña, deja ambos campos vacíos.</div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-brand" type="submit">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editModal = document.getElementById('modalEdit');
            if (!editModal) return;

            editModal.addEventListener('show.bs.modal', event => {
                const btn  = event.relatedTarget;
                const id   = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                const email= btn.getAttribute('data-email');
                const role = btn.getAttribute('data-role') || '';

                const form = document.getElementById('editForm');
                form.action = "{{ route('users.update', ':id') }}".replace(':id', id);

                document.getElementById('editName').value  = name;
                document.getElementById('editEmail').value = email;

                const roleSelect = document.getElementById('editRole');
                [...roleSelect.options].forEach(o => o.selected = (o.value === role));
            });
        });
    </script>
@endpush
