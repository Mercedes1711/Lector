<?php
session_start();
require __DIR__ . "/../src/conexion_bd.php";

// Verificar sesión
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Validar que venga el ID del manga
if (empty($_GET['manga']) || !is_numeric($_GET['manga'])) {
    die("Manga inválido. Ve a <a href='../public/index.php'>inicio</a>");
}
$manga_id = (int)$_GET['manga'];

// Verificar que el manga pertenece al usuario logueado
$stmt = $conn->prepare("SELECT titulo, es_original FROM mangas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$manga_id, $usuario_id]);
$manga = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$manga) {
    die("No tienes permiso para ver este manga.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capítulos de <?= htmlspecialchars($manga['titulo']); ?> - Manga_verso</title>
    <!-- Carga de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
            margin: 0;
            min-height: 100vh;
            color: white;
        }

        .manga-font {
            font-family: 'Bangers', cursive, sans-serif;
            letter-spacing: 0.05em;
        }

        /* Pétalos de Sakura */
        .sakura {
            position: absolute;
            background: #f472b6;
            border-radius: 100% 0% 100% 0%;
            opacity: 0.7;
            pointer-events: none;
            z-index: 1;
            animation: fall linear infinite;
        }

        @keyframes fall {
            0% { transform: translateY(-10vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(110vh) rotate(360deg); opacity: 0; }
        }

        /* Líneas de acción laterales */
        .speed-lines {
            position: fixed;
            top: 0;
            bottom: 0;
            width: 20%;
            background: repeating-linear-gradient(
                90deg,
                rgba(59, 130, 246, 0.05) 0px,
                rgba(59, 130, 246, 0.05) 1px,
                transparent 1px,
                transparent 30px
            );
            pointer-events: none;
            z-index: 0;
        }

        /* Header estilo manga */
        .manga-header {
            background: white;
            border-bottom: 4px solid #000;
            box-shadow: 0 8px 0px rgba(0, 0, 0, 0.2);
        }

        .user-badge {
            background: #0f172a;
            border: 2px solid #000;
            box-shadow: 3px 3px 0px #f472b6;
            transition: all 0.1s ease;
        }

        .user-badge:hover {
            box-shadow: 5px 5px 0px #f472b6;
            transform: translate(-1px, -1px);
        }

        .logout-btn {
            background: #dc2626;
            border: 2px solid #000;
            box-shadow: 3px 3px 0px #000;
            transition: all 0.1s ease;
        }

        .logout-btn:hover {
            background: #991b1b;
            box-shadow: 5px 5px 0px #000;
            transform: translate(-1px, -1px);
        }

        /* Tarjeta manga */
        .manga-card {
            background: white;
            border: 4px solid #000;
            box-shadow: 6px 6px 0px #000;
            transition: all 0.2s ease;
        }

        .manga-card:hover {
            transform: translate(2px, 2px);
            box-shadow: 4px 4px 0px #000;
        }

        /* Botón manga */
        .btn-manga {
            background: #0f172a;
            border: 2px solid #000;
            box-shadow: 4px 4px 0px #000;
            transition: all 0.1s ease;
            font-weight: 900;
            text-transform: uppercase;
        }

        .btn-manga:hover {
            background: #1e293b;
        }

        .btn-manga:active {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0px #000;
        }

        .btn-primary {
            background: #3b82f6;
            border: 2px solid #000;
            box-shadow: 4px 4px 0px #000;
            transition: all 0.1s ease;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-primary:active {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0px #000;
        }

        .btn-danger {
            background: #dc2626;
            border: 2px solid #000;
            box-shadow: 3px 3px 0px #000;
            transition: all 0.1s ease;
        }

        .btn-danger:hover {
            background: #991b1b;
        }

        .btn-danger:active {
            transform: translate(1px, 1px);
            box-shadow: 2px 2px 0px #000;
        }

        /* Mensaje de error */
        .error-box {
            background: #fee2e2;
            border: 3px solid #dc2626;
            color: #991b1b;
            box-shadow: 4px 4px 0px #dc2626;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        /* Input estilo manga */
        .manga-input {
            border: 2px solid #000;
            transition: all 0.2s ease;
        }

        .manga-input:focus {
            outline: none;
            border-color: #f472b6;
            box-shadow: 3px 3px 0px #f472b6;
        }

        /* Capítulo item */
        .chapter-item {
            background: white;
            border: 3px solid #000;
            box-shadow: 4px 4px 0px #000;
            transition: all 0.2s ease;
        }

        .chapter-item:hover {
            transform: translateX(3px);
            box-shadow: 2px 2px 0px #000;
        }
    </style>
</head>
<body>

    <!-- Decoración Lateral -->
    <div class="speed-lines left-0"></div>
    <div class="speed-lines right-0 rotate-180"></div>

    <!-- Contenedor de Sakura -->
    <div id="sakura-container" class="fixed inset-0 z-1 pointer-events-none"></div>

    <!-- Header -->
    <header class="manga-header relative z-40">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div>
                    <h1 class="manga-font text-4xl md:text-5xl italic transform -rotate-1">
                        MANGA<span class="text-blue-500">_</span>VERSO
                    </h1>
                    <p class="text-[9px] font-black text-slate-600 uppercase tracking-[0.3em] mt-1">Tu Portal de Manga</p>
                </div>

                <!-- Usuario y Logout -->
                <div class="flex items-center gap-3">
                    <a href="perfil.php" class="user-badge px-4 py-2 text-white font-black text-sm uppercase">
                        👤 <?= htmlspecialchars($_SESSION['usuario']); ?>
                    </a>
                    <a href="../public/logout.php" class="logout-btn px-4 py-2 text-white font-black text-sm uppercase">
                        ✕ SALIR
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-10 container mx-auto px-6 py-12 min-h-[calc(100vh-140px)]">
        
        <!-- Título del Manga -->
        <div class="text-center mb-12">
            <h2 class="manga-font text-5xl md:text-6xl text-white mb-4 italic transform -rotate-1 drop-shadow-[4px_4px_0px_#f472b6]">
                <?= strtoupper(htmlspecialchars($manga['titulo'])); ?>
            </h2>
            <div class="bg-purple-600 text-white font-bold text-[10px] inline-block px-4 py-2 tracking-[0.3em] uppercase border-2 border-black shadow-[4px_4px_0px_#000]">
                章 • CAPÍTULOS
            </div>

            <?php if ($manga['es_original']): ?>
                <div class="mt-6">
                    <a href="gestionar_compartir.php?manga=<?= $manga_id; ?>" class="group relative inline-block transition-transform hover:scale-105">
                        <div class="absolute inset-0 bg-green-500 translate-x-1 translate-y-1"></div>
                        <div class="relative bg-white border-2 border-black px-8 py-3 text-black font-black text-sm uppercase italic flex items-center gap-2">
                            <span>📢</span> GESTIONAR COMPARTICIÓN
                        </div>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mensaje de Error -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-box p-4 mb-8 font-bold">
                ⚠ <?= htmlspecialchars($_SESSION['error']); ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Formulario para subir capítulo -->
        <div class="manga-card p-8 mb-8 max-w-3xl mx-auto">
            <h3 class="manga-font text-3xl text-slate-900 mb-6 italic border-l-4 border-purple-500 pl-3">
                SUBIR NUEVO CAPÍTULO
            </h3>
            
            <form action="subir_capitulo.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="manga_id" value="<?= $manga_id; ?>">

                <div>
                    <label class="block text-[10px] font-black text-purple-600 uppercase mb-2 tracking-widest">Título del capítulo:</label>
                    <input type="text" 
                           name="titulo" 
                           required 
                           class="manga-input w-full p-3 text-slate-900 font-semibold"
                           placeholder="Ej: Capítulo 1 - El Despertar">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-purple-600 uppercase mb-2 tracking-widest">Archivo del capítulo (PDF o ZIP):</label>
                    <input type="file" 
                           name="archivo" 
                           accept=".pdf,.zip" 
                           required 
                           class="manga-input w-full p-3 text-slate-900 font-semibold">
                </div>

                <button type="submit" class="btn-primary w-full py-3 text-white font-black text-sm tracking-wider">
                    ⬆️ SUBIR CAPÍTULO
                </button>
            </form>
        </div>

        <!-- Buscador y Lista de Capítulos -->
        <div class="max-w-5xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="manga-font text-4xl text-white italic">CAPÍTULOS EXISTENTES</h3>
            </div>

            <!-- Formulario de búsqueda y filtros -->
            <form method="GET" action="" class="mb-8 bg-slate-900/50 p-6 border-l-4 border-purple-500">
                <input type="hidden" name="manga" value="<?= $manga_id; ?>">
                <div class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-[10px] font-black text-purple-600 uppercase mb-2 tracking-widest">Buscar por título:</label>
                        <input type="text" 
                               name="buscar" 
                               placeholder="Ej: El despertar..." 
                               value="<?= htmlspecialchars($_GET['buscar'] ?? ''); ?>" 
                               class="manga-input w-full p-3 text-slate-900 font-semibold">
                    </div>
                    
                    <div class="min-w-[150px]">
                        <label class="block text-[10px] font-black text-purple-600 uppercase mb-2 tracking-widest">Ordenar por:</label>
                        <select name="orden" class="manga-input w-full p-3 text-slate-900 font-semibold bg-white cursor-pointer">
                            <option value="ASC" <?= (isset($_GET['orden']) && $_GET['orden'] == 'ASC') ? 'selected' : ''; ?>>📅 ANTIGUOS PRIMERO</option>
                            <option value="DESC" <?= (isset($_GET['orden']) && $_GET['orden'] == 'DESC') ? 'selected' : ''; ?>>🆕 RECIENTES PRIMERO</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary px-8 py-3 text-white font-black text-sm">
                        FILTRAR
                    </button>
                    
                    <?php if (!empty($_GET['buscar']) || (isset($_GET['orden']) && $_GET['orden'] !== 'ASC')): ?>
                        <a href="capitulos.php?manga=<?= $manga_id; ?>" class="btn-manga px-6 py-3 text-white font-black text-xs">
                            ✕ LIMPIAR
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Lista de Capítulos -->
            <div class="space-y-4">
                <?php
                // --- CONFIGURACIÓN DE PAGINACIÓN ---
                $limite = 5;
                $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                if ($pagina_actual < 1) $pagina_actual = 1;
                $offset = ($pagina_actual - 1) * $limite;

                // --- DETERMINAR FILTROS ---
                $where_base = "WHERE manga_id = ?";
                $params_base = [$manga_id];

                if (!empty($_GET['buscar'])) {
                    $where_base .= " AND titulo LIKE ?";
                    $params_base[] = "%" . trim($_GET['buscar']) . "%";
                }

                // --- DETERMINAR ORDENAMIENTO ---
                $orden = (isset($_GET['orden']) && $_GET['orden'] == 'DESC') ? 'DESC' : 'ASC';

                // --- CONTAR TOTAL PARA PAGINACIÓN ---
                $stmt_count = $conn->prepare("SELECT COUNT(*) FROM capitulos $where_base");
                $stmt_count->execute($params_base);
                $total_capitulos = $stmt_count->fetchColumn();
                $total_paginas = ceil($total_capitulos / $limite);

                // --- OBTENER CAPÍTULOS PAGINADOS ---
                $query = "SELECT * FROM capitulos $where_base ORDER BY fecha_subida $orden LIMIT $limite OFFSET $offset";

                $stmt = $conn->prepare($query);
                $stmt->execute($params_base);
                $capitulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($capitulos) {
                    foreach ($capitulos as $cap) {
                        echo "
                        <div class='chapter-item p-4 flex items-center justify-between'>
                            <div class='flex-1'>
                                <h4 class='font-black text-slate-900 text-lg'>" . htmlspecialchars($cap['titulo']) . "</h4>
                                <p class='text-[10px] text-slate-500 font-semibold uppercase tracking-wider mt-1'>
                                    Subido: " . date('d/m/Y', strtotime($cap['fecha_subida'])) . "
                                </p>
                            </div>
                            <div class='flex gap-3'>
                                <a href='leer_capitulo.php?capitulo=" . $cap['id'] . "' 
                                   class='btn-primary px-4 py-2 text-white font-black text-xs inline-block'>
                                    📖 LEER
                                </a>
                                <form action='eliminar_capitulo.php' method='POST' style='display:inline;' 
                                      onsubmit=\"return confirm('¿Eliminar este capítulo?');\">
                                    <input type='hidden' name='capitulo_id' value='" . $cap['id'] . "'>
                                    <button type='submit' class='btn-danger px-4 py-2 text-white font-black text-xs'>
                                        🗑️ ELIMINAR
                                    </button>
                                </form>
                            </div>
                        </div>";
                    }
                } else {
                    $mensaje = !empty($_GET['buscar']) 
                        ? "No se encontraron capítulos con ese título." 
                        : "No hay capítulos subidos todavía.";
                    echo "<div class='manga-card p-8 text-center'>
                            <p class='text-slate-600 font-bold text-lg'>" . $mensaje . "</p>
                          </div>";
                }
                ?>
            </div>

            <!-- Controles de Paginación -->
            <?php if ($total_paginas > 1): ?>
                <div class="mt-10 flex flex-wrap justify-center gap-2">
                    <?php 
                    // Mantener parámetros en los enlaces de paginación
                    $url_base = "capitulos.php?manga=$manga_id";
                    if (!empty($_GET['buscar'])) {
                        $url_base .= "&buscar=" . urlencode($_GET['buscar']);
                    }
                    if (isset($_GET['orden'])) {
                        $url_base .= "&orden=" . urlencode($_GET['orden']);
                    }
                    ?>

                    <?php if ($pagina_actual > 1): ?>
                        <a href="<?= $url_base ?>&pagina=<?= $pagina_actual - 1 ?>" 
                           class="btn-manga px-4 py-2 text-white text-xs">
                           « ANTERIOR
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <a href="<?= $url_base ?>&pagina=<?= $i ?>" 
                           class="px-4 py-2 border-2 border-black font-black text-xs transition-all <?= ($i == $pagina_actual) ? 'bg-purple-600 text-white shadow-[3px_3px_0px_#000]' : 'bg-white text-slate-900 hover:bg-slate-100 shadow-[2px_2px_0px_#000]' ?>">
                           <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pagina_actual < $total_paginas): ?>
                        <a href="<?= $url_base ?>&pagina=<?= $pagina_actual + 1 ?>" 
                           class="btn-manga px-4 py-2 text-white text-xs">
                           SIGUIENTE »
                        </a>
                    <?php endif; ?>
                </div>
                <p class="text-center text-[10px] text-purple-400 font-black uppercase tracking-widest mt-4">
                    PÁGINA <?= $pagina_actual ?> DE <?= $total_paginas ?> (TOTAL: <?= $total_capitulos ?> CAPÍTULOS)
                </p>
            <?php endif; ?>

            <!-- Botón Volver -->
            <div class="mt-12 text-center">
                <a href="../public/index.php" class="btn-manga px-8 py-3 text-white font-black text-sm inline-block">
                    ⬅ VOLVER AL INICIO
                </a>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="relative z-10 text-center py-6 border-t-4 border-white/10">
        <p class="text-[9px] text-purple-400/50 font-bold uppercase tracking-[0.4em]">
            MANGA_VERSO SELECT • VOL. 01 • 2026 • © ALL RIGHTS RESERVED
        </p>
    </footer>

    <script>
        function createSakura() {
            const container = document.getElementById('sakura-container');
            if (!container) return;
            
            const count = 20;
            for (let i = 0; i < count; i++) {
                const petal = document.createElement('div');
                petal.className = 'sakura';
                
                const size = Math.random() * 8 + 6;
                const left = Math.random() * 100;
                const duration = Math.random() * 8 + 5;
                const delay = Math.random() * 5;
                
                petal.style.width = `${size}px`;
                petal.style.height = `${size}px`;
                petal.style.left = `${left}vw`;
                petal.style.animationDuration = `${duration}s`;
                petal.style.animationDelay = `${delay}s`;
                
                container.appendChild(petal);
            }
        }
        window.onload = createSakura;
    </script>
</body>
</html>
