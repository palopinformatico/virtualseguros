<?php
/* Template Name: Ver datos */
get_header();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Listado de Datos</title>
  <style>
    table {
      border-collapse: collapse;
      width: 100%;
      margin-top: 20px;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 8px;
    }
    th {
      background-color: #f4f4f4;
    }
  </style>
</head>
<body>

  <h2>Cotizaciones realizadas</h2>

  <?php
  $archivo = "cotizaciones.txt";

  if (file_exists($archivo)) {
      $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      $datos = [];
      $columnas = [];

      foreach ($lineas as $linea) {
          $persona = [];
          $campos = explode(";", $linea);

          foreach ($campos as $campo) {
              if (strpos($campo, ":") !== false) {
                  list($clave, $valor) = explode(":", $campo, 2);
                  $clave = trim($clave);
                  $valor = trim($valor);
                  $persona[$clave] = $valor;

                  // Guardar columna única
                  if (!in_array($clave, $columnas)) {
                      $columnas[] = $clave;
                  }
              }
          }

          if (!empty($persona)) {
              $datos[] = $persona;
          }
      }

      if (!empty($datos)) {
          echo "<table>";
          echo "<thead><tr>";
          foreach ($columnas as $col) {
              echo "<th>" . ucfirst($col) . "</th>";
          }
          echo "</tr></thead><tbody>";

          foreach ($datos as $persona) {
              echo "<tr>";
              foreach ($columnas as $col) {
                  echo "<td>" . htmlspecialchars($persona[$col] ?? '') . "</td>";
              }
              echo "</tr>";
          }

          echo "</tbody></table>";
      } else {
          echo "<p>No hay datos para mostrar.</p>";
      }
  } else {
      echo "<p>El archivo <strong>datos.txt</strong> no existe.</p>";
  }
  ?>

</body>
</html>
