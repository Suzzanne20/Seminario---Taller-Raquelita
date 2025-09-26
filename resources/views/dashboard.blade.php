@extends('layouts.app')
@section('title','Dashboard')

@section('content')
    <div class="container py-4">
        <h2 class="mb-3">Panel de control</h2>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-people fs-3 me-2"></i>
                            <div>
                                <div class="fw-bold">Usuarios</div>
                                <small class="text-muted">Gestión de cuentas y roles</small>
                            </div>
                        </div>
                        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-primary mt-3">Ir a usuarios</a>
                    </div>
                </div>
            </div>
            {{-- DASHBOARD... --}}
        </div>
    </div>
@endsection
