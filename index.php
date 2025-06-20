<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['orden'])) {
    header("Location: orden.php");
    exit();
}

$usuario = $_SESSION['usuario'];
$orden = $_SESSION['orden'];
$archivo_json = "checklists.json";

$checklists = [
    "Asesor" => ["Vehículo Ingresado"],
    "Mecánico" => ["Test de nivel de batería", "Revisión de filtro de aire", "Revisión de filtro de motor", "Revisión de nivel de depósito swiper", "Revisión de líquido de frenos", "Revisión de bandas", "Revisión de balatas", "Cambio de balatas delanteras", "Cambio de balatas traseras", "Revisión de luces traseras y delanteras", "Revisión de suspensión", "Alineación y balanceo", "Cambio de aceite", "Cambio de filtro de aceite", "Rotación de llantas", "Engrasado de bisagras", "Calibración de llantas", "Calibración de llanta de refacción"],
    "Lavado" => ["Lavado de cortesía", "Estética de motor", "Estética de exterior", "Estética general", "Pulido de faros", "Descontaminación de cristales", "Higienizador A/C y eliminación de olores"],
    "Garantía" => ["Garantía revisada"]
];

$datos = [];
if (file_exists($archivo_json)) {
    $contenido = file_get_contents($archivo_json);
    $datos = json_decode($contenido, true);
}

