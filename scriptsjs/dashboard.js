document.addEventListener('DOMContentLoaded', function () {
    var sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            document.body.classList.toggle('toggled'); // Alterna la clase 'toggled' en el body
        });
    }

    // Opcional: Añadir estado activo a los elementos de la barra lateral (ejemplo)
    var listItems = document.querySelectorAll('.list-group-item');
    listItems.forEach(function(item) {
        item.addEventListener('click', function() {
            // Quita la clase 'active' de todos los elementos
            listItems.forEach(function(el) {
                el.classList.remove('active');
            });
            // Añade la clase 'active' al elemento clicado
            this.classList.add('active');
        });
    });
});