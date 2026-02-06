<?php
session_start();
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
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
    <link rel="stylesheet" href="../css/css/manga_verso.css">
    <title>Mi Biblioteca - Manga_verso</title>
</head>
<body>
<header>
    <div>
        <h1>Manga_verso</h1>
        <p>Tu portal de manga</p>
    </div>
    <div class="auth-logged">
        <a href="perfil.php"><?= htmlspecialchars($_SESSION['usuario']); ?></a>
        <a href="../public/logout.php">Cerrar sesión</a>
    </div>
</header>

<main>
    <h1>📚 Mi Biblioteca</h1>

    <!-- Tabs de navegación -->
    <div class="biblioteca-tabs">
        <button class="tab-button active" onclick="switchTab(event, 'mis-mangas')">Mis Mangas</button>
        <button class="tab-button" onclick="switchTab(event, 'biblioteca')">Mangas Originales</button>
    </div>

    <!-- TAB 1: MIS MANGAS -->
    <div id="mis-mangas" class="tab-content active">
        <!-- Filtros -->
        <div class="filtros-container">
            <form method="GET" class="filtros-form">
                <input type="hidden" name="tab" value="mis-mangas">
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
                    <a href="biblioteca.php" class="btn-secondary">Limpiar filtros</a>
                </div>
            </form>
        </div>

        <div class="mangas-container">
            <?php
            // MIS MANGAS
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
                        <?php if ($row['es_original']): ?>
                            <p class="categoria-badge" style="background-color: #FFD700; color: #333;">✨ Original</p>
                        <?php else: ?>
                            <p class="categoria-badge"><?= htmlspecialchars($row['categoria_nombre'] ?? 'Sin categoría'); ?></p>
                        <?php endif; ?>
                        <p><?= htmlspecialchars($row['descripcion']); ?></p>

                        <a href="capitulos.php?manga=<?= $row['id']; ?>">📖 Ver manga</a>

                        <?php if ($row['es_original']): ?>
                            <a href="gestionar_compartir.php?manga=<?= $row['id']; ?>" style="margin-top:10px; background-color:#28a745; color:white; padding:10px 20px; text-decoration:none; border-radius:4px; display:inline-block;" class="btn-primary">📢 Compartir</a>
                        <?php endif; ?>

                        <form action="eliminar_manga.php" method="POST" style="margin-top:10px;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este manga? Esta acción no se puede deshacer.');">
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
    </div>

    <!-- TAB 2: MANGAS ORIGINALES AGREGADOS A LA BIBLIOTECA -->
    <div id="biblioteca" class="tab-content">
        <?php if (isset($_SESSION['exito'])): ?>
            <div style="color: green; margin-bottom: 20px; border: 1px solid green; padding: 10px; background-color: #e6ffe6; border-radius: 4px;">
                <?= htmlspecialchars($_SESSION['exito']); ?>
            </div>
            <?php unset($_SESSION['exito']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="color: red; margin-bottom: 20px; border: 1px solid red; padding: 10px; background-color: #ffe6e6; border-radius: 4px;">
                <?= htmlspecialchars($_SESSION['error']); ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="mangas-container">
            <?php
            // MANGAS ORIGINALES AGREGADOS A MI BIBLIOTECA
            $stmt_biblioteca = $conn->prepare("
                SELECT m.id, m.titulo, m.descripcion, m.portada, m.fecha_subida, m.categoria_id, m.es_original, u.usuario as autor,
                       COUNT(c.id) as total_capitulos, bu.fecha_agregado
                FROM biblioteca_usuario bu
                JOIN mangas m ON bu.manga_id = m.id
                JOIN usuarios u ON m.usuario_id = u.id
                LEFT JOIN capitulos c ON m.id = c.manga_id
                WHERE bu.usuario_id = ?
                GROUP BY m.id
                ORDER BY bu.fecha_agregado DESC
            ");
            $stmt_biblioteca->execute([$usuario_id]);
            $mangas_biblioteca = $stmt_biblioteca->fetchAll(PDO::FETCH_ASSOC);

            if ($mangas_biblioteca) {
                echo "<p class='resultados-info'>Tienes " . count($mangas_biblioteca) . " manga(s) en tu biblioteca</p>";
                foreach ($mangas_biblioteca as $manga): ?>
                    <div class="manga-card">
                        <img src="../<?= htmlspecialchars($manga['portada']); ?>" alt="<?= htmlspecialchars($manga['titulo']); ?>" onerror="this.src='../img/placeholder.png'">
                        <h3><?= htmlspecialchars($manga['titulo']); ?></h3>
                        <p class="categoria-badge" style="color: #666;">👤 <?= htmlspecialchars($manga['autor']); ?>
                            <?php if ($manga['es_original']): ?>
                                <span style="color: #FFD700; font-weight: bold;">✨</span>
                            <?php endif; ?>
                        </p>
                        <p style="font-size: 12px; color: #999;">📖 <?= $manga['total_capitulos']; ?> capítulo(s)</p>
                        <p><?= htmlspecialchars($manga['descripcion']); ?></p>

                        <a href="leer_manga_compartido.php?manga=<?= $manga['id']; ?>">📖 Leer</a>

                        <form action="procesar_biblioteca.php" method="POST" style="margin-top:10px;">
                            <input type="hidden" name="manga_id" value="<?= $manga['id']; ?>">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="referer" value="biblioteca.php">
                            <button type="submit" class="btn-primary" style="background-color:#dc3545; border:none; width: 100%;" onclick="return confirm('¿Eliminar de tu biblioteca?')">🗑️ Eliminar de biblioteca</button>
                        </form>
                    </div>
                <?php endforeach;
            } else {
                echo "<p>Tu biblioteca está vacía. Ve a 'Mangas Compartidos' para agregar mangas.</p>";
            }
            ?>
        </div>
    </div>

    <div style="text-align: center; margin-top: 40px;">
        <a href="../public/index.php" class="btn-secondary">⬅ Volver al menú</a>
    </div>
</main>

<footer>
    <p>&copy; 2025 Manga_verso</p>
</footer>

<script>
function switchTab(event, tabName) {
    event.preventDefault();
    
    // Ocultar todos los tabs
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Desactivar todos los botones
    const buttons = document.querySelectorAll('.tab-button');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    // Mostrar el tab seleccionado
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
    
    // Activar el botón del tab que fue clickeado
    event.target.classList.add('active');
}
</script>
</body>
</html>
