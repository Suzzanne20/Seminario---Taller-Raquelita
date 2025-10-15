@extends('layouts.app')
@section('title','Home')

@push('styles')
<style>
  :root{
    --brand:#161a1e;          /* mismo tono oscuro que el sidebar */
    --accent:#9F3B3B;         /* tu rojo de marca */
    --ink:#0f172a;
    --muted:#6b7280;
  }

  /* --- Layout fixes para que no queden “franjas” --- */
  .page-body{ padding-top: 84px; } /* topbar fijo: 72–84px */
  @media (max-width:576px){ .page-body{ padding-top: 72px; } }

  /* --- HERO full width --- */
  .hero{
    position:relative; min-height:58vh;
    background:url('{{ asset('img/portada.jpg') }}') center/cover no-repeat;
    display:flex; align-items:center; justify-content:center;
    color:#fff; text-align:center;
  }
  .hero::before{
    content:"";
    position:absolute; inset:0;
    background:linear-gradient(180deg,rgba(0,0,0,.45),rgba(0,0,0,.55));
  }
  .hero-inner{ position:relative; z-index:1; max-width:980px; padding:18px; }
  .hero h1{ font-weight:800; letter-spacing:.3px; font-size:44px; margin-bottom:.25rem }
  .hero p{ opacity:.95; margin:0 0 1rem 0 }
  .btn-brand{
    display:inline-block; padding:12px 18px; border:0; border-radius:999px;
    background:linear-gradient(180deg, var(--accent), #C24242);
    color:#fff; font-weight:700;
  }
  .btn-brand:hover{ filter:brightness(.95); color:#fff; }

  /* --- Tarjeta de tracking, se “monta” bajo el hero --- */
  .track-card{
    max-width:1100px; margin:-48px auto 32px; position:relative; z-index:2;
    background:#fff; border-radius:18px; box-shadow:0 14px 36px rgba(0,0,0,.16);
    padding:20px 22px;
  }
  .track-form .form-control{ border-radius:12px; }
  .track-form .btn{ border-radius:12px; }

  /* --- Timeline de 5 pasos --- */
  .timeline{
    --line:#e5e7eb; --ok:#22c55e; --dot:#d1d5db;
    display:grid; grid-template-columns: repeat(5,1fr); gap:14px; margin-top:16px;
  }
  .t-step{
    text-align:center; position:relative; padding-top:44px;
  }
  .t-step .icon{
    width:68px; height:48px; border-radius:12px;
    margin:0 auto 8px; display:grid; place-items:center;
    background:linear-gradient(145deg,#1f2937,#111827); color:#fff;
    box-shadow: 0 6px 16px rgba(0,0,0,.25);
  }
  .t-step small{ color:#0f172a; font-weight:700; display:block; line-height:1.1 }
  .t-step span{ color:#475569; font-size:.82rem; display:block }

  /* línea conectando puntos */
  .t-step::before{
    content:""; position:absolute; top:20px; left:-50%; right:-50%; height:6px;
    background:var(--line); z-index:0;
  }
  .t-step:first-child::before{ left:50%; }
  .t-step:last-child::before{ right:50%; }

  /* dots */
  .dot{
    position:absolute; top:12px; left:50%; transform:translateX(-50%);
    width:26px; height:26px; border-radius:50%; background:var(--dot);
    box-shadow:0 0 0 6px #fff; z-index:1;
  }
  /* estados completados */
  .t-step.done .dot{ background:var(--ok); }
  .t-step.done::before{ background:var(--ok); }

  /* estado activo */
  .t-step.active .dot{
    background:var(--ok);
    box-shadow:0 0 0 6px #dcfce7;               /* aro verdoso */
  }
  .t-step.active small{ color:#16a34a; }

  /* --- GRID de bloques inferiores --- */
  .public-grid{
    max-width:1100px; margin:0 auto 40px; padding:0 16px;
    display:grid; gap:20px;
    grid-template-columns: 2fr 1fr;
  }
  @media (max-width: 992px){ .public-grid{ grid-template-columns:1fr; } }

  .card-soft{
    background:#fff; border-radius:16px; padding:18px;
    box-shadow:0 8px 26px rgba(0,0,0,.12);
  }

  /* carrusel promo simple */
  .promo{
    overflow:hidden; border-radius:12px;
  }
  .promo-track{
    display:flex; gap:12px; animation:promoSlide 22s infinite alternate ease-in-out;
  }
  .promo-track img{ width:100%; max-width:520px; height:260px; object-fit:cover; border-radius:12px; }

  @keyframes promoSlide{
    0%{ transform:translateX(0) }
    50%{ transform:translateX(-50%) }
    100%{ transform:translateX(0) }
  }

  /* mapa */
  .map-embed{ width:100%; height:260px; border:0; border-radius:12px; }

  /* galería mini */
  .gal-mini{ display:grid; grid-template-columns: repeat(2,1fr); gap:10px; }
  .gal-mini img{ width:100%; height:110px; object-fit:cover; border-radius:10px; }



</style>
@endpush

@section('content')

  {{-- HERO --}}
  <section class="hero">
    <div class="hero-inner">
      <h1>Centro de Servicio</h1>
      <p>Organiza tu taller, mejora tu servicio y controla todo desde un solo lugar</p>
      <a href="tel:+50200000000" class="text-white fw-semibold" style="text-decoration: none;">
        <i class="bi bi-telephone me-1"></i> +502 79453982
      </a>

      @guest
        <a href="{{ route('acceso') }}" class="btn-brand">Iniciar sesión</a>
      @endguest
      @auth
        <a href="{{ route('welcome') }}" class="btn-brand">Ir al panel</a>
      @endauth
    </div>
  </section><br><br><br>

  <section class="track-card">
    <div class="card-soft">
      <h5 class="mb-3">Nuestros servicios</h5>
      <div class="promo">
        <div class="promo-track">
          <img src="https://scontent.fgua3-6.fna.fbcdn.net/v/t39.30808-6/475077509_919905433464785_7239403634159879337_n.jpg?stp=cp6_dst-jpg_tt6&_nc_cat=102&ccb=1-7&_nc_sid=833d8c&_nc_ohc=3n5GfxnKPRQQ7kNvwE33pCt&_nc_oc=Adlf5GGqq9Yjs7k_8iuAxSSUGAhqDKRn-A_MXKQ-Wu-yFfnPVQGuVHfYgkAjNxIP3cyx3e1yO4thFFad86h3-gCH&_nc_zt=23&_nc_ht=scontent.fgua3-6.fna&_nc_gid=Pva5dOReQvNfdT7HAV3gTg&oh=00_Aff_vHr81k-OAFPwrUL4RtLEpg2UEr9tXeRDXQvAf1D7Fw&oe=68F5AF01" alt="Servicio 1">
          <img src="https://scontent.fgua9-2.fna.fbcdn.net/v/t39.30808-6/475115516_919909890131006_887864230794469421_n.jpg?_nc_cat=109&ccb=1-7&_nc_sid=833d8c&_nc_ohc=eeQJoizWl38Q7kNvwHgAm4_&_nc_oc=Adl87uJn2aBYAiE34mDmjEqKnK9LcyxMCClcuX03mhpf3c4yjQyWEvbDL6gw6zVohRxp1sc-NwxFhty4y8CxKBnF&_nc_zt=23&_nc_ht=scontent.fgua9-2.fna&_nc_gid=2eIKIr6tVpv6yktWhko64A&oh=00_Aff8TtvRPwSmDl9dhVdcil0qqPvMoqFPVrd-GsG2XZLnmg&oe=68F5A76E" alt="Servicio 2">
          <img src="https://scontent.fgua9-2.fna.fbcdn.net/v/t39.30808-6/475277450_919902453465083_5035177757349946442_n.jpg?_nc_cat=109&ccb=1-7&_nc_sid=833d8c&_nc_ohc=2vrdginbrA4Q7kNvwFOv6l_&_nc_oc=AdkcmUZTsTPhLcdIJmSX4QMO1KK1Ev36RS73n_FZo11m_a8vFZXjJ-VecdPjWDM9GfWgy9k1XdqnlEELhMfRGMG_&_nc_zt=23&_nc_ht=scontent.fgua9-2.fna&_nc_gid=7pcxPRXzqZv4Q5IDUeLS9A&oh=00_Afc3HyWyYoj9hLgJG7SCO8iegiOqzioJu9ksW6gf8UT5vQ&oe=68F5A540" alt="Servicio 3">
          <img src="https://scontent.fgua9-2.fna.fbcdn.net/v/t39.30808-6/476228146_927487639373231_5254933782692339784_n.jpg?_nc_cat=109&ccb=1-7&_nc_sid=833d8c&_nc_ohc=y1GicI6OUcwQ7kNvwHhjVZ8&_nc_oc=Adl9lJJJBj6BVFtegDwl_PkavxWmGGJMVJgnwrqdsekX_e6vyGFUx4mblB8la0AU741tXycBcSdld1-vxwu2DQ9b&_nc_zt=23&_nc_ht=scontent.fgua9-2.fna&_nc_gid=6dlcYsO6D7guJ-a141X5XA&oh=00_AffLcANyWoKegRUjjuKuj-V6MiPjtfVGUVfJgjv4gxyGxg&oe=68F5AC14" alt="Servicio 4">
          <img src="https://scontent.fgua3-5.fna.fbcdn.net/v/t39.30808-6/474976195_919894860132509_765467511337233984_n.jpg?_nc_cat=104&ccb=1-7&_nc_sid=833d8c&_nc_ohc=WbT_lNsI6BkQ7kNvwHqFN3t&_nc_oc=AdnlU53U2g1wD3C_SfKVNNOsxvBymO-dzsz5IMLIlO4swQzDE8syJMbR1ENGUlr41TSrxF58sd4XzyMIgD0sNVL1&_nc_zt=23&_nc_ht=scontent.fgua3-5.fna&_nc_gid=tN1elqQFRWIxxQ1X8qthMQ&oh=00_AffBkQB4135H1kgJrQjGs25O2l0I8nqMCYE1if5lK2HqTQ&oe=68F5AAF8" alt="Servicio 4">
          <img src="https://scontent.fgua3-6.fna.fbcdn.net/v/t39.30808-6/475105676_919902716798390_1089898621306806568_n.jpg?_nc_cat=102&ccb=1-7&_nc_sid=833d8c&_nc_ohc=lxmjNc_vk7wQ7kNvwFY0pd6&_nc_oc=Adlj4OCqAtFf7uNrfiDQLiDZFVhqL02Hpjv7Dkb_fnFTe7Wo_GoP8ZAZejUe16Uzwhvc6qXsJbsxyb_dRcwTjeAz&_nc_zt=23&_nc_ht=scontent.fgua3-6.fna&_nc_gid=q6zaK2CfWPKNgn-uiVSrNw&oh=00_AfcZzooBG61HLZqfXMxD-0yEULFC2ZMzyH09xSb98ANb1w&oe=68F5A539" alt="Servicio 4">
        </div>
      </div>
    </div>
  </section><br><br>

  {{-- TRACKING PÚBLICO --}}
  <section class="track-card">
    <h5 class="mb-3">Conoce el Estado de tu Orden de Trabajo</h5>

    {{-- Formulario --}}
    <label class="text-muted small mb-1">Ingresa el número de orden</label>
    <form class="track-form d-flex gap-2 flex-wrap" action="{{ route('track') }}" method="GET">
      <input type="number" min="1" name="ot" value="{{ request('ot') }}"
            class="form-control form-control-lg flex-grow-1" placeholder="Ej. 1234" required>
      <button class="btn btn-dark btn-lg" type="submit">Consultar</button>

      {{-- Botón limpiar (quita el querystring) --}}
      <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-lg">Limpiar</a>
    </form>
    <div class="text-muted small mt-2">* Este seguimiento solo muestra estado, placa y detalles básicos de la orden.</div>

    {{-- Resultado (debajo del formulario) --}}
    <div class="mt-3">
      @if(isset($orden) && $orden)
        <div class="card-soft mb-3">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Orden #{{ $orden->id }}</h6>
            <span class="badge bg-{{ $orden->estado->badge_class ?? 'secondary' }}">
              {{ $orden->estado->nombre ?? '—' }}
            </span>
          </div>

          <div class="mt-2 row g-3">
            <div class="col-6 col-md-4">
              <div class="text-muted small">Placa</div>
              <div class="fw-semibold">{{ $orden->vehiculo->placa ?? '—' }}</div>
            </div>
            <div class="col-6 col-md-4">
              <div class="text-muted small">Servicio</div>
              <div class="fw-semibold">{{ $orden->servicio->descripcion ?? '—' }}</div>
            </div>
            <div class="col-12">
              <div class="text-muted small">Descripción</div>
              <div>{{ $orden->descripcion ?? '—' }}</div>
            </div>
          </div>
        </div>

        {{-- TIMELINE VERTICAL --}}
        @php
          $estado = strtolower($orden->estado->nombre ?? '');
          $map = ['nueva'=>1,'asignada'=>2,'pendiente'=>3,'en proceso'=>4,'finalizada'=>5];
          $level = $map[$estado] ?? 1;
        @endphp

        <div class="timeline timeline--vertical">
          <div class="t-step {{ $level>=1?'done':'' }} {{ $level==1?'active':'' }}">
            <div class="dot"></div>
            <div class="icon"><i class="bi bi-clipboard-plus"></i></div>
            <div>
              <small>NUEVA</small>
              <span>Creada</span>
            </div>
          </div>

          <div class="t-step {{ $level>=2?'done':'' }} {{ $level==2?'active':'' }}">
            <div class="dot"></div>
            <div class="icon"><i class="bi bi-person-workspace"></i></div>
            <div>
              <small>ASIGNADA</small>
              <span>Técnico</span>
            </div>
          </div>

          <div class="t-step {{ $level>=3?'done':'' }} {{ $level==3?'active':'' }}">
            <div class="dot"></div>
            <div class="icon"><i class="bi bi-hourglass-split"></i></div>
            <div>
              <small>PENDIENTE</small>
              <span>En cola</span>
            </div>
          </div>

          <div class="t-step {{ $level>=4?'done':'' }} {{ $level==4?'active':'' }}">
            <div class="dot"></div>
            <div class="icon"><i class="bi bi-gear-wide-connected"></i></div>
            <div>
              <small>EN PROCESO</small>
              <span>Trabajando</span>
            </div>
          </div>

          <div class="t-step {{ $level>=5?'done':'' }} {{ $level==5?'active':'' }}">
            <div class="dot"></div>
            <div class="icon"><i class="bi bi-check2-circle"></i></div>
            <div>
              <small>FINALIZADA</small>
              <span>Lista</span>
            </div>
          </div>
        </div>
      @elseif(request()->has('ot'))
        <div class="alert alert-warning mb-0 rounded-3">No encontramos una orden con el ID indicado.</div>
      @endif
    </div>
  </section>


  {{-- BLOQUES: Mapa + Galería --}}
  <section class="public-grid">

    <div class="card-soft">
      <h5 class="mb-3">Galería del taller</h5>
      <div class="gal-mini">
        <img src="{{ asset('img/foto1.jpg') }}" alt="">
        <img src="{{ asset('img/foto2.jpg') }}" alt="">
        <img src="{{ asset('img/foto3.jpg') }}" alt="">
        <img src="{{ asset('img/foto4.jpg') }}" alt="">
        <img src="https://scontent.fgua3-4.fna.fbcdn.net/v/t1.6435-9/105610020_153761039607512_3729277442659497237_n.jpg?_nc_cat=111&ccb=1-7&_nc_sid=833d8c&_nc_ohc=pBqLdiJOYdsQ7kNvwFf8rFA&_nc_oc=AdkLyMhTQMt47zAcTu0hbj-6y41ELFhzh8jNhFc9CIqMI-O7rXbiEx81SiR1zt6ox-e8Ehvhm15X40GO0fAEBMFy&_nc_zt=23&_nc_ht=scontent.fgua3-4.fna&_nc_gid=s2sueJcFr-1DKhSlBJ2Y1Q&oh=00_AfeI1KIDuokT2vJmtdg5vsSNMgQ40u2C5rBMzESQJuZvtw&oe=691751C7" alt="">
        <img src="https://scontent.fgua3-4.fna.fbcdn.net/v/t39.30808-6/480183246_1181473383502934_1650660878132985825_n.jpg?_nc_cat=106&ccb=1-7&_nc_sid=833d8c&_nc_ohc=xm6Ge-upLH0Q7kNvwE04rY4&_nc_oc=AdmMbsjQT58XzRgrsKX5juwnzpv7Z_YfUMgIdPIfiGe7nsf5gVSMJ51uhdCqJE-1stz1AkUj0zriCxsCfayHBLYA&_nc_zt=23&_nc_ht=scontent.fgua3-4.fna&_nc_gid=TlUIIKpkfA_Ira3DS44B_A&oh=00_AfcNWHC5UB7COwe9RkRadKnE8auWKfh-HFen8nRZ5bHFyA&oe=68F5C89D" alt="">
        <img src="https://scontent.fgua3-4.fna.fbcdn.net/v/t39.30808-6/481346581_1192119049105034_8152765714954599951_n.jpg?_nc_cat=106&ccb=1-7&_nc_sid=833d8c&_nc_ohc=WihW-5u4besQ7kNvwGerqjC&_nc_oc=Adl0rfUdhG3vdNZQhMe94u--qaYvUpHx3HxWo3zFQ9qS1dy9ds_i2eunxzO74qNgATZN4wCurx0aV9Da_rfl2FG7&_nc_zt=23&_nc_ht=scontent.fgua3-4.fna&_nc_gid=W4czv57lpLoaCZsVBjKWNw&oh=00_AfcNDtGnzeYz1m-vbB-9tG21TaJSU0vhmEizD9H-eW9Qzw&oe=68F5CE0B" alt="">
        <img src="https://scontent.fgua3-5.fna.fbcdn.net/v/t39.30808-6/481217656_1192607582389514_7540645972927077496_n.jpg?_nc_cat=101&ccb=1-7&_nc_sid=833d8c&_nc_ohc=lB17j9AhXz0Q7kNvwGiVCa9&_nc_oc=Adl5NPEZwz7xLw7lpSWYPxZngSNdr2dnReN4r-ffruTAMUdZi3XCy8pCd7LsdCWPTfX2RRh_Y_05I5uMn2Vn1D8H&_nc_zt=23&_nc_ht=scontent.fgua3-5.fna&_nc_gid=B2_GHrKQff5pCYFAYa8tFQ&oh=00_AfeNbc4DbR8Le-8Q6g49S2klZyKpiGZgum3Elvkxodkr_w&oe=68F5CF11" alt="">
      </div>
    </div>

    <div class="card-soft">
      <h5 class="mb-3">¿Dónde nos Ubicamos?</h5>
      <h7 class="mb-3"><i class="bi bi-geo-alt-fill"></i> Calle Principal, Colonia 15 de Abril, Santo Tomas de Castilla., Puerto Barrios, Guatemala</h7>
      <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d480.1490944098941!2d-88.61612516142601!3d15.687917568444545!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1ses!2sgt!4v1760551902778!5m2!1ses!2sgt" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </section>

  <footer class="py-4 text-center text-muted">
    <small>© {{ date('Y') }} Centro de Servicio Raquelita. Todos los derechos reservados.</small>
  </footer>
@endsection

