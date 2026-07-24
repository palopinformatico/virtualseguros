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
    
if (!isset($cotizacionServicioID)) {
    // Formato de fecha y hora
    date_default_timezone_set('America/Santiago'); // Ajusta a tu zona si es necesario
    $fechaHora = date('Y-m-d H:i:s');
    
    //se guardan datos en un archivo local
    $linea = "fecha: $fechaHora; nombre: $nombre; rut: $rutCompleto; fecha_nacimiento: $fecha_nacimiento; anio: $anio; codmarca: $marca; marca: $nombre_marca; codmodelo: $modelo; modelo: $nombre_modelo; codcomuna: $comuna;  comuna: $nombre_comuna; patente: $patente; uso: $uso;\n";
    file_put_contents("cotizaciones.txt", $linea, FILE_APPEND | LOCK_EX);
	
	//crearPrimeraCotizacion($amaterno, $apaterno, $comuna, $fecha_nacimiento, $nombre, $rut, $anio, $marca, $modelo, $patente, $uso);

    $url = 'https://hub-qa.segurossura.cl/seguros-generales/vechiculos-cotizacion';
	$token = recuperarToken();

    $headers = [
        'Content-Type' => 'text/xml; charset=utf-8',
        'SOAPAction'   => '"http://ws.rsa.cl/WsIndustrializacion/CotizaOferta"',
        'Authorization'=> 'Bearer ' . $token,
        'Ocp-Apim-Subscription-Key' => '712c61e9edff4ad4842111902b1dcf18',
    ];

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
               <ws:autoReemplazo>SinOpcional</ws:autoReemplazo>
               <ws:rCExceso>SinOpcional</ws:rCExceso>
               <ws:taller>SinOpcional</ws:taller>
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

    if (isset($_SESSION['respuesta_api']) && !isset($cotizacionServicioID)) {
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
				//echo "ID encontrado: " . $cotizacionServicioID;
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
				$seguros[] = [
					'cotizacionServicioID' => $cotizacionServicioID,
					'nombre_aseguradora' => 'Sura',
					'logo' => get_stylesheet_directory_uri().'/sura.png',
					'titulo_seguro' => $nombrePlan[$i],
					'cubiertas' => [
						'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
						'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
						'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'
					],
					'precio_clp_aprox' => $valorEnPeso[$i],
					'uf_anual' => $valorEnUf[$i]
				];
				
				//se gurdan datos de consulta en un archivo para posterior consulta
                $linea = "fecha: $fechaHora; rut: $rutCompleto; cotizacionServicioID: $cotizacionServicioID; patente: $patente; nombrePlan.$i: $nombrePlan[$i]; valorEnPeso.$i: $valorEnPeso[$i]; valorEnUf.$i: $valorEnUf[$i];\n";
                file_put_contents("consultas_detalle.txt", $linea, FILE_APPEND | LOCK_EX);
			}
				
			//se gurdan datos de consulta en un archivo para posterior consulta
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
    
	if (!isset($_SESSION['respuesta_api']) && !isset($cotizacionServicioID)) {
		echo "No hay resultados de la api para mostrar.";
	}
	
    if (isset($cotizacionServicioID)) {
        $cotizacionServicioID = htmlspecialchars(urldecode($cotizacionServicioID));

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
			$seguros[] = [
				'cotizacionServicioID' => $cotizacionServicioID,
				'nombre_aseguradora' => 'Sura',
				'logo' => get_stylesheet_directory_uri().'/sura.png',
				'titulo_seguro' => $nombrePlan[$i],
				'cubiertas' => [
					'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
					'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
					'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'
				],
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
