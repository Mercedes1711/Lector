<?php
session_start();
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
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
    <title>Manga_verso - Portal del Guerrero</title>
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

        /* Líneas de acción laterales */
        .speed-lines {
            position: fixed;
            top: 0;
            bottom: 0;
            width: 25%;
            background: repeating-linear-gradient(
                90deg,
                rgba(59, 130, 246, 0.05) 0px,
                rgba(59, 130, 246, 0.05) 1px,
                transparent 1px,
                transparent 30px
            );
            pointer-events: none;
            z-index: 0;
        }

        /* Tarjetas de menú estilo manga */
        .menu-card {
            background: white;
            border: 4px solid #000;
            box-shadow: 8px 8px 0px #000;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .menu-card::before {
            content: '';
            position: absolute;
            top: -2px;
            right: -2px;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 40px 40px 0;
            border-color: transparent #000 transparent transparent;
            z-index: 1;
        }

        .menu-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 36px 36px 0;
            z-index: 2;
        }

        .menu-card.cyan::after {
            border-color: transparent #06b6d4 transparent transparent;
        }

        .menu-card.verde::after {
            border-color: transparent #10b981 transparent transparent;
        }

        .menu-card.azul::after {
            border-color: transparent #3b82f6 transparent transparent;
        }

        .menu-card:hover {
            transform: translate(3px, 3px);
            box-shadow: 5px 5px 0px #000;
        }

        .menu-card:active {
            transform: translate(5px, 5px);
            box-shadow: 3px 3px 0px #000;
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
    </style>
</head>
<body>

    <!-- Decoración Lateral Izquierda -->
    <div class="speed-lines left-0"></div>
    
    <!-- Decoración Lateral Derecha -->
    <div class="speed-lines right-0 rotate-180"></div>

    <!-- Contenedor de Sakura -->
    <div id="sakura-container" class="fixed inset-0 z-1 pointer-events-none"></div>

    <!-- Header -->
    <header class="manga-header relative z-40">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div>
                    <h1 class="manga-font text-4xl md:text-5xl italic transform -rotate-1 text-blue-600">
    MANGA<span class="text-pink-500">_</span>VERSO
</h1>
                    <p class="text-[9px] font-black text-slate-600 uppercase tracking-[0.3em] mt-1">Tu Portal de Manga</p>
                </div>

                <!-- Usuario y Logout -->
                <div class="flex items-center gap-3">
                    <a href="../pages/perfil.php" class="user-badge px-4 py-2 text-white font-black text-sm uppercase">
                        👤 <?= htmlspecialchars($_SESSION['usuario']); ?>
                    </a>
                    <a href="logout.php" class="logout-btn px-4 py-2 text-white font-black text-sm uppercase">
                        ✕ SALIR
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-10 container mx-auto px-6 py-12 min-h-[calc(100vh-140px)]">
        
        <!-- Título de Bienvenida -->
        <div class="text-center mb-12">
            <h2 class="manga-font text-6xl md:text-7xl text-white mb-4 italic transform -rotate-1 drop-shadow-[4px_4px_0px_#f472b6]">
                BIENVENIDO, GUERRERO
            </h2>
            <div class="bg-blue-600 text-white font-bold text-[10px] inline-block px-4 py-2 tracking-[0.3em] uppercase border-2 border-black shadow-[4px_4px_0px_#000]">
                戦 士 の 道 • CAMINO DEL GUERRERO
            </div>
            <p class="text-slate-300 font-semibold mt-6 text-lg">
                Selecciona tu siguiente misión
            </p>
        </div>

        <!-- Tarjetas de Menú -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            
            <!-- Mi Biblioteca -->
            <a href="../pages/biblioteca.php" class="menu-card cyan group">
                <div class="p-8 relative z-10">
                    <div class="text-6xl mb-4 transform group-hover:scale-110 transition-transform">📚</div>
                    <h3 class="manga-font text-3xl text-slate-900 mb-3 italic">MI BIBLIOTECA</h3>
                    <div class="h-1 w-16 bg-cyan-500 mb-4"></div>
                    <p class="text-slate-600 font-semibold text-sm leading-relaxed">
                        Tus mangas y tu colección personal
                    </p>
                    <div class="mt-6 inline-block">
                        <span class="text-[9px] font-black text-cyan-600 uppercase tracking-widest px-3 py-1 border-2 border-cyan-600">
                            ACCEDER →
                        </span>
                    </div>
                </div>
            </a>

            <!-- Mangas Originales -->
            <a href="../pages/mangas_compartidos.php" class="menu-card verde group">
                <div class="p-8 relative z-10">
                    <div class="text-6xl mb-4 transform group-hover:scale-110 transition-transform">📢</div>
                    <h3 class="manga-font text-3xl text-slate-900 mb-3 italic">MANGAS ORIGINALES</h3>
                    <div class="h-1 w-16 bg-emerald-500 mb-4"></div>
                    <p class="text-slate-600 font-semibold text-sm leading-relaxed">
                        Descubre mangas originales de otros creadores
                    </p>
                    <div class="mt-6 inline-block">
                        <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest px-3 py-1 border-2 border-emerald-600">
                            EXPLORAR →
                        </span>
                    </div>
                </div>
            </a>

            <!-- Subir Manga -->
            <a href="../pages/subirManga.php" class="menu-card azul group">
                <div class="p-8 relative z-10">
                    <div class="text-6xl mb-4 transform group-hover:scale-110 transition-transform">⬆️</div>
                    <h3 class="manga-font text-3xl text-slate-900 mb-3 italic">SUBIR MANGA</h3>
                    <div class="h-1 w-16 bg-blue-500 mb-4"></div>
                    <p class="text-slate-600 font-semibold text-sm leading-relaxed">
                        Comparte tu creación con la comunidad
                    </p>
                    <div class="mt-6 inline-block">
                        <span class="text-[9px] font-black text-blue-600 uppercase tracking-widest px-3 py-1 border-2 border-blue-600">
                            PUBLICAR →
                        </span>
                    </div>
                </div>
            </a>

        </div>

        <!-- Onomatopeyas decorativas -->
        <div class="hidden lg:block">
            <h3 class="manga-font text-6xl text-blue-400/20 absolute left-12 bottom-24 -rotate-12 select-none">SLASH!</h3>
            <h3 class="manga-font text-6xl text-pink-400/20 absolute right-12 bottom-32 rotate-12 select-none">BOOM!</h3>
        </div>

    </main>

    <!-- Footer -->
    <footer class="relative z-10 text-center py-6 border-t-4 border-white/10">
        <p class="text-[9px] text-blue-400/50 font-bold uppercase tracking-[0.4em]">
            MANGA_VERSO SELECT • VOL. 01 • 2026 • &copy; ALL RIGHTS RESERVED
        </p>
    </footer>

    <script>
        function createSakura() {
            const container = document.getElementById('sakura-container');
            if (!container) return;
            
            const count = 25;
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
        window.onload = createSakura;
    </script>
</body>
</html>