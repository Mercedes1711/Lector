<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include __DIR__ . '/../src/conexion_bd.php'; 

$error = ''; // Variable corregida para evitar el Warning de la captura 271

if (!isset($_SESSION['login_fails'])) {
    $_SESSION['login_fails'] = 0;
}

if (!empty($_SESSION['usuario'])) {
    header('Location: perfil.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario'] ?? '');
    $contraseña = $_POST['contraseña'] ?? '';

    if ($usuario === '' || $contraseña === '') {
        $error = 'Rellena todos los campos.';
    } else {
        $sql = "SELECT id, usuario, contraseña, email FROM usuarios WHERE usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usuario]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fila && password_verify($contraseña, $fila['contraseña'])) {
            $_SESSION['user_id'] = $fila['id'];
            $_SESSION['usuario'] = $fila['usuario'];
            $_SESSION['email'] = $fila['email'];
            $_SESSION['login_fails'] = 0;
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['login_fails']++;
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manga_verso - Acceso Clan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0f172a; margin: 0; overflow: hidden; color: white; }
        .manga-font { font-family: 'Bangers', cursive, sans-serif; letter-spacing: 0.05em; }

        /* Pétalos de Sakura */
        .sakura { position: absolute; background: #f472b6; border-radius: 100% 0% 100% 0%; opacity: 0.7; pointer-events: none; z-index: 1; animation: fall linear infinite; }
        @keyframes fall { 
            0% { transform: translateY(-10vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(110vh) rotate(360deg); opacity: 0; }
        }

        /* Líneas de acción */
        .speed-lines { position: absolute; top: 0; bottom: 0; width: 30%; background: repeating-linear-gradient(90deg, rgba(59, 130, 246, 0.08) 0px, rgba(59, 130, 246, 0.08) 1px, transparent 1px, transparent 30px); pointer-events: none; z-index: 0; }

        .side-panel-text { writing-mode: vertical-rl; text-orientation: upright; font-weight: 900; letter-spacing: 0.5em; opacity: 0.1; font-size: 5rem; user-select: none; }

        /* Panel Principal REDUCIDO */
        .manga-panel {
            background: white; border: 4px solid #000;
            box-shadow: 8px 8px 0px #3b82f6, 16px 16px 0px #f472b6; /* Sombras un poco más cortas */
            z-index: 20; position: relative; overflow: hidden;
        }

        .btn-manga { background: #0f172a; border: 2px solid #000; box-shadow: 4px 4px 0px #f472b6; transition: all 0.1s ease; }
        .btn-manga:active { transform: translate(2px, 2px); box-shadow: 2px 2px 0px #f472b6; }

        .illustration-box { border: 4px solid black; background: #f1f5f9; padding: 4px; box-shadow: 6px 6px 0px rgba(0,0,0,1); transition: all 0.3s ease; overflow: hidden; }
        .illustration-box img { display: block; width: 100%; height: auto; filter: grayscale(100%) contrast(110%) brightness(105%); }
        .illustration-box:hover { transform: scale(1.05) rotate(0deg) !important; z-index: 30; filter: none; }

        .error-message { background: #fee2e2; border: 2px solid #dc2626; color: #991b1b; padding: 8px; font-weight: bold; font-size: 12px; box-shadow: 3px 3px 0px #dc2626; margin-bottom: 15px; animation: shake 0.5s; }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center relative bg-slate-900">

    <div class="speed-lines left-0"></div>
    <div class="absolute left-0 top-0 bottom-0 flex items-center justify-center z-0 pointer-events-none">
        <div class="side-panel-text text-blue-500 hidden xl:block">マンガベルソ</div>
    </div>
    
    <div class="hidden lg:flex flex-col gap-8 absolute left-8 z-10">
        <div class="illustration-box -rotate-6 opacity-95">
        <img src="https://images.unsplash.com/photo-1618336753974-aae8e04506aa?q=80&w=400&auto=format&fit=crop" alt="Protagonista" class="w-40">
        </div>
        <div class="illustration-box rotate-3 opacity-90">
        <img src="https://images.unsplash.com/photo-1541562232579-512a21360020?q=80&w=400&auto=format&fit=crop" alt="Ojo" class="w-32">
        <div class="text-black font-black text-[10px] text-center mt-1 uppercase">EDICIÓN ESPECIAL</div>
        </div>
        <h3 class="manga-font text-5xl text-blue-400 -rotate-12 select-none">KA-BOOM!</h3>
    </div>

    <div class="speed-lines right-0 rotate-180"></div>
    <div class="absolute right-0 top-0 bottom-0 flex items-center justify-center z-0 pointer-events-none">
        <div class="side-panel-text text-pink-500 hidden xl:block">マンガベルソ</div>
    </div>

    <div class="hidden lg:flex flex-col items-end gap-8 absolute right-8 z-10">
        <h3 class="manga-font text-5xl text-pink-400 rotate-12 select-none">GOGOGO...</h3>
        <div class="illustration-box -rotate-3 opacity-90">
        <img src="https://images.unsplash.com/photo-1542332213-31f87348057f?q=80&w=400&auto=format&fit=crop" alt="Neo Tokyo" class="w-40">
        <div class="text-black font-black text-[10px] text-center mt-1 uppercase">PÁGINA FINAL</div>
        </div>
        <div class="illustration-box rotate-6 opacity-95">
        <img src="https://images.unsplash.com/photo-1528360983277-13d401cdc186?q=80&w=400&auto=format&fit=crop" alt="Samurai" class="w-32">
        </div>
    </div>

    <div id="sakura-container" class="absolute inset-0 z-1 pointer-events-none"></div>

    <div class="relative z-50 w-full max-w-[360px] px-6">
        
        <div class="text-center mb-5">
            <h1 class="manga-font text-6xl text-white italic transform -rotate-2 drop-shadow-[4px_4px_0px_#f472b6]">
                MANGA<span class="text-blue-500">_</span>VERSO
            </h1>
            <div class="bg-blue-600 text-white font-bold text-[9px] inline-block px-2 py-1 mt-2 tracking-widest uppercase">
                暁 de 読書 • LECTURA DEL ALBA
            </div>
        </div>

        <div class="manga-panel">
            <div class="p-6 md:p-8 relative"> <h2 class="text-lg font-black text-slate-900 mb-6 border-l-4 border-blue-600 pl-3 italic uppercase leading-none">
                    ACCESO AL CLAN
                </h2>

                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        ⚠ <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-4">
                    <div>
                        <label class="block text-[9px] font-black text-blue-600 uppercase mb-1 tracking-tighter">Usuario / ユーザー</label>
                        <input type="text" name="usuario" placeholder="TU NOMBRE" required
                        class="w-full p-2.5 border-2 border-slate-900 text-slate-900 font-bold text-sm focus:border-pink-500 outline-none rounded-none">
                    </div>

                    <div>
                        <label class="block text-[9px] font-black text-blue-600 uppercase mb-1 tracking-tighter">Password / パスワード</label>
                        <input type="password" name="contraseña" placeholder="••••••••" required
                        class="w-full p-2.5 border-2 border-slate-900 text-slate-900 font-bold text-sm focus:border-pink-500 outline-none rounded-none">
                    </div>

                    <div class="pt-1">
                        <button type="submit" class="btn-manga w-full py-3 text-white manga-font text-xl uppercase italic tracking-widest">
                            DESBLOQUEAR
                        </button>
                    </div>

                    <div class="text-center pt-3 border-t border-slate-100 flex flex-col gap-1">
                        <a href="crear_cuenta.php" class="text-[10px] font-black text-pink-500 hover:text-blue-600 underline">
                            REGISTRO
                        </a>
                        <a href="forgot_password.php" class="text-[10px] font-black text-pink-500 hover:text-blue-600 underline">
                            RECUPERAR CLAVE
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <p class="mt-6 text-center text-[9px] text-blue-700/50 font-bold uppercase tracking-[0.3em]">
            ADRIAN & MERCEDES 2026
            
        </p>
    
    
    </div>

    <script>
        function createSakura() {
            const container = document.getElementById('sakura-container');
            if (!container) return;
            for (let i = 0; i < 25; i++) {
                const petal = document.createElement('div');
                petal.className = 'sakura';
                petal.style.width = `${Math.random() * 6 + 4}px`;
                petal.style.height = `${Math.random() * 6 + 4}px`;
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