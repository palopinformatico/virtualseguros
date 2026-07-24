<style>
.step-container {
    display: flex;
    justify-content: space-around;
    margin-bottom: 20px;
}

.step {
    font-weight: bold;
    color: #999;
}

.step.active {
    color: green;
    border-bottom: 2px solid green;
}

.card {
    max-width: 700px;
    margin: auto;
    background-color: #fff;
    padding: 30px;
}

.hidden {
    display: none;
}

.btn-success {
	background-color: #3CB043 !important;
	color: white;
	border: none;
	padding: 10px 20px;
	border-radius: 5px;
	cursor: pointer;
	font-size: 0.9em;
	transition: background-color 0.3s ease;
}

.btn-success:hover {
	background-color: #5a6268;
}
</style>

<script>
  jQuery(document).ready(function() {
    jQuery('#marca').select2({
      placeholder: 'Buscar marca...',
      allowClear: true
    });
  });
  
  jQuery(document).ready(function() {
    jQuery('#modelo').select2({
      placeholder: 'Buscar modelo...',
      allowClear: true
    });
  });

document.addEventListener('DOMContentLoaded', function () {
  const marcaSelect = document.querySelector('[name="marca"]');

  // Limpia las opciones actuales y muestra el mensaje de carga
  marcaSelect.innerHTML = ''; // vacía por si acaso
  const loadingOption = document.createElement('option');
  loadingOption.textContent = 'Cargando datos...';
  loadingOption.disabled = true;
  loadingOption.selected = true;
  marcaSelect.appendChild(loadingOption);

  // Llamada AJAX
  fetch('/wp-admin/admin-ajax.php?action=get_marcas_sura')
    .then(res => res.json())
    .then(res => {
      if (!res.estado) throw new Error(res.respuesta || 'Error en respuesta');

      // Limpiar opciones
      marcaSelect.innerHTML = '';
      const opc = document.createElement('option');
      opc.value = '';
      opc.textContent = "Seleccione una marca de vehículo";
      marcaSelect.appendChild(opc);

      // Agregar las marcas
      res.data.forEach(marca => {
        const opt = document.createElement('option');
        opt.value = marca.codigo;
        opt.textContent = marca.nombre;
        marcaSelect.appendChild(opt);
      });
    })
    .catch(err => {
      console.error('Error al cargar marcas:', err);

      // Mostrar mensaje de error
      marcaSelect.innerHTML = '';
      const errorOption = document.createElement('option');
      errorOption.textContent = 'Error al cargar marcas';
      errorOption.disabled = true;
      errorOption.selected = true;
      marcaSelect.appendChild(errorOption);
    });

  // Al seleccionar una marca, obtener modelos
  marcaSelect.onchange = function (){
    const select = this;
    const nombre = select.options[select.selectedIndex].text;
    document.getElementById('nombre_marca').value = nombre;
    
	const modeloSelect = document.querySelector('[name="modelo"]');
    const codigoMarca = this.value;

    // Limpia las opciones actuales y muestra el mensaje de carga
    modeloSelect.innerHTML = ''; // vacía por si acaso
    const loadingOption = document.createElement('option');
    loadingOption.textContent = 'Cargando datos...';
    loadingOption.disabled = true;
    loadingOption.selected = true;
    modeloSelect.appendChild(loadingOption);

    if (!codigoMarca) return;

    fetch(`/wp-admin/admin-ajax.php?action=get_modelos_sura&marca=${codigoMarca}`)
      .then(res => res.json())
      .then(res => {
        if (!res.estado) return console.error(res.respuesta);
        const modelos = res.data;

        modeloSelect.innerHTML = '';
        const opc = document.createElement('option');
        opc.value = '';
        opc.textContent = "Seleccione un modelo de vehículo";
        modeloSelect.appendChild(opc);
      
        modelos.forEach(modelo => {
          const option = document.createElement('option');
          option.value = modelo.codigo;
          option.textContent = modelo.nombre;
          option.dataset.codigoSOL = modelo.codigoSOL; // segundo código
          modeloSelect.appendChild(option);
        });
        modeloSelect.disabled = false;
      })
      .catch(err => {
        console.error('Error al cargar modelos:', err);

        // Mostrar mensaje de error
        modeloSelect.innerHTML = '';
        const errorOption = document.createElement('option');
        errorOption.textContent = 'Error al cargar modelos';
        errorOption.disabled = true;
        errorOption.selected = true;
        modeloSelect.appendChild(errorOption);
      });
  
      modeloSelect.onchange = function (){
          const select = this;
          const nombre = select.options[select.selectedIndex].text;
         document.getElementById('nombre_modelo').value = nombre;
      };
  };
  
  const regionSelect = document.querySelector('[name="region"]');

  // Limpiar opciones
  regionSelect.innerHTML = '';
  const loadingOption2 = document.createElement('option');
  loadingOption2.textContent = 'Cargando datos...';
  loadingOption2.disabled = true;
  loadingOption2.selected = true;
  regionSelect.appendChild(loadingOption2);

  // Cargar regiones
  fetch('/wp-admin/admin-ajax.php?action=get_regiones_sura')
    .then(res => res.json())
    .then(res => {
      if (!res.estado) return console.error(res.respuesta);
      const regiones = res.data;
	  
      // Limpiar opciones
      regionSelect.innerHTML = '';

      regiones.forEach(region => {
		const emptyOption = document.createElement('option');
		emptyOption.value = region.codigo;
		emptyOption.textContent = region.nombre;
		regionSelect.appendChild(emptyOption);      });
    })
    .catch(err => {
      console.error('Error al cargar regiones:', err);

      // Mostrar mensaje de error
      regionSelect.innerHTML = '';
      const errorOption = document.createElement('option');
      errorOption.textContent = 'Error al cargar regiones';
      errorOption.disabled = true;
      errorOption.selected = true;
      regionSelect.appendChild(errorOption);
    });

  // Al seleccionar una region, obtener comunas
  regionSelect.onchange = function (){
    const select = jQuery('#select-4');
    select.empty().append('<option></option>'); // limpia y agrega opción vacía
	const comunaSelect = document.querySelector('[name="comuna"]');
    const codigoRegion = this.value;

    // Limpia las opciones actuales y muestra el mensaje de carga
    comunaSelect.innerHTML = ''; // vacía por si acaso
    const loadingOption = document.createElement('option');
    loadingOption.textContent = 'Cargando datos...';
    loadingOption.disabled = true;
    loadingOption.selected = true;
    comunaSelect.appendChild(loadingOption);

    if (!codigoRegion) return;

    fetch(`/wp-admin/admin-ajax.php?action=get_comunas_sura&region=${codigoRegion}`)
      .then(res => res.json())
      .then(res => {
        if (!res.estado) return console.error(res.respuesta);
        const comunas = res.data;

        comunaSelect.innerHTML = '';
        const opc = document.createElement('option');
        opc.value = '';
        opc.textContent = "Seleccione una comuna";
        comunaSelect.appendChild(opc);
        
        comunas.forEach(comuna => {
          const option = document.createElement('option');
          option.value = comuna.codigo;
          option.textContent = comuna.nombre;
          comunaSelect.appendChild(option);
        });
        comunaSelect.disabled = false;
      })
      .catch(err => {
        console.error('Error al cargar modelos:', err);

        // Mostrar mensaje de error
        comunaSelect.innerHTML = '';
        const errorOption = document.createElement('option');
        errorOption.textContent = 'Error al cargar modelos';
        errorOption.disabled = true;
        errorOption.selected = true;
        comunaSelect.appendChild(errorOption);
      });
  
      comunaSelect.onchange = function (){
          const select = this;
          const nombre = select.options[select.selectedIndex].text;
         document.getElementById('nombre_comuna').value = nombre;
      };
  };
});

