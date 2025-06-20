<?php
// Ruta a los archivos
$archivo = 'ordenes.txt';
$checklists = 'checklists.json';

// Leer contenido de checklists.json en un arreglo asociativo
$clientes = [];
$data = [];
if (file_exists($checklists)) {
    $json = file_get_contents($checklists);
    $data = json_decode($json, true);
    if (is_array($data)) {
        foreach ($data as $id => $cliente) {
            if (isset($cliente['meta']['nombre_cliente'])) {
                $clientes[$id] = $cliente['meta']['nombre_cliente'];
            }
        }
    }
}

// Procesar eliminación si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $eliminar_id = $_POST['eliminar_id'];

    // Eliminar de ordenes.txt
    if (file_exists($archivo)) {
        $ordenes = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $nuevas_ordenes = [];
        foreach ($ordenes as $orden) {
            $campos = explode(',', $orden);
            if ($campos[0] !== $eliminar_id) {
                $nuevas_ordenes[] = $orden;
            }
        }
        file_put_contents($archivo, implode(PHP_EOL, $nuevas_ordenes) . PHP_EOL);
    }

    // Eliminar de checklists.json
    if (file_exists($checklists)) {
        $json = file_get_contents($checklists);
        $data = json_decode($json, true);
        if (is_array($data)) {
            if (isset($data[$eliminar_id])) {
                unset($data[$eliminar_id]);
            }
            file_put_contents($checklists, json_encode($data, JSON_PRETTY_PRINT));
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Mostrar la tabla
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Órdenes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .container {
            max-width: 1200px;
            width: 100%;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            box-sizing: border-box;
        }

        h2 {
            color: #007bff;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden; /* Ensures border-radius applies to table */
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e2f2ff;
        }

        td {
            color: #555;
            font-size: 0.95em;
        }

        button {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.2s ease, transform 0.1s ease;
            margin-right: 5px; /* Spacing between buttons if any */
        }

        button[type="submit"] {
            background-color: #dc3545; /* Red for delete */
            color: white;
        }

        button[type="submit"]:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }

        .back-button {
            background-color: #6c757d; /* Gray for back button */
            color: white;
            margin-top: 30px;
            padding: 10px 20px;
            font-size: 1em;
            text-decoration: none; /* For button acting as link */
            display: inline-block;
        }

        .back-button:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
        }

        p {
            text-align: center;
            font-size: 1.1em;
            color: #777;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Órdenes Registradas</h2>

        <?php
        if (!file_exists($archivo) || empty(file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))) {
            echo "<p>No hay órdenes registradas.</p>";
        } else {
            $ordenes = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        ?>
            <table>
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Nombre del Cliente</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordenes as $orden):
                        $campos = explode(',', $orden);
                        $idCliente = htmlspecialchars($campos[0]);
                        $nombreCliente = isset($clientes[$idCliente]) ? htmlspecialchars($clientes[$idCliente]) : '(Nombre no encontrado)';

                        // Obtener fecha y hora desde checklists.json
                        $fecha = 'N/D';
                        $hora = 'N/D';
                        if (isset($data[$idCliente]['meta']['fecha_hora_creacion'])) {
                            $fechaHora = $data[$idCliente]['meta']['fecha_hora_creacion'];
                            if (strpos($fechaHora, ' ') !== false) {
                                list($fecha, $hora) = explode(' ', $fechaHora);
                                $fecha = htmlspecialchars($fecha);
                                $hora = htmlspecialchars($hora);
                            }
                        }

                        // Obtener comentario de garantía
                        $comentarioGarantia = isset($data[$idCliente]['meta']['comentarios_garantía'])
                            ? htmlspecialchars($data[$idCliente]['meta']['comentarios_garantía'])
                            : 'Sin comentario';
                    ?>
                        <tr>
                            <td><?php echo $idCliente; ?></td>
                            <td><?php echo $nombreCliente; ?></td>
                            <td><?php echo $fecha; ?></td>
                            <td><?php echo $hora; ?></td>
                            <td><?php echo $comentarioGarantia; ?></td>
                            <td>
                                <form method="post" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar esta orden?');">
                                    <input type="hidden" name="eliminar_id" value="<?php echo $idCliente; ?>">
                                    <button type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php } ?>

        <form action="index.php" method="get">
            <button type="submit" class="back-button">Regresar al Inicio</button>
        </form>
    </div>
</body>
</html>