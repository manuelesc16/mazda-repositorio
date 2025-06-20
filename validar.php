<?php
session_start();

$usuario = $_POST['usuario'];
$clave = $_POST['clave'];

// Aquí se podría validar con una base de datos
// Supongamos que la contraseña para todos es '1234'
if ($clave === '1234') {
  $_SESSION['usuario'] = $usuario;
  header("Location: orden.php"); // ← Redirige a orden.php después del login
  exit();
} else {
  header("Location: login.php?error=1");
  exit();
}
