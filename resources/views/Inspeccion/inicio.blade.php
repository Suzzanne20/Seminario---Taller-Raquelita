@extends('layouts.app')
@section('title','Inspección 360')

@section('content')
<style>
/* ==== Estilos aislados a esta vista ==== */
.isp360-start { --brand:#8f2f2f; --brand2:#b43b3b; --ink:#1f1f1f; --muted:#6b6b6b; --bg:#f6f6f7; }
.isp360-start .hero{background:var(--brand);color:#fff;text-align:center;padding:48px 16px 56px;position:relative}
.isp360-start .logo-wrap{display:flex;align-items:center;justify-content:center;gap:24px}
.isp360-start .logo{width:72px;height:72px;object-fit:contain;border-radius:999px;background:#fff;padding:8px}
.isp360-start .divider{height:8px;background:#fff;border-radius:4px;width:min(760px,90vw);position:absolute;left:50%;transform:translateX(-50%);bottom:-4px;box-shadow:0 2px 10px rgba(0,0,0,.15)}
.isp360-start h1{margin:16px 0 6px;font-weight:800;letter-spacing:.3px}
.isp360-start .subtitle{margin:0;opacity:.95}
.isp360-start .cards{max-width:1080px;margin:64px auto;padding:0 20px;display:grid;gap:22px;grid-template-columns:repeat(3,1fr)}
@media (max-width: 900px){.isp360-start .cards{grid-template-columns:1fr}}
.isp360-start .card{background:#fff;border:1px solid #ececec;border-radius:18px;padding:24px 20px;text-decoration:none;color:var(--ink);display:flex;flex-direction:column;align-items:center;text-align:center;transition:transform .15s ease,box-shadow .15s ease,border-color .15s ease;box-shadow:0 6px 20px rgba(0,0,0,.06)}
.isp360-start .card:hover{transform:translateY(-4px);box-shadow:0 12px 26px rgba(0,0,0,.08);border-color:#e7e7e7}
.isp360-start .pic{width:120px;height:120px;border-radius:20px;background:#fff;display:grid;place-items:center;margin-bottom:16px;border:1px dashed #e6e6e6}
.isp360-start .card h3{margin:6px 0 8px;font-weight:700}
.isp360-start .card p{margin:0 0 18px;color:var(--muted);line-height:1.5}
.isp360-start .cta{margin-top:auto;display:inline-block;padding:10px 16px;border-radius:999px;background:var(--brand2);color:#fff;font-weight:600;letter-spacing:.2px;box-shadow:0 6px 16px rgba(180,59,59,.25)}
/* opcional para que el fondo luzca limpio */
body{background:#f3f4f6}
</style>

<div class="isp360-start">
  <header class="hero">
   
  <main class="cards">
    {{-- Registrar --}}
    <a class="card" href="{{ route('inspecciones.create') }}">
      <div class="pic">
        {{-- Ícono SVG inline (no requiere archivo) --}}
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="#b43b3b" stroke-width="2"/>
          <path d="M12 7v10M7 12h10" stroke="#b43b3b" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
      <h3>Registrar inspección</h3>
      <p>Marca puntos en el vehículo, adjunta fotos y guarda el detalle por placa.</p>
      <span class="cta">Empezar</span>
    </a>

    {{-- Modificar → te llevo al listado con un flag para editar --}}
    <a class="card" href="{{ route('inspecciones.index', ['modo'=>'editar']) }}">
      <div class="pic">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
          <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z" stroke="#b43b3b" stroke-width="2"/>
          <path d="M14.06 6.19l2.12-2.12a1.5 1.5 0 0 1 2.12 0l1.63 1.63a1.5 1.5 0 0 1 0 2.12l-2.12 2.12" stroke="#b43b3b" stroke-width="2"/>
        </svg>
      </div>
      <h3>Modificar inspección</h3>
      <p>Edita una inspección existente buscando por placa o eligiendo de la lista.</p>
      <span class="cta">Editar</span>
    </a>

    {{-- Listado --}}
    <a class="card" href="{{ route('inspecciones.index') }}">
      <div class="pic">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
          <rect x="4" y="5" width="16" height="3" rx="1.5" stroke="#b43b3b" stroke-width="2"/>
          <rect x="4" y="10.5" width="16" height="3" rx="1.5" stroke="#b43b3b" stroke-width="2"/>
          <rect x="4" y="16" width="16" height="3" rx="1.5" stroke="#b43b3b" stroke-width="2"/>
        </svg>
      </div>
      <h3>Ver lista de inspecciones</h3>
      <p>Consulta todas las inspecciones registradas y gestiona sus acciones.</p>
      <span class="cta">Ver lista</span>
    </a>
  </main>
</div>
@endsection
