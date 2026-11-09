<?php

require_once __DIR__ . '/conexion.php';

$categoriasOpciones = [
    'Electrónica',
    'Periféricos',
    'Accesorios',
    'Monitores',
    'General',
];

if (isset($_GET['borrar'])) {
    $idBorrar = (int) $_GET['borrar'];
    if ($idBorrar > 0) {
        $stmt = $pdo->prepare('DELETE FROM productos WHERE id = ?');
        $stmt->execute([$idBorrar]);
        header('Location: index.php?ok=eliminado');
        exit;
    }
}

$busqueda = trim($_GET['busqueda'] ?? '');
$productoEditar = null;
$idEditar = (int) ($_GET['editar'] ?? 0);

if ($idEditar > 0) {
    $stmt = $pdo->prepare('SELECT id, nombre, categoria, precio, stock FROM productos WHERE id = ?');
    $stmt->execute([$idEditar]);
    $productoEditar = $stmt->fetch() ?: null;
}

$sql = 'SELECT id, nombre, categoria, precio, stock FROM productos';
$params = [];
if ($busqueda !== '') {
    $sql .= ' WHERE nombre LIKE ? OR categoria LIKE ?';
    $like = '%' . $busqueda . '%';
    $params = [$like, $like];
}
$sql .= ' ORDER BY id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

$totalProductos = (int) $pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn();
$totalStock = (int) $pdo->query('SELECT COALESCE(SUM(stock), 0) FROM productos')->fetchColumn();
$valorInventario = (float) $pdo->query('SELECT COALESCE(SUM(precio * stock), 0) FROM productos')->fetchColumn();

$idForm = $productoEditar ? (int) $productoEditar['id'] : 0;
$nombreVal = $productoEditar['nombre'] ?? '';
$categoriaVal = $productoEditar['categoria'] ?? '';
$precioVal = $productoEditar !== null ? $productoEditar['precio'] : '';
$stockVal = $productoEditar !== null ? $productoEditar['stock'] : '';

$ok = $_GET['ok'] ?? '';
$error = $_GET['error'] ?? '';

$mensajesOk = [
    'creado' => 'Operación realizada con éxito: producto creado.',
    'actualizado' => 'Operación realizada con éxito: producto actualizado.',
    'eliminado' => 'Operación realizada con éxito: producto eliminado.',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario IberoTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #e8ecef; }
        .titulo-app {
            background: linear-gradient(135deg, #1a2f4a, #243b55);
            color: #fff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }
        .card-panel {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #dee2e6;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }
        .stat-card {
            text-align: center;
            padding: 1rem;
        }
        .stat-card .valor {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a2f4a;
        }
        .btn-editar {
            background: #3d5a80;
            color: #fff;
            border: none;
            font-size: 0.85rem;
        }
        .btn-editar:hover { background: #2c4463; color: #fff; }
        .table thead th { background: #f8f9fa; font-size: 0.9rem; }
        .badge-disp { background: #198754; }
        .badge-agotado { background: #dc3545; }
    </style>
</head>
<body class="py-4">
    <div class="container-xl">
        <div class="titulo-app mb-3">
            Inventario IberoTech — Aplicación CRUD (PHP + MySQL)
        </div>

        <?php if ($ok !== '' && isset($mensajesOk[$ok])) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensajesOk[$ok]) ?></div>
        <?php endif; ?>

        <?php if ($error !== '') : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card-panel stat-card h-100">
                    <div class="text-muted small">Productos listados</div>
                    <div class="valor"><?= $totalProductos ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-panel stat-card h-100">
                    <div class="text-muted small">Unidades en Stock</div>
                    <div class="valor"><?= number_format($totalStock, 0, ',', '.') ?> uds</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-panel stat-card h-100">
                    <div class="text-muted small">Valor del Inventario</div>
                    <div class="valor">$<?= number_format($valorInventario, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card-panel p-3">
                    <h2 class="h5 mb-3">
                        <?= $idForm > 0 ? 'Editar Producto #' . $idForm : 'Nuevo Producto' ?>
                    </h2>
                    <form method="POST" action="guardar_producto.php">
                        <input type="hidden" name="id" value="<?= $idForm ?>">

                        <div class="mb-3">
                            <label class="form-label">Nombre del producto</label>
                            <input type="text" name="nombre" class="form-control" required
                                   value="<?= htmlspecialchars($nombreVal) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Categoría</label>
                            <select name="categoria" class="form-select" required>
                                <option value="">— Seleccionar —</option>
                                <?php foreach ($categoriasOpciones as $cat) : ?>
                                    <option value="<?= htmlspecialchars($cat) ?>"
                                        <?= $categoriaVal === $cat ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat) ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php if (
                                    $categoriaVal !== ''
                                    && ! in_array($categoriaVal, $categoriasOpciones, true)
                                ) : ?>
                                    <option value="<?= htmlspecialchars($categoriaVal) ?>" selected>
                                        <?= htmlspecialchars($categoriaVal) ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Precio unitario ($ COP)</label>
                            <input type="number" name="precio" class="form-control" step="0.01" min="0" required
                                   value="<?= htmlspecialchars((string) $precioVal) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Stock (unidades)</label>
                            <input type="number" name="stock" class="form-control" min="0" required
                                   value="<?= htmlspecialchars((string) $stockVal) ?>">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <?= $idForm > 0 ? 'Guardar Cambios' : 'Agregar al Inventario' ?>
                        </button>
                        <?php if ($idForm > 0) : ?>
                            <a href="index.php" class="btn btn-outline-secondary w-100 mt-2">Cancelar edición</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card-panel p-3">
                    <form method="GET" action="index.php" class="row g-2 align-items-center mb-3">
                        <div class="col">
                            <input type="text" name="busqueda" class="form-control"
                                   placeholder="Buscar por nombre o categoría…"
                                   value="<?= htmlspecialchars($busqueda) ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </div>
                        <div class="col-auto">
                            <a href="index.php" class="btn btn-outline-secondary">Limpiar</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($productos) === 0) : ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No se encontraron productos.
                                        </td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($productos as $p) : ?>
                                        <tr>
                                            <td><?= (int) $p['id'] ?></td>
                                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                                            <td><?= htmlspecialchars($p['categoria']) ?></td>
                                            <td>$<?= number_format((float) $p['precio'], 0, ',', '.') ?></td>
                                            <td>
                                                <?php if ((int) $p['stock'] > 0) : ?>
                                                    <span class="badge badge-disp">
                                                        disp. <?= (int) $p['stock'] ?>
                                                    </span>
                                                <?php else : ?>
                                                    <span class="badge badge-agotado">Agotado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-nowrap">
                                                <a href="index.php?editar=<?= (int) $p['id'] ?>"
                                                   class="btn btn-sm btn-editar me-1">Editar</a>
                                                <a href="index.php?borrar=<?= (int) $p['id'] ?>"
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('¿Eliminar este producto?');">Borrar</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
