document.addEventListener("DOMContentLoaded", () => {
    // Light/Dark mode toggle - select by class since that's what's in the header.php
    const themeToggle = document.querySelector('.light-mode');

    if (!themeToggle) {
        console.error("Theme toggle element not found");
        return;
    }

    // Get theme from localStorage (consistent with all pages)
    const savedTheme = localStorage.getItem('theme') || 'light';
    let isDarkMode = savedTheme === 'dark';

    // Apply saved theme on page load
    if (isDarkMode) {
        enableDarkMode();
    } else {
        enableLightMode();
    }
    
    themeToggle.addEventListener('click', (e) => {
        e.preventDefault();
        
        if (isDarkMode) {
            enableLightMode();
        } else {
            enableDarkMode();
        }
        
        isDarkMode = !isDarkMode;

        // Save theme preference in localStorage (consistent with all pages)
        const newTheme = isDarkMode ? 'dark' : 'light';
        localStorage.setItem('theme', newTheme);
        document.documentElement.setAttribute('data-theme', newTheme);
    });
    
    function enableDarkMode() {
        // Add dark-mode class to body
        document.body.classList.add('dark-mode');
        
        // Update the toggle icon
        themeToggle.textContent = "🌙";
        
        // Update navigation links color
        updateElementColors();
    }
    
    function enableLightMode() {
        // Remove dark-mode class from body
        document.body.classList.remove('dark-mode');
        
        // Update the toggle icon
        themeToggle.textContent = "🌞";
        
        // Update navigation links color
        updateElementColors();
    }
    
    function updateElementColors() {
        // Update the colors of various elements based on theme
        const isDark = document.body.classList.contains('dark-mode');
        
        // Update desktop navigation links
        const desktopNavLinks = document.querySelectorAll('.desktop-navigation a:not(.active)');
        desktopNavLinks.forEach(link => {
            if (!link.classList.contains('light-mode')) {
                link.style.color = isDark ? "#f9f9f9" : "#000000";
            }
        });
        
        // Update text elements
        const textElements = document.querySelectorAll('.service-description, .about-text, .connect-intro, .social-description, .role-description, .expertise-content p');
        textElements.forEach(el => {
            el.style.color = isDark ? "#ccc" : "#666";
        });
        
        // Update card elements
        const cardElements = document.querySelectorAll('.social-link, .client-card, .expertise-card');
        cardElements.forEach(card => {
            if (isDark) {
                card.style.backgroundColor = card.classList.contains('expertise-card') ? "#3a3a3a" : "#444";
                card.style.borderColor = "#555";
                card.style.color = "#f9f9f9";
            } else {
                card.style.backgroundColor = card.classList.contains('expertise-card') ? "#fff" : "";
                card.style.borderColor = "#ddd";
                card.style.color = "#333";
            }
        });
        
        // Update tag elements
        const tagElements = document.querySelectorAll('.expertise-tag');
        tagElements.forEach(tag => {
            tag.style.backgroundColor = isDark ? "#444" : "#f5f5f5";
            tag.style.color = isDark ? "#ddd" : "#555";
        });
    }
    
    // Update colors on page load based on saved theme
    updateElementColors();
});