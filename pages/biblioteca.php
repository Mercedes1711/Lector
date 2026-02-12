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
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
        }
        .manga-font { font-family: 'Bangers', cursive; letter-spacing: 0.05em; }

        /* Efecto Sakura */
        .sakura {
            position: fixed;
            background: #f472b6;
            border-radius: 100% 0% 100% 0%;
            opacity: 0.6;
            pointer-events: none;
            z-index: 1;
            animation: fall linear infinite;
        }
        @keyframes fall {
            0% { transform: translateY(-10vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            100% { transform: translateY(110vh) rotate(360deg); opacity: 0; }
        }

        .manga-panel {
            background: white;
            border: 4px solid #000;
            box-shadow: 8px 8px 0px #3b82f6;
            color: #0f172a;
        }
        
        .tab-button {
            background: white;
            color: black;
            border: 3px solid #000;
            padding: 8px 16px;
            font-family: 'Bangers', cursive;
            font-size: 1.2rem;
            box-shadow: 4px 4px 0px #000;
            transition: 0.1s;
        }
        .tab-button.active {
            background: #3b82f6;
            color: white;
            box-shadow: 4px 4px 0px #f472b6;
            transform: translateY(-2px);
        }

        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body class="overflow-x-hidden">

    <div id="sakura-container" class="fixed inset-0 pointer-events-none z-0"></div>

    <header class="bg-white border-b-4 border-black p-3 relative z-50">
        <div class="container mx-auto flex justify-between items-center">
            <div>
                <h1 class="manga-font text-3xl text-black italic leading-none">
                    MANGA<span class="text-blue-500">_</span>VERSO
                </h1>
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Tu portal de manga</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-black text-white px-3 py-1 font-black text-[11px] border-2 border-black shadow-[2px_2px_0px_#f472b6]">
                    👤 <?= htmlspecialchars($_SESSION['usuario']); ?>
                </div>
                <a href="../public/logout.php" class="bg-red-600 text-white px-3 py-1 font-black text-[11px] border-2 border-black shadow-[2px_2px_0px_#000] hover:bg-red-700">
                    SALIR
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-6 relative z-10 flex-grow">
        <div class="text-center mb-6">
            <h2 class="manga-font text-5xl md:text-5xl italic drop-shadow-[3px_3px_0px_#3b82f6] text-white tracking-tight">
                📚 MI BIBLIOTECA
            </h2>
        </div>

        <div class="flex justify-center gap-4 mb-8">
            <button class="tab-button active" onclick="switchTab(event, 'mis-mangas')">Mis Mangas</button>
            <button class="tab-button" onclick="switchTab(event, 'biblioteca')">Mangas Agregados</button>
        </div>

        <div id="mis-mangas" class="tab-content active">
            <div class="manga-panel p-5 mb-8">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="flex flex-col">
                        <label class="text-[10px] font-black uppercase text-blue-600 mb-1">Categoría</label>
                        <select name="categoria" class="border-2 border-black p-1.5 font-bold text-black outline-none text-sm">
                            <option value="">Todas</option>
                            <?php
                            $stmt_cat = $conn->query("SELECT id, nombre FROM categorias ORDER BY nombre");
                            while ($cat = $stmt_cat->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='{$cat['id']}'>{$cat['nombre']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-[10px] font-black uppercase text-blue-600 mb-1">Buscar Título</label>
                        <input type="text" name="busqueda" placeholder="Ej: Naruto..." class="border-2 border-black p-1.5 font-bold text-black outline-none text-sm">
                    </div>
                    <div class="flex flex-col">
                        <label class="text-[10px] font-black uppercase text-blue-600 mb-1">Orden</label>
                        <select name="orden" class="border-2 border-black p-1.5 font-bold text-black outline-none text-sm">
                            <option value="fecha_subida DESC">Más recientes</option>
                            <option value="titulo ASC">A-Z</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-blue-600 text-white font-black py-2 border-2 border-black shadow-[3px_3px_0px_#000] text-xs">FILTRAR</button>
                        <a href="biblioteca.php" class="flex-1 bg-slate-200 text-black text-center font-black py-2 border-2 border-black shadow-[3px_3px_0px_#000] text-xs leading-loose">LIMPIAR</a>
                    </div>
                </form>
            </div>
            <p class="text-center text-slate-400 italic text-sm">No tienes mangas subidos aún.</p>
        </div>

        <div id="biblioteca" class="tab-content">
            <p class="text-center text-slate-400 italic text-sm">No hay mangas agregados en tu lista.</p>
        </div>

        <div class="flex justify-center mt-12 mb-10">
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

    <footer class="p-6 text-center opacity-30 text-[9px] font-black tracking-[0.5em] uppercase">
        MANGA_VERSO • 2026
    </footer>

    <script>
        function switchTab(event, tabName) {
            event.preventDefault();
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        function createSakura() {
            const container = document.getElementById('sakura-container');
            for (let i = 0; i < 15; i++) {
                const petal = document.createElement('div');
                petal.className = 'sakura';
                const size = Math.random() * 6 + 4;
                petal.style.width = `${size}px`;
                petal.style.height = `${size}px`;
                petal.style.left = `${Math.random() * 100}vw`;
                petal.style.animationDuration = `${Math.random() * 5 + 5}s`;
                petal.style.animationDelay = `${Math.random() * 5}s`;
                container.appendChild(petal);
            }
        }
        window.onload = createSakura;
    </script>
</body>
</html>