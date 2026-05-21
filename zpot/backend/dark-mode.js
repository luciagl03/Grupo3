(function() {
    'use strict';

    const DARK_MODE_KEY = 'zpot_dark_mode';
    
    function applyDarkMode(enabled) {
        if (enabled) {
            document.documentElement.classList.add('dark-mode');
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark-mode');
            document.documentElement.setAttribute('data-theme', 'light');
        }
    }

    function getDarkModeState() {
        const stored = localStorage.getItem(DARK_MODE_KEY);
        return stored === 'true';
    }

    function saveDarkModeState(enabled) {
        localStorage.setItem(DARK_MODE_KEY, enabled ? 'true' : 'false');
    }

    function initDarkMode() {
        const isDarkMode = getDarkModeState();
        applyDarkMode(isDarkMode);
        
        // Actualizar el switch si existe
        const darkModeToggle = document.getElementById('darkMode');
        if (darkModeToggle) {
            darkModeToggle.checked = isDarkMode;
        }
    }

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
