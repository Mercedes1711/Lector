<?php
session_start();
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

require __DIR__ . "/../src/conexion_bd.php";
$usuario_id = $_SESSION['user_id'];

// Obtener mis mangas (MIS SUBIDAS)
$stmt = $conn->prepare(
    "SELECT m.*, c.nombre AS categoria
     FROM mangas m
     LEFT JOIN categorias c ON m.categoria_id = c.id
     WHERE m.usuario_id = ?
     ORDER BY m.fecha_subida DESC"
);
$stmt->execute([$usuario_id]);
$mangas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener mi colección (BIBLIOTECA)
$stmt_col = $conn->prepare(
    "SELECT m.*, c.nombre AS categoria, u.usuario AS autor
     FROM biblioteca_usuario bu
     JOIN mangas m ON bu.manga_id = m.id
     LEFT JOIN categorias c ON m.categoria_id = c.id
     LEFT JOIN usuarios u ON m.usuario_id = u.id
     WHERE bu.usuario_id = ?
     ORDER BY bu.fecha_agregado DESC"
);
$stmt_col->execute([$usuario_id]);
$coleccion = $stmt_col->fetchAll(PDO::FETCH_ASSOC);

$active_tab = 'mis-mangas';
if (empty($mangas) && !empty($coleccion)) {
    $active_tab = 'biblioteca';
}

$tab_mis_mangas_btn = ($active_tab === 'mis-mangas') ? 'bg-pink-600 text-white' : 'bg-slate-800 text-slate-400';
$tab_biblioteca_btn = ($active_tab === 'biblioteca') ? 'bg-pink-600 text-white' : 'bg-slate-800 text-slate-400';

$content_mis_mangas_class = ($active_tab === 'mis-mangas') ? 'block' : 'hidden';
$content_biblioteca_class = ($active_tab === 'biblioteca') ? 'block' : 'hidden';
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

        .manga-font {
            font-family: 'Bangers', cursive, sans-serif;
            letter-spacing: 0.05em;
        }

        /* Sakura */
        #sakura-container {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }

        .sakura {
            position: absolute;
            background: #f472b6;
            border-radius: 100% 0% 100% 0%;
            opacity: 0.7;
            animation: fall linear infinite;
        }

        @keyframes fall {
            from { transform: translateY(-10vh) rotate(0deg); opacity: 1; }
            to   { transform: translateY(110vh) rotate(360deg); opacity: 0; }
        }

        /* CONTENIDO POR ENCIMA DEL SAKURA */
        header, main, footer {
            position: relative;
            z-index: 20;
        }

        .manga-card {
            background: #1e293b;
            transition: all 0.3s ease;
        }

        .manga-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 0 20px rgba(59,130,246,0.5);
        }
    </style>
</head>

<body class="min-h-screen">

<div id="sakura-container"></div>

<header class="border-b-4 border-pink-500 bg-slate-900/80 backdrop-blur-md p-6 shadow-2xl">
    <div class="container mx-auto flex justify-between items-center">
        <div>
            <h1 class="manga-font text-5xl italic">Manga_verso</h1>
            <p class="text-pink-500 font-black text-xs tracking-[0.3em]">
                BIBLIOTECA DEL GUERRERO
            </p>
        </div>
        <div class="flex items-center gap-4">
            <span class="manga-font text-xl text-blue-400">
                HOLA, <?= htmlspecialchars($_SESSION['usuario']) ?>
            </span>
            <a href="../public/logout.php"
               class="bg-pink-600 hover:bg-blue-600 text-white font-black text-xs px-4 py-2 skew-x-[-12deg] transition-all">
                CERRAR SESIÓN
            </a>
        </div>
    </div>
</header>

<main class="container mx-auto px-4 py-10">

    <!-- TABS -->
    <div class="flex justify-center gap-4 mb-12">
        <button onclick="switchTab(event,'mis-mangas', this)"
            class="tab-button <?= $tab_mis_mangas_btn ?> manga-font text-2xl px-8 py-2 skew-x-[-12deg]">
            MIS SUBIDAS
        </button>
        <button onclick="switchTab(event,'biblioteca', this)"
            class="tab-button <?= $tab_biblioteca_btn ?> manga-font text-2xl px-8 py-2 skew-x-[-12deg]">
            MI COLECCIÓN
        </button>
    </div>

    <!-- MIS MANGAS -->
    <div id="mis-mangas" class="tab-content <?= $content_mis_mangas_class ?>">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <?php
        if ($mangas):
            foreach ($mangas as $m): ?>
                <div class="manga-card p-4 border-b-4 border-slate-800">
                    <img src="../<?= htmlspecialchars($m['portada']) ?>"
                         class="w-full aspect-[3/4] object-cover mb-4 border border-white/10">

                    <h3 class="manga-font text-xl truncate mb-1">
                        <?= htmlspecialchars($m['titulo']) ?>
                    </h3>

                    <span class="text-blue-400 font-black text-[10px] uppercase">
                        <?= htmlspecialchars($m['categoria'] ?? 'General') ?>
                    </span>
                     <form action="eliminar_manga.php" method="POST"
      onsubmit="return confirm('¿Seguro que quieres borrar este manga?');">

    <input type="hidden" name="manga_id" value="<?= $m['id'] ?>">

    <button type="submit"
        class="w-full mt-4 border border-red-500/60
               text-red-500 font-black text-[10px] py-2
               hover:bg-red-600 hover:text-white
               transition-all uppercase">
        BORRAR
    </button>
