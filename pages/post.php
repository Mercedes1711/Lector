<?php
session_start();
$is_logged_in = !empty($_SESSION['usuario']) && !empty($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;
$nombre_usuario = $is_logged_in ? $_SESSION['usuario'] : 'INVITADO';

require __DIR__ . "/../src/conexion_bd.php";

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id <= 0) {
    header("Location: blog.php");
    exit;
}

// Obtener el artículo
$stmt = $conn->prepare("
    SELECT ab.*, u.usuario as autor_nombre 
    FROM articulos_blog ab 
    JOIN usuarios u ON ab.autor_id = u.id 
    WHERE ab.id = ?
");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header("Location: blog.php");
    exit;
}

// Obtener los comentarios
$stmt_comments = $conn->prepare("
    SELECT cb.*, u.usuario as usuario_nombre 
    FROM comentarios_blog cb 
    JOIN usuarios u ON cb.usuario_id = u.id 
    WHERE cb.articulo_id = ? 
    ORDER BY fecha_comentario ASC
");
$stmt_comments->execute([$post_id]);
$todos_comentarios = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);

// Organizar comentarios por parent_id
$comentarios_por_parent = [];
foreach ($todos_comentarios as $c) {
    $parent = $c['parent_id'] ?? 0;
    $comentarios_por_parent[$parent][] = $c;
}

