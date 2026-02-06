# 📚 Mi Biblioteca - Guía de Instalación

## 📋 Resumen de Cambios

Se ha implementado la funcionalidad de **Mi Biblioteca**, que permite a los usuarios agregar mangas compartidos de otros creadores a su biblioteca personal.

### 📂 Archivos Nuevos Creados

1. **`pages/procesar_biblioteca.php`** - Script para procesar agregar/eliminar de biblioteca
2. **`pages/mi_biblioteca.php`** - Página para ver todos los mangas agregados a la biblioteca

### 📄 Archivos Modificados

1. **`pages/leer_manga_compartido.php`** - Agregado botón "Agregar a mi biblioteca"
2. **`public/index.php`** - Nuevo botón "📖 Mi biblioteca" en el menú principal

### 📊 Archivo SQL Nuevo

- **`docs/AGREGAR_BIBLIOTECA.sql`** - Script para crear la tabla de biblioteca

---

## 🚀 Instalación

### Paso 1: Ejecutar Script SQL

Abre **PhpMyAdmin** y ve a tu base de datos `manga_verso`:

1. Haz clic en **"SQL"** en el menú superior
2. Copia y pega el contenido del archivo `docs/AGREGAR_BIBLIOTECA.sql`
3. Haz clic en **"Enviar"**

**O ejecuta directamente:**

```sql
CREATE TABLE IF NOT EXISTS `biblioteca_usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `manga_id` int(11) NOT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_manga` (`usuario_id`, `manga_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `manga_id` (`manga_id`),
  CONSTRAINT `biblioteca_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `biblioteca_usuario_ibfk_2` FOREIGN KEY (`manga_id`) REFERENCES `mangas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

---

## 📖 Cómo Usar

### Agregar Manga a Mi Biblioteca

1. Ve a **"Mangas compartidos"** en el menú principal
2. Encuentra un manga que te interese
3. Haz clic en **"Leer"** para ver los detalles
4. En la página del manga, haz clic en **"📚 Agregar a mi biblioteca"**
5. ✅ El manga aparecerá en tu biblioteca

### Ver Mi Biblioteca

1. En el menú principal, haz clic en **"📖 Mi biblioteca"**
2. Verás todos los mangas que has agregado
3. Desde aquí puedes:
   - **"📖 Leer"** - Leer el manga
   - **"🗑️ Eliminar"** - Remover de tu biblioteca

### Eliminar de Mi Biblioteca

**Opción 1:** Desde la página del manga
- Haz clic en **"📚 Eliminar de mi biblioteca"**

**Opción 2:** Desde Mi Biblioteca
- Haz clic en el botón **"🗑️ Eliminar"**

---

## 🎯 Características

✅ **Agregar mangas**: Todos los mangas compartidos pueden agregarse  
✅ **Organización personal**: Tu biblioteca es privada y personal  
✅ **Acceso rápido**: Todos tus mangas guardados en un solo lugar  
✅ **Información completa**: Ve el autor y si es original  
✅ **Lectura directa**: Lee directamente desde tu biblioteca  
✅ **Eliminar**: Quita mangas cuando quieras  

---

## 📌 Notas Importantes

- **No puedes duplicados**: No puedes agregar el mismo manga dos veces
- **Acceso privado**: Solo tú ves tu biblioteca
- **Independiente**: Agregar a biblioteca no afecta el manga compartido original
- **Sincronización**: Cuando el autor actualiza capítulos, ves las nuevas versiones
- **Sin límite**: Puedes agregar ilimitados mangas a tu biblioteca

---

## 🎨 Vista de la Biblioteca

- **Tarjetas con portada**: Cada manga muestra su portada y detalles
- **Información del autor**: Ves quién creó el manga
- **Badge de original**: Se marca con ✨ si es un manga original
- **Cantidad de capítulos**: Ves cuántos capítulos tiene cada manga
- **Acciones rápidas**: Botones para leer o eliminar

---

## ❓ Preguntas Frecuentes

### ¿Perderé el manga si el autor lo descomparte?
No, pero no podrás leer nuevos capítulos si ya no está compartido.

### ¿Puedo compartir mi biblioteca con otros?
No, tu biblioteca es privada.

### ¿Se notifica al autor cuando agrego su manga?
No, es una acción silenciosa.

### ¿Hay límite de mangas en la biblioteca?
No, puedes agregar todos los que quieras.

### ¿Qué pasa si elimino un manga de mi biblioteca?
Solo se elimina de tu lista personal, no se borra el manga original.

---

¡Listo! Ya puedes crear tu propia biblioteca de mangas. 📚✨
