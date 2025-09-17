@extends('layouts.app')
    @section('title','Home')

    @push('styles')
        <style>
            :root{
                --brand-primary:#B23940; --brand-accent:#D44A52;
                --ink:#111; --muted:#6b7280;
            }
            /* Hero */
            .hero{
                position:relative; min-height:56vh;
                display:flex; align-items:center; justify-content:center;
                text-align:center; color:#fff;
                background-size:cover; background-position:center;
            }
            .hero-overlay{position:absolute; inset:0; background:linear-gradient(180deg,rgba(0,0,0,.55),rgba(0,0,0,.55));}
            .hero-content{position:relative; z-index:1; max-width:900px; padding:32px 16px;}
            .hero h1{font-size:42px; font-weight:800; margin-bottom:10px}
            .hero p{opacity:.95; margin-bottom:18px}
            .btn{
                display:inline-block; padding:12px 18px; border-radius:999px;
                background:linear-gradient(180deg,var(--brand-primary),var(--brand-accent));
                color:#fff; font-weight:700;
            }

            /* CTA pills */
            .cta-bar{background:#2a2a2a; border-top:4px solid var(--brand-primary); border-bottom:4px solid var(--brand-primary); padding:14px 0; text-align:center}
            .pill{display:inline-block; margin:0 8px; padding:10px 14px; border-radius:999px; background:#eaeaea; font-weight:700}

            /* Secciones */

            .container{max-width:1100px; margin:0 auto; padding:0 16px}
            .container h2{color:var(--ink); font-weight:800; margin:12px 0 6px}
            .container p{color:#374151; line-height:1.6}

            /* Galería simple */
            .galeria{padding:28px 0; background:#fff; text-align:center}
            .galeria h2{font-weight:800; margin-bottom:12px}
            .carousel{overflow:hidden}
            .carousel-track{display:flex; gap:10px; overflow:auto; scroll-snap-type:x mandatory; padding:0 16px}
            .carousel-track img{width:260px; height:160px; object-fit:cover; border-radius:12px; scroll-snap-align:start}

            footer{padding:24px 0; text-align:center; color:var(--muted)}
            @media (max-width:768px){ .hero h1{font-size:34px} }
        </style>
    @endpush

    @section('content')
        <section class="hero" style="background-image:url('{{ asset('img/portada.jpg') }}');">
            <div class="content">
                <h1 class="display-5">Centro de Servicio Raquelita</h1>
                <p class="lead mb-3">Organiza tu taller y controla todo desde un solo lugar</p>

                @guest
                    <a href="{{ route('acceso') }}" class="btn btn-brand">Iniciar sesión</a> {{--Dirige a acceso para invitados que no esten logueados--}}
                @endguest

                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-brand">Ir al panel</a>{{--Dirige al welcome donde se encuentra información interna del taller--}}
                @endauth
            </div>
        </section>

    {{-- Barra de botones principales --}}
    <section class="cta-bar">
        <a href="#" class="pill">Servicios</a>
        <a href="#" class="pill">Ubicacion</a>
        <a href="#" class="pill">Contacto</a>
    </section>

    {{-- Misión y Visión --}}
    <section class="mision-vision" id="mision-vision">
        <div class="container">
            <h2>Misión</h2>
            <p>Brindar un servicio técnico automotriz integral, transparente y de calidad, optimizando cada proceso del taller mediante tecnología.</p>
            <h2>Visión</h2>
            <p>Ser el taller líder en la región en gestión administrativa y técnica, destacando por su innovación y digitalización de procesos.</p>
        </div>
    </section>

    {{-- CARRUSEL --}}
    <section class="galeria" id="galeria">
        <h2>Galería de Servicios</h2>
        <div class="carousel">
            <div class="carousel-track">
                <img src="{{ asset('img/foto1.jpg') }}" alt="Servicio 1">
                <img src="{{ asset('img/foto2.jpg') }}" alt="Servicio 2">
                <img src="{{ asset('img/foto3.jpg') }}" alt="Servicio 3">
                <img src="{{ asset('img/foto4.jpg') }}" alt="Servicio 4">
            </div>
        </div>
    </section>

    <footer>
        <p>Todos los derechos reservados.</p>
    </footer>
    @endsection

