<?php
session_start();
require __DIR__ . "/../src/conexion_bd.php";

$is_logged_in = !empty($_SESSION['usuario']) && !empty($_SESSION['user_id']);
$usuario_id = $is_logged_in ? $_SESSION['user_id'] : 0;
$nombre_usuario = $is_logged_in ? $_SESSION['usuario'] : 'INVITADO';

// Obtener parámetros de búsqueda y filtros
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$categoria_id = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'recientes';

// Obtener todas las categorías para el select
$stmt_cats = $conn->query("SELECT id, nombre FROM categorias ORDER BY nombre");
$categorias = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

// --- CONFIGURACIÓN DE PAGINACIÓN ---
$limite = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $limite;

// Consulta base para contar el total (sin LIMIT/OFFSET)
$query_count = "
    SELECT COUNT(DISTINCT m.id)
    FROM mangas_compartidos mc
    JOIN mangas m ON mc.manga_id = m.id
    JOIN usuarios u ON m.usuario_id = u.id
    WHERE mc.activo = 1 " . ($is_logged_in ? "AND m.usuario_id != ?" : "") . "
";

$params_count = $is_logged_in ? [$usuario_id] : [];

if (!empty($busqueda)) {
    $query_count .= " AND (m.titulo LIKE ? OR u.usuario LIKE ?)";
    $params_count[] = "%" . $busqueda . "%";
    $params_count[] = "%" . $busqueda . "%";
}

if (!empty($categoria_id)) {
    $query_count .= " AND m.categoria_id = ?";
    $params_count[] = $categoria_id;
}

$stmt_count = $conn->prepare($query_count);
$stmt_count->execute($params_count);
$total_mangas = $stmt_count->fetchColumn();
$total_paginas = ceil($total_mangas / $limite);

// Consulta principal con LIMIT y OFFSET
$query = "
    SELECT m.id, m.titulo, m.descripcion, m.portada, m.fecha_subida, m.categoria_id, m.es_original, u.usuario as autor,
           COUNT(c.id) as total_capitulos, mc.fecha_comparticion
    FROM mangas_compartidos mc
    JOIN mangas m ON mc.manga_id = m.id
    JOIN usuarios u ON m.usuario_id = u.id
    LEFT JOIN capitulos c ON m.id = c.manga_id
    WHERE mc.activo = 1 " . ($is_logged_in ? "AND m.usuario_id != ?" : "") . "
";

$params = $is_logged_in ? [$usuario_id] : [];

if (!empty($busqueda)) {
    $query .= " AND (m.titulo LIKE ? OR u.usuario LIKE ?)";
    $params[] = "%" . $busqueda . "%";
    $params[] = "%" . $busqueda . "%";
}

if (!empty($categoria_id)) {
    $query .= " AND m.categoria_id = ?";
    $params[] = $categoria_id;
}

$query .= " GROUP BY m.id";

// Ordenamiento dinámico
if ($orden === 'antiguos') {
    $query .= " ORDER BY mc.fecha_comparticion ASC";
} elseif ($orden === 'capitulos') {
    $query .= " ORDER BY total_capitulos DESC, mc.fecha_comparticion DESC";
} else {
    $query .= " ORDER BY mc.fecha_comparticion DESC";
}

