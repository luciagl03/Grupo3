# Sistema de Traducciones de Zpot

## 📋 Descripción

Sistema de internacionalización (i18n) para la aplicación Zpot que permite cambiar entre múltiples idiomas de forma dinámica.

## 🚀 Uso Rápido

### Cambiar idioma programáticamente

```javascript
// Cambiar a inglés
changeLanguage('en');

// Cambiar a francés
changeLanguage('fr');

// Cambiar a alemán
changeLanguage('de');

// Cambiar a español
changeLanguage('es');
```

### Obtener el idioma actual

```javascript
const currentLang = getCurrentLanguage();
console.log(currentLang); // 'es', 'en', 'fr', o 'de'
```

### Obtener una traducción específica

```javascript
// Obtener traducción en el idioma actual
const settingsText = t('settings'); // "Ajustes" si el idioma es español

// Obtener traducción en un idioma específico
const saveInEnglish = t('save', 'en'); // "Save"
```

## 📝 Cómo Agregar Traducciones

### Paso 1: Editar translations.js

Abre el archivo `backend/translations.js` y agrega nuevas claves en cada idioma:

```javascript
const translations = {
    es: {
        settings: "Ajustes",
        language: "Idioma",
        close: "Cerrar",
        save: "Guardar",
        back: "Volver",
        // Agrega aquí tus nuevas traducciones
        welcome: "Bienvenido",
        logout: "Cerrar sesión"
    },
    en: {
        settings: "Settings",
        language: "Language",
        close: "Close",
        save: "Save",
        back: "Back",
        // Agrega aquí tus nuevas traducciones
        welcome: "Welcome",
        logout: "Logout"
    },
    // ... repite para fr y de
};
```

### Paso 2: Usar traducciones en HTML

Agrega el atributo `data-i18n` a cualquier elemento HTML:

```html
<h1 data-i18n="settings">Ajustes</h1>
<button data-i18n="save">Guardar</button>
<span data-i18n="close">Cerrar</span>
```

El sistema actualizará automáticamente el contenido cuando cambie el idioma.

### Paso 3: Incluir el script en tu página

```html
<script src="../translations.js"></script>
```

## 🔧 Funciones Disponibles

### `changeLanguage(lang)`
Cambia el idioma de la aplicación y guarda la preferencia en localStorage.

**Parámetros:**
- `lang` (string): Código del idioma ('es', 'en', 'fr', 'de')

**Ejemplo:**
```javascript
changeLanguage('en');
```

### `getCurrentLanguage()`
Obtiene el idioma actual guardado en localStorage o el predeterminado (español).

**Retorna:** string - Código del idioma

**Ejemplo:**
```javascript
const lang = getCurrentLanguage(); // 'es'
```

### `t(key, lang)`
Obtiene una traducción específica.

**Parámetros:**
- `key` (string): Clave de la traducción
- `lang` (string, opcional): Código del idioma. Si no se proporciona, usa el idioma actual

**Retorna:** string - Texto traducido

**Ejemplo:**
```javascript
const text = t('settings'); // "Ajustes" o "Settings" según el idioma actual
const textInEnglish = t('settings', 'en'); // "Settings"
```

### `updateTranslations()`
Actualiza todos los elementos HTML con el atributo `data-i18n`.

**Ejemplo:**
```javascript
updateTranslations(); // Actualiza todos los elementos traducibles
```

## 🎯 Eventos

El sistema dispara un evento personalizado cuando cambia el idioma:

```javascript
window.addEventListener('languageChanged', (event) => {
    console.log('Nuevo idioma:', event.detail.language);
    // Aquí puedes ejecutar código personalizado cuando cambie el idioma
});
```

## 💾 Persistencia

El idioma seleccionado se guarda automáticamente en `localStorage` con la clave `zpot_language` y se restaura al recargar la página.

## 🌍 Idiomas Soportados

- **es** - Español (predeterminado)
- **en** - English
- **fr** - Français
- **de** - Deutsch

## 📦 Términos Actuales

Actualmente el sistema incluye 5 términos básicos de prueba:

1. `settings` - Ajustes / Settings / Paramètres / Einstellungen
2. `language` - Idioma / Language / Langue / Sprache
3. `close` - Cerrar / Close / Fermer / Schließen
4. `save` - Guardar / Save / Enregistrer / Speichern
5. `back` - Volver / Back / Retour / Zurück

**Nota:** Puedes agregar más términos editando el archivo `translations.js` siguiendo la estructura existente.

## 🔗 Integración con el Selector de Idioma

El sistema está integrado con el selector de idioma en la página de ajustes (`backend/settings/ajustes.php`). Cuando el usuario selecciona un idioma del dropdown, se llama automáticamente a `changeLanguage()`.

## 🛠️ Ejemplo Completo

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Página</title>
    <script src="../translations.js"></script>
</head>
<body>
    <h1 data-i18n="settings">Ajustes</h1>
    <button onclick="changeLanguage('en')">English</button>
    <button onclick="changeLanguage('es')">Español</button>
    <button onclick="changeLanguage('fr')">Français</button>
    <button onclick="changeLanguage('de')">Deutsch</button>
    
    <script>
        // Escuchar cambios de idioma
        window.addEventListener('languageChanged', (e) => {
            console.log('Idioma cambiado a:', e.detail.language);
        });
    </script>
</body>
</html>
```

## 📌 Notas Importantes

- El sistema se inicializa automáticamente al cargar la página
- Las traducciones se aplican a elementos con el atributo `data-i18n`
- El idioma se persiste en localStorage entre sesiones
- Si un término no tiene traducción, se muestra la clave original
- Para inputs y textareas, se actualiza el atributo `placeholder`
- Para otros elementos, se actualiza el `textContent`

---

**Desarrollado para Zpot - Sistema de Aparcamientos**
