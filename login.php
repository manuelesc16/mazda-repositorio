<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            /* Fondo oscuro con un ligero degradado */
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); /* Tonos de azul oscuro/gris */
            margin: 0;
            padding: 20px;
            color: #ecf0f1; /* Color de texto claro para el fondo oscuro */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
            overflow: hidden;
        }

        .login-container {
            background-color: #ffffff; /* El contenedor sigue siendo claro para contrastar */
            padding: 40px;
            border-radius: 12px; /* Bordes más redondeados */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4); /* Sombra más oscura para destacar sobre el fondo */
            width: 100%;
            max-width: 400px; /* Ancho máximo para el formulario */
            text-align: center;
            opacity: 0; /* Inicialmente invisible para la animación */
            transform: translateY(20px); /* Ligeramente desplazado para la animación */
            animation: fadeInSlideUp 0.8s ease-out forwards; /* Animación de entrada */
        }

       .logo-img {
    display: block; /* Asegura que la imagen se comporte como un bloque */
    margin: 0 auto; /* Centra la imagen horizontalmente */
    max-width: 400px; /* ¡Mantenido significativamente aumentado para ser mucho más grande! */
    height: auto;
    margin-bottom: 25px; /* Espacio entre el logo y el título */
}

        h2 {
            color: #007bff;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 2em; /* Título más grande */
        }

 .subtitle {
    color: #888; /* Gris */
    /* font-size: 0.9em; <--- Elimina esta línea */
    margin-top: 0;
    margin-bottom: 30px; /* Espacio debajo del subtítulo */
    font-weight: 500; /* Puedes añadir esto para que coincida más con el peso de la etiqueta */
}

        form {
            display: flex;
            flex-direction: column;
            gap: 20px; /* Espacio entre elementos del formulario */
        }

        .form-group {
            text-align: left; /* Alinea etiquetas a la izquierda */
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
            transition: color 0.3s ease; /* Transición de color para el focus */
        }

        input[type="password"],
        select {
            width: calc(100% - 24px); /* Full width, restando padding y border */
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px; /* Bordes más suaves */
            font-size: 1.1em;
            box-sizing: border-box;
            transition: border-color 0.3s ease, box-shadow 0.3s ease; /* Transiciones para interactividad */
        }

        input[type="password"]:focus,
        select:focus {
            border-color: #007bff; /* Color de borde al enfocar */
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.2); /* Sombra suave al enfocar */
            outline: none; /* Eliminar el contorno predeterminado del navegador */
        }

        button[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.2); /* Sombra para el botón */
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
            transform: translateY(-3px); /* Efecto de "levantar" */
            box-shadow: 0 6px 15px rgba(0, 123, 255, 0.3); /* Sombra más pronunciada al hover */
        }

        button[type="submit"]:active {
            transform: translateY(0); /* Vuelve a la posición original al hacer clic */
            box-shadow: 0 2px 5px rgba(0, 123, 255, 0.2); /* Sombra más sutil al hacer clic */
        }

        p.error-message {
            color: #dc3545; /* Rojo para mensajes de error */
            font-weight: 500;
            margin-top: 20px;
            animation: shake 0.5s ease-in-out; /* Animación de "sacudida" para errores */
        }

        /* Animación de entrada para el contenedor */
        @keyframes fadeInSlideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Animación de sacudida para errores */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        /* Responsive adjustments */
        @media (max-width: 500px) {
            .login-container {
                padding: 30px 20px;
                margin: 0 15px;
            }
            h2 {
                font-size: 1.8em;
            }
            input[type="password"],
            select,
            button[type="submit"] {
                font-size: 1em;
            }
            .logo-img {
                max-width: 250px; /* Ajuste para móviles para que no sea excesivamente grande */
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="Logo_Mazda.png" alt="Logo Mazda" class="logo-img">

        <h2>Checklist de servicio</h2>
        <p class="subtitle">Iniciar sesión</p>
        <form action="validar.php" method="post">
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <select name="usuario" id="usuario" required>
                    <option value="Garantía">Garantía</option>
                    <option value="Asesor">Asesor</option>
                    <option value="Mecánico">Mecánico</option>
                    <option value="Lavado">Lavado</option>
                </select>
            </div>

            <div class="form-group">
                <label for="clave">Contraseña:</label>
                <input type="password" name="clave" id="clave" required>
            </div>

            <button type="submit">Ingresar</button>
        </form>

        <?php
        if (isset($_GET['error'])) {
            echo "<p class='error-message'>Usuario o contraseña incorrectos</p>";
        }
        ?>
    </div>
</body>
</html>