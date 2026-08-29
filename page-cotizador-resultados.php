<?php
if ( session_status() !== PHP_SESSION_ACTIVE && ! headers_sent() ) {
    session_start();
}
    
if (isset($_SESSION['usuario'])) {
    $usuario = $_SESSION['usuario'];

    $rut = htmlspecialchars($usuario['rut']);
    $rutCompleto = $rut;
    $rut = substr($rut, 0, -1);
    $dv = substr($rut, -1);
    $nombre = htmlspecialchars($usuario['nombre']);
    $apaterno = htmlspecialchars($usuario['apaterno']);
    $amaterno = htmlspecialchars($usuario['amaterno']);
    $email = htmlspecialchars($usuario['email']);
    $telefono = htmlspecialchars($usuario['telefono']);
    $diasPago = htmlspecialchars($usuario['diasPago']);
    $comuna = htmlspecialchars($usuario['comuna']);
    $fecha_nacimiento = htmlspecialchars($usuario['fecha_nacimiento']);
    $anio = htmlspecialchars($usuario['anio']);
    $marca = htmlspecialchars($usuario['marca']);
    $modelo = htmlspecialchars($usuario['modelo']);
    $patente = htmlspecialchars($usuario['patente']);
    $uso = htmlspecialchars($usuario['uso']);
    $esNuevo = htmlspecialchars($usuario['esNuevo']);
    $genero = htmlspecialchars($usuario['genero']);
    $direccion = htmlspecialchars($usuario['direccion']);
    $nombre_marca = htmlspecialchars($usuario['nombre_marca']);
    $nombre_modelo = htmlspecialchars($usuario['nombre_modelo']);
    $nombre_comuna = htmlspecialchars($usuario['nombre_comuna']);
} else {
    echo "No hay datos de usuario en la sesión.";
}

$esNuevoValor = 'false';
if (strtolower((string)($esNuevo ?? '')) === 'nuevo') {
    $esNuevoValor = 'true';
} elseif (strtolower((string)($esNuevo ?? '')) === 'usado') {
    $esNuevoValor = 'false';
}

$accion = $_POST['accion'] ?? '';

if ($accion === 'continuar') {
    $cotizacionServicioID = $_POST['cotizacionServicioID'];
} elseif ($accion === 'nueva') {
    $cotizacionServicioID = null;
}

// Formato de fecha y hora
date_default_timezone_set('America/Santiago'); // Ajusta a tu zona si es necesario
$fechaHora = date('Y-m-d H:i:s');

//se guardan datos en un archivo local
$linea = "fecha: $fechaHora; nombre: $nombre; rut: $rutCompleto; fecha_nacimiento: $fecha_nacimiento; anio: $anio; codmarca: $marca; marca: $nombre_marca; codmodelo: $modelo; modelo: $nombre_modelo; codcomuna: $comuna;  comuna: $nombre_comuna; patente: $patente; uso: $uso;\n";
file_put_contents("cotizaciones.txt", $linea, FILE_APPEND | LOCK_EX);

echo "<h3 style='color: #333;'>Elige el plan a tu medida</h3>";

