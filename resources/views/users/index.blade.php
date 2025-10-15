@extends('layouts.app')

@section('title','Usuarios')

@php
  // si no lo recibes desde el controller
  $roles = $roles ?? \Spatie\Permission\Models\Role::orderBy('name')->get();
@endphp

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width:576px){ .page-body{ min-height:calc(100vh - 64px);} }

  :root{
    --brand:#9F3B3B;             /* rojo marca */
    --ink:#1f2937;               /* gris oscuro texto */
  }

  /* título grande centrado (igual a órdenes) */
  .page-header{
    text-align:center; margin:64px 0 20px;
  }
  .page-header h1{
    font-weight:800; color:#C24242; letter-spacing:.2px;
  }

  /* toolbar */
  .toolbar{
    max-width:1150px; margin:0 auto 14px; display:flex; gap:10px;
    align-items:center; justify-content:space-between; flex-wrap:wrap;
  }
  .btn-theme{ background:#9F3B3B; border-color:#9F3B3B; color:#fff; border-radius:12px; padding:.55rem 1rem; }
  .btn-theme:hover{ background:#873131; border-color:#873131; color:#fff; }
  .search-wrap .input-group-text{ background:#fff; }
  .control{ height:40px; border-radius:12px; }

  /* card + tabla */
  .card-shell{ max-width:1150px; margin:0 auto; border-radius:16px; overflow:hidden; }
  .table thead.table-dark th{ background:#1e1e1e; border-color:#1e1e1e; }
  .table td,.table th{ vertical-align:middle; }
  .role-badge{ background:#eef2ff; color:#1f3a8a; border:1px solid #dbeafe; }

  /* acciones redondas */
  .btn-round{
    width:38px; height:38px; border-radius:999px; display:inline-grid; place-items:center;
  }

  /* paginación igual al resto */
  .pagination .page-link{ color:#1d1d1d; border-color:#e9ecef; }
  .pagination .page-link:hover{ color:#1d1d1d; background:#f8f9fa; border-color:#e9ecef; }
  .pagination .page-item.active .page-link{ background:#535353; border-color:#1d1d1d; color:#fff; }
  .pagination .page-item.disabled .page-link{ color:#adb5bd; background:#f8f9fa; border-color:#e9ecef; }
  .pagination .page-link:focus{ box-shadow:0 0 0 .15rem rgba(159,59,59,.15); }
</style>
@endpush

@section('content')
  {{-- Título --}}
  <div class="page-header">
    <h1>Usuarios</h1>
  </div>

  {{-- Toolbar: botón + filtros (como Órdenes) --}}
  <div class="toolbar">
    <a class="btn btn-theme" data-bs-toggle="modal" data-bs-target="#modalCreate">
      <i class="bi bi-person-plus me-1"></i> Nuevo usuario
    </a>

    <form id="filterForm" method="GET" class="d-flex align-items-center gap-2 search-wrap">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input name="q" class="form-control control" placeholder="Buscar por nombre o correo" value="{{ request('q') }}">
      </div>
      <select name="role" class="form-select control" style="min-width:210px">
        <option value="">Filtrar por rol</option>
        @foreach($roles as $role)
          <option value="{{ $role->name }}" @selected(request('role')===$role->name)>{{ ucfirst($role->name) }}</option>
        @endforeach
      </select>
      <button class="btn btn-dark control" type="submit" style="border-radius:12px;">Buscar</button>
    </form>
  </div>

  {{-- Tabla --}}
  <div class="card card-shell shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
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
              @forelse($u->roles as $r)
                <span class="badge role-badge me-1"><i class="bi bi-shield-lock me-1"></i>{{ $r->name }}</span>
              @empty
                <span class="text-muted">—</span>
              @endforelse
            </td>
            <td class="text-end">
              <button
                class="btn btn-outline-primary btn-round me-1"
                data-bs-toggle="modal"
                data-bs-target="#modalEdit"
                data-id="{{ $u->id }}"
                data-name="{{ $u->name }}"
                data-email="{{ $u->email }}"
                data-role="{{ optional($u->roles->first())->name }}">
                <i class="bi bi-pencil-square"></i>
              </button>

              <form action="{{ route('users.destroy',$u) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('¿Eliminar a {{ $u->name }}?');">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-round">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          @empty
            <tr><td colspan="4" class="text-center py-4 text-muted">Aún no hay usuarios.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if(method_exists($users,'links'))
      <div class="card-footer bg-white">
        {{ $users->links() }}
      </div>
    @endif
  </div>

  {{-- ============ MODALES ============ --}}
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
            <div class="alert alert-danger mx-3">
              <div class="fw-bold mb-1">Corrige los siguientes campos:</div>
              <ul class="mb-0">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
              </ul>
            </div>
          @endif

          <div class="modal-body">
            <div class="alert alert-info py-2">
              <small>Requisitos: mínimo 8 caracteres, incluir mayúsculas, minúsculas, números y símbolos.</small>
            </div>

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
                <input type="password" name="password" class="form-control" autocomplete="new-password" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Confirmación</label>
                <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Rol</label>
                <select name="role" class="form-select" required>
                  <option value="" disabled selected>Selecciona un rol…</option>
                  @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          <div class="modal-footer border-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-theme" type="submit">Guardar</button>
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
            <button class="btn btn-theme" type="submit">Actualizar</button>
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
  if(!editModal) return;

  editModal.addEventListener('show.bs.modal', e => {
    const b = e.relatedTarget;
    const id   = b.getAttribute('data-id');
    const name = b.getAttribute('data-name');
    const email= b.getAttribute('data-email');
    const role = b.getAttribute('data-role') || '';

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

