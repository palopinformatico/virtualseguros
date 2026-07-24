<?php

// URL de la API
$url = 'https://hub-qa.segurossura.cl/seguros-generales-soporte/lap/api/lap/poliza';
$token = recuperarToken();
    
if (isset($_SESSION['usuario'])) {
    $usuario = $_SESSION['usuario'];

    $rut = htmlspecialchars($usuario['rut']);
    $rut = htmlspecialchars($usuario['rut']);
    $rut = substr($rut, 0, -1);
    $dv = substr($rut, -1);
    $nombre = htmlspecialchars($usuario['nombre']);
    $apaterno = htmlspecialchars($usuario['apaterno']);
    $amaterno = htmlspecialchars($usuario['apaterno']);
    $email = htmlspecialchars($usuario['email']);
    $telefono = htmlspecialchars($usuario['telefono']);
    $anio = htmlspecialchars($usuario['anio']);
    $marca = htmlspecialchars($usuario['marca']);
    $modelo = htmlspecialchars($usuario['modelo']);
    $patente = htmlspecialchars($usuario['patente']);
    $nombre_marca = $usuario['nombre_marca'];
    $nombre_modelo = $usuario['nombre_modelo'];

    if (isset($_SESSION['NumeroCotizacion'])) $NumeroCotizacion = $_SESSION['NumeroCotizacion'];
    else $NumeroCotizacion = "";
    
    if (isset($_SESSION['tipoVehSeleccionado'])) $tipoVehSeleccionado = $_SESSION['tipoVehSeleccionado'];
    else $tipoVehSeleccionado = "";
    
    if (isset($_SESSION['numeroPoliza'])) $numeroPoliza = $_SESSION['numeroPoliza'];
    else $numeroPoliza = "";
    
    wp_custom_log("tipoVehSeleccionado ".$tipoVehSeleccionado);
    wp_custom_log("NumeroCotizacion ".$NumeroCotizacion);
    wp_custom_log("numeroPoliza ".$numeroPoliza);
    
    
    // Datos a enviar en el cuerpo de la solicitud
    $data = [
        "numeroCotizacion" => $NumeroCotizacion,
        "numeroPoliza" => $numeroPoliza,
        "rutAsegurado" => $rut,
        "dvAsegurado" => $dv,
        "nombresAsegurado" => $nombre,
        "segundoNombreAsegurado" => ".",
        "primerApellidoAsegurado" => $apaterno,
        "segundoApellidoAsegurado" => $amaterno,
        "emailAsegurado" => $email,
        "corredor" => "77496140",
        "origen" => "2K12_EPV_IndustrializacionIC-T",
        "redirectUrl" => "https://as-lap-web-prd.azurewebsites.net/",
        "rutProveedor" => "77496140",
        "marca" => $nombre_marca,
        "modelo" => $nombre_modelo,
        "patente" => $patente,
        "a\\u00f1o" => $anio,
        "telefono" => $telefono,
        "tipoVehiculo" => $tipoVehSeleccionado,
        "vin" => "",
        "direccion" => "",
        "comuna" => "",
        "kilometraje" => "0"
    ];
    
    function manual_json_encode($array) {
        $parts = [];
        foreach ($array as $key => $value) {
            // Escapar comillas y backslashes en el valor
            $escapedKey = addslashes($key);
            $escapedValue = addslashes((string)$value);
            $parts[] = '"' . $escapedKey . '":"' . $escapedValue . '"';
        }
        return '{' . implode(',', $parts) . '}';
    }
    
    $jsonData = manual_json_encode($data);
    
    // Codificar el cuerpo como JSON
    //$jsonData = json_encode($data);
    
    // Encabezados HTTP
    $headers = [
        "Content-Type: application/json",
        "Ocp-Apim-Subscription-Key: 6866acda868341dd9ab261965122a0e0",
    	"Authorization: ".$token
    ];
    
    // Inicializar cURL
    $ch = curl_init($url);
    
    // Configurar cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Solo para pruebas locales
    
    // Ejecutar cURL
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Verificar errores
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
    }
    
    // Cerrar cURL
    curl_close($ch);
    
    // Decodificar respuesta
    $result = json_decode($response, true);
    
    wp_custom_log("idDatosGps ".$result['result']['idDatosGps']);
    
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
    
    <?php if (isset($result['result']['idDatosGps'])): ?>
    				
            <br>
            <br>
            <br>
                <div class="card shadow">
                    <h3 class="text-center mb-4 fw-bold">Generar solicitud LAP</h3>
    				<form method="post" action="/finaliza">
                        <div class="mb-3 fw-bold">
                            <label>Solicitar LAP:<span style="color: red;">*</span></label><br>
                            <div class="form-check">
                                <input type="radio" name="solicitaLap" class="form-check-input" value="1" required checked style="outline: 2px solid green;"> Acepta
                            </div>
                            <div class="form-check">
                                <input type="radio" name="solicitaLap" class="form-check-input" value="2" style="outline: 2px solid green;"> Posterga
                            </div>
                        </div>
    					<input type="hidden" name="csrf_token" value="10ser"/>
                        <div class="text-end">
    					<button class="btn btn-success" type="submit">Continuar</button>
    					</div>
    				</form>
        		</div>
            <br>
            <br>
            <br>
    
    <?php else: ?>
        <h2>Error al generar solicitud LAP</h2>
        <p>Codigo HTTP: <?= $httpCode ?></p>
        <pre><?= isset($error_msg) ? $error_msg : htmlspecialchars($response) ?></pre>
    <?php endif; ?>
<br>
<br>
<br>
<?php
} else {
    echo "Error, contacte al administrador.";
?>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<?php
}
?>