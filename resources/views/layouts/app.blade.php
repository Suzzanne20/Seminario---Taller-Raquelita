<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>@yield('title','Centro de Servicio Raquelita')</title>
  <link rel="icon" href="{{ asset('img/logo_taller_circular.ico') }}">

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  {{-- Tu hoja de estilos global (no debe oscurecer body) --}}
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=1"/>

  <style>
        :root{
        --sbw-collapsed:72px;
        --sbw-expanded:260px;
        --topbar-h:72px;
        --brand:#9F3B3B;
        --sidebar-bg:#1b1b1b;
        --sidebar-hover:#9F3B3B;
        --sidebar-border:#1b1b1b;
        }

        /* ===== Topbar (color anterior) ===== */
      .topbar {
        background-color: var(--sidebar-bg);   /* <-- AHORA IGUAL QUE EL NAVBAR */
        padding: 1px 0;
        text-align: center;
        position: fixed;
        top: 0; left: 0;
        height: 72px;
        overflow: visible;
        width: 100%;
        z-index: 999;
        box-shadow: 0 2px 8px rgba(0,0,0,.25);
      }

        /* Logo flotando más grande y sobresaliendo */
        .topbar .logo-wrap{
        position:absolute; left:50%; top:75%;
        transform:translate(-50%, -50%);
        z-index:1031;
        }
        .topbar .logo{
        height:88px;                    /* más grande */
        width:auto; background:#fff; border-radius:50%;
        padding:6px; box-shadow:0 4px 14px rgba(0,0,0,.25);
        }

        /* Botón y acciones */
        .topbar .left-actions{ position:absolute; left:12px; top:50%; transform:translateY(-50%); display:flex; gap:8px; }
        .topbar .right-actions{ position:absolute; right:14px; top:50%; transform:translateY(-50%); display:flex; gap:14px; color:#fff; }
        .btn-toggle{ border:0; background:transparent; color:#fff; font-size:1.6rem; padding:.25rem .35rem; border-radius:.5rem; }
        .btn-home{ display:inline-flex; align-items:center; gap:.4rem; background:#ffffff1a; color:#fff; border:1px solid #ffffff33; padding:.35rem .65rem; border-radius:999px; text-decoration:none; font-weight:700; }
        .btn-home:hover{ background:#ffffff26; color:#fff; }

        /* ===== Sidebar ===== */
        .sidebar{ position:fixed; top:var(--topbar-h); left:0; bottom:0; width:var(--sbw-collapsed); background:var(--sidebar-bg); color:#e7ecf3; border-right:1px solid var(--sidebar-border); transition:width .25s ease; z-index:1020; overflow:hidden; }
        .sidebar.expanded{ width:var(--sbw-expanded); }
        .sidebar .nav-section{ padding:.5rem .5rem .25rem; }
        .side-link{ display:flex; align-items:center; gap:.75rem; color:#cfd6e4; text-decoration:none; padding:.65rem .85rem; margin:.2rem .35rem; border-radius:.6rem; font-weight:600; white-space:nowrap; }
        .side-link:hover{ background:var(--sidebar-hover); color:#fff; }
        .side-link .text{ opacity:1; transition:opacity .2s ease; }
        .sidebar:not(.expanded) .side-link .text{ opacity:0; }

        /* Tooltips rápidos para modo retraído */
        .sidebar:not(.expanded) .side-link{ position:relative; }
        .sidebar:not(.expanded) .side-link:hover::after{
        content:attr(data-title);
        position:absolute; left:calc(100% + 8px); top:50%; transform:translateY(-50%);
        background:#111; color:#fff; padding:.35rem .55rem; border-radius:.4rem;
        box-shadow:0 6px 20px rgba(0,0,0,.25); white-space:nowrap; z-index:10; font-size:.85rem;
        }

        /* ===== Flyout (submenús al pasar el mouse estando retraído) ===== */
        .flyout{
        position:fixed; left:calc(var(--sbw-collapsed) + 8px);
        min-width:220px;
        background:#1b1b1b; border:1px solid var(--sidebar-border);
        border-radius:.6rem; padding:.35rem .35rem;
        box-shadow:0 12px 30px rgba(0,0,0,.35);
        z-index:1040; display:none;
        }
        .flyout.show{ display:block; }
        .flyout .side-link{ margin:.15rem; }
        .flyout .side-link .text{ opacity:1!important; }

        body{ background:#f0f0f0; }
        .page-body{
        padding-top:calc(var(--topbar-h) + 44px);  /* subimos un poco por el logo flotante */
        padding-left:calc(var(--sbw-collapsed) + 18px);
        min-height:100vh; color:#212529; background:#f0f0f0; transition:padding-left .25s ease;
        }
        .sidebar.expanded ~ .page-body{ padding-left:calc(var(--sbw-expanded) + 18px); }

        /* Móvil */
        @media (max-width: 992px){
        .sidebar{ transform:translateX(-100%); width:var(--sbw-expanded); }
        .sidebar.expanded{ transform:none; }
        .page-body{ padding-left:18px; padding-top:calc(var(--topbar-h) + 44px); }
        .flyout{ left:16px; } /* no lo usamos realmente en móvil */
        }


    @stack('styles')
  </style>
</head>

<body>
    <header class="topbar">
    <div class="left-actions">
        <button id="sbToggle" class="btn-toggle" aria-label="Abrir menú">
        <i class="bi bi-list"></i>
        </button>
    </div>

    <!-- Logo flotando más grande -->
    <div class="logo-wrap">
        <img class="logo" src="{{ asset('img/logo_taller.jpg') }}" alt="Logo"/>
    </div>

    <div class="right-actions">
        <a class="btn-home" href="{{ route('home') }}"><i class="bi bi-house"></i><span class="d-none d-sm-inline">Inicio</span></a>
        <a class="text-white" href="https://www.facebook.com/share/1CiUS3TA9y/?mibextid=wwXIfr" target="_blank" aria-label="Facebook"><i class="bi bi-facebook fs-5"></i></a>
        <a class="text-white" href="https://wa.me/50200000000" target="_blank" aria-label="WhatsApp"><i class="bi bi-whatsapp fs-5"></i></a>
    </div>
    </header>

  {{-- ===== Sidebar ===== --}}
  <aside id="sidebar" class="sidebar collapsed">
    <nav class="nav-section">

      {{-- Invitado --}}
      @guest
        <a href="{{ route('acceso') }}" class="side-link" data-title="Iniciar sesión">
          <i class="bi bi-box-arrow-in-right"></i>
          <span class="text">Iniciar Sesión</span>
        </a>
      @endguest

      {{-- Usuario (rol) + logout en dropdown simple --}}
      @auth
        @php $roleName = Auth::user()->getRoleNames()->first(); @endphp
        <a class="side-link" data-bs-toggle="collapse" href="#secUser" role="button" aria-expanded="false" aria-controls="secUser" data-title="{{ $roleName ?? 'Usuario' }}">
          <i class="bi bi-person-badge"></i>
          <span class="text">{{ $roleName ?? 'Usuario' }}</span>
          <i class="bi bi-caret-right-fill side-caret ms-auto"></i>
        </a>
        <div class="collapse ps-4" id="secUser">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="side-link w-100 text-start" type="submit" data-title="Cerrar sesión">
              <i class="bi bi-box-arrow-right"></i><span class="text">Cerrar sesión</span>
            </button>
          </form>
        </div>
      @endauth

      {{-- Panel Administrativo (solo admin) --}}
      @role('admin')
        <a class="side-link" data-bs-toggle="collapse" href="#secAdmin" role="button" aria-expanded="false" aria-controls="secAdmin" data-title="Panel Administrativo">
          <i class="bi bi-briefcase-fill"></i><span class="text">Panel Administrativo</span>
          <i class="bi bi-caret-right-fill side-caret"></i>
        </a>
        <div class="collapse ps-4" id="secAdmin">
          <a class="side-link" href="{{ route('dashboard') }}" data-title="Dashboard">
            <i class="bi bi-bar-chart"></i><span class="text">Dashboard</span>
          </a>
          <a class="side-link" href="{{ url('/inventario') }}" data-title="Contabilidad">
            <i class="bi bi-cash-coin"></i><span class="text">Contabilidad</span>
          </a>
          <a class="side-link" href="{{ route('users.index') }}" data-title="Usuarios">
            <i class="bi bi-people"></i><span class="text">Usuarios</span>
          </a>
        </div>
      @endrole

      {{-- Vehículos (admin|secretaria) --}}
      @hasanyrole('admin|secretaria')
        <a class="side-link" data-bs-toggle="collapse" href="#secVeh" role="button" aria-expanded="false" aria-controls="secVeh" data-title="Vehículos">
          <i class="bi bi-car-front-fill"></i><span class="text">Vehículos</span>
          <i class="bi bi-caret-right-fill side-caret"></i>
        </a>
        <div class="collapse ps-4" id="secVeh">
          <a class="side-link" href="{{ route('vehiculos.index') }}" data-title="Gestión de Vehículos">
            <i class="bi bi-list-ul"></i><span class="text">Gestión de Vehículos</span>
          </a>
          <a class="side-link" href="{{ route('marcas.index') }}" data-title="Gestión de Marcas">
            <i class="bi bi-tags"></i><span class="text">Gestión de Marcas</span>
          </a>
          <a class="side-link" href="{{ route('clientes.index') }}" data-title="Clientes">
            <i class="bi bi-person-rolodex"></i><span class="text">Clientes</span>
          </a>
        </div>
      @endhasanyrole

      {{-- Inspecciones 360 (mecánico|admin) --}}
      @hasanyrole('mecanico|admin')
        <a class="side-link" href="{{ route('inspecciones.start', [], false) ?? '#' }}" data-title="Inspecciones 360°">
          <i class="bi bi-ev-front-fill"></i><span class="text">Inspecciones 360°</span>
        </a>
      @endhasanyrole

      {{-- Órdenes de trabajo (admin|secretaria) --}}
      @hasanyrole('admin|secretaria')
        <a class="side-link" data-bs-toggle="collapse" href="#secOT" role="button" aria-expanded="false" aria-controls="secOT" data-title="Órdenes de Trabajo">
          <i class="bi bi-tools"></i><span class="text">Órdenes de Trabajo</span>
          <i class="bi bi-caret-right-fill side-caret"></i>
        </a>
        <div class="collapse ps-4" id="secOT">
          <a class="side-link" href="{{ route('ordenes.index') }}" data-title="Listado de Órdenes">
            <i class="bi bi-ev-front"></i><span class="text">Listado de Órdenes</span>
          </a>
          <a class="side-link" href="{{ route('ordenes.create') }}" data-title="Nueva Orden">
            <i class="bi bi-pencil-square"></i><span class="text">Nueva Orden de Trabajo</span>
          </a>
        </div>
      @endhasanyrole

      {{-- Bodega (solo admin) --}}
      @role('admin')
        <a class="side-link" data-bs-toggle="collapse" href="#secBod" role="button" aria-expanded="false" aria-controls="secBod" data-title="Bodega / Inventario">
          <i class="bi bi-box-seam"></i><span class="text">Bodega</span>
          <i class="bi bi-caret-right-fill side-caret"></i>
        </a>
        <div class="collapse ps-4" id="secBod">
          <a class="side-link" href="{{ route('insumos.index') }}" data-title="Inventario">
            <i class="bi bi-dropbox"></i><span class="text">Inventario</span>
          </a>
          <a class="side-link" href="{{ route('insumos.create') }}" data-title="Registro de Insumos">
            <i class="bi bi-pencil-square"></i><span class="text">Registro de Insumos</span>
          </a>
          <a class="side-link" href="{{ route('tipo-insumos.index') }}" data-title="Tipos de Insumo">
            <i class="bi bi-sliders"></i><span class="text">Gestionar Tipos Insumo</span>
          </a>
          <a class="side-link" href="{{ route('ordenes_compras.index') }}" data-title="Ordenes de Compras">
            <i class="bi bi-receipt"></i><span class="text">Ordenes de Compra</span>
          </a>
          <a class="side-link" href="{{ route('proveedores.index') }}" data-title="Proveedores">
            <i class="bi bi-truck"></i><span class="text">Gestión de Proveedores</span>
          </a>
        </div>
      @endrole

      {{-- Cotizaciones (admin|secretaria) --}}
      @hasanyrole('admin|secretaria')
        <a class="side-link" data-bs-toggle="collapse" href="#secCoti" role="button" aria-expanded="false" aria-controls="secCoti" data-title="Cotizaciones">
          <i class="bi bi-wallet2"></i><span class="text">Cotizaciones</span>
          <i class="bi bi-caret-right-fill side-caret"></i>
        </a>
        <div class="collapse ps-4" id="secCoti">
          <a class="side-link" href="{{ route('cotizaciones.index') }}" data-title="Listado">
            <i class="bi bi-list-ul"></i><span class="text">Listado de Cotizaciones</span>
          </a>
          @role('admin')
          <a class="side-link" href="{{ route('cotizaciones.create') }}" data-title="Nueva">
            <i class="bi bi-plus-square"></i><span class="text">Nueva Cotización</span>
          </a>
          @endrole
        </div>
      @endhasanyrole

    </nav>
  </aside>

  {{-- ===== Contenido ===== --}}
  <main class="page-body" id="pageBody">
    @yield('content')
  </main>

  {{-- Scripts --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  {{-- SweetAlert2 --}}
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    window.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
            icon: 'success',
            title: '¡Hecho!',
            text: @json(session('success')),
            confirmButtonText: 'OK',
            confirmButtonColor: '#9F3B3B',
            customClass: {
                popup: 'rounded-3'
            }
        });
    });
</script>
@endif

@if(session('error'))
<script>
    window.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: @json(session('error')),
            confirmButtonText: 'OK',
            confirmButtonColor: '#9F3B3B',
            customClass: {
                popup: 'rounded-3'
            }
        });
    });
</script>
@endif

@if(session('warning'))
<script>
    window.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: @json(session('warning')),
            confirmButtonText: 'OK',
            confirmButtonColor: '#9F3B3B',
            customClass: {
                popup: 'rounded-3'
            }
        });
    });
