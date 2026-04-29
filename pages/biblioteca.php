<?php
session_start();
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

require __DIR__ . "/../src/conexion_bd.php";
$usuario_id = $_SESSION['user_id'];

// --- OBTENCIÓN DE MANGAS SUBIDOS (MIS SUBIDAS) ---
$where_conditions = ["m.usuario_id = ?"];
$params = [$usuario_id];

if (!empty($_GET['categoria'])) {
    $where_conditions[] = "m.categoria_id = ?";
    $params[] = (int)$_GET['categoria'];
}

if (!empty($_GET['busqueda'])) {
    $where_conditions[] = "m.titulo LIKE ?";
    $params[] = "%" . $_GET['busqueda'] . "%";
}

$orden = $_GET['orden'] ?? 'fecha_subida DESC';
$allowed_orders = ['fecha_subida DESC', 'fecha_subida ASC', 'titulo ASC', 'titulo DESC'];
if (!in_array($orden, $allowed_orders)) {
    $orden = 'fecha_subida DESC';
}

$where_clause = implode(" AND ", $where_conditions);
$stmt_subidas = $conn->prepare("
    SELECT m.*, c.nombre as categoria_nombre
    FROM mangas m
    LEFT JOIN categorias c ON m.categoria_id = c.id
    WHERE {$where_clause}
    ORDER BY {$orden}
");
$stmt_subidas->execute($params);
$mangas_subidos = $stmt_subidas->fetchAll(PDO::FETCH_ASSOC);

// --- OBTENCIÓN DE MANGAS DE MI COLECCIÓN ---
$stmt_coleccion = $conn->prepare("
    SELECT m.id, m.titulo, m.descripcion, m.portada, m.fecha_subida, m.categoria_id, m.es_original, u.usuario as autor,
           (SELECT COUNT(*) FROM capitulos WHERE manga_id = m.id) as total_capitulos, 
           bu.fecha_agregado, cat.nombre as categoria_nombre
    FROM biblioteca_usuario bu
    JOIN mangas m ON bu.manga_id = m.id
    JOIN usuarios u ON m.usuario_id = u.id
    LEFT JOIN categorias cat ON m.categoria_id = cat.id
    WHERE bu.usuario_id = ?
    ORDER BY bu.fecha_agregado DESC
");
$stmt_coleccion->execute([$usuario_id]);
$mangas_coleccion = $stmt_coleccion->fetchAll(PDO::FETCH_ASSOC);

$active_tab = $_GET['tab'] ?? 'mis-mangas';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Biblioteca - Manga_verso</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/css/manga_verso.css">

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
            color: white;
            overflow-x: hidden;
        }
        .manga-font { font-family: 'Bangers', cursive, sans-serif; letter-spacing: 0.05em; }
        #sakura-container { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
        .sakura {
            position: absolute; background: #f472b6; border-radius: 100% 0% 100% 0%;
            opacity: 0.7; animation: fall linear infinite;
        }
        @keyframes fall {
            from { transform: translateY(-10vh) rotate(0deg); opacity: 1; }
            to   { transform: translateY(110vh) rotate(360deg); opacity: 0; }
        }
        header, main, footer { position: relative; z-index: 20; }
        .manga-card { 
            background: #1e293b; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.05);
        }
        .manga-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(59,130,246,0.3); border-color: #3b82f6; }
    </style>
</head>

<body class="min-h-screen">
<div id="sakura-container"></div>

<header class="border-b-4 border-pink-500 bg-slate-900/80 backdrop-blur-md p-6 shadow-2xl">
    <div class="container mx-auto flex flex-col sm:flex-row justify-between items-center gap-6 text-center sm:text-left">
        <div>
            <h1 class="manga-font text-5xl italic">Manga_verso</h1>
            <p class="text-pink-500 font-black text-xs tracking-[0.3em]">BIBLIOTECA DEL GUERRERO</p>
        </div>
        <div class="flex flex-wrap justify-center items-center gap-4">
            <a href="blog.php" class="manga-font text-xl text-pink-500 hover:text-white transition-colors">BLOG</a>
            <span class="manga-font text-xl text-blue-400">HOLA, <?= htmlspecialchars($_SESSION['usuario']) ?></span>
            <a href="../public/logout.php" class="bg-pink-600 hover:bg-blue-600 text-white font-black text-xs px-4 py-2 skew-x-[-12deg] transition-all">
                CERRAR SESIÓN
            </a>
        </div>
    </div>
</header>

<main class="container mx-auto px-4 py-10">

    <!-- MENSAJES DE ÉXITO/ERROR -->
    <?php if (isset($_SESSION['exito'])): ?>
        <div class="bg-green-500/20 border border-green-500 text-green-400 p-4 mb-6 rounded-lg text-center font-bold">
            <?= htmlspecialchars($_SESSION['exito']); unset($_SESSION['exito']); ?>
        </div>
    <?php endif; ?>

    <!-- TABS -->
    <div class="flex justify-center gap-4 mb-12">
        <button onclick="switchTab(event,'mis-mangas', this)"
            id="btn-mis-mangas"
            class="tab-button <?= ($active_tab === 'mis-mangas') ? 'bg-pink-600 text-white' : 'bg-slate-800 text-slate-400' ?> manga-font text-2xl px-8 py-2 skew-x-[-12deg] transition-all">
            MIS SUBIDAS
        </button>
        <button onclick="switchTab(event,'biblioteca', this)"
            id="btn-biblioteca"
            class="tab-button <?= ($active_tab === 'biblioteca') ? 'bg-pink-600 text-white' : 'bg-slate-800 text-slate-400' ?> manga-font text-2xl px-8 py-2 skew-x-[-12deg] transition-all">
            MI COLECCIÓN
        </button>
    </div>

    <!-- TAB 1: MIS SUBIDAS -->
    <div id="mis-mangas" class="tab-content <?= ($active_tab === 'mis-mangas') ? 'block' : 'hidden' ?>">
        
        <!-- FILTROS (Solo para mis subidas por ahora) -->
        <div class="bg-slate-900/50 p-6 mb-10 border-l-4 border-pink-500 backdrop-blur-sm">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <input type="hidden" name="tab" value="mis-mangas">
                <div class="flex-grow min-w-[200px]">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-pink-500 mb-2">Buscar</label>
                    <input type="text" name="busqueda" value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>" placeholder="Título..." 
                           class="w-full bg-slate-800 border-2 border-slate-700 p-2 text-white outline-none focus:border-blue-500 transition-all">
                </div>
                <div class="min-w-[150px]">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-pink-500 mb-2">Categoría</label>
                    <select name="categoria" class="w-full bg-slate-800 border-2 border-slate-700 p-2 text-white outline-none focus:border-blue-500">
                        <option value="">Todas</option>
                        <?php
                        $stmt_cat = $conn->query("SELECT id, nombre FROM categorias ORDER BY nombre");
                        while ($cat = $stmt_cat->fetch(PDO::FETCH_ASSOC)) {
                            $selected = (isset($_GET['categoria']) && $_GET['categoria'] == $cat['id']) ? 'selected' : '';
                            echo "<option value='{$cat['id']}' {$selected}>{$cat['nombre']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-pink-600 text-white font-black px-6 py-2 transition-all">FILTRAR</button>
                <a href="biblioteca.php?tab=mis-mangas" class="text-slate-500 text-xs underline hover:text-white">Limpiar</a>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php if ($mangas_subidos): ?>
                <?php foreach ($mangas_subidos as $m): ?>
                    <div class="manga-card p-4 flex flex-col h-full">
                        <img src="../<?= htmlspecialchars($m['portada']) ?>" class="w-full aspect-[3/4] object-cover mb-4 border border-white/10" onerror="this.src='../img/blog_placeholder.png'">
                        <h3 class="manga-font text-xl truncate mb-1"><?= htmlspecialchars($m['titulo']) ?></h3>
                        <span class="text-blue-400 font-black text-[10px] uppercase mb-4"><?= htmlspecialchars($m['categoria_nombre'] ?? 'Sin categoría') ?></span>
                        
                        <div class="mt-auto space-y-2">
                            <a href="capitulos.php?manga=<?= $m['id'] ?>" class="block w-full bg-pink-600 text-center text-white text-xs py-2 hover:bg-blue-600 transition-all italic uppercase font-black">📖 Ver Capítulos</a>
                            
                            <?php if ($m['es_original']): ?>
                                <a href="gestionar_compartir.php?manga=<?= $m['id'] ?>" class="block w-full bg-green-600 text-center text-white text-xs py-2 hover:bg-green-500 transition-all italic uppercase font-black">📢 Compartir</a>
                            <?php endif; ?>

                            <form action="eliminar_manga.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este manga?');">
                                <input type="hidden" name="manga_id" value="<?= $m['id'] ?>">
                                <button type="submit" class="w-full text-red-500 text-[10px] font-black py-2 border border-red-500/30 hover:bg-red-500 hover:text-white transition-all uppercase">🗑️ Eliminar Manga</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="col-span-full text-center text-slate-500 italic py-20 text-xl font-bold">Aún no has subido ningún manga al Manga-verso.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- TAB 2: MI COLECCIÓN -->
    <div id="biblioteca" class="tab-content <?= ($active_tab === 'biblioteca') ? 'block' : 'hidden' ?>">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php if ($mangas_coleccion): ?>
                <?php foreach ($mangas_coleccion as $c): ?>
                    <div class="manga-card p-4 flex flex-col h-full">
                        <img src="../<?= htmlspecialchars($c['portada']) ?>" class="w-full aspect-[3/4] object-cover mb-4 border border-white/10" onerror="this.src='../img/blog_placeholder.png'">
                        <h3 class="manga-font text-xl truncate mb-1"><?= htmlspecialchars($c['titulo']) ?></h3>
                        
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-blue-400 font-black text-[10px] uppercase"><?= htmlspecialchars($c['categoria_nombre'] ?? 'General') ?></span>
                            <span class="text-slate-400 font-black text-[10px]">👤 <?= htmlspecialchars($c['autor']) ?></span>
                        </div>

                        <?php if ($c['es_original']): ?>
                            <span class="inline-block bg-yellow-400/20 text-yellow-500 text-[9px] font-black px-2 py-1 border border-yellow-500/50 self-start mb-3 uppercase">✨ Original</span>
                        <?php endif; ?>

                        <p class="text-slate-400 text-xs line-clamp-2 mb-4"><?= htmlspecialchars($c['descripcion']) ?></p>

                        <div class="mt-auto space-y-2">
                            <a href="leer_manga_compartido.php?manga=<?= $c['id'] ?>" class="block w-full bg-pink-600 text-center text-white text-xs py-2 hover:bg-blue-600 transition-all italic uppercase font-black">📖 Leer Manga</a>
                            
                            <form action="procesar_biblioteca.php" method="POST" onsubmit="return confirm('¿Quitar de tu colección?');">
                                <input type="hidden" name="manga_id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="referer" value="biblioteca.php?tab=biblioteca">
                                <button type="submit" class="w-full text-red-500 text-[10px] font-black py-2 border border-red-500/30 hover:bg-red-500 hover:text-white transition-all uppercase">🗑️ Quitar de mi Colección</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-20 bg-slate-900/30 border-2 border-dashed border-slate-700">
                    <p class="text-slate-500 italic text-xl mb-4 font-bold">Tu estantería personal está vacía.</p>
                    <a href="mangas_compartidos.php" class="inline-block bg-blue-600 text-white px-6 py-2 manga-font text-lg hover:bg-pink-600 transition-all">EXPLORAR COMUNIDAD</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- VOLVER AL PORTAL -->
    <div class="mt-24 text-center">
        <a href="../public/index.php" class="inline-block manga-font text-3xl text-pink-500 hover:text-blue-400 transition-all tracking-widest drop-shadow-[0_0_10px_rgba(236,72,153,0.5)]">
            ← VOLVER AL PORTAL
        </a>
    </div>

</main>

<footer class="text-center py-10 opacity-40">
    <p class="text-[9px] tracking-[0.5em] text-blue-400">MANGA_VERSO • VOL. 01 • 2026</p>
</footer>

<script>
function switchTab(e, id, btn){
    e.preventDefault();
    // Ocultar todos los contenidos
    document.querySelectorAll('.tab-content').forEach(t=>{ t.classList.add('hidden'); t.classList.remove('block'); });
    // Estilo botones
    document.querySelectorAll('.tab-button').forEach(b=>{ b.classList.remove('bg-pink-600', 'text-white'); b.classList.add('bg-slate-800', 'text-slate-400'); });

    // Activar seleccionado
    if(btn) { btn.classList.remove('bg-slate-800', 'text-slate-400'); btn.classList.add('bg-pink-600', 'text-white'); }
    const target = document.getElementById(id);
    if(target) { target.classList.remove('hidden'); target.classList.add('block'); }
    
    // Actualizar URL sin recargar para persistencia ligera
    const newUrl = new URL(window.location);
    newUrl.searchParams.set('tab', id);
    window.history.pushState({}, '', newUrl);
}

function createSakura(){
    const c = document.getElementById('sakura-container');
    if(!c) return;
    for(let i=0;i<25;i++){
        const p = document.createElement('div');
        p.className = 'sakura';
        const s = Math.random()*8+6;
        p.style.width = p.style.height = s+'px';
        p.style.left = Math.random()*100+'vw';
        p.style.animationDuration = (Math.random()*8+5)+'s';
        p.style.animationDelay = Math.random()*5+'s';
        c.appendChild(p);
    }
}
window.onload = createSakura;
</script>
</body>
</html>

