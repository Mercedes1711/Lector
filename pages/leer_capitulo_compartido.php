<?php
session_start();
require __DIR__ . "/../src/conexion_bd.php";

$is_logged_in = !empty($_SESSION['usuario']) && !empty($_SESSION['user_id']);
$usuario_id = $is_logged_in ? $_SESSION['user_id'] : 0;
$nombre_usuario = $is_logged_in ? $_SESSION['usuario'] : 'INVITADO';

// Validar que venga el ID del capítulo
if (empty($_GET['capitulo']) || !is_numeric($_GET['capitulo'])) {
    die("Capítulo inválido. Ve a <a href='../public/index.php'>inicio</a>");
}
$capitulo_id = (int)$_GET['capitulo'];

// Obtener el capítulo y verificar que está en un manga compartido
$stmt = $conn->prepare("
    SELECT c.*, m.titulo AS manga_titulo, m.usuario_id as owner_id, u.usuario as autor
    FROM capitulos c
    JOIN mangas m ON c.manga_id = m.id
    JOIN usuarios u ON m.usuario_id = u.id
    JOIN mangas_compartidos mc ON m.id = mc.manga_id
    WHERE c.id = ? AND mc.activo = 1 " . ($is_logged_in ? "AND m.usuario_id != ?" : "") . "
");
$exec_params = $is_logged_in ? [$capitulo_id, $usuario_id] : [$capitulo_id];
$stmt->execute($exec_params);
$capitulo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$capitulo) {
    die("Este capítulo no está disponible o no tienes permiso para verlo. Ve a <a href='mangas_compartidos.php'>mangas compartidos</a>");
}

