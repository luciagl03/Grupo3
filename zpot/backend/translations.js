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
        addMySpot: "Añadir mi plaza",
        // FAQ
        faqTitle: "Preguntas frecuentes (FAQ)",
        faqQ1: "¿Cómo reservo una plaza?",
        faqA1: "Selecciona el punto en el mapa, elige tus horas y confirma.",
        faqQ2: "¿Puedo cancelar una reserva?",
        faqA2: "Sí, desde el apartado 'Mis Reservas' hasta 1 hora antes.",
        faqQ3: "¿Cómo publico mi garaje?",
        faqA3: "Ve a 'Mis Plazas' y rellena el formulario con fotos y dirección.",
        faqQ4: "¿Es seguro el pago?",
        faqA4: "Sí, usamos pasarelas de pago cifradas para tu seguridad.",
        // Support Section
        supportTitle: "Contactar soporte",
        supportDescription: "Envíanos tus consultas o reporta problemas",
        supportSubject: "Asunto",
        supportMessage: "Mensaje",
        supportSend: "Enviar",
        supportSending: "Enviando...",
        supportSuccess: "Mensaje enviado con éxito",
        supportQuickActions: "Acciones rápidas",
        supportContactEmail: "Contactar por Email",
        supportReportBug: "Reportar un error"
    },
    en: {
        settings: "Settings",
        language: "Language",
        close: "Close",
        save: "Save",
        back: "Back",
        addMySpot: "Add my spot",
        // FAQ
        faqTitle: "Frequently Asked Questions (FAQ)",
        faqQ1: "How do I book a spot?",
        faqA1: "Select the point on the map, choose your hours and confirm.",
        faqQ2: "Can I cancel a reservation?",
        faqA2: "Yes, from the 'My Reservations' section up to 1 hour before.",
        faqQ3: "How do I publish my garage?",
        faqA3: "Go to 'My Spots' and fill out the form with photos and address.",
        faqQ4: "Is payment secure?",
        faqA4: "Yes, we use encrypted payment gateways for your security.",
        // Support Section
        supportTitle: "Contact support",
        supportDescription: "Send us your questions or report issues",
        supportSubject: "Subject",
        supportMessage: "Message",
        supportSend: "Send",
        supportSending: "Sending...",
        supportSuccess: "Message sent successfully",
        supportQuickActions: "Quick actions",
        supportContactEmail: "Contact by Email",
        supportReportBug: "Report a bug"
    },
    fr: {
        settings: "Paramètres",
        language: "Langue",
        close: "Fermer",
        save: "Enregistrer",
        back: "Retour",
        addMySpot: "Ajouter ma place",
        // FAQ
        faqTitle: "Questions fréquemment posées (FAQ)",
        faqQ1: "Comment réserver une place ?",
        faqA1: "Sélectionnez le point sur la carte, choisissez vos heures et confirmez.",
        faqQ2: "Puis-je annuler une réservation ?",
        faqA2: "Oui, depuis la section 'Mes Réservations' jusqu'à 1 heure avant.",
        faqQ3: "Comment publier mon garage ?",
        faqA3: "Allez dans 'Mes Places' et remplissez le formulaire avec photos et adresse.",
        faqQ4: "Le paiement est-il sécurisé ?",
        faqA4: "Oui, nous utilisons des passerelles de paiement cryptées pour votre sécurité.",
        // Support Section
        supportTitle: "Contacter le support",
        supportDescription: "Envoyez-nous vos questions ou signalez des problèmes",
        supportSubject: "Sujet",
        supportMessage: "Message",
        supportSend: "Envoyer",
        supportSending: "Envoi en cours...",
        supportSuccess: "Message envoyé avec succès",
        supportQuickActions: "Actions rapides",
        supportContactEmail: "Contacter par Email",
        supportReportBug: "Signaler un bug"
    },
    de: {
        settings: "Einstellungen",
        language: "Sprache",
        close: "Schließen",
        save: "Speichern",
        back: "Zurück",
        addMySpot: "Meinen Platz hinzufügen",
        // FAQ
        faqTitle: "Häufig gestellte Fragen (FAQ)",
        faqQ1: "Wie buche ich einen Platz?",
        faqA1: "Wählen Sie den Punkt auf der Karte, wählen Sie Ihre Stunden und bestätigen Sie.",
        faqQ2: "Kann ich eine Reservierung stornieren?",
        faqA2: "Ja, im Bereich 'Meine Reservierungen' bis 1 Stunde vorher.",
        faqQ3: "Wie veröffentliche ich meine Garage?",
        faqA3: "Gehen Sie zu 'Meine Plätze' und füllen Sie das Formular mit Fotos und Adresse aus.",
        faqQ4: "Ist die Zahlung sicher?",
        faqA4: "Ja, wir verwenden verschlüsselte Zahlungsgateways für Ihre Sicherheit.",
        // Support Section
        supportTitle: "Support kontaktieren",
        supportDescription: "Senden Sie uns Ihre Fragen oder melden Sie Probleme",
        supportSubject: "Betreff",
        supportMessage: "Nachricht",
        supportSend: "Senden",
        supportSending: "Wird gesendet...",
        supportSuccess: "Nachricht erfolgreich gesendet",
        supportQuickActions: "Schnellaktionen",
        supportContactEmail: "Per E-Mail kontaktieren",
        supportReportBug: "Fehler melden"
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
