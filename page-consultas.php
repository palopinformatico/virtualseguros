<?php
if ( session_status() !== PHP_SESSION_ACTIVE && ! headers_sent() ) {
    session_start();
}
    
if (isset($_SESSION['usuario'])) {
    $usuario = $_SESSION['usuario'];

    $rut = htmlspecialchars($usuario['rut']);
} else {
    echo "No hay datos de usuario en la sesión.";
}

// Ruta del archivo
$archivo = "consultas.txt";

// Fecha actual
$hoy = new DateTime("now");

// Fecha hace 15 días
$hace15dias = clone $hoy;
$hace15dias->modify("-15 days");

// Array para guardar registros válidos
$registros = [];

// Leer registros del archivo si existe
if (file_exists($archivo)) {
    $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lineas as $linea) {
        $partes = explode(";", $linea);
        $registro = [];
        foreach ($partes as $parte) {
            $kv = explode(":", $parte, 2);
            if (count($kv) == 2) {
                $clave = trim($kv[0]);
                $valor = trim($kv[1]);
                $registro[$clave] = $valor;
            }
        }

        // Validar si tiene fecha y está dentro de los últimos 15 días
        if (!empty($registro) && isset($registro['fecha'])) {
            $fechaRegistro = DateTime::createFromFormat("Y-m-d H:i:s", $registro['fecha']);
            if ($fechaRegistro && $fechaRegistro >= $hace15dias && $rut == $registro['rut']) {
                $registros[] = $registro;
            }
        }
    }

    // ---- Filtrar duplicados ----
    $conteo = [];
    foreach ($registros as $r) {
        $clave = $r['rut'] . '|' . $r['cotizacionServicioID'];
        if (!isset($conteo[$clave])) {
            $conteo[$clave] = 0;
        }
        $conteo[$clave]++;
    }
    
    $registrosFiltrados = array_filter($registros, function($r) use ($conteo) {
        $clave = $r['rut'] . '|' . $r['cotizacionServicioID'];
        return $conteo[$clave] === 1; // solo mostrar los que aparecen 1 vez
    });

    // Ordenar por fecha ascendente
    //usort($registros, function($a, $b) {
    //    return $a['fecha'] <=> $b['fecha'];
    //});
} else {
    echo "❌ El archivo no existe.";
    exit;
}

// Mostrar tabla de registros disponibles
if (!empty($registrosFiltrados)) {
    echo "<h4>Se han encontrado las siguientes consultas realizadas anteriormente</h4>";
    echo "<h5>Si desea retomar seleccione una y presione Continuar, en caso contrario seleccione Consulta nueva</h5>";
    echo "<form method='post' action='/cotizador-resultados' onsubmit='return validarSeleccion()'>";
    echo "<table border='1' cellpadding='8' cellspacing='0' 
        style='width:100%; border-collapse:collapse; table-layout:fixed; text-align:center;'>";
    echo "<tr>
        <th style='width:33%;'>Fecha</th>
        <th style='width:33%;'>Patente</th>
        <th style='width:33%;'>Seleccionar</th>
      </tr>";
    
    $cantRegs = 0;
    foreach ($registrosFiltrados as $i => $r) {
        echo "<tr>";
        echo "<td>" . $r['fecha'] . "</td>";
        echo "<td>" . htmlspecialchars($r['patente']) . "</td>";
        echo "<td style='text-align:center;'>
            <input type='radio' name='cotizacionServicioID' value='" . htmlspecialchars($r['cotizacionServicioID']) . "'>
              </td>";
        echo "</tr>";
        $cantRegs++;
    }
    
    echo "</table>";
    echo "<div style='text-align:right; margin-top:10px;'>
			<input type='hidden' name='csrf_token' value='eld380'/>
            <button type='submit' name='accion' value='continuar'>Continuar</button>
            <button type='submit' name='accion' value='nueva'>Consulta nueva</button>
          </div>";
    echo "</form>";
    
    echo "
    <script>
    function validarSeleccion(e) {
        // Identificar cuál botón disparó el submit
        const botonPresionado = document.activeElement;
    
        if (botonPresionado && botonPresionado.value === 'nueva') {
            return true; // no validamos si es 'Consulta nueva'
        }
    
        // Revisar si hay alguna opción seleccionada
        const seleccionados = document.querySelectorAll('input[name=\"cotizacionServicioID\"]:checked');
        if (seleccionados.length === 0) {
            alert('Debe seleccionar una opción antes de continuar.');
            return false; // evita el envío
        }
        return true;
    }
    </script>
    ";
} else {
    
    // si no hay consultas previas redireccionar a la URL
    wp_redirect( home_url( '/cotizador-resultados' ) );
    exit;
}

?>