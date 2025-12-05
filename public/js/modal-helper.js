/**
 * Modal Helper - Gestión de modales en mobile
 * Asegura que los modales funcionen correctamente en dispositivos móviles
 * cerrando el sidebar y manejando el z-index correctamente
 */

(function() {
    'use strict';

    // Esperar a que el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initModalHelper);
    } else {
        initModalHelper();
    }

    function initModalHelper() {
        // El cierre de sidebar es manejado por sidebar.js en setupModalHandlers()
        // Aquí nos enfocamos SOLO en corregir backdrop z-index y modal interactividad

        // Asegurar que el body tenga la clase modal-open cuando hay modales
        observeBodyModalClass();
        
        // Observar y corregir backdrop
        observeBackdrop();
    }

    /**
     * Observa cambios en la clase modal-open del body
     */
    function observeBodyModalClass() {
        const bodyObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    if (document.body.classList.contains('modal-open') && window.innerWidth <= 768) {
                        // Prevenir scroll del body en mobile cuando hay modal abierto
                        document.body.style.overflow = 'hidden';
                        document.body.style.position = 'fixed';
                        document.body.style.width = '100%';
                    } else if (!document.body.classList.contains('modal-open')) {
                        // Restaurar scroll cuando se cierra el modal
                        document.body.style.overflow = '';
                        document.body.style.position = '';
                        document.body.style.width = '';
                    }
                }
            });
        });

        bodyObserver.observe(document.body, { 
            attributes: true, 
            attributeFilter: ['class'] 
        });
    }

    /**
     * Observa y corrige el z-index del backdrop
     */
    function observeBackdrop() {
        // Observer para detectar cuando se agrega el backdrop al DOM
        const backdropObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && node.classList && node.classList.contains('modal-backdrop')) {
                        // Forzar z-index del backdrop INMEDIATAMENTE
                        fixBackdropZIndex();
                    }
                });
            });
        });

        backdropObserver.observe(document.body, { childList: true, subtree: false });
        
        // Listener para eventos de modal de Bootstrap
        document.addEventListener('shown.bs.modal', function() {
            fixBackdropZIndex();
        });
        
        // Corregir backdrop cada 200ms mientras haya modales abiertos
        setInterval(() => {
            const activeModal = document.querySelector('.modal.show');
            if (activeModal) {
                fixBackdropZIndex();
            }
        }, 200);
    }
    
    /**
     * Fuerza el z-index correcto del backdrop y modal
     * SOLUCIÓN: Hacer el backdrop completamente invisible
     */
    function fixBackdropZIndex() {
        // Hacer backdrop INVISIBLE (no bloquea visualmente)
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => {
            backdrop.style.setProperty('z-index', '1040', 'important');
            backdrop.style.setProperty('pointer-events', 'none', 'important');
            backdrop.style.setProperty('background-color', 'transparent', 'important');
            backdrop.style.setProperty('opacity', '0', 'important');
            backdrop.style.setProperty('visibility', 'visible', 'important'); // Visible en el DOM pero invisible para el ojo
        });
        
        // Forzar modal por encima y completamente interactuable
        const activeModals = document.querySelectorAll('.modal.show');
        activeModals.forEach(modal => {
            modal.style.setProperty('z-index', '1060', 'important');
            modal.style.setProperty('pointer-events', 'auto', 'important');
            modal.style.setProperty('display', 'block', 'important');
            
            const modalDialog = modal.querySelector('.modal-dialog');
            if (modalDialog) {
                modalDialog.style.setProperty('z-index', '1070', 'important');
                modalDialog.style.setProperty('pointer-events', 'auto', 'important');
                modalDialog.style.setProperty('position', 'relative', 'important');
            }
            
            const modalContent = modal.querySelector('.modal-content');
            if (modalContent) {
                modalContent.style.setProperty('z-index', '1075', 'important');
                modalContent.style.setProperty('pointer-events', 'auto', 'important');
                modalContent.style.setProperty('position', 'relative', 'important');
            }
            
            // Asegurar que TODOS los inputs sean clicables
            modal.querySelectorAll('input, select, textarea, button, a, label, .btn, .form-control, .form-select').forEach(element => {
                element.style.setProperty('pointer-events', 'auto', 'important');
                element.style.setProperty('touch-action', 'auto', 'important');
                element.style.setProperty('cursor', 'pointer', 'important');
            });
        });
    }

    // Función global para abrir modal de forma segura
    window.openModalSafe = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            // Si usa Bootstrap
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
                bsModal.show();
            } else {
                // Abrir manualmente
                modal.style.display = 'block';
                modal.classList.add('show');
                document.body.classList.add('modal-open');
                
                // Crear backdrop si no existe
                if (!document.querySelector('.modal-backdrop')) {
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    document.body.appendChild(backdrop);
                }
            }
        }
    };

    // Función global para cerrar modal de forma segura
    window.closeModalSafe = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            // Si usa Bootstrap
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            } else {
                // Cerrar manualmente
                modal.style.display = 'none';
                modal.classList.remove('show');
                document.body.classList.remove('modal-open');
                
                // Remover backdrop
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                
                // Restaurar scroll del body
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
            }
        }
    };

    // Log para debug (remover en producción)
    console.log('Modal Helper inicializado');
})();
