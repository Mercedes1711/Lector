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
        <button onclick="switchTab(event,'mis-mangas')"
            class="tab-button bg-pink-600 text-white manga-font text-2xl px-8 py-2 skew-x-[-12deg]">
            MIS SUBIDAS
        </button>
        <button onclick="switchTab(event,'biblioteca')"
            class="tab-button bg-slate-800 text-slate-400 manga-font text-2xl px-8 py-2 skew-x-[-12deg]">
            MI COLECCIÓN
        </button>
    </div>

    <!-- MIS MANGAS -->
    <div id="mis-mangas" class="tab-content block">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <?php
        $stmt = $conn->prepare(
            "SELECT m.*, c.nombre AS categoria
             FROM mangas m
             LEFT JOIN categorias c ON m.categoria_id = c.id
             WHERE m.usuario_id = ?
             ORDER BY m.fecha_subida DESC"
        );
        $stmt->execute([$usuario_id]);
        $mangas = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <div id="biblioteca" class="tab-content hidden">
        <p class="text-center text-slate-500 italic">
            Tu estantería está vacía.
        </p>
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
function switchTab(e,id){
    e.preventDefault();
    document.querySelectorAll('.tab-content').forEach(t=>{
        t.classList.add('hidden');
        t.classList.remove('block');
    });
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