// Añadir límites para paginación
$query .= " LIMIT $limite OFFSET $offset";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$mangas_compartidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorar Mangas - Manga_verso</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0f172a; color: white; min-height: 100vh; display: flex; flex-direction: column; }
        .manga-font { font-family: 'Bangers', cursive; letter-spacing: 0.05em; }
        
        /* Efecto Sakura */
        .sakura {
            position: fixed; background: #f472b6; border-radius: 100% 0% 100% 0%;
            opacity: 0.7; pointer-events: none; z-index: 1; animation: fall linear infinite;
        }
        @keyframes fall {
            0% { transform: translateY(-10vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            100% { transform: translateY(110vh) rotate(360deg); opacity: 0; }
        }

        .manga-card-panel {
            background: white; border: 3px solid #000;
            box-shadow: 8px 8px 0px #3b82f6; transition: 0.2s;
        }
        .manga-card-panel:hover {
            transform: translate(-4px, -4px);
            box-shadow: 12px 12px 0px #f472b6;
        }
    </style>
</head>
<body class="overflow-x-hidden">

    <div id="sakura-container" class="fixed inset-0 pointer-events-none z-0"></div>

    <header class="bg-white border-b-4 border-black p-4 relative z-50 text-black">
        <div class="container mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
            <a href="../public/index.php" class="text-center sm:text-left">
                <h1 class="manga-font text-4xl italic transform -rotate-1">
                    MANGA<span class="text-blue-500">_</span>VERSO
                </h1>
            </a>
            <div class="flex items-center gap-4 justify-center">
                <a href="blog.php" class="font-black text-sm uppercase hover:text-pink-500 transition-colors">Blog</a>
                <?php if ($is_logged_in): ?>
                    <span class="hidden md:block font-black text-sm uppercase">Bienvenido, <?= htmlspecialchars($nombre_usuario); ?></span>
                    <a href="perfil.php" class="bg-yellow-400 p-2 border-2 border-black shadow-[3px_3px_0px_#000] hover:shadow-none transition-all">👤</a>
                <?php else: ?>
                    <a href="../public/login.php?redirect=../pages/mangas_compartidos.php" class="bg-blue-600 text-white font-black text-xs px-6 py-2 border-2 border-black shadow-[3px_3px_0px_#f472b6] hover:shadow-none transition-all">
                        INICIAR SESIÓN
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-12 relative z-10 flex-grow">
        

        <div class="flex flex-col xl:flex-row justify-between items-center mb-12 gap-6 w-full">
            <h2 class="manga-font text-6xl italic drop-shadow-[4px_4px_0px_#3b82f6] xl:w-1/4 text-center xl:text-left">COMUNIDAD</h2>
            
            <form method="GET" class="flex flex-col md:flex-row w-full xl:w-3/4 justify-end gap-3 flex-wrap">
                <div class="flex-grow md:min-w-[200px]">
                    <input type="text" name="busqueda" placeholder="Buscar título o autor..." 
                           value="<?= htmlspecialchars($busqueda); ?>"
                           class="w-full border-4 border-black p-2 text-black font-bold outline-none focus:bg-blue-50 h-full">
                </div>
                
                <select name="categoria" class="border-4 border-black p-2 text-black font-bold outline-none focus:bg-pink-50 cursor-pointer flex-grow md:flex-grow-0">
                    <option value="">Categoría: Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($categoria_id == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="orden" class="border-4 border-black p-2 text-black font-bold outline-none focus:bg-blue-50 cursor-pointer flex-grow md:flex-grow-0">
                    <option value="recientes" <?= ($orden === 'recientes') ? 'selected' : '' ?>>Más recientes</option>
                    <option value="antiguos" <?= ($orden === 'antiguos') ? 'selected' : '' ?>>Más antiguos</option>
                    <option value="capitulos" <?= ($orden === 'capitulos') ? 'selected' : '' ?>>Más capítulos</option>
                </select>

                <button type="submit" class="bg-black text-white px-6 py-2 font-black border-4 border-black shadow-[4px_4px_0px_#f472b6] hover:bg-blue-600 hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">🔍</button>
                
                <?php if (!empty($busqueda) || !empty($categoria_id) || $orden !== 'recientes'): ?>
                    <a href="mangas_compartidos.php" class="bg-red-500 flex justify-center items-center px-4 py-2 border-4 border-black font-black text-white shadow-[4px_4px_0px_#000] hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all" title="Limpiar filtros">✕</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($mangas_compartidos): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
                <?php foreach ($mangas_compartidos as $manga): ?>
                    <div class="manga-card-panel flex flex-col h-full overflow-hidden text-black">
                        <div class="relative">
                            <img src="../<?= htmlspecialchars($manga['portada']); ?>" 
                                 class="w-full h-72 object-cover border-b-4 border-black"
                                 onerror="this.src='../img/blog_placeholder.png'">
                            <div class="absolute top-2 right-2 bg-yellow-400 border-2 border-black px-2 py-1 font-black text-xs shadow-[2px_2px_0px_#000]">
                                📖 <?= $manga['total_capitulos']; ?> CAP.
                            </div>
                        </div>

                        <div class="p-4 flex flex-col flex-grow">
                            <h3 class="manga-font text-2xl leading-tight mb-2 truncate"><?= htmlspecialchars($manga['titulo']); ?></h3>
                            
                            <?php if ($manga['es_original']): ?>
                                <span class="bg-pink-100 text-pink-600 text-[10px] font-black px-2 py-1 border border-pink-600 self-start mb-3">✨ ORIGINAL</span>
                            <?php endif; ?>

                            <div class="flex items-center gap-2 mb-4 text-sm font-bold text-slate-600">
                                <span class="bg-blue-100 p-1 rounded">👤</span>
                                <span><?= htmlspecialchars($manga['autor']); ?></span>
                            </div>

                            <a href="leer_manga_compartido.php?manga=<?= $manga['id']; ?>" 
                               class="mt-auto block text-center bg-blue-500 text-white py-3 manga-font text-2xl border-4 border-black shadow-[4px_4px_0px_#000] hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                                LEER AHORA
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Controles de Paginación -->
            <?php if ($total_paginas > 1): ?>
                <div class="mt-16 flex flex-wrap justify-center gap-3">
                    <?php 
                    // Construir URL base manteniendo filtros
                    $url_pagi = "mangas_compartidos.php?busqueda=" . urlencode($busqueda) . "&categoria=$categoria_id&orden=$orden";
                    ?>

                    <?php if ($pagina_actual > 1): ?>
                        <a href="<?= $url_pagi ?>&pagina=<?= $pagina_actual - 1 ?>" 
                           class="bg-white text-black px-6 py-2 border-4 border-black font-black uppercase text-xs shadow-[4px_4px_0px_#3b82f6] hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                           « Anterior
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <a href="<?= $url_pagi ?>&pagina=<?= $i ?>" 
                           class="px-4 py-2 border-4 border-black font-black text-xs transition-all <?= ($i == $pagina_actual) ? 'bg-pink-500 text-white shadow-[4px_4px_0px_#000]' : 'bg-white text-black hover:bg-yellow-50 shadow-[4px_4px_0px_#000] hover:shadow-none hover:translate-x-1 hover:translate-y-1' ?>">
                           <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pagina_actual < $total_paginas): ?>
                        <a href="<?= $url_pagi ?>&pagina=<?= $pagina_actual + 1 ?>" 
                           class="bg-blue-500 text-white px-6 py-2 border-4 border-black font-black uppercase text-xs shadow-[4px_4px_0px_#000] hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                           Siguiente »
                        </a>
                    <?php endif; ?>
                </div>
                <p class="text-center text-[10px] text-blue-400 font-black uppercase tracking-widest mt-6">
                    PÁGINA <?= $pagina_actual ?> DE <?= $total_paginas ?> (TOTAL: <?= $total_mangas ?> MANGAS)
                </p>
            <?php endif; ?>
        <?php else: ?>
            <div class="manga-panel bg-white border-4 border-black p-12 text-center text-black shadow-[10px_10px_0px_#3b82f6]">
                <p class="manga-font text-4xl mb-4">¡Vaya! No hay nada aquí.</p>
                <p class="font-bold italic text-slate-500">Nadie ha compartido este manga todavía o el autor no existe.</p>
                <a href="mangas_compartidos.php" class="inline-block mt-6 text-blue-600 font-black underline">VER TODO EL CATÁLOGO</a>
            </div>
        <?php endif; ?>

        <div class="flex justify-center mt-20 mb-10">
            <a href="../public/index.php" class="group relative inline-block transition-transform hover:scale-105 active:scale-95">
                <div class="absolute inset-0 bg-blue-600 translate-x-1.5 translate-y-1.5 group-hover:bg-pink-500 transition-colors"></div>
                <div class="relative bg-white border-2 border-black px-8 py-2 flex items-center gap-3">
                    <span class="text-pink-500 font-black text-sm">«</span>
                    <span class="font-black text-[11px] text-black uppercase tracking-[0.2em] italic">Volver al Inicio</span>
                    <span class="text-pink-500 font-black text-sm">»</span>
                </div>
            </a>
        </div>
    </main>

    <footer class="py-10 text-center opacity-30 text-[10px] font-black tracking-[0.5em] uppercase">
        &copy; 2026 Manga_verso • GALERÍA PÚBLICA
    </footer>

    <script>
        function createSakura() {
            const container = document.getElementById('sakura-container');
            for (let i = 0; i < 20; i++) {
                const petal = document.createElement('div');
                petal.className = 'sakura';
                const size = Math.random() * 8 + 5;
                petal.style.width = size + 'px';
                petal.style.height = size + 'px';
                petal.style.left = Math.random() * 100 + 'vw';
                petal.style.animationDuration = (Math.random() * 6 + 4) + 's';
                petal.style.animationDelay = Math.random() * 5 + 's';
                container.appendChild(petal);
            }
        }
        window.onload = createSakura;
    </script>
</body>
</html>