</script>

<div class="card shadow">
    <h3 class="text-center mb-4 fw-bold">Cotizador Seguro Automotriz</h3>

    <div class="step-container">
        <div class="step step-1 active">Vehículo</div>
        <div class="step step-2">Personal</div>
    </div>

    <form id="cotizador-form" method="post" action="/consultas">
        <!-- Paso 1 -->
        <div id="paso-vehiculo">
            <div class="mb-3 fw-bold">
                <label>Elige tu marca <span style="color: red;">*</label>
                <select name="marca" id="marca" required>
                    <option value="">Selecciona una opción</option>
                </select>
            </div>
            <div class="mb-3 fw-bold">
                <label>Elige tu modelo <span style="color: red;">*</label>
                <select name="modelo" id="modelo" required>
                    <option value="">Selecciona una opción</option>
                </select>
                <input type="hidden" name="codigoSOL" id="codigoSOL">
            </div>
            <div class="mb-3">
                <label>Año <span style="color: red;">*</label>
                <select name="anio" id="anio" required>
                    <option value="">Seleccione el año</option>
                </select>
            </div>
            <div class="mb-3 fw-bold">
                <label>Patente <span style="color: red;">*</label>
                <input type="text" class="form-control" name="patente" id="patente" 
                   required maxlength="6"
                   pattern="[A-Z1-9]+"
                   title="Solo letras A-Z y números del 1 al 9">
            </div>
            <div class="mb-3 fw-bold">
                <label>Correo electrónico <span style="color: red;">*</label>
                <input type="email" class="form-control" name="email" id="email"
                   required
                   pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                   title="Ingrese un correo electrónico válido (ejemplo: usuario@dominio.com)">
            </div>
            <div class="mb-3 fw-bold">
                <!-- Aquí podrías integrar reCAPTCHA si lo deseas -->
                <label>No soy un robot</label>
                <div style="border:1px solid #ccc; padding:10px; width: fit-content;">
					<div class="g-recaptcha" data-sitekey="6LcjU3orAAAAAHFue4p4A0Rrj5lZdg3LoXXfRyuP"></div>
				</div>
				<div id="mensaje-error" class="mb-3"></div>
            </div>
            <div class="text-end">
                <button type="button" class="btn btn-success" id="siguiente">SIGUIENTE</button>
            </div>
        </div>

        <!-- Paso 2 -->
        <div id="paso-personal" class="hidden">
            <div class="mb-3 fw-bold">
                <label>Nombre completo<span style="color: red;">*</span></label>
                <input type="text" class="form-control" name="nombre" required>
            </div>
            <div class="mb-3 fw-bold">
                <label>Apellido Paterno<span style="color: red;">*</span></label>
                <input type="text" class="form-control" name="apaterno" required>
            </div>
            <div class="mb-3 fw-bold">
                <label>Apellido Materno<span style="color: red;">*</span></label>
                <input type="text" class="form-control" name="amaterno" required>
            </div>
            <div class="mb-3 fw-bold">
                <label>RUT <span style="color: red;">*</span></label>
                <input type="text" class="form-control" name="rut" required placeholder="12.345.678-9">
            </div>
            <div class="mb-3 fw-bold">
                <label>Fecha de nacimiento <span style="color: red;">*</span></label>
                <input type="date" class="form-control" name="fecha_nacimiento" required>
            </div>
            <div class="mb-3 fw-bold">
                <label>Género <span style="color: red;">*</span></label>
                <select name="genero" required>
                    <option value="">Seleccionar</option>
                    <option value="Femenino">Femenino</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
            <div class="mb-3 fw-bold">
                <label>Región <span style="color: red;">*</span></label>
                <select name="region" required>
                </select>
            </div>
            <div class="mb-3 fw-bold">
                <label>Comuna <span style="color: red;">*</span></label>
                <select name="comuna" required>
                    <option value="">Selecciona tu comuna</option>
                </select>
            </div>
            <div class="mb-3 fw-bold">
                <label>Dirección <span style="color: red;">*</span></label>
                <input type="text" class="form-control" name="direccion" required>
            </div>
            <div class="mb-3 fw-bold">
                <label>Teléfono <span style="color: red;">*</span></label>
                <input type="tel" class="form-control" name="telefono" required placeholder="9 1234 5678">
            </div>
            <div class="mb-3 fw-bold">
              <label class="d-block">¿Qué uso le das al vehículo? <span class="text-danger">*</span></label>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="uso" id="usoParticular" value="Particular" required style="outline: 2px solid green;" checked>
                <label class="form-check-label" for="usoParticular">Particular</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="uso" id="usoComercial" value="Comercial" style="outline: 2px solid green;">
                <label class="form-check-label" for="usoComercial">Comercial</label>
              </div>
            </div>
            
            <div class="mb-3 fw-bold">
              <label class="d-block">¿Nuevo o usado? <span class="text-danger">*</span></label>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="esNuevo" id="esNuevoNuevo" value="Nuevo" required style="outline: 2px solid green;" checked>
                <label class="form-check-label" for="esNuevoNuevo">Nuevo (solo si es del año actual)</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="esNuevo" id="esNuevoUsado" value="Usado" style="outline: 2px solid green;">
                <label class="form-check-label" for="esNuevoUsado">Usado</label>
              </div>
            </div>
            
            <div class="mb-3 fw-bold">
              <label class="d-block">¿Qué días de pago? <span class="text-danger">*</span></label>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="diasPago" id="5" value="5" required style="outline: 2px solid green;" checked>
                <label class="form-check-label" for="5">5</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="diasPago" id="15" value="15" style="outline: 2px solid green;">
                <label class="form-check-label" for="15">15</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="diasPago" id="25" value="25" style="outline: 2px solid green;">
                <label class="form-check-label" for="25">25</label>
              </div>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="terminos" name="terminos" required style="outline: 2px solid green;">
                <label class="form-check-label fw-bold"><span style="color: red;">*</span> Acepto <a href="#">Términos y Condiciones</a></label>
            </div>
			<input type='hidden' name='csrf_token' value='los422'/>
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-success" id="anterior">ANTERIOR</button>
                <button type="submit" class="btn btn-success" id="cotizar">COTIZAR</button>
            </div>
        </div>
        <input type="hidden" name="nombre_marca" id="nombre_marca">
        <input type="hidden" name="nombre_modelo" id="nombre_modelo">
        <input type="hidden" name="nombre_comuna" id="nombre_comuna">
    </form>
