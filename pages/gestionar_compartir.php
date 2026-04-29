<?php
session_start();
require __DIR__ . "/../src/conexion_bd.php";

// Verificar sesión
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Validar que venga el ID del manga
if (empty($_GET['manga']) || !is_numeric($_GET['manga'])) {
    die("Manga inválido. Ve a <a href='../public/index.php'>inicio</a>");
}
$manga_id = (int)$_GET['manga'];

// Verificar que el manga pertenece al usuario logueado
$stmt = $conn->prepare("SELECT titulo, es_original FROM mangas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$manga_id, $usuario_id]);
$manga = $stmt->fetch(PDO::FETCH_ASSOC);

$error_acceso = "";
$mensaje = "";
$compartido = null;

if (!$manga) {
    $error_acceso = "No tienes permiso para gestionar este manga.";
} elseif (!$manga['es_original']) {
    $error_acceso = "❌ No puedes compartir este manga porque no está marcado como original.";
}

// Si hay error de acceso, saltamos el procesamiento de POST
if (!$error_acceso) {
    // Verificar si el manga ya está compartido
    $stmt_check = $conn->prepare("SELECT id, activo FROM mangas_compartidos WHERE manga_id = ?");
    $stmt_check->execute([$manga_id]);
    $compartido = $stmt_check->fetch(PDO::FETCH_ASSOC);

    // Procesar cambios
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'compartir' && !$compartido) {
            // Compartir el manga
            $stmt_share = $conn->prepare("INSERT INTO mangas_compartidos (manga_id, activo) VALUES (?, 1)");
            if ($stmt_share->execute([$manga_id])) {
                $mensaje = "✅ Manga compartido exitosamente!";
                $compartido = ['id' => $conn->lastInsertId(), 'activo' => 1];
            } else {
                $mensaje = "❌ Error al compartir el manga.";
            }
        } elseif ($accion === 'dejar_compartir' && $compartido) {
            // Dejar de compartir
            $stmt_unshare = $conn->prepare("DELETE FROM mangas_compartidos WHERE manga_id = ?");
            if ($stmt_unshare->execute([$manga_id])) {
                $mensaje = "✅ Manga removido de compartidos.";
                $compartido = null;
            } else {
                $mensaje = "❌ Error al remover el manga.";
            }
        } elseif ($accion === 'toggle' && $compartido) {
            // Activar/Desactivar compartir
            $nuevo_estado = $compartido['activo'] ? 0 : 1;
            $stmt_toggle = $conn->prepare("UPDATE mangas_compartidos SET activo = ? WHERE manga_id = ?");
            if ($stmt_toggle->execute([$nuevo_estado, $manga_id])) {
                $compartido['activo'] = $nuevo_estado;
                $estado_text = $nuevo_estado ? "activado" : "desactivado";
                $mensaje = "✅ Acceso al manga " . $estado_text . ".";
            } else {
                $mensaje = "❌ Error al actualizar el estado.";
            }
        }
    }

    // Re-verificar estado actual si fue borrado o creado
    if (!$compartido) {
        $stmt_check = $conn->prepare("SELECT id, activo FROM mangas_compartidos WHERE manga_id = ?");
        $stmt_check->execute([$manga_id]);
        $compartido = $stmt_check->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compartir: <?= htmlspecialchars($manga['titulo'] ?? 'Manga'); ?> - Manga_verso</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0f172a; color: white; min-height: 100vh; }
        .manga-font { font-family: 'Bangers', cursive; letter-spacing: 0.05em; }
        
        .sakura {
            position: fixed; background: #f472b6; border-radius: 100% 0% 100% 0%;
            opacity: 0.7; pointer-events: none; z-index: 1; animation: fall linear infinite;
        }
        @keyframes fall {
            0% { transform: translateY(-10vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            100% { transform: translateY(110vh) rotate(360deg); opacity: 0; }
        }

        .manga-panel {
            background: white; border: 4px solid #000;
            box-shadow: 10px 10px 0px #3b82f6; transition: 0.2s;
        }
        
        .status-card {
            border: 3px solid #000;
            transition: all 0.3s ease;
        }
        
        .btn-manga {
            border: 3px solid #000;
            box-shadow: 4px 4px 0px #000;
            transition: all 0.1s ease;
        }
        .btn-manga:active {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0px #000;
        }
    </style>
</head>
<body class="overflow-x-hidden">

    <div id="sakura-container" class="fixed inset-0 pointer-events-none z-0"></div>

    <header class="bg-white border-b-4 border-black p-4 relative z-50 text-black">
        <div class="container mx-auto flex justify-between items-center">
            <a href="../public/index.php">
                <h1 class="manga-font text-4xl italic transform -rotate-1">
                    MANGA<span class="text-blue-500">_</span>VERSO
                </h1>
            </a>
            <div class="flex items-center gap-4">
                <span class="hidden md:block font-black text-sm uppercase">HOLA, <?= htmlspecialchars($_SESSION['usuario']); ?></span>
                <a href="perfil.php" class="bg-yellow-400 p-2 border-2 border-black shadow-[3px_3px_0px_#000] hover:shadow-none transition-all">👤</a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-12 relative z-10">
        
        <div class="text-center mb-10">
            <h2 class="manga-font text-5xl md:text-6xl italic drop-shadow-[4px_4px_0px_#3b82f6] text-white">
                GESTIÓN DE COMPARTICIÓN
            </h2>
            <?php if ($manga): ?>
                <p class="font-black text-blue-400 uppercase tracking-widest mt-2">Manga: <?= htmlspecialchars($manga['titulo']); ?></p>
            <?php endif; ?>
        </div>

        <div class="max-w-3xl mx-auto">
            <?php if ($error_acceso): ?>
                <div class="manga-panel p-8 text-black text-center">
                    <span class="text-6xl block mb-4">🚫</span>
                    <h3 class="manga-font text-3xl mb-4"><?= htmlspecialchars($error_acceso); ?></h3>
                    <a href="../public/index.php" class="btn-manga bg-slate-900 text-white px-8 py-3 inline-block">Volver al inicio</a>
                </div>
            <?php else: ?>
                <?php if ($mensaje): ?>
                    <div class="manga-panel p-4 mb-8 text-black font-bold flex items-center gap-3 border-l-[12px] <?= strpos($mensaje, '✅') === 0 ? 'border-l-green-500' : 'border-l-red-500'; ?>">
                        <span class="text-2xl"><?= strpos($mensaje, '✅') === 0 ? '✨' : '❌'; ?></span>
                        <?= htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>

                <div class="manga-panel p-8 text-black">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                        <div>
                            <h3 class="manga-font text-3xl mb-4 border-b-4 border-blue-500 inline-block">ESTADO ACTUAL</h3>
                            
                            <?php if ($compartido && $compartido['activo']): ?>
                                <div class="status-card bg-green-50 p-6 rounded-none border-green-600">
                                    <div class="flex items-center gap-4 mb-2">
                                        <span class="text-4xl">🌍</span>
                                        <strong class="text-green-700 text-xl font-black uppercase">PÚBLICO</strong>
                                    </div>
                                    <p class="text-sm font-bold text-green-600">
                                        Este manga es visible para toda la comunidad en la sección de Mangas Compartidos.
                                    </p>
                                </div>
                            <?php elseif ($compartido && !$compartido['activo']): ?>
                                <div class="status-card bg-yellow-50 p-6 rounded-none border-yellow-600">
                                    <div class="flex items-center gap-4 mb-2">
                                        <span class="text-4xl">🔒</span>
                                        <strong class="text-yellow-700 text-xl font-black uppercase">PAUSADO</strong>
                                    </div>
                                    <p class="text-sm font-bold text-yellow-600">
                                        Está en la lista de compartidos pero el acceso está desactivado actualmente.
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="status-card bg-slate-100 p-6 rounded-none border-slate-600">
                                    <div class="flex items-center gap-4 mb-2">
                                        <span class="text-4xl">👤</span>
                                        <strong class="text-slate-700 text-xl font-black uppercase">PRIVADO</strong>
                                    </div>
                                    <p class="text-sm font-bold text-slate-600">
                                        Solo tú puedes ver este manga. Nadie más tiene acceso a él.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="bg-blue-50 border-4 border-dashed border-blue-200 p-6">
                            <h4 class="font-black text-blue-800 uppercase text-xs tracking-widest mb-3">¿Cómo funciona?</h4>
                            <ul class="space-y-2 text-xs font-bold text-blue-700">
                                <li>✨ Al compartir, otros usuarios podrán leer tus capítulos.</li>
                                <li>🚀 Aparecerá en la galería de "Comunidad".</li>
                                <li>🛠️ Puedes revocar el acceso en cualquier momento.</li>
                                <li>🔒 Solo los mangas marcados como "Originales" pueden compartirse.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-12 flex flex-col sm:flex-row gap-4 justify-center">
                        <?php if (!$compartido): ?>
                            <form method="POST" class="w-full sm:w-auto">
                                <input type="hidden" name="accion" value="compartir">
                                <button type="submit" class="btn-manga w-full bg-blue-500 text-white font-black px-8 py-4 uppercase tracking-tighter hover:bg-blue-600">
                                    📢 Compartir con la comunidad
                                </button>
                            </form>
                        <?php else: ?>
                            <?php if ($compartido['activo']): ?>
                                <form method="POST" class="w-full sm:w-auto">
                                    <input type="hidden" name="accion" value="toggle">
                                    <button type="submit" class="btn-manga w-full bg-yellow-400 text-black font-black px-8 py-4 uppercase tracking-tighter hover:bg-yellow-500">
                                        🔒 Desactivar Acceso
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" class="w-full sm:w-auto">
                                    <input type="hidden" name="accion" value="toggle">
                                    <button type="submit" class="btn-manga w-full bg-green-500 text-white font-black px-8 py-4 uppercase tracking-tighter hover:bg-green-600">
                                        🔓 Activar Acceso
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="POST" class="w-full sm:w-auto" onsubmit="return confirm('¿Dejar de compartir este manga?');">
                                <input type="hidden" name="accion" value="dejar_compartir">
                                <button type="submit" class="btn-manga w-full bg-red-500 text-white font-black px-8 py-4 uppercase tracking-tighter hover:bg-red-600">
                                    ❌ Dejar de compartir
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="flex flex-col sm:flex-row justify-center gap-6 mt-12">
                <a href="capitulos.php?manga=<?= $manga_id; ?>" class="group relative inline-block transition-transform hover:scale-105">
                    <div class="absolute inset-0 bg-slate-700 translate-x-1 translate-y-1"></div>
                    <div class="relative bg-white border-2 border-black px-6 py-2 text-black font-black text-xs uppercase italic">
                        ⬅ Volver a capítulos
                    </div>
                </a>
                <a href="../public/index.php" class="group relative inline-block transition-transform hover:scale-105">
                    <div class="absolute inset-0 bg-pink-500 translate-x-1 translate-y-1"></div>
                    <div class="relative bg-white border-2 border-black px-6 py-2 text-black font-black text-xs uppercase italic">
                        🏠 Ir al inicio
                    </div>
                </a>
            </div>
        </div>
    </main>

    <footer class="py-10 text-center opacity-30 text-[10px] font-black tracking-[0.5em] uppercase">
        &copy; 2026 Manga_verso • PANEL DE CONTROL
    </footer>

    <script>
        function createSakura() {
            const container = document.getElementById('sakura-container');
            for (let i = 0; i < 15; i++) {
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
