<?php
/* Template Name: Subir documento */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../../PHPMailer/src/Exception.php';
require __DIR__ . '/../../../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../../../PHPMailer/src/SMTP.php';

if ( session_status() !== PHP_SESSION_ACTIVE && ! headers_sent() ) {
    session_start();
}

if (isset($_SESSION['usuario'])) {
    $usuario = $_SESSION['usuario'];

    $patente = htmlspecialchars($usuario['patente']);
    $nombre = htmlspecialchars($usuario['nombre']);
    $apaterno = htmlspecialchars($usuario['apaterno']);
    $amaterno = htmlspecialchars($usuario['amaterno']);
    $fecha_nacimiento = htmlspecialchars($usuario['fecha_nacimiento']);
    $anio = htmlspecialchars($usuario['anio']);
    $email = htmlspecialchars($usuario['email']);
    $telefono = htmlspecialchars($usuario['telefono']);
    $rut = htmlspecialchars($usuario['rut']);
    $comuna = htmlspecialchars($usuario['comuna']);
    $genero = htmlspecialchars($usuario['genero']);
    $direccion = htmlspecialchars($usuario['direccion']);
	$esNuevo = htmlspecialchars($usuario['esNuevo']);
    $marca = $usuario['marca'];
    $modelo = $usuario['modelo'];
    $nombre_marca = $usuario['nombre_marca'];
    $nombre_modelo = $usuario['nombre_modelo'];
    $nombre_comuna = $usuario['nombre_comuna'];
    $uso = $usuario['uso'];
    $codigoSOL = $usuario['codigoSOL'];
} else {
    echo "No hay datos de usuario en la sesión.";
}

if (isset($_SESSION['NumeroChasis'])) $NumeroChasis = $_SESSION['NumeroChasis'];
else $NumeroChasis = "";

if (isset($_SESSION['colorVehiculo'])) $colorVehiculo = $_SESSION['colorVehiculo'];
else $colorVehiculo = "";

if (isset($_SESSION['NumeroMotor'])) $NumeroMotor = $_SESSION['NumeroMotor'];
else $NumeroMotor = "";

if (isset($_SESSION['cotizacionServicioID'])) $cotizacionServicioID = $_SESSION['cotizacionServicioID'];
else $cotizacionServicioID = "";

if (isset($_SESSION['NumeroCotizacion'])) $NumeroCotizacion = $_SESSION['NumeroCotizacion'];
else $NumeroCotizacion = "";

if (isset($_SESSION['nombrePlan'])) $nombrePlan = $_SESSION['nombrePlan'];
else $nombrePlan = "";

if (isset($_SESSION['precio_clp'])) $precio_clp = $_SESSION['precio_clp'];
else $precio_clp = "";

if (isset($_SESSION['tipoVehSeleccionado'])) $tipoVehSeleccionado = $_SESSION['tipoVehSeleccionado'];
else $tipoVehSeleccionado = "";


$tipoVehSeleccionado = strtr($tipoVehSeleccionado, [
    'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
    'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
    'Ñ' => 'N', 'ñ' => 'n'
]);

//Se guardan los datos de los usuarios que han realizado la suscripcion
date_default_timezone_set('America/Santiago');
$fechaHora = date('Y-m-d H:i:s');
$fechaHoraJson = date('Y-m-d')."T".date('H:i:s');

//definir fecha +14 dias
$fecha = new DateTime();
$fecha->modify('+14 days');
$fechaMaxInspec = $fecha->format('Y-m-d\TH:i:s');

$uuid = '';

if (!empty($_GET['uuid'])) {
    $uuid = sanitize_text_field($_GET['uuid']);
    $_SESSION['uuid'] = $uuid;
} elseif (!empty($_POST['uuid'])) {
    $uuid = sanitize_text_field($_POST['uuid']);
    $_SESSION['uuid'] = $uuid;
} elseif (!empty($_SERVER['QUERY_STRING'])) {
    parse_str($_SERVER['QUERY_STRING'], $query_params);
    if (!empty($query_params['uuid'])) {
        $uuid = sanitize_text_field($query_params['uuid']);
        $_SESSION['uuid'] = $uuid;
    }
} elseif (!empty($_SESSION['uuid'])) {
    $uuid = sanitize_text_field($_SESSION['uuid']);
} elseif (!empty($_SESSION['oneclick_uuid'])) {
    $uuid = sanitize_text_field($_SESSION['oneclick_uuid']);
    $_SESSION['uuid'] = $uuid;
}

