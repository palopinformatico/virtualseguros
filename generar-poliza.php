<?php
/* Template Name: Generar Poliza */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../../PHPMailer/src/Exception.php';
require __DIR__ . '/../../../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../../../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    
    if (isset($_SESSION['usuario'])) {
        $usuario = $_SESSION['usuario'];

		$rut = htmlspecialchars($usuario['rut']);
        $uso = $usuario['uso'];
    } else {
        echo "No hay datos de usuario en la sesión.";
    }
	
	$fechaHora = date('Y-m-d');

    // Generar un ID 煤nico (puedes cambiarlo por tu propia l贸gica)
    $nuevo_nombre = $rut . '_factura_'.$fechaHora.'.' . pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);

    // Obtener ruta de uploads de WordPress
    //$upload_dir = wp_upload_dir();
    //$destino = trailingslashit($upload_dir['path']) . $nuevo_nombre;
    $destino ="/home/virtualseguros/public_html/uploads/". $nuevo_nombre;

    // Usar funci贸n de WordPress para manejar la subida
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    $moved_file = wp_handle_upload(
        $_FILES['archivo'],
        array('test_form' => false)
    );

    if ($moved_file && !isset($moved_file['error'])) {
        // Renombrar el archivo en la carpeta
        rename($moved_file['file'], $destino);
        //echo "Archivo subido como: " . $nuevo_nombre." en ".$destino;
    } else {
        echo "Error al subir el archivo: " . $moved_file['error'];
    }
    
    //capturar uuid
    if (isset($_SESSION['uuid'])) {
        $uuid = $_SESSION['uuid'];
        
        wp_custom_log("uuid en generar.poliza".$uuid);
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
            
            //$fecha = date('Y-m-d');
            $fecha = date('Y-m-d', strtotime($fecha . ' -1 day'));
            $anio = date('Y');
            $NumeroChasis = $_SESSION['NumeroChasis'];
            $colorVehiculo = $_SESSION['colorVehiculo'];
            $NumeroMotor = $_SESSION['NumeroMotor'];
            $cotizacionServicioID = $_SESSION['cotizacionServicioID'];
            $tipoVehSeleccionado = $_SESSION['tipoVehSeleccionado'];
            
            // Si tienes la extensión intl
            $tipoVehSeleccionado = Normalizer::normalize($tipoVehSeleccionado, Normalizer::FORM_D);
            $tipoVehSeleccionado = preg_replace('/\p{Mn}/u', '', $tipoVehSeleccionado);

            $tipoVehSeleccionado = strtr($tipoVehSeleccionado, [
                'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
                'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
                'Ñ' => 'N', 'ñ' => 'n'
            ]);
            
            if (isset($_SESSION['usuario'])) {
                $usuario = $_SESSION['usuario'];
            
                $patente = htmlspecialchars($usuario['patente']);
                $nombre = htmlspecialchars($usuario['nombre']);
                $apaterno = htmlspecialchars($usuario['apaterno']);
                $amaterno = htmlspecialchars($usuario['amaterno']);
                $email = htmlspecialchars($usuario['email']);
                $telefono = htmlspecialchars($usuario['telefono']);
                $rutAsegurado = htmlspecialchars($usuario['rut']);
                $comuna = htmlspecialchars($usuario['comuna']);
                $genero = htmlspecialchars($usuario['genero']);
                $direccion = htmlspecialchars($usuario['direccion']);
                $email = htmlspecialchars($usuario['email']);
                
            } else {
                echo "No hay datos del usuario.";
            }
            
            //dejar archivo en base64
            $file_content = file_get_contents($destino);
        
            // Codifica en Base64
            $base64_string = base64_encode($file_content);
        
            // Si quieres incluir el tipo MIME (útil para imágenes, PDFs, etc.)
            $mime_type = mime_content_type($destino);
            $base64_with_prefix = 'data:' . $mime_type . ';base64,' . $base64_string;
        
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
                              <epv1:esNuevo>true</epv1:esNuevo>
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
                                 <epv1:rut>$rutAsegurado</epv1:rut>
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
                                 <epv1:rut>$rutAsegurado</epv1:rut>
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
                                 <epv1:rut>$rutAsegurado</epv1:rut>
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
    		}
    		
            $_SESSION['numeroPoliza'] = $numeroPoliza;
            
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
                            <p>Adjuntamos tu poliza</p>
                        ";
                
                        $mail->send();
                
                        //echo json_encode(["mensaje" => "Correo enviado correctamente."]);
                        
                        ini_set('display_errors', 1);
                        error_reporting(E_ALL);

                        // Redireccionar a la URL de LAP
                        header("Location: lap-solicitud");
                        exit;
                
                    } catch (Exception $e) {
                        http_response_code(500);
                        echo json_encode(["error" => "Error al enviar correo: " . $mail->ErrorInfo]);
                    }
                }
            }
    
    
        } else {
            echo "No se pudo obtener el paymentMethodId. Respuesta: " . $response;
        }
    }
    else{
        echo "error al obtener uuid";
        wp_custom_log("error al obtener uuid");
    }
}
?>