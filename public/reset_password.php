<?php
session_start();
include __DIR__ . '/../src/conexion_bd.php'; // Conexión PDO

$message = '';
$show_form = false;

// Comprobar si existe el token en la URL
$token = $_GET['token'] ?? '';

if (!$token) {
    $message = "Token inválido o no proporcionado.";
} else {
    // Buscar token válido en password_resets
    $stmt = $conn->prepare("
        SELECT pr.id AS reset_id, pr.user_id, pr.expires_at, u.usuario
        FROM password_resets pr
        JOIN usuarios u ON u.id = pr.user_id
        WHERE pr.token = :token
    ");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        $message = "Token inválido o ya utilizado.";
    } elseif (strtotime($reset['expires_at']) < time()) {
        $message = "El token ha expirado. Por favor, solicita uno nuevo.";
    } else {
        $show_form = true; // Mostrar formulario para nueva contraseña
    }
}

// Procesar formulario al enviar nueva contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $show_form) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($password) || empty($password_confirm)) {
        $message = "Todos los campos son obligatorios.";
    } elseif (strlen($password) < 8) {
        $message = "La contraseña debe tener al menos 8 caracteres.";
    } elseif ($password !== $password_confirm) {
        $message = "Las contraseñas no coinciden.";
    } else {
        // Hashear la nueva contraseña
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        // Actualizar la contraseña en usuarios
        $stmt = $conn->prepare("UPDATE usuarios SET contraseña = :password WHERE id = :user_id");
        $stmt->bindParam(':password', $password_hashed);
        $stmt->bindParam(':user_id', $reset['user_id']);
        $stmt->execute();

        // Borrar el token para que no se pueda reutilizar
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE id = :reset_id");
        $stmt->bindParam(':reset_id', $reset['reset_id']);
        $stmt->execute();

        // Redirigir al login después de restablecer contraseña con éxito
        $_SESSION['exito'] = "Contraseña actualizada. Ahora puedes entrar.";
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manga_verso - Nueva Contraseña</title>
    <!-- Carga de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
            margin: 0;
            overflow: hidden;
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
            position: absolute;
            top: 0;
            bottom: 0;
            width: 30%;
            background: repeating-linear-gradient(
                90deg,
                rgba(59, 130, 246, 0.08) 0px,
                rgba(59, 130, 246, 0.08) 1px,
                transparent 1px,
                transparent 30px
            );
            pointer-events: none;
            z-index: 0;
        }

        .side-panel-text {
            writing-mode: vertical-rl;
            text-orientation: upright;
            font-weight: 900;
            letter-spacing: 0.5em;
            opacity: 0.1;
            font-size: 5rem;
            user-select: none;
        }

        /* Panel Principal */
        .manga-panel {
            background: white;
            border: 4px solid #000;
            box-shadow: 12px 12px 0px #3b82f6, 24px 24px 0px #f472b6;
            z-index: 20;
            position: relative;
            overflow: hidden;
        }

        .btn-manga {
            background: #0f172a;
            border: 2px solid #000;
            box-shadow: 5px 5px 0px #f472b6;
            transition: all 0.1s ease;
        }

        .btn-manga:active {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0px #f472b6;
        }

        .illustration-box {
            border: 4px solid black;
            background: #f1f5f9;
            padding: 4px;
            box-shadow: 6px 6px 0px rgba(0,0,0,1);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .illustration-box img {
            display: block;
            width: 100%;
            filter: grayscale(100%) contrast(110%) brightness(105%);
        }
        
        .illustration-box:hover {
            transform: scale(1.05) rotate(0deg) !important;
            z-index: 30;
            filter: none;
        }

        .alert-box {
            background: #fee2e2;
            border: 3px solid #dc2626;
            color: #b91c1c;
            padding: 12px;
            font-weight: bold;
            box-shadow: 4px 4px 0px #dc2626;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .success-box {
            background: #dcfce7;
            border: 3px solid #16a34a;
            color: #166534;
            padding: 12px;
            font-weight: bold;
            box-shadow: 4px 4px 0px #16a34a;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center relative bg-slate-900">

    <div class="speed-lines left-0"></div>
    <div class="absolute left-0 top-0 bottom-0 flex items-center justify-center z-0 pointer-events-none">
        <div class="side-panel-text text-blue-500 hidden xl:block">マンガベルソ</div>
    </div>
    
    <div class="hidden lg:flex flex-col gap-8 absolute left-8 z-10">
        <div class="illustration-box -rotate-6 opacity-95">
             <img src="https://images.unsplash.com/photo-1618336753974-aae8e04506aa?q=80&w=400" alt="Manga" class="w-40">
        </div>
        <h3 class="manga-font text-5xl text-blue-400 -rotate-12 select-none">UPDATE!</h3>
    </div>

    <div class="speed-lines right-0 rotate-180"></div>
    <div class="absolute right-0 top-0 bottom-0 flex items-center justify-center z-0 pointer-events-none">
        <div class="side-panel-text text-pink-500 hidden xl:block">マンガベルソ</div>
    </div>

    <div id="sakura-container" class="absolute inset-0 z-1 pointer-events-none"></div>

    <div class="relative z-50 w-full max-w-md px-6">
        
        <div class="text-center mb-6">
            <h1 class="manga-font text-7xl text-white italic transform -rotate-2 drop-shadow-[5px_5px_0px_#f472b6]">
                MANGA<span class="text-blue-500">_</span>VERSO
            </h1>
            <div class="bg-blue-600 text-white font-bold text-[10px] inline-block px-3 py-1 mt-3 tracking-[0.3em] uppercase">
                パスワード更新 • ACTUALIZAR CLAN
            </div>
        </div>

        <div class="manga-panel">
            <div class="p-8 md:p-10 relative">
                <h2 class="text-2xl font-black text-slate-900 mb-8 border-l-4 border-blue-600 pl-3 italic">
                    NUEVA CONTRASEÑA
                </h2>

                <?php if ($message): ?>
                    <div class="<?= $show_form ? 'alert-box' : 'success-box' ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_form): ?>
                    <form method="POST" action="" class="space-y-5">
                        <div class="mb-4">
                            <p class="text-[10px] text-slate-500 font-bold uppercase mb-2">Restableciendo cuenta de: <span class="text-blue-600"><?= htmlspecialchars($reset['usuario']) ?></span></p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-blue-600 uppercase mb-1 tracking-widest">Nueva Clave / 新パスワード</label>
                            <input type="password" 
                                   name="password" 
                                   placeholder="AL MENOS 8 CARACTERES" 
                                   required
                                   class="w-full p-3 border-2 border-slate-900 text-slate-900 font-bold focus:border-blue-600 outline-none rounded-none placeholder:text-slate-300">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-blue-600 uppercase mb-1 tracking-widest">Repetir Clave / 確認</label>
                            <input type="password" 
                                   name="password_confirm" 
                                   placeholder="CONFIRMA TU CLAVE" 
                                   required
                                   class="w-full p-3 border-2 border-slate-900 text-slate-900 font-bold focus:border-blue-600 outline-none rounded-none placeholder:text-slate-300">
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="btn-manga w-full py-4 text-white manga-font text-2xl uppercase italic tracking-widest">
                                ACTUALIZAR CLAN
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="text-center pt-4">
                        <a href="forgot_password.php" class="text-xs font-black text-pink-500 hover:text-blue-600 underline underline-offset-4 decoration-2">
                            SOLICITAR OTRO ENLACE
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <p class="mt-8 text-center text-[9px] text-blue-400/50 font-bold uppercase tracking-[0.4em]">
            MANGA_VERSO SELECT • VOL. 01 • 2026
        </p>
    </div>

    <script>
        function createSakura() {
            const container = document.getElementById('sakura-container');
            if (!container) return;
            for (let i = 0; i < 30; i++) {
                const petal = document.createElement('div');
                petal.className = 'sakura';
                const size = Math.random() * 8 + 6;
                petal.style.width = `${size}px`;
                petal.style.height = `${size}px`;
                petal.style.left = `${Math.random() * 100}vw`;
                petal.style.animationDuration = `${Math.random() * 7 + 4}s`;
                petal.style.animationDelay = `${Math.random() * 5}s`;
                container.appendChild(petal);
            }
        }
        window.onload = createSakura;
    </script>
</body>
</html>
