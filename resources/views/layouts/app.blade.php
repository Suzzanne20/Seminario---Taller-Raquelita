<!doctype html>
<html lang="es">


<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title','Centro de Servicio Raquelita')</title>
    <link rel="icon" href="{{ asset('img/logo_taller_circular.ico') }}">

    {{-- Bootstrap + Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Estilos con Vite --}}

    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=1" />

    {{-- Estilos puntuales del header --}}
    <style>
        .topbar{background:#8e2f2f; height:72px; position:relative; overflow:visible;}
        .logo-centered{
            position:absolute; left:50%; top:100%; transform:translate(-50%, -50%);
            height:88px; width:auto; z-index:2; background:#fff; padding:6px; border-radius:50%;
            box-shadow:0 4px 14px rgba(0,0,0,.25);
        }
        .offcanvas-btn{
            position:absolute; right:16px; top:50%; transform:translateY(-50%);
            border:0; z-index:3; color:#fff;
        }
        .offcanvas .nav-link{ color:#222; }
        .offcanvas .nav-link:hover{ color:#8e2f2f; }
        .offcanvas .navbar-nav .nav-item + .nav-item{ border-top:1px solid #e9ecef; }
        @media (max-width:576px){ .topbar{height:64px;} .logo-centered{height:72px; padding:5px;} .offcanvas-btn{right:12px;} }
        html{ scroll-behavior:smooth; }
        @stack('styles')
    </style>
</head>
<body>


{{-- HEADER reutilizable --}}
<header class="topbar">
    {{-- Botón del menú (anclado a la derecha) --}}
    <button class="navbar-toggler offcanvas-btn text-white"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#offcanvasNavbar"
            aria-controls="offcanvasNavbar"
            aria-label="Abrir menú">

        <span class="d-inline-flex fs-3"><i class="bi bi-list"></i></span>
    </button>

    {{-- Logo centrado y sobresaliente --}}
    <img

        src="{{ asset('img/logo_taller.jpg') }}"
        alt="Logo Centro de Servicio Raquelita"
        class="logo-centered">
</header>

{{-- OFFCANVAS (menú lateral derecho) --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menú</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column">
        <ul class="navbar-nav flex-grow-1 pe-3">
            <li class="nav-item my-2 text-center">
                <img
                    src="https://raw.githubusercontent.com/Suzzanne20/ResourceNekoStation/refs/heads/main/1757173060470.png"
                    alt="logo" width="110" height="110" style="margin-top:-30px;">
            </li>

            @guest
                <li class="nav-item pt-2">
                    <a class="nav-link" href="{{ route('acceso') }}">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                    </a>
                </li>
            @endguest
            @auth
                @role('admin')
                <li class="nav-item pt-2">
                    <a class="nav-link" href="{{ route('users.index') }}">
                        <i class="bi bi-people me-2"></i>Usuarios
                    </a>
                </li>
                @endrole

                <li class="nav-item dropdown pt-2">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-2"></i>{{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-gear me-2"></i>Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            @endauth

            <li class="nav-item pt-2"><a class="nav-link" href="{{ route('home') }}"><i class="bi bi-house me-2"></i>Inicio</a></li>
            <li class="nav-item pt-2"><a class="nav-link" href="#"><i class="bi bi-bar-chart me-2"></i>Dashboard</a></li>
            <li class="nav-item pt-2"><a class="nav-link" href="#"><i class="bi bi-tools me-2"></i>Servicios</a></li>
            <li class="nav-item pt-2"><a class="nav-link" href="{{ route('vehiculos.index') }}"><i class="bi bi-car-front-fill me-2"></i>Vehículos</a></li>
            <li class="nav-item pt-2"><a class="nav-link" href="#"><i class="bi bi-ev-front-fill me-2"></i>Inspecciones 360°</a></li>

            <li class="nav-item dropdown pt-2">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-card-list me-2"></i>Órdenes de Trabajo
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('ordenes.index') }}"><i class="bi bi-ev-front me-2"></i>Listado de Órdenes</a></li>
                    <li><a class="dropdown-item" href="{{ route('ordenes.create') }}"><i class="bi bi-pencil-square me-2"></i>Nueva Orden de Trabajo</a></li>
                </ul>
            </li>

            <li class="nav-item dropdown pt-2">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-box-seam me-2"></i>Bodega
                </a>
                <ul class="dropdown-menu">

                    <li><a class="dropdown-item" href="{{ route('insumos.index') }}"><i class="bi bi-dropbox me-2"></i>Inventario</a></li>
                    <li><a class="dropdown-item" href="{{ route('insumos.create') }}"><i class="bi bi-pencil-square me-2"></i>Registro de Insumos</a></li>
                    <li><a class="dropdown-item" href="{{ route('tipo-insumos.index') }}"><i class="bi bi-pencil-square me-2"></i>Gestionar Tipos Insumos</a></li>

                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">Órdenes de Compra</a></li>
                </ul>
            </li>

            <li class="nav-item dropdown pt-2">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-wallet2 me-2"></i>Cotizaciones
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('cotizaciones.index') }}">Listado de Cotizaciones</a></li>
                    <li><a class="dropdown-item" href="{{ route('cotizaciones.create') }}">Nueva Cotización</a></li>
                </ul>
            </li>

            <li class="nav-item pt-2"><a class="nav-link" href="#"><i class="bi bi-person-rolodex me-2"></i>Técnicos</a></li>
        </ul>

        <hr class="mt-3 mb-2">
        <div class="d-flex justify-content-center gap-3">
            <a class="text-decoration-none" href="https://facebook.com/tu-pagina" target="_blank" aria-label="Facebook">
                <i class="bi bi-facebook fs-4"></i>
            </a>
            <a class="text-decoration-none" href="https://wa.me/50200000000" target="_blank" aria-label="WhatsApp">
                <i class="bi bi-whatsapp fs-4"></i>
            </a>
        </div>
    </div>
</div>

{{-- CONTENIDO de cada página --}}
<main class="page-body">
    @yield('content')
</main>


@stack('scripts')

</body>
</html>
