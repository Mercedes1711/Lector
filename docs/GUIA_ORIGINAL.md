# ✨ Etiqueta de Manga Original - Guía de Instalación

## 📋 Resumen de Cambios

Se ha agregado una funcionalidad para marcar mangas como **originales** (creación propia) durante su subida. Los mangas originales mostrarán una etiqueta especial **✨ Original**.

### 📂 Archivos Modificados

1. **`pages/subirManga.php`** - Agregado checkbox para marcar como original
2. **`pages/procesar_manga.php`** - Captura y guarda el valor de original
3. **`public/index.php`** - Muestra etiqueta "✨ Original" en mangas propios
4. **`pages/mangas_compartidos.php`** - Muestra etiqueta en mangas compartidos

### 📄 Archivo SQL Nuevo

- **`docs/AGREGAR_ORIGINAL.sql`** - Script para agregar la columna a la BD

---

## 🚀 Instalación

### Paso 1: Ejecutar Script SQL

Abre **PhpMyAdmin** y ve a tu base de datos `manga_verso`:

1. Haz clic en **"SQL"** en el menú superior
2. Copia y pega el contenido del archivo `docs/AGREGAR_ORIGINAL.sql`
3. Haz clic en **"Enviar"**

**O ejecuta directamente:**

```sql
ALTER TABLE `mangas` ADD COLUMN `es_original` TINYINT(1) NOT NULL DEFAULT 0 AFTER `categoria_id`;
```

---

## 📖 Cómo Usar

### Cuando Subes un Manga Nuevo

1. Ve a **Subir Manga**
2. Completa los campos normalmente
3. **Nuevo:** Verás un checkbox con la opción: ✨ **Este manga es de mi creación original**
4. Marca el checkbox si es tu manga original
5. Haz clic en **"Subir manga"**

### Visualización de la Etiqueta

**En Mis Mangas (página principal):**
- Mangas originales: Mostrarán una etiqueta **✨ Original** en color dorado
- Mangas no originales: Mostrarán la categoría normal

**En Mangas Compartidos:**
- Si es original: **✨ Original por [Autor]** en color dorado
- Si no es original: **👤 [Autor]** en color gris

---

## 🎨 Estilo de la Etiqueta

- **Color:** Dorado (#FFD700)
- **Ícono:** ✨ (destellos)
- **Ubicación:** Reemplaza la etiqueta de categoría en la vista de tarjeta

---

## 📌 Notas Importantes

✅ **Por defecto:** Si no marcas el checkbox, el manga se sube como "no original"  
✅ **Es opcional:** Puedes dejar mangas sin marcar como originales  
✅ **Visible en:** Tus mangas, mangas compartidos, y cuando los compartes  
✅ **No afecta:** La funcionalidad de compartir, subir capítulos o cualquier otra característica  

---

## ❓ Preguntas Frecuentes

### ¿Puedo cambiar el estado de original después?
En esta versión, no. Pero puedes eliminar y re-subir el manga si es necesario.

### ¿Qué significa "creación propia"?
Que el manga fue creado/escrito por ti, no que es una adaptación de una obra existente.

### ¿Se ve la etiqueta en los mangas compartidos?
Sí, cuando compartes un manga original, otros usuarios verán **✨ Original**

---

¡Listo! Los mangas originales ahora tienen su propia identidad. 🌟
