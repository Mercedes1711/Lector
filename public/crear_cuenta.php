<?php
session_start();
include __DIR__ . '/../src/conexion_bd.php';

$error = '';

if (!empty($_SESSION['usuario'])) {
    header('Location: perfil.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $contraseña = $_POST['contraseña'] ?? '';
    $correo = trim($_POST['correo'] ?? '');

    if ($usuario === '' || $contraseña === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'Rellena todos los campos correctamente.';
    } else if (strlen($contraseña) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } else if (!preg_match('/[0-9]/', $contraseña)) {
        $error = 'La contraseña debe incluir al menos un número.';
    } else {
        $sql = "SELECT id FROM usuarios WHERE usuario = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usuario, $correo]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $error = 'El usuario o correo ya está en uso.';
        } else {
            $hash = password_hash($contraseña, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (usuario, contraseña, email) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $ok = $stmt->execute([$usuario, $hash, $correo]);

            if ($ok) {
                $id = $conn->lastInsertId();
                $_SESSION['user_id'] = $id;
                $_SESSION['usuario'] = $usuario;
                $_SESSION['email'] = $correo;
                header('Location: index.php');
                exit;
            } else {
                $error = 'No se pudo crear la cuenta.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manga_verso - Unirse al Clan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0f172a; margin: 0; overflow-x: hidden; color: white; }
        .manga-font { font-family: 'Bangers', cursive; letter-spacing: 0.05em; }
        
        /* Pétalos de Sakura */
        .sakura { position: fixed; background: #f472b6; border-radius: 100% 0% 100% 0%; opacity: 0.7; pointer-events: none; z-index: 1; animation: fall linear infinite; }
        @keyframes fall { 0% { transform: translateY(-10vh) rotate(0deg); opacity: 0; } 10% { opacity: 1; } 100% { transform: translateY(110vh) rotate(360deg); opacity: 0; } }

        /* Líneas de acción */
        .speed-lines { position: absolute; top: 0; bottom: 0; width: 30%; background: repeating-linear-gradient(90deg, rgba(59, 130, 246, 0.08) 0px, rgba(59, 130, 246, 0.08) 1px, transparent 1px, transparent 30px); pointer-events: none; z-index: 0; }
        .side-panel-text { writing-mode: vertical-rl; text-orientation: upright; font-weight: 900; letter-spacing: 0.5em; opacity: 0.1; font-size: 5rem; user-select: none; }

        /* Ilustraciones */
        .illustration-box { border: 4px solid black; background: #f1f5f9; padding: 4px; box-shadow: 6px 6px 0px rgba(0,0,0,1); transition: all 0.3s ease; overflow: hidden; }
        .illustration-box img { display: block; width: 100%; filter: grayscale(100%) contrast(110%); }
        .illustration-box:hover { transform: scale(1.05) rotate(0deg) !important; z-index: 30; filter: none; }

        /* Panel Principal */
        .manga-panel { background: white; border: 4px solid #000; box-shadow: 12px 12px 0px #3b82f6, 24px 24px 0px #f472b6; z-index: 20; position: relative; }
        .btn-manga { background: #0f172a; border: 2px solid #000; box-shadow: 5px 5px 0px #f472b6; transition: all 0.1s ease; }
        .btn-manga:active { transform: translate(2px, 2px); box-shadow: 2px 2px 0px #f472b6; }

        .error-message { background: #fee2e2; border: 3px solid #dc2626; color: #991b1b; padding: 12px; font-weight: bold; box-shadow: 4px 4px 0px #dc2626; margin-bottom: 20px; animation: shake 0.5s; }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-10px); } 75% { transform: translateX(10px); } }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center relative bg-slate-900 py-10">

    <div id="sakura-container" class="fixed inset-0 z-0 pointer-events-none"></div>

    <div class="speed-lines left-0"></div>
    <div class="hidden lg:flex flex-col gap-8 absolute left-8 z-10">
        <div class="illustration-box -rotate-6 opacity-95">
             <img src="https://images.unsplash.com/photo-1618336753974-aae8e04506aa?q=80&w=400" alt="Manga" class="w-32">
        </div>
        <div class="illustration-box rotate-3 opacity-90">
             <img src="https://images.unsplash.com/photo-1541562232579-512a21360020?q=80&w=400" alt="Manga" class="w-28">
        </div>
        <h3 class="manga-font text-5xl text-blue-400 -rotate-12 select-none">POW!</h3>
    </div>

    <div class="speed-lines right-0 rotate-180"></div>
    <div class="hidden lg:flex flex-col items-end gap-8 absolute right-8 z-10">
        <h3 class="manga-font text-5xl text-pink-400 rotate-12 select-none">BOOM!</h3>
        <div class="illustration-box -rotate-3 opacity-90">
             <img src="https://images.unsplash.com/photo-1542332213-31f87348057f?q=80&w=400" alt="Manga" class="w-32">
        </div>
        <div class="illustration-box rotate-6 opacity-95">
             <img src="https://images.unsplash.com/photo-1528360983277-13d401cdc186?q=80&w=400" alt="Manga" class="w-28">
        </div>
    </div>

    <div class="relative z-50 w-full max-w-md px-6">
        <div class="text-center mb-6">
            <h1 class="manga-font text-7xl text-white italic transform -rotate-2 drop-shadow-[5px_5px_0px_#f472b6]">
                MANGA<span class="text-blue-500">_</span>VERSO
            </h1>
            <div class="bg-pink-600 text-white font-bold text-[10px] inline-block px-3 py-1 mt-3 tracking-[0.3em] uppercase">
                新 入 隊 • NUEVO RECLUTA
            </div>
        </div>

        <div class="manga-panel">
            <div class="p-8 relative">
                <h2 class="text-2xl font-black text-slate-900 mb-8 border-l-4 border-pink-600 pl-3 italic">UNIRSE AL CLAN</h2>

                <?php if (!empty($error)): ?>
                    <div class="error-message">⚠ <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="space-y-5">
                    <div>
                        <label class="block text-[10px] font-black text-pink-600 uppercase mb-1 tracking-widest">Usuario / ユーザー</label>
                        <input type="text" name="usuario" placeholder="TU NOMBRE DE GUERRERO" value="<?php echo isset($usuario) ? htmlspecialchars($usuario) : ''; ?>" required class="w-full p-3 border-2 border-slate-900 text-slate-900 font-bold focus:border-pink-500 outline-none rounded-none placeholder:text-slate-300">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-pink-600 uppercase mb-1 tracking-widest">Email / メール</label>
                        <input type="email" name="correo" placeholder="TU CORREO ELECTRÓNICO" value="<?php echo isset($correo) ? htmlspecialchars($correo) : ''; ?>" required class="w-full p-3 border-2 border-slate-900 text-slate-900 font-bold focus:border-pink-500 outline-none rounded-none placeholder:text-slate-300">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-pink-600 uppercase mb-1 tracking-widest">Password / パスワード</label>
                        <input type="password" name="contraseña" placeholder="••••••••" required class="w-full p-3 border-2 border-slate-900 text-slate-900 font-bold focus:border-pink-500 outline-none rounded-none placeholder:text-slate-300">
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="btn-manga w-full py-4 text-white manga-font text-2xl uppercase italic tracking-widest hover:brightness-110">ACTIVAR CUENTA</button>
                    </div>
                    <div class="text-center pt-4 border-t border-slate-100">
                        <a href="login.php" class="text-xs font-black text-pink-500 hover:text-blue-600 underline underline-offset-4 decoration-2">¿YA TIENES CUENTA? ACCEDER</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="flex justify-center mt-8">
            <a href="../public/index.php" class="group relative inline-block">
                <div class="absolute inset-0 bg-slate-700 translate-x-1 translate-y-1 group-hover:bg-blue-500 transition-colors"></div>
                <div class="relative bg-white border-2 border-black px-5 py-1.5 flex items-center gap-3 hover:-translate-x-0.5 hover:-translate-y-0.5 transition-transform active:translate-x-0 active:translate-y-0">
                    <span class="text-pink-500 font-black text-xs">«</span>
                    <span class="font-black text-[10px] text-black uppercase tracking-[0.2em] italic">Volver al Inicio</span>
                </div>
            </a>
        </div>

        <p class="mt-8 text-center text-[9px] text-pink-400/50 font-bold uppercase tracking-[0.4em]">MANGA_VERSO SELECT • VOL. 01 • 2026</p>
    </div>

    <script>
        function createSakura() {
            const container = document.getElementById('sakura-container');
            for (let i = 0; i < 25; i++) {
                const petal = document.createElement('div');
                petal.className = 'sakura';
                const size = Math.random() * 8 + 6;
                petal.style.width = size + 'px';
                petal.style.height = size + 'px';
                petal.style.left = Math.random() * 100 + 'vw';
                petal.style.animationDuration = (Math.random() * 7 + 4) + 's';
                petal.style.animationDelay = Math.random() * 5 + 's';
                container.appendChild(petal);
            }
        }
        window.onload = createSakura;
    </script>
</body>
</html>