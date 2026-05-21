/**
 * ZPOT - Dark Mode Manager
 * Gestiona el modo oscuro de toda la aplicación
 */

(function() {
    'use strict';

    const DARK_MODE_KEY = 'zpot_dark_mode';
    
    /**
     * Aplica el modo oscuro al documento
     */
    function applyDarkMode(enabled) {
        if (enabled) {
            document.documentElement.classList.add('dark-mode');
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark-mode');
            document.documentElement.setAttribute('data-theme', 'light');
        }
    }

    /**
     * Obtiene el estado actual del modo oscuro
     */
    function getDarkModeState() {
        const stored = localStorage.getItem(DARK_MODE_KEY);
        return stored === 'true';
    }

    /**
     * Guarda el estado del modo oscuro
     */
    function saveDarkModeState(enabled) {
        localStorage.setItem(DARK_MODE_KEY, enabled ? 'true' : 'false');
    }

    /**
     * Inicializa el modo oscuro al cargar la página
     */
    function initDarkMode() {
        const isDarkMode = getDarkModeState();
        applyDarkMode(isDarkMode);
        
        // Actualizar el switch si existe
        const darkModeToggle = document.getElementById('darkMode');
        if (darkModeToggle) {
            darkModeToggle.checked = isDarkMode;
        }
    }

    /**
     * Toggle del modo oscuro
     */
    function toggleDarkMode(enabled) {
        applyDarkMode(enabled);
        saveDarkModeState(enabled);
    }

    // Aplicar modo oscuro inmediatamente (antes de que cargue el DOM)
    initDarkMode();

    // Cuando el DOM esté listo, configurar el listener
    document.addEventListener('DOMContentLoaded', function() {
        const darkModeToggle = document.getElementById('darkMode');
        
        if (darkModeToggle) {
            // Asegurar que el estado inicial es correcto
            darkModeToggle.checked = getDarkModeState();
            
            // Listener para cambios
            darkModeToggle.addEventListener('change', function() {
                toggleDarkMode(this.checked);
            });
        }
    });

    // Exponer funciones globalmente si es necesario
    window.zpotDarkMode = {
        toggle: toggleDarkMode,
        isEnabled: getDarkModeState,
        apply: applyDarkMode
    };
})();
