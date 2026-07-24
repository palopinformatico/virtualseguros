<?php
// Nombre del archivo
$archivo = "consultas_detalle.txt";

// Verificar que existe
if (!file_exists($archivo)) {
    die("El archivo no existe.");
}

// Leer el archivo en un array (cada línea es un elemento)
$lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$datos = [];

foreach ($lineas as $linea) {
    // Dividir por ";"
    $partes = explode(";", $linea);
    $fila = [];

    foreach ($partes as $parte) {
        // Dividir cada campo por ":"
        $kv = explode(":", $parte, 2);
        if (count($kv) == 2) {
            $clave = trim($kv[0]);
            $valor = trim($kv[1]);
            $fila[$clave] = $valor;
        }
    }

    $datos[] = $fila;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consultas Realizadas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #999;
            padding: 8px;
            text-align: center;
        }
        th {
            background: #eee;
        }
        .btn-export {
            padding: 8px 14px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-export:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Detalle de Consultas</h2>
        <a href="/exportar_excel" class="btn-export">Exportar a Excel</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>RUT</th>
                <th>Cotización ID</th>
                <th>Patente</th>
                <th>Nombre Plan</th>
                <th>Valor en Peso</th>
                <th>Valor en UF</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($datos as $fila): ?>
                <tr>
                    <td><?= htmlspecialchars($fila["fecha"] ?? "") ?></td>
                    <td><?= htmlspecialchars($fila["rut"] ?? "") ?></td>
                    <td><?= htmlspecialchars($fila["cotizacionServicioID"] ?? "") ?></td>
                    <td><?= htmlspecialchars($fila["patente"] ?? "") ?></td>
                    <td>
                        <?php 
                            foreach ($fila as $k => $v) {
                                if (strpos($k, "nombrePlan") === 0) {
                                    echo htmlspecialchars($v);
                                }
                            }
                        ?>
                    </td>
                    <td>
                        <?php 
                            foreach ($fila as $k => $v) {
                                if (strpos($k, "valorEnPeso") === 0) {
                                    echo htmlspecialchars($v);
                                }
                            }
                        ?>
                    </td>
                    <td>
                        <?php 
                            foreach ($fila as $k => $v) {
                                if (strpos($k, "valorEnUf") === 0) {
                                    echo htmlspecialchars($v);
                                }
                            }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
