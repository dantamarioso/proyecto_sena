// ===== Image Editor Modal - Discord Style =====

class ImageEditor {
    constructor(fileInputId) {
        this.fileInputId = fileInputId;
        this.fileInput = document.getElementById(fileInputId);
        this.modal = null;
        this.image = null;
        this.container = null;
        
        // Estado
        this.zoom = 1;
        this.offsetX = 0;
        this.offsetY = 0;
        this.isDragging = false;
        this.dragStartX = 0;
        this.dragStartY = 0;
        this.dragStartOffsetX = 0;
        this.dragStartOffsetY = 0;
        this.lastDistance = 0;
        this.circleRadius = 150;
        
        if (this.fileInput) {
            this.fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
        }
    }
    
    handleFileSelect(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = (evt) => {
            if (!this.modal) {
                this.createModal();
            }
            this.openModal(evt.target.result);
        };
        reader.readAsDataURL(file);
    }
    
    createModal() {
        const html = `
            <div class="image-editor-modal" id="imageEditorModal_${this.fileInputId}">
                <div class="image-editor-content">
                    <div class="image-editor-header">
                        <h5>Editar Foto</h5>
                        <button class="image-editor-close" aria-label="Cerrar">&times;</button>
                    </div>
                    <div class="image-editor-canvas">
                        <div class="image-editor-canvas-container" id="canvasContainer_${this.fileInputId}">
                            <img class="image-editor-image" alt="Imagen">
                            <div class="image-editor-circle-overlay"></div>
                        </div>
                    </div>
                    <div class="image-editor-footer">
                        <button class="btn-cancel" type="button">Cancelar</button>
                        <button class="btn-save" type="button">Guardar</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', html);
        
        this.modal = document.getElementById(`imageEditorModal_${this.fileInputId}`);
        this.image = this.modal.querySelector('.image-editor-image');
        this.container = document.getElementById(`canvasContainer_${this.fileInputId}`);
        
        const circleOverlay = this.modal.querySelector('.image-editor-circle-overlay');
        circleOverlay.style.width = (this.circleRadius * 2) + 'px';
        circleOverlay.style.height = (this.circleRadius * 2) + 'px';
        
        // Event listeners - Botones
        this.modal.querySelector('.image-editor-close').addEventListener('click', () => this.closeModal());
        this.modal.querySelector('.btn-cancel').addEventListener('click', () => this.closeModal());
        this.modal.querySelector('.btn-save').addEventListener('click', () => this.saveImage());
        
        // Event listeners - Mouse
        this.container.addEventListener('mousedown', (e) => this.startDrag(e));
        this.container.addEventListener('mousemove', (e) => this.drag(e));
        this.container.addEventListener('mouseup', () => this.endDrag());
        this.container.addEventListener('mouseleave', () => this.endDrag());
        
        // Event listeners - Touch (passive para mejor performance)
        this.container.addEventListener('touchstart', (e) => this.handleTouchStart(e), { passive: true });
        this.container.addEventListener('touchmove', (e) => this.handleTouchMove(e), { passive: false });
        this.container.addEventListener('touchend', () => this.endDrag(), { passive: true });
        
        // Event listeners - Wheel (passive)
        this.container.addEventListener('wheel', (e) => this.handleWheel(e), { passive: false });
        this.container.addEventListener('contextmenu', (e) => e.preventDefault());
    }
    
    openModal(imageSrc) {
        if (!this.image) return;
        
        // Mostrar el modal primero
        if (this.modal) {
            this.modal.classList.add('show');
        }
        
        // Usar requestAnimationFrame para garantizar que el layout se ha calculado
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                // Limpiar handlers previos
                this.image.onload = null;
                this.image.onerror = null;
                
                // Configurar handlers ANTES de asignar src
                this.image.onload = () => {
                    this.resetPosition();
                };
                
                this.image.onerror = () => {
                    alert('No se pudo cargar la imagen.');
                };
                
                // Asignar src (esto dispara onload)
                this.image.src = imageSrc;
            });
        });
    }
    
    closeModal() {
        if (this.modal) {
            this.modal.classList.remove('show');
        }
    }
    
    // DRAG
    startDrag = (e) => {
        if (e.button !== 0) return; // Solo click izquierdo
        e.preventDefault();
        this.isDragging = true;
        this.dragStartX = e.clientX;
        this.dragStartY = e.clientY;
        this.dragStartOffsetX = this.offsetX;
        this.dragStartOffsetY = this.offsetY;
        this.container.classList.add('dragging');
        
        // Agregar listeners globales para mejor tracking
        document.addEventListener('mousemove', this.drag);
        document.addEventListener('mouseup', this.endDrag);
    }
    
    drag = (e) => {
        if (!this.isDragging || !this.image) return;
        
        const deltaX = e.clientX - this.dragStartX;
        const deltaY = e.clientY - this.dragStartY;
        
        this.offsetX = this.dragStartOffsetX + deltaX;
        this.offsetY = this.dragStartOffsetY + deltaY;
        
        this.updateImage();
    }
    
    endDrag = (e) => {
        this.isDragging = false;
        this.container.classList.remove('dragging');
        
        // Remover listeners globales
        document.removeEventListener('mousemove', this.drag);
        document.removeEventListener('mouseup', this.endDrag);
    }
    
    // TOUCH
    handleTouchStart = (e) => {
        if (e.touches.length === 1) {
            this.isDragging = true;
            this.dragStartX = e.touches[0].clientX;
            this.dragStartY = e.touches[0].clientY;
            this.dragStartOffsetX = this.offsetX;
            this.dragStartOffsetY = this.offsetY;
            this.container.classList.add('dragging');
        } else if (e.touches.length === 2) {
            const dx = e.touches[0].clientX - e.touches[1].clientX;
            const dy = e.touches[0].clientY - e.touches[1].clientY;
            this.lastDistance = Math.sqrt(dx * dx + dy * dy);
            this.isDragging = false;
        }
    }
    
    handleTouchMove = (e) => {
        if (e.touches.length === 1 && this.isDragging) {
            const deltaX = e.touches[0].clientX - this.dragStartX;
            const deltaY = e.touches[0].clientY - this.dragStartY;
            
            this.offsetX = this.dragStartOffsetX + deltaX;
            this.offsetY = this.dragStartOffsetY + deltaY;
            
            this.updateImage();
        } else if (e.touches.length === 2) {
            const dx = e.touches[0].clientX - e.touches[1].clientX;
            const dy = e.touches[0].clientY - e.touches[1].clientY;
            const distance = Math.sqrt(dx * dx + dy * dy);
            
            if (this.lastDistance > 0) {
                const scale = distance / this.lastDistance;
                const newZoom = Math.max(0.5, Math.min(4, this.zoom * scale));
                this.zoom = newZoom;
            }
            this.lastDistance = distance;
            this.updateImage();
        }
    }
    
    // WHEEL ZOOM
    handleWheel = (e) => {
        e.preventDefault();
        const delta = e.deltaY > 0 ? -0.1 : 0.1;
        const newZoom = Math.max(0.1, Math.min(5, this.zoom + delta));
        this.zoom = newZoom;
        this.updateImage();
    }
    
    updateImage() {
        if (!this.image) return;
        const transform = `translate(calc(-50% + ${this.offsetX}px), calc(-50% + ${this.offsetY}px)) scale(${this.zoom})`;
        this.image.style.transform = transform;
    }
    
    resetPosition() {
        // Iniciar con zoom 1 (100%) - imagen sin escalar
        this.zoom = 1;
        this.offsetX = 0;
        this.offsetY = 0;
        this.updateImage();
    }
    
    saveImage = () => {
        if (!this.image || !this.image.src) return;
        
        const canvas = document.createElement('canvas');
        canvas.width = 300;
        canvas.height = 300;
        const ctx = canvas.getContext('2d');
        
        // Fondo transparente
        ctx.clearRect(0, 0, 300, 300);
        
        // Crear imagen
        const img = new Image();
        img.crossOrigin = 'anonymous';
        
        img.onload = () => {
            try {
                // Aplicar transformaciones y recorte circular
                ctx.save();
                
                // Crear círculo de recorte
                ctx.beginPath();
                ctx.arc(150, 150, 150, 0, Math.PI * 2);
                ctx.clip();
                
                // Dibujar imagen transformada
                ctx.translate(150, 150);
                ctx.scale(this.zoom, this.zoom);
                ctx.translate(this.offsetX, this.offsetY);
                ctx.drawImage(img, -img.width / 2, -img.height / 2);
                
                ctx.restore();
                
                // Convertir a PNG (mantiene transparencia)
                const base64 = canvas.toDataURL('image/png');
                
                // Si es desde perfil (inputFoto), enviar al servidor
                if (this.fileInputId === 'inputFoto') {
                    this.sendProfilePhoto(base64);
                } else {
                    // Para formularios, guardar en input hidden
                    const photoDataInput = document.getElementById('foto_data');
                    if (photoDataInput) {
                        photoDataInput.value = base64;
                    }
                    
                    // Actualizar preview
                    this.updatePreview(base64);
                }
                
                this.closeModal();
            } catch (error) {
                alert('Error al guardar la imagen.');
            }
        };
        
        img.onerror = () => {
            alert('Error al cargar la imagen para procesar.');
        };
        
        img.src = this.image.src;
    }
    
    sendProfilePhoto = (base64) => {
        // Convertir base64 a blob
        const byteCharacters = atob(base64.split(',')[1]);
        const byteNumbers = new Array(byteCharacters.length);
        for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
        }
        const byteArray = new Uint8Array(byteNumbers);
        const blob = new Blob([byteArray], { type: 'image/png' });
        
        // Crear FormData con la imagen
        const formData = new FormData();
        formData.append('foto', blob, 'foto.png');
        formData.append('from_editor', '1');
        
        // Enviar al servidor
        fetch(window.BASE_URL + '/?url=perfil/cambiarFoto', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(json => {
            if (json.success) {
                // Actualizar foto en la vista actual
                const fotoPerfil = document.getElementById('fotoPerfil');
                if (fotoPerfil) {
                    const fotoConTimestamp = json.foto + '?t=' + new Date().getTime();
                    fotoPerfil.src = fotoConTimestamp;
                }
                
                // Actualizar sidebar
                const sidebarAvatar = document.querySelector('.sidebar-avatar');
                if (sidebarAvatar) {
                    const fotoConTimestamp = json.foto + '?t=' + new Date().getTime();
                    sidebarAvatar.src = fotoConTimestamp;
                }
            } else {
                alert('Error al guardar la foto: ' + (json.message || 'Error desconocido'));
            }
        })
        .catch(err => {
            alert('Error al guardar la foto');
        });
    }
    
    updatePreview = (base64) => {
        // Buscar preview por ID del input
        let previewImg = null;
        
        if (this.fileInputId === 'foto_editar') {
            previewImg = document.getElementById('preview_editar');
        } else if (this.fileInputId === 'foto_crear') {
            previewImg = document.getElementById('preview_crear');
        } else if (this.fileInputId === 'inputFoto') {
            // Para ver perfil - actualizar foto principal
            previewImg = document.getElementById('fotoPerfil');
        }
        
        if (previewImg) {
            previewImg.src = base64;
            previewImg.style.borderRadius = '50%';
            previewImg.style.objectFit = 'cover';
            
            // Si es preview_crear/editar, establecer dimensiones
            if (this.fileInputId !== 'inputFoto') {
                previewImg.style.width = '120px';
                previewImg.style.height = '120px';
                
                // Mostrar el contenedor de preview
                const container = previewImg.closest('.image-preview-container');
                if (container) {
                    container.style.display = 'block';
                }
            }
        }
    }
}

// Auto-init
document.addEventListener('DOMContentLoaded', () => {
    const fotoEditarInput = document.getElementById('foto_editar');
    if (fotoEditarInput) {
        new ImageEditor('foto_editar');
    }
    
    const fotoCrearInput = document.getElementById('foto_crear');
    if (fotoCrearInput) {
        new ImageEditor('foto_crear');
    }
    
    // Para perfil/ver.php - usar inputFoto
    const inputFotoInput = document.getElementById('inputFoto');
    if (inputFotoInput) {
        new ImageEditor('inputFoto');
    }
});
