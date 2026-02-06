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
    $stmt = $conn->prepare('SELECT id, usuario, email, foto_perfil FROM usuarios WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
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

        // Validar archivo
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $errores[] = "Error al subir el archivo.";
        } elseif (!in_array(mime_content_type($archivo['tmp_name']), ['image/jpeg', 'image/png', 'image/gif'])) {
            $errores[] = "Solo se permiten imágenes (JPEG, PNG, GIF).";
        } elseif ($archivo['size'] > 5 * 1024 * 1024) { // 5MB máximo
            $errores[] = "La imagen no debe exceder 5MB.";
        }

        if (empty($errores)) {
            // Crear directorio si no existe
            $upload_dir = __DIR__ . '/../img/perfiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Generar nombre único
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $nombre_archivo = 'perfil_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
            $ruta_archivo = $upload_dir . $nombre_archivo;

            // Mover archivo
            if (move_uploaded_file($archivo['tmp_name'], $ruta_archivo)) {
                // Actualizar en base de datos
                $ruta_relativa = 'img/perfiles/' . $nombre_archivo;
                $stmt = $conn->prepare('UPDATE usuarios SET foto_perfil = ? WHERE id = ?');
                $stmt->execute([$ruta_relativa, $_SESSION['user_id']]);
                $usuario['foto_perfil'] = $ruta_relativa;
                $success = "Foto de perfil actualizada correctamente.";
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
        if (empty($nuevo_usuario)) {
            $error = "El nombre de usuario no puede estar vacío.";
        } elseif (strlen($nuevo_usuario) < 3) {
            $error = "El nombre de usuario debe tener al menos 3 caracteres.";
        } elseif (strlen($nuevo_usuario) > 20) {
            $error = "El nombre de usuario no puede exceder 20 caracteres.";
        } else {
            // Verificar que el nombre no esté en uso
            $stmt = $conn->prepare('SELECT id FROM usuarios WHERE usuario = ? AND id != ?');
            $stmt->execute([$nuevo_usuario, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $error = "Este nombre de usuario ya está en uso.";
            } else {
                // Actualizar el nombre de usuario
                $stmt = $conn->prepare('UPDATE usuarios SET usuario = ? WHERE id = ?');
                $stmt->execute([$nuevo_usuario, $_SESSION['user_id']]);
                $_SESSION['usuario'] = $nuevo_usuario;
                $usuario['usuario'] = $nuevo_usuario;
                $success = "Nombre de usuario actualizado correctamente.";
            }
        }
    } catch (PDOException $e) {
        error_log('Error al actualizar nombre de usuario: ' . $e->getMessage());
        $error = "No se pudo actualizar el nombre de usuario.";
    }
}

// Manejo de cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        // Obtener la contraseña actual desde la base de datos
        $stmt = $conn->prepare('SELECT contraseña FROM usuarios WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user_data) {
            $error = "Usuario no encontrado.";
        } elseif (!password_verify($current_password, $user_data['contraseña'])) {
            $error = "La contraseña actual es incorrecta.";
        } elseif ($new_password !== $confirm_password) {
            $error = "La nueva contraseña y la confirmación no coinciden.";
        } elseif (strlen($new_password) < 6) {
            $error = "La nueva contraseña debe tener al menos 6 caracteres.";
        } else {
            // Actualizar la contraseña
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE usuarios SET contraseña = ? WHERE id = ?');
            $stmt->execute([$hashed_password, $_SESSION['user_id']]);
            $success = "Contraseña actualizada correctamente.";
        }
    } catch (PDOException $e) {
        error_log('Error al actualizar contraseña: ' . $e->getMessage());
        $error = "No se pudo actualizar la contraseña.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/css/manga_verso.css">
    <title>Mi Perfil - Manga_verso</title>
</head>
<body>
    <header>
        <div>
            <h1>Manga_verso</h1>
            <p>Tu portal de manga</p>
        </div>
        <div class="auth-logged">
            <a class="user-link" href="perfil.php"><?php echo htmlspecialchars($usuario['usuario']); ?></a>
            <a class="logout-btn" href="../public/index.php">⬅ Volver</a>
            <a class="logout-btn" href="../public/logout.php">Cerrar sesión</a>
        </div>
    </header>

    <main class="profile-container">
        <div class="profile-card">
            <!-- FOTO DE PERFIL -->
            <div class="profile-header">
                <h2>Mi Perfil</h2>
                <div class="profile-photo-container">
                    <?php if (!empty($usuario['foto_perfil']) && file_exists(__DIR__ . '/../' . $usuario['foto_perfil'])): ?>
                        <img src="../<?= htmlspecialchars($usuario['foto_perfil']); ?>" alt="Foto de perfil" class="profile-photo">
                    <?php else: ?>
                        <img src="../img/placeholder-avatar.png" alt="Sin foto de perfil" class="profile-photo">
                    <?php endif; ?>
                </div>
                
                <!-- Formulario para cambiar foto -->
                <div class="profile-photo-upload">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="file" name="foto_perfil" accept="image/*" required>
                        <button type="submit">📸 Cambiar foto</button>
                    </form>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="form-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="form-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="profile-info">
                <div class="info-group">
                    <label>Usuario</label>
                    <p><?php echo htmlspecialchars($usuario['usuario']); ?></p>
                </div>
                <div class="info-group">
                    <label>Correo</label>
                    <p><?php echo htmlspecialchars($usuario['email']); ?></p>
                </div>
            </div>

            <hr>

            <div class="password-change">
                <h3>Cambiar Nombre de Usuario</h3>
                <form method="POST">
                    <div class="info-group">
                        <label>Nuevo nombre de usuario</label>
                        <input type="text" name="nuevo_usuario" placeholder="<?php echo htmlspecialchars($usuario['usuario']); ?>" minlength="3" maxlength="20" required>
                        <small style="color: #666;">Entre 3 y 20 caracteres</small>
                    </div>
                    <button class="btn-primary" type="submit" name="cambiar_usuario">Cambiar nombre de usuario</button>
                </form>
            </div>

            <hr>

            <div class="password-change">
                <h3>Cambiar Contraseña</h3>
                <form method="POST">
                    <div class="info-group">
                        <label>Contraseña actual</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="info-group">
                        <label>Nueva contraseña</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="info-group">
                        <label>Confirmar nueva contraseña</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button class="btn-primary" type="submit" name="cambiar_password">Actualizar Contraseña</button>
                </form>
            </div>
        </div>
    </main>

    <!-- SECCIÓN DE MIS MANGAS SUBIDOS -->
    <main class="profile-container">
        <div class="profile-card shared-mangas-section">
            <div class="shared-mangas-title">📚 Mangas Subidos</div>
            
            <?php
            try {
                $stmt = $conn->prepare("
                    SELECT m.id, m.titulo, m.descripcion, m.portada, m.fecha_subida, m.es_original,
                           COUNT(c.id) as total_capitulos, mc.activo, mc.fecha_comparticion
                    FROM mangas_compartidos mc
                    JOIN mangas m ON mc.manga_id = m.id
                    LEFT JOIN capitulos c ON m.id = c.manga_id
                    WHERE m.usuario_id = ?
                    GROUP BY m.id
                    ORDER BY mc.fecha_comparticion DESC
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $mis_mangas_compartidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if ($mis_mangas_compartidos) {
                    echo '<div class="mangas-grid">';
                    foreach ($mis_mangas_compartidos as $manga): ?>
                        <div class="manga-card">
                            <img src="../<?= htmlspecialchars($manga['portada']); ?>" 
                                 alt="<?= htmlspecialchars($manga['titulo']); ?>" 
                                 class="manga-card-img"
                                 onerror="this.src='../img/placeholder.png'">
                            <div class="manga-card-info">
                                <div class="status-badge <?= !$manga['activo'] ? 'inactive' : ''; ?>">
                                    <?= $manga['activo'] ? '✅ ACTIVO' : '🔒 INACTIVO'; ?>
                                </div>
                                <div class="manga-card-titulo"><?= htmlspecialchars($manga['titulo']); ?></div>
                                <div class="manga-card-capitulos">📖 <?= $manga['total_capitulos']; ?> capítulo(s)</div>
                                <a href="biblioteca.php?manga=<?= $manga['id']; ?>" style="display: block; margin-top: 10px; padding: 8px; background-color: #007bff; color: white; text-align: center; text-decoration: none; border-radius: 4px;">⚙️ Gestionar</a>
                            </div>
                        </div>
                    <?php endforeach;
                    echo '</div>';
                } else {
                    echo '<p style="text-align: center; color: #666; padding: 20px;">No has compartido ningún manga aún. <a href="biblioteca.php" style="color: #007bff;">Comparte uno ahora.</a></p>';
                }
            } catch (PDOException $e) {
                error_log('Error al obtener mangas compartidos: ' . $e->getMessage());
                echo '<p style="color: red;">Error al cargar tus mangas compartidos.</p>';
            }
            ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Manga_verso</p>
    </footer>
</body>
</html>
