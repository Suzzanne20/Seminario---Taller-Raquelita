@extends('layouts.app')

@push('styles')
<style>
  html, body { height: 100%; background:#f0f0f0 !important; }
  .page-body { min-height: calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width: 576px){ .page-body{ min-height: calc(100vh - 64px);} }

  /* Tarjeta centrada con sombra suave */
  .md-card{
    max-width: 900px;
    margin: 32px auto 64px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
    padding: 28px;
  }
  .md-title{
    font-weight: 700;
    color: #C24242;
    text-align: center;
    margin-bottom: 18px;
  }

  /* Estilo "material" para los inputs */
  .form-control, .form-select{
    border: none;
    border-bottom: 2px solid #e6e6e6;
    border-radius: 0;
    background: transparent;
    padding-left: 0;
  }
  .form-control:focus, .form-select:focus{
    box-shadow: none;
    border-color: #3f51b5; /* azul material */
  }
  .form-label{
    font-size: .9rem;
    color: #6b7280;
  }

  .btn-md-primary{
    background:#9F3B3B; border:none;
  }
  .btn-md-primary:hover {
    background: #3f51b5; /* Azul en hover */
  }
  .btn-md-secondary{
    background:#e5e7eb; color:#111827; border:none;
  }
  
  /* Estilos para el botón de nueva marca */
  .btn-new-brand {
    background: transparent;
    border: 1px dashed #9F3B3B;
    color: #9F3B3B;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 0.875rem;
    margin-top: 8px;
    transition: all 0.2s ease;
  }
  .btn-new-brand:hover {
    background: #9F3B3B;
    color: white;
  }
  
  .select-with-button {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  /* Estilo para alerta de marca deshabilitada */
  .alert-warning {
    border-left: 4px solid #ffc107;
    background-color: #fffbf0;
    border-color: #ffeaa7;
  }

  /* Quitar flechas del input number */
  input[type=number]::-webkit-outer-spin-button,
  input[type=number]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }
  input[type=number] {
    -moz-appearance: textfield;
  }

  /* Estilos para secciones de campos */
  .section-title {
    background: linear-gradient(135deg, #9F3B3B, #C24242);
    color: white;
    padding: 12px 16px;
    border-radius: 8px;
    margin: 25px 0 15px 0;
    font-weight: 600;
    font-size: 1.1rem;
  }
  .section-subtitle {
    background: #f8f9fa;
    color: #495057;
    padding: 8px 12px;
    border-radius: 6px;
    margin: 20px 0 10px 0;
    font-weight: 500;
    border-left: 4px solid #9F3B3B;
  }
</style>
@endpush

@section('content')
<div class="container">
  <div class="md-card">
    <h2 class="md-title">Registrar Vehículo</h2>

    {{-- Errores de validación --}}
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('vehiculos.store') }}" method="POST" novalidate id="vehiculoForm">
      @csrf

      {{-- SECCIÓN: INFORMACIÓN BÁSICA DEL VEHÍCULO --}}
      <div class="section-title">
        <i class="bi bi-car-front me-2"></i>Información Básica del Vehículo
      </div>

      <div class="mb-3">
        <label class="form-label">Placa</label>
        <input type="text"
               name="placa"
               value="{{ old('placa') }}"
               maxlength="7"
               class="form-control"
               placeholder="Ej: P123ABC"
               required>
        <div class="form-text">Formato: 1 letra + 3 números + 3 letras (P123ABC)</div>
      </div>

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Modelo</label>
          <input type="number" 
                 name="modelo" 
                 value="{{ old('modelo') }}" 
                 class="form-control" 
                 min="1960" 
                 max="{{ date('Y') + 1 }}"
                 required
                 onkeydown="return event.keyCode !== 69 && event.keyCode !== 189">
        </div>
        <div class="col-md-4">
          <label class="form-label">Línea</label>
          <input type="text" name="linea" value="{{ old('linea') }}" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Motor</label>
          <input type="text" name="motor" value="{{ old('motor') }}" class="form-control" required>
        </div>
      </div>

      <div class="row g-3 mt-1">
        <div class="col-md-6">
          <label class="form-label">Cilindraje</label>
          <input type="number" 
                 step="0.01" 
                 name="cilindraje" 
                 value="{{ old('cilindraje') }}" 
                 class="form-control" 
                 min="0.1"
                 required
                 onkeydown="return event.keyCode !== 69 && event.keyCode !== 189">
        </div>
        <div class="col-md-6">
          <label class="form-label">Marca</label>
          <div class="select-with-button">
            <select name="marca_id" class="form-select" required id="marcaSelect">
              <option value="" selected disabled>Seleccione una marca</option>
              @foreach($marcas as $m)
                <option value="{{ $m->id }}" 
                        @selected(old('marca_id')==$m->id)
                        data-activa="{{ $m->activo ? '1' : '0' }}"
                        data-nombre="{{ $m->nombre }}"
                        class="{{ !$m->activo ? 'text-warning' : '' }}">
                  {{ $m->nombre }}{{ !$m->activo ? ' (Deshabilitada)' : '' }}
                </option>
              @endforeach
            </select>
            <button type="button" class="btn btn-new-brand" data-bs-toggle="modal" data-bs-target="#marcaModal">
              + Agregar Marca Nueva
            </button>
          </div>
          {{-- Aquí aparecerá la advertencia de marca deshabilitada --}}
          <div id="alertaMarcaDeshabilitada" class="alert alert-warning mt-2 d-none">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <span id="textoAlerta"></span>
          </div>
        </div>
      </div>

      {{-- SECCIÓN: SISTEMA DE LUBRICACIÓN --}}
      <div class="section-title">
        <i class="bi bi-droplet me-2"></i>Sistema de Lubricación
      </div>

      <div class="section-subtitle">Aceite del Motor</div>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Cantidad Aceite Motor</label>
          <input type="text" name="cantidad_aceite_motor" value="{{ old('cantidad_aceite_motor') }}" class="form-control" placeholder="Ej: 5 litros">
        </div>
        <div class="col-md-4">
          <label class="form-label">Marca Aceite</label>
          <input type="text" name="marca_aceite" value="{{ old('marca_aceite') }}" class="form-control" placeholder="Ej: Mobil 1">
        </div>
        <div class="col-md-4">
          <label class="form-label">Tipo Aceite</label>
          <input type="text" name="tipo_aceite" value="{{ old('tipo_aceite') }}" class="form-control" placeholder="Ej: 5W-30">
        </div>
      </div>

      <div class="row g-3 mt-2">
        <div class="col-md-6">
          <label class="form-label">Filtro Aceite</label>
          <input type="text" name="filtro_aceite" value="{{ old('filtro_aceite') }}" class="form-control" placeholder="Ej: PH3683">
        </div>
        <div class="col-md-6">
          <label class="form-label">Filtro Aire</label>
          <input type="text" name="filtro_aire" value="{{ old('filtro_aire') }}" class="form-control" placeholder="Ej: A2024">
        </div>
      </div>

        {{-- SECCIÓN: CAJA DE CAMBIOS --}}
        <div class="section-title">
            <i class="bi bi-gear me-2"></i>Caja de Cambios
        </div>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Cantidad Aceite CC</label>
                <input type="text" name="cantidad_aceite_cc" value="{{ old('cantidad_aceite_cc') }}" class="form-control" placeholder="Ej: 2.5 litros">
            </div>
            <div class="col-md-3">
                <label class="form-label">Marca CC</label>
                <input type="text" name="marca_cc" value="{{ old('marca_cc') }}" class="form-control" placeholder="Ej: Castrol">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo Aceite CC</label>
                <input type="text" name="tipo_aceite_cc" value="{{ old('tipo_aceite_cc') }}" class="form-control" placeholder="Ej: ATF">
            </div>
            <div class="col-md-3">
                <label class="form-label">Filtro Aceite CC</label>
                <input type="text" name="filtro_aceite_cc" value="{{ old('filtro_aceite_cc') }}" class="form-control" placeholder="Ej: TF087">
            </div>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <label class="form-label">Filtro de Enfriador</label>
                <input type="text" name="filtro_de_enfriador" value="{{ old('filtro_de_enfriador') }}" class="form-control" placeholder="Ej: CF123">
            </div>
            <div class="col-md-6">
                <label class="form-label">Tipo Caja</label>
                <input type="text" name="tipo_caja" value="{{ old('tipo_caja') }}" class="form-control" placeholder="Ej: Automática 6 velocidades">
            </div>
        </div>

      {{-- SECCIÓN: DIFERENCIAL --}}
      <div class="section-title">
        <i class="bi bi-gear-fill me-2"></i>Diferencial
      </div>

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Cantidad Aceite Diferencial</label>
          <input type="text" name="cantidad_aceite_diferencial" value="{{ old('cantidad_aceite_diferencial') }}" class="form-control" placeholder="Ej: 1.2 litros">
        </div>
        <div class="col-md-4">
          <label class="form-label">Marca Aceite D</label>
          <input type="text" name="marca_aceite_d" value="{{ old('marca_aceite_d') }}" class="form-control" placeholder="Ej: Shell">
        </div>
        <div class="col-md-4">
          <label class="form-label">Tipo Aceite D</label>
          <input type="text" name="tipo_aceite_d" value="{{ old('tipo_aceite_d') }}" class="form-control" placeholder="Ej: 80W-90">
        </div>
      </div>

      {{-- SECCIÓN: TRANSFER --}}
      <div class="section-title">
        <i class="bi bi-gear-wide me-2"></i>Transfer
      </div>

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Cantidad Aceite Transfer</label>
          <input type="text" name="cantidad_aceite_transfer" value="{{ old('cantidad_aceite_transfer') }}" class="form-control" placeholder="Ej: 1.5 litros">
        </div>
        <div class="col-md-4">
          <label class="form-label">Marca Aceite T</label>
          <input type="text" name="marca_aceite_t" value="{{ old('marca_aceite_t') }}" class="form-control" placeholder="Ej: Valvoline">
        </div>
        <div class="col-md-4">
          <label class="form-label">Tipo Aceite T</label>
          <input type="text" name="tipo_aceite_t" value="{{ old('tipo_aceite_t') }}" class="form-control" placeholder="Ej: 75W-140">
        </div>
      </div>

      {{-- SECCIÓN: FILTROS Y COMPONENTES --}}
      <div class="section-title">
        <i class="bi bi-funnel me-2"></i>Filtros y Componentes
      </div>

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Filtro Cabina</label>
          <input type="text" name="filtro_cabina" value="{{ old('filtro_cabina') }}" class="form-control" placeholder="Ej: CF072">
        </div>
        <div class="col-md-4">
          <label class="form-label">Filtro Diesel</label>
          <input type="text" name="filtro_diesel" value="{{ old('filtro_diesel') }}" class="form-control" placeholder="Ej: P550935">
        </div>
        <div class="col-md-4">
          <label class="form-label">Contra Filtro Diesel</label>
          <input type="text" name="contra_filtro_diesel" value="{{ old('contra_filtro_diesel') }}" class="form-control" placeholder="Ej: WK731/6">
        </div>
      </div>

      {{-- SECCIÓN: FRENOS Y REPUESTOS MULTIPLES --}}
      <div class="section-title">
        <i class="bi bi-lightning-charge me-2"></i>Frenos Y Repuestos Multiples
      </div>

      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Candelas</label>
          <input type="text" name="candelas" value="{{ old('candelas') }}" class="form-control" placeholder="Ej: BKR6E-11">
        </div>
        <div class="col-md-3">
          <label class="form-label">Pastillas Delanteras</label>
          <input type="text" name="pastillas_delanteras" value="{{ old('pastillas_delanteras') }}" class="form-control" placeholder="Ej: P06065">
        </div>
        <div class="col-md-3">
          <label class="form-label">Pastillas Traseras</label>
          <input type="text" name="pastillas_traseras" value="{{ old('pastillas_traseras') }}" class="form-control" placeholder="Ej: P06070">
        </div>
        <div class="col-md-3">
          <label class="form-label">Fajas</label>
          <input type="text" name="fajas" value="{{ old('fajas') }}" class="form-control" placeholder="Ej: 6PK2280">
        </div>
      </div>

      <div class="row g-3 mt-2">
        <div class="col-md-6">
          <label class="form-label">Aceite Hidráulico</label>
          <input type="text" name="aceite_hidraulico" value="{{ old('aceite_hidraulico') }}" class="form-control" placeholder="Ej: DOT 4">
        </div>
      </div>

      {{-- BOTONES DE ACCIÓN --}}
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-md-primary px-4 text-white">Guardar Vehículo</button>
        <a href="{{ route('vehiculos.index') }}" class="btn btn-md-secondary px-4">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<!-- Modal para agregar nueva marca -->
