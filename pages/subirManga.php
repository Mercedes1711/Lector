<?php
session_start();
if (empty($_SESSION['usuario'])) {
    header("Location: ../public/login.php");
    exit;
}
require __DIR__ . "/../src/conexion_bd.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar Obra | Manga_verso</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Black+Ops+One&family=Outfit:wght@400;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0f172a; color: white; min-height: 100vh; overflow-x: hidden; display: flex; flex-direction: column; }
        .manga-font { font-family: 'Bangers', cursive; letter-spacing: 0.05em; }
        
        /* FUENTE PARA EL TÍTULO PRINCIPAL */
        .title-font { font-family: 'Black Ops One', system-ui; text-transform: uppercase; }

        /* Sakura */
        .sakura { position: fixed; background: #f472b6; border-radius: 100% 0% 100% 0%; opacity: 0.7; pointer-events: none; z-index: 1; animation: fall linear infinite; }
        @keyframes fall { 0% { transform: translateY(-10vh) rotate(0deg); opacity: 0; } 10% { opacity: 1; } 100% { transform: translateY(110vh) rotate(360deg); opacity: 0; } }

        /* Panel Central con borde tipo papel */
        .manga-panel { background: white; border: 4px solid #000; box-shadow: 15px 15px 0px #3b82f6; color: #000; position: relative; z-index: 10; }
        
        /* Decoraciones */
        .onomatopeya { position: fixed; left: 20px; top: 40%; transform: rotate(-90deg); font-size: 8rem; font-weight: 900; color: #3b82f6; opacity: 0.15; pointer-events: none; z-index: 0; filter: blur(1px); }

        .ranking-board {
            position: fixed; right: 20px; top: 20%; width: 220px;
            background: #000; border: 3px solid #f472b6; padding: 15px;
            transform: rotate(2deg); z-index: 0; opacity: 0.9;
            box-shadow: 8px 8px 0px rgba(244, 114, 182, 0.3);
        }

        .input-manga { border: 2px solid #000; padding: 12px; font-weight: bold; outline: none; transition: 0.2s; width: 100%; }
        .input-manga:focus { border-color: #3b82f6; box-shadow: 4px 4px 0px #3b82f6; }
    </style>
</head>
<body class="bg-slate-900">

    <div id="sakura-container" class="fixed inset-0 pointer-events-none z-0"></div>

    <div class="onomatopeya title-font hidden lg:block">ドドドド</div>

    <div class="ranking-board hidden xl:block">
        <h4 class="manga-font text-pink-500 text-2xl border-b border-pink-500 mb-3 italic">TOP WEEKLY</h4>
        <ul class="text-[10px] font-black space-y-2 uppercase tracking-tighter">
            <li class="flex justify-between text-yellow-400"><span>1. One Piece</span> <span class="text-white">HOT!</span></li>
            <li class="flex justify-between"><span>2. Jujutsu Kaisen</span> <span>-</span></li>
            <li class="flex justify-between text-blue-400 italic"><span>3. TU NUEVA OBRA</span> <span>NEW</span></li>
            <li class="flex justify-between opacity-50"><span>4. My Hero Ac...</span> <span>-</span></li>
            <li class="flex justify-between opacity-50"><span>5. Black Clover</span> <span>-</span></li>
        </ul>
        <div class="mt-4 bg-pink-500 text-black text-center text-[9px] py-1 font-black">PROXIMAMENTE EN PORTADA</div>
    </div>

    <header class="bg-white border-b-4 border-black p-4 relative z-50 text-black">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="manga-font text-4xl italic transform -rotate-1">
                MANGA<span class="text-blue-500">_</span>VERSO
            </h1>
            <div class="auth-logged">
                <span class="bg-black text-white px-4 py-1 font-black border-2 border-black shadow-[3px_3px_0px_#f472b6] text-xs uppercase italic">
                    Author Mode: On
                </span>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-12 relative z-10 max-w-2xl flex-grow">
        <div class="text-center mb-12 relative">
            <div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-yellow-400 text-black text-[10px] font-black px-4 py-1 border-2 border-black rotate-3 z-20">
                ¡NUEVA SERIALIZACIÓN!
            </div>
            <h2 class="title-font text-5xl md:text-7xl leading-none text-white drop-shadow-[4px_4px_0px_#3b82f6] italic">
                SUBIR <span class="text-blue-500 block md:inline">OBRA</span>
            </h2>
            <div class="w-24 h-2 bg-pink-500 mx-auto mt-2 shadow-[4px_4px_0px_#000]"></div>
        </div>

        <div class="manga-panel p-8">
            <form action="procesar_manga.php" method="POST" enctype="multipart/form-data" class="flex flex-col gap-6">
                
                <div>
                    <label class="text-[10px] font-black uppercase text-blue-600 mb-1 flex items-center gap-2">
                        <span class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></span> Título de la serie
                    </label>
                    <input type="text" name="titulo" class="input-manga text-xl" placeholder="Nombre de tu manga..." required>
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-blue-600 mb-1 block">Sinopsis oficial</label>
                    <textarea name="descripcion" rows="4" class="input-manga" placeholder="De qué trata esta leyenda..." required></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-[10px] font-black uppercase text-blue-600 mb-1 block">Género</label>
                        <select name="categoria_id" class="input-manga italic" required>
                            <option value="">Seleccionar...</option>
                            <?php
                            $stmt = $conn->query("SELECT id, nombre FROM categorias ORDER BY nombre");
                            while ($cat = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='{$cat['id']}'>{$cat['nombre']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="flex items-end">
                         <label class="flex items-center gap-3 cursor-pointer bg-blue-50 p-3 border-2 border-black w-full hover:bg-yellow-50 transition-colors">
                            <input type="checkbox" name="es_original" value="1" class="w-5 h-5 accent-pink-500">
                            <span class="font-black text-[11px] text-black uppercase italic">✨ Es mi propia creación</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-blue-600 mb-1 block">Portada del Volumen 1</label>
                    <div class="border-4 border-black border-dotted p-6 bg-slate-50 text-center">
                        <input type="file" name="portada" accept="image/*" class="text-xs text-black font-black file:bg-blue-600 file:text-white file:border-2 file:border-black file:px-4 file:py-2 file:mr-4 file:cursor-pointer hover:file:bg-black transition-all" required>
                    </div>
                </div>

                <button type="submit" class="title-font text-4xl bg-yellow-400 text-black py-6 border-4 border-black shadow-[10px_10px_0px_#f472b6] hover:bg-white hover:text-pink-600 hover:translate-y-2 hover:shadow-none transition-all italic">
                    ¡REGISTRAR OBRA!
                </button>
            </form>
        </div>

        <div class="flex justify-center mt-20 mb-10">
            <a href="../public/index.php" class="group relative inline-block transition-transform hover:scale-105 active:scale-95">
                <div class="absolute inset-0 bg-blue-600 translate-x-1.5 translate-y-1.5 group-hover:bg-pink-500 transition-colors"></div>
                <div class="relative bg-white border-2 border-black px-8 py-2 flex items-center gap-3">
                    <span class="text-pink-500 font-black text-sm">«</span>
                    <span class="font-black text-[11px] text-black uppercase tracking-[0.2em] italic">Volver al Inicio</span>
                    <span class="text-pink-500 font-black text-sm">»</span>
                </div>
            </a>
        </div>
    </main>

    <footer class="py-10 text-center opacity-30 text-[10px] font-black tracking-[0.5em] uppercase">
        &copy; 2026 Manga_verso • PANEL DE CREACIÓN
    </footer>

    <script>
        function createSakura() {
            const container = document.getElementById('sakura-container');
            for (let i = 0; i < 20; i++) {
                const petal = document.createElement('div');
                petal.className = 'sakura';
                const size = Math.random() * 8 + 5;
                petal.style.width = size + 'px';
                petal.style.height = size + 'px';
                petal.style.left = Math.random() * 100 + 'vw';
                petal.style.animationDuration = (Math.random() * 6 + 4) + 's';
                petal.style.animationDelay = Math.random() * 5 + 's';
                container.appendChild(petal);
            }
        }
        window.onload = createSakura;
    </script>
</body>
</html>