</form>
                    <?php if (isset($m['es_original']) && $m['es_original'] == 1): ?>
                    <a href="gestionar_compartir.php?manga=<?= $m['id'] ?>"
                       class="block mt-4 bg-blue-600 text-center text-white font-black text-xs py-2 hover:bg-blue-500 transition-all italic">
                        📢 COMPARTIR MANGA
                    </a>
                    <?php endif; ?>
                    <a href="capitulos.php?manga=<?= $m['id'] ?>"
                       class="block mt-4 bg-pink-600 text-center text-white text-xs py-2 hover:bg-blue-600 transition-all italic">
                        VER CAPÍTULOS
                    </a>
                </div>
        <?php endforeach;
        else: ?>
            <p class="col-span-full text-center text-slate-500 italic">
                Aún no has subido ningún manga.
            </p>
        <?php endif; ?>
        </div>
    </div>

    <!-- BIBLIOTECA -->
    <div id="biblioteca" class="tab-content <?= $content_biblioteca_class ?>">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <?php
        if ($coleccion):
            foreach ($coleccion as $c): ?>
                <div class="manga-card p-4 border-b-4 border-slate-800 flex flex-col h-full">
                    <img src="../<?= htmlspecialchars($c['portada']) ?>"
                         class="w-full aspect-[3/4] object-cover mb-4 border border-white/10">

                    <h3 class="manga-font text-xl truncate mb-1">
                        <?= htmlspecialchars($c['titulo']) ?>
                    </h3>

                    <div class="flex items-center justify-between mb-2">
                        <span class="text-blue-400 font-black text-[10px] uppercase">
                            <?= htmlspecialchars($c['categoria'] ?? 'General') ?>
                        </span>
                        <span class="text-slate-400 font-black text-[10px]">
                            👤 <?= htmlspecialchars($c['autor']) ?>
                        </span>
                    </div>
                    
                    <?php if ($c['es_original']): ?>
                        <span class="bg-pink-100 text-pink-600 text-[9px] font-black px-2 py-1 border border-pink-600 self-start mb-3">✨ ORIGINAL</span>
                    <?php endif; ?>

                    <div class="mt-auto">
                        <form action="procesar_biblioteca.php" method="POST">
                            <input type="hidden" name="manga_id" value="<?= $c['id'] ?>">
                            <input type="hidden" name="referer" value="biblioteca.php">
                            <input type="hidden" name="accion" value="eliminar">
                            <button type="submit"
                                class="w-full mt-4 border border-red-500/60
                                       text-red-500 font-black text-[10px] py-2
                                       hover:bg-red-600 hover:text-white
                                       transition-all uppercase">
                                🗑️ ELIMINAR
                            </button>
                        </form>
                        <a href="leer_manga_compartido.php?manga=<?= $c['id'] ?>"
                           class="block mt-4 bg-pink-600 text-center text-white text-xs py-2 hover:bg-blue-600 transition-all italic uppercase">
                            📖 LEER
                        </a>
                    </div>
                </div>
        <?php endforeach;
        else: ?>
            <p class="col-span-full text-center text-slate-500 italic text-xl mt-10">
                Tu estantería está vacía.
            </p>
        <?php endif; ?>
        </div>
    </div>

    <!-- VOLVER AL PORTAL (NO FLOTANTE) -->
    <div class="mt-24 text-center relative z-30">
        <a href="../public/index.php"
           class="inline-block manga-font text-3xl
                  text-pink-500 hover:text-blue-400
                  transition-all tracking-widest
                  drop-shadow-[0_0_10px_rgba(236,72,153,0.8)]">
            ← VOLVER AL PORTAL DEL GUERRERO
        </a>
    </div>

</main>

<footer class="text-center py-10 opacity-40">
    <p class="text-[9px] tracking-[0.5em] text-blue-400">
        MANGA_VERSO • VOL. 01 • 2026
    </p>
</footer>

<script>
function switchTab(e, id, btn){
    e.preventDefault();
    document.querySelectorAll('.tab-content').forEach(t=>{
        t.classList.add('hidden');
        t.classList.remove('block');
    });

    document.querySelectorAll('.tab-button').forEach(b=>{
        b.classList.remove('bg-pink-600', 'text-white');
        b.classList.add('bg-slate-800', 'text-slate-400');
    });

    if(btn) {
        btn.classList.remove('bg-slate-800', 'text-slate-400');
        btn.classList.add('bg-pink-600', 'text-white');
    }

    document.getElementById(id).classList.remove('hidden');
    document.getElementById(id).classList.add('block');
}

function createSakura(){
    const c = document.getElementById('sakura-container');
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

