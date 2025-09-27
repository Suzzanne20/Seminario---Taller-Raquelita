@extends('layouts.app')

@section('title','Inicio')

@section('content')

  {{-- Hero principal --}}
  <section class="hero" style="background-image:url('{{ asset('img/portada.jpg') }}');">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1>Centro de Servicio</h1>
      <p>Organiza tu taller, mejora tu servicio y controla todo desde un solo lugar</p>

      <a href="{{ route('clientes.index') }}" class="btn">Ir a Clientes</a>

      @if (Route::has('inspecciones.start'))
        <a href="{{ route('inspecciones.start') }}" class="btn btn-danger" style="margin-left:.5rem">
          Inspección 360
        </a>
      @endif
    </div>
  </section>

  {{-- Barra de botones principales --}}
  <section class="cta-bar">
    <a href="{{ route('clientes.index') }}" class="pill">Clientes</a>
    <a href="{{ route('ordenes.index') }}" class="pill">Órdenes de Trabajo</a>

    @if (Route::has('inspecciones.start'))
      <a href="{{ route('inspecciones.start') }}" class="pill">Inspección 360</a>
    @endif
  </section>

  {{-- Misión y Visión --}}
  <section class="mision-vision" id="mision-vision">
    <div class="container">
      <h2>Misiónnnnn</h2>
      <p>Brindar un servicio técnico automotriz integral, transparente y de calidad, optimizando cada proceso del taller mediante tecnología.</p>
      <h2>Visión</h2>
      <p>Ser el taller líder en la región en gestión administrativa y técnica, destacando por su innovación y digitalización de procesos.</p>
    </div>
  </section>

  {{-- Galería --}}
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
