<?php
session_start();
require __DIR__ . "/../src/conexion_bd.php";

$is_logged_in = !empty($_SESSION['usuario']) && !empty($_SESSION['user_id']);
$usuario_id = $is_logged_in ? $_SESSION['user_id'] : 0;
$nombre_usuario = $is_logged_in ? $_SESSION['usuario'] : 'INVITADO';

// Validar que venga el ID del manga
if (empty($_GET['manga']) || !is_numeric($_GET['manga'])) {
    die("Manga inválido. Ve a <a href='../public/index.php'>inicio</a>");
}
$manga_id = (int)$_GET['manga'];

// Obtener el manga compartido (no puede ser del usuario actual)
$stmt = $conn->prepare("
    SELECT m.*, u.usuario as autor
    FROM mangas m
    JOIN usuarios u ON m.usuario_id = u.id
    JOIN mangas_compartidos mc ON m.id = mc.manga_id
    WHERE m.id = ? AND mc.activo = 1 " . ($is_logged_in ? "AND m.usuario_id != ?" : "") . "
");
$exec_params = $is_logged_in ? [$manga_id, $usuario_id] : [$manga_id];
$stmt->execute($exec_params);
$manga = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$manga) {
    die("Este manga no está disponible o no tienes permiso para verlo. Ve a <a href='mangas_compartidos.php'>mangas compartidos</a>");
}

// Obtener todos los capítulos del manga
$stmt_chapters = $conn->prepare("SELECT id, titulo FROM capitulos WHERE manga_id = ? ORDER BY fecha_subida ASC");
$stmt_chapters->execute([$manga_id]);
$capitulos = $stmt_chapters->fetchAll(PDO::FETCH_ASSOC);

