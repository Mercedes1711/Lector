# 📚 Sistema de Compartir Mangas - Guía de Instalación

## ✅ Cambios Realizados

Se ha implementado un sistema completo de compartir mangas en tu plataforma Manga_verso.

### 📂 Nuevos Archivos Creados

1. **`pages/gestionar_compartir.php`** - Página para activar/desactivar el compartir de un manga
2. **`pages/mangas_compartidos.php`** - Página principal donde se ven todos los mangas compartidos
3. **`pages/leer_manga_compartido.php`** - Página para ver detalles de un manga compartido
4. **`pages/leer_capitulo_compartido.php`** - Página para leer capítulos de mangas compartidos
5. **`docs/INSTALAR_COMPARTIR.sql`** - Script SQL para crear la tabla necesaria

### 🔧 Cambios en Archivos Existentes

1. **`public/index.php`** - Agregado botón "Mangas compartidos" en el menú principal
2. **`pages/capitulos.php`** - Agregado botón "Compartir manga" en la barra de herramientas
3. **`docs/manga_verso.sql`** - Actualizado con la nueva tabla `mangas_compartidos`

---

## 🚀 Instrucciones de Instalación

### Paso 1: Crear la tabla en la base de datos

Abre **PhpMyAdmin** y ve a tu base de datos `manga_verso`:

1. Haz clic en **"SQL"** en el menú superior
2. Copia y pega el contenido del archivo `docs/INSTALAR_COMPARTIR.sql`
3. Haz clic en **"Enviar"** o presiona **Ctrl + Enter**

**Alternativa rápida:**
Ejecuta este SQL directamente:

```sql
CREATE TABLE IF NOT EXISTS `mangas_compartidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manga_id` int(11) NOT NULL,
  `fecha_comparticion` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `manga_id` (`manga_id`),
  KEY `activo` (`activo`),
  CONSTRAINT `mangas_compartidos_ibfk_1` FOREIGN KEY (`manga_id`) REFERENCES `mangas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

---

## 📖 Cómo Usar

### Para Compartir un Manga (Autor)

1. Inicia sesión en tu cuenta
2. Ve a **"Mis mangas"** en la página principal
3. Haz clic en **"Ver manga"** en el manga que deseas compartir
4. En la página de capítulos, verás el nuevo botón **"📢 Compartir manga"**
5. Haz clic y serás redirigido a la página de gestión
6. Haz clic en **"Compartir manga"** para hacerlo público
7. ¡Listo! Tu manga ahora es visible para otros usuarios

### Para Desactivar/Activar la Compartición

En la página **Gestionar Compartir**:
- **Desactivar acceso**: Si el manga está compartido, puedes desactivarlo temporalmente
- **Activar acceso**: Si está desactivado, puedes activarlo de nuevo
- **Dejar de compartir**: Elimina completamente la compartición

### Para Ver Mangas Compartidos (Lector)

1. En la página principal, verás el nuevo botón **"📢 Mangas compartidos"**
2. Haz clic para ver:
   - **Tus mangas compartidos** (arriba) - Con estado ACTIVO/INACTIVO
   - **Mangas de otros usuarios** (abajo) - Todos los mangas que otros han compartido
3. Haz clic en **"Leer"** para ver un manga compartido
4. Verás la página del manga con todos sus capítulos disponibles
5. Haz clic en **"Leer"** en un capítulo para verlo

---

## 🔐 Características de Seguridad

✅ **Control de acceso**: Solo los usuarios autenticados pueden ver mangas compartidos  
✅ **Permisos**: Los autores solo pueden gestionar sus propios mangas  
✅ **Privacidad**: Puedes activar/desactivar la compartición en cualquier momento  
✅ **Integridad**: Si eliminas un manga, se elimina automáticamente de compartidos  

---

## 🎯 Flujo de Mangas

```
Autor sube manga privado
         ↓
   Autor puede compartirlo
         ↓
Otros usuarios ven "Mangas compartidos"
         ↓
Otros usuarios pueden leer capítulos
         ↓
Autor puede desactivar en cualquier momento
```

---

## 📌 Notas Importantes

- Cuando un manga es **compartido**, es visible para **TODOS los usuarios registrados**
- Puedes compartir/descompartir desde **Gestionar Compartir** en cualquier momento
- Los capítulos solo se comparten si el **manga principal está activo**
- Los autores siempre ven sus mangas en "Mis mangas compartidos"
- Otros usuarios ven el nombre del autor en cada manga compartido

---

## ❓ Preguntas Frecuentes

### ¿Qué pasa si elimino un manga?
Se elimina del sistema completamente, incluyendo de los compartidos.

### ¿Puedo compartir el manga de otro usuario?
No, solo puedes compartir tus propios mangas.

### ¿Pueden editar mis capítulos si los comparto?
No, solo pueden leerlos. La edición siempre es exclusiva del autor.

### ¿Se notifica a los usuarios cuando comparto un manga?
En esta versión, no. Pero pueden verlo en la sección "Mangas compartidos".

---

## 🆘 Troubleshooting

### "La tabla mangas_compartidos no existe"
→ Ejecuta el script SQL en PhpMyAdmin

### "No ves el botón de compartir"
→ Asegúrate de haber actualizado `pages/capitulos.php`

### "Error al compartir"
→ Verifica que la BD tenga la tabla creada correctamente

---

¡Listo! Tu sistema de compartir mangas está operativo. 🎉
