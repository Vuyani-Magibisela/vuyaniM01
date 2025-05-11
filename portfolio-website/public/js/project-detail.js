document.addEventListener("DOMContentLoaded", function() {
  console.log("Project Detail JS loaded successfully!");
  
  // First, check if GSAP is defined before trying to use it
  const isGsapLoaded = typeof gsap !== 'undefined';
  
  console.log("GSAP loaded:", isGsapLoaded);
  
  // Gallery functionality
  const mainImage = document.querySelector('.project-main-image img');
  const thumbnails = document.querySelectorAll('.thumbnail');
  const baseUrl = document.body.getAttribute('data-base-url') || '';
  
  // Apply CSS-based fallback animations
  function applyFallbackAnimations() {
    const elements = [
      { selector: '.project-title', delay: 0 },
      { selector: '.project-metadata', delay: 0.2 },
      { selector: '.project-main-image', delay: 0.4 }
    ];
    
    elements.forEach(item => {
      const elements = document.querySelectorAll(item.selector);
      elements.forEach(el => {
        el.style.opacity = "0";
        el.classList.add('fade-in-animation');
        el.style.animationDelay = `${item.delay}s`;
        setTimeout(() => {
          el.style.opacity = "1";
        }, 100);
      });
    });
    
    // Handle thumbnails separately for staggered effect
    document.querySelectorAll('.thumbnail').forEach((thumb, index) => {
      thumb.style.opacity = "0";
      thumb.classList.add('fade-in-animation');
      thumb.style.animationDelay = `${0.6 + (index * 0.1)}s`;
      setTimeout(() => {
        thumb.style.opacity = "1";
      }, 100);
    });
  }
  
  // Add click event to thumbnails
  if (thumbnails.length > 0 && mainImage) {
    thumbnails.forEach(thumbnail => {
      thumbnail.addEventListener('click', () => {
        // Remove active class from all thumbnails
        thumbnails.forEach(t => t.classList.remove('active'));
        
        // Add active class to clicked thumbnail
        thumbnail.classList.add('active');
        
        // Get the image filename from data attribute
        const imageFilename = thumbnail.getAttribute('data-image');
        
        // Check if GSAP is available
        if (isGsapLoaded) {
          try {
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
          } catch (error) {
            console.error("Error using GSAP:", error);
            // Fallback without GSAP
            mainImage.style.opacity = 0;
            setTimeout(() => {
              mainImage.src = `${baseUrl}/images/projects/${imageFilename}`;
              mainImage.style.opacity = 1;
            }, 300);
          }
        } else {
          // Fallback without GSAP
          mainImage.style.opacity = 0;
          setTimeout(() => {
            mainImage.src = `${baseUrl}/images/projects/${imageFilename}`;
            mainImage.style.opacity = 1;
          }, 300);
        }
      });
    });
  }
  
  // Handle animations
  if (isGsapLoaded) {
    try {
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
      
      if (thumbnails.length > 0) {
        gsap.from(".thumbnail", {
          duration: 0.6,
          opacity: 0,
          y: 20,
          stagger: 0.1,
          delay: 0.6,
          ease: "power3.out"
        });
      }
      
      // Check if ScrollTrigger is available
      if (gsap.ScrollTrigger) {
        if (document.querySelector(".project-description h2")) {
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
        }
        
        if (document.querySelector(".description-content, .challenges-content, .tech-list")) {
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
        }
        
        // Animate related projects on scroll
        if (document.querySelector(".related-project")) {
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
        }
      }
    } catch (error) {
      console.error("Error using GSAP animations:", error);
      // Apply CSS-based fallback animations
      applyFallbackAnimations();
    }
  } else {
    console.warn("GSAP not loaded. Using CSS animations instead.");
    // Apply CSS-based fallback animations
    applyFallbackAnimations();
  }
});