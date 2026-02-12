<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit();
}

require_once __DIR__ . '/../src/conexion_bd.php';

// Obtener datos del usuario
try {
    $stmt = $conn->prepare('SELECT id, usuario, email, foto_perfil FROM usuarios WHERE id = ?;');
    $stmt->execute([intval($_SESSION['user_id'])]); 
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $_SESSION = array();
        session_destroy();
        header('Location: ../public/login.php');
        exit();
    }
} catch (PDOException $e) {
    error_log('Error al obtener datos del usuario: ' . $e->getMessage());
    $error = "Ha ocurrido un error al cargar tu perfil.";
}

// Manejo de carga de foto de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_perfil'])) {
   try {
        $archivo = $_FILES['foto_perfil'];
        $errores = [];

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $errores[] = "Error al subir el archivo.";
        } elseif (!in_array(mime_content_type($archivo['tmp_name']), ['image/jpeg', 'image/png', 'image/gif'])) {
            $errores[] = "Solo se permiten imágenes (JPEG, PNG, GIF).";
        } elseif ($archivo['size'] > 5 * 1024 * 1024) { 
            $errores[] = "La imagen no debe exceder 5MB.";
        }

        if (empty($errores)) {
            $upload_dir = __DIR__ . '/../img/perfiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $nombre_archivo = 'perfil_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
            $ruta_archivo = $upload_dir . $nombre_archivo;

            if (move_uploaded_file($archivo['tmp_name'], $ruta_archivo)) {
                $ruta_relativa = 'img/perfiles/' . $nombre_archivo;
                $stmt = $conn->prepare('UPDATE usuarios SET foto_perfil = ? WHERE id = ?');
                $stmt->execute([$ruta_relativa, $_SESSION['user_id']]);
                $usuario['foto_perfil'] = $ruta_relativa;
                $success = "¡Foto de batalla actualizada!";
            } else {
                $error = "No se pudo guardar la imagen.";
            }
        } else {
            $error = implode('<br>', $errores);
        }
    } catch (Exception $e) {
        error_log('Error al actualizar foto: ' . $e->getMessage());
        $error = "Error al actualizar la foto de perfil.";
    }
}

// Manejo de cambio de nombre de usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_usuario'])) {
    $nuevo_usuario = trim($_POST['nuevo_usuario'] ?? '');
    try {
        if (empty($nuevo_usuario) || strlen($nuevo_usuario) < 3) {
            $error = "Nombre demasiado corto.";
        } else {
            $stmt = $conn->prepare('SELECT id FROM usuarios WHERE usuario = ? AND id != ?');
            $stmt->execute([$nuevo_usuario, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $error = "Este nombre ya ha sido reclamado.";
            } else {
                $stmt = $conn->prepare('UPDATE usuarios SET usuario = ? WHERE id = ?');
                $stmt->execute([$nuevo_usuario, $_SESSION['user_id']]);
                $_SESSION['usuario'] = $nuevo_usuario;
                $usuario['usuario'] = $nuevo_usuario;
                $success = "¡Nuevo alias registrado!";
            }
        }
    } catch (PDOException $e) {
        $error = "Error al actualizar alias.";
    }
}