wp_custom_log('uuid en subir-documento por sesion: ' . (isset($_SESSION['uuid']) ? $_SESSION['uuid'] : 'vacío'));
wp_custom_log('uuid en subir-documento por get: ' . (!empty($_GET['uuid']) ? $_GET['uuid'] : 'vacío'));
wp_custom_log('uuid final en subir-documento: ' . $uuid);

//se guardan datos en un archivo local
$linea = "patente: $patente; rut: $rut; fecha: $fechaHora; nombre: $nombre; apaterno: $apaterno; amaterno: $amaterno; email: $email; telefono: $telefono; fecha_nacimiento: $fecha_nacimiento; anio: $anio; codmarca: $marca; marca: $nombre_marca; codmodelo: $modelo; modelo: $nombre_modelo; codcomuna: $comuna; comuna: $nombre_comuna; genero: $genero; direccion: $direccion; esNuevo: $esNuevo; uso: $uso; uuid: $uuid; NumeroChasis: $NumeroChasis; colorVehiculo: $colorVehiculo; NumeroMotor: $NumeroMotor; cotizacionServicioID: $cotizacionServicioID; NumeroCotizacion: $NumeroCotizacion; nombrePlan: $nombrePlan; precio_clp: $precio_clp; tipoVehSeleccionado: $tipoVehSeleccionado; codigoSOL: $codigoSOL\n";
file_put_contents("suscripciones.txt", $linea, FILE_APPEND | LOCK_EX);

