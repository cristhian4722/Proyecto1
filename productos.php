<?php

require_once __DIR__ . '/conexion.php';

$resultado = $pdo->query('SELECT nombre, precio, stock FROM productos ORDER BY nombre');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos</title>
    <style>
        table { border-collapse: collapse; font-family: sans-serif; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
        th { background: #f0f0f0; }
        .agotado { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Listado de productos</h1>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($p = $resultado->fetch()) : ?>
                <tr>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td>$<?= number_format((float) $p['precio'], 2) ?></td>
                    <td>
                        <?php if ((int) $p['stock'] > 0) : ?>
                            Disponible <?= (int) $p['stock'] ?>
                        <?php else : ?>
                            <span class="agotado">Agotado</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