// Manejo de cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        $stmt = $conn->prepare('SELECT contraseña FROM usuarios WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user_data || !password_verify($current_password, $user_data['contraseña'])) {
            $error = "La contraseña actual es incorrecta.";
        } elseif ($new_password !== $confirm_password) {
            $error = "Las contraseñas no coinciden.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE usuarios SET contraseña = ? WHERE id = ?');
            $stmt->execute([$hashed_password, $_SESSION['user_id']]);
            $success = "¡Contraseña reforzada!";
        }
    } catch (PDOException $e) {
        $error = "Error en el servidor.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Manga_verso</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0f172a; color: white; min-height: 100vh; }
        .manga-font { font-family: 'Bangers', cursive; letter-spacing: 0.05em; }
        
        .manga-panel {
            background: white; border: 4px solid #000;
            box-shadow: 10px 10px 0px #3b82f6; color: #000;
        }

        .sakura {
            position: fixed; background: #f472b6; border-radius: 100% 0% 100% 0%;
            opacity: 0.7; pointer-events: none; z-index: 1; animation: fall linear infinite;
        }
        @keyframes fall {
            0% { transform: translateY(-10vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            100% { transform: translateY(110vh) rotate(360deg); opacity: 0; }
        }

        .profile-photo-big {
            border: 5px solid #000; box-shadow: 5px 5px 0px #f472b6;
            width: 150px; height: 150px; object-fit: cover;
        }
    </style>
</head>
<body class="overflow-x-hidden">

    <div id="sakura-container" class="fixed inset-0 pointer-events-none z-0"></div>

    <header class="bg-white border-b-4 border-black p-4 relative z-10 text-black">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="manga-font text-4xl italic transform -rotate-1">
                MANGA<span class="text-blue-500">_</span>VERSO
            </h1>
            <div class="flex gap-4">
                <a href="../public/index.php" class="bg-blue-500 text-white px-4 py-1 font-black border-2 border-black shadow-[3px_3px_0px_#000]">VOLVER</a>
                <a href="../public/logout.php" class="bg-red-500 text-white px-4 py-1 font-black border-2 border-black shadow-[3px_3px_0px_#000]">SALIR</a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-12 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="manga-panel p-8 text-center">
                    <div class="relative inline-block mb-6">
                        <?php if (!empty($usuario['foto_perfil']) && file_exists(__DIR__ . '/../' . $usuario['foto_perfil'])): ?>
                            <img src="../<?= htmlspecialchars($usuario['foto_perfil']); ?>" class="profile-photo-big rounded-full">
                        <?php else: ?>
                            <img src="../img/placeholder-avatar.png" class="profile-photo-big rounded-full">
                        <?php endif; ?>
                    </div>
                    
                    <h2 class="manga-font text-4xl mb-2"><?= htmlspecialchars($usuario['usuario']); ?></h2>
                    <p class="text-slate-500 font-bold mb-6 italic"><?= htmlspecialchars($usuario['email']); ?></p>

                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <label class="block">
                            <span class="sr-only">Elegir foto</span>
                            <input type="file" name="foto_perfil" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:border-2 file:border-black file:text-xs file:font-black file:bg-yellow-400 hover:file:bg-yellow-500">
                        </label>
                        <button type="submit" class="w-full bg-black text-white py-2 font-black uppercase border-2 border-black shadow-[4px_4px_0px_#f472b6]">Actualizar Foto</button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-8">
                
                <?php if (isset($error)): ?>
                    <div class="bg-red-500 text-white p-4 border-4 border-black font-black shadow-[4px_4px_0px_#000]">⚠️ <?= $error ?></div>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div class="bg-green-500 text-white p-4 border-4 border-black font-black shadow-[4px_4px_0px_#000]">✅ <?= $success ?></div>
                <?php endif; ?>

                <div class="manga-panel p-6">
                    <h3 class="manga-font text-3xl mb-4 italic text-blue-600">Cambiar Alias</h3>
                    <form method="POST" class="flex flex-col md:flex-row gap-4">
                        <input type="text" name="nuevo_usuario" placeholder="Nuevo nombre..." class="flex-1 border-2 border-black p-2 font-bold outline-none focus:ring-2 ring-blue-400">
                        <button type="submit" name="cambiar_usuario" class="bg-blue-500 text-white px-6 py-2 font-black border-2 border-black shadow-[4px_4px_0px_#000]">GUARDAR</button>
                    </form>
                </div>

                <div class="manga-panel p-6">
                    <h3 class="manga-font text-3xl mb-4 italic text-pink-500">Seguridad</h3>
                    <form method="POST" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="password" name="current_password" placeholder="Contraseña Actual" class="border-2 border-black p-2 font-bold outline-none" required>
                            <div class="hidden md:block"></div>
                            <input type="password" name="new_password" placeholder="Nueva Contraseña" class="border-2 border-black p-2 font-bold outline-none" required>
                            <input type="password" name="confirm_password" placeholder="Confirmar Nueva" class="border-2 border-black p-2 font-bold outline-none" required>
                        </div>
                        <button type="submit" name="cambiar_password" class="bg-black text-white px-8 py-3 font-black border-2 border-black shadow-[4px_4px_0px_#3b82f6] uppercase">Actualizar Contraseña</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-16">
            <h2 class="manga-font text-5xl mb-8 italic text-center drop-shadow-[3px_3px_0px_#f472b6]">MIS PUBLICACIONES</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php
                $stmt = $conn->prepare("SELECT m.id, m.titulo, m.portada FROM mangas m WHERE m.usuario_id = ? ORDER BY m.fecha_subida DESC");
                $stmt->execute([$_SESSION['user_id']]);
                $mangas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($mangas): foreach ($mangas as $m): ?>
                    <div class="bg-white border-4 border-black p-3 shadow-[6px_6px_0px_#000] group">
                        <div class="overflow-hidden mb-3">
                            <img src="../<?= htmlspecialchars($m['portada']); ?>" class="w-full h-64 object-cover grayscale group-hover:grayscale-0 transition-all duration-300" onerror="this.src='../img/placeholder.png'">
                        </div>
                        <h4 class="manga-font text-xl text-black truncate"><?= htmlspecialchars($m['titulo']); ?></h4>
                        <a href="biblioteca.php" class="block text-center bg-blue-500 text-white text-[10px] font-black py-1 mt-2 border-2 border-black shadow-[2px_2px_0px_#000]">GESTIONAR</a>
                    </div>
                <?php endforeach; else: ?>
                    <p class="col-span-full text-center opacity-50 italic">Aún no has compartido ninguna obra maestra.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        function createSakura() {
            const container = document.getElementById('sakura-container');
            for (let i = 0; i < 20; i++) {
                const petal = document.createElement('div');
                petal.className = 'sakura';
                const size = Math.random() * 10 + 5;
                petal.style.width = size + 'px';
                petal.style.height = size + 'px';
                petal.style.left = Math.random() * 100 + 'vw';
                petal.style.animationDuration = (Math.random() * 5 + 5) + 's';
                petal.style.animationDelay = Math.random() * 5 + 's';
                container.appendChild(petal);
            }
        }
        window.onload = createSakura;
    </script>
</body>
</html>