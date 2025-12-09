    const appNav = document.getElementById('app-nav');
    const menuToggle = document.getElementById('menu-toggle');
    const appContent = document.querySelector('.app-content');
    const appFooter = document.querySelector('.footer');

    // Helper function to toggle the classes
    const toggleMenu = () => {
        appNav.classList.toggle('nav-open');
        // Blur content for backdrop effect
        if (appContent) appContent.classList.toggle('blur-content');
        if (appFooter) appFooter.classList.toggle('blur-content');
        // Add nav-pushed so content shifts right when nav opens (matches dashboard)
        if (appContent) appContent.classList.toggle('nav-pushed');
    };

if (menuToggle) {
    menuToggle.addEventListener('click', toggleMenu);
}

if (appContent) {
    appContent.addEventListener('click', (event) => {
        if (appNav.classList.contains('nav-open') && event.currentTarget.classList.contains('blur-content')) {
            toggleMenu();
        }
    });
}

if (appFooter) {
    appFooter.addEventListener('click', (event) => {
        if (appNav.classList.contains('nav-open') && event.currentTarget.classList.contains('blur-content')) {
            toggleMenu();
        }
    });
}