<div class="modal fade" id="marcaModal" tabindex="-1" aria-labelledby="marcaModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="marcaModalLabel">Agregar Nueva Marca</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="marcaForm">
          @csrf
          <div class="mb-3">
            <label for="nombreMarca" class="form-label">Nombre de la Marca</label>
            <input type="text" class="form-control" id="nombreMarca" name="nombre" required 
                   placeholder="Ej: Toyota, Honda, Ford...">
            <div class="form-text">Ingrese el nombre de la nueva marca.</div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-md-primary" onclick="guardarMarca()" id="btnGuardarMarca">
          Guardar Marca
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Helpers UI --}}
<script>
  // Placa en mayúsculas automáticamente y validación en tiempo real
  document.querySelector('input[name="placa"]').addEventListener('input', function(){
    this.value = this.value.toUpperCase();
    validarPlaca(this.value);
  });

  // Validación del formato de placa
  function validarPlaca(placa) {
    const placaInput = document.querySelector('input[name="placa"]');
    const formatoValido = /^[A-Z][0-9]{3}[A-Z]{3}$/.test(placa);
    
    if (placa.length > 0 && !formatoValido) {
      placaInput.style.borderColor = '#dc3545';
      mostrarErrorPlaca('Formato incorrecto.');
    } else {
      placaInput.style.borderColor = '';
      ocultarErrorPlaca();
    }
  }

  function mostrarErrorPlaca(mensaje) {
    let errorDiv = document.getElementById('error-placa');
    if (!errorDiv) {
      errorDiv = document.createElement('div');
      errorDiv.id = 'error-placa';
      errorDiv.className = 'text-danger small mt-1';
      document.querySelector('input[name="placa"]').closest('.mb-3').appendChild(errorDiv);
    }
    errorDiv.textContent = mensaje;
  }

  function ocultarErrorPlaca() {
    const errorDiv = document.getElementById('error-placa');
    if (errorDiv) {
      errorDiv.remove();
    }
  }

  // Validación antes de enviar el formulario
  document.getElementById('vehiculoForm').addEventListener('submit', function(e) {
    const placa = document.querySelector('input[name="placa"]').value;
    const formatoValido = /^[A-Z][0-9]{3}[A-Z]{3}$/.test(placa);
    
    if (!formatoValido) {
      e.preventDefault();
      mostrarErrorPlaca('❌ Formato de placa inválido. Debe ser: 1 letra + 3 números + 3 letras (ejemplo: P123ABC)');
      document.querySelector('input[name="placa"]').focus();
    }
  });

  // ADVERTENCIA CUANDO SELECCIONAN MARCA DESHABILITADA
  document.getElementById('marcaSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const marcaActiva = selectedOption.getAttribute('data-activa');
    const nombreMarca = selectedOption.getAttribute('data-nombre');
    const alerta = document.getElementById('alertaMarcaDeshabilitada');
    const textoAlerta = document.getElementById('textoAlerta');
    
    if (marcaActiva === '0') {
      textoAlerta.textContent = `La marca "${nombreMarca}" está deshabilitada. El vehículo se registrará pero no se mostrará en el listado principal.`;
      alerta.classList.remove('d-none');
    } else {
      alerta.classList.add('d-none');
    }
  });

  // FUNCIÓN MEJORADA PARA GUARDAR MARCA CON MANEJO DE ERRORES EN ESPAÑOL
  function guardarMarca() {
    const nombre = document.getElementById('nombreMarca').value.trim();
    const select = document.getElementById('marcaSelect');
    const btnGuardar = document.getElementById('btnGuardarMarca');
    
    if (!nombre) {
      mostrarSweetAlert('error', 'Campo requerido', 'Por favor ingrese el nombre de la marca');
      return;
    }

    // Mostrar loading
    const originalText = btnGuardar.innerHTML;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';
    btnGuardar.disabled = true;

    // Enviar solicitud AJAX
    fetch('{{ route("marcas.store") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        nombre: nombre
      })
    })
    .then(response => {
      if (!response.ok) {
        return response.json().then(err => { throw err; });
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        // Agregar la nueva opción al select y seleccionarla
        const nuevaOpcion = new Option(data.marca.nombre, data.marca.id, true, true);
        nuevaOpcion.setAttribute('data-activa', '1');
        nuevaOpcion.setAttribute('data-nombre', data.marca.nombre);
        select.add(nuevaOpcion);
        
        // Cerrar modal y limpiar formulario
        const modal = bootstrap.Modal.getInstance(document.getElementById('marcaModal'));
        modal.hide();
        document.getElementById('marcaForm').reset();
        
        // Mostrar mensaje de éxito
        mostrarSweetAlert('success', '¡Éxito!', 'Marca agregada correctamente');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      let errorMsg = 'Error al guardar la marca';
      
      if (error.errors && error.errors.nombre) {
        // Traducir mensaje de error único
        if (error.errors.nombre[0].includes('already been taken')) {
          errorMsg = 'Esta marca ya existe en el sistema. Por favor ingrese un nombre diferente.';
        } else {
          errorMsg = error.errors.nombre[0];
        }
      }
      
      mostrarSweetAlert('error', 'Error', errorMsg);
    })
    .finally(() => {
      // Restaurar botón
      btnGuardar.innerHTML = originalText;
      btnGuardar.disabled = false;
    });
  }

  // FUNCIÓN MEJORADA PARA MOSTRAR ALERTAS CON SWEETALERT2
  function mostrarSweetAlert(icon, title, text) {
    Swal.fire({
      icon: icon,
      title: title,
      text: text,
      confirmButtonText: 'Entendido',
      confirmButtonColor: '#9F3B3B',
      customClass: {
        popup: 'rounded-3'
      }
    });
  }

  // Función para mostrar alertas simples (mantener compatibilidad)
  function showAlert(message, type = 'info') {
    // Crear elemento de alerta
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show`;
    alertDiv.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insertar al inicio del contenedor
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-eliminar después de 5 segundos
    setTimeout(() => {
      if (alertDiv.parentElement) {
        alertDiv.remove();
      }
    }, 5000);
  }

  // Limpiar formulario del modal cuando se cierre
  document.getElementById('marcaModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('marcaForm').reset();
  });

  // Permitir enviar con Enter en el modal
  document.getElementById('nombreMarca').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      guardarMarca();
    }
  });

  // Enfocar el input cuando se abre el modal
  document.getElementById('marcaModal').addEventListener('shown.bs.modal', function () {
    document.getElementById('nombreMarca').focus();
  });

  // Mostrar advertencia si hay una marca seleccionada al cargar la página (en caso de error de validación)
  document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('marcaSelect');
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption && selectedOption.value !== '') {
      const marcaActiva = selectedOption.getAttribute('data-activa');
      const nombreMarca = selectedOption.getAttribute('data-nombre');
      if (marcaActiva === '0') {
        const alerta = document.getElementById('alertaMarcaDeshabilitada');
        const textoAlerta = document.getElementById('textoAlerta');
        textoAlerta.textContent = `La marca "${nombreMarca}" está deshabilitada. El vehículo se registrará pero no se mostrará en el listado principal.`;
        alerta.classList.remove('d-none');
      }
    }
  });
</script>
@endsection