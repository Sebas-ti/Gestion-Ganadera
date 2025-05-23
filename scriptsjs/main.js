// main.js

document.addEventListener('DOMContentLoaded', () => {
    // Referencias a elementos clave en el HTML
    const languageDropdownItems = document.querySelectorAll('.dropdown-menu [data-lang]');
    const currentLanguageSpan = document.getElementById('currentLanguage'); // Para actualizar el texto 'ES', 'EN', etc.

    /**
     * Aplica las traducciones a los elementos según el idioma seleccionado.
     * Espera que el objeto 'translations' esté disponible globalmente (definido en translations.js).
     * @param {string} lang El código de idioma (por ejemplo, 'es', 'en', 'fr', 'pt').
     */
    const applyTranslations = (lang) => {
        const currentTranslations = window.translations || (typeof translations !== 'undefined' ? translations : null);

        if (!currentTranslations || !currentTranslations[lang]) {
            console.error(`Error: No se encontraron traducciones para el idioma: ${lang}. Asegúrate de que 'translations.js' se cargue y defina el objeto 'translations' correctamente.`);
            return;
        }

        document.querySelectorAll('[data-key]').forEach(element => {
            const key = element.getAttribute('data-key');
            let translatedText = currentTranslations[lang][key];

            if (translatedText) {
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    if (key.includes('-placeholder')) {
                         element.setAttribute('placeholder', translatedText);
                    } else {
                        element.textContent = translatedText;
                    }
                } else if (key === "footer-copyright") {
                    element.innerHTML = translatedText.replace('{year}', new Date().getFullYear());
                } else {
                    element.textContent = translatedText;
                }
            } else {
                console.warn(`Advertencia: Falta la traducción para la clave: '${key}' en el idioma: '${lang}'`);
            }
        });

        // Actualizar el texto del idioma actual en el botón del dropdown
        if (currentLanguageSpan) {
            currentLanguageSpan.textContent = lang.toUpperCase();
        }
    };

    // --- Lógica del Selector de Idioma (para Dropdown) ---
    // Carga la preferencia de idioma guardada en localStorage o usa 'es' por defecto.
    const savedLang = localStorage.getItem('lang') || 'es';

    // Aplica las traducciones iniciales al cargar la página
    applyTranslations(savedLang);

    // Agrega event listeners a cada elemento del dropdown de idioma
    if (languageDropdownItems.length > 0) {
        languageDropdownItems.forEach(item => {
            item.addEventListener('click', (event) => {
                event.preventDefault(); // Evita que el enlace recargue la página
                const newLang = item.getAttribute('data-lang'); // Obtiene el idioma del atributo data-lang
                localStorage.setItem('lang', newLang); // Guarda la nueva preferencia
                applyTranslations(newLang); // Aplica las traducciones para el nuevo idioma
            });
        });
    } else {
        console.warn("Advertencia: No se encontraron elementos con 'data-lang' dentro de '.dropdown-menu'. La funcionalidad de cambio de idioma podría no estar disponible.");
    }


    // --- Inicializaciones de Carrusel de Bootstrap ---
    const contactInfoCarousel = document.getElementById('contactInfoCarousel');
    if (contactInfoCarousel) {
        new bootstrap.Carousel(contactInfoCarousel, {
            interval: 5000,
            wrap: true
        });
    }

    const testimonialsCarousel = document.getElementById('testimonialsCarousel');
    if (testimonialsCarousel) {
        new bootstrap.Carousel(testimonialsCarousel, {
            interval: 6000,
            pause: 'hover'
        });
    }

    // --- Desplazamiento Suave para Enlaces de Navegación ---
    document.querySelectorAll('a.nav-link[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            document.querySelector(targetId).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // --- Animate.css al Desplazarse usando Intersection Observer ---
    const animateOnScrollElements = document.querySelectorAll('.animate__animated');
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animation-triggered');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    animateOnScrollElements.forEach(element => {
        observer.observe(element);
    });
});