function renderComentarios($parentId, $comentarios_por_parent, $nivel = 0) {
    if (!isset($comentarios_por_parent[$parentId])) return;

    foreach ($comentarios_por_parent[$parentId] as $c) {
        $margin = $nivel * 40;
        ?>
        <div class="comment-card p-6 border-l-4 <?= $nivel > 0 ? 'border-blue-500/50' : 'border-slate-700' ?> group hover:border-pink-500 transition-all mb-4" style="margin-left: <?= $margin ?>px">
            <div class="flex justify-between items-start mb-4">
                <span class="manga-font text-lg text-blue-400"><?= htmlspecialchars($c['usuario_nombre']) ?></span>
                <span class="text-[9px] text-slate-500 font-black uppercase"><?= date('d/m/Y H:i', strtotime($c['fecha_comentario'])) ?></span>
            </div>
            <p class="text-slate-300 text-sm italic mb-4">"<?= htmlspecialchars($c['comentario']) ?>"</p>
            
            <?php if ($GLOBALS['is_logged_in']): ?>
                <button onclick="toggleReplyForm(<?= $c['id'] ?>)" class="text-[10px] font-black text-pink-500 hover:text-white uppercase tracking-widest transition-colors">
                    ↳ Responder
                </button>
            <?php endif; ?>

            <!-- Formulario de respuesta (oculto) -->
            <div id="reply-form-<?= $c['id'] ?>" class="hidden mt-6 bg-slate-900/50 p-4 border border-slate-700">
                <form action="procesar_comentario.php" method="POST">
                    <input type="hidden" name="articulo_id" value="<?= $c['articulo_id'] ?>">
                    <input type="hidden" name="parent_id" value="<?= $c['id'] ?>">
                    <textarea name="comentario" required placeholder="Escribe tu respuesta..." 
                              class="w-full bg-slate-800 border border-slate-700 p-3 text-white text-xs outline-none focus:border-pink-500 mb-3 min-h-[80px]"></textarea>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-pink-600 text-white font-black text-[10px] px-4 py-2 skew-x-[-12deg] transition-all uppercase">
                            Enviar Respuesta
                        </button>
                        <button type="button" onclick="toggleReplyForm(<?= $c['id'] ?>)" class="text-[10px] font-black text-slate-500 hover:text-white uppercase px-4 py-2">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php
        renderComentarios($c['id'], $comentarios_por_parent, $nivel + 1);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['titulo']) ?> - Manga_verso Blog</title>

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
        .post-container {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(10px);
            border-left: 4px solid #f472b6;
        }
        .comment-card {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>

<body class="min-h-screen">
<div id="sakura-container"></div>

<header class="border-b-4 border-pink-500 bg-slate-900/80 backdrop-blur-md p-6 shadow-2xl">
    <div class="container mx-auto flex flex-col sm:flex-row justify-between items-center gap-6">
        <a href="../public/index.php" class="text-center sm:text-left">
            <h1 class="manga-font text-5xl italic hover:text-pink-500 transition-colors">Manga_verso</h1>
            <p class="text-pink-500 font-black text-xs tracking-[0.3em]">BLOG DE LA COMUNIDAD</p>
        </a>
        <div class="flex items-center gap-4">
            <?php if ($is_logged_in): ?>
                <span class="manga-font text-xl text-blue-400">HOLA, <?= htmlspecialchars($nombre_usuario) ?></span>
            <?php endif; ?>
            <a href="blog.php" class="bg-blue-600 hover:bg-pink-600 text-white font-black text-xs px-4 py-2 skew-x-[-12deg] transition-all">
                VOLVER AL BLOG
            </a>
            <?php if (!$is_logged_in): ?>
                <a href="../public/login.php" class="bg-pink-600 border-2 border-black text-white font-black text-xs px-4 py-2 skew-x-[-12deg] transition-all">
                    INICIAR SESIÓN
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="container mx-auto max-w-4xl px-4 py-12">
    <!-- ARTÍCULO -->
    <article class="post-container p-8 md:p-12 mb-16 shadow-2xl">
        <div class="mb-8">
            <div class="flex items-center gap-3 text-pink-500 text-xs font-black uppercase mb-4 tracking-widest">
                <span>📅 <?= date('d M, Y', strtotime($post['fecha_creacion'])) ?></span>
                <span>•</span>
                <span>👤 <?= htmlspecialchars($post['autor_nombre']) ?></span>
            </div>
            <h1 class="manga-font text-5xl md:text-7xl mb-6"><?= htmlspecialchars($post['titulo']) ?></h1>
            <div class="h-1 w-24 bg-blue-500"></div>
        </div>

        <?php if ($is_logged_in && $post['autor_id'] == $user_id): ?>
            <div class="flex justify-end mb-6">
                <a href="editar_blog.php?id=<?= $post_id ?>" class="bg-blue-600 hover:bg-pink-600 text-white font-black text-[10px] px-6 py-2 skew-x-[-12deg] transition-all uppercase tracking-widest flex items-center gap-2 shadow-[4px_4px_0px_rgba(255,255,255,0.1)]">
                    ✏️ Editar Artículo
                </a>
            </div>
        <?php endif; ?>

        <?php if ($post['portada']): ?>
            <img src="../<?= htmlspecialchars($post['portada']) ?>" class="w-full aspect-video object-cover mb-10 rounded-lg shadow-2xl" onerror="this.style.display='none'">
        <?php endif; ?>

        <div class="prose prose-invert max-w-none text-slate-300 leading-relaxed text-lg mb-12">
            <?= nl2br($post['contenido']) ?>
        </div>
    </article>

    <!-- SECCIÓN DE COMENTARIOS -->
    <section class="mt-20">
        <h2 class="manga-font text-4xl mb-10 text-blue-400 flex items-center gap-4">
            💬 COMENTARIOS <span class="text-pink-500 text-2xl">[<?= count($todos_comentarios) ?>]</span>
        </h2>

        <!-- FORMULARIO DE COMENTARIO -->
        <div class="comment-card p-6 mb-12 border-l-4 border-blue-500">
            <?php if ($is_logged_in): ?>
                <form action="procesar_comentario.php" method="POST">
                    <input type="hidden" name="articulo_id" value="<?= $post_id ?>">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-pink-500 mb-4">Escribe tu comentario</label>
                    <textarea name="comentario" required placeholder="¿Qué opinas sobre este post?..." 
                              class="w-full bg-slate-800 border-2 border-slate-700 p-4 text-white outline-none focus:border-pink-500 transition-all min-h-[120px] mb-4"></textarea>
                    <button type="submit" class="bg-pink-600 hover:bg-blue-600 text-white font-black px-8 py-3 transition-all skew-x-[-12deg] uppercase">
                        PUBLICAR COMENTARIO
                    </button>
                </form>
            <?php else: ?>
                <div class="text-center py-6">
                    <p class="text-slate-400 italic mb-4 font-bold">Inicia sesión para participar en el debate.</p>
                    <a href="../public/login.php" class="bg-blue-600 text-white font-black text-xs px-8 py-3 skew-x-[-12deg] inline-block transition-all hover:bg-pink-600">
                        INICIAR SESIÓN / REGISTRO
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- LISTA DE COMENTARIOS -->
        <div class="space-y-6">
            <?php if (!empty($comentarios_por_parent)): ?>
                <?php renderComentarios(0, $comentarios_por_parent); ?>
            <?php else: ?>
                <p class="text-slate-500 italic text-center py-10">Sé el primero en comentar este artículo.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- BOTÓN VOLVER -->
    <div class="mt-24 text-center">
        <a href="blog.php" class="text-slate-500 hover:text-white transition-all manga-font text-2xl tracking-widest">
            ← VOLVER A LAS NOTICIAS
        </a>
    </div>
</main>

<footer class="text-center py-10 opacity-40">
    <p class="text-[9px] tracking-[0.5em] text-blue-400">MANGA_VERSO • BLOG VOL. 01 • 2026</p>
</footer>

<script>
function toggleReplyForm(id) {
    const form = document.getElementById('reply-form-' + id);
    if (form.classList.contains('hidden')) {
        form.classList.remove('hidden');
    } else {
        form.classList.add('hidden');
    }
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
