@extends('layouts.app')
@section('title','Inspección 360')

@section('content')
<style>
  :root{
    --brand:#8f2f2f;      /* vino */
    --brand-2:#b43b3b;    /* rojo acento */
    --ink:#171717;        /* texto principal */
    --muted:#6b7280;      /* texto suave */
    --bg:#f6f7f9;         /* fondo */
  }
  body{ background:var(--bg); }

  /* ===== Layout centrado ===== */
  .landing-wrap{
    min-height:70vh;
    display:grid;
    place-items:center;
    padding:28px 16px;
  }
  .cta-grid{
    display:grid;
    grid-template-columns: repeat(2, minmax(260px, 360px));
    gap:22px;
    justify-content:center;
    width:100%;
    max-width:820px;
  }
  @media (max-width:720px){
    .cta-grid{ grid-template-columns: minmax(260px, 520px); }
  }

  /* ===== Tarjeta/ botón “wow” ===== */
  .cta-card{
    position:relative;
    text-decoration:none;
    display:flex;
    align-items:center;
    gap:16px;
    padding:18px 20px;
    border-radius:18px;
    color:var(--ink);
    background:linear-gradient(180deg, rgba(255,255,255,.88), rgba(248,248,250,.92));
    border:1px solid rgba(255,255,255,.55);
    box-shadow:
      0 10px 28px rgba(0,0,0,.10),
      inset 0 1px 0 rgba(255,255,255,.6);
    backdrop-filter: blur(6px);
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
    will-change: transform;
    overflow:hidden;
  }
  /* Borde degradado animado */
  .cta-card::before{
    content:"";
    position:absolute; inset:-1px;
    z-index:0;
    border-radius:19px;
    padding:1px;
    background: conic-gradient(from 30deg, #ffb3b3, #ffd0d0, #fff, #ffd0d0, #ffb3b3);
    -webkit-mask:
      linear-gradient(#000 0 0) content-box, 
      linear-gradient(#000 0 0);
    -webkit-mask-composite: xor;
            mask-composite: exclude;
    animation: spin 6s linear infinite;
    opacity:.75;
  }
  @keyframes spin { to { transform: rotate(1turn); } }

  .cta-card:hover{
    transform: translateY(-4px);
    box-shadow:
      0 18px 40px rgba(0,0,0,.14),
      inset 0 1px 0 rgba(255,255,255,.7);
    border-color: rgba(255,255,255,.8);
    background:linear-gradient(180deg, rgba(255,255,255,.92), rgba(250,250,252,.96));
  }

  /* Ripple suave al hover (decorativo) */
  .cta-card::after{
    content:"";
    position:absolute; inset:-40%;
    background: radial-gradient(400px 220px at var(--mx,70%) var(--my,50%), rgba(180,59,59,.08), transparent 55%);
    transition: opacity .2s ease;
    opacity:0;
    z-index:0;
  }
  .cta-card:hover::after{ opacity:1; }

  .cta-icon{
    position:relative; z-index:1;
    width:58px; height:58px; flex:0 0 58px;
    display:grid; place-items:center;
    border-radius:16px;
    background: linear-gradient(180deg, #ffffff 0%, #f4f4f7 100%);
    border:1px dashed #ececec;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.7);
  }
  .cta-text{
    position:relative; z-index:1;
    display:flex; flex-direction:column; gap:6px;
  }
  .cta-title{ font-size:18px; font-weight:800; letter-spacing:.2px; color:var(--ink) }
  .cta-sub{ font-size:13px; color:var(--muted); line-height:1.45 }

  .tag{
    position:relative; z-index:1;
    margin-left:auto;
    font-size:12px; font-weight:700;
    color:#fff;
    background: linear-gradient(90deg, var(--brand-2), #e06a6a);
    padding:6px 10px;
    border-radius:999px;
    box-shadow:0 6px 16px rgba(180,59,59,.28);
  }

  /* Focus accesible */
  .cta-card:focus-visible{ outline:none; box-shadow:0 0 0 4px rgba(180,59,59,.22), 0 10px 28px rgba(0,0,0,.10); }
</style>

<div class="landing-wrap">
  <div class="cta-grid">

    {{-- Botón: Registrar inspección --}}
    <a class="cta-card" href="{{ route('inspecciones.create') }}">
      <div class="cta-icon" aria-hidden="true">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="9" stroke="#b43b3b" stroke-width="2"/>
          <path d="M12 7v10M7 12h10" stroke="#b43b3b" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
      <div class="cta-text">
        <span class="cta-title">Registrar inspección</span>
        <span class="cta-sub">Crea una nueva inspección, marca puntos y adjunta evidencias.</span>
      </div>
      <span class="tag">Nuevo</span>
    </a>

    {{-- Botón: Ver lista --}}
    <a class="cta-card" href="{{ route('inspecciones.index') }}">
      <div class="cta-icon" aria-hidden="true">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none">
          <rect x="5" y="6" width="14" height="2.6" rx="1.3" stroke="#b43b3b" stroke-width="2"/>
          <rect x="5" y="10.7" width="14" height="2.6" rx="1.3" stroke="#b43b3b" stroke-width="2"/>
          <rect x="5" y="15.4" width="14" height="2.6" rx="1.3" stroke="#b43b3b" stroke-width="2"/>
        </svg>
      </div>
      <div class="cta-text">
        <span class="cta-title">Ver lista de inspecciones</span>
        <span class="cta-sub">Explora el historial por placa y consulta cada detalle.</span>
      </div>
    </a>

  </div>
</div>

<script>
  // Efecto ripple sigue al mouse (decorativo)
  document.querySelectorAll('.cta-card').forEach(card=>{
    card.addEventListener('pointermove', (e)=>{
      const r = card.getBoundingClientRect();
      card.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      card.style.setProperty('--my', (e.clientY - r.top) + 'px');
    });
  });
</script>
@endsection