// Obtener el siguiente capítulo del mismo manga
$stmt_next = $conn->prepare("
    SELECT c.id
    FROM capitulos c
    WHERE c.manga_id = ? AND c.fecha_subida > ?
    ORDER BY c.fecha_subida ASC
    LIMIT 1
");
$stmt_next->execute([$capitulo['manga_id'], $capitulo['fecha_subida']]);
$next_capitulo = $stmt_next->fetch(PDO::FETCH_ASSOC);

// Obtener el capítulo anterior
$stmt_prev = $conn->prepare("
    SELECT c.id
    FROM capitulos c
    WHERE c.manga_id = ? AND c.fecha_subida < ?
    ORDER BY c.fecha_subida DESC
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
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;900&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0f172a; color: white; min-height: 100vh; }
        .manga-font { font-family: 'Bangers', cursive; letter-spacing: 0.05em; }
        
        /* Estilos del Lector */
        .reader-container { background: #111827; border-top: 4px solid #3b82f6; border-bottom: 4px solid #3b82f6; }
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
        .manga-page:hover { transform: scale(1.01); }
        
        .btn-manga {
            background: #1e293b; border: 2px solid #000; box-shadow: 4px 4px 0px #000;
            transition: all 0.1s ease; font-weight: 900; text-transform: uppercase;
        }
        .btn-manga:disabled { opacity: 0.5; pointer-events: none; }
        .btn-primary { background: #3b82f6; border: 2px solid #000; box-shadow: 4px 4px 0px #000; font-weight: 900; }
        .manga-select { background: #1e293b; border: 2px solid #3b82f6; color: white; font-weight: 900; }
        
        .loading-spinner {
            width: 50px; height: 50px; border: 5px solid #3b82f6; border-top-color: transparent; border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .manga-header { background: white; border-bottom: 4px solid #000; }
    </style>
</head>
<body>

    <header class="manga-header relative z-40 p-4">
        <div class="container mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
            <a href="../public/index.php">
                <h1 class="manga-font text-3xl md:text-4xl italic text-slate-900 leading-none">
                    MANGA<span class="text-blue-500">_</span>VERSO
                </h1>
            </a>
            <div class="text-center">
                <h2 class="manga-font text-2xl text-blue-600 line-clamp-1"><?= htmlspecialchars($capitulo['manga_titulo']); ?></h2>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-none mt-1">Capítulo: <?= htmlspecialchars($capitulo['titulo']); ?></p>
            </div>
            <div class="flex items-center gap-4">
                <?php if ($is_logged_in): ?>
                    <span class="manga-font text-blue-600 hidden md:block"><?= htmlspecialchars($nombre_usuario); ?></span>
                <?php else: ?>
                    <a href="../public/login.php" class="bg-pink-600 text-white font-black text-[10px] px-4 py-2 border-2 border-black">LOGIN</a>
                <?php endif; ?>
                <a href="leer_manga_compartido.php?manga=<?= $capitulo['manga_id']; ?>" class="bg-black text-white px-4 py-2 font-black text-xs uppercase shadow-[3px_3px_0px_#3b82f6] hover:shadow-none transition-all italic">Ficha</a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        
        <!-- Controles Superiores -->
        <div id="reader-top" class="max-w-4xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6 mb-8 bg-slate-900/80 p-6 border-b-4 border-blue-500">
            <div class="flex items-center gap-4">
                <label class="text-[10px] font-black text-pink-500 uppercase tracking-widest leading-none">Ver:</label>
                <select id="batchSize" onchange="changeBatchSize(this.value)" class="manga-select py-1 px-4 text-xs">
                    <option value="5">5 páginas</option>
                    <option value="10">10 páginas</option>
                </select>
            </div>

            <div id="pageControls" class="flex items-center gap-4 hidden">
                <button onclick="prevBatch()" class="btn-manga py-2 px-6 text-xs text-white">« Ant.</button>
                <div class="text-center">
                    <p id="pageRange" class="font-black text-[10px] text-blue-400 uppercase tracking-widest"></p>
                </div>
                <button onclick="nextBatch()" class="btn-manga py-2 px-6 text-xs text-white">Sig. »</button>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-[10px] font-black text-pink-500 uppercase tracking-widest leading-none">Salto:</label>
                <select onchange="window.location.href='leer_capitulo_compartido.php?capitulo=' + this.value" class="manga-select py-1 px-4 text-xs">
                    <?php foreach ($all_capitulos as $chap): ?>
                        <option value="<?= $chap['id']; ?>" <?= $chap['id'] == $capitulo_id ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($chap['titulo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Lector Dinámico -->
        <div id="reader" class="reader-container mb-12 max-w-5xl mx-auto py-10">
            <div id="loader" class="flex flex-col items-center justify-center py-20">
                <div class="loading-spinner mb-4"></div>
                <p class="manga-font text-2xl text-blue-400 italic uppercase">Cargando páginas...</p>
            </div>
            
            <div id="pages-container"></div>

            <div id="bottomControls" class="flex flex-col items-center gap-4 mt-12 pt-8 border-t-2 border-slate-800 hidden">
                <div class="flex items-center gap-6">
                    <button onclick="prevBatch()" class="btn-manga py-3 px-8 text-xs text-white block">« Anterior Lote</button>
                    <button onclick="nextBatch()" class="btn-primary py-3 px-8 text-xs text-white block">Siguiente Lote »</button>
                </div>
                <p id="bottomPageRange" class="font-black text-pink-500 uppercase tracking-widest mt-2" style="font-size: 10px;"></p>
            </div>
        </div>

        <!-- Navegación entre capítulos -->
        <div class="max-w-4xl mx-auto flex justify-between items-center mt-20 px-6">
            <?php if ($prev_capitulo): ?>
                <a href="leer_capitulo_compartido.php?capitulo=<?= $prev_capitulo['id']; ?>" class="btn-manga text-white py-4 px-8 text-sm group">
                    <span class="group-hover:text-blue-400 transition-colors">« ANTERIOR</span>
                </a>
            <?php else: ?>
                <div></div>
            <?php endif; ?>

            <a href="leer_manga_compartido.php?manga=<?= $capitulo['manga_id']; ?>" class="manga-font text-2xl text-slate-400 hover:text-white transition-all uppercase italic">
                Cerrar Lector
            </a>

            <?php if ($next_capitulo): ?>
                <a href="leer_capitulo_compartido.php?capitulo=<?= $next_capitulo['id']; ?>" class="btn-primary text-white py-4 px-8 text-sm group text-center block">
                    <span class="group-hover:italic transition-all">SIGUIENTE »</span>
                </a>
            <?php else: ?>
                <div class="bg-emerald-600/20 text-emerald-500 font-black text-[10px] px-6 py-2 border-2 border-emerald-500 uppercase italic">
                    ¡ESTÁS AL DÍA!
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const pdfUrl = '../<?= htmlspecialchars($capitulo['archivo']); ?>';
        let pdfDoc = null;
        let totalPages = 0;
        let currentStartPage = 1;
        let currentBatchSize = 5;

        const container = document.getElementById('pages-container');
        const loader = document.getElementById('loader');
        const rangeText = document.getElementById('pageRange');
        const bottomRangeText = document.getElementById('bottomPageRange');
        const controls = document.getElementById('pageControls');
        const bottomControls = document.getElementById('bottomControls');

        function id(name) { return document.getElementById(name); }

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
                loader.innerHTML = '<p class="text-red-500 font-bold">Error al cargar el archivo de este manga.</p>';
            }
        }

        async function renderBatch() {
            loader.classList.remove('hidden');
            container.innerHTML = '';
            
            const endPage = Math.min(currentStartPage + currentBatchSize - 1, totalPages);
            const rangeMsg = `PÁGINAS ${currentStartPage} - ${endPage} DE ${totalPages}`;
            rangeText.textContent = rangeMsg;
            bottomRangeText.textContent = rangeMsg;

            for (let i = currentStartPage; i <= endPage; i++) {
                await renderPage(i);
            }
            
            loader.classList.add('hidden');
            const targetTop = id('reader-top').offsetTop - 30;
            window.scrollTo({ top: targetTop, behavior: 'auto' });
        }

        async function renderPage(num) {
            const page = await pdfDoc.getPage(num);
            const scale = 2.0;
            const viewport = page.getViewport({ scale });

            const canvas = document.createElement('canvas');
            canvas.className = 'manga-page';
            const context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            await page.render({ canvasContext: context, viewport }).promise;
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
            currentStartPage = 1; // Volver al inicio al cambiar tamaño
            renderBatch();
        }

        loadPdf();
    </script>
</body>
</html>