// Sección de opciones (radiobuttons) para Opcionales - con estilos
?>
<style>
.opcionales-bar {
	display: flex;
	align-items: center;
	gap: 20px;
	margin: 18px 0;
	border: 2px solid #d0d0d0;
	padding: 12px 16px;
	border-radius: 8px;
	background: #fff;
}
.opc-grupo { display:flex; gap:12px; align-items:center; width:100%; }
.opc-grupo label { display:flex; align-items:center; gap:8px; font-size:13px; color:#222; }
.opc-options { display:flex; flex-direction:column; gap:8px; align-items:flex-start; }
.opc-grupo .opc-title { font-weight:600; margin-right:8px; flex: 0 0 130px; }
.opc-column { display:flex; gap:18px; align-items:flex-start; flex-direction:column; flex:1 1 0; min-width:0; }
.opc-radio { width:16px; height:16px; }
.tooltip-icon { position: relative; display: inline-block; width: 20px; height: 20px; background-color: #007bff; color: white; border-radius: 50%; text-align: center; line-height: 20px; font-size: 12px; font-weight: bold; cursor: help; margin-left: 4px; }
.tooltip-icon .tooltip { visibility: hidden; width: 200px; background-color: #333; color: #fff; text-align: center; border-radius: 6px; padding: 8px; position: absolute; z-index: 1; bottom: 125%; left: 50%; margin-left: -100px; opacity: 0; transition: opacity 0.3s; font-size: 12px; font-weight: normal; line-height: 1.4; white-space: normal; }
.tooltip-icon .tooltip::after { content: ""; position: absolute; top: 100%; left: 50%; margin-left: -5px; border-width: 5px; border-style: solid; border-color: #333 transparent transparent transparent; }
.tooltip-icon:hover .tooltip { visibility: visible; opacity: 1; }
.btn-cotizar { background:#19a974 !important; color:#fff !important; border:none !important; padding:10px 26px !important; border-radius:6px !important; font-weight:700 !important; }
@media(max-width:768px){ 
	.opcionales-bar{flex-direction:column; align-items:flex-start;} 
	.opc-column{flex-direction:column; width:100%;}
	.opc-grupo .opc-title{min-width:unset;}
}
/* Apilar opciones del primer grupo (AutoReemplazo) en columna */
.opc-column:first-child .opc-options { flex-direction: column; align-items: flex-start; }
</style>

<form method="post" id="opcionalesForm">
	<div class="opcionales-bar">
		<div class="opc-column">
			<div class="opc-grupo">
				<span class="opc-title">AutoReemplazo:</span>
				<div class="opc-options">
					<label><input class="opc-radio" type="radio" name="opc_autoReemplazo" value="QuinceDias" <?php echo (!isset($_POST['opc_autoReemplazo']) || $_POST['opc_autoReemplazo'] === 'QuinceDias') ? 'checked' : ''; ?>> 15 dias</label>
					<label><input class="opc-radio" type="radio" name="opc_autoReemplazo" value="TreintaDias" <?php echo (isset($_POST['opc_autoReemplazo']) && $_POST['opc_autoReemplazo'] === 'TreintaDias') ? 'checked' : ''; ?>> 30 dias</label>
					<label><input class="opc-radio" type="radio" name="opc_autoReemplazo" value="CuarentayCincoDias" <?php echo (isset($_POST['opc_autoReemplazo']) && $_POST['opc_autoReemplazo'] === 'CuarentayCincoDias') ? 'checked' : ''; ?>> 45 dias</label>
					<label><input class="opc-radio" type="radio" name="opc_autoReemplazo" value="Ilimitado" <?php echo (isset($_POST['opc_autoReemplazo']) && $_POST['opc_autoReemplazo'] === 'Ilimitado') ? 'checked' : ''; ?>> Ilimitado</label>
				</div>
			</div>
		</div>

		<div class="opc-column">
			<div class="opc-grupo">
				<span class="opc-title">Taller:</span>
				<div class="opc-options">
					<label><input class="opc-radio" type="radio" name="opc_taller" value="MultiMarca" <?php echo (!isset($_POST['opc_taller']) || $_POST['opc_taller'] === 'MultiMarca') ? 'checked' : ''; ?>> MultiMarca <span class="tooltip-icon">?<span class="tooltip">TALLER MULTIMARCA : Taller en convenio que repara de todas las marcas, puede usar repuestos alternativos</span></span></label>
					<label><input class="opc-radio" type="radio" name="opc_taller" value="TallerdeMarca" <?php echo (isset($_POST['opc_taller']) && $_POST['opc_taller'] === 'TallerdeMarca') ? 'checked' : ''; ?>> Taller de Marca <span class="tooltip-icon">?<span class="tooltip">TALLER DE MARCA : Se repara en el lugar donde usted compró el vehiculo o en otro taller concesionario de la Marca, solo usa repuestos originales.</span></span></label>
					<label><input class="opc-radio" type="radio" name="opc_taller" value="TallerGarantia" <?php echo (isset($_POST['opc_taller']) && $_POST['opc_taller'] === 'TallerGarantia') ? 'checked' : ''; ?>> Taller Garantía <span class="tooltip-icon">?<span class="tooltip">TALLER GARANTIA : Se repara en el lugar donde usted compró el vehiculo o en otro taller concesionario de la Marca, durante el tiempo que el vehiculo esté en garantia.</span></span></label>
				</div>
			</div>
		</div>

		<div class="opc-column">
			<div class="opc-grupo">
				<span class="opc-title">RCExceso:</span>
				<div class="opc-options">
					<label><input class="opc-radio" type="radio" name="opc_rCExceso" value="SinOpcional" <?php echo (!isset($_POST['opc_rCExceso']) || $_POST['opc_rCExceso'] === 'SinOpcional') ? 'checked' : ''; ?>> Sin RCexceso</label>
					<label><input class="opc-radio" type="radio" name="opc_rCExceso" value="MilUF" <?php echo (isset($_POST['opc_rCExceso']) && $_POST['opc_rCExceso'] === 'MilUF') ? 'checked' : ''; ?>> 1.000 UF</label>
					<label><input class="opc-radio" type="radio" name="opc_rCExceso" value="DosMilUF" <?php echo (isset($_POST['opc_rCExceso']) && $_POST['opc_rCExceso'] === 'DosMilUF') ? 'checked' : ''; ?>> 2.000 UF</label>
					<label><input class="opc-radio" type="radio" name="opc_rCExceso" value="TresMilUF" <?php echo (isset($_POST['opc_rCExceso']) && $_POST['opc_rCExceso'] === 'TresMilUF') ? 'checked' : ''; ?>> 3.000 UF</label>
				</div>
			</div>
		</div>

		<div style="margin-left:auto;">
			<input type="hidden" name="accion" value="cotizarOpc">
			<?php if (isset($cotizacionServicioID)) : ?>
				<input type="hidden" name="cotizacionServicioID" value="<?php echo htmlspecialchars($cotizacionServicioID); ?>">
			<?php endif; ?>
			<button type="submit" class="btn-cotizar">COTIZAR</button>
		</div>
	</div>
</form>
<?php

//crearPrimeraCotizacion($amaterno, $apaterno, $comuna, $fecha_nacimiento, $nombre, $rut, $anio, $marca, $modelo, $patente, $uso);

$url = 'https://hub-qa.segurossura.cl/seguros-generales/vechiculos-cotizacion';
$token = recuperarToken();

$headers = [
	'Content-Type' => 'text/xml; charset=utf-8',
	'SOAPAction'   => '"http://ws.rsa.cl/WsIndustrializacion/CotizaOferta"',
	'Authorization'=> 'Bearer ' . $token,
	'Ocp-Apim-Subscription-Key' => '712c61e9edff4ad4842111902b1dcf18',
];

// Opcionales: tomar desde POST si el usuario envió el formulario, sino usar los valores por defecto
$opc_autoReemplazo = isset($_POST['opc_autoReemplazo']) ? sanitize_text_field($_POST['opc_autoReemplazo']) : 'QuinceDias';
$opc_rCExceso = isset($_POST['opc_rCExceso']) ? sanitize_text_field($_POST['opc_rCExceso']) : 'SinOpcional';
$opc_taller = isset($_POST['opc_taller']) ? sanitize_text_field($_POST['opc_taller']) : 'MultiMarca';

// Guardar opcionales en sesión para usar después
$_SESSION['opc_autoReemplazo'] = $opc_autoReemplazo;
$_SESSION['opc_taller'] = $opc_taller;
$_SESSION['opc_rCExceso'] = $opc_rCExceso;

// Función para mapear autoReemplazo a texto legible
function obtenerTextoAutoReemplazo($opc_autoReemplazo) {
	switch($opc_autoReemplazo) {
		case 'QuinceDias':
			return '15 días';
		case 'TreintaDias':
			return '30 días';
		case 'CuarentayCincoDias':
			return '45 días';
		case 'Ilimitado':
			return 'ilimitados';
		default:
			return 'ilimitados';
	}
}

// Función para mapear taller a texto legible
function obtenerTextoTaller($opc_taller) {
	switch($opc_taller) {
		case 'TallerdeMarca':
			return 'Taller de Marca';
		case 'TallerGarantia':
			return 'Taller Garantía';
		case 'MultiMarca':
			return 'MultiMarca';
		default:
			return 'MultiMarca';
	}
}

$body = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://ws.rsa.cl/" xmlns:epv="http://schemas.datacontract.org/2004/07/EPV.Servicios.Services">
<soapenv:Header>
	<ws:DatosTransaccion>
		<epv:contrasena>Sura2025_06_R</epv:contrasena>
		<epv:fechaHora>2021-12-28</epv:fechaHora>
		<epv:idContexto></epv:idContexto>
		<epv:idTransaccion></epv:idTransaccion>
		<epv:sistema></epv:sistema>
		<epv:usuario>77496140@ProductoraDiaz.cl</epv:usuario>
	</ws:DatosTransaccion>
</soapenv:Header>
<soapenv:Body>
	<ws:CotizaOfertaRequest>
		<ws:CotizacionOferta>
		<ws:DatosBase>
			<ws:Asegurado>
				<ws:apellidoMaterno>$amaterno</ws:apellidoMaterno>
				<ws:apellidoPaterno>$apaterno</ws:apellidoPaterno>
				<ws:codigoComuna>$comuna</ws:codigoComuna>
				<ws:fechaNacimiento>$fecha_nacimiento</ws:fechaNacimiento>
				<ws:nombres>$nombre</ws:nombres>
				<ws:razonSocial>$nombre $apaterno $amaterno</ws:razonSocial>
				<ws:rut>$rutCompleto</ws:rut>
				<ws:tipoPersona>Natural</ws:tipoPersona>
			</ws:Asegurado>
			<ws:declaraConductorMenor30Anios>true</ws:declaraConductorMenor30Anios>
			<ws:formaPago>Oneclick</ws:formaPago>
			<ws:recargoDescuento>Recargo</ws:recargoDescuento>
			<ws:valorRecargo>0,00</ws:valorRecargo>
		</ws:DatosBase>
		<ws:DatosBien>
			<ws:anio>$anio</ws:anio>
			<ws:codigoMarca>$marca</ws:codigoMarca>
			<ws:codigoModelo>$modelo</ws:codigoModelo>
			<ws:esNuevo>$esNuevoValor</ws:esNuevo>
			<ws:patente>$patente</ws:patente>
			<ws:usoBien>$uso</ws:usoBien>
		</ws:DatosBien>
		<ws:Opcionales>
			<ws:asistencias>SinOpcional</ws:asistencias>
			<ws:autoReemplazo>{$opc_autoReemplazo}</ws:autoReemplazo>
			<ws:rCExceso>{$opc_rCExceso}</ws:rCExceso>
			<ws:taller>{$opc_taller}</ws:taller>
		</ws:Opcionales>
		</ws:CotizacionOferta>
	</ws:CotizaOfertaRequest>
</soapenv:Body>
</soapenv:Envelope>
XML;

$response = wp_remote_post($url, [
	'headers' => $headers,
	'body'    => $body,
	'timeout' => 30, // Opcional: aumenta el tiempo de espera si tarda
	'sslverify' => false
]);

if (is_wp_error($response)) {
	$error_message = $response->get_error_message();
	error_log("❌ Error HTTP: $error_message");
	//echo "Error: $error_message";
} else {
	$status_code = wp_remote_retrieve_response_code($response);
	$response_body = wp_remote_retrieve_body($response);
	
	// Guardar resultado en sesión para usar en otra pantalla
	if (!session_id()) session_start();
	
	$_SESSION['respuesta_api'] = htmlspecialchars($response_body);
	
}

echo '<style>

.container {
	display: flex;
	flex-direction: column;
	gap: 20px;
	width: 100%;
	max-width: 900px; /* Ajusta según el ancho deseado */
}

.card {
	background-color: #ffffff;
	border-radius: 8px;
	box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
	overflow: hidden;
	display: flex;
	flex-direction: column;
}

.card-header {
	background-color: #f7f9fc;
	padding: 15px 20px;
	border-bottom: 1px solid #eee;
}

.card-header h3 {
	margin: 0;
	color: #333;
	font-size: 1.1em;
}

.card-body {
	display: flex;
	flex-wrap: wrap; /* Permite que los elementos se envuelvan en pantallas pequeñas */
	padding: 20px;
	gap: 20px; /* Espacio entre las secciones */
}

.logo-section {
	flex: 0 0 auto; /* No crece ni se encoge, toma el tamaño de su contenido */
	display: flex;
	align-items: center;
	justify-content: center;
	padding-right: 20px; /* Espacio a la derecha del logo */
	border-right: 1px solid #eee; /* Separador visual */
}

.logo-section img {
	max-width: 120px; /* Ajusta el tamaño del logo */
	height: auto;
}

.details-section {
	flex: 2; /* Ocupa más espacio que el logo y el precio */
	min-width: 250px; /* Ancho mínimo para esta sección antes de envolverse */
}

.details-section h4 {
	margin-top: 0;
	color: #555;
	font-size: 0.95em;
}

.coverage-list {
	list-style: none;
	padding: 0;
	margin: 10px 0;
}

.coverage-list li {
	margin-bottom: 5px;
	color: #444;
	font-size: 0.9em;
	display: flex;
	align-items: center;
}

.check-icon {
	color: #28a745; /* Color verde para el check */
	margin-right: 8px;
	font-weight: bold;
}

.more-details {
	color: #007bff;
	text-decoration: none;
	font-size: 0.85em;
	display: inline-block;
	margin-top: 10px;
}

.more-details:hover {
	text-decoration: underline;
}

.price-section {
	flex: 1; /* Ocupa el espacio restante */
	display: flex;
	flex-direction: column;
	align-items: flex-end; /* Alinea el contenido a la derecha */
	justify-content: center;
	min-width: 150px; /* Ancho mínimo para esta sección */
	padding-left: 20px; /* Espacio a la izquierda del precio */
	border-left: 1px solid #eee; /* Separador visual */
}

.price-clp {
	font-size: 1.6em;
	font-weight: bold;
	color: #333;
	margin-bottom: 5px;
}

.price-uf {
	font-size: 0.85em;
	color: #666;
	margin-bottom: 15px;
}

.btn-comprar {
	background-color: #3CB043 !important;
	color: white;
	border: none;
	padding: 10px 20px;
	border-radius: 5px;
	cursor: pointer;
	font-size: 0.9em;
	transition: background-color 0.3s ease;
}

.btn-comprar:hover {
	background-color: #5a6268;
}

/* Popup Styles */
.coverage-popup {
	display: none;
	position: fixed;
	z-index: 1000;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	background-color: rgba(0, 0, 0, 0.5);
}

.coverage-popup.active {
	display: flex;
	align-items: center;
	justify-content: center;
}

.popup-content {
	background-color: #fff;
	padding: 30px;
	border-radius: 10px;
	max-width: 500px;
	width: 90%;
	max-height: 80vh;
	overflow-y: auto;
	position: relative;
	box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.close-popup {
	position: absolute;
	top: 15px;
	right: 20px;
	font-size: 28px;
	cursor: pointer;
	color: #666;
	line-height: 1;
}

.close-popup:hover {
	color: #333;
}

.popup-content h3 {
	margin-top: 0;
	margin-bottom: 20px;
	color: #333;
	font-size: 1.3em;
}

.popup-coverage-list {
	list-style: none;
	padding: 0;
	margin: 0;
}

.popup-coverage-list li {
	padding: 8px 0;
	color: #444;
	font-size: 0.95em;
	border-bottom: 1px solid #eee;
}

.popup-coverage-list li:last-child {
	border-bottom: none;
}

/* Media Queries para responsividad */
@media (max-width: 768px) {
	.card-body {
		flex-direction: column;
		align-items: center;
		text-align: center;
	}

	.logo-section,
	.details-section,
	.price-section {
		border: none; /* Eliminar bordes verticales en pantallas pequeñas */
		padding: 0; /* Eliminar paddings de los bordes */
		width: 100%;
		text-align: center;
	}

	.logo-section {
		margin-bottom: 15px;
	}

	.details-section ul {
		text-align: left; /* Mantener la lista alineada a la izquierda */
		margin-left: auto;
		margin-right: auto;
		max-width: fit-content; /* Ajustar al contenido */
	}

	.price-section {
		align-items: center; /* Centrar el contenido de precio */
		margin-top: 20px;
	}
}
</style>';

if (!isset($_SESSION['respuesta_api'])) {
	echo "No hay resultados de la api para mostrar.";
}

	if (isset($_SESSION['respuesta_api']) && $cotizacionServicioID === null) {
	
	$respuesta = $_SESSION['respuesta_api'];
	unset($_SESSION['respuesta_api']);

	// Obtener opcionales de sesión
	$opc_autoReemplazo_actual = isset($_SESSION['opc_autoReemplazo']) ? $_SESSION['opc_autoReemplazo'] : 'Ilimitado';
	$opc_taller_actual = isset($_SESSION['opc_taller']) ? $_SESSION['opc_taller'] : 'MultiMarca';
	
	$textoAutoReemplazo = obtenerTextoAutoReemplazo($opc_autoReemplazo_actual);
	$textoTaller = obtenerTextoTaller($opc_taller_actual);

	// Formatea lo que quieras mostrar
	//ob_start();
	$codigoError = "";
	preg_match('/<a:codigoError>(.*?)<\/a:codigoError>/', html_entity_decode($respuesta), $match);

	if (isset($match[1])) {
		$codigoError = $match[1];
	}
	
	preg_match('/<a:detalleError>(.*?)<\/a:detalleError>/', html_entity_decode($respuesta), $match);

	if (isset($match[1])) {
		$detalleError = $match[1];
	}

	// Validar si todo está OK
	if ($codigoError !== '0' || strtoupper($detalleError) !== 'OK') {
		//Hay un error al llamar API de ofertas
		echo 'Hay un error en la pagina, contactese con el administrador<br>';
		echo 'codigoError: '.$codigoError.'<br>';
		echo 'detalleError: '.$detalleError.'<br>';
	} else {
		preg_match_all('/<cotizacionServicioID[^>]*>(.*?)<\/cotizacionServicioID>/', html_entity_decode($respuesta), $matches);

		if (!empty($matches[1])) {
			$cotizacionServicioID = $matches[1][0];
		} else {
			echo "No se encontró ID";
		}
		
		preg_match_all('/<nombrePlan>(.*?)<\/nombrePlan>/', html_entity_decode($respuesta), $matches);

		if (!empty($matches[1])) {
			foreach ($matches[1] as $index => $valor) {
				$nombrePlan[$index] = $valor;
			}
		}
		
		preg_match_all('/<valorEnPeso>(.*?)<\/valorEnPeso>/', html_entity_decode($respuesta), $matches);

		if (!empty($matches[1])) {
			foreach ($matches[1] as $index => $valor) {
				$valorEnPeso[$index] = $valor;
			}
		}
		
		preg_match_all('/<valorEnUf>(.*?)<\/valorEnUf>/', html_entity_decode($respuesta), $matches);

		if (!empty($matches[1])) {
			foreach ($matches[1] as $index => $valor) {
				$valorEnUf[$index] = $valor;
			}
		}
		
		preg_match_all('/<valorUf>(.*?)<\/valorUf>/', html_entity_decode($respuesta), $matches);

		if (!empty($matches[1])) {
			foreach ($matches[1] as $index => $valor) {
				$valorUf[$index] = $valor;
			}
		}
		
		$seguros = [];

		for ($i = 0; $i < count($nombrePlan); $i++) {
			if (str_contains($nombrePlan[$i], "PREMIUM")) {
				break;
			}
			
			$tituloPlan = '';
			$coberturas = [];
			$detalle_coberturas = [];
			
			if($nombrePlan[$i] == 'PT_RC_SD'){
				$tituloPlan = 'Perdida Total / Responsabilidad Civil / Sin Deducible';
				$coberturas = [
					'Full Cobertura',
					'Perdida total (Valor comercial)',
					'Responsabilidad Civil UF 500 c/u'
				];
				$detalle_coberturas = [
					'✅ Daños totales al vehículo',
					'✅ Robo, Hurto, uso no Autorizado',
					'✅ Responsabilidad Civil UF 500 c/u',
					'✅ Actos Maliciosos',
					'✅ Huelga y Terrorismo',
					'✅ Riesgos de la naturaleza, Granizo / Sismo',
					'✅ Daños por la propia carga',
					'✅ Daños a tercero por la propia carga',
					'✅ Defensa Penal y Constitución de Fianza UF 50',
					'✅ Asistencia al vehículo',
					'✅ Asiento Pasajero Plan A y Plan B'
				];
			}

			if (str_contains($nombrePlan[$i], "CLASSIC")) {
				$coberturas = [
					'Full Cobertura',
					'Daños Materiales (Valor comercial)',
					'Responsabilidad Civil UF 500 c/u',
					$textoTaller
				];
				$detalle_coberturas = [
					'✅ Daños propios al vehículo',
					'✅ Daños totales al vehículo',
					'✅ Robo, Hurto, uso no Autorizado',
					'✅ Responsabilidad Civil UF 500 c/u',
					'✅ Actos Maliciosos',
					'✅ Huelga y Terrorismo',
					'✅ Riesgos de la naturaleza, Granizo / Sismo',
					'✅ Daños por la propia carga',
					'✅ Daños a tercero por la propia carga UF 500',
					'✅ Robo de Accesorios',
					'✅ Defensa Penal y Constitución de Fianza UF 50',
					'✅ Deducible Resposable',
					'✅ Taller de reparación ' . $textoTaller,
					'✅ Auto de reemplazo ' . $textoAutoReemplazo,
					'✅ Asistencia al vehículo',
					'✅ Asiento Pasajero Plan A y Plan B'
				];
			}

			if (str_contains($nombrePlan[$i], "FULL")) {
				$coberturas = [
					'Full Cobertura',
					'Daños Materiales (Valor comercial)',
					'Responsabilidad Civil UF 700 c/u',
					$textoTaller
				];
				$detalle_coberturas = [
					'✅ Daños propios al vehículo',
					'✅ Daños totales al vehículo',
					'✅ Robo, Hurto, uso no Autorizado',
					'✅ Responsabilidad Civil UF 700 c/u',
					'✅ Actos Maliciosos',
					'✅ Huelga y Terrorismo',
					'✅ Riesgos de la naturaleza, Granizo / Sismo',
					'✅ Daños por la propia carga',
					'✅ Daños a tercero por la propia carga UF 700',
					'✅ Robo de Accesorios',
					'✅ Defensa Penal y Constitución de Fianza UF 50',
					'✅ Deducible Resposable',
					'✅ Taller de reparación ' . $textoTaller,
					'✅ Auto de reemplazo ' . $textoAutoReemplazo,
					'✅ Asistencia al vehículo',
					'✅ Asiento Pasajero Plan A y Plan B',
					'✅ Daños propios en el extranjero'
				];
			}
				
			if($nombrePlan[$i] == 'CLASSIC_SD')
				$tituloPlan = 'Classic Sin Deducible';
			else if($nombrePlan[$i] == 'CLASSIC_D3')
				$tituloPlan = 'Classic Deducible 3UF';
			else if($nombrePlan[$i] == 'CLASSIC_D5')
				$tituloPlan = 'Classic Deducible 5UF';
			else if($nombrePlan[$i] == 'CLASSIC_D10')
				$tituloPlan = 'Classic Deducible 10UF';
			else if($nombrePlan[$i] == 'CLASSIC_D20')
				$tituloPlan = 'Classic Deducible 20UF';
			else if($nombrePlan[$i] == 'CLASSIC_D50')
				$tituloPlan = 'Classic Deducible 50UF';
				
			else if($nombrePlan[$i] == 'FULL_SD')
				$tituloPlan = 'Full Sin Deducible';
			else if($nombrePlan[$i] == 'FULL_D3')
				$tituloPlan = 'Full Deducible 3UF';
			else if($nombrePlan[$i] == 'FULL_D5')
				$tituloPlan = 'Full Deducible 5UF';
			else if($nombrePlan[$i] == 'FULL_D10')
				$tituloPlan = 'Full Deducible 10UF';
			else if($nombrePlan[$i] == 'FULL_D20')
				$tituloPlan = 'Full Deducible 20UF';
			else if($nombrePlan[$i] == 'FULL_D50')
				$tituloPlan = 'Full Deducible 50UF';
				
			/*else if($nombrePlan[$i] == 'PREMIUM_SD')
				$tituloPlan = 'Premium Sin Deducible';
			else if($nombrePlan[$i] == 'PREMIUM_D3')
				$tituloPlan = 'Premium Deducible 3UF';
			else if($nombrePlan[$i] == 'PREMIUM_D5')
				$tituloPlan = 'Premium Deducible 5UF';
			else if($nombrePlan[$i] == 'PREMIUM_D10')
				$tituloPlan = 'Premium Deducible 10UF';
			else if($nombrePlan[$i] == 'PREMIUM_D20')
				$tituloPlan = 'Premium Deducible 20UF';
			else if($nombrePlan[$i] == 'PREMIUM_D50')
				$tituloPlan = 'Premium Deducible 50UF';*/

			$seguros[] = [
				'cotizacionServicioID' => $cotizacionServicioID,
				'nombre_aseguradora' => 'Sura',
				'logo' => get_stylesheet_directory_uri().'/sura.png',
				'titulo_seguro' => $tituloPlan,
				'nombre_plan' => $nombrePlan[$i],
				'cubiertas' => $coberturas,
				'detalle_coberturas' => $detalle_coberturas,
				'precio_clp_aprox' => $valorEnPeso[$i],
				'uf_anual' => $valorEnUf[$i]
			];
			
			//se guardan datos de consulta en un archivo para posterior consulta
			$linea = "fecha: $fechaHora; rut: $rutCompleto; cotizacionServicioID: $cotizacionServicioID; patente: $patente; nombrePlan.$i: $nombrePlan[$i]; valorEnPeso.$i: $valorEnPeso[$i]; valorEnUf.$i: $valorEnUf[$i];\n";
			file_put_contents("consultas_detalle.txt", $linea, FILE_APPEND | LOCK_EX);
		}
			
		//se guardan datos de consulta en un archivo para posterior consulta
		$linea = "fecha: $fechaHora; rut: $rutCompleto; cotizacionServicioID: $cotizacionServicioID; patente: $patente; \n";
		file_put_contents("consultas.txt", $linea, FILE_APPEND | LOCK_EX);

		echo '<div class="container">';
		// Iterar sobre los seguros y renderizar cada tarjeta
		foreach ($seguros as $seguro) {
			renderizarTarjetaSeguro($seguro);
		}
		echo '</div>';
	}
	
}

if (isset($_SESSION['respuesta_api']) && $cotizacionServicioID !== null) {
	
	$cotizacionServicioID = htmlspecialchars(urldecode($cotizacionServicioID));

	// Obtener opcionales de sesión
	$opc_autoReemplazo_actual = isset($_SESSION['opc_autoReemplazo']) ? $_SESSION['opc_autoReemplazo'] : 'Ilimitado';
	$opc_taller_actual = isset($_SESSION['opc_taller']) ? $_SESSION['opc_taller'] : 'MultiMarca';
	
	$textoAutoReemplazo = obtenerTextoAutoReemplazo($opc_autoReemplazo_actual);
	$textoTaller = obtenerTextoTaller($opc_taller_actual);

	$archivo = "consultas_detalle.txt";
	
	$nombrePlan   = [];
	$valorEnPeso  = [];
	$valorEnUf    = [];
	
	if (file_exists($archivo)) {
		$lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	
		foreach ($lineas as $linea) {
			// verificar si la línea pertenece al cotizacionServicioID buscado
			if (strpos($linea, "cotizacionServicioID: $cotizacionServicioID") !== false) {
	
				// Extraer todos los nombrePlan
				if (preg_match_all('/nombrePlan\.\d+:\s*([^;]+)/', $linea, $matchesNombrePlan)) {
					foreach ($matchesNombrePlan[1] as $plan) {
						$nombrePlan[] = trim($plan);
					}
				}
	
				// Extraer todos los valorEnPeso
				if (preg_match_all('/valorEnPeso\.\d+:\s*([^;]+)/', $linea, $matchesValorPeso)) {
					foreach ($matchesValorPeso[1] as $valor) {
						// reemplazar coma por punto si quieres manejar float
						$valorEnPeso[] = floatval(str_replace(',', '.', trim($valor)));
					}
				}
	
				// Extraer todos los valorEnUf
				if (preg_match_all('/valorEnUf\.\d+:\s*([^;]+)/', $linea, $matchesValorUf)) {
					foreach ($matchesValorUf[1] as $valor) {
						$valorEnUf[] = floatval(str_replace(',', '.', trim($valor)));
					}
				}
	
				// Si solo quieres la primera coincidencia de cotizacionServicioID, puedes hacer break;
				// break;
			}
		}
	}
	
	$seguros = [];

	for ($i = 0; $i < count($nombrePlan); $i++) {
		if (str_contains($nombrePlan[$i], "PREMIUM")) {
			break;
		}
			    
			$tituloPlan = '';
			$coberturas = [];
			$detalle_coberturas = [];
			
			if($nombrePlan[$i] == 'PT_RC_SD'){
				$tituloPlan = 'Perdida Total / Responsabilidad Civil / Sin Deducible';
				$coberturas = [
					'Full Cobertura',
					'Perdida total (Valor comercial)',
					'Responsabilidad Civil UF 500 c/u'
				];
				$detalle_coberturas = [
					'✅ Daños totales al vehículo',
					'✅ Robo, Hurto, uso no Autorizado',
					'✅ Responsabilidad Civil UF 500 c/u',
					'✅ Actos Maliciosos',
					'✅ Huelga y Terrorismo',
					'✅ Riesgos de la naturaleza, Granizo / Sismo',
					'✅ Daños por la propia carga',
					'✅ Daños a tercero por la propia carga',
					'✅ Defensa Penal y Constitución de Fianza UF 50',
					'✅ Asistencia al vehículo',
					'✅ Asiento Pasajero Plan A y Plan B'
				];
			}

			if (str_contains($nombrePlan[$i], "CLASSIC")) {
				$coberturas = [
					'Full Cobertura',
					'Daños Materiales (Valor comercial)',
					'Responsabilidad Civil UF 500 c/u',
					$textoTaller
				];
				$detalle_coberturas = [
					'✅ Daños propios al vehículo',
					'✅ Daños totales al vehículo',
					'✅ Robo, Hurto, uso no Autorizado',
					'✅ Responsabilidad Civil UF 500 c/u',
					'✅ Actos Maliciosos',
					'✅ Huelga y Terrorismo',
					'✅ Riesgos de la naturaleza, Granizo / Sismo',
					'✅ Daños por la propia carga',
					'✅ Daños a tercero por la propia carga UF 500',
					'✅ Robo de Accesorios',
					'✅ Defensa Penal y Constitución de Fianza UF 50',
					'✅ Deducible Resposable',
					'✅ Taller de reparación ' . $textoTaller,
					'✅ Auto de reemplazo ' . $textoAutoReemplazo,
					'✅ Asistencia al vehículo',
					'✅ Asiento Pasajero Plan A y Plan B'
				];
			}

			if (str_contains($nombrePlan[$i], "FULL")) {
				$coberturas = [
					'Full Cobertura',
					'Daños Materiales (Valor comercial)',
					'Responsabilidad Civil UF 700 c/u',
					$textoTaller
				];
				$detalle_coberturas = [
					'✅ Daños propios al vehículo',
					'✅ Daños totales al vehículo',
					'✅ Robo, Hurto, uso no Autorizado',
					'✅ Responsabilidad Civil UF 700 c/u',
					'✅ Actos Maliciosos',
					'✅ Huelga y Terrorismo',
					'✅ Riesgos de la naturaleza, Granizo / Sismo',
					'✅ Daños por la propia carga',
					'✅ Daños a tercero por la propia carga UF 700',
					'✅ Robo de Accesorios',
					'✅ Defensa Penal y Constitución de Fianza UF 50',
					'✅ Deducible Resposable',
					'✅ Taller de reparación ' . $textoTaller,
					'✅ Auto de reemplazo ' . $textoAutoReemplazo,
					'✅ Asistencia al vehículo',
					'✅ Asiento Pasajero Plan A y Plan B',
					'✅ Daños propios en el extranjero'
				];
			}
			        
			    if($nombrePlan[$i] == 'CLASSIC_SD')
			        $tituloPlan = 'Classic Sin Deducible';
			    else if($nombrePlan[$i] == 'CLASSIC_D3')
			        $tituloPlan = 'Classic Deducible 3UF';
			    else if($nombrePlan[$i] == 'CLASSIC_D5')
			        $tituloPlan = 'Classic Deducible 5UF';
			    else if($nombrePlan[$i] == 'CLASSIC_D10')
			        $tituloPlan = 'Classic Deducible 10UF';
			    else if($nombrePlan[$i] == 'CLASSIC_D20')
			        $tituloPlan = 'Classic Deducible 20UF';
			    else if($nombrePlan[$i] == 'CLASSIC_D50')
			        $tituloPlan = 'Classic Deducible 50UF';
			        
			    else if($nombrePlan[$i] == 'FULL_SD')
			        $tituloPlan = 'Full Sin Deducible';
			    else if($nombrePlan[$i] == 'FULL_D3')
			        $tituloPlan = 'Full Deducible 3UF';
			    else if($nombrePlan[$i] == 'FULL_D5')
			        $tituloPlan = 'Full Deducible 5UF';
			    else if($nombrePlan[$i] == 'FULL_D10')
			        $tituloPlan = 'Full Deducible 10UF';
			    else if($nombrePlan[$i] == 'FULL_D20')
			        $tituloPlan = 'Full Deducible 20UF';
			    else if($nombrePlan[$i] == 'FULL_D50')
			        $tituloPlan = 'Full Deducible 50UF';
			        
			    /*else if($nombrePlan[$i] == 'PREMIUM_SD')
			        $tituloPlan = 'Premium Sin Deducible';
			    else if($nombrePlan[$i] == 'PREMIUM_D3')
			        $tituloPlan = 'Premium Deducible 3UF';
			    else if($nombrePlan[$i] == 'PREMIUM_D5')
			        $tituloPlan = 'Premium Deducible 5UF';
			    else if($nombrePlan[$i] == 'PREMIUM_D10')
			        $tituloPlan = 'Premium Deducible 10UF';
			    else if($nombrePlan[$i] == 'PREMIUM_D20')
			        $tituloPlan = 'Premium Deducible 20UF';
			    else if($nombrePlan[$i] == 'PREMIUM_D50')
			        $tituloPlan = 'Premium Deducible 50UF';*/
				
		$seguros[] = [
			'cotizacionServicioID' => $cotizacionServicioID,
			'nombre_aseguradora' => 'Sura',
			'logo' => get_stylesheet_directory_uri().'/sura.png',
			'titulo_seguro' => $tituloPlan,
			'nombre_plan' => $nombrePlan[$i],
			'cubiertas' => $coberturas,
			'detalle_coberturas' => $detalle_coberturas,
			'precio_clp_aprox' => $valorEnPeso[$i],
			'uf_anual' => $valorEnUf[$i]
		];
		
	}

	echo '<div class="container">';
	// Iterar sobre los seguros y renderizar cada tarjeta
	foreach ($seguros as $seguro) {
		renderizarTarjetaSeguro($seguro);
	}
	echo '</div>';
}
    
?>
<script>
function togglePopup(event, planName) {
    event.preventDefault();
    event.stopPropagation();
    const popupId = planName ? 'coverage-popup-' + planName : 'coverage-popup';
    const popup = document.getElementById(popupId);
    if (popup) {
        popup.classList.toggle('active');
    }
}

// Close popup when clicking outside the content
document.addEventListener('click', function(event) {
    const popups = document.querySelectorAll('.coverage-popup.active');
    popups.forEach(function(popup) {
        const content = popup.querySelector('.popup-content');
        if (!content.contains(event.target)) {
            popup.classList.remove('active');
        }
    });
});
</script>
