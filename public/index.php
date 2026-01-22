<?php
session_start();
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require __DIR__ . "/../src/conexion_bd.php";
$usuario_id = $_SESSION['user_id']; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="manga_verso.css">
    <title>Manga_verso</title>
</head>
<body>
<header>
    <div>
        <h1>Manga_verso</h1>
        <p>Tu portal de manga</p>
    </div>
    <div class="auth-logged">
        <a href="../pages/perfil.php"><?= htmlspecialchars($_SESSION['usuario']); ?></a>
        <a href="logout.php">Cerrar sesión</a>
    </div>
</header>

<main>
    <h1>Bienvenido a Manga_verso</h1>
    <div style="text-align:center; margin:20px 0;">
        <a class="btn-primary" href="../pages/subirManga.php">📚 Subir manga</a>
    </div>

    <h2>Mis mangas</h2>

    <!-- Filtros -->
    <div class="filtros-container">
        <form method="GET" class="filtros-form">
            <div class="filtro-item">
                <label for="categoria">Categoría:</label>
                <select name="categoria" id="categoria">
                    <option value="">Todas las categorías</option>
                    <?php
                    $stmt_cat = $conn->query("SELECT id, nombre FROM categorias ORDER BY nombre");
                    while ($cat = $stmt_cat->fetch(PDO::FETCH_ASSOC)) {
                        $selected = (isset($_GET['categoria']) && $_GET['categoria'] == $cat['id']) ? 'selected' : '';
                        echo "<option value='{$cat['id']}' {$selected}>{$cat['nombre']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="filtro-item">
                <label for="busqueda">Buscar por título:</label>
                <input type="text" name="busqueda" id="busqueda" value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>" placeholder="Buscar manga...">
            </div>

            <div class="filtro-item">
                <label for="orden">Ordenar por:</label>
                <select name="orden" id="orden">
                    <option value="fecha_subida DESC" <?= (isset($_GET['orden']) && $_GET['orden'] == 'fecha_subida DESC') ? 'selected' : '' ?>>Más recientes</option>
                    <option value="fecha_subida ASC" <?= (isset($_GET['orden']) && $_GET['orden'] == 'fecha_subida ASC') ? 'selected' : '' ?>>Más antiguos</option>
                    <option value="titulo ASC" <?= (isset($_GET['orden']) && $_GET['orden'] == 'titulo ASC') ? 'selected' : '' ?>>Título A-Z</option>
                    <option value="titulo DESC" <?= (isset($_GET['orden']) && $_GET['orden'] == 'titulo DESC') ? 'selected' : '' ?>>Título Z-A</option>
                </select>
            </div>

            <div class="filtro-item">
                <button type="submit" class="btn-primary">Filtrar</button>
                <a href="index.php" class="btn-secondary">Limpiar filtros</a>
            </div>
        </form>
    </div>

    <div class="mangas-container">
        <?php
        // Construir la consulta con filtros
        $where_conditions = ["usuario_id = ?"];
        $params = [$usuario_id];

        if (!empty($_GET['categoria'])) {
            $where_conditions[] = "categoria_id = ?";
            $params[] = (int)$_GET['categoria'];
        }

        if (!empty($_GET['busqueda'])) {
            $where_conditions[] = "titulo LIKE ?";
            $params[] = "%" . $_GET['busqueda'] . "%";
        }

        $orden = $_GET['orden'] ?? 'fecha_subida DESC';
        $allowed_orders = ['fecha_subida DESC', 'fecha_subida ASC', 'titulo ASC', 'titulo DESC'];
        if (!in_array($orden, $allowed_orders)) {
            $orden = 'fecha_subida DESC';
        }

        $where_clause = implode(" AND ", $where_conditions);

        $stmt = $conn->prepare("
            SELECT m.*, c.nombre as categoria_nombre
            FROM mangas m
            LEFT JOIN categorias c ON m.categoria_id = c.id
            WHERE {$where_clause}
            ORDER BY {$orden}
        ");
        $stmt->execute($params);
        $mangas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total_resultados = count($mangas);
        echo "<p class='resultados-info'>Mostrando {$total_resultados} manga" . ($total_resultados != 1 ? 's' : '') . "</p>";

        if ($mangas) {
            foreach ($mangas as $row): ?>
                <div class="manga-card">
                    <img src="../<?= $row['portada']; ?>" alt="Portada del manga">
                    <h3><?= htmlspecialchars($row['titulo']); ?></h3>
                    <p class="categoria-badge"><?= htmlspecialchars($row['categoria_nombre'] ?? 'Sin categoría'); ?></p>
                    <p><?= htmlspecialchars($row['descripcion']); ?></p>

                    <a href="../pages/capitulos.php?manga=<?= $row['id']; ?>">📖 Ver manga</a>

                    <form action="../pages/eliminar_manga.php" method="POST" style="margin-top:10px;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este manga? Esta acción no se puede deshacer.');">
                        <input type="hidden" name="manga_id" value="<?= $row['id']; ?>">
                        <button type="submit" class="btn-primary" style="background-color:#dc3545; border:none;">
                            🗑️ Eliminar manga
                        </button>
                    </form>
                </div>
            <?php endforeach;
        } else {
            echo "<p>No tienes mangas subidos todavía.</p>";
        }
        ?>
    </div>
</main>

<footer>
    <p>&copy; 2025 Manga_verso - Creado por Adrian Arenas Vega y Mercedes Lizcano Mora</p>
</footer>
</body>
</html>
