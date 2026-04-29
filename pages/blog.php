<?php
session_start();
$is_logged_in = !empty($_SESSION['usuario']) && !empty($_SESSION['user_id']);
$nombre_usuario = $is_logged_in ? $_SESSION['usuario'] : 'INVITADO';

require __DIR__ . "/../src/conexion_bd.php";

$stmt = $conn->query("
    SELECT ab.*, u.usuario as autor_nombre 
    FROM articulos_blog ab 
    JOIN usuarios u ON ab.autor_id = u.id 
    ORDER BY fecha_creacion DESC
");
$articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Manga_verso</title>

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
        .blog-card { 
            background: #1e293b; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.05);
            overflow: hidden;
        }
        .blog-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(236,72,153,0.3); border-color: #f472b6; }
        .glass-effect {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>

<body class="min-h-screen">
<div id="sakura-container"></div>

<header class="border-b-4 border-pink-500 bg-slate-900/80 backdrop-blur-md p-6 shadow-2xl">
    <div class="container mx-auto flex flex-col sm:flex-row justify-between items-center gap-6">
        <a href="<?= BASE_URL ?>public/index.php" class="text-center sm:text-left">
            <h1 class="manga-font text-5xl italic hover:text-pink-500 transition-colors">Manga_verso</h1>
            <p class="text-pink-500 font-black text-xs tracking-[0.3em]">BLOG DE LA COMUNIDAD</p>
        </a>
        <div class="flex items-center gap-4">
            <?php if ($is_logged_in): ?>
                <span class="manga-font text-xl text-blue-400">HOLA, <?= htmlspecialchars($nombre_usuario) ?></span>
                <a href="<?= BASE_URL ?>public/logout.php" class="bg-pink-600 hover:bg-blue-600 text-white font-black text-xs px-4 py-2 skew-x-[-12deg] transition-all">
                    CERRAR SESIÓN
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>public/login.php" class="bg-blue-600 hover:bg-pink-600 text-white font-black text-xs px-6 py-2 skew-x-[-12deg] transition-all">
                    INICIAR SESIÓN
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="container mx-auto px-4 py-10">
    <div class="mb-12 flex flex-col md:flex-row justify-between items-center gap-6">
        <div class="text-left">
            <h2 class="manga-font text-6xl text-white mb-2 drop-shadow-[0_0_15px_rgba(59,130,246,0.5)]">NOTICIAS Y RESEÑAS</h2>
            <div class="h-1 w-32 bg-pink-500"></div>
        </div>
        </div>
        <?php if ($is_logged_in): ?>
            <a href="crear_blog.php" class="bg-pink-600 hover:bg-white hover:text-pink-600 text-white font-black px-8 py-4 skew-x-[-12deg] transition-all manga-font text-2xl shadow-[5px_5px_0px_#3b82f6] hover:shadow-none active:translate-x-1 active:translate-y-1">
                + CREAR BLOG
            </a>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if ($articulos): ?>
            <?php foreach ($articulos as $a): ?>
                <article class="blog-card flex flex-col group">
                    <div class="relative overflow-hidden aspect-video">
                        <img src="../<?= htmlspecialchars($a['portada'] ?: 'img/blog_placeholder.png') ?>" 
                             alt="<?= htmlspecialchars($a['titulo']) ?>"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                             onerror="this.src='../img/placeholder-avatar.png'">
                        <div class="absolute top-0 right-0 m-4 flex flex-col items-end gap-2">
                            <div class="bg-pink-600 text-white text-[10px] font-black px-3 py-1 skew-x-[-12deg]">
                                <?= date('d M, Y', strtotime($a['fecha_creacion'])) ?>
                            </div>
                            <?php if ($a['es_spoiler']): ?>
                                <div class="bg-yellow-500 text-black text-[10px] font-black px-3 py-1 skew-x-[-12deg] shadow-lg">
                                    ⚠️ SPOILER
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <h3 class="manga-font text-2xl mb-3 line-clamp-2"><?= htmlspecialchars($a['titulo']) ?></h3>
                        
                        <?php if ($a['es_spoiler']): ?>
                            <div class="bg-slate-900/80 p-4 border border-yellow-500/30 mb-6 flex-grow flex items-center justify-center">
                                <p class="text-[10px] font-black text-yellow-500 text-center uppercase tracking-widest">
                                    Contenido oculto por spoilers
                                </p>
                            </div>
                        <?php else: ?>
                            <p class="text-slate-400 text-sm line-clamp-3 mb-6 flex-grow">
                                <?= strip_tags($a['contenido']) ?>
                            </p>
                        <?php endif; ?>

                        <div class="flex items-center justify-between mt-auto border-t border-white/5 pt-4">
                            <span class="text-xs font-black text-blue-400 uppercase">👤 <?= htmlspecialchars($a['autor_nombre']) ?></span>
                            <a href="post.php?id=<?= $a['id'] ?>" class="text-pink-500 font-black text-xs hover:text-white transition-colors uppercase tracking-widest">
                                LEER MÁS →
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-20 glass-effect rounded-2xl">
                <p class="text-slate-500 italic text-2xl font-bold mb-4">No hay artículos publicados todavía.</p>
                <div class="inline-block animate-bounce text-pink-500 text-4xl">📚</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- BOTÓN VOLVER -->
    <div class="mt-20 text-center">
        <a href="<?= BASE_URL ?>public/index.php" class="inline-block bg-slate-800 hover:bg-pink-600 text-white font-black px-10 py-4 skew-x-[-12deg] transition-all manga-font text-2xl">
            VOLVER AL INICIO
        </a>
    </div>
</main>

<footer class="text-center py-10 opacity-40">
    <p class="text-[9px] tracking-[0.5em] text-blue-400">MANGA_VERSO • BLOG VOL. 01 • 2026</p>
</footer>

<script>
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
