@extends('layouts.app')

@section('title','Home')

@section('content')



@push('styles')
<style>
  :root{
    --brand-primary:#9F3B3B; /* mismo tema que Welcome */
    --brand-accent:#C24242;
  }
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
  .btn-brand{
    display:inline-block; padding:12px 18px; border-radius:999px;
    background:linear-gradient(180deg,var(--brand-primary),var(--brand-accent));
    color:#fff; font-weight:700; border:0;
  }
  .btn-brand:hover{ filter:brightness(.95); color:#fff; }

  /* CTA pills (idéntico a Welcome) */
  .cta-bar{
    background:#2a2a2a; border-top:4px solid var(--brand-primary);
    border-bottom:4px solid var(--brand-primary); padding:14px 0; text-align:center
  }
  .pill{
    display:inline-block; margin:0 8px; padding:10px 14px; border-radius:999px;
    background:#eaeaea; font-weight:700; color:#111; text-decoration:none;
  }

  /* Secciones */
  .container-sec{max-width:1100px; margin:0 auto; padding:0 16px}
  .container-sec h2{color:#111827; font-weight:800; margin:12px 0 6px}
  .container-sec p{color:#374151; line-height:1.6}

  /* Galería (idéntica) */
  .galeria{padding:28px 0; background:#fff; text-align:center}
  .galeria h2{font-weight:800; margin-bottom:12px}
  .carousel{overflow:hidden}
  .carousel-track{display:flex; gap:10px; overflow:auto; scroll-snap-type:x mandatory; padding:0 16px}
  .carousel-track img{width:260px; height:160px; object-fit:cover; border-radius:12px; scroll-snap-align:start}

  footer{padding:24px 0; text-align:center; color:#6b7280}

  /* ===== Tracking ===== */
  .track-card{
    max-width:900px; margin:-48px auto 24px; position:relative; z-index:2;
    background:#fff; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,.12);
    padding:18px;
  }
  .track-form .form-control{border-radius:12px;}
  .track-form .btn{border-radius:12px;}
  .result-card{
    border:1px solid #eee; border-radius:12px; padding:16px; background:#fafafa;
  }
  .result-card h6{font-weight:800; margin-bottom:6px;}
  .small-muted{color:#6b7280; font-size:.92rem;}
  @media (max-width:768px){ .hero h1{font-size:34px} }
</style>
@endpush

@section('content')

  {{-- HERO igual a Welcome --}}
  <section class="hero" style="background-image:url('{{ asset('img/portada.jpg') }}');">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1>Centro de Servicio</h1>
      <p>Organiza tu taller, mejora tu servicio y controla todo desde un solo lugar</p>

      @guest
        <a href="{{ route('acceso') }}" class="btn-brand">Iniciar sesión</a>
      @endguest

      @auth
        <a href="{{ route('welcome') }}" class="btn-brand">Ir al panel</a>
      @endauth
    </div>
  </section>

  {{-- ===== Widget: Tracking público de órdenes ===== --}}
  <section class="track-card">
    <div class="row g-3 align-items-end">
      <div class="col-lg-7">
        <label class="small-muted mb-1">Ingresa el número de orden</label>
        <form class="track-form d-flex gap-2" action="{{ route('track') }}" method="GET">
          <input type="number" min="1" name="ot" value="{{ request('ot') }}"
                 class="form-control form-control-lg" placeholder="Ej. 1234" required>
          <button class="btn btn-dark btn-lg" type="submit">Consultar</button>
        </form>
        <div class="small-muted mt-2">
          * Este seguimiento solo muestra estado, placa y detalles básicos de la orden.
        </div>
      </div>

      {{-- Resultado (si existe $orden) --}}
      <div class="col-lg-5">
        @if(isset($orden) && $orden)
          <div class="result-card">
            <h6 class="mb-1">Orden #{{ $orden->id }}</h6>
            <div class="mb-2">
              <span class="badge bg-{{ $orden->estado->badge_class ?? 'secondary' }}">
                {{ $orden->estado->nombre ?? '—' }}
              </span>
            </div>
            <div class="small-muted">Placa</div>
            <div class="fw-semibold mb-2">{{ $orden->vehiculo->placa ?? '—' }}</div>

            <div class="small-muted">Tipo de servicio</div>
            <div class="fw-semibold mb-2">{{ $orden->servicio->descripcion ?? '—' }}</div>

            <div class="small-muted">Descripción</div>
            <div>{{ $orden->descripcion ?? '—' }}</div>
          </div>
        @elseif(request()->has('ot'))
          <div class="alert alert-warning mb-0" style="border-radius:12px;">
            No encontramos una orden con el ID indicado.
          </div>
        @endif
      </div>
    </div>
  </section>

  {{-- Barra de botones principales (idéntico a Welcome) --}}
  <section class="cta-bar">
    <a href="#mision-vision" class="pill">Servicios</a>
    <a href="#mision-vision" class="pill">Ubicación</a>
    <a href="#mision-vision" class="pill">Contacto</a>
  </section>

  {{-- Misión y Visión --}}
  <section class="mision-vision" id="mision-vision">
    <div class="container-sec">
      <h2>Misión</h2>
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

