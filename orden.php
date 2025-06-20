<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION['usuario'];
$archivo = 'ordenes.txt';
$error = '';
$ordenes = [];

if (file_exists($archivo)) {
    $ordenes = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($usuario === 'Asesor') {
        // Si se envía nueva orden
        if (!empty($_POST["nueva_orden"])) {
            $nuevaOrden = trim($_POST["nueva_orden"]);

            // Validar que sea exactamente 8 dígitos numéricos
            if (preg_match('/^\d{8}$/', $nuevaOrden)) {
                if (!in_array($nuevaOrden, $ordenes)) {
                    // Guardar en ordenes.txt
                    file_put_contents($archivo, $nuevaOrden . PHP_EOL, FILE_APPEND);

                    // Guardar en checklists.json con fecha, hora y usuario
                    $checklistFile = 'checklists.json';
                    $checklists = [];
                    if (file_exists($checklistFile)) {
                        $contenido = file_get_contents($checklistFile);
                        $checklists = json_decode($contenido, true) ?? [];
                    }

                    // Crear entrada para la nueva orden
                    if (!isset($checklists[$nuevaOrden])) {
                        $checklists[$nuevaOrden] = [
                            'meta' => [
                                'creado_por' => $usuario,
                                'fecha_hora_creacion' => date('Y-m-d H:i:s')
                            ]
                        ];
                    }

                    // Guardar el nuevo contenido
                    file_put_contents($checklistFile, json_encode($checklists, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                    // Redirigir
                    $_SESSION['orden'] = $nuevaOrden;
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Número de orden ya existe.";
                }
            } else {
                $error = "La nueva orden debe ser un número de exactamente 8 dígitos.";
            }
        }
        // Si se selecciona una orden existente
        elseif (!empty($_POST["orden_existente"])) {
            $_SESSION['orden'] = $_POST["orden_existente"];
            header("Location: index.php");
            exit();
        }
        else {
            $error = "Debes ingresar o seleccionar un número de orden.";
        }

    } else {
        // Otros usuarios solo seleccionan
        $ordenSeleccionada = $_POST["orden_existente"] ?? '';
        if (!empty($ordenSeleccionada)) {
            $_SESSION['orden'] = $ordenSeleccionada;
            header("Location: index.php");
            exit();
        } else {
            $error = "Debes seleccionar un número de orden.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Número de Orden</title>
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
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .container {
            max-width: 500px;
            width: 100%;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            text-align: center; /* Center content within the container */
        }

        h2 {
            color: #007bff;
            margin-bottom: 30px;
            font-weight: 700;
            text-align: center;
        }

        p.error {
            color: #dc3545; /* Red for error messages */
            font-weight: 500;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px; /* Space between fieldsets */
        }

        fieldset {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin: 0;
            background-color: #fdfdfd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
            text-align: left; /* Align content inside fieldset to left */
        }

        legend {
            font-size: 1.2em;
            font-weight: 600;
            color: #007bff;
            padding: 0 10px;
            margin-left: -10px; /* Adjust legend position */
            background-color: #ffffff; /* Match container background */
            border-radius: 5px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 400;
            color: #444;
        }

        input[type="text"],
        select {
            width: calc(100% - 22px); /* Full width minus padding and border */
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box; /* Include padding and border in width */
            margin-bottom: 15px; /* Space below input/select */
        }

        button[type="submit"] {
            background-color: #28a745; /* Green for submit */
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            transition: background-color 0.2s ease, transform 0.1s ease;
            margin-top: 10px; /* Space above the button */
        }

        button[type="submit"]:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .container {
                padding: 20px;
                margin: 10px;
            }
            input[type="text"],
            select {
                width: 100%; /* Adjust for smaller screens */
            }
            button[type="submit"] {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bienvenido, <?php echo htmlspecialchars($usuario); ?></h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="post">
            <?php if ($usuario === 'Asesor'): ?>
                <fieldset>
                    <legend>Agregar nueva orden</legend>
                    <label for="nueva_orden">Nueva orden:</label>
                    <input type="text" name="nueva_orden" id="nueva_orden"
                           minlength="8" maxlength="8" pattern="\d{8}"
                           title="Debe ser un número de exactamente 8 dígitos"
                           placeholder="Introduce 8 dígitos">
                </fieldset>
            <?php endif; ?>

            <fieldset>
                <legend>Seleccionar orden existente</legend>
                <label for="orden_existente">Órdenes disponibles:</label>
                <select name="orden_existente" id="orden_existente">
                    <option value="">-- Selecciona --</option>
                    <?php foreach ($ordenes as $orden): ?>
                        <option value="<?php echo htmlspecialchars($orden); ?>"><?php echo htmlspecialchars($orden); ?></option>
                    <?php endforeach; ?>
                </select>
            </fieldset>

            <button type="submit">Continuar</button>
        </form>
    </div>
</body>
</html>