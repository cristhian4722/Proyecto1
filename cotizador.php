<?php

$cliente = '';
$precio = '';
$cantidad = 1;
$error = '';
$resultado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente = trim($_POST['cliente'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $cantidad = trim($_POST['cantidad'] ?? '1');

    if ($cliente === '' || $precio === '' || $cantidad === '') {
        $error = 'Por favor completa todos los campos.';
    } elseif (
        ! is_numeric($precio)
        || ! is_numeric($cantidad)
        || (float) $precio <= 0
        || (int) $cantidad < 1
    ) {
        $error = 'El precio debe ser mayor a 0 y la cantidad al menos 1.';
    } else {
        $total = (float) $precio * (int) $cantidad;
        $resultado = 'Cotización para ' . $cliente . ': Total $' . number_format($total, 0, ',', '.');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotizador PHP</title>
</head>
<body>
    <h1>Cotizador en el Servidor</h1>

    <?php if ($error !== '') : ?>
        <p style="color:red; font-weight:bold;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($resultado !== '') : ?>
        <p style="color:green; font-weight:bold;"><?= htmlspecialchars($resultado) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Nombre del cliente:</label><br>
        <input type="text" name="cliente" value="<?= htmlspecialchars((string) $cliente) ?>"><br><br>

        <label>Precio unitario ($):</label><br>
        <input type="number" name="precio" step="any" value="<?= htmlspecialchars((string) $precio) ?>"><br><br>

        <label>Cantidad:</label><br>
        <input type="number" name="cantidad" min="1" value="<?= htmlspecialchars((string) $cantidad) ?>"><br><br>

        <button type="submit">Calcular en el Servidor</button>
    </form>
</body>
</html>
