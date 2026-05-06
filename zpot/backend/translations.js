/**
 * Sistema de traducciones para Zpot
 * Archivo de configuración de idiomas
 */

const translations = {
    es: {
        settings: "Ajustes",
        language: "Idioma",
        close: "Cerrar",
        save: "Guardar",
        back: "Volver",
        addMySpot: "Añadir mi plaza"
    },
    en: {
        settings: "Settings",
        language: "Language",
        close: "Close",
        save: "Save",
        back: "Back",
        addMySpot: "Add my spot"
    },
    fr: {
        settings: "Paramètres",
        language: "Langue",
        close: "Fermer",
        save: "Enregistrer",
        back: "Retour",
        addMySpot: "Ajouter ma place"
    },
    de: {
        settings: "Einstellungen",
        language: "Sprache",
        close: "Schließen",
        save: "Speichern",
        back: "Zurück",
        addMySpot: "Meinen Platz hinzufügen"
    }
};

/**
 * Obtiene el idioma actual guardado en localStorage o el predeterminado
 * @returns {string} Código del idioma (es, en, fr, de)
 */
function getCurrentLanguage() {
    return localStorage.getItem('zpot_language') || 'es';
}

/**
 * Cambia el idioma de la aplicación
 * @param {string} lang - Código del idioma (es, en, fr, de)
 */
function changeLanguage(lang) {
    if (!translations[lang]) {
        console.error(`Idioma no soportado: ${lang}`);
        return;
    }
    
    // Guardar en localStorage
    localStorage.setItem('zpot_language', lang);
    
    // Actualizar el atributo lang del HTML
    document.documentElement.lang = lang;
    
    // Actualizar todos los elementos con data-i18n
    updateTranslations();
    
    // Disparar evento personalizado para que otros componentes puedan reaccionar
    window.dispatchEvent(new CustomEvent('languageChanged', { detail: { language: lang } }));
    
    console.log(`Idioma cambiado a: ${lang}`);
}

/**
 * Obtiene una traducción específica
 * @param {string} key - Clave de la traducción
 * @param {string} lang - Código del idioma (opcional, usa el actual por defecto)
 * @returns {string} Texto traducido
 */
function t(key, lang = null) {
    const currentLang = lang || getCurrentLanguage();
    return translations[currentLang]?.[key] || key;
}

/**
 * Actualiza todos los elementos con el atributo data-i18n
 */
function updateTranslations() {
    const currentLang = getCurrentLanguage();
    const elements = document.querySelectorAll('[data-i18n]');
    
    elements.forEach(element => {
        const key = element.getAttribute('data-i18n');
        const translation = t(key, currentLang);
        
        // Actualizar el contenido del elemento
        if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
            element.placeholder = translation;
        } else {
            element.textContent = translation;
        }
    });
}

/**
 * Inicializa el sistema de traducciones al cargar la página
 */
function initTranslations() {
    const currentLang = getCurrentLanguage();
    document.documentElement.lang = currentLang;
    updateTranslations();
    
    // Sincronizar el selector de idioma si existe
    const languageSelect = document.getElementById('languageSelect');
    if (languageSelect) {
        languageSelect.value = currentLang;
    }
}

// Inicializar automáticamente cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTranslations);
} else {
    initTranslations();
}
