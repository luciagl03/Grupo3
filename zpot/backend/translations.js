/**
 * Sistema de traducciones para Zpot
 * Archivo de configuración de idiomas
 */

const translations = {
    es: {
        // Básicos
        settings: "Ajustes",
        language: "Idioma",
        close: "Cerrar",
        save: "Guardar",
        back: "Volver",
        addMySpot: "Añadir mi plaza",
        
        // Búsqueda y Filtros
        searchPlaceholder: "¿Dónde buscas aparcamiento?",
        filters: "Filtros",
        filterLocation: "Ubicación",
        filterCovered: "Cubierto",
        filterGarage: "Garaje",
        filterOutdoor: "Exterior",
        filterExtras: "Extras",
        filterElectric: "Carga eléctrica",
        filterGuarded: "Vigilado",
        filterAccess24h: "Acceso 24h",
        clear: "Limpiar",
        apply: "Aplicar",
        
        // Menú de Usuario
        myProfile: "Mi perfil",
        myReservations: "Mis reservas",
        mySpots: "Mis plazas",
        paymentMethods: "Métodos de pago",
        logout: "Cerrar sesión",
        
        // Notificaciones
        notifications: "Notificaciones",
        markAllRead: "Marcar todo leído",
        noNotifications: "Sin notificaciones",
        
        // Banners y Mensajes
        spotPublishedSuccess: "Plaza publicada correctamente. Ya aparece en el mapa.",
        spotSavedNoLocation: "Plaza guardada, pero no pudimos ubicarla en el mapa. Comprueba que la dirección incluye la ciudad (ej: Calle Larios 2, Málaga).",
        noResultsFound: "No se encontraron plazas con esos criterios.",
        
        // Panel de Detalles
        noImage: "Sin imagen",
        reserve: "Reservar",
        
        // Notificaciones del Sistema
        reservationStarted: "¡Tu reserva ha comenzado!",
        reservationStartedMsg: "Tu reserva en {address} acaba de empezar.",
        fifteenMinutesLeft: "Quedan 15 minutos",
        fifteenMinutesLeftMsg: "Tu reserva en {address} termina pronto.",
        fiveMinutesLeft: "¡Quedan 5 minutos!",
        fiveMinutesLeftMsg: "Recoge tu vehículo en {address}.",
        today: "Hoy",
        
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
        supportReportBug: "Reportar un error",
        
        // Settings Page - ajustes.php
        backToMap: "Volver al mapa",
        settingsSubtitle: "Configura tu experiencia en Zpot",
        privacySecurity: "Privacidad y Seguridad",
        changePassword: "Cambiar contraseña",
        changePasswordDesc: "Actualiza tu contraseña de acceso",
        currentPassword: "Contraseña actual",
        newPassword: "Nueva contraseña",
        confirmPassword: "Confirmar contraseña",
        updatePassword: "Actualizar contraseña",
        deleteAccount: "Borrar cuenta",
        deleteAccountDesc: "Eliminar permanentemente tu cuenta",
        deleteMyAccount: "Eliminar mi cuenta",
        pushNotifications: "Notificaciones push",
        pushNotificationsDesc: "Recibe alertas sobre tus reservas",
        displayLanguage: "Pantalla e Idiomas",
        darkMode: "Modo oscuro",
        darkModeDesc: "Tema oscuro para la interfaz",
        languageDesc: "Selecciona tu idioma preferido",
        deleteAccountQuestion: "¿Eliminar cuenta?",
        deleteAccountWarning: "Esta acción es permanente.",
        enterPassword: "Introduce tu contraseña",
        deletePermanently: "Eliminar definitivamente",
        cancel: "Cancelar",
        supportlegal: "Soporte y Legal",
        termsconditions: "Términos y Condiciones",
        privacypolicy: "Política de privacidad",
        helpcenter: "Centro de Ayuda",
        
        // Add My Spot Page - alta_plaza.php
        addMySpotTitle: "Añadir mi plaza",
        addMySpotSubtitle: "Publica tu plaza de aparcamiento o garaje. Los campos con (*) son obligatorios.",
        address: "Dirección",
        addressPlaceholder: "Ej: Calle Larios 2, Málaga",
        addressHint: "Incluye siempre la ciudad para que aparezca en el mapa.",
        addressRequired: "La dirección es obligatoria",
        addressCityRequired: "Incluye la ciudad separada por coma (ej: Calle Larios 2, Málaga)",
        spotPhoto: "Foto de la plaza",
        selectImage: "Seleccionar imagen",
        removeImage: "Eliminar imagen",
        invalidImageFile: "Por favor selecciona un archivo de imagen válido",
        imageTooLarge: "La imagen no debe superar los 5MB",
        width: "Ancho (m)",
        length: "Largo (m)",
        widthPlaceholder: "2.5",
        lengthPlaceholder: "5",
        invalidNumber: "Debe ser un número ≥ 0",
        location: "Ubicación",
        covered: "Cubierto",
        garage: "Garaje",
        outdoor: "Exterior / Al aire libre",
        extras: "Extras",
        electricCharging: "Carga eléctrica",
        guarded: "Vigilado",
        access24h: "Acceso 24h",
        description: "Descripción",
        descriptionPlaceholder: "Detalles del aparcamiento, acceso, seguridad...",
        pricePerHour: "Precio (€/h)",
        pricePlaceholder: "4.50",
        priceRequired: "El precio es obligatorio",
        pricePositive: "El precio debe ser mayor que 0",
        publishSpot: "Publicar plaza",
        publishing: "Publicando…",
        saveError: "Error al guardar. Inténtalo de nuevo.",
        connectionError: "Error de conexión. Inténtalo de nuevo.",
        imageProcessError: "Error al procesar la imagen. Inténtalo de nuevo.",
        
        // My Profile Page - mi_perfil.php
        myProfileTitle: "Mi perfil",
        myProfileSubtitle: "Gestiona tu información personal en Zpot",
        firstName: "Nombre",
        firstNamePlaceholder: "Tu nombre",
        lastName: "Apellidos",
        lastNamePlaceholder: "Tus apellidos",
        email: "Correo electrónico",
        emailPlaceholder: "tu@email.com",
        phone: "Teléfono",
        phonePlaceholder: "Número de contacto",
        addressField: "Dirección",
        addressFieldPlaceholder: "Tu dirección",
        saveChanges: "Guardar cambios",
        changesSaved: "Cambios guardados correctamente.",
        requiredFieldsError: "Nombre, apellidos y email son obligatorios.",
        validEmailRequired: "Nombre, apellidos y un email válido son obligatorios.",
        
        // My Reservations Page - mis_reservas.php
        myReservationsTitle: "Mis reservas",
        myReservationsSubtitle: "Gestiona tus próximas estancias y revisa tu historial.",
        noReservationsYet: "Aún no has realizado ninguna reserva.",
        searchSpot: "Buscar una plaza",
        statusConfirmed: "Confirmada",
        statusPending: "Pendiente de pago",
        statusCancelled: "Cancelada",
        statusCompleted: "Completada",
        dateLabel: "Fecha",
        scheduleLabel: "Horario",
        durationLabel: "Duración",
        hoursLabel: "horas",
        chatButton: "Chat",
        payNowButton: "Pagar ahora",
        cancelButton: "Cancelar",
        confirmCancelReservation: "¿Seguro que quieres cancelar esta reserva?",
        
        // My Spots Page - mis_plazas.php
        mySpotsTitle: "Mis plazas publicadas",
        mySpotsSubtitle: "Gestiona los anuncios de tus plazas de aparcamiento o añade nuevas.",
        receivedReservations: "Reservas recibidas",
        spotUpdatedSuccess: "Plaza actualizada correctamente.",
        noSpotsYet: "Aún no has publicado ninguna plaza.",
        publishFirstSpot: "Publicar mi primera plaza",
        addressLabel: "Dirección",
        priceLabel: "Precio",
        measurementsLabel: "Medidas",
        notSpecified: "No especificado",
        editButton: "Editar",
        deleteButton: "Eliminar",
        confirmDeleteSpot: "¿Estás seguro de que deseas eliminar este anuncio?",
        
        // Received Reservations Page - reservas_propietario.php
        backToMySpots: "Volver a mis plazas",
        receivedReservationsTitle: "Reservas en mis plazas",
        receivedReservationsSubtitle: "Gestiona las reservas que han hecho otros usuarios en tus plazas y chatea con ellos.",
        noReceivedReservations: "Aún no hay reservas confirmadas en tus plazas.",
        viewMySpots: "Ver mis plazas",
        tenantLabel: "Inquilino",
        newReservationBadge: "Nueva",
        
        // Payment Methods Page - metodos_pago.php
        paymentMethodsTitle: "Métodos de pago",
        paymentMethodsSubtitle: "Gestiona tus formas de pago para futuras reservas",
        savedMethodsTitle: "Tus métodos guardados",
        loadingMethods: "Cargando métodos de pago…",
        addMethodTitle: "Añadir método",
        addPayPalButton: "Añadir cuenta de PayPal",
        addCardButton: "Añadir tarjeta de crédito / débito",
        securityInfo: "Tus datos de pago están protegidos. Los números de tarjeta son procesados de forma segura por PayPal y nunca se almacenan en nuestros servidores. Solo guardamos los últimos 4 dígitos como referencia visual.",
        noPaymentMethods: "No tienes métodos de pago guardados.<br>Añade uno para agilizar tus reservas.",
        defaultBadge: "Predeterminado",
        setAsDefaultTitle: "Establecer como predeterminado",
        deleteTitle: "Eliminar",
        addPaymentMethodTitle: "Añadir método de pago",
        addPayPalTitle: "Añadir cuenta PayPal",
        addCardTitle: "Añadir tarjeta",
        aliasLabel: "Alias (nombre que verás)",
        aliasPayPalPlaceholder: "Ej: Mi PayPal personal",
        paypalEmailLabel: "Email de PayPal",
        aliasCardPlaceholder: "Ej: Visa personal",
        cardBrandLabel: "Marca de la tarjeta",
        otherBrandButton: "Otra",
        last4DigitsLabel: "Últimos 4 dígitos",
        last4DigitsHint: "Solo los últimos 4 dígitos — nunca almacenamos el número completo.",
        expiryLabel: "Caducidad",
        setAsDefaultLabel: "Establecer como método predeterminado",
        saveButton: "Guardar",
        errorEnterAlias: "Introduce un alias.",
        errorEnterPayPalEmail: "Introduce el email de PayPal.",
        errorSelectBrand: "Selecciona la marca.",
        errorEnterLast4: "Introduce los últimos 4 dígitos.",
        errorInvalidExpiry: "Fecha inválida (MM/AA).",
        confirmDeleteMethod: "¿Eliminar \"{alias}\"?",
        
        // Map Bubble - app.js
        bubblePriceLabel: "Precio",
        bubblePriceNotSpecified: "no especificado",
        bubblePublishedBy: "Publicado por",
        bubbleTagCovered: "Cubierto",
        bubbleTagGarage: "Garaje",
        bubbleTagOutdoor: "Exterior",
        bubbleTagElectric: "Carga eléctrica",
        bubbleTagGuarded: "Vigilado",
        bubbleTagAccess24h: "Acceso 24h"
    },
    en: {
        // Básicos
        settings: "Settings",
        language: "Language",
        close: "Close",
        save: "Save",
        back: "Back",
        addMySpot: "Add my spot",
        
        // Búsqueda y Filtros
        searchPlaceholder: "Where are you looking for parking?",
        filters: "Filters",
        filterLocation: "Location",
        filterCovered: "Covered",
        filterGarage: "Garage",
        filterOutdoor: "Outdoor",
        filterExtras: "Extras",
        filterElectric: "Electric charging",
        filterGuarded: "Guarded",
        filterAccess24h: "24h access",
        clear: "Clear",
        apply: "Apply",
        
        // Menú de Usuario
        myProfile: "My profile",
        myReservations: "My reservations",
        mySpots: "My spots",
        paymentMethods: "Payment methods",
        logout: "Logout",
        
        // Notificaciones
        notifications: "Notifications",
        markAllRead: "Mark all as read",
        noNotifications: "No notifications",
        
        // Banners y Mensajes
        spotPublishedSuccess: "Spot published successfully. It now appears on the map.",
        spotSavedNoLocation: "Spot saved, but we couldn't locate it on the map. Make sure the address includes the city (e.g., Calle Larios 2, Málaga).",
        noResultsFound: "No spots found with those criteria.",
        
        // Panel de Detalles
        noImage: "No image",
        reserve: "Reserve",
        
        // Notificaciones del Sistema
        reservationStarted: "Your reservation has started!",
        reservationStartedMsg: "Your reservation at {address} has just begun.",
        fifteenMinutesLeft: "15 minutes left",
        fifteenMinutesLeftMsg: "Your reservation at {address} ends soon.",
        fiveMinutesLeft: "5 minutes left!",
        fiveMinutesLeftMsg: "Pick up your vehicle at {address}.",
        today: "Today",
        
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
        supportReportBug: "Report a bug",
        
        // Settings Page - ajustes.php
        backToMap: "Back to map",
        settingsSubtitle: "Configure your Zpot experience",
        privacySecurity: "Privacy and Security",
        changePassword: "Change password",
        changePasswordDesc: "Update your access password",
        currentPassword: "Current password",
        newPassword: "New password",
        confirmPassword: "Confirm password",
        updatePassword: "Update password",
        deleteAccount: "Delete account",
        deleteAccountDesc: "Permanently delete your account",
        deleteMyAccount: "Delete my account",
        pushNotifications: "Push notifications",
        pushNotificationsDesc: "Receive alerts about your reservations",
        displayLanguage: "Display and Language",
        darkMode: "Dark mode",
        darkModeDesc: "Dark theme for the interface",
        languageDesc: "Select your preferred language",
        deleteAccountQuestion: "Delete account?",
        deleteAccountWarning: "This action is permanent.",
        enterPassword: "Enter your password",
        deletePermanently: "Delete permanently",
        cancel: "Cancel",
        supportlegal: "Support and Legal",
        termsconditions: "Terms and conditions",
        privacypolicy: "Privacy Policy",
        helpcenter: "Help Center",
        
        // Add My Spot Page - alta_plaza.php
        addMySpotTitle: "Add my spot",
        addMySpotSubtitle: "Publish your parking spot or garage. Fields with (*) are required.",
        address: "Address",
        addressPlaceholder: "E.g.: Calle Larios 2, Málaga",
        addressHint: "Always include the city so it appears on the map.",
        addressRequired: "Address is required",
        addressCityRequired: "Include the city separated by comma (e.g.: Calle Larios 2, Málaga)",
        spotPhoto: "Spot photo",
        selectImage: "Select image",
        removeImage: "Remove image",
        invalidImageFile: "Please select a valid image file",
        imageTooLarge: "Image must not exceed 5MB",
        width: "Width (m)",
        length: "Length (m)",
        widthPlaceholder: "2.5",
        lengthPlaceholder: "5",
        invalidNumber: "Must be a number ≥ 0",
        location: "Location",
        covered: "Covered",
        garage: "Garage",
        outdoor: "Outdoor / Open air",
        extras: "Extras",
        electricCharging: "Electric charging",
        guarded: "Guarded",
        access24h: "24h access",
        description: "Description",
        descriptionPlaceholder: "Parking details, access, security...",
        pricePerHour: "Price (€/h)",
        pricePlaceholder: "4.50",
        priceRequired: "Price is required",
        pricePositive: "Price must be greater than 0",
        publishSpot: "Publish spot",
        publishing: "Publishing…",
        saveError: "Error saving. Please try again.",
        connectionError: "Connection error. Please try again.",
        imageProcessError: "Error processing image. Please try again.",
        
        // My Profile Page - mi_perfil.php
        myProfileTitle: "My profile",
        myProfileSubtitle: "Manage your personal information on Zpot",
        firstName: "First name",
        firstNamePlaceholder: "Your first name",
        lastName: "Last name",
        lastNamePlaceholder: "Your last name",
        email: "Email",
        emailPlaceholder: "your@email.com",
        phone: "Phone",
        phonePlaceholder: "Contact number",
        addressField: "Address",
        addressFieldPlaceholder: "Your address",
        saveChanges: "Save changes",
        changesSaved: "Changes saved successfully.",
        requiredFieldsError: "First name, last name and email are required.",
        validEmailRequired: "First name, last name and a valid email are required.",
        
        // My Reservations Page - mis_reservas.php
        myReservationsTitle: "My reservations",
        myReservationsSubtitle: "Manage your upcoming stays and review your history.",
        noReservationsYet: "You haven't made any reservations yet.",
        searchSpot: "Search for a spot",
        statusConfirmed: "Confirmed",
        statusPending: "Pending payment",
        statusCancelled: "Cancelled",
        statusCompleted: "Completed",
        dateLabel: "Date",
        scheduleLabel: "Schedule",
        durationLabel: "Duration",
        hoursLabel: "hours",
        chatButton: "Chat",
        payNowButton: "Pay now",
        cancelButton: "Cancel",
        confirmCancelReservation: "Are you sure you want to cancel this reservation?",
        
        // My Spots Page - mis_plazas.php
        mySpotsTitle: "My published spots",
        mySpotsSubtitle: "Manage your parking spot listings or add new ones.",
        receivedReservations: "Received reservations",
        spotUpdatedSuccess: "Spot updated successfully.",
        noSpotsYet: "You haven't published any spots yet.",
        publishFirstSpot: "Publish my first spot",
        addressLabel: "Address",
        priceLabel: "Price",
        measurementsLabel: "Measurements",
        notSpecified: "Not specified",
        editButton: "Edit",
        deleteButton: "Delete",
        confirmDeleteSpot: "Are you sure you want to delete this listing?",
        
        // Received Reservations Page - reservas_propietario.php
        backToMySpots: "Back to my spots",
        receivedReservationsTitle: "Reservations at my spots",
        receivedReservationsSubtitle: "Manage reservations made by other users at your spots and chat with them.",
        noReceivedReservations: "There are no confirmed reservations at your spots yet.",
        viewMySpots: "View my spots",
        tenantLabel: "Tenant",
        newReservationBadge: "New",
        
        // Payment Methods Page - metodos_pago.php
        paymentMethodsTitle: "Payment methods",
        paymentMethodsSubtitle: "Manage your payment methods for future reservations",
        savedMethodsTitle: "Your saved methods",
        loadingMethods: "Loading payment methods…",
        addMethodTitle: "Add method",
        addPayPalButton: "Add PayPal account",
        addCardButton: "Add credit / debit card",
        securityInfo: "Your payment data is protected. Card numbers are securely processed by PayPal and never stored on our servers. We only save the last 4 digits as a visual reference.",
        noPaymentMethods: "You don't have any saved payment methods.<br>Add one to speed up your reservations.",
        defaultBadge: "Default",
        setAsDefaultTitle: "Set as default",
        deleteTitle: "Delete",
        addPaymentMethodTitle: "Add payment method",
        addPayPalTitle: "Add PayPal account",
        addCardTitle: "Add card",
        aliasLabel: "Alias (name you'll see)",
        aliasPayPalPlaceholder: "E.g.: My personal PayPal",
        paypalEmailLabel: "PayPal email",
        aliasCardPlaceholder: "E.g.: Personal Visa",
        cardBrandLabel: "Card brand",
        otherBrandButton: "Other",
        last4DigitsLabel: "Last 4 digits",
        last4DigitsHint: "Only the last 4 digits — we never store the full number.",
        expiryLabel: "Expiry",
        setAsDefaultLabel: "Set as default payment method",
        saveButton: "Save",
        errorEnterAlias: "Enter an alias.",
        errorEnterPayPalEmail: "Enter PayPal email.",
        errorSelectBrand: "Select the brand.",
        errorEnterLast4: "Enter the last 4 digits.",
        errorInvalidExpiry: "Invalid date (MM/YY).",
        confirmDeleteMethod: "Delete \"{alias}\"?",
        
        // Map Bubble - app.js
        bubblePriceLabel: "Price",
        bubblePriceNotSpecified: "not specified",
        bubblePublishedBy: "Published by",
        bubbleTagCovered: "Covered",
        bubbleTagGarage: "Garage",
        bubbleTagOutdoor: "Outdoor",
        bubbleTagElectric: "Electric charging",
        bubbleTagGuarded: "Guarded",
        bubbleTagAccess24h: "24h access"
    },
    fr: {
        // Básicos
        settings: "Paramètres",
        language: "Langue",
        close: "Fermer",
        save: "Enregistrer",
        back: "Retour",
        addMySpot: "Ajouter ma place",
        
        // Búsqueda y Filtros
        searchPlaceholder: "Où cherchez-vous un parking ?",
        filters: "Filtres",
        filterLocation: "Emplacement",
        filterCovered: "Couvert",
        filterGarage: "Garage",
        filterOutdoor: "Extérieur",
        filterExtras: "Extras",
        filterElectric: "Recharge électrique",
        filterGuarded: "Surveillé",
        filterAccess24h: "Accès 24h",
        clear: "Effacer",
        apply: "Appliquer",
        
        // Menú de Usuario
        myProfile: "Mon profil",
        myReservations: "Mes réservations",
        mySpots: "Mes places",
        paymentMethods: "Moyens de paiement",
        logout: "Déconnexion",
        
        // Notificaciones
        notifications: "Notifications",
        markAllRead: "Tout marquer comme lu",
        noNotifications: "Aucune notification",
        
        // Banners y Mensajes
        spotPublishedSuccess: "Place publiée avec succès. Elle apparaît maintenant sur la carte.",
        spotSavedNoLocation: "Place enregistrée, mais nous n'avons pas pu la localiser sur la carte. Assurez-vous que l'adresse inclut la ville (ex: Calle Larios 2, Málaga).",
        noResultsFound: "Aucune place trouvée avec ces critères.",
        
        // Panel de Detalles
        noImage: "Pas d'image",
        reserve: "Réserver",
        
        // Notificaciones del Sistema
        reservationStarted: "Votre réservation a commencé !",
        reservationStartedMsg: "Votre réservation à {address} vient de commencer.",
        fifteenMinutesLeft: "15 minutes restantes",
        fifteenMinutesLeftMsg: "Votre réservation à {address} se termine bientôt.",
        fiveMinutesLeft: "5 minutes restantes !",
        fiveMinutesLeftMsg: "Récupérez votre véhicule à {address}.",
        today: "Aujourd'hui",
        
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
        supportReportBug: "Signaler un bug",
        
        // Settings Page - ajustes.php
        backToMap: "Retour à la carte",
        settingsSubtitle: "Configurez votre expérience Zpot",
        privacySecurity: "Confidentialité et Sécurité",
        changePassword: "Changer le mot de passe",
        changePasswordDesc: "Mettez à jour votre mot de passe d'accès",
        currentPassword: "Mot de passe actuel",
        newPassword: "Nouveau mot de passe",
        confirmPassword: "Confirmer le mot de passe",
        updatePassword: "Mettre à jour le mot de passe",
        deleteAccount: "Supprimer le compte",
        deleteAccountDesc: "Supprimer définitivement votre compte",
        deleteMyAccount: "Supprimer mon compte",
        pushNotifications: "Notifications push",
        pushNotificationsDesc: "Recevez des alertes sur vos réservations",
        displayLanguage: "Affichage et Langue",
        darkMode: "Mode sombre",
        darkModeDesc: "Thème sombre pour l'interface",
        languageDesc: "Sélectionnez votre langue préférée",
        deleteAccountQuestion: "Supprimer le compte ?",
        deleteAccountWarning: "Cette action est permanente.",
        enterPassword: "Entrez votre mot de passe",
        deletePermanently: "Supprimer définitivement",
        cancel: "Annuler",
        supportlegal: "Support et juridique",
        termsconditions: "Termes et conditions",
        privacypolicy: "Politique de confidentialité",
        helpcenter: "Centre d'aide",
        
        // Add My Spot Page - alta_plaza.php
        addMySpotTitle: "Ajouter ma place",
        addMySpotSubtitle: "Publiez votre place de parking ou garage. Les champs avec (*) sont obligatoires.",
        address: "Adresse",
        addressPlaceholder: "Ex: Calle Larios 2, Málaga",
        addressHint: "Incluez toujours la ville pour qu'elle apparaisse sur la carte.",
        addressRequired: "L'adresse est obligatoire",
        addressCityRequired: "Incluez la ville séparée par une virgule (ex: Calle Larios 2, Málaga)",
        spotPhoto: "Photo de la place",
        selectImage: "Sélectionner une image",
        removeImage: "Supprimer l'image",
        invalidImageFile: "Veuillez sélectionner un fichier image valide",
        imageTooLarge: "L'image ne doit pas dépasser 5 Mo",
        width: "Largeur (m)",
        length: "Longueur (m)",
        widthPlaceholder: "2.5",
        lengthPlaceholder: "5",
        invalidNumber: "Doit être un nombre ≥ 0",
        location: "Emplacement",
        covered: "Couvert",
        garage: "Garage",
        outdoor: "Extérieur / En plein air",
        extras: "Extras",
        electricCharging: "Recharge électrique",
        guarded: "Surveillé",
        access24h: "Accès 24h",
        description: "Description",
        descriptionPlaceholder: "Détails du parking, accès, sécurité...",
        pricePerHour: "Prix (€/h)",
        pricePlaceholder: "4.50",
        priceRequired: "Le prix est obligatoire",
        pricePositive: "Le prix doit être supérieur à 0",
        publishSpot: "Publier la place",
        publishing: "Publication en cours…",
        saveError: "Erreur lors de l'enregistrement. Veuillez réessayer.",
        connectionError: "Erreur de connexion. Veuillez réessayer.",
        imageProcessError: "Erreur lors du traitement de l'image. Veuillez réessayer.",
        
        // My Profile Page - mi_perfil.php
        myProfileTitle: "Mon profil",
        myProfileSubtitle: "Gérez vos informations personnelles sur Zpot",
        firstName: "Prénom",
        firstNamePlaceholder: "Votre prénom",
        lastName: "Nom",
        lastNamePlaceholder: "Votre nom",
        email: "Email",
        emailPlaceholder: "votre@email.com",
        phone: "Téléphone",
        phonePlaceholder: "Numéro de contact",
        addressField: "Adresse",
        addressFieldPlaceholder: "Votre adresse",
        saveChanges: "Enregistrer les modifications",
        changesSaved: "Modifications enregistrées avec succès.",
        requiredFieldsError: "Le prénom, le nom et l'email sont obligatoires.",
        validEmailRequired: "Le prénom, le nom et un email valide sont obligatoires.",
        
        // My Reservations Page - mis_reservas.php
        myReservationsTitle: "Mes réservations",
        myReservationsSubtitle: "Gérez vos prochains séjours et consultez votre historique.",
        noReservationsYet: "Vous n'avez pas encore effectué de réservation.",
        searchSpot: "Rechercher une place",
        statusConfirmed: "Confirmée",
        statusPending: "En attente de paiement",
        statusCancelled: "Annulée",
        statusCompleted: "Terminée",
        dateLabel: "Date",
        scheduleLabel: "Horaire",
        durationLabel: "Durée",
        hoursLabel: "heures",
        chatButton: "Chat",
        payNowButton: "Payer maintenant",
        cancelButton: "Annuler",
        confirmCancelReservation: "Êtes-vous sûr de vouloir annuler cette réservation ?",
        
        // My Spots Page - mis_plazas.php
        mySpotsTitle: "Mes places publiées",
        mySpotsSubtitle: "Gérez les annonces de vos places de parking ou ajoutez-en de nouvelles.",
        receivedReservations: "Réservations reçues",
        spotUpdatedSuccess: "Place mise à jour avec succès.",
        noSpotsYet: "Vous n'avez pas encore publié de place.",
        publishFirstSpot: "Publier ma première place",
        addressLabel: "Adresse",
        priceLabel: "Prix",
        measurementsLabel: "Dimensions",
        notSpecified: "Non spécifié",
        editButton: "Modifier",
        deleteButton: "Supprimer",
        confirmDeleteSpot: "Êtes-vous sûr de vouloir supprimer cette annonce ?",
        
        // Received Reservations Page - reservas_propietario.php
        backToMySpots: "Retour à mes places",
        receivedReservationsTitle: "Réservations dans mes places",
        receivedReservationsSubtitle: "Gérez les réservations effectuées par d'autres utilisateurs dans vos places et discutez avec eux.",
        noReceivedReservations: "Il n'y a pas encore de réservations confirmées dans vos places.",
        viewMySpots: "Voir mes places",
        tenantLabel: "Locataire",
        newReservationBadge: "Nouvelle",
        
        // Payment Methods Page - metodos_pago.php
        paymentMethodsTitle: "Moyens de paiement",
        paymentMethodsSubtitle: "Gérez vos moyens de paiement pour vos futures réservations",
        savedMethodsTitle: "Vos moyens enregistrés",
        loadingMethods: "Chargement des moyens de paiement…",
        addMethodTitle: "Ajouter un moyen",
        addPayPalButton: "Ajouter un compte PayPal",
        addCardButton: "Ajouter une carte de crédit / débit",
        securityInfo: "Vos données de paiement sont protégées. Les numéros de carte sont traités en toute sécurité par PayPal et ne sont jamais stockés sur nos serveurs. Nous ne conservons que les 4 derniers chiffres comme référence visuelle.",
        noPaymentMethods: "Vous n'avez aucun moyen de paiement enregistré.<br>Ajoutez-en un pour accélérer vos réservations.",
        defaultBadge: "Par défaut",
        setAsDefaultTitle: "Définir par défaut",
        deleteTitle: "Supprimer",
        addPaymentMethodTitle: "Ajouter un moyen de paiement",
        addPayPalTitle: "Ajouter un compte PayPal",
        addCardTitle: "Ajouter une carte",
        aliasLabel: "Alias (nom que vous verrez)",
        aliasPayPalPlaceholder: "Ex: Mon PayPal personnel",
        paypalEmailLabel: "Email PayPal",
        aliasCardPlaceholder: "Ex: Visa personnelle",
        cardBrandLabel: "Marque de la carte",
        otherBrandButton: "Autre",
        last4DigitsLabel: "4 derniers chiffres",
        last4DigitsHint: "Seulement les 4 derniers chiffres — nous ne stockons jamais le numéro complet.",
        expiryLabel: "Expiration",
        setAsDefaultLabel: "Définir comme moyen de paiement par défaut",
        saveButton: "Enregistrer",
        errorEnterAlias: "Entrez un alias.",
        errorEnterPayPalEmail: "Entrez l'email PayPal.",
        errorSelectBrand: "Sélectionnez la marque.",
        errorEnterLast4: "Entrez les 4 derniers chiffres.",
        errorInvalidExpiry: "Date invalide (MM/AA).",
        confirmDeleteMethod: "Supprimer \"{alias}\" ?",
        
        // Map Bubble - app.js
        bubblePriceLabel: "Prix",
        bubblePriceNotSpecified: "non spécifié",
        bubblePublishedBy: "Publié par",
        bubbleTagCovered: "Couvert",
        bubbleTagGarage: "Garage",
        bubbleTagOutdoor: "Extérieur",
        bubbleTagElectric: "Recharge électrique",
        bubbleTagGuarded: "Surveillé",
        bubbleTagAccess24h: "Accès 24h"
    },
    de: {
        // Básicos
        settings: "Einstellungen",
        language: "Sprache",
        close: "Schließen",
        save: "Speichern",
        back: "Zurück",
        addMySpot: "Meinen Platz hinzufügen",
        
        // Búsqueda y Filtros
        searchPlaceholder: "Wo suchen Sie einen Parkplatz?",
        filters: "Filter",
        filterLocation: "Standort",
        filterCovered: "Überdacht",
        filterGarage: "Garage",
        filterOutdoor: "Im Freien",
        filterExtras: "Extras",
        filterElectric: "Elektrisches Laden",
        filterGuarded: "Bewacht",
        filterAccess24h: "24h Zugang",
        clear: "Löschen",
        apply: "Anwenden",
        
        // Menú de Usuario
        myProfile: "Mein Profil",
        myReservations: "Meine Reservierungen",
        mySpots: "Meine Plätze",
        paymentMethods: "Zahlungsmethoden",
        logout: "Abmelden",
        
        // Notificaciones
        notifications: "Benachrichtigungen",
        markAllRead: "Alle als gelesen markieren",
        noNotifications: "Keine Benachrichtigungen",
        
        // Banners y Mensajes
        spotPublishedSuccess: "Platz erfolgreich veröffentlicht. Er erscheint jetzt auf der Karte.",
        spotSavedNoLocation: "Platz gespeichert, aber wir konnten ihn nicht auf der Karte lokalisieren. Stellen Sie sicher, dass die Adresse die Stadt enthält (z.B. Calle Larios 2, Málaga).",
        noResultsFound: "Keine Plätze mit diesen Kriterien gefunden.",
        
        // Panel de Detalles
        noImage: "Kein Bild",
        reserve: "Reservieren",
        
        // Notificaciones del Sistema
        reservationStarted: "Ihre Reservierung hat begonnen!",
        reservationStartedMsg: "Ihre Reservierung bei {address} hat gerade begonnen.",
        fifteenMinutesLeft: "15 Minuten verbleibend",
        fifteenMinutesLeftMsg: "Ihre Reservierung bei {address} endet bald.",
        fiveMinutesLeft: "5 Minuten verbleibend!",
        fiveMinutesLeftMsg: "Holen Sie Ihr Fahrzeug bei {address} ab.",
        today: "Heute",
        
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
        supportReportBug: "Fehler melden",
        
        // Settings Page - ajustes.php
        backToMap: "Zurück zur Karte",
        settingsSubtitle: "Konfigurieren Sie Ihre Zpot-Erfahrung",
        privacySecurity: "Datenschutz und Sicherheit",
        changePassword: "Passwort ändern",
        changePasswordDesc: "Aktualisieren Sie Ihr Zugangspasswort",
        currentPassword: "Aktuelles Passwort",
        newPassword: "Neues Passwort",
        confirmPassword: "Passwort bestätigen",
        updatePassword: "Passwort aktualisieren",
        deleteAccount: "Konto löschen",
        deleteAccountDesc: "Ihr Konto dauerhaft löschen",
        deleteMyAccount: "Mein Konto löschen",
        pushNotifications: "Push-Benachrichtigungen",
        pushNotificationsDesc: "Erhalten Sie Benachrichtigungen über Ihre Reservierungen",
        displayLanguage: "Anzeige und Sprache",
        darkMode: "Dunkler Modus",
        darkModeDesc: "Dunkles Thema für die Benutzeroberfläche",
        languageDesc: "Wählen Sie Ihre bevorzugte Sprache",
        deleteAccountQuestion: "Konto löschen?",
        deleteAccountWarning: "Diese Aktion ist dauerhaft.",
        enterPassword: "Geben Sie Ihr Passwort ein",
        deletePermanently: "Dauerhaft löschen",
        cancel: "Abbrechen",
        supportlegal: "Unterstützung und Recht",
        termsconditions: "Allgemeine Geschäftsbedingungen",
        privacypolicy: "Datenschutzrichtlinie",
        helpcenter: "Hilfezentrum",
        
        // Add My Spot Page - alta_plaza.php
        addMySpotTitle: "Meinen Platz hinzufügen",
        addMySpotSubtitle: "Veröffentlichen Sie Ihren Parkplatz oder Ihre Garage. Felder mit (*) sind erforderlich.",
        address: "Adresse",
        addressPlaceholder: "Z.B.: Calle Larios 2, Málaga",
        addressHint: "Geben Sie immer die Stadt an, damit sie auf der Karte erscheint.",
        addressRequired: "Adresse ist erforderlich",
        addressCityRequired: "Geben Sie die Stadt durch Komma getrennt an (z.B.: Calle Larios 2, Málaga)",
        spotPhoto: "Platzfoto",
        selectImage: "Bild auswählen",
        removeImage: "Bild entfernen",
        invalidImageFile: "Bitte wählen Sie eine gültige Bilddatei",
        imageTooLarge: "Bild darf 5 MB nicht überschreiten",
        width: "Breite (m)",
        length: "Länge (m)",
        widthPlaceholder: "2.5",
        lengthPlaceholder: "5",
        invalidNumber: "Muss eine Zahl ≥ 0 sein",
        location: "Standort",
        covered: "Überdacht",
        garage: "Garage",
        outdoor: "Im Freien / Draußen",
        extras: "Extras",
        electricCharging: "Elektrisches Laden",
        guarded: "Bewacht",
        access24h: "24h Zugang",
        description: "Beschreibung",
        descriptionPlaceholder: "Parkplatzdetails, Zugang, Sicherheit...",
        pricePerHour: "Preis (€/h)",
        pricePlaceholder: "4.50",
        priceRequired: "Preis ist erforderlich",
        pricePositive: "Preis muss größer als 0 sein",
        publishSpot: "Platz veröffentlichen",
        publishing: "Wird veröffentlicht…",
        saveError: "Fehler beim Speichern. Bitte versuchen Sie es erneut.",
        connectionError: "Verbindungsfehler. Bitte versuchen Sie es erneut.",
        imageProcessError: "Fehler beim Verarbeiten des Bildes. Bitte versuchen Sie es erneut.",
        
        // My Profile Page - mi_perfil.php
        myProfileTitle: "Mein Profil",
        myProfileSubtitle: "Verwalten Sie Ihre persönlichen Informationen auf Zpot",
        firstName: "Vorname",
        firstNamePlaceholder: "Ihr Vorname",
        lastName: "Nachname",
        lastNamePlaceholder: "Ihr Nachname",
        email: "E-Mail",
        emailPlaceholder: "ihre@email.com",
        phone: "Telefon",
        phonePlaceholder: "Kontaktnummer",
        addressField: "Adresse",
        addressFieldPlaceholder: "Ihre Adresse",
        saveChanges: "Änderungen speichern",
        changesSaved: "Änderungen erfolgreich gespeichert.",
        requiredFieldsError: "Vorname, Nachname und E-Mail sind erforderlich.",
        validEmailRequired: "Vorname, Nachname und eine gültige E-Mail sind erforderlich.",
        
        // My Reservations Page - mis_reservas.php
        myReservationsTitle: "Meine Reservierungen",
        myReservationsSubtitle: "Verwalten Sie Ihre bevorstehenden Aufenthalte und überprüfen Sie Ihren Verlauf.",
        noReservationsYet: "Sie haben noch keine Reservierungen vorgenommen.",
        searchSpot: "Einen Platz suchen",
        statusConfirmed: "Bestätigt",
        statusPending: "Zahlung ausstehend",
        statusCancelled: "Storniert",
        statusCompleted: "Abgeschlossen",
        dateLabel: "Datum",
        scheduleLabel: "Zeitplan",
        durationLabel: "Dauer",
        hoursLabel: "Stunden",
        chatButton: "Chat",
        payNowButton: "Jetzt bezahlen",
        cancelButton: "Stornieren",
        confirmCancelReservation: "Sind Sie sicher, dass Sie diese Reservierung stornieren möchten?",
        
        // My Spots Page - mis_plazas.php
        mySpotsTitle: "Meine veröffentlichten Plätze",
        mySpotsSubtitle: "Verwalten Sie Ihre Parkplatzanzeigen oder fügen Sie neue hinzu.",
        receivedReservations: "Erhaltene Reservierungen",
        spotUpdatedSuccess: "Platz erfolgreich aktualisiert.",
        noSpotsYet: "Sie haben noch keine Plätze veröffentlicht.",
        publishFirstSpot: "Meinen ersten Platz veröffentlichen",
        addressLabel: "Adresse",
        priceLabel: "Preis",
        measurementsLabel: "Maße",
        notSpecified: "Nicht angegeben",
        editButton: "Bearbeiten",
        deleteButton: "Löschen",
        confirmDeleteSpot: "Sind Sie sicher, dass Sie diese Anzeige löschen möchten?",
        
        // Received Reservations Page - reservas_propietario.php
        backToMySpots: "Zurück zu meinen Plätzen",
        receivedReservationsTitle: "Reservierungen an meinen Plätzen",
        receivedReservationsSubtitle: "Verwalten Sie Reservierungen anderer Benutzer an Ihren Plätzen und chatten Sie mit ihnen.",
        noReceivedReservations: "Es gibt noch keine bestätigten Reservierungen an Ihren Plätzen.",
        viewMySpots: "Meine Plätze ansehen",
        tenantLabel: "Mieter",
        newReservationBadge: "Neu",
        
        // Payment Methods Page - metodos_pago.php
        paymentMethodsTitle: "Zahlungsmethoden",
        paymentMethodsSubtitle: "Verwalten Sie Ihre Zahlungsmethoden für zukünftige Reservierungen",
        savedMethodsTitle: "Ihre gespeicherten Methoden",
        loadingMethods: "Zahlungsmethoden werden geladen…",
        addMethodTitle: "Methode hinzufügen",
        addPayPalButton: "PayPal-Konto hinzufügen",
        addCardButton: "Kredit- / Debitkarte hinzufügen",
        securityInfo: "Ihre Zahlungsdaten sind geschützt. Kartennummern werden sicher von PayPal verarbeitet und niemals auf unseren Servern gespeichert. Wir speichern nur die letzten 4 Ziffern als visuelle Referenz.",
        noPaymentMethods: "Sie haben keine gespeicherten Zahlungsmethoden.<br>Fügen Sie eine hinzu, um Ihre Reservierungen zu beschleunigen.",
        defaultBadge: "Standard",
        setAsDefaultTitle: "Als Standard festlegen",
        deleteTitle: "Löschen",
        addPaymentMethodTitle: "Zahlungsmethode hinzufügen",
        addPayPalTitle: "PayPal-Konto hinzufügen",
        addCardTitle: "Karte hinzufügen",
        aliasLabel: "Alias (Name, den Sie sehen werden)",
        aliasPayPalPlaceholder: "Z.B.: Mein persönliches PayPal",
        paypalEmailLabel: "PayPal-E-Mail",
        aliasCardPlaceholder: "Z.B.: Persönliche Visa",
        cardBrandLabel: "Kartenmarke",
        otherBrandButton: "Andere",
        last4DigitsLabel: "Letzte 4 Ziffern",
        last4DigitsHint: "Nur die letzten 4 Ziffern — wir speichern niemals die vollständige Nummer.",
        expiryLabel: "Ablauf",
        setAsDefaultLabel: "Als Standard-Zahlungsmethode festlegen",
        saveButton: "Speichern",
        errorEnterAlias: "Geben Sie einen Alias ein.",
        errorEnterPayPalEmail: "Geben Sie die PayPal-E-Mail ein.",
        errorSelectBrand: "Wählen Sie die Marke.",
        errorEnterLast4: "Geben Sie die letzten 4 Ziffern ein.",
        errorInvalidExpiry: "Ungültiges Datum (MM/JJ).",
        confirmDeleteMethod: "\"{alias}\" löschen?",
        
        // Map Bubble - app.js
        bubblePriceLabel: "Preis",
        bubblePriceNotSpecified: "nicht angegeben",
        bubblePublishedBy: "Veröffentlicht von",
        bubbleTagCovered: "Überdacht",
        bubbleTagGarage: "Garage",
        bubbleTagOutdoor: "Im Freien",
        bubbleTagElectric: "Elektrisches Laden",
        bubbleTagGuarded: "Bewacht",
        bubbleTagAccess24h: "24h Zugang"
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
