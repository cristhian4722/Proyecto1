<?php

require_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

$nombre = trim($_POST['nombre'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$precio = $_POST['precio'] ?? '';
$stock = $_POST['stock'] ?? '';

if ($nombre === '' || $categoria === '' || $precio === '' || $stock === '') {
    $error = 'Todos los campos son obligatorios.';
} elseif (
    ! is_numeric($precio)
    || (float) $precio < 0
    || ! is_numeric($stock)
    || (int) $stock < 0
) {
    $error = 'El precio y el stock deben ser números mayores o iguales a 0.';
} else {
    if ($id > 0) {
        $sql = 'UPDATE productos SET nombre = ?, categoria = ?, precio = ?, stock = ? WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $categoria, (float) $precio, (int) $stock, $id]);
        header('Location: index.php?ok=actualizado');
        exit;
    }

    $sql = 'INSERT INTO productos (nombre, categoria, precio, stock) VALUES (?, ?, ?, ?)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre, $categoria, (float) $precio, (int) $stock]);
    header('Location: index.php?ok=creado');
    exit;
}

header('Location: index.php?error=' . urlencode($error));
