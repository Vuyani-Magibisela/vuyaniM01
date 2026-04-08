document.addEventListener("DOMContentLoaded", () => {

  // Header shrink on scroll
  const header = document.querySelector('header');
  if (header) {
    window.addEventListener('scroll', () => {
      header.classList.toggle('scrolled', window.scrollY > 60);
    }, { passive: true });
  }

  // Hero section — staggered entrance
  if (document.querySelector('.hero')) {
    gsap.from('.hero-label', {
      duration: 0.8,
      x: -20,
      opacity: 0,
      ease: 'power3.out',
      delay: 0.1
    });

    gsap.from('.hero-title', {
      duration: 1.2,
      y: 60,
      opacity: 0,
      ease: 'power4.out',
      delay: 0.25
    });

    gsap.from('.hero-subtitle', {
      duration: 0.8,
      y: 20,
      opacity: 0,
      ease: 'power3.out',
      delay: 0.5
    });

    gsap.from('.hero-latest-badge', {
      duration: 0.6,
      y: 10,
      opacity: 0,
      ease: 'power2.out',
      delay: 0.75
    });

    gsap.from('.hero-image', {
      duration: 1.4,
      scale: 1.05,
      opacity: 0,
      ease: 'power4.out',
      delay: 0.3
    });
  }

  // Services section — staggered entrance
  gsap.from('.service-card', {
    duration: 0.7,
    opacity: 0,
    y: 40,
    stagger: 0.12,
    ease: 'power3.out',
    scrollTrigger: {
      trigger: '.services',
      start: 'top 80%'
    }
  });

  // About section
  if (document.querySelector('.about')) {
    gsap.from('.about-quote', {
      duration: 0.9,
      x: -30,
      opacity: 0,
      ease: 'power3.out',
      scrollTrigger: {
        trigger: '.about',
        start: 'top 80%'
      }
    });

    gsap.from('.about-body', {
      duration: 0.9,
      x: 30,
      opacity: 0,
      ease: 'power3.out',
      delay: 0.15,
      scrollTrigger: {
        trigger: '.about',
        start: 'top 80%'
      }
    });
  }

  // Blog post cards — staggered
  if (document.querySelector('.post-card')) {
    gsap.from('.home-posts-grid .post-card', {
      duration: 0.7,
      opacity: 0,
      y: 30,
      stagger: 0.12,
      ease: 'power3.out',
      scrollTrigger: {
        trigger: '.latest-posts-home',
        start: 'top 80%'
      }
    });
  }

  // Expertise cards if they exist
  if (document.querySelector('.expertise-card')) {
    gsap.from('.expertise-card', {
      duration: 0.8,
      opacity: 0,
      y: 30,
      stagger: 0.15,
      ease: 'power3.out',
      scrollTrigger: {
        trigger: '.freelance-expertise',
        start: 'top 80%'
      }
    });
  }
});
