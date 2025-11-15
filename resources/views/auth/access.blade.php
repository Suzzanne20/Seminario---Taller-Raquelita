@extends('layouts.app')

@section('title','Acceso – Taller Raquelita')

@section('content')
    <style>
        :root{
            --brand-primary:#B23940; --brand-dark:#000; --brand-bg:#F3EEEE;
            --brand-muted:#B06B74; --brand-accent:#D44A52;
            --radius:18px; --shadow:0 10px 30px rgba(0,0,0,.08);
        }
        *{box-sizing:border-box}
        .split{display:grid; grid-template-columns:1.1fr 1fr; max-width:1200px; margin:0 auto; min-height:calc(100vh - 72px)}
        @media (max-width: 960px){ .split{grid-template-columns:1fr} .hero{display:none} }
        .hero{position:relative; overflow:hidden; background:#111; color:#fff; border-radius:18px; margin:16px}
        .hero img{position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:.65; filter:contrast(1.05) saturate(1.05)}
        .hero::after{content:""; position:absolute; inset:0; background:none;}
        .hero-badge{position:absolute; top:24px; left:24px; display:flex; align-items:center; gap:12px; background:rgba(0,0,0,.45); padding:10px 14px; border-radius:999px; backdrop-filter: blur(6px); font-weight:600;}
        .hero-badge .dot{width:10px; height:10px; border-radius:999px; background:var(--brand-accent); box-shadow:0 0 0 4px rgba(212,74,82,.35)}
        .hero-caption{position:absolute; left:24px; bottom:24px; right:24px;}
        .hero-caption h2{margin:0 0 6px; font-size:28px; font-weight:800}

        .panel{display:flex; align-items:center; justify-content:center; padding:24px}
        .card{width:100%; max-width:480px; background:#fff; border-radius:24px; box-shadow:var(--shadow); padding:28px 26px 24px;}
        .brand{display:flex; align-items:center; gap:12px; margin-bottom:14px;}
        .brand .logo{width:56px; height:56px; border-radius:14px; object-fit:contain; background:#fff; border:1px solid #eee; padding:6px; box-shadow:0 8px 18px rgba(212,74,82,.25);}
        .brand .name{font-weight:800; color:var(--brand-dark); font-size:20px}
        .brand .tag{font-size:12px; color:var(--brand-muted); font-weight:600}

        .tabs{display:flex; gap:8px; margin:10px 0 18px}
        .tab-btn{appearance:none; border:1px solid #eee; background:#fff; padding:10px 14px; border-radius:12px; cursor:pointer; font-weight:700; color:#555; transition:.2s}
        .tab-btn[aria-selected="true"]{ background:var(--brand-bg); border-color:var(--brand-accent); color:#111}
        .tab-btn:focus-visible{outline:3px solid var(--brand-accent); outline-offset:2px}
        form{display:grid; gap:14px}
        label{font-size:13px; font-weight:700; color:#333}
        .input{position:relative}
        input[type="text"], input[type="email"], input[type="password"]{width:100%; padding:14px 14px 14px 44px; border-radius:12px; border:1px solid #e7e3e3; background:#fff; color:#222; transition:border-color .15s}
        input::placeholder{color:#b9b3b3}
        .icon{position:absolute; left:12px; top:50%; transform: translateY(-50%); opacity:.55}
        .actions{display:flex; gap:10px; align-items:center; justify-content:space-between}
        .link{color:var(--brand-primary); font-weight:700; text-decoration:none}
        .link:hover{text-decoration:underline}
        .btn{appearance:none; border:none; cursor:pointer; border-radius:12px; padding:14px 16px; font-weight:800; background:linear-gradient(180deg,var(--brand-primary), var(--brand-accent)); color:#fff; box-shadow:0 10px 20px rgba(178,57,64,.28)}
        .btn-secondary{background:#fff; border:2px solid var(--brand-muted); color:#111}
        .note{margin-top:8px; font-size:12px; color:#6b6464}
        .alert{border-radius:12px; padding:12px 14px; font-weight:600}
        .alert-danger{background:#fff1f2; color:#991b1b; border:1px solid #fecdd3}
        .alert-success{background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0}
    </style>

    <div class="split">
        {{-- Lado izquierdo con foto --}}
        <aside class="hero" aria-hidden="true">
            <img alt="Taller mecánico" src="{{ asset('img/Login.jpg') }}"/>
            <div class="hero-badge"><span class="dot"></span> Taller Raquelita</div>
            <div class="hero-caption">
                <h2>Servicio técnico con garantía</h2>
                <p>Agilidad, transparencia y seguridad de punta.</p>
            </div>
        </aside>

        {{-- Lado derecho: tarjeta con tabs --}}
        <section class="panel">
            <div class="card" id="authCard">
                <header class="brand">
                    <img src="https://raw.githubusercontent.com/Suzzanne20/ResourceNekoStation/refs/heads/main/1757173060470.png" class="logo" alt="Logo Taller"/>
                    <div>
                        <div class="name">Taller Raquelita</div>
                        <div class="tag">Acceso a miembros</div>
                    </div>
                </header>

                {{-- Mensajes de estado Laravel (recuperación, etc.) --}}
                @if (session('status'))
                    <div class="alert alert-success mb-2">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Errores de validación --}}
                @if ($errors->any())
                    <div class="alert alert-danger mb-2">
                        <ul class="m-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li class="m-0">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Tabs --}}
                <nav class="tabs" role="tablist" aria-label="Autenticación">
                    <button class="tab-btn" role="tab" aria-selected="true" aria-controls="panel-login" id="tab-login">Iniciar sesión</button>
                    <button class="tab-btn" role="tab" aria-selected="false" aria-controls="panel-recover" id="tab-recover">Recuperar</button>
                </nav>

                {{-- LOGIN (POST /login) --}}
                <section id="panel-login" role="tabpanel" aria-labelledby="tab-login">
                    <form id="form-login" method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="input">
                            <label for="login-email">Correo</label>
                            <svg class="icon" width="18" height="18" viewBox="0 0 24 24"><rect x="4" y="6" width="16" height="12" stroke="currentColor" stroke-width="1" fill="none"/><path d="M4 7l8 5 8-5" stroke="currentColor" stroke-width="1" fill="none"/></svg>
                            <input id="login-email" name="email" required type="email" placeholder="correo@gmail.com" value="{{ old('email') }}"/>
                        </div>
                        <div class="input">
                            <label for="login-pass">Contraseña</label>
                            <svg class="icon" width="18" height="18" viewBox="0 0 24 24"><rect x="3" y="10" width="18" height="10" rx="2" stroke="currentColor" stroke-width="1" fill="none"/><path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1" fill="none"/></svg>
                            <input id="login-pass" name="password" required type="password" placeholder="Tu contraseña"/>
                            <button type="button" id="togglePassword"
                                    style="position:absolute; right:10px; top:70%; transform:translateY(-50%); background:none; border:none; cursor:pointer;">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                        <div class="actions">
                            <label style="display:flex; align-items:center; gap:8px; font-size:13px">
                                <input type="checkbox" name="remember"> Recuérdame
                            </label>
                            <a class="link" href="#" id="goto-recover">¿Olvidaste tu contraseña?</a>
                        </div>
                        <div class="mt-3">
                        <div class="cf-turnstile"
                            data-sitekey="{{ config('services.turnstile.site_key') }}"
                            data-theme="auto"  {{-- light|dark|auto --}}
                            data-appearance="always">
                        </div>
                        @error('cf-turnstile-response')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        </div>
                        <button class="btn" type="submit">Acceder</button>
                    </form>
                </section>

                {{-- RECUPERACIÓN (POST /forgot-password → password.email) --}}
                <section id="panel-recover" role="tabpanel" aria-labelledby="tab-recover" hidden>
                    <form id="form-recover" method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="input">
                            <label for="rec-email">Correo para recuperar</label>
                            <svg class="icon" width="18" height="18" viewBox="0 0 24 24"><rect x="4" y="6" width="16" height="12" stroke="currentColor" stroke-width="1" fill="none"/><path d="M4 7l8 5 8-5" stroke="currentColor" stroke-width="1" fill="none"/></svg>
                            <input id="rec-email" name="email" required type="email" placeholder="correo@gmail.com" value="{{ old('email') }}"/>
                        </div>
                        <div class="actions" style="justify-content:space-between">
                            <button class="btn" type="submit">Enviar enlace</button>
                            <button class="btn btn-secondary" type="button" id="back-login">Volver a login</button>
                        </div>
                    </form>
                </section>
            </div>
        </section>
    </div>
{{-- Cloudflare Turnstile --}}
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @push('scripts')
        <script>
            const tabs = {
                login:   { btn: document.getElementById('tab-login'),   panel: document.getElementById('panel-login') },
                recover: { btn: document.getElementById('tab-recover'), panel: document.getElementById('panel-recover') },
            };
            function switchTab(name){
                Object.entries(tabs).forEach(([key,{btn,panel}])=>{
                    const sel = key===name;
                    btn.setAttribute('aria-selected', String(sel));
                    panel.hidden = !sel;
                });
                document.getElementById('authCard').scrollIntoView({behavior:'smooth', block:'start'});
            }
            tabs.login.btn.addEventListener('click',()=>switchTab('login'));
            tabs.recover.btn.addEventListener('click',()=>switchTab('recover'));

            const goRecover = document.getElementById('goto-recover');
            if(goRecover){ goRecover.addEventListener('click',(e)=>{ e.preventDefault(); switchTab('recover'); }); }
            const backLogin = document.getElementById('back-login');
            if(backLogin){ backLogin.addEventListener('click',()=>switchTab('login')); }

            switchTab('login');
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const togglePassword = document.getElementById('togglePassword');
                const passwordInput = document.getElementById('login-pass');

                if (togglePassword && passwordInput) {
                    togglePassword.addEventListener('click', () => {
                        const type = passwordInput.type === 'password' ? 'text' : 'password';
                        passwordInput.type = type;

                        // Alterna ícono del botón
                        togglePassword.innerHTML = type === 'password'
                            ? '<i class="fa fa-eye"></i>'
                            : '<i class="fa fa-eye-slash"></i>';
                    });
                }
            });
        </script>
    @endpush
@endsection
