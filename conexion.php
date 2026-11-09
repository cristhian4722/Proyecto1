<?php

// Archivo: C:\laragon\www\Proyecto1\conexion.php

$servidor = '127.0.0.1';
$baseDatos = 'diplomado_ibero';
$usuario = 'root';
$clave = ''; // En Laragon por defecto va vacío

try {
    $pdo = new PDO(
        "mysql:host=$servidor;dbname=$baseDatos;charset=utf8mb4",
        $usuario,
        $clave,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}
