/**
 * Script de Debug para Modales en Mobile
 * Pega este script en la consola del navegador cuando tengas un modal abierto
 * para diagnosticar el problema del z-index
 */

function debugModal() {
    console.log('=== DEBUG MODAL ===');
    
    // Verificar ancho de pantalla
    console.log('Ancho de pantalla:', window.innerWidth);
    console.log('Es mobile?', window.innerWidth <= 768);
    
    // Buscar backdrop
    const backdrops = document.querySelectorAll('.modal-backdrop');
    console.log('\n--- BACKDROPS (' + backdrops.length + ') ---');
    backdrops.forEach((backdrop, index) => {
        const styles = window.getComputedStyle(backdrop);
        console.log(`Backdrop ${index + 1}:`, {
            zIndex: styles.zIndex,
            display: styles.display,
            opacity: styles.opacity,
            backgroundColor: styles.backgroundColor,
            pointerEvents: styles.pointerEvents,
            position: styles.position,
            isVisible: styles.opacity !== '0' && styles.display !== 'none',
            inlineStyle: backdrop.style.cssText
        });
    });
    
    // Buscar modales
    const modals = document.querySelectorAll('.modal');
    console.log('\n--- MODALES (' + modals.length + ') ---');
    modals.forEach((modal, index) => {
        const styles = window.getComputedStyle(modal);
        const isShow = modal.classList.contains('show');
        console.log(`Modal ${index + 1} (${isShow ? 'ABIERTO' : 'cerrado'}):`, {
            id: modal.id,
            zIndex: styles.zIndex,
            display: styles.display,
            pointerEvents: styles.pointerEvents,
            position: styles.position,
            inlineStyle: modal.style.cssText
        });
        
        if (isShow) {
            const dialog = modal.querySelector('.modal-dialog');
            const content = modal.querySelector('.modal-content');
            
            if (dialog) {
                const dialogStyles = window.getComputedStyle(dialog);
                console.log('  Dialog:', {
                    zIndex: dialogStyles.zIndex,
                    pointerEvents: dialogStyles.pointerEvents,
                    position: dialogStyles.position,
                    inlineStyle: dialog.style.cssText
                });
            }
            
            if (content) {
                const contentStyles = window.getComputedStyle(content);
                console.log('  Content:', {
                    zIndex: contentStyles.zIndex,
                    pointerEvents: contentStyles.pointerEvents,
                    position: contentStyles.position,
                    inlineStyle: content.style.cssText
                });
            }
        }
    });
    
    // Verificar sidebar
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        const styles = window.getComputedStyle(sidebar);
        console.log('\n--- SIDEBAR ---');
        console.log('Sidebar:', {
            zIndex: styles.zIndex,
            display: styles.display,
            transform: styles.transform,
            isMobileOpen: sidebar.classList.contains('mobile-open'),
            inlineStyle: sidebar.style.cssText
        });
    }
    
    // Verificar body
    console.log('\n--- BODY ---');
    console.log('Body classes:', document.body.className);
    console.log('Body overflow:', window.getComputedStyle(document.body).overflow);
    
    console.log('\n=== FIN DEBUG ===\n');
    
    // Intentar fix automático
    console.log('Intentando fix automático...');
    fixModalNow();
}

function fixModalNow() {
    // Forzar backdrop detrás
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.style.setProperty('z-index', '1040', 'important');
        backdrop.style.setProperty('pointer-events', 'none', 'important');
    });
    
    // Forzar modal por encima
    document.querySelectorAll('.modal.show').forEach(modal => {
        modal.style.setProperty('z-index', '1060', 'important');
        modal.style.setProperty('pointer-events', 'auto', 'important');
        
        const dialog = modal.querySelector('.modal-dialog');
        if (dialog) {
            dialog.style.setProperty('z-index', '1070', 'important');
            dialog.style.setProperty('pointer-events', 'auto', 'important');
            dialog.style.setProperty('position', 'relative', 'important');
        }
        
        const content = modal.querySelector('.modal-content');
        if (content) {
            content.style.setProperty('z-index', '1075', 'important');
            content.style.setProperty('pointer-events', 'auto', 'important');
            content.style.setProperty('position', 'relative', 'important');
        }
    });
    
    console.log('Fix aplicado. Verifica si el modal ahora es interactuable.');
}

// Ejecutar debug
debugModal();

// Instrucciones
console.log('\n📋 INSTRUCCIONES:');
console.log('1. Si el modal sigue bloqueado, ejecuta: fixModalNow()');
console.log('2. Para ver el debug de nuevo: debugModal()');
console.log('3. Copia los resultados y envíalos al desarrollador si el problema persiste\n');
