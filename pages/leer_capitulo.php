<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require __DIR__ . "/../src/conexion_bd.php";

// Verificar sesión
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Validar que venga el ID del capítulo
if (empty($_GET['capitulo']) || !is_numeric($_GET['capitulo'])) {
    die("Capítulo inválido. Ve a <a href='../public/index.php'>inicio</a>");
}
$capitulo_id = (int)$_GET['capitulo'];

// Obtener el capítulo y verificar que pertenece al usuario
$stmt = $conn->prepare("
    SELECT c.*, m.titulo AS manga_titulo
    FROM capitulos c
    JOIN mangas m ON c.manga_id = m.id
    WHERE c.id = ? AND m.usuario_id = ?
");
$stmt->execute([$capitulo_id, $usuario_id]);
$capitulo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$capitulo) {
    die("No tienes permiso para ver este capítulo.");
}

// Obtener el siguiente capítulo
$stmt_next = $conn->prepare("
    SELECT id
    FROM capitulos
    WHERE manga_id = ? AND fecha_subida > ?
    ORDER BY fecha_subida ASC
    LIMIT 1
");
$stmt_next->execute([$capitulo['manga_id'], $capitulo['fecha_subida']]);
$next_capitulo = $stmt_next->fetch(PDO::FETCH_ASSOC);

// Obtener el capítulo anterior
$stmt_prev = $conn->prepare("
    SELECT id
    FROM capitulos
    WHERE manga_id = ? AND fecha_subida < ?
    ORDER BY fecha_subida DESC
    LIMIT 1
");
$stmt_prev->execute([$capitulo['manga_id'], $capitulo['fecha_subida']]);
$prev_capitulo = $stmt_prev->fetch(PDO::FETCH_ASSOC);

// Obtener todos los capítulos para el selector
$stmt_all = $conn->prepare("SELECT id, titulo FROM capitulos WHERE manga_id = ? ORDER BY fecha_subida ASC");
$stmt_all->execute([$capitulo['manga_id']]);
$all_capitulos = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leyendo: <?= htmlspecialchars($capitulo['titulo']); ?> - Manga_verso</title>
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

        /* Botones manga */
        .btn-manga {
            background: #0f172a;
            border: 2px solid #000;
            box-shadow: 4px 4px 0px #000;
            transition: all 0.1s ease;
            font-weight: 900;
            text-transform: uppercase;
            display: inline-block;
            padding: 12px 24px;
            color: white;
            text-decoration: none;
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
            font-weight: 900;
            text-transform: uppercase;
            display: inline-block;
            padding: 12px 24px;
            color: white;
            text-decoration: none;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-primary:active {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0px #000;
        }

        .btn-secondary {
            background: #6b7280;
            border: 2px solid #000;
            box-shadow: 4px 4px 0px #000;
            transition: all 0.1s ease;
            font-weight: 900;
            text-transform: uppercase;
            display: inline-block;
            padding: 12px 24px;
            color: white;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-secondary:active {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0px #000;
        }

        /* Select estilo manga */
        .manga-select {
            background: white;
            border: 2px solid #000;
            box-shadow: 4px 4px 0px #000;
            padding: 12px 24px;
            font-weight: 900;
            text-transform: uppercase;
            color: #000;
            outline: none;
        }

        .manga-select:focus {
            border-color: #f472b6;
            box-shadow: 4px 4px 0px #f472b6;
        }

        /* Contenedor del PDF */
        .pdf-container {
            background: white;
            border: 4px solid #000;
            box-shadow: 8px 8px 0px #000;
            min-height: 80vh;
        }

        .pdf-container embed {
            width: 100%;
            height: 80vh;
            border: none;
        }
    </style>
</head>
<body>

    <!-- Contenedor de Sakura -->
    <div id="sakura-container" class="fixed inset-0 z-1 pointer-events-none"></div>

    <!-- Header -->
    <header class="manga-header relative z-40">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div>
                    <h1 class="manga-font text-4xl md:text-5xl italic transform -rotate-1 text-slate-900">
                        MANGA<span class="text-pink-500">_</span><span class="text-blue-500">VERSO</span>
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
    <main class="relative z-10 container mx-auto px-6 py-12">
        
        <!-- Título del Capítulo -->
        <div class="text-center mb-8">
            <div class="bg-pink-600 text-white font-bold text-[10px] inline-block px-4 py-2 tracking-[0.3em] uppercase border-2 border-black shadow-[4px_4px_0px_#000] mb-4">
                📖 MODO LECTURA
            </div>
            <h2 class="manga-font text-4xl md:text-5xl text-white mb-2 italic transform -rotate-1 drop-shadow-[4px_4px_0px_#f472b6]">
                <?= strtoupper(htmlspecialchars($capitulo['titulo'])); ?>
            </h2>
            <p class="text-slate-300 font-semibold text-lg">
                De: <?= htmlspecialchars($capitulo['manga_titulo']); ?>
            </p>
        </div>

        <!-- Navegación de Capítulos (Arriba) -->
        <div class="flex flex-wrap items-center justify-center gap-4 mb-8">
            <?php if ($prev_capitulo): ?>
                <a href="leer_capitulo.php?capitulo=<?= $prev_capitulo['id']; ?>" class="btn-secondary text-xs md:text-sm">
                    ⬅️ Capítulo Anterior
                </a>
            <?php else: ?>
                <span class="btn-secondary text-xs md:text-sm opacity-50 cursor-not-allowed">
                    ⬅️ Capítulo Anterior
                </span>
            <?php endif; ?>

            <select onchange="location.href='leer_capitulo.php?capitulo='+this.value;" class="manga-select text-xs md:text-sm">
                <?php foreach ($all_capitulos as $chap): ?>
                    <option value="<?= $chap['id']; ?>" <?= $chap['id'] == $capitulo_id ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($chap['titulo']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if ($next_capitulo): ?>
                <a href="leer_capitulo.php?capitulo=<?= $next_capitulo['id']; ?>" class="btn-primary text-xs md:text-sm">
                    Siguiente Capítulo ➡️
                </a>
            <?php else: ?>
                <span class="btn-primary text-xs md:text-sm opacity-50 cursor-not-allowed">
                    Siguiente Capítulo ➡️
                </span>
            <?php endif; ?>
        </div>

        <!-- Visor de PDF -->
        <div class="pdf-container mb-8">
            <embed src="../<?= htmlspecialchars($capitulo['archivo']); ?>" type="application/pdf">
        </div>

        <!-- Navegación de Capítulos (Abajo) -->
        <div class="flex flex-wrap items-center justify-center gap-4 mb-8">
            <?php if ($prev_capitulo): ?>
                <a href="leer_capitulo.php?capitulo=<?= $prev_capitulo['id']; ?>" class="btn-secondary text-xs md:text-sm">
                    ⬅️ Capítulo Anterior
                </a>
            <?php else: ?>
                <span class="btn-secondary text-xs md:text-sm opacity-50 cursor-not-allowed">
                    ⬅️ Capítulo Anterior
                </span>
            <?php endif; ?>

            <?php if ($next_capitulo): ?>
                <a href="leer_capitulo.php?capitulo=<?= $next_capitulo['id']; ?>" class="btn-primary text-xs md:text-sm">
                    Siguiente Capítulo ➡️
                </a>
            <?php else: ?>
                <span class="btn-primary text-xs md:text-sm opacity-50 cursor-not-allowed">
                    Siguiente Capítulo ➡️
                </span>
            <?php endif; ?>
        </div>

        <!-- Botón Volver -->
        <div class="text-center mt-12 flex justify-center items-center gap-4">
            <div class="h-[2px] w-12 bg-slate-700"></div>
            <a href="capitulos.php?manga=<?= $capitulo['manga_id']; ?>" class="font-black text-[10px] text-slate-500 hover:text-pink-400 transition-colors uppercase tracking-[0.4em]">
                ⬅ Volver a Capítulos
            </a>
            <div class="h-[2px] w-12 bg-slate-700"></div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="relative z-10 text-center py-6 border-t-4 border-white/10">
        <p class="text-[9px] text-pink-400/50 font-bold uppercase tracking-[0.4em]">
            MANGA_VERSO SELECT • VOL. 01 • 2026 • © ALL RIGHTS RESERVED
        </p>
    </footer>

    <script>
        function createSakura() {
            const container = document.getElementById('sakura-container');
            if (!container) return;
            
            const count = 15;
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