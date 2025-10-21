@extends('layouts.app')

@push('styles')
    <style>
        html, body { height:100%; background:#f0f0f0 !important; }
        .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
        @media (max-width:576px){ .page-body{ min-height:calc(100vh - 64px);} }

        .md-card{
            max-width: 720px;
            margin: 32px auto 64px;
            background:#fff;
            border-radius:12px;
            box-shadow:0 10px 30px rgba(0,0,0,.08);
            padding:28px;
        }
        .md-title{
            font-weight:700; color:#C24242; text-align:center; margin-bottom:18px;
        }

        .form-control{
            border:none; border-bottom:2px solid #e6e6e6;
            border-radius:0; background:transparent; padding-left:0;
        }
        .form-select{
            border:none; border-bottom:2px solid #e6e6e6; border-radius:0; background:transparent;
            padding-left:0;
        }
        .form-control:focus, .form-select:focus{
            box-shadow:none; border-color:#3f51b5;
        }
        .form-label{ font-size:.9rem; color:#6b7280; }
        .help{ font-size:.8rem; color:#9CA3AF; }

        .btn-theme{ background:#9F3B3B; border:none; color:#fff; }
        .btn-theme:hover{ background:#873131; color:#fff; }
        .btn-muted{ background:#e5e7eb; color:#111827; border:none; }
    </style>
@endpush

@section('content')
    <div class="container">
        <div class="md-card">
            <h2 class="md-title">Editar Proveedor: {{ $proveedor->nombre }}</h2>

            {{-- Errores de validación --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('proveedores.update', $proveedor->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre del Proveedor <span class="text-danger">*</span></label>
                    <input
                        id="nombre"
                        name="nombre"
                        type="text"
                        class="form-control @error('nombre') is-invalid @enderror"
                        value="{{ old('nombre', $proveedor->nombre) }}"
                        required maxlength="150"
                        placeholder="Ej: Repuestos ABC S.A.">
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label for="nit" class="form-label">NIT</label>
                        <input
                            id="nit"
                            name="nit"
                            type="text"
                            class="form-control @error('nit') is-invalid @enderror"
                            value="{{ old('nit', $proveedor->nit) }}"
                            maxlength="50"
                            placeholder="Ej: 12345678">
                    </div>
                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input
                            id="telefono"
                            name="telefono"
                            type="text"
                            class="form-control @error('telefono') is-invalid @enderror"
                            value="{{ old('telefono', $proveedor->telefono) }}"
                            maxlength="30"
                            placeholder="Ej: 40718171">
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-12">
                        <label for="email" class="form-label">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $proveedor->email) }}"
                            maxlength="150"
                            placeholder="proveedor@dominio.com">
                    </div>
                </div>

                <div class="mt-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <textarea
                        id="direccion"
                        name="direccion"
                        rows="3"
                        maxlength="255"
                        class="form-control @error('direccion') is-invalid @enderror"
                        placeholder="Calle 123 #45-67, Ciudad">{{ old('direccion', $proveedor->direccion) }}</textarea>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-theme px-4">
                        <i class="bi bi-save me-1"></i> Guardar Cambios
                    </button>
                    <a href="{{ route('proveedores.index') }}" class="btn btn-muted px-4">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection
