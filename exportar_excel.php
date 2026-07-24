<?php
/* Template Name: Exportar Excel */
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=consultas.xls");

$archivo = "consultas_detalle.txt";

if (!file_exists($archivo)) {
    die("El archivo no existe.");
}

$lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

echo "<table border='1'>";
echo "<tr>
        <th>Fecha</th>
        <th>RUT</th>
        <th>Cotizaci贸n ID</th>
        <th>Patente</th>
        <th>Nombre Plan</th>
        <th>Valor en Peso</th>
        <th>Valor en UF</th>
      </tr>";

foreach ($lineas as $linea) {
    $partes = explode(";", $linea);
    $fila = [];

    foreach ($partes as $parte) {
        $kv = explode(":", $parte, 2);
        if (count($kv) == 2) {
            $clave = trim($kv[0]);
            $valor = trim($kv[1]);
            $fila[$clave] = $valor;
        }
    }

    echo "<tr>";
    echo "<td>".($fila["fecha"] ?? "")."</td>";
    echo "<td>".($fila["rut"] ?? "")."</td>";
    echo "<td>".($fila["cotizacionServicioID"] ?? "")."</td>";
    echo "<td>".($fila["patente"] ?? "")."</td>";

    foreach ($fila as $k => $v) {
        if (strpos($k, "nombrePlan") === 0) echo "<td>$v</td>";
    }
    foreach ($fila as $k => $v) {
        if (strpos($k, "valorEnPeso") === 0) echo "<td>$v</td>";
    }
    foreach ($fila as $k => $v) {
        if (strpos($k, "valorEnUf") === 0) echo "<td>$v</td>";
    }

    echo "</tr>";
}

echo "</table>";
?>