if ($esNuevo == 'Usado'){
	
	//Se hace llamado a api de acepta/posterga
	$url = 'https://hub-qa.segurossura.cl/seguros-generales-soporte/lap/api/lap/sgpsmail';
	$token = recuperarToken();

	$solicitaLap = $_POST['solicitaLap'];
	wp_custom_log('solicitaLap en subir-documento: '.$solicitaLap);

	$rutSinDv = substr($rut, 0, -1);

	// Datos que se enviar¨¢n en el cuerpo del POST (JSON)
	$data = [
		"numeroCotizacion" => $NumeroCotizacion,
		"rutAsegurado" => (int)$rutSinDv,
		"nombresAsegurado" => $nombre,
		"emailAsegurado" => $email,
		"corredor" => "Virtual Seguros",
		"patente" => $patente,
		"tipoComunicacion" => (int)$solicitaLap,
		"estadoVehiculo" => $esNuevo,
		"esIntermediado" => true,
		"esAutoClickComercial" => true
	];

	// Codificar a JSON
	$jsonData = json_encode($data);
	wp_custom_log('$data en subir-documento '.$data);
	wp_custom_log('$data en subir-documento '.$jsonData);

	// Encabezados requeridos
	$headers = [
		'Content-Type: application/json',
		'Ocp-Apim-Subscription-Key: 6866acda868341dd9ab261965122a0e0',
		'Authorization: '.$token
	];

	// Inicializar cURL
	$ch = curl_init($url);
	wp_custom_log("token en subir-documento: ".$token);

	// Configurar opciones cURL
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // solo en desarrollo

	// Ejecutar la solicitud
	$response = curl_exec($ch);

	// Verificar si hay errores
	if (curl_errno($ch)) {
		echo 'Error en cURL: ' . curl_error($ch);

		// Cerrar cURL
		curl_close($ch);
	} else {
		// Cerrar cURL
		curl_close($ch);
		$decoded = json_decode($response, true);
		wp_custom_log("response: ".$response);

		// Mostrar mensaje 'success' si existe
		if (isset($decoded['result']['mensaje']) && $decoded['result']['mensaje'] === 'success') {
	
			//llamado a API
			$url = "https://hub-qa.segurossura.cl/seguros-generales/inspecciones-vehiculo/api/SolicitudInspeccion/SolicitudInspeccionCreate";

			$rut_formateado = substr($rut, 0, -1) . "-" . substr($rut, -1);

			// Datos de entrada en arreglo PHP
			$data = [
				"Asegurado" => [
					"ApellidoMaterno" => $amaterno,
					"ApellidoPaterno" => $apaterno,
					"DirAsegurado" => $direccion,
					"EmailAsegurado" => $email,
					"FonoACliente" => $telefono,
					"FonoBCliente" => $telefono,
					"NombreAsegurado" => $nombre,
					"RutAsegurado" => $rut_formateado
				],
				"CanalId" => "46",
				"CorreoCorredor" => "contacto@virtualseguros.cl",
				"FechaCoordina" => $fechaHoraJson,
				"FechaMaximaInspeccion" => $fechaMaxInspec,
				"FechaSolicitud" => $fechaHoraJson,
				"Filler1" => "",
				"Filler2" => "",
				"Filler3" => "",
				"Filler4" => "",
				"HomologaCodigos" => false,
				"IdCiudad" => "0047SAN  00",
				"IdComuna" => "0045COL  00",
				"IdProveedor" => "57",
				"IdRegion" => "004613   00",
				"Idsolicitud" => "0",
				"IdSucursalVeh" => "0049SAN  00",
				"NombreContacto" => "SEGURO AUTO FULL",
				"NroInspeccionProveedor" => "0",
				"NumMandato" => "0",
				"NumPropuestaCorredor" => "0",
				"ObsMandato" => "",
				//"Poliza" => "976974",
				"Poliza" => $NumeroCotizacion,
				"Ramo" => "EE",
				"RutCorredor" => "77496140-2",
				"RutInspector" => "0",
				"Solicitante" => [
					"EmailSolicitante" => $email,
					"NombreSolicitante" => $nombre." ".$apaterno." ".$amaterno
				],
				"TipoInspeccion" => "0",
				"Vehiculo" => [
					"CodigoModelo" => $codigoSOL,
					"ColorVeh" => $colorVehiculo,
					"FabricacionVeh" => $anio,
					"IdMarcaVeh" => "CHE",
					"NumChasisVeh" => $NumeroChasis,
					"NumMotorVeh" => $NumeroMotor,
					"PatenteVeh" => $patente,
					"UsoVeh" => "P"
				]
			];

			// Convertimos el array a JSON
			$jsonData = json_encode($data);
			wp_custom_log("data en subir-documento auto usado: ".$data);
			wp_custom_log("jsonData en subir-documento auto usado: ".$jsonData);

			// Inicializamos cURL
			$ch = curl_init($url);

			// Configuración de cURL
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // solo en desarrollo
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				"Content-Type: application/json",
				"Accept: application/json",
				"Ocp-Apim-Subscription-Key: 712c61e9edff4ad4842111902b1dcf18",
				"Authorization: Bearer ".$token
			]);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

			// Ejecutamos la petición
			$response = curl_exec($ch);

			// Verificamos si hubo error
			if (curl_errno($ch)) {
				echo "Error en cURL: " . curl_error($ch);
				exit;
			}

			// Cerramos conexión
			curl_close($ch);

			// Decodificamos la respuesta JSON
			$result = json_decode($response, true);
			
			if (isset($result['errors'])) {
			// La API devolvió un error
			echo "<p style='color:red;'>Error recibido: " . htmlspecialchars($result['errors']) . "</p>";
			} else {
				// La API devolvió respuesta correcta
				$numeroSolicitud = htmlspecialchars($result['numeroSolicitud'] ?? 'No disponible');
				$numeroInspeccion = htmlspecialchars($result['numeroInspeccion'] ?? 'No disponible');
			}

			wp_custom_log("numeroSolicitud en subir-documento auto usado: ".$numeroSolicitud);
			wp_custom_log("numeroInspeccion en subir-documento auto usado: ".$numeroInspeccion);
			#############################################
			
			// Configuración del correo
			$mail = new PHPMailer(true);

			try {
				// Configuración SMTP
				$mail->isSMTP();
				$mail->Host = 'mail.virtualseguros.cl'; // reemplaza con tu dominio
				$mail->SMTPAuth = true;
				$mail->Username = 'api@virtualseguros.cl'; // tu correo
				$mail->Password = 'PVXpGskaSzY7N#R';
				$mail->SMTPSecure = 'ssl'; // o 'tls' dependiendo del servidor
				$mail->Port = 465; // o 587 para TLS

				$mail->setFrom('api@virtualseguros.cl', 'API Inspección');
				$mail->addAddress($email);
				
				$mail->isHTML(true);
				$mail->Subject = "Realizac Inspección de vehículo para obtención seguro automotriz";

				$mail->Body = "
					<h2>virtualseguros.cl</h2>
					<p>Para terminar el proceso de solicitud de Seguro Automotriz y realizar la inspección haz click en este <a target='_blank' href='https://auto.inspeccionvehiculo.com/auth'>enlace</a>.</p>
					<p>El plazo para realizar la inspección del vehículo es de 15 días a partir de la llegada de este correo.</p>
				";

				$mail->send();

				//echo json_encode(["mensaje" => "Correo enviado correctamente."]);
				
				// Redireccionar a la URL de LAP
				//header("Location: lap-solicitud");
				//exit;
				
				// Formato de fecha y hora
				date_default_timezone_set('America/Santiago'); // Ajusta a tu zona si es necesario
				$fechaHora = date('Y-m-d H:i:s');
						
				//se gurdan datos de consulta en un archivo para posterior consulta
				$linea = "fecha: $fechaHora; rut: $rut; cotizacionServicioID: $cotizacionServicioID; patente: $patente; terminado: SI \n";
				file_put_contents("consultas.txt", $linea, FILE_APPEND | LOCK_EX);

			} catch (Exception $e) {
				http_response_code(500);
				echo json_encode(["error" => "Error al enviar correo: " . $mail->ErrorInfo]);
			}
		}
		else{
			echo "<p style='color:red;'>Error al hacer llamado a api de acepta/posterga </p>";
		}
	}
}
?>
<style>
	body {
		font-family: Arial, sans-serif;
	}
	.form-group {
		margin-bottom: 15px;
		display: flex;
		align-items: center;
	}
	label {
		width: 150px;
	}
	select, input[type="text"] {
		padding: 8px;
		width: 200px;
	}
	.container {
		margin: 50px;
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
	.card {
		max-width: 700px;
		margin: auto;
		background-color: #fff;
		padding: 30px;
	}
</style>

<?php if ($esNuevo == 'Nuevo'): ?>

    <br>
    <div class="card shadow">
        <h3 class="text-center mb-4 fw-bold">Subir Factura</h3>
        <form id="facturaForm" method="post" action="/generar-poliza" enctype="multipart/form-data">
            <div class="form-group">
                <p>Subir Factura</p>
            </div>
            <label for="archivo">Selecciona un archivo:</label><br><br>
            <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
            <input type="file" name="archivo" id="archivo" required><br>
    
            <!-- Mensaje de error -->
            <div id="mensajeError" style="color: red; font-weight: bold; margin-top: 10px;"></div>
    
            <br>
			<input type='hidden' name='csrf_token' value='loq420'/>
            <div class="text-end">
                <button class="btn btn-success" type="submit">Subir factura y continuar</button>
            </div>
        </form>
    </div>
    <br>

<?php else: ?>

	<br>
	<br>
    <div class="card shadow">
        <h3 class="text-center mb-4 fw-bold">Realizar Inspección</h3>
    	<form method="post" action="/">
    		<div>
				<p>Nro Solicitud: <?=$numeroSolicitud?>
                <p>Muchas gracias por adquirir su seguro con virtualseguros.cl.</p>
                <p>A continuación debe realizar la inspección del vehículo en este <a  style="color: blue;" target="_blank" href="https://auto.inspeccionvehiculo.com/auth">enlace</a>.</p>
                <p>El plazo para realizar la inspección del vehículo es de 15 días a partir de hoy.</p>
                <p>También se te ha enviado un correo con el enlace.</p>
    		</div>
    	</form>
    </div>
	<br>
	<br>

<?php endif; ?>

<script>
const archivoInput = document.getElementById("archivo");
const mensajeError = document.getElementById("mensajeError");
const form = document.getElementById("facturaForm");

form.addEventListener("submit", function (e) {
    const archivo = archivoInput.files[0];
    mensajeError.textContent = "";

    if (archivo && archivo.size > 2097152) { // 2 MB
        e.preventDefault();
        mensajeError.textContent = "El archivo no debe superar los 2 MB.";
    }

    if (archivo && archivo.size == 0) { // 0 MB
        e.preventDefault();
        mensajeError.textContent = "El archivo no debe estar vacío.";
    }
});

// Limpia el mensaje de error si el archivo es válido al seleccionar uno nuevo
archivoInput.addEventListener("change", function () {
    const archivo = archivoInput.files[0];
    if (archivo && archivo.size <= 2097152) {
        mensajeError.textContent = "";
    }
    if (archivo && archivo.size > 0) {
        mensajeError.textContent = "";
    }
});
</script>