</div>

<script>
document.getElementById('patente').addEventListener('input', function() {
    // Convertir a mayúsculas y eliminar caracteres no permitidos
    this.value = this.value.toUpperCase().replace(/[^A-Z1-9]/g, '');
});

document.getElementById("siguiente").addEventListener("click", function () {
    const captchaResponse = grecaptcha.getResponse();

    // Obtener valores de los campos
    const patente = document.querySelector('[name="patente"]').value.trim();
    const email = document.querySelector('[name="email"]').value.trim();
    const anio = document.querySelector('[name="anio"]').value.trim();
    const marca = document.querySelector('[name="marca"]').value;
    const modelo = document.querySelector('[name="modelo"]').value;
    const select = document.querySelector('[name="modelo"]');
    const selectedOption = select.options[select.selectedIndex];
    const modeloAux = selectedOption.dataset.codigoSOL;
    document.getElementById('codigoSOL').value = modeloAux;
    
    const patron = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    testEmail = patron.test(email);
    let mensaje = "";

    // Validar campos vacíos
    if (!marca) mensaje += "<div>Debes seleccionar una marca.</div>";
    if (!modelo) mensaje += "<div>Debes seleccionar un modelo.</div>";
    if (!anio) mensaje += "<div>Debes ingresar el año del vehículo.</div>";
    if (!testEmail) mensaje += "<div>Debes ingresar un correo electrónico válido.</div>";
    if (!patente) mensaje += "<div>Debes ingresar la patente.</div>";

    // Validar CAPTCHA
    if (!captchaResponse) {
        mensaje += "<div>Por favor confirma que no eres un robot.</div>";
    }

    // Mostrar errores si hay
    if (mensaje) {
        document.getElementById("mensaje-error").innerHTML = `<div class="alert alert-danger">${mensaje}</div>`;
        return; // No avanza
    }

    // Si todo está correcto → avanzar al siguiente paso
    document.getElementById("mensaje-error").innerHTML = ""; // Limpiar errores

    document.getElementById("paso-vehiculo").classList.add("hidden");
    document.getElementById("paso-personal").classList.remove("hidden");
    document.querySelector(".step-1").classList.remove("active");
    document.querySelector(".step-2").classList.add("active");
});

