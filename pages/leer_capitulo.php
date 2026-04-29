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

        /* Contenedor del Lector */
        .reader-container {
            background: #111827;
            border: 4px solid #000;
            box-shadow: 8px 8px 0px #000;
            padding: 20px;
        }

        .manga-page {
            max-width: 800px;
            width: 100%;
            height: auto;
            margin: 0 auto 30px auto;
            display: block;
            border: 4px solid #000;
            box-shadow: 0 10px 25px rgba(0,0,0,0.7);
            background: #000;
            transition: transform 0.3s ease;
        }

        .manga-page:hover {
            transform: scale(1.01);
        }

        .loading-spinner {
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-left-color: #f472b6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <!-- pdf.js desde CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
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

        <!-- Selector de Modo de Vista y Navegación Interna -->
        <div id="reader-top" class="max-w-4xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6 mb-8 bg-slate-900/80 p-6 border-b-4 border-blue-500">
            <div class="flex items-center gap-4">
                <label class="text-[10px] font-black text-pink-500 uppercase tracking-widest leading-none">Ver:</label>
                <select id="batchSize" onchange="changeBatchSize(this.value)" class="manga-select py-1 px-4 text-xs">
                    <option value="5">5 PÁGINAS</option>
                    <option value="10">10 PÁGINAS</option>
                </select>
            </div>
            
            <div id="pageControls" class="flex items-center gap-4 hidden">
                <button onclick="prevBatch()" class="btn-manga py-2 px-4 text-[10px]">« Anterior Lote</button>
                <div class="text-center">
                    <span id="pageRange" class="font-black text-xs text-white">Cargando...</span>
                </div>
                <button onclick="nextBatch()" class="btn-manga py-2 px-4 text-[10px]">Siguiente Lote »</button>
            </div>
        </div>

        <!-- Lector Dinámico -->
        <div id="reader" class="reader-container mb-12 max-w-5xl mx-auto">
            <div id="loader" class="flex flex-col items-center justify-center py-20">
                <div class="loading-spinner mb-4"></div>
                <p class="manga-font text-2xl text-blue-400 italic">CARGANDO RECURSOS NINJA...</p>
            </div>
            
            <div id="pages-container"></div>

            <!-- Navegación Inferior -->
            <div id="bottomControls" class="flex flex-col items-center gap-4 mt-12 pt-8 border-t-2 border-slate-800 hidden">
                <div class="flex items-center gap-6">
                    <button onclick="prevBatch()" class="btn-manga py-3 px-8 text-xs">« Anterior Lote</button>
                    <button onclick="nextBatch()" class="btn-primary py-3 px-8 text-xs">Siguiente Lote »</button>
                </div>
                <p id="bottomPageRange" class="font-black text-[10px] text-pink-500 uppercase tracking-widest mt-2"></p>
            </div>
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
        // CONFIGURACIÓN DE PDF.JS
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const pdfUrl = '../<?= htmlspecialchars($capitulo['archivo']); ?>';
        let pdfDoc = null;
        let currentStartPage = 1;
        let currentBatchSize = 5;
        let totalPages = 0;

        const container = document.getElementById('pages-container');
        const loader = document.getElementById('loader');
        const rangeText = id('pageRange');
        const bottomRangeText = id('bottomPageRange');
        const controls = id('pageControls');
        const bottomControls = id('bottomControls');

        function id(name) { return document.getElementById(name); }

        // Cargar documento
        async function loadPdf() {
            try {
                const loadingTask = pdfjsLib.getDocument(pdfUrl);
                pdfDoc = await loadingTask.promise;
                totalPages = pdfDoc.numPages;
                controls.classList.remove('hidden');
                bottomControls.classList.remove('hidden');
                renderBatch();
            } catch (error) {
                console.error("Error cargando PDF:", error);
                loader.innerHTML = '<p class="text-red-500 font-bold">Error al cargar el archivo. ¿Es un PDF válido?</p>';
            }
        }

        // Renderizar un lote de páginas
        async function renderBatch() {
            loader.classList.remove('hidden');
            container.innerHTML = '';
            
            const endPage = Math.min(currentStartPage + currentBatchSize - 1, totalPages);
            const rangeMsg = `MOSTRANDO PÁGINAS ${currentStartPage} - ${endPage} DE ${totalPages}`;
            rangeText.textContent = rangeMsg;
            bottomRangeText.textContent = rangeMsg;

            for (let i = currentStartPage; i <= endPage; i++) {
                await renderPage(i);
            }
            
            loader.classList.add('hidden');
            // Salto directo al control de navegación para empezar a leer inmediatamente
            const targetTop = id('reader-top').offsetTop - 30;
            window.scrollTo({ top: targetTop, behavior: 'auto' });
        }

        // Renderizar una página individual en un canvas
        async function renderPage(num) {
            const page = await pdfDoc.getPage(num);
            const scale = 2.0; // Alta calidad
            const viewport = page.getViewport({ scale });

            const canvas = document.createElement('canvas');
            canvas.className = 'manga-page';
            const context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderContext = {
                canvasContext: context,
                viewport: viewport
            };

            await page.render(renderContext).promise;
            container.appendChild(canvas);
        }

        function nextBatch() {
            if (currentStartPage + currentBatchSize <= totalPages) {
                currentStartPage += currentBatchSize;
                renderBatch();
            }
        }

        function prevBatch() {
            if (currentStartPage - currentBatchSize >= 1) {
                currentStartPage -= currentBatchSize;
                renderBatch();
            }
        }

        function changeBatchSize(val) {
            currentBatchSize = parseInt(val);
            currentStartPage = 1; // Reiniciar al cambiar el tamaño
            renderBatch();
        }

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

        window.onload = function() {
            createSakura();
            loadPdf();
        };
    </script>
</body>
</html>