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
  
  // Note: Theme toggle functionality has been moved to theme.js
  
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
  
  // Animation for expertise cards if they exist
  if (document.querySelector('.expertise-card')) {
    gsap.from(".expertise-card", {
      duration: 0.8,
      opacity: 0,
      y: 30,
      stagger: 0.15,
      ease: "power3.out",
      scrollTrigger: {
        trigger: ".freelance-expertise",
        start: "top 80%"
      }
    });
  }
});