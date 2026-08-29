<?php
/* Template Name: Suscripcion */

if ( session_status() !== PHP_SESSION_ACTIVE && ! headers_sent() ) {
    session_start();
}

// API Endpoint
$url = "https://hub-qa.segurossura.cl/seguros-transversales/oneclick/v1/token_enrollment/url-subscription";
$token = recuperarToken();

$tipoVehSeleccionado = $_POST['tipoVehSeleccionado'];

$tipoVehSeleccionado = strtr($tipoVehSeleccionado, [
    'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
    'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
    'Ñ' => 'N', 'ñ' => 'n'
]);

$_SESSION['tipoVehSeleccionado'] = $tipoVehSeleccionado;
    
if (isset($_SESSION['usuario'])) {
    $usuario = $_SESSION['usuario'];

    $rut = htmlspecialchars($usuario['rut']);
    $rut = substr($rut, 0, -1);
    $dv = substr($rut, -1);
    $nombre = htmlspecialchars($usuario['nombre']);
    $apaterno = htmlspecialchars($usuario['apaterno']);
    $amaterno = htmlspecialchars($usuario['amaterno']);
    $email = htmlspecialchars($usuario['email']);
    $telefono = htmlspecialchars($usuario['telefono']);
    $diasPago = htmlspecialchars($usuario['diasPago']);
    $esNuevo = isset($usuario['esNuevo']) ? trim((string) $usuario['esNuevo']) : '';

    if ($esNuevo === 'Nuevo') {
        $targetCallback = 'subir-documento';
    } else {
        $targetCallback = 'lap-solicitud-usado';
    }

    $_SESSION['oneclick_target'] = $targetCallback;
    $_SESSION['esNuevo'] = $esNuevo;
    $urlCallback = "https://virtualseguros.cl/oneclick-callback?target=" . $targetCallback;

    wp_custom_log('esNuevo en suscripcion: ' . $esNuevo);
    wp_custom_log('targetCallback en suscripcion: ' . $targetCallback);
    
    if (isset($_SESSION['NumeroCotizacion']))
        $NumeroCotizacion = $_SESSION['NumeroCotizacion'];
    else
        $NumeroCotizacion = "";
    
    if (isset($_SESSION['nombrePlan']))
        $nombrePlan = $_SESSION['nombrePlan'];
    else
        $nombrePlan = "";
    
    if (isset($_SESSION['precio_clp']))
        $precio_clp = $_SESSION['precio_clp'];
    else
        $precio_clp = "";
    	
    if($diasPago < 10)
    	$diasPago = "0".$diasPago;
    $mesSiguiente = date('Y-m-d');
    $mesSiguientePago = date("Y-m-$diasPago", strtotime('+1 month'));
    $anioSiguiente = date('Y-m-d', strtotime('+1 year'));
    	
    // Headers
    $headers = [
        "Ocp-Apim-Subscription-Key: 9b17ac14f09d44a2baadba510066cf22",
        "Authorization: ".$token,
        "Content-Type: application/json"
    ];
    
    wp_custom_log("NumeroCotizacion ".$NumeroCotizacion);
    wp_custom_log("nombrePlan ".$nombrePlan);
    wp_custom_log("precio_clp ".$precio_clp);
    
    // Body
    $data = [
        "intermediary" => [
            "rut" => $rut,
            "dv" => $dv,
            "name" => $nombre,
            "firstSurname" => $apaterno,
            "secondSurname" => $amaterno,
            "email" => $email,
            "phone" => "+569".$telefono
        ],
        "payer" => [
            "rut" => $rut,
            "dv" => $dv,
            "name" => $nombre,
            "firstSurname" => $apaterno,
            "secondSurname" => $amaterno,
            "email" => $email,
            "phone" => "+569".$telefono
        ],
        "enrollmentType" => "direct",
        "ensured" => [
            "rut" => $rut,
            "dv" => $dv,
            "name" => $nombre,
            "firstSurname" => $apaterno,
            "secondSurname" => $amaterno,
            "email" => $email,
            "phone" => "+569".$telefono
        ],
        "urlCallback" => $urlCallback,
        "apiConsumerId" => 34,
        "insurance" => [
            "insuranceCode" => $NumeroCotizacion,
            "description" => $nombrePlan,
            "payDay" => $diasPago,
            "firstQuotaValue" => $precio_clp,
            "monthlyQuotaValue" => $precio_clp,
            "currencyType" => 1,
            "nextPay" => $mesSiguientePago,
            "validityStart" => $mesSiguiente,
            "validityEnd" => $anioSiguiente
        ]
    ];
    
    // Iniciar cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // solo en ambientes QA, remueve en producci贸n
    
    // Ejecutar la petici贸n
    $response = curl_exec($ch);
    wp_custom_log("data en suscripcion: " . json_encode($data));
    //echo "json_encode ".json_encode($data);
    //echo "response ".$response;
    
    if (curl_errno($ch)) {
        die('Error en cURL: ' . curl_error($ch));
    }
    
    curl_close($ch);
    
    // Procesar la respuesta
    $result = json_decode($response, true);
    wp_custom_log("result en suscripcion: " . print_r($result, true));

    if (!empty($result["url"])) {
        $urlSuscripcion = $result["url"];
        $uuidSesion = "";

        if (preg_match('#/resumen/([a-f0-9\-]{8}-[a-f0-9\-]{4}-[a-f0-9\-]{4}-[a-f0-9\-]{4}-[a-f0-9\-]{12})/?$#i', $urlSuscripcion, $matches)) {
            $uuidSesion = $matches[1];
        } elseif (preg_match('#[?&]uuid=([a-f0-9\-]{8}-[a-f0-9\-]{4}-[a-f0-9\-]{4}-[a-f0-9\-]{4}-[a-f0-9\-]{12})#i', $urlSuscripcion, $matches)) {
            $uuidSesion = $matches[1];
        }

        if (!empty($uuidSesion)) {
            $_SESSION['uuid'] = sanitize_text_field($uuidSesion);
            $_SESSION['oneclick_uuid'] = sanitize_text_field($uuidSesion);
            wp_custom_log("UUID guardado en sesión desde la URL de suscripción: " . $uuidSesion);
        }

        // Redireccionar a la URL
        echo '<!doctype html><html><head><meta charset="utf-8">';
        echo '<meta http-equiv="refresh" content="0;url=' . esc_attr($urlSuscripcion) . '">';
        echo '</head><body>';
        echo '<script>try{window.top.location.href = "' . esc_js($urlSuscripcion) . '";}catch(e){window.location.href = "' . esc_js($urlSuscripcion) . '";}</script>';
        echo '<noscript><a href="' . esc_attr($urlSuscripcion) . '">Continuar</a></noscript>';
        echo '</body></html>';
        exit;
    } else {
        echo "Error al obtener la URL de suscripci贸n.";
        echo "<pre>" . print_r($result, true) . "</pre>";
    }

} else {
    echo "Error, contacte al administrador.";
}
?>
