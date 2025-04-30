document.addEventListener("DOMContentLoaded", () => {
    // Gallery functionality
    const mainImage = document.querySelector('.project-main-image img');
    const thumbnails = document.querySelectorAll('.thumbnail');
    const baseUrl = document.body.getAttribute('data-base-url') || '';
    
    // Add click event to thumbnails
    thumbnails.forEach(thumbnail => {
      thumbnail.addEventListener('click', () => {
        // Remove active class from all thumbnails
        thumbnails.forEach(t => t.classList.remove('active'));
        
        // Add active class to clicked thumbnail
        thumbnail.classList.add('active');
        
        // Get the image filename from data attribute
        const imageFilename = thumbnail.getAttribute('data-image');
        
        // Animate the main image change
        gsap.to(mainImage, {
          duration: 0.3,
          opacity: 0,
          scale: 0.95,
          onComplete: () => {
            // Change the main image source
            mainImage.src = `${baseUrl}/images/projects/${imageFilename}`;
            
            // Animate the new image in
            gsap.to(mainImage, {
              duration: 0.3,
              opacity: 1,
              scale: 1
            });
          }
        });
      });
    });
    
    // Initial animation for project detail page
    gsap.from(".project-title", {
      duration: 0.8,
      opacity: 0,
      y: 20,
      ease: "power3.out"
    });
    
    gsap.from(".project-metadata", {
      duration: 0.8,
      opacity: 0,
      y: 20,
      delay: 0.2,
      ease: "power3.out"
    });
    
    gsap.from(".project-main-image", {
      duration: 1,
      opacity: 0,
      y: 30,
      delay: 0.4,
      ease: "power3.out"
    });
    
    gsap.from(".thumbnail", {
      duration: 0.6,
      opacity: 0,
      y: 20,
      stagger: 0.1,
      delay: 0.6,
      ease: "power3.out"
    });
    
    gsap.from(".project-description h2", {
      duration: 0.8,
      opacity: 0,
      x: -20,
      stagger: 0.2,
      scrollTrigger: {
        trigger: ".project-description",
        start: "top 80%"
      },
      ease: "power3.out"
    });
    
    gsap.from(".description-content, .challenges-content, .tech-list", {
      duration: 0.8,
      opacity: 0,
      y: 20,
      stagger: 0.2,
      scrollTrigger: {
        trigger: ".project-description",
        start: "top 70%"
      },
      ease: "power3.out"
    });
    
    // Animate related projects on scroll
    gsap.from(".related-project", {
      duration: 0.8,
      opacity: 0,
      y: 30,
      stagger: 0.15,
      scrollTrigger: {
        trigger: ".related-projects",
        start: "top 80%"
      },
      ease: "power3.out"
    });
  });