</script>
  @endif

  @if(session('warning') && !request()->is('marcas*'))
  <script>
      window.addEventListener('DOMContentLoaded', () => {
          Swal.fire({
              icon: 'warning',
              title: 'Advertencia',
              text: @json(session('warning')),
              confirmButtonText: 'OK',
              confirmButtonColor: '#9F3B3B',
              customClass: {
                  popup: 'rounded-3'
              }
          });
      });
  </script>
  @endif

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.4.2/dist/css/tom-select.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.2/dist/js/tom-select.complete.min.js"></script>


  @stack('scripts')

  <script>
      (function(){
      const sidebar = document.getElementById('sidebar');
      const btn = document.getElementById('sbToggle');
      const mq = window.matchMedia('(max-width: 992px)');

      const saved = localStorage.getItem('sb_state');
      if (saved === 'expanded') sidebar.classList.add('expanded');
      else sidebar.classList.remove('expanded');

      btn?.addEventListener('click', ()=>{
          sidebar.classList.toggle('expanded');
          localStorage.setItem('sb_state', sidebar.classList.contains('expanded') ? 'expanded' : 'collapsed');
      });

      // ---- FLYOUT para submenús cuando el sidebar está retraído ----
      const dropdownTriggers = document.querySelectorAll('.side-link[data-bs-toggle="collapse"]');

      dropdownTriggers.forEach(trigger=>{
          const targetSel = trigger.getAttribute('href') || trigger.dataset.bsTarget || trigger.getAttribute('data-bs-target');
          if(!targetSel) return;
          const submenu = document.querySelector(targetSel);
          if(!submenu) return;

          // Creamos contenedor flyout (clon visual del submenu)
          const fly = document.createElement('div'); fly.className = 'flyout';
          // Clonamos los side-link del submenu
          [...submenu.querySelectorAll('.side-link')].forEach(a=>{
          const c = a.cloneNode(true);
          c.querySelectorAll('.text').forEach(t=>t.style.opacity='1');
          fly.appendChild(c);
          });
          document.body.appendChild(fly);

          // Mostrar en hover sólo si el sidebar está RETRAÍDO y no en móvil
          let hideTimer = null;
          const showFly = () => {
          if (sidebar.classList.contains('expanded') || mq.matches) return;
          const r = trigger.getBoundingClientRect();
          fly.style.top = Math.max(12, r.top) + 'px';
          fly.classList.add('show');
          };
          const hideFly = () => { fly.classList.remove('show'); };

          trigger.addEventListener('mouseenter', showFly);
          trigger.addEventListener('mouseleave', ()=>{ hideTimer=setTimeout(hideFly,150); });
          fly.addEventListener('mouseenter', ()=>{ clearTimeout(hideTimer); });
          fly.addEventListener('mouseleave', hideFly);

          // En modo expandido usamos el collapse normal
          trigger.addEventListener('click', (e)=>{
          if (!sidebar.classList.contains('expanded')) {
              e.preventDefault(); // prevenimos apertura del collapse cuando está retraído
              showFly();
          }
          });
      });
      })();
  </script>

</body>
</html>

