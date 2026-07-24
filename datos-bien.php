<?php
session_start();

    $nombrePlan = $_POST['nombrePlan'];
    $precio_clp = $_POST['precio_clp'];
    $cotizacionServicioID = $_POST['cotizacionServicioID'];
    
    $_SESSION['nombrePlan'] = $nombrePlan;
    $_SESSION['precio_clp'] = $precio_clp;
    
    $fecha = date('Y-m-d');
    
    if (isset($_SESSION['usuario'])) {
        $usuario = $_SESSION['usuario'];
    
        $patente = htmlspecialchars($usuario['patente']);
        $modelo = htmlspecialchars($usuario['modelo']);
        $nombre_marca = htmlspecialchars($usuario['nombre_marca']);
        $nombre_modelo = htmlspecialchars($usuario['nombre_modelo']);
    } else {
        echo "No hay datos de usuario en la sesión.";
    }
    
    $_SESSION['cotizacionServicioID'] = $cotizacionServicioID;
    
    //$cotizacionServicioID = "a0abc8cb-c2b6-481a-9e56-6778f540e310";
    //$nombrePlan = "PREMIUM_D3";
    
    // Formato de fecha y hora
    date_default_timezone_set('America/Santiago'); // Ajusta a tu zona si es necesario
    $fechaHora = date('Y-m-d H:i:s');
    
    //se guardan datos en un archivo local
    //$linea = "fecha: $fechaHora; nombre: $nombre; rut: $rut; fecha_nacimiento: $fecha_nacimiento; anio: $anio; codmarca: $marca; marca: $nombre_marca; codmodelo: $modelo; modelo: $nombre_modelo; codcomuna: $comuna;  comuna: $nombre_comuna; patente: $patente; uso: $uso;\n";
    //file_put_contents("cotizaciones.txt", $linea, FILE_APPEND | LOCK_EX);
    
    // URL de la API
    $url = 'https://hub-qa.segurossura.cl/seguros-generales/vehiculos-beneficios/api/ConsultaPorPpu';
	$token = recuperarToken();
    
    // Datos de entrada (JSON)
    $data = [
        "Patente" => $patente
    ];
    
    // Configura los headers incluyendo la key y authorization
    $headers = [
        'Content-Type: application/json',
        'Ocp-Apim-Subscription-Key: 712c61e9edff4ad4842111902b1dcf18',
        "Authorization: Bearer $token"
        //'Authorization: ey12345'
    ];
    
    // Iniciar cURL
    $ch = curl_init($url);
    
    // Configurar opciones cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Ejecutar la petición
    $response = curl_exec($ch);
    
    // Manejo de error cURL
    if (curl_errno($ch)) {
        echo 'Error en cURL: ' . curl_error($ch);
        curl_close($ch);
        exit;
    }
    
    curl_close($ch);
    
    // Decodificar la respuesta JSON
    $result = json_decode($response, true);
    
    $NumeroChasis = $result['data']['NumeroChasis'];
    $colorVehiculo = $result['data']['Color'];
    $NumeroMotor = $result['data']['NumeroMotor'];
    
    // Verificar y mostrar el NumeroChasis
    if (isset($NumeroChasis)) {
        //echo 'Número de Chasis: ' . $NumeroChasis;
        $_SESSION['NumeroChasis'] = $NumeroChasis;
        $_SESSION['colorVehiculo'] = $colorVehiculo;
        $_SESSION['NumeroMotor'] = $NumeroMotor;
    } else {
        echo 'Error o datos no encontrados';
    }

    //API cotizacion
    $url = 'https://hub-qa.segurossura.cl/seguros-generales/vechiculos-cotizacion';

    $headers = [
        'Content-Type' => 'text/xml; charset=utf-8',
        'SOAPAction'   => '"http://ws.rsa.cl/WsIndustrializacion/GenerarCotizacion"',
        'Authorization'=> 'Bearer ' . $token,
        'Ocp-Apim-Subscription-Key' => '712c61e9edff4ad4842111902b1dcf18',
    ];

    $body = <<<XML
	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://ws.rsa.cl/" xmlns:epv="http://schemas.datacontract.org/2004/07/EPV.Servicios.Services">
		<soapenv:Header>
			<ws:DatosTransaccion>
				<epv:contrasena>Sura2025_06_R</epv:contrasena>
				<epv:fechaHora>$fecha</epv:fechaHora>
				<epv:idContexto></epv:idContexto>
				<epv:idTransaccion></epv:idTransaccion>
				<epv:sistema></epv:sistema>
				<epv:usuario>77496140@ProductoraDiaz.cl</epv:usuario>
			</ws:DatosTransaccion>
		</soapenv:Header>
		<soapenv:Body>
			<ws:GeneraCotizacionRequest>
				<ws:Oferta>
					<epv:cotizacionServicioID>$cotizacionServicioID</epv:cotizacionServicioID>
					<epv:nombrePlan>$nombrePlan</epv:nombrePlan>
				</ws:Oferta>
			</ws:GeneraCotizacionRequest>
		</soapenv:Body>
	</soapenv:Envelope>
	XML;

    wp_custom_log("body en datos bien".$body);

    $response = wp_remote_post($url, [
        'headers' => $headers,
        'body'    => $body,
        'timeout' => 30, // Opcional: aumenta el tiempo de espera si tarda
		'sslverify' => false
    ]);

	//echo "response ".$response;

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

    if (isset($_SESSION['respuesta_api'])) {
        $respuesta = $_SESSION['respuesta_api'];
        unset($_SESSION['respuesta_api']);

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
		
		preg_match('/<NumeroCotizacion>(.*?)<\/NumeroCotizacion>/', html_entity_decode($respuesta), $match);

		if (isset($match[1])) {
			$NumeroCotizacion = $match[1];
		}
		
        $_SESSION['NumeroCotizacion'] = $NumeroCotizacion;

        wp_custom_log("respuesta ".$respuesta);
        wp_custom_log("NumeroCotizacion ".$NumeroCotizacion);
        wp_custom_log("codigoError ".$codigoError);
        wp_custom_log("detalleError ".$detalleError);
        
		// Validar si todo está OK
		if ($codigoError !== '0' /*|| strtoupper($detalleError) !== 'OK'*/) {
			//Hay un error al llamar API de ofertas
			echo 'Hay un error en la pagina, contactese con el administrador<br>';
			echo 'codigoError: '.$codigoError.'<br>';
			echo 'detalleError: '.$detalleError.'<br>';
		} else {
			//llamar a api de tipo de vechiculo
			$url = 'https://hub-qa.segurossura.cl/seguros-generales-vehiculo/api/modelos/'.$modelo.'/tipos-vehiculo';
			//$token = recuperarToken();

			$headers = [
				'Authorization'=> 'Bearer ' . $token,
				'Ocp-Apim-Subscription-Key' => '712c61e9edff4ad4842111902b1dcf18',
			];

			$response = wp_remote_get($url, [
				'headers' => $headers,
				'timeout' => 30, // Opcional: aumenta el tiempo de espera si tarda
				'sslverify' => false
			]);

			//echo "response2 ".$response;

			if (is_wp_error($response)) {
				$error_message = $response->get_error_message();
				error_log("❌ Error HTTP: $error_message");
				//echo "Error: $error_message";
			} else {
				$status_code = wp_remote_retrieve_response_code($response);
                $body = wp_remote_retrieve_body($response);
                $tiposVehiculo = json_decode($body, true);
				
				// Guardar resultado en sesión para usar en otra pantalla
				if (!session_id()) session_start();
				
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
				
            <div class="card shadow">
                <h3 class="text-center mb-4 fw-bold">Datos del Vehículo</h3>
				<form method="post" action="/suscripcion" onsubmit="return prepararEnvio()">
					<div class="mb-3 fw-bold">
						<label for="marca">Marca:</label>
						<input type="text" name="marca" id="marca" value="<?php echo htmlspecialchars($nombre_marca); ?>" required disabled>
					</div>
					<div class="mb-3 fw-bold">
						<label for="modelo">Modelo:</label>
						<input type="text" name="modelo" id="modelo" value="<?php echo htmlspecialchars($nombre_modelo); ?>" required disabled>
					</div>
					<div class="mb-3 fw-bold">
						<label for="motor_chasis">Chasis:</label>
						<input type="text" name="chasis" id="chasis" value="<?php echo htmlspecialchars($NumeroChasis); ?>" required disabled>
					</div>
					<div class="mb-3 fw-bold">
						<label for="motor_chasis">Motor:</label>
						<input type="text" name="motor" id="motor" value="<?php echo htmlspecialchars($NumeroMotor); ?>" required disabled>
					</div>
					<div class="mb-3 fw-bold">
						<label for="motor_chasis">Color:</label>
						<input type="text" name="colorVehiculo" id="colorVehiculo" value="<?php echo htmlspecialchars($colorVehiculo); ?>" required disabled>
					</div>
					<div class="form-group">
						<label for="tipo_vehiculo">Tipo de vehículo:</label>
						<select name="tipo_vehiculo" id="tipo_vehiculo" required>
							<option value="">Seleccione...</option>
							<?php if (!empty($tiposVehiculo)): ?>
								<?php foreach ($tiposVehiculo as $vehiculo): ?>
									<option value="<?php echo htmlspecialchars($vehiculo['codigo']); ?>">
										<?php echo htmlspecialchars($vehiculo['nombre']); ?>
									</option>
								<?php endforeach; ?>
							<?php else: ?>
								<option value="">No se encontraron datos</option>
							<?php endif; ?>
						</select>
					</div>
					<input type="hidden" name="tipoVehSeleccionado" id="tipoVehSeleccionado">
					<input type="hidden" name="csrf_token" value="lib3730"/>
                    <div class="text-end">
					<button class="btn btn-success" type="submit">Continuar a Suscripcion</button>
					</div>
				</form>
    		</div>
			<?
        }
		
    }
	else{
		echo "No hay resultados para mostrar.";
	}
?>

<script>
function prepararEnvio() {
  const combo = document.getElementById("tipo_vehiculo");
  const texto = combo.options[combo.selectedIndex].text;
  document.getElementById("tipoVehSeleccionado").value = texto;
  return true;
}
</script>
