(function () {
    const mobileMenu = document.getElementById('mobile-menu');
    const navToggle = document.querySelector('.mobile-nav-toggle');
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const body = document.body;

   
    function handleMobileNav() {
        mobileMenu.classList.toggle('is-open');

        const isExpanded = mobileMenu.classList.contains('is-open');
        
        navToggle.setAttribute('aria-expanded', isExpanded);
        document.body.classList.toggle('no-scroll', isExpanded);
    }


    function toggleDarkMode() {
        const currentTheme = body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        body.setAttribute('data-theme', currentTheme);
        localStorage.setItem('theme', currentTheme);
        
        const icon = darkModeToggle.querySelector('i');
        const textSpan = darkModeToggle.querySelector('.desktop-hidden');

        if (currentTheme === 'dark') {
            icon.className = 'fas fa-sun';
            if (textSpan) textSpan.textContent = 'Light Mode';
        } else {
            icon.className = 'fas fa-moon';
            if (textSpan) textSpan.textContent = 'Dark Mode';
        }
    }

    // LIGHT MODE DEFAULT
    function loadTheme() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        body.setAttribute('data-theme', savedTheme);
        
        const icon = darkModeToggle.querySelector('i');
        const textSpan = darkModeToggle.querySelector('.desktop-hidden');
        if (savedTheme === 'dark') {
            icon.className = 'fas fa-sun';
            if (textSpan) textSpan.textContent = 'Light Mode';
        } else {
            icon.className = 'fas fa-moon';
            if (textSpan) textSpan.textContent = 'Dark Mode';
        }
    }

 
    function showModal(title, message) {
        if (document.querySelector('.modal-overlay')) return;

        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.setAttribute('aria-hidden', 'false');
        
        const modalContent = document.createElement('div');
        modalContent.className = 'modal-content';
        modalContent.setAttribute('role', 'dialog');
        modalContent.setAttribute('aria-modal', 'true');
        modalContent.setAttribute('aria-label', title);
        
        modalContent.innerHTML = `
            <h4>${title}</h4>
            <p>${message}</p>
            <button class="btn action-button" aria-label="Close modal">Close</button>
        `;
        
        overlay.appendChild(modalContent);
        document.body.appendChild(overlay);

        const removeModal = () => overlay.remove();
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) removeModal();
        });
        const closeBtn = modalContent.querySelector('.action-button');
        closeBtn.addEventListener('click', removeModal);
    }

   
    function init() {
        loadTheme();

        const yearEl = document.getElementById('current-year');
        if (yearEl) {
             yearEl.textContent = new Date().getFullYear();
        }
        
        // Mobile Navigation
        if (navToggle) {
            navToggle.addEventListener('click', handleMobileNav);
        }
        
        //  Dark Mode Toggle
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', toggleDarkMode);
        }

       // Redirect buttons to signin.html with optional redirect parameter
        const buttons = [
            { id: 'cta-go-to-app-mobile', redirect: null },
            { id: 'cta-free-trial', redirect: null },
            { id: 'cta-meet-team', redirect: 'about' },
            { id: 'cta-overview-app', redirect: null }
        ];

        buttons.forEach(config => {
            const btn = document.getElementById(config.id);
            if (btn) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    // Build signin URL with optional redirect parameter
                    let signinUrl = 'signin.html';
                    if (config.redirect) {
                        signinUrl += '?redirect=' + encodeURIComponent(config.redirect);
                    }
                    window.location.href = signinUrl;
                });
            }
        });
        
        //  Overview Scroll Link (smooth scroll)
        const scrollLink = document.getElementById('scroll-to-overview-btn');
        if (scrollLink) {
            scrollLink.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.getElementById('overview');
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                    if (mobileMenu.classList.contains('is-open')) {
                         handleMobileNav();
                    }
                }
            });
        }
    }

    // Initialize once the DOM is fully loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();