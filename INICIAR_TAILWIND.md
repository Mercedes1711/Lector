# Iniciar Tailwind CSS Compiler

## Opción 1: Desde Terminal (Recomendado)

Abre una terminal en la raíz del proyecto y ejecuta:

```bash
npm run watch:css
```

O para compilar una sola vez:

```bash
npm run build:css
```

## Opción 2: Usando Scripts del Proyecto

### Windows (CMD)
```cmd
start-tailwind.bat
```

### Linux/Mac (Bash)
```bash
bash start-tailwind.sh
```

## Qué hace cada comando

| Comando | Función |
|---------|---------|
| `npm run watch:css` | Inicia el compilador en modo vigilancia (watch). Se recompila automáticamente cuando cambias `css/input.css` |
| `npm run build:css` | Compila `css/input.css` a `css/css/output.css` una sola vez |
| `npm run dev` | Alias para `npm run watch:css` |
| `npm install` | Instala todas las dependencias del proyecto |

## Archivos Monitorados

- **Entrada**: `css/input.css`
- **Salida**: `css/css/output.css`
- **Configuración PostCSS**: `postcss.config.js`
- **Configuración Tailwind**: `css/tailwind.config.js`

## Lo que compila automáticamente

El compilador procesa:
1. ✅ Directivas Tailwind (@tailwind base, @tailwind components, @tailwind utilities)
2. ✅ Componentes personalizados (@layer components)
3. ✅ Prefijos de navegadores (autoprefixer)
4. ✅ Minificación para producción (opcional)

## Dependencias Instaladas

```
tailwindcss@3.4.0       - Framework CSS
postcss@8.4.0          - Procesador CSS
postcss-cli@9.1.0      - CLI para PostCSS
autoprefixer@10.4.0    - Prefijos automáticos
```

## Ejemplo de Uso

1. Abre terminal en la raíz del proyecto
2. Ejecuta `npm run watch:css`
3. Edita `css/input.css` y agrega nuevas clases
4. El compilador generará automáticamente `css/css/output.css`
5. Los navegadores cargarán automáticamente los cambios

## Troubleshooting

**Error: "Missing script watch:css"**
- Solución: Ejecuta `npm install` primero

**El CSS no se actualiza**
- Verifica que estés editando `css/input.css`
- Reinicia el compilador (Ctrl+C y vuelve a ejecutar)

**Port o proceso en uso**
- Intenta ejecutar desde otra terminal
- En Windows: `netstat -ano | findstr :3000` para verificar puertos

---

**Estado**: ✅ Sistema de compilación Tailwind CSS listo  
**Última actualización**: 2025-02-05
