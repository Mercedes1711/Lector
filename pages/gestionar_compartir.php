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

if (!$manga) {
    die("No tienes permiso para gestionar este manga.");
}

// Verificar que el manga es original
if (!$manga['es_original']) {
    die("❌ No puedes compartir este manga porque no está marcado como original. <a href='../public/index.php'>Volver al inicio</a>");
}

// Verificar si el manga ya está compartido
$stmt_check = $conn->prepare("SELECT id, activo FROM mangas_compartidos WHERE manga_id = ?");
$stmt_check->execute([$manga_id]);
$compartido = $stmt_check->fetch(PDO::FETCH_ASSOC);

$mensaje = "";

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

// Re-verificar estado actual
if (empty($compartido)) {
    $stmt_check = $conn->prepare("SELECT id, activo FROM mangas_compartidos WHERE manga_id = ?");
    $stmt_check->execute([$manga_id]);
    $compartido = $stmt_check->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../css/css/manga_verso.css">
<title>Compartir - <?= htmlspecialchars($manga['titulo']); ?></title>
</head>
<body>

<header>
    <div>
        <h1>Manga_verso</h1>
        <p>Tu portal de manga</p>
    </div>
    <div class="auth-logged">
        <a href="perfil.php"><?= htmlspecialchars($_SESSION['usuario']); ?></a>
        <a href="../public/logout.php">Cerrar sesión</a>
    </div>
</header>

<main>
    <h1>Compartir: <?= htmlspecialchars($manga['titulo']); ?></h1>
    
    <?php if ($mensaje): ?>
        <div class="mensaje" style="background-color: <?= strpos($mensaje, '✅') === 0 ? '#d4edda' : '#f8d7da'; ?>; color: <?= strpos($mensaje, '✅') === 0 ? '#155724' : '#721c24'; ?>;">
            <?= htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>
    
    <div class="share-container">
        <h2 style="text-align: center; margin-bottom: 30px;">Estado de compartición</h2>
        
        <?php if ($compartido && $compartido['activo']): ?>
            <div class="share-status active">
                <span class="share-status-icon">✅</span>
                <div>
                    <strong>Manga compartido</strong><br>
                    Este manga es visible para otros usuarios. ¡Otros pueden leerlo!
                </div>
            </div>
        <?php elseif ($compartido && !$compartido['activo']): ?>
            <div class="share-status inactive">
                <span class="share-status-icon">🔒</span>
                <div>
                    <strong>Manga no disponible</strong><br>
                    Has compartido este manga pero está desactivado. No es visible para otros.
                </div>
            </div>
        <?php else: ?>
            <div class="share-status inactive">
                <span class="share-status-icon">🔒</span>
                <div>
                    <strong>Manga privado</strong><br>
                    Solo tú puedes ver este manga. Compártelo para que otros usuarios lo lean.
                </div>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <strong>ℹ️ Información:</strong><br>
            Cuando compartes un manga, todos los usuarios registrados en Manga_verso podrán verlo y leerlo desde la sección de "Mangas Compartidos".
        </div>
        
        <div class="share-actions">
            <?php if (!$compartido): ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="accion" value="compartir">
                    <button type="submit" class="btn-share">📢 Compartir manga</button>
                </form>
            <?php else: ?>
                <?php if ($compartido['activo']): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="accion" value="toggle">
                        <button type="submit" class="btn-toggle">🔒 Desactivar acceso</button>
                    </form>
                <?php else: ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="accion" value="toggle">
                        <button type="submit" class="btn-toggle">🔓 Activar acceso</button>
                    </form>
                <?php endif; ?>
                
                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Dejar de compartir este manga?');">
                    <input type="hidden" name="accion" value="dejar_compartir">
                    <button type="submit" class="btn-unshare">❌ Dejar de compartir</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="capitulos.php?manga=<?= $manga_id; ?>" class="btn-secondary">⬅ Volver a capítulos</a>
        <a href="../public/index.php" class="btn-secondary" style="margin-left: 10px;">🏠 Ir al inicio</a>
    </div>
</main>

<footer>
    <p>&copy; 2025 Manga_verso</p>
</footer>
</body>
</html>
