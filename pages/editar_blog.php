<?php
session_start();
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

require __DIR__ . "/../src/conexion_bd.php";

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$usuario_id = $_SESSION['user_id'];

if ($post_id <= 0) {
    header("Location: blog.php");
    exit;
}

// Obtener el artículo y verificar autoría
$stmt = $conn->prepare("SELECT * FROM articulos_blog WHERE id = ? AND autor_id = ?");
$stmt->execute([$post_id, $usuario_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    $_SESSION['error'] = "No tienes permiso para editar este artículo o no existe.";
    header("Location: blog.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Blog - Manga_verso</title>

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
        .form-container {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>

<body class="min-h-screen">
<div id="sakura-container"></div>

<header class="border-b-4 border-pink-500 bg-slate-900/80 backdrop-blur-md p-6 shadow-2xl">
    <div class="container mx-auto flex justify-between items-center">
        <a href="blog.php">
            <h1 class="manga-font text-5xl italic">Manga_verso</h1>
            <p class="text-pink-500 font-black text-xs tracking-[0.3em]">EDITOR DE CONTENIDO</p>
        </a>
        <div class="flex items-center gap-4">
            <a href="post.php?id=<?= $post_id ?>" class="bg-blue-600 hover:bg-pink-600 text-white font-black text-xs px-4 py-2 skew-x-[-12deg] transition-all">
                CANCELAR
            </a>
        </div>
    </div>
</header>

<main class="container mx-auto max-w-4xl px-4 py-12">
    <div class="mb-10 text-center">
        <h2 class="manga-font text-6xl text-white italic drop-shadow-[0_0_15px_rgba(236,72,153,0.5)]">MODIFICAR ARTÍCULO</h2>
        <div class="h-1 w-32 bg-pink-500 mx-auto mt-4"></div>
    </div>

    <div class="form-container p-8 md:p-12 rounded-2xl">
        <form action="actualizar_blog.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $post_id ?>">
            <div class="space-y-8">
                <!-- TÍTULO -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-pink-500 mb-2">TÍTULO DEL ARTÍCULO</label>
                    <input type="text" name="titulo" required value="<?= htmlspecialchars($post['titulo']) ?>" placeholder="Un título llamativo..." 
                           class="w-full bg-slate-800/50 border-2 border-slate-700 p-4 text-white outline-none focus:border-blue-500 transition-all text-xl font-bold">
                </div>

                <!-- PORTADA ACTUAL -->
                <?php if ($post['portada']): ?>
                    <div class="relative w-full aspect-video rounded-lg overflow-hidden border-2 border-slate-700">
                        <img src="../<?= htmlspecialchars($post['portada']) ?>" class="w-full h-full object-cover opacity-50">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="bg-black/80 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-white italic border border-white/20">Portada Actual</span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- PORTADA NUEVA -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-pink-500 mb-2">CAMBIAR PORTADA (DEJAR VACÍO PARA MANTENER)</label>
                    <div class="relative group">
                        <input type="file" name="portada" id="portada" accept="image/*" class="hidden" onchange="updateFileName(this)">
                        <label for="portada" class="cursor-pointer block w-full bg-slate-800/50 border-2 border-dashed border-slate-700 p-8 text-center hover:border-blue-500 transition-all group-hover:bg-slate-800">
                            <span id="file-name" class="text-slate-400 group-hover:text-blue-400 font-bold">📁 Haz clic para subir una nueva imagen</span>
                        </label>
                    </div>
                </div>

                <!-- SPOILER -->
                <div class="flex items-center gap-4 bg-slate-800/30 p-4 border border-slate-700/50">
                    <input type="checkbox" name="es_spoiler" id="es_spoiler" class="w-6 h-6 accent-pink-600 cursor-pointer" <?= $post['es_spoiler'] ? 'checked' : '' ?>>
                    <label for="es_spoiler" class="cursor-pointer select-none">
                        <span class="block text-sm font-black text-white">¿CONTIENE SPOILERS?</span>
                        <span class="block text-[10px] text-slate-500 uppercase">Márcalo para advertir a otros guerreros.</span>
                    </label>
                </div>

                <!-- CONTENIDO -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-pink-500 mb-2">CONTENIDO DEL BLOG</label>
                    <textarea name="contenido" required placeholder="Escribe aquí tu historia..." 
                              class="w-full bg-slate-800/50 border-2 border-slate-700 p-6 text-white outline-none focus:border-blue-500 transition-all min-h-[400px] text-lg leading-relaxed"><?= htmlspecialchars($post['contenido']) ?></textarea>
                </div>

                <!-- BOTÓN ACTUALIZAR -->
                <div class="pt-6">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-pink-600 text-white font-black py-5 px-10 skew-x-[-12deg] transition-all manga-font text-3xl shadow-[0_10px_0_#1e40af] hover:shadow-[0_10px_0_#9d174d] active:translate-y-1 active:shadow-none">
                        GUARDAR CAMBIOS 💾
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>

<footer class="text-center py-10 opacity-40">
    <p class="text-[9px] tracking-[0.5em] text-blue-400">MANGA_VERSO • EDITOR • 2026</p>
</footer>

<script>
function updateFileName(input) {
    const fileName = input.files[0] ? input.files[0].name : "📁 Haz clic para subir una nueva imagen";
    document.getElementById('file-name').textContent = fileName;
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
