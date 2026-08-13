<?php

// URL de la API
$url = 'https://hub-qa.segurossura.cl/seguros-generales-soporte/lap/api/lap/sgpsmail';
$token = recuperarToken();

$solicitaLap = $_POST['solicitaLap'];
wp_custom_log('solicitaLap: '.$solicitaLap);
    
if (isset($_SESSION['usuario'])) {
    $usuario = $_SESSION['usuario'];
    //$usuario = json_decode($_SESSION['usuario'], true);

    $rut = htmlspecialchars($usuario['rut']);
    $rutCompleto = $rut;
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
    $esNuevo = htmlspecialchars($usuario['esNuevo']);

    if (isset($_SESSION['NumeroCotizacion'])) $NumeroCotizacion = $_SESSION['NumeroCotizacion'];
    else $NumeroCotizacion = "";
    
    if (isset($_SESSION['numeroPoliza'])) $numeroPoliza = $_SESSION['numeroPoliza'];
    else $numeroPoliza = "";
    
    // Datos que se enviar¨¢n en el cuerpo del POST (JSON)
    $data = [
        "numeroCotizacion" => $NumeroCotizacion,
        "rutAsegurado" => (int)$rut,
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
    
    // Encabezados requeridos
    $headers = [
        'Content-Type: application/json',
        'Ocp-Apim-Subscription-Key: 6866acda868341dd9ab261965122a0e0',
        'Authorization: '.$token
    ];
    
    // Inicializar cURL
    $ch = curl_init($url);
    
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
    
        // Mostrar mensaje 'success' si existe
        if (isset($decoded['result']['mensaje']) && $decoded['result']['mensaje'] === 'success') {
            wp_custom_log('solicitaLap en finaliza: '.$solicitaLap);
            
            // URL de la API
            $url = 'https://hub-qa.segurossura.cl/seguros-generales-soporte/lap/api/lap/polizaemitir';
            
            // Datos a enviar
            $data = [
                "numeroCotizacion" => $NumeroCotizacion,
                "numeroPoliza" => $numeroPoliza,
                "rutAsegurado" => $rut,
                "patente" => $patente
            ];
            
            // Convertir a JSON
            $jsonData = json_encode($data);
            
            // Headers requeridos
            $headers = [
                'Content-Type: application/json',
                'Ocp-Apim-Subscription-Key: 6866acda868341dd9ab261965122a0e0',
                'Authorization: '.$token
            ];
            
            // Inicializar cURL
            $ch = curl_init($url);
            
            // Configurar opciones cURL
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Solo para entorno QA
            
            // Ejecutar cURL
            $response = curl_exec($ch);
            
            // Verificar si hubo error
            if (curl_errno($ch)) {
                echo 'Error en segundo cURL: ' . curl_error($ch);
            } else {
                // Decodificar respuesta JSON
                $decoded = json_decode($response, true);
                
                if (isset($decoded['result'])) {
                    wp_custom_log('error: ' . print_r($decoded['result'], true));
                } else {
                    //echo "Respuesta inesperada poiliza a emitir: " . $response;
                    wp_custom_log("Respuesta inesperada poiliza a emitir: " . $response);
                }
            }
            
            // Cerrar cURL
            curl_close($ch);
            
            // Formato de fecha y hora
            date_default_timezone_set('America/Santiago'); // Ajusta a tu zona si es necesario
            $fechaHora = date('Y-m-d H:i:s');
            
            $cotizacionServicioID = $_SESSION['cotizacionServicioID'];
    				
    		//se gurdan datos de consulta en un archivo para posterior consulta
            $linea = "fecha: $fechaHora; rut: $rutCompleto; cotizacionServicioID: $cotizacionServicioID; patente: $patente; terminado: SI \n";
            file_put_contents("consultas.txt", $linea, FILE_APPEND | LOCK_EX);
    
        } else {
            echo "Respuesta inesperada posterga/acepta: " . $response;
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
    
    <br>
    <br>
    <br>
    <div class="card shadow">
        <h3 class="text-center mb-4 fw-bold">Proceso Finalizado</h3>
    	<form method="post" action="/">
    		<div>
                <p><strong>Póliza <?=$numeroPoliza?></strong></p>
                <p>La póliza ha sido generada y enviada a su email.</p>
                <p>En caso de no ver el email revise su carpeta de spam.</p>
                <p>Muchas gracias por adquirir su seguro con virtualseguros.cl</p>
    		</div>
    	</form>
    </div>
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