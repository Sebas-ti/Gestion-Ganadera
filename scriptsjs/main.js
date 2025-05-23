// main.js
document.addEventListener('DOMContentLoaded', () => {
    const currentLanguageSpan = document.getElementById('currentLanguage');
    const languageDropdownItems = document.querySelectorAll('#languageDropdown + .dropdown-menu .dropdown-item');

    // Función para aplicar las traducciones
    const applyTranslations = (lang) => {
        const currentLangData = translations[lang];
        if (!currentLangData) {
            console.error(`No se encontraron traducciones para el idioma: ${lang}`);
            return;
        }

        // Actualizar todos los elementos con data-key
        document.querySelectorAll('[data-key]').forEach(element => {
            const key = element.getAttribute('data-key');
            if (currentLangData[key]) {
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    element.placeholder = currentLangData[key];
                } else {
                    element.textContent = currentLangData[key];
                }
            }
        });

        // Actualizar el texto del botón del idioma
        if (currentLanguageSpan) {
            currentLanguageSpan.textContent = lang.toUpperCase();
        }

        // Guardar el idioma seleccionado en localStorage
        localStorage.setItem('selectedLanguage', lang);
    };

    // Cargar el idioma guardado o establecer el español por defecto
    const savedLanguage = localStorage.getItem('selectedLanguage') || 'es';
    applyTranslations(savedLanguage);

    // Añadir event listeners a los elementos del dropdown de idioma
    languageDropdownItems.forEach(item => {
        item.addEventListener('click', (event) => {
            event.preventDefault();
            const lang = event.target.getAttribute('data-lang');
            applyTranslations(lang);
        });
    });
});