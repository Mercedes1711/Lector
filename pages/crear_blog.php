<?php
session_start();
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Blog - Manga_verso</title>

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
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }
    </style>
</head>

<body class="min-h-screen">
<div id="sakura-container"></div>

<header class="border-b-4 border-pink-500 bg-slate-900/80 backdrop-blur-md p-6 shadow-2xl">
    <div class="container mx-auto flex justify-between items-center">
        <a href="blog.php">
            <h1 class="manga-font text-5xl italic">Manga_verso</h1>
            <p class="text-pink-500 font-black text-xs tracking-[0.3em]">NUEVA ENTRADA</p>
        </a>
        <div class="flex items-center gap-4">
            <a href="blog.php" class="bg-blue-600 hover:bg-pink-600 text-white font-black text-xs px-4 py-2 skew-x-[-12deg] transition-all">
                CANCELAR
            </a>
        </div>
    </div>
</header>

<main class="container mx-auto max-w-4xl px-4 py-12">
    <div class="mb-10">
        <h2 class="manga-font text-5xl text-white italic drop-shadow-[0_0_10px_rgba(59,130,246,0.5)]">COMPARTIR CON LA COMUNIDAD</h2>
        <div class="h-1 w-20 bg-pink-500 mt-2"></div>
    </div>

    <div class="form-container p-8 md:p-12 rounded-2xl">
        <form action="procesar_blog.php" method="POST" enctype="multipart/form-data">
            <div class="space-y-8">
                <!-- TÍTULO -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-pink-500 mb-2">TÍTULO DEL ARTÍCULO</label>
                    <input type="text" name="titulo" required placeholder="Un título llamativo..." 
                           class="w-full bg-slate-800/50 border-2 border-slate-700 p-4 text-white outline-none focus:border-blue-500 transition-all text-xl font-bold">
                </div>

                <!-- PORTADA -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-pink-500 mb-2">IMAGEN DE PORTADA (OPCIONAL)</label>
                    <div class="relative group">
                        <input type="file" name="portada" id="portada" accept="image/*" class="hidden" onchange="updateFileName(this)">
                        <label for="portada" class="cursor-pointer block w-full bg-slate-800/50 border-2 border-dashed border-slate-700 p-8 text-center hover:border-blue-500 transition-all group-hover:bg-slate-800">
                            <span id="file-name" class="text-slate-400 group-hover:text-blue-400 font-bold">📁 Haz clic para subir una imagen</span>
                            <p class="text-[9px] text-slate-500 mt-2 uppercase">Formatos permitidos: JPG, PNG, WEBP</p>
                        </label>
                    </div>
                </div>

                <!-- SPOILER -->
                <div class="flex items-center gap-4 bg-slate-800/30 p-4 border border-slate-700/50">
                    <input type="checkbox" name="es_spoiler" id="es_spoiler" class="w-6 h-6 accent-pink-600 cursor-pointer">
                    <label for="es_spoiler" class="cursor-pointer select-none">
                        <span class="block text-sm font-black text-white">¿CONTIENE SPOILERS?</span>
                        <span class="block text-[10px] text-slate-500 uppercase">Activa esta opción si el artículo revela detalles importantes de la trama.</span>
                    </label>
                </div>

                <!-- CONTENIDO -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-pink-500 mb-2">CONTENIDO DEL BLOG</label>
                    <textarea name="contenido" required placeholder="Escribe aquí tu historia, reseña o noticia..." 
                              class="w-full bg-slate-800/50 border-2 border-slate-700 p-6 text-white outline-none focus:border-blue-500 transition-all min-h-[400px] text-lg leading-relaxed"></textarea>
                </div>

                <!-- BOTÓN SUBIR -->
                <div class="pt-6">
                    <button type="submit" class="w-full bg-pink-600 hover:bg-blue-600 text-white font-black py-5 px-10 skew-x-[-12deg] transition-all manga-font text-3xl shadow-[0_10px_0_#9d174d] hover:shadow-[0_10px_0_#1e40af] active:translate-y-1 active:shadow-none">
                        PUBLICAR BLOG ✨
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>

<footer class="text-center py-10 opacity-40">
    <p class="text-[9px] tracking-[0.5em] text-blue-400">MANGA_VERSO • CREATOR STUDIO • 2026</p>
</footer>

<script>
function updateFileName(input) {
    const fileName = input.files[0] ? input.files[0].name : "📁 Haz clic para subir una imagen";
    document.getElementById('file-name').textContent = fileName;
    document.getElementById('file-name').classList.add('text-blue-400');
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
