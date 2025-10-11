@extends('layouts.app')

@push('styles')
<style>
  html, body { height: 100%; background:#f0f0f0 !important; }
  .page-body { min-height: calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width: 576px){ .page-body{ min-height: calc(100vh - 64px);} }

  /* Tarjeta centrada con sombra suave */
  .md-card{
    max-width: 620px;
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
         <!-- <div class="form-text">Año desde 1960 hasta {{ date('Y') + 1 }}</div>  -->
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