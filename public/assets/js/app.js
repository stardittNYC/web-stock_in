/*
 * Public landing page helpers: Lucide icon rendering + language dropdown.
 */
(function () {
    if (window.lucide) {
        window.lucide.createIcons();
    }

    var languageButton = document.getElementById('languageButton');
    var languageMenu = document.getElementById('languageMenu');

    if (languageButton && languageMenu) {
        languageButton.addEventListener('click', function () {
            languageMenu.style.display = languageMenu.style.display === 'block' ? 'none' : 'block';
        });
    }
})();
