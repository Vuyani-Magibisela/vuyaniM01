document.addEventListener("DOMContentLoaded", () => {
    // Mobile navigation toggling
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileNavOverlay = document.querySelector('.mobile-nav-overlay');
    const closeMobileNav = document.querySelector('.close-mobile-nav');
    const darkModeBtn = document.querySelector('.dark-mode-btn');
    const lightModeBtn = document.querySelector('.light-mode-btn');
    
    if (mobileMenuToggle && mobileNavOverlay && closeMobileNav) {
        // Open mobile nav
        mobileMenuToggle.addEventListener('click', () => {
            mobileNavOverlay.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        });
        
        // Close mobile nav
        closeMobileNav.addEventListener('click', () => {
            mobileNavOverlay.classList.remove('active');
            document.body.style.overflow = ''; // Re-enable scrolling
        });
        
        // Close nav when clicking on overlay (outside the nav)
        mobileNavOverlay.addEventListener('click', (e) => {
            if (e.target === mobileNavOverlay) {
                mobileNavOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
    
    // Dark/Light mode toggles in mobile nav
    if (darkModeBtn && lightModeBtn) {
        const isDarkMode = document.body.classList.contains('dark-mode');
        
        // Set initial active state
        if (isDarkMode) {
            darkModeBtn.classList.add('active');
        } else {
            lightModeBtn.classList.add('active');
        }
        
        // Dark mode button
        darkModeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            enableDarkMode();
            darkModeBtn.classList.add('active');
            lightModeBtn.classList.remove('active');
        });
        
        // Light mode button
        lightModeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            enableLightMode();
            lightModeBtn.classList.add('active');
            darkModeBtn.classList.remove('active');
        });
        
        function enableDarkMode() {
            document.body.classList.add('dark-mode');
            // Update theme toggle in header
            const themeToggle = document.querySelector('#theme-toggle');
            if (themeToggle) {
                themeToggle.textContent = "🌙";
            }
            
            // Save preference
            setCookie('theme', 'dark', 365);
            
            // Update colors
            updateElementColors(true);
        }
        
        function enableLightMode() {
            document.body.classList.remove('dark-mode');
            // Update theme toggle in header
            const themeToggle = document.querySelector('#theme-toggle');
            if (themeToggle) {
                themeToggle.textContent = "🌞";
            }
            
            // Save preference
            setCookie('theme', 'light', 365);
            
            // Update colors
            updateElementColors(false);
        }
        
        function updateElementColors(isDark) {
            // Similar to the function in theme.js
            // Update navigation links
            const navLinks = document.querySelectorAll('.navigation a:not(.active):not(#theme-toggle)');
            navLinks.forEach(link => {
                link.style.color = isDark ? "#f9f9f9" : "#000000";
            });
            
            // Update other elements as needed
            const textElements = document.querySelectorAll('.service-description, .about-text, .connect-intro, .social-description, .role-description, .expertise-content p');
            textElements.forEach(el => {
                el.style.color = isDark ? "#ccc" : "#666";
            });
        }
        
        // Helper function to set a cookie
        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }
    }
});