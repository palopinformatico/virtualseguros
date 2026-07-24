<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer/src/Exception.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once '/home/virtualseguros/public_html/wp-load.php';

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);
    wp_custom_log($data);

    if (!$data) {
        http_response_code(400);
        echo json_encode(["error" => "JSON inválido"]);
        exit;
    }
    // Cambia esta clave a una secreta y privada
    //define('API_KEY', 'VIRTUAL_SEGUROS_a3Zp8Kd1QxLm2Rt9');
    
    // Validar clave API por encabezado
    //$headers = getallheaders();
    //$apiKey  = $headers['X-API-Key'] ?? $headers['x-api-key'] ?? null;
    
    /*if ($apiKey !== API_KEY) {
        http_response_code(401);
        echo json_encode(['error' => 'Acceso no autorizado']);
        exit;
    }*/
    
    // Obtener el JSON del cuerpo de la petición
    /*$input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'JSON inválido']);
        exit;
    }*/
    
    // Obtener datos desde JSON
    $idSolicitud = $data['idSolicitud'] ?? '';
    $idInspeccion = $data['idInspeccion'] ?? '';
    $patente = $data['patente'] ?? '';
    $estado = $data['aprueba_rechaza_inspeccion'] ?? '';
    $motivoCancelacion = $data['motivo_cancelacion'] ?? '';
    $motivoRechazo = $data['motivo_rechazo_distinto_danio'] ?? '';
    
    $archivo = "../suscripciones.txt";
    
    // Leer el archivo línea por línea
    $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    $registroMasReciente = null;
    $fechaMasReciente = null;
    
    foreach ($lineas as $linea) {
        if (stripos($linea, "patente: $patente") !== false) {
    
            // Buscar el campo fecha en la línea
            preg_match('/fecha:\s*([\d-]+\s[\d:]+)/', $linea, $matches);
    
            if (isset($matches[1])) {
                $fechaActual = strtotime($matches[1]);
    
                // Si es el primer match o es más reciente que el guardado
                if ($fechaMasReciente === null || $fechaActual > $fechaMasReciente) {
                    $fechaMasReciente = $fechaActual;
                    $registroMasReciente = $linea;
                }
            }
        }
    }
    
    // Mostrar el registro más reciente
    if ($registroMasReciente) {
        $uuid = "";
        $NumeroChasis = "";
        $colorVehiculo = "";
        $NumeroMotor = "";
        $cotizacionServicioID = "";
        $nombre = "";
        $apaterno = "";
        $amaterno = "";
        $email = "";
        $telefono = "";
        $rut = "";
        $comuna = "";
        $genero = "";
        $direccion = "";
        $tipoVehSeleccionado = "";
        
        $campos = explode(";", $registroMasReciente);
    
        foreach ($campos as $campo) {
            $campo = trim($campo);
            if (!empty($campo)) {
                list($clave, $valor) = explode(":", $campo, 2);
                
                switch (htmlspecialchars(trim($clave))) {
                    case "uuid":
                        $uuid = htmlspecialchars(trim($valor));
                        break;
                    case "NumeroChasis":
                        $NumeroChasis = htmlspecialchars(trim($valor));
                        break;
                    case "colorVehiculo":
                        $colorVehiculo = htmlspecialchars(trim($valor));
                        break;
                    case "NumeroMotor":
                        $NumeroMotor = htmlspecialchars(trim($valor));
                        break;
                    case "cotizacionServicioID":
                        $cotizacionServicioID = htmlspecialchars(trim($valor));
                        break;
                    case "nombre":
                        $nombre = htmlspecialchars(trim($valor));
                        break;
                    case "apaterno":
                        $apaterno = htmlspecialchars(trim($valor));
                        break;
                    case "amaterno":
                        $amaterno = htmlspecialchars(trim($valor));
                        break;
                    case "email":
                        $email = htmlspecialchars(trim($valor));
                        break;
                    case "telefono":
                        $telefono = htmlspecialchars(trim($valor));
                        break;
                    case "rut":
                        $rut = htmlspecialchars(trim($valor));
                        break;
                    case "codcomuna":
                        $comuna = htmlspecialchars(trim($valor));
                        break;
                    case "genero":
                        $genero = htmlspecialchars(trim($valor));
                        break;
                    case "direccion":
                        $direccion = htmlspecialchars(trim($valor));
                        break;
                    case "tipoVehSeleccionado":
                        $tipoVehSeleccionado = htmlspecialchars(trim($valor));
                        break;
                    case "NumeroCotizacion":
                        $NumeroCotizacion = htmlspecialchars(trim($valor));
                        break;
                
                    default:
                        break;
                }
            }
        }
    } else {
        echo "No se encontró la patente $patente.";
    }

    //if ($estado == "APROBADO"){
    if ($estado == "Aprobado"){
            
            wp_custom_log("uuid ".$uuid);
            //Se genera poliza
            $url = "https://hub-qa.segurossura.cl/seguros-transversales/oneclick/v1/token_enrollment/subscription-detail/" . $uuid;
        	$token = recuperarToken();
            
            // Inicializa cURL
            $ch = curl_init();
            
            // Headers requeridos
            $headers = [
                "Ocp-Apim-Subscription-Key: 9b17ac14f09d44a2baadba510066cf22",
                "Authorization: ".$token
            ];
            
            // Opciones de cURL
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // solo si hay problemas con el certificado
            
            // Ejecuta la petición
            $response = curl_exec($ch);
            
            // Manejo de errores
            if (curl_errno($ch)) {
                echo 'Error en cURL: ' . curl_error($ch);
                curl_close($ch);
                exit;
            }
            
            curl_close($ch);
            
            // Decodifica la respuesta JSON
            $data = json_decode($response, true);
            
            // Verifica si se recibió correctamente y extrae el paymentMethodId
            if (isset($data['paymentMethodId'])) {
                
                $paymentMethodId = $data['paymentMethodId'];
                //echo "El paymentMethodId es: " . $paymentMethodId;
                wp_custom_log("El paymentMethodId es: " . $paymentMethodId);
                
                $fecha = date('Y-m-d');
                //$fecha = date('Y-m-d', strtotime($fecha . ' -1 day'));
                $anio = date('Y');
            
                //llamar api generar poliza
                $url = 'https://hub-qa.segurossura.cl/seguros-generales/vechiculos-cotizacion/?soapAction=http%3A%2F%2Fws.rsa.cl%2FWsIndustrializacion%2FGenerarPoliza';
                
                $xmlBody = <<<XML
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://ws.rsa.cl/" xmlns:epv="http://schemas.datacontract.org/2004/07/EPV.Servicios.Services" xmlns:epv1="http://schemas.datacontract.org/2004/07/EPV.Servicios.Services.Models">
                   <soapenv:Header>
                      <ws:DatosTransaccion>
                         <epv:contrasena>Sura2025_06_R</epv:contrasena>
                         <epv:fechaHora>$fecha</epv:fechaHora>
                         <epv:idContexto/>
                         <epv:idTransaccion/>
                         <epv:sistema/>
                         <epv:usuario>77496140@ProductoraDiaz.cl</epv:usuario>
                      </ws:DatosTransaccion>
                   </soapenv:Header>
                   <soapenv:Body>
                      <ws:GeneraPolizaRequest>
                         <ws:Poliza>
                            <epv1:InfoPoliza>
                               <epv1:Materia>
                                  <epv1:ContinuidadCobertura>
                                     <epv1:ArchivosAdjuntos>
                                        <epv1:ArchivoAdjuntosModel>
                                           <epv1:archivosAdjuntoByte/>
                                           <epv1:nombreArchivo/>
                                        </epv1:ArchivoAdjuntosModel>
                                     </epv1:ArchivosAdjuntos>
                                     <epv1:companiaID>0</epv1:companiaID>
                                     <epv1:deducibleAnterior>TresUF</epv1:deducibleAnterior>
                                     <epv1:fechaVencimiento>$fecha</epv1:fechaVencimiento>
                                     <epv1:numeroPolizaAnterior>858892</epv1:numeroPolizaAnterior>
                                  </epv1:ContinuidadCobertura>
                                  <epv1:Factura>
                                     <epv1:ArchivosAdjuntos>
                                        <epv1:ArchivoAdjuntosModel>
                                           <epv1:archivosAdjuntoByte>aqui</epv1:archivosAdjuntoByte>
                                           <epv1:nombreArchivo>aaa.pdf</epv1:nombreArchivo>
                                        </epv1:ArchivoAdjuntosModel>
                                     </epv1:ArchivosAdjuntos>
                                     <epv1:facturaGuiaDespacho>0</epv1:facturaGuiaDespacho>
                                     <epv1:fechaFacturaGuiaDespacho>$fecha</epv1:fechaFacturaGuiaDespacho>
                                  </epv1:Factura>
                                  <epv1:Inspeccion>
                                     <epv1:numeroInspeccion>365528</epv1:numeroInspeccion>
                                  </epv1:Inspeccion>
                                  <epv1:chasis>$NumeroChasis</epv1:chasis>
                                  <epv1:color>$colorVehiculo</epv1:color>
                                  <epv1:esNuevo>false</epv1:esNuevo>
                                  <epv1:fechaInicioVigencia>$fecha</epv1:fechaInicioVigencia>
                                  <epv1:motor>$NumeroMotor</epv1:motor>
                                  <epv1:patente>$patente</epv1:patente>
                                  <epv1:tieneContinuidadCobertura>false</epv1:tieneContinuidadCobertura>
                                  <epv1:tieneInspeccion>false</epv1:tieneInspeccion>
                                  <epv1:tipoVehiculo>$tipoVehSeleccionado</epv1:tipoVehiculo>
                               </epv1:Materia>
                               <epv1:Personas>
                                  <epv1:Asegurado>
                                     <epv1:apellidoMaterno>$amaterno</epv1:apellidoMaterno>
                                     <epv1:apellidoPaterno>$apaterno</epv1:apellidoPaterno>
                                     <epv1:comunaID>$comuna</epv1:comunaID>
                                     <epv1:direccion>$direccion</epv1:direccion>
                                     <epv1:email>$email</epv1:email>
                                     <epv1:fonoContacto>$telefono</epv1:fonoContacto>
                                     <epv1:nombre>$nombre</epv1:nombre>
                                     <epv1:nombreCompleto>$nombre $apaterno $amaterno</epv1:nombreCompleto>
                                     <epv1:rut>$rut</epv1:rut>
                                     <epv1:sexo>$genero</epv1:sexo>
                                     <epv1:tipoPersona>Natural</epv1:tipoPersona>
                                  </epv1:Asegurado>
                                  <epv1:Pagador>
                                     <epv1:apellidoMaterno>$amaterno</epv1:apellidoMaterno>
                                     <epv1:apellidoPaterno>$apaterno</epv1:apellidoPaterno>
                                     <epv1:comunaID>$comuna</epv1:comunaID>
                                     <epv1:direccion>$direccion</epv1:direccion>
                                     <epv1:email>$email</epv1:email>
                                     <epv1:fonoContacto>$telefono</epv1:fonoContacto>
                                     <epv1:nombre>$nombre</epv1:nombre>
                                     <epv1:nombreCompleto>$nombre $apaterno $amaterno</epv1:nombreCompleto>
                                     <epv1:rut>$rut</epv1:rut>
                                     <epv1:sexo>$genero</epv1:sexo>
                                     <epv1:tipoPersona>Natural</epv1:tipoPersona>
                                  </epv1:Pagador>
                                  <epv1:Proponente>
                                     <epv1:apellidoMaterno>$amaterno</epv1:apellidoMaterno>
                                     <epv1:apellidoPaterno>$apaterno</epv1:apellidoPaterno>
                                     <epv1:comunaID>$comuna</epv1:comunaID>
                                     <epv1:direccion>$direccion</epv1:direccion>
                                     <epv1:email>$email</epv1:email>
                                     <epv1:fonoContacto>$telefono</epv1:fonoContacto>
                                     <epv1:nombre>$nombre</epv1:nombre>
                                     <epv1:nombreCompleto>$nombre $apaterno $amaterno</epv1:nombreCompleto>
                                     <epv1:rut>$rut</epv1:rut>
                                     <epv1:sexo>$genero</epv1:sexo>
                                     <epv1:tipoPersona>Natural</epv1:tipoPersona>
                                  </epv1:Proponente>
                               </epv1:Personas>
                               <epv1:PlanPago>
                                  <epv1:PAC>
                                     <epv1:bancoID>0</epv1:bancoID>
                                     <epv1:numeroCuenta>234234234234</epv1:numeroCuenta>
                                  </epv1:PAC>
                                  <epv1:PAT>
                                     <epv1:anioVencimiento>$anio</epv1:anioVencimiento>
                                     <epv1:bancoID>5</epv1:bancoID>
                                     <epv1:mesVencimiento>05</epv1:mesVencimiento>
                                     <epv1:numeroTarjeta>$paymentMethodId</epv1:numeroTarjeta>
                                     <epv1:tarjetaID>1</epv1:tarjetaID>
                                  </epv1:PAT>
                                  <epv1:diaVencimiento>Cinco</epv1:diaVencimiento>
                                  <epv1:formaPago>Oneclick</epv1:formaPago>
                                  <epv1:numeroCuotas>12</epv1:numeroCuotas>
                               </epv1:PlanPago>
                            </epv1:InfoPoliza>
                            <epv1:cotizacionServicioID>$cotizacionServicioID</epv1:cotizacionServicioID>
                         </ws:Poliza>
                      </ws:GeneraPolizaRequest>
                   </soapenv:Body>
                </soapenv:Envelope>
XML;
                
                $headers = [
                    "Content-Type: text/xml",
                    "SOAPAction: http://ws.rsa.cl/WsIndustrializacion/GenerarPoliza",
                    "Cache-Control: no-cache",
                    "Ocp-Apim-Subscription-Key: 712c61e9edff4ad4842111902b1dcf18",
                    "Authorization: Bearer $token"
                ];
                
                $ch = curl_init();
                
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlBody);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                // Para entornos de prueba donde el certificado puede ser inválido
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $response = curl_exec($ch);
        
                if (curl_errno($ch)) {
                    echo 'Error cURL: ' . curl_error($ch);
                    curl_close($ch);
                    exit;
                }
                
        		$numeroPoliza = "";
        		preg_match('/<a:numeroPoliza>(.*?)<\/a:numeroPoliza>/', html_entity_decode($response), $match);
        
        		if (isset($match[1])) {
        			$numeroPoliza = $match[1];
        			
        			//Se guardan los datos de los usuarios que han recibido la poliza
                    date_default_timezone_set('America/Santiago');
                    $fechaHora = date('Y-m-d H:i:s');
                    
                    //se guardan datos en un archivo local
                    $linea = "uuid: $uuid; numeroPoliza: $numeroPoliza; fecha: $fechaHora; \n";
                    file_put_contents("../polizas.txt", $linea, FILE_APPEND | LOCK_EX);
        		}
                //wp_custom_log($response);
                $lines = explode("\n", $response);
                $boundary = trim($lines[1]); // primera línea como boundary
                
                // Divide la respuesta en partes
                $parts = explode($boundary, $response);
                
                // Itera para encontrar el PDF (usualmente en la segunda parte)
                foreach ($parts as $part) {
                    
                    if (strpos($part, 'application/octet-stream') !== false) {
                        
                        // El contenido viene después de los headers de esta parte
                        list(, $pdfContent) = explode("\r\n\r\n", $part, 2);
                        $pdfContent = trim($pdfContent);
                
                        // Guardar archivo
                        $destinoPol ="/home/virtualseguros/public_html/polizas/";
                        file_put_contents($destinoPol.$rut."_poliza.pdf", $pdfContent);
                        //echo "PDF guardado exitosamente.";
                        
                        //enviar adjunto por correo
                        // Configuración del correo
                        $mail = new PHPMailer(true);
                    
                        try {
                            // Configuración SMTP
                            $mail->isSMTP();
                            $mail->Host = 'mail.virtualseguros.cl'; // reemplaza con tu dominio
                            $mail->SMTPAuth = true;
                            $mail->Username = 'api@virtualseguros.cl'; // tu correo creado en Banahosting
                            $mail->Password = 'PVXpGskaSzY7N#R';
                            $mail->SMTPSecure = 'ssl'; // o 'tls' dependiendo del servidor
                            $mail->Port = 465; // o 587 para TLS
                    
                            $mail->setFrom('api@virtualseguros.cl', 'API Inspección');
                            $mail->addAddress($email);
                            
                            $ruta_archivo = ABSPATH . "polizas/".$rut."_poliza.pdf";
                            // Adjuntar archivo
                            $mail->addAttachment($ruta_archivo);
                    
                            $mail->isHTML(true);
                            $mail->Subject = "Poliza de seguro automotriz";
                    
                            $mail->Body = "
                                <h2>Virtual Seguros</h2>
                                <p>Adjuntamos tu póliza</p>
                            ";
                    
                            $mail->send();
                    
                            echo json_encode(["mensaje" => "Correo con poliza enviado correctamente."]);
                    
                        } catch (Exception $e) {
                            http_response_code(500);
                            echo json_encode(["error" => "Error al enviar correo: " . $mail->ErrorInfo]);
                        }
                    }
                }
				
						
				wp_custom_log('antes de llamar polizaemitir en recibe_poliza_sura');
				
				// URL de la API
				$url = 'https://hub-qa.segurossura.cl/seguros-generales-soporte/lap/api/lap/polizaemitir';
				
				// Datos a enviar
				$data = [
					"numeroCotizacion" => $NumeroCotizacion,
					"numeroPoliza" => $numeroPoliza,
					"rutAsegurado" => $rut,
					"patente" => $patente
				];
				
				wp_custom_log('datos envio polizaemitir: '.print_r($data, true));
				
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
				
				wp_custom_log('datos envio polizaemitir2 : '.$ch);
				wp_custom_log('datos envio polizaemitir2 : '.$response);
				
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
        
        
            } else {
                echo "No se pudo obtener el paymentMethodId. Respuesta: " . $response;
            }
    }
    else{
        // Configuración del correo
        $mail = new PHPMailer(true);
    
        try {
            // Configuración SMTP
            $mail->isSMTP();
            $mail->Host = 'mail.virtualseguros.cl'; // reemplaza con tu dominio
            $mail->SMTPAuth = true;
            $mail->Username = 'api@virtualseguros.cl'; // tu correo creado en Banahosting
            $mail->Password = 'PVXpGskaSzY7N#R';
            $mail->SMTPSecure = 'ssl'; // o 'tls' dependiendo del servidor
            $mail->Port = 465; // o 587 para TLS
    
            $mail->setFrom('api@virtualseguros.cl', 'API Inspección');
            $mail->addAddress($email); //correo de destino
    
            $mail->isHTML(true);
            $mail->Subject = "Resultado de Inspección: $estado";
    
            $mail->Body = "
                <h2>Resultado de inspección</h2>
                <p><strong>ID Solicitud:</strong> $idSolicitud</p>
                <p><strong>ID Inspección:</strong> $idInspeccion</p>
                <p><strong>Patente:</strong> $patente</p>
                <p><strong>Estado:</strong> $estado</p>
                <p><strong>Motivo cancelación:</strong> $motivoCancelacion</p>
                <p><strong>Motivo rechazo distinto al daño:</strong> $motivoRechazo</p>
            ";
    
            $mail->send();
    
            echo json_encode(["mensaje" => "Correo con rechazo enviado correctamente."]);
    
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al enviar correo: " . $mail->ErrorInfo]);
        }
    }

} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
}
