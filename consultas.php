<?php
/* Template Name: Consultas */
if ( session_status() !== PHP_SESSION_ACTIVE && ! headers_sent() ) {
    session_start();
}

$comuna = $_POST['comuna'];
$fecha_nacimiento = $_POST['fecha_nacimiento'];
$nombre = $_POST['nombre'];
$apaterno = $_POST['apaterno'];
$amaterno = $_POST['amaterno'];
$rut = $_POST['rut'];
$rut = preg_replace('/[.\-]/', '', $rut);
$anio = $_POST['anio'];
$marca = $_POST['marca'];
$modelo = $_POST['modelo'];
$patente = $_POST['patente'];
$email = $_POST['email'];
$uso = $_POST['uso'];
$telefono = $_POST['telefono'];
$esNuevo = $_POST['esNuevo'];
$genero = $_POST['genero'];
$direccion = $_POST['direccion'];
$diasPago = $_POST['diasPago'];
$nombre_marca = $_POST['nombre_marca'];
$nombre_modelo = $_POST['nombre_modelo'];
$nombre_comuna = $_POST['nombre_comuna'];
$codigoSOL = $_POST['codigoSOL'];

$userData = [
    "comuna" => $comuna,
    "fecha_nacimiento" => $fecha_nacimiento,
    "nombre" => $nombre,
    "apaterno" => $apaterno,
    "amaterno" => $amaterno,
    "rut" => $rut,
    "anio" => $anio,
    "marca" => $marca,
    "modelo" => $modelo,
    "patente" => $patente,
    "uso" => $uso,
    "telefono" => $telefono,
    "email" => $email,
    "esNuevo" => $esNuevo,
    "genero" => $genero,
    "direccion" => $direccion,
    "diasPago" => $diasPago,
    "nombre_marca" => $nombre_marca,
    "nombre_modelo" => $nombre_modelo,
    "nombre_comuna" => $nombre_comuna,
    "codigoSOL" => $codigoSOL
];

// Guardar en sesión
$_SESSION['usuario'] = $userData;

// Ruta del archivo
$archivo = "consultas.txt";

// Fecha actual
$hoy = new DateTime("now");

// Fecha hace 15 días
$hace15dias = clone $hoy;
$hace15dias->modify("-15 days");

// Leer registros del archivo si existe
if (file_exists($archivo)) {
    $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    $rutEncontrado = false;

    foreach ($lineas as $linea) {
        if (strpos($linea, "rut: $rut") !== false) {
            if (preg_match('/fecha:\s*([\d-]+\s[\d:]+)/', $linea, $matches)) {
                $fechaStr = $matches[1];
                $fechaRegistro = DateTime::createFromFormat('Y-m-d H:i:s', $fechaStr);

                if ($fechaRegistro && $fechaRegistro >= $hace15dias && $fechaRegistro <= $hoy) {
                    $rutEncontrado = true;
                }
            }
        }
    }
    
    if($rutEncontrado == true){
        // Redireccionar a pagina de consultas
        wp_redirect( home_url( '/page-consultas' ) );
        exit;
    }
    else{
        // Redireccionar a cotizador-resultados
        wp_redirect( home_url( '/cotizador-resultados' ) );
        exit;
    }
    
} else {
    //echo "❌ El archivo no existe.";
    // Redireccionar a cotizador-resultados
    wp_redirect( home_url( '/cotizador-resultados' ) );
    exit;
}

?>