document.getElementById("anterior").addEventListener("click", function () {
    document.getElementById("paso-personal").classList.add("hidden");
    document.getElementById("paso-vehiculo").classList.remove("hidden");
    document.querySelector(".step-2").classList.remove("active");
    document.querySelector(".step-1").classList.add("active");
});
</script>
<script>
  const selectAnio = document.getElementById('anio');
  const anioActual = new Date().getFullYear();
  const radioNuevo = document.getElementById('esNuevoNuevo');
  const radioUsado = document.getElementById('esNuevoUsado');
  const labelNuevo = document.querySelector('label[for="esNuevoNuevo"]');
  const labelUsado = document.querySelector('label[for="esNuevoUsado"]');

  function actualizarEstadoVehiculo() {
    const anioSeleccionado = parseInt(selectAnio.value, 10);

    if (!Number.isNaN(anioSeleccionado) && anioSeleccionado < anioActual) {
      radioNuevo.disabled = true;
      radioNuevo.checked = false;
      radioUsado.checked = true;
      labelNuevo.style.opacity = '0.5';
      labelUsado.style.opacity = '1';
      return;
    }

    radioNuevo.disabled = false;
    labelNuevo.style.opacity = '1';
    labelUsado.style.opacity = '1';
  }

  for (let anio = anioActual; anio >= anioActual - 15; anio--) {
    const opcion = document.createElement('option');
    opcion.value = anio;
    opcion.textContent = anio;
    selectAnio.appendChild(opcion);
  }

  selectAnio.addEventListener('change', actualizarEstadoVehiculo);
  actualizarEstadoVehiculo();
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const rutInputs = document.querySelectorAll('[name="rut"]');

  rutInputs.forEach(function(input) {
    input.addEventListener('input', function () {
      let value = input.value.replace(/[^0-9kK]/g, '').toUpperCase();

      if (value.length > 1) {
        const body = value.slice(0, -1);
        const dv = value.slice(-1);

        // Validación: no permitir más de 8 dígitos en el cuerpo
        if (body.length > 8) {
          input.value = ''; // o podrías mantener el valor anterior si prefieres
          return;
        }

        let formatted = '';
        let i = 0;

        for (let j = body.length - 1; j >= 0; j--) {
          formatted = body[j] + formatted;
          i++;
          if (i % 3 === 0 && j !== 0) {
            formatted = '.' + formatted;
          }
        }

        input.value = `${formatted}-${dv}`;
      } else {
        input.value = value;
      }
    });
  });
});
</script>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
