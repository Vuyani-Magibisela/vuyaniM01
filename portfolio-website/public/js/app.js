document.addEventListener("DOMContentLoaded", () => {
    // Animation for the hero section
    gsap.from(".hero-content", {
      duration: 1,
      opacity: 0,
      y: 30,
      stagger: 0.2,
      ease: "power3.out"
    });
    
    gsap.from(".hero-image", {
      duration: 1,
      opacity: 0,
      x: -30,
      ease: "power3.out"
    });
    
    // Animation for the services section
    gsap.from(".service-card", {
      duration: 0.8,
      opacity: 0,
      y: 50,
      stagger: 0.15,
      ease: "power3.out",
      scrollTrigger: {
        trigger: ".services",
        start: "top 80%"
      }
    });
    
    // Light mode toggle
    const lightModeToggle = document.querySelector('.light-mode');
    let isLightMode = true;
    
    lightModeToggle.addEventListener('click', (e) => {
      e.preventDefault();
      isLightMode = !isLightMode;
      
      if (isLightMode) {
        document.body.style.backgroundColor = "#f9f9f9";
        document.body.style.color = "#333";
        lightModeToggle.textContent = "🌞";
      } else {
        document.body.style.backgroundColor = "#333";
        document.body.style.color = "#f9f9f9";
        lightModeToggle.textContent = "🌙";
        
        // Adjust links and other elements for dark mode
        document.querySelectorAll('.navigation a').forEach(link => {
          if (!link.classList.contains('active') && !link.classList.contains('light-mode')) {
            link.style.color = "#f9f9f9";
          }
        });
        
        document.querySelectorAll('.service-description, .about-text, .connect-intro, .social-description').forEach(text => {
          text.style.color = "#ccc";
        });
        
        document.querySelectorAll('.social-link').forEach(link => {
          link.style.color = "#f9f9f9";
          link.style.borderColor = "#555";
        });
      }
    });
    
    // Interactive service cards
    const serviceCards = document.querySelectorAll('.service-card');
    
    serviceCards.forEach(card => {
      card.addEventListener('mouseenter', () => {
        gsap.to(card, {
          duration: 0.3,
          y: -5,
          boxShadow: "0 10px 20px rgba(0, 0, 0, 0.1)"
        });
      });
      
      card.addEventListener('mouseleave', () => {
        gsap.to(card, {
          duration: 0.3,
          y: 0,
          boxShadow: "none"
        });
      });
    });
  });