<?php
session_start();
include __DIR__ . '/../src/conexion_bd.php'; // Tu conexión PDO
include __DIR__ . '/../src/correo.php';       // Tu función enviarCorreo()

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $message = "Por favor, introduce un correo válido.";
    } else {
        // Buscar usuario en la tabla 'usuarios'
        $stmt = $conn->prepare("SELECT id, usuario, email FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Crear tabla password_resets si no existe
            $conn->exec("
                CREATE TABLE IF NOT EXISTS password_resets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    token VARCHAR(100) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            // Generar token seguro
            $token = bin2hex(random_bytes(50));
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Guardar token
            $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
            $stmt->bindParam(':user_id', $user['id']);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expires_at', $expiry);
            $stmt->execute();

            // Preparar correo
            $reset_link = "http://localhost/dashboard/Lector/public/reset_password.php?token=" . $token;
            $asunto = "Restablece tu contraseña";
            $cuerpoHTML = "
                <p>Hola {$user['usuario']},</p>
                <p>Haz clic en este enlace para restablecer tu contraseña:</p>
                <p><a href='{$reset_link}'>Restablecer contraseña</a></p>
                <p>Este enlace expirará en 1 hora.</p>
            ";

            // Enviar correo solo si $user existe
            $resultado = enviarCorreo($user['email'], $user['usuario'], $asunto, $cuerpoHTML);

            // Registrar error si falla PHPMailer
            if (!$resultado['exito']) {
                error_log("Error PHPMailer: " . ($resultado['error_detalle'] ?? 'No disponible'));
            }

            // Redirigir automáticamente al login después de enviar enlace
            header("Location: login.php");
            exit;
        }

        // Mensaje genérico por seguridad (solo se mostraría si el correo no existe)
        $message = "Si tu correo está registrado, recibirás un enlace para restablecer la contraseña.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manga_verso - Recuperar Contraseña</title>
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

        /* Texto vertical de fondo */
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
            height: auto;
            filter: grayscale(100%) contrast(110%) brightness(105%);
        }
        
        .illustration-box:hover {
            transform: scale(1.05) rotate(0deg) !important;
            z-index: 30;
            filter: none;
        }

        /* Estilo para mensaje de error/info */
        .info-message {
            background: #dbeafe;
            border: 3px solid #3b82f6;
            color: #1e40af;
            padding: 12px;
            font-weight: bold;
            box-shadow: 4px 4px 0px #3b82f6;
            margin-bottom: 20px;
            animation: pulse 1s ease-in-out;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center relative bg-slate-900">

    <!-- Decoración Lateral Izquierda -->
    <div class="speed-lines left-0"></div>
    <div class="absolute left-0 top-0 bottom-0 flex items-center justify-center z-0 pointer-events-none">
        <div class="side-panel-text text-blue-500 hidden xl:block">
            マンガベルソ
        </div>
    </div>
    
    <!-- Elementos Izquierdos -->
    <div class="hidden lg:flex flex-col gap-8 absolute left-8 z-10">
        <!-- Imagen de Personaje Estilo Shonen -->
        <div class="illustration-box -rotate-6 opacity-95">
             <img src="https://images.unsplash.com/photo-1618336753974-aae8e04506aa?q=80&w=400&auto=format&fit=crop" alt="Protagonista de Manga" class="w-40">
        </div>
        <!-- Imagen de Ojo estilo Anime -->
        <div class="illustration-box rotate-3 opacity-90">
             <img src="https://images.unsplash.com/photo-1541562232579-512a21360020?q=80&w=400&auto=format&fit=crop" alt="Detalle de mirada manga" class="w-32">
             <div class="text-black font-black text-[10px] text-center mt-1 uppercase">RECUPERACIÓN</div>
        </div>
        <h3 class="manga-font text-5xl text-blue-400 -rotate-12 select-none">RESET!</h3>
    </div>

    <!-- Decoración Lateral Derecha -->
    <div class="speed-lines right-0 rotate-180"></div>
    <div class="absolute right-0 top-0 bottom-0 flex items-center justify-center z-0 pointer-events-none">
        <div class="side-panel-text text-pink-500 hidden xl:block">
            マンガベルソ
        </div>
    </div>

    <!-- Elementos Derechos -->
    <div class="hidden lg:flex flex-col items-end gap-8 absolute right-8 z-10">
        <h3 class="manga-font text-5xl text-pink-400 rotate-12 select-none">WHOOSH!</h3>
        <!-- Imagen de Ciudad estilo Cyberpunk/Manga -->
        <div class="illustration-box -rotate-3 opacity-90">
             <img src="https://images.unsplash.com/photo-1542332213-31f87348057f?q=80&w=400&auto=format&fit=crop" alt="Neo Tokyo" class="w-40">
             <div class="text-black font-black text-[10px] text-center mt-1 uppercase">NIVEL SECRETO</div>
        </div>
        <!-- Imagen de Katana/Arte Tinta -->
        <div class="illustration-box rotate-6 opacity-95">
             <img src="https://images.unsplash.com/photo-1528360983277-13d401cdc186?q=80&w=400&auto=format&fit=crop" alt="Samurai Art" class="w-32">
        </div>
    </div>

    <!-- Contenedor de Sakura -->
    <div id="sakura-container" class="absolute inset-0 z-1 pointer-events-none"></div>

    <!-- Contenedor Principal -->
    <div class="relative z-50 w-full max-w-md px-6">
        
        <!-- Logo -->
        <div class="text-center mb-6">
            <h1 class="manga-font text-7xl text-white italic transform -rotate-2 drop-shadow-[5px_5px_0px_#f472b6]">
                MANGA<span class="text-blue-500">_</span>VERSO
            </h1>
            <div class="bg-yellow-500 text-slate-900 font-bold text-[10px] inline-block px-3 py-1 mt-3 tracking-[0.3em] uppercase">
                回復 • RECUPERACIÓN
            </div>
        </div>

        <!-- Formulario -->
        <div class="manga-panel">
            <div class="p-8 md:p-10 relative">
                <h2 class="text-2xl font-black text-slate-900 mb-8 border-l-4 border-yellow-500 pl-3 italic">
                    RESTABLECER CONTRASEÑA
                </h2>

                <?php if (!empty($message)): ?>
                    <div class="info-message">
                        ⚡ <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-5">
                    <div>
                        <label class="block text-[10px] font-black text-yellow-600 uppercase mb-1 tracking-widest">Email / メール</label>
                        <input type="email" 
                               name="email" 
                               placeholder="TU CORREO ELECTRÓNICO" 
                               required
                               class="w-full p-3 border-2 border-slate-900 text-slate-900 font-bold focus:border-yellow-500 focus:ring-0 outline-none rounded-none placeholder:text-slate-300">
                        <small class="text-[9px] text-slate-600 block mt-1 font-semibold">Te enviaremos un enlace de recuperación</small>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn-manga w-full py-4 text-white manga-font text-2xl uppercase italic tracking-widest hover:brightness-110">
                            ENVIAR ENLACE
                        </button>
                    </div>

                    <div class="text-center pt-4 border-t border-slate-100">
                        <a href="login.php" class="text-xs font-black text-pink-500 hover:text-blue-600 underline underline-offset-4 decoration-2">
                            ← VOLVER AL LOGIN
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <p class="mt-8 text-center text-[9px] text-yellow-400/50 font-bold uppercase tracking-[0.4em]">
            MANGA_VERSO SELECT • VOL. 01 • 2026
        </p>
    </div>

    <script>
        function createSakura() {
            const container = document.getElementById('sakura-container');
            if (!container) return;
            
            const count = 30;
            for (let i = 0; i < count; i++) {
                const petal = document.createElement('div');
                petal.className = 'sakura';
                
                const size = Math.random() * 8 + 6;
                const left = Math.random() * 100;
                const duration = Math.random() * 7 + 4;
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