if (!isset($datos[$orden])) {
    $datos[$orden] = [];
}
if (!isset($datos[$orden][$usuario])) {
    $datos[$orden][$usuario] = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['nueva_orden']) && !empty($_POST['nueva_orden'])) {
        $_SESSION['orden'] = $_POST['nueva_orden'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    $tipoGuardar = $_POST['tipo'] ?? null;
    if ($tipoGuardar) {
        $tareasEnviadas = $_POST['tareas'] ?? [];
        $tareasValidas = array_intersect($tareasEnviadas, $checklists[$tipoGuardar]);

        $tareasActuales = $datos[$orden][$usuario] ?? [];
        $otrasTareas = array_filter($tareasActuales, function($tarea) use ($tipoGuardar, $checklists) {
            return !in_array($tarea, $checklists[$tipoGuardar]);
        });

        $datos[$orden][$usuario] = array_merge($otrasTareas, $tareasValidas);

        if (isset($_POST['comentarios'])) {
            $comentarioKey = 'comentarios_' . strtolower($tipoGuardar);
            $datos[$orden]['meta'][$comentarioKey] = trim($_POST['comentarios']);
        }

        if ($tipoGuardar === "Asesor") {
            if (isset($_POST['nombre_cliente']) && !isset($datos[$orden]['meta']['nombre_cliente'])) {
                $nombreCliente = trim($_POST['nombre_cliente']);
                if ($nombreCliente !== '') {
                    $datos[$orden]['meta']['nombre_cliente'] = $nombreCliente;
                }
            }

            if (isset($_POST['unidad']) && !isset($datos[$orden]['meta']['unidad'])) {
                $unidad = trim($_POST['unidad']);
                if ($unidad !== '') {
                    $datos[$orden]['meta']['unidad'] = $unidad;
                }
            }

            if (isset($_POST['placa']) && !isset($datos[$orden]['meta']['placa'])) {
                $placa = trim($_POST['placa']);
                if ($placa !== '') {
                    $datos[$orden]['meta']['placa'] = $placa;
                }
            }

            if (isset($_POST['kilometraje']) && !isset($datos[$orden]['meta']['kilometraje'])) {
                $kilometraje = trim($_POST['kilometraje']);
                if ($kilometraje !== '') {
                    $datos[$orden]['meta']['kilometraje'] = $kilometraje;
                }
            }

            if (isset($_POST['telefono']) && !isset($datos[$orden]['meta']['telefono'])) {
                $telefono = trim($_POST['telefono']);
                if ($telefono !== '') {
                    $datos[$orden]['meta']['telefono'] = $telefono;
                }
            }

            if (isset($_POST['tipo_servicio'])) {
                $servicios = array_filter($_POST['tipo_servicio'], function($s) {
                    return trim($s) !== '';
                });
                if (!empty($servicios)) {
                    $datos[$orden]['meta']['tipo_servicio'] = array_values($servicios);
                }
            }
            
            // Add this block to save 'total_a_pagar'
            if (isset($_POST['total_a_pagar'])) {
                $total_a_pagar = trim($_POST['total_a_pagar']);
                // Basic validation for numeric value, adjust as needed
                if (is_numeric($total_a_pagar) && $total_a_pagar !== '') {
                    $datos[$orden]['meta']['total_a_pagar'] = $total_a_pagar;
                } else if ($total_a_pagar === '') {
                    // If the field is submitted empty, remove it from meta
                    unset($datos[$orden]['meta']['total_a_pagar']);
                }
            }
        }

        file_put_contents($archivo_json, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header("Location: index.php");
        exit();
    }
}

$lista_ordenes = [];
if (file_exists("ordenes.txt")) {
    $contenido_ordenes = file("ordenes.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lista_ordenes = $contenido_ordenes ?: [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Panel de Usuario</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .user-info {
            font-size: 1.1em;
            color: #555;
        }

        .options-menu {
            position: relative;
            display: inline-block;
        }

        .options-button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.2s ease;
        }

        .options-button:hover {
            background-color: #0056b3;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            right: 0;
            border-radius: 5px;
            overflow: hidden;
            margin-top: 5px;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.2s ease;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .dropdown-content span {
            color: #ccc;
            padding: 12px 16px;
            display: block;
            cursor: not-allowed;
        }

        .dropdown-content {
    display: none; /* Por defecto oculto */
    position: absolute;
    background-color: #f9f9f9;
    min-width: 160px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1;
    right: 0;
    border-radius: 5px;
    overflow: hidden;
    margin-top: 5px;
}

.dropdown-content.show-menu { /* Nueva clase para mostrar el menú */
    display: block;
}

        .order-selector {
            margin-left: 20px;
            display: inline-block;
        }

        .order-selector select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 0.9em;
        }

        .checklist-form {
            background-color: #fdfdfd;
            border: 1px solid #e0e0e0;
            margin-bottom: 25px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .checklist-form fieldset {
            border: none;
            padding: 0;
            margin: 0;
        }

        .checklist-form legend {
            font-size: 1.5em;
            font-weight: 700;
            color: #007bff;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #007bff;
            display: block;
            width: 100%;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 400;
            color: #444;
        }

        .form-group input[type="text"],
        .form-group input[type="tel"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
        }

        .form-group input[type="checkbox"] {
            margin-right: 10px;
            transform: scale(1.2);
        }

        .task-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
        }

        .task-item:last-child {
            border-bottom: none;
        }

        .task-item label {
            margin: 0;
            display: flex;
            align-items: center;
            cursor: pointer;
            width: 100%;
        }

        .task-item input[type="checkbox"]:checked + span {
            text-decoration: line-through;
            color: #888;
        }

        .task-owner {
            font-size: 0.85em;
            color: #999;
            margin-left: 10px;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .button-group {
            margin-top: 20px;
            text-align: right;
        }

        .button-group button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.2s ease, transform 0.1s ease;
            margin-left: 10px;
        }

        .button-group button[type="submit"] {
            background-color: #28a745;
            color: white;
        }

        .button-group button[type="submit"]:hover {
            background-color: #218838;
            transform: translateY(-1px);
        }

        .button-group button[onclick="generarPDF()"] {
            background-color: #dc3545;
            color: white;
        }

        .button-group button[onclick="generarPDF()"]:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }

        .button-group button[onclick="agregarServicio()"] {
            background-color: #6c757d;
            color: white;
            margin-left: 0; /* Align with input */
        }

        .button-group button[onclick="agregarServicio()"]:hover {
            background-color: #5a6268;
        }

        ul {
            list-style: none;
            padding-left: 0;
        }

        ul li {
            margin-bottom: 5px;
            color: #555;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            .user-info {
                margin-bottom: 15px;
            }
            .order-selector {
                margin-left: 0;
                width: 100%;
            }
            .order-selector select {
                width: 100%;
            }
            .form-group input, .form-group select, .form-group textarea {
                width: 100%;
            }
            .button-group {
                text-align: center;
            }
            .button-group button {
                margin: 5px auto;
                display: block;
                width: calc(100% - 20px);
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <div class="user-info">
                Bienvenido, <?php echo htmlspecialchars($usuario); ?> | Orden: <?php echo htmlspecialchars($orden); ?>
            </div>
            <div class="options-menu">
                <button class="options-button" onclick="toggleMenu()">Opciones</button>
                <div id="dropdownMenu" class="dropdown-content">
                    <?php if ($usuario === "Garantía"): ?>
                        <a href="ordenes.php">Todas las órdenes</a>
                    <?php else: ?>
                        <span>Todas las órdenes</span>
                    <?php endif; ?>
                    <a href="informacion.php">Información</a>
                    <a href="logout.php">Cerrar sesión</a>
                </div>
            </div>
        </div>

        <div class="order-selector">
            <form method="post">
                <label for="nueva_orden">Cambiar de orden:</label>
                <select name="nueva_orden" id="nueva_orden" onchange="this.form.submit()">
                    <option value="">-- Selecciona una orden --</option>
                    <?php foreach ($lista_ordenes as $ord): ?>
                        <option value="<?php echo htmlspecialchars($ord); ?>" <?php echo ($ord === $orden) ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($ord); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php
        $tareasMarcadasPorTodos = [];
        foreach ($datos[$orden] as $usuarioQueMarcó => $tareasMarcadasUsuario) {
            if ($usuarioQueMarcó === "meta" || !is_array($tareasMarcadasUsuario)) continue;
            foreach ($tareasMarcadasUsuario as $tareaMarcada) {
                $tareasMarcadasPorTodos[$tareaMarcada] = $usuarioQueMarcó;
            }
        }
        ?>

        <?php foreach ($checklists as $tipo => $tareas): ?>
            <form method="post" class="checklist-form">
                <fieldset>
                    <legend><?php echo htmlspecialchars($tipo); ?></legend>

                    <?php if ($tipo === "Asesor"): ?>
                        <div class="form-group">
                            <?php if (isset($datos[$orden]['meta']['nombre_cliente'])): ?>
                                <p><strong>Nombre del cliente:</strong> <?php echo htmlspecialchars($datos[$orden]['meta']['nombre_cliente']); ?></p>
                            <?php else: ?>
                                <label for="nombre_cliente">Nombre del cliente:</label>
                                <input type="text" name="nombre_cliente" id="nombre_cliente" required oninput="titleCase(this)">
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <?php if (isset($datos[$orden]['meta']['telefono'])): ?>
                                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($datos[$orden]['meta']['telefono']); ?></p>
                            <?php else: ?>
                                <label for="telefono">Teléfono del cliente:</label>
                                <input type="tel" name="telefono" id="telefono" pattern="[0-9]{10}" maxlength="10" title="10 dígitos numéricos" required>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <?php if (isset($datos[$orden]['meta']['unidad'])): ?>
                                <p><strong>Unidad:</strong> <?php echo htmlspecialchars($datos[$orden]['meta']['unidad']); ?></p>
                            <?php else: ?>
                                <label for="unidad">Unidad:</label>
                                <select name="unidad" id="unidad" required>
                                    <option value="">-- Selecciona una unidad --</option>
                                    <option value="M 3 hatchback">M 3 hatchback</option>
                                    <option value="M 3 sedan">M 3 sedan</option>
                                    <option value="M 2 hatchback">M 2 hatchback</option>
                                    <option value="M 2 sedan">M 2 sedan</option>
                                    <option value="Mx 5">Mx 5</option>
                                    <option value="Cx 3">Cx 3</option>
                                    <option value="Cx 5">Cx 5</option>
                                    <option value="Cx 7">Cx 7</option>
                                    <option value="Cx 9">Cx 9</option>
                                    <option value="Cx 30">Cx 30</option>
                                    <option value="Cx 50">Cx 50</option>
                                    <option value="Cx 70">Cx 70</option>
                                    <option value="Cx 90">Cx 90</option>
                                    <option value="Bt 50">Bt 50</option>
                                    <option value="Multimarca">Multimarca</option>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <?php if (isset($datos[$orden]['meta']['placa'])): ?>
                                <p><strong>Placa:</strong> <?php echo htmlspecialchars($datos[$orden]['meta']['placa']); ?></p>
                            <?php else: ?>
                                <label for="placa">Placas del vehículo:</label>
                                <input type="text" name="placa" id="placa" required pattern="[A-Za-z0-9\-]{4,10}" title="Placa válida (4-10 caracteres alfanuméricos)">
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <?php if (isset($datos[$orden]['meta']['kilometraje'])): ?>
                                <p><strong>Kilometraje:</strong> <?php echo htmlspecialchars($datos[$orden]['meta']['kilometraje']); ?> km</p>
                            <?php else: ?>
                                <label for="kilometraje">Kilometraje de ingreso:</label>
                                <input type="number" name="kilometraje" id="kilometraje" min="0" step="1" required> km
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <?php if (isset($datos[$orden]['meta']['tipo_servicio'])): ?>
                                <p><strong>Servicios:</strong></p>
                                <ul>
                                    <?php foreach ($datos[$orden]['meta']['tipo_servicio'] as $serv): ?>
                                        <li><?php echo htmlspecialchars($serv); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <label>Tipo de servicio:</label>
                                <div id="servicios-container">
                                    <select name="tipo_servicio[]" required>
                                        <option value="">-- Selecciona un servicio --</option>
                                        <option value="Servicio por diagnóstico">Servicio por diagnóstico</option>
                                        <option value="Reingreso por garantía">Reingreso por garantía</option>
                                        <option value="Reparacíon menor">Reparacíon menor</option>
                                        <option value="Publica de empleados">Publica de empleados</option>
                                        <option value="Servicio por kilometraje básico">Servicio por kilometraje básico</option>
                                        <option value="Servicio por kilometraje intermedio">Servicio por kilometraje intermedio</option>
                                        <option value="Servicio por kilometraje premium">Servicio por kilometraje premium</option>
                                        <option value="Cambio de aceite S-sintetico">Cambio de aceite S-sintético</option>
                                        <option value="Cambio de aceite sintetico">Cambio de aceite sintético</option>
                                        <option value="Cambio de aceite de trasmisión AUT">Cambio de aceite de transmisión AUT</option>
                                        <option value="Cambio de embrague">Cambio de embrague</option>
                                        <option value="Lavado de inyectores">Lavado de inyectores</option>
                                        <option value="Cambio de balatas traseras">Cambio de balatas traseras</option>
                                        <option value="Cambio de balatas delanteras">Cambio de balatas delanteras</option>
                                        <option value="Limp. de cuerpo de aceleración">Limpieza de cuerpo de aceleración</option>
                                        <option value="Alineación + Balanceo">Alineación + Balanceo</option>
                                        <option value="Mto. al aire acondicionado">Mantenimiento al aire acondicionado</option>
                                        <option value="Estética general">Estética general</option>
                                        <option value="Estética de motor">Estética de motor</option>
                                        <option value="Estética de interior">Estética de interior</option>
                                        <option value="Descontaminación de cristales">Descontaminación de cristales</option>
                                        <option value="Higienizador A/C y eliminación de olores">Higienizador A/C y eliminación de olores</option>
                                        <option value="Pulido de faros">Pulido de faros</option>
                                    </select>
                                    <button type="button" class="options-button" onclick="agregarServicio()">Agregar otro servicio</button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <?php if (isset($datos[$orden]['meta']['total_a_pagar'])): ?>
                                <p><strong>Total a pagar:</strong> $<?php echo number_format(htmlspecialchars($datos[$orden]['meta']['total_a_pagar']), 2); ?> MXN</p>
                            <?php else: ?>
                                <label for="total_a_pagar">Total a pagar:</label>
                                <input type="number" name="total_a_pagar" id="total_a_pagar" min="0" step="0.01" placeholder="Ej: 1500.00" <?php echo ($usuario !== $tipo) ? 'disabled' : ''; ?>> MXN
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                    <?php foreach ($tareas as $tarea): ?>
                        <div class="task-item">
                            <?php
                                $marcadoPorAlguien = isset($tareasMarcadasPorTodos[$tarea]);
                                $marcadoPorMi = in_array($tarea, $datos[$orden][$usuario] ?? []);
                                $checked = $marcadoPorAlguien ? 'checked' : '';
                                $editable = ($usuario === $tipo && $marcadoPorAlguien && $tareasMarcadasPorTodos[$tarea] !== $usuario) ? 'disabled' : '';
                                if ($usuario !== $tipo) {
                                    $editable = 'disabled';
                                }
                            ?>
                            <label>
                                <input
                                    type="checkbox"
                                    name="tareas[]"
                                    value="<?php echo htmlspecialchars($tarea); ?>"
                                    <?php echo $checked . ' ' . $editable; ?>
                                >
                                <span><?php echo htmlspecialchars($tarea); ?></span>
                                <?php if ($marcadoPorAlguien && $tareasMarcadasPorTodos[$tarea] !== $usuario): ?>
                                    <small class="task-owner">(Marcado por <?php echo htmlspecialchars($tareasMarcadasPorTodos[$tarea]); ?>)</small>
                                <?php endif; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>

                    <div class="form-group">
                        <label for="comentarios_<?php echo strtolower($tipo); ?>">Comentario o detalle:</label>
                        <textarea name="comentarios" id="comentarios_<?php echo strtolower($tipo); ?>" rows="4" cols="40"><?php echo htmlspecialchars($datos[$orden]['meta']['comentarios_' . strtolower($tipo)] ?? ''); ?></textarea>
                    </div>

                    <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipo); ?>">
                    
                    <div class="button-group">
                        <?php if ($usuario === $tipo): ?>
                            <button type="submit">Guardar</button>
                            <?php if ($usuario === "Garantía"): ?>
                                <button type="button" onclick="generarPDF()">Descargar PDF</button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </fieldset>
            </form>
        <?php endforeach; ?>
    </div>

<script>
function toggleMenu() {
    const menu = document.getElementById('dropdownMenu');
    menu.classList.toggle('show-menu'); // Esto añade la clase si no está, la quita si sí está
}

// Opcional: Cerrar el menú si se hace click fuera de él
document.addEventListener('click', function(event) {
    const optionsMenu = document.querySelector('.options-menu');
    const dropdownMenu = document.getElementById('dropdownMenu');
    if (!optionsMenu.contains(event.target) && dropdownMenu.classList.contains('show-menu')) {
        dropdownMenu.classList.remove('show-menu');
    }
});

function titleCase(input) {
    input.value = input.value.toLowerCase().replace(/\b\w/g, function(l){ return l.toUpperCase() });
}

function agregarServicio() {
    const container = document.getElementById('servicios-container');
    const newDiv = document.createElement('div');
    newDiv.classList.add('form-group'); // Reutiliza el estilo de grupo de formulario
    const select = document.createElement('select');
    select.name = 'tipo_servicio[]';
    select.required = true;
    select.innerHTML = `
        <option value="">-- Selecciona un servicio --</option>
        <option value="Servicio por diagnóstico">Servicio por diagnóstico</option>
        <option value="Reingreso por garantía">Reingreso por garantía</option>
        <option value="Reparacíon menor">Reparacíon menor</option>
        <option value="Publica de empleados">Publica de empleados</option>
        <option value="Servicio por kilometraje básico">Servicio por kilometraje básico</option>
        <option value="Servicio por kilometraje intermedio">Servicio por kilometraje intermedio</option>
        <option value="Servicio por kilometraje premium">Servicio por kilometraje premium</option>
        <option value="Cambio de aceite S-sintetico">Cambio de aceite S-sintético</option>
        <option value="Cambio de aceite sintetico">Cambio de aceite sintético</option>
        <option value="Cambio de aceite de trasmisión AUT">Cambio de aceite de transmisión AUT</option>
        <option value="Cambio de embrague">Cambio de embrague</option>
        <option value="Lavado de inyectores">Lavado de inyectores</option>
        <option value="Cambio de balatas traseras">Cambio de balatas traseras</option>
        <option value="Cambio de balatas delanteras">Cambio de balatas delanteras</option>
        <option value="Limp. de cuerpo de aceleración">Limpieza de cuerpo de aceleración</option>
        <option value="Alineación + Balanceo">Alineación + Balanceo</option>
        <option value="Mto. al aire acondicionado">Mantenimiento al aire acondicionado</option>
        <option value="Estética general">Estética general</option>
        <option value="Estética de motor">Estética de motor</option>
        <option value="Estética de interior">Estética de interior</option>
        <option value="Descontaminación de cristales">Descontaminación de cristales</option>
        <option value="Higienizador A/C y eliminación de olores">Higienizador A/C y eliminación de olores</option>
        <option value="Pulido de faros">Pulido de faros</option>
    `;
    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.classList.add('options-button');
    removeButton.textContent = 'Eliminar';
    removeButton.style.backgroundColor = '#dc3545';
    removeButton.style.marginLeft = '10px';
    removeButton.onclick = function() {
        container.removeChild(newDiv);
    };

    newDiv.appendChild(select);
    newDiv.appendChild(removeButton);
    container.appendChild(newDiv);
}
</script>

<script>
async function generarPDF() {
    const { jsPDF } = window.jspdf;
    const checklistElement = document.querySelector('.container'); // Captura el contenedor principal

    const canvas = await html2canvas(checklistElement, {
        scale: 2, // Aumenta la escala para mejor calidad
        useCORS: true // Necesario si hay imágenes de diferentes orígenes
    });
    const imgData = canvas.toDataURL('image/png');

    const pdf = new jsPDF('p', 'mm', 'a4');
    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();
    const imgProps = pdf.getImageProperties(imgData);

    const imgWidth = pageWidth - 20; // Deja un margen de 10mm a cada lado
    const imgHeight = (imgProps.height * imgWidth) / imgProps.width;

    let position = 10; // Margen superior

    if (imgHeight < pageHeight - 20) { // Si cabe en una página con márgenes
        pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
    } else {
        // Múltiples páginas
        let heightLeft = imgHeight;
        while (heightLeft > 0) {
            pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
            heightLeft -= (pageHeight - 20); // Resta el espacio de los márgenes
            position -= (pageHeight - 20); // Ajusta la posición para la siguiente página
            if (heightLeft > 0) {
                pdf.addPage();
            }
        }
    }

    pdf.save("checklist_<?php echo $orden; ?>.pdf");
}
</script>

</body>
</html>