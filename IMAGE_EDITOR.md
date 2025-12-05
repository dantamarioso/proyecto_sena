# Image Editor Modal - Documentación

## Descripción
Modal profesional de edición de imágenes similar a Discord, con capacidades de zoom y arrastre para posicionar la imagen dentro de un círculo de perfil.

## Características
✅ **Zoom ajustable** - Rango de 0.5x a 3x
✅ **Arrastre de imagen** - Posiciona la imagen dentro del canvas
✅ **Vista previa en tiempo real** - Círculo de 120px como preview
✅ **Controles de zoom** - Botones +/-, slider, y botón "Ajustar"
✅ **Soporte Touch** - Funciona en dispositivos móviles
✅ **Rueda del mouse** - Zoom con scroll del mouse
✅ **Interfaz intuitiva** - Diseño limpio y profesional

## Uso

### En formularios de editar perfil
```html
<input type="file" id="foto_editar" name="foto" accept="image/*">
```

El script se inicializa automáticamente al detectar este elemento.

### En formularios de crear usuario
```html
<input type="file" id="foto_crear" name="foto" accept="image/*">
```

También se inicializa automáticamente.

### Inicialización manual
```javascript
const editor = new ImageEditor('foto_editar', containerElement, {
    maxZoom: 3,
    minZoom: 0.5
});
```

## Flujo de usuario

1. Usuario selecciona una imagen
2. Modal se abre con la imagen
3. Usuario puede:
   - Arrastrar la imagen (click + drag)
   - Hacer zoom (slider, botones, o rueda del mouse)
   - Presionar "Ajustar" para centrar la imagen
4. Vista previa muestra cómo se vería en el círculo
5. Al presionar "Guardar":
   - Se crea un canvas de 300x300px con la imagen editada
   - Se guarda como base64 en un input hidden "foto_data"
   - Se actualiza la vista previa en el formulario

## Archivos incluidos

- **image-editor.css** - Estilos del modal y componentes
- **image-editor.js** - Lógica de edición y control de imagen

## Integración en header.php y footer.php

El CSS se carga en `<head>`:
```html
<link rel="stylesheet" href="/proyecto_sena/public/css/image-editor.css">
```

El JS se carga antes de `</body>`:
```html
<script src="/proyecto_sena/public/js/image-editor.js"></script>
```

## Variables de la clase ImageEditor

- `zoom` - Factor de zoom actual (1 = 100%)
- `offsetX`, `offsetY` - Posición de la imagen
- `isDragging` - Estado del arrastre
- `image` - Datos base64 de la imagen
- `imageElement` - Elemento DOM de la imagen

## Métodos públicos

- `setZoom(newZoom)` - Establece el zoom
- `resetZoom()` - Centra y ajusta la imagen automáticamente
- `openModal()` - Abre el modal
- `closeModal()` - Cierra el modal
- `saveImage()` - Guarda la imagen editada

## Personalización

### Cambiar zoom máximo/mínimo
```javascript
new ImageEditor('foto_editar', null, {
    maxZoom: 4,    // Máximo 4x
    minZoom: 0.3   // Mínimo 0.3x
});
```

### Cambiar tamaño del preview
Editar en `image-editor.css`:
```css
.image-editor-preview-circle {
    width: 150px;  /* Cambiar ancho */
    height: 150px; /* Cambiar alto */
}
```

### Cambiar tamaño final de la imagen
En `image-editor.js`, método `saveImage()`:
```javascript
canvas.width = 400;  // Cambiar de 300
canvas.height = 400; // Cambiar de 300
```

## Compatibilidad
- Chrome/Edge 88+
- Firefox 87+
- Safari 14+
- Dispositivos móviles con soporte touch

## Notas técnicas
- Usa Canvas API para procesar la imagen
- Soporta cualquier formato de imagen (JPG, PNG, WebP, etc.)
- El archivo se convierte a base64 para envío
- No requiere dependencias externas
