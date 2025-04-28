document.addEventListener("DOMContentLoaded", () => {
    // Light mode toggle
    const lightModeToggle = document.querySelector('.light-mode');
    let isLightMode = true;
    
    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        enableDarkMode();
        isLightMode = false;
    }
    
    lightModeToggle.addEventListener('click', (e) => {
        e.preventDefault();
        
        if (isLightMode) {
            enableDarkMode();
        } else {
            enableLightMode();
        }
        
        isLightMode = !isLightMode;
        
        // Save theme preference
        localStorage.setItem('theme', isLightMode ? 'light' : 'dark');
    });
    
    function enableDarkMode() {
        document.body.classList.add('dark-mode');
        document.body.style.backgroundColor = "#333";
        document.body.style.color = "#f9f9f9";
        lightModeToggle.textContent = "🌙";
        
        // Adjust links and other elements for dark mode
        document.querySelectorAll('.navigation a').forEach(link => {
            if (!link.classList.contains('active') && !link.classList.contains('light-mode')) {
                link.style.color = "#f9f9f9";
            }
        });
        
        // Update text colors
        document.querySelectorAll('.service-description, .about-text, .connect-intro, .social-description, .role-description').forEach(text => {
            text.style.color = "#ccc";
        });
        
        // Update border colors
        document.querySelectorAll('.social-link, .client-card').forEach(item => {
            item.style.color = "#f9f9f9";
            item.style.borderColor = "#555";
        });
        
        // Update client card backgrounds
        document.querySelectorAll('.client-card').forEach(card => {
            card.style.backgroundColor = "#444";
        });
        
        // Update bullet points in role achievements
        document.querySelectorAll('.role-achievements li:before').forEach(bullet => {
            bullet.style.color = "#f9f9f9";
        });
    }
    
    function enableLightMode() {
        document.body.classList.remove('dark-mode');
        document.body.style.backgroundColor = "#f9f9f9";
        document.body.style.color = "#333";
        lightModeToggle.textContent = "🌞";
        
        // Reset link colors
        document.querySelectorAll('.navigation a').forEach(link => {
            if (!link.classList.contains('active') && !link.classList.contains('light-mode')) {
                link.style.color = "#000000";
            }
        });
        
        // Reset text colors
        document.querySelectorAll('.service-description, .about-text, .connect-intro, .social-description, .role-description').forEach(text => {
            text.style.color = "#666";
        });
        
        // Reset border colors
        document.querySelectorAll('.social-link, .client-card').forEach(item => {
            item.style.color = "#333";
            item.style.borderColor = "#ddd";
        });
        
        // Reset client card backgrounds
        document.querySelectorAll('.client-card').forEach(card => {
            card.style.backgroundColor = "transparent";
        });
        
        // Reset bullet points in role achievements
        document.querySelectorAll('.role-achievements li:before').forEach(bullet => {
            bullet.style.color = "#333";
        });
    }
});