// Verificar si el manga está en la biblioteca del usuario
$stmt_biblioteca = $conn->prepare("SELECT id FROM biblioteca_usuario WHERE usuario_id = ? AND manga_id = ?");
$stmt_biblioteca->execute([$usuario_id, $manga_id]);
$en_biblioteca = $stmt_biblioteca->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viendo: <?= htmlspecialchars($manga['titulo']); ?> - Manga_verso</title>
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

        /* Tarjeta manga */
        .manga-card {
            background: white;
            border: 4px solid #000;
            box-shadow: 8px 8px 0px #000;
            transition: all 0.2s ease;
        }

        /* Botones */
        .btn-primary {
            background: #28a745;
            border: 2px solid #000;
            box-shadow: 4px 4px 0px #000;
            transition: all 0.1s ease;
            font-weight: 900;
            text-transform: uppercase;
            display: inline-block;
            padding: 12px 24px;
            color: white;
            text-decoration: none;
        }

        .btn-primary:hover {
            background: #218838;
        }

        .btn-primary:active {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0px #000;
        }

        .btn-danger {
            background: #dc3545;
            border: 2px solid #000;
            box-shadow: 4px 4px 0px #000;
            transition: all 0.1s ease;
            font-weight: 900;
            text-transform: uppercase;
            display: inline-block;
            padding: 12px 24px;
            color: white;
            text-decoration: none;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-danger:active {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0px #000;
        }

        .btn-secondary {
            background: #6b7280;
            border: 2px solid #000;
            box-shadow: 4px 4px 0px #000;
            transition: all 0.1s ease;
            font-weight: 900;
            text-transform: uppercase;
            display: inline-block;
            padding: 12px 24px;
            color: white;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-secondary:active {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0px #000;
        }

        .btn-leer {
            background: #3b82f6;
            border: 2px solid #000;
            box-shadow: 3px 3px 0px #000;
            transition: all 0.1s ease;
            font-weight: 900;
            text-transform: uppercase;
            padding: 8px 16px;
            color: white;
            text-decoration: none;
            display: inline-block;
            font-size: 12px;
        }

        .btn-leer:hover {
            background: #2563eb;
        }

        .btn-leer:active {
            transform: translate(1px, 1px);
            box-shadow: 2px 2px 0px #000;
        }

        /* Capítulo item */
        .chapter-item {
            background: white;
            border: 3px solid #000;
            box-shadow: 4px 4px 0px #000;
            transition: all 0.2s ease;
        }

        .chapter-item:hover {
            transform: translateX(3px);
            box-shadow: 2px 2px 0px #000;
        }
    </style>
</head>
<body>

    <!-- Contenedor de Sakura -->
    <div id="sakura-container" class="fixed inset-0 z-1 pointer-events-none"></div>

    <!-- Header -->
    <header class="manga-header relative z-40">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div>
                    <h1 class="manga-font text-4xl md:text-5xl italic transform -rotate-1 text-slate-900">
                        MANGA<span class="text-pink-500">_</span><span class="text-blue-500">VERSO</span>
                    </h1>
                    <p class="text-[9px] font-black text-slate-600 uppercase tracking-[0.3em] mt-1">Tu Portal de Manga</p>
                </div>

                <!-- Usuario y Logout -->
                <div class="flex items-center gap-3">
                    <?php if ($is_logged_in): ?>
                        <a href="perfil.php" class="user-badge px-4 py-2 text-white font-black text-sm uppercase">
                            👤 <?= htmlspecialchars($nombre_usuario); ?>
                        </a>
                        <a href="../public/logout.php" class="logout-btn px-4 py-2 text-white font-black text-sm uppercase">
                            ✕ SALIR
                        </a>
                    <?php else: ?>
                        <a href="../public/login.php?redirect=../pages/leer_manga_compartido.php?manga=<?= $manga_id ?>" class="bg-blue-600 border-2 border-black shadow-[3px_3px_0px_#f472b6] px-6 py-2 text-white font-black text-xs uppercase hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                            INICIAR SESIÓN
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-10 container mx-auto px-6 py-12">
        
        <!-- Badge superior -->
        <div class="text-center mb-8">
            <div class="bg-emerald-600 text-white font-bold text-[10px] inline-block px-4 py-2 tracking-[0.3em] uppercase border-2 border-black shadow-[4px_4px_0px_#000]">
                📢 MANGA COMPARTIDO
            </div>
        </div>

        <!-- Detalles del Manga -->
        <div class="manga-card p-8 mb-8 max-w-5xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Portada -->
                <div class="md:col-span-1">
                    <div class="border-4 border-black shadow-[6px_6px_0px_#000]">
                        <img src="../<?= htmlspecialchars($manga['portada']); ?>" 
                             alt="<?= htmlspecialchars($manga['titulo']); ?>"
                             onerror="this.src='../img/blog_placeholder.png'"
                             class="w-full">
                    </div>
                </div>

                <!-- Detalles -->
                <div class="md:col-span-2">
                    <h2 class="manga-font text-4xl md:text-5xl text-slate-900 mb-4 italic border-l-4 border-emerald-500 pl-3">
                        <?= strtoupper(htmlspecialchars($manga['titulo'])); ?>
                    </h2>

                    <div class="mb-4 flex items-center gap-2 text-slate-700">
                        <span class="font-black text-[10px] uppercase tracking-wider bg-slate-100 px-3 py-1 border-2 border-slate-900">
                            👤 Autor
                        </span>
                        <span class="font-bold text-lg"><?= htmlspecialchars($manga['autor']); ?></span>
                    </div>

                    <div class="mb-6">
                        <h3 class="font-black text-[10px] text-emerald-600 uppercase mb-2 tracking-widest">Sinopsis:</h3>
                        <p class="text-slate-700 font-semibold leading-relaxed">
                            <?= nl2br(htmlspecialchars($manga['descripcion'])); ?>
                        </p>
                    </div>

                    <!-- Info -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-slate-100 border-2 border-slate-900 p-4 text-center">
                            <div class="font-black text-[10px] text-slate-500 uppercase tracking-wider mb-1">Capítulos</div>
                            <div class="manga-font text-3xl text-slate-900 italic"><?= count($capitulos); ?></div>
                        </div>
                        <div class="bg-slate-100 border-2 border-slate-900 p-4 text-center">
                            <div class="font-black text-[10px] text-slate-500 uppercase tracking-wider mb-1">Compartido</div>
                            <div class="font-black text-sm text-slate-900"><?= date('d/m/Y', strtotime($manga['fecha_subida'])); ?></div>
                        </div>
                    </div>

                    <!-- Botón Biblioteca -->
                    <?php if ($is_logged_in): ?>
                        <form action="procesar_biblioteca.php" method="POST">
                            <input type="hidden" name="manga_id" value="<?= $manga_id; ?>">
                            <input type="hidden" name="referer" value="leer_manga_compartido.php">
                            <?php if ($en_biblioteca): ?>
                                <input type="hidden" name="accion" value="eliminar">
                                <button type="submit" class="btn-danger w-full text-sm">
                                    📚 Eliminar de mi Biblioteca
                                </button>
                            <?php else: ?>
                                <input type="hidden" name="accion" value="agregar">
                                <button type="submit" class="btn-primary w-full text-sm">
                                    📚 Agregar a mi Biblioteca
                                </button>
                            <?php endif; ?>
                        </form>
                    <?php else: ?>
                        <a href="../public/login.php?redirect=../pages/leer_manga_compartido.php?manga=<?= $manga_id ?>" class="btn-primary w-full text-sm text-center">
                            📚 Inicia sesión para guardar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Lista de Capítulos -->
        <div class="max-w-5xl mx-auto">
            <h3 class="manga-font text-4xl text-white italic mb-6">📖 CAPÍTULOS DISPONIBLES</h3>

            <?php if ($capitulos): ?>
                <div class="space-y-4">
                    <?php foreach ($capitulos as $cap): ?>
                        <div class="chapter-item p-4 flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="font-black text-slate-900 text-lg"><?= htmlspecialchars($cap['titulo']); ?></h4>
                            </div>
                            <div>
                                <a href="leer_capitulo_compartido.php?capitulo=<?= $cap['id']; ?>" class="btn-leer">
                                    📖 LEER
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="manga-card p-8 text-center">
                    <p class="text-slate-600 font-bold text-lg">No hay capítulos disponibles para este manga.</p>
                </div>
            <?php endif; ?>

            <!-- Navegación -->
            <div class="text-center mt-12 flex justify-center items-center gap-4">
                <div class="h-[2px] w-12 bg-slate-700"></div>
                <a href="mangas_compartidos.php" class="font-black text-[10px] text-slate-500 hover:text-emerald-400 transition-colors uppercase tracking-[0.4em]">
                    ⬅ Volver a Mangas Compartidos
                </a>
                <div class="h-[2px] w-12 bg-slate-700"></div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="relative z-10 text-center py-6 border-t-4 border-white/10">
        <p class="text-[9px] text-emerald-400/50 font-bold uppercase tracking-[0.4em]">
            MANGA_VERSO SELECT • VOL. 01 • 2026 • © ALL RIGHTS RESERVED
        </p>
    </footer>

    <script>
        function createSakura() {
            const container = document.getElementById('sakura-container');
            if (!container) return;
            
            const count = 20;
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
