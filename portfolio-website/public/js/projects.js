document.addEventListener("DOMContentLoaded", function() {
  console.log("Projects JS loaded successfully!");
  
  // Project filtering functionality
  const filterButtons = document.querySelectorAll('.filter-btn');
  const projectCards = document.querySelectorAll('.project-card');
  const noResults = document.querySelector('.no-results');
  const resetFilterBtn = document.querySelector('.reset-filter-btn');
  
  // First, check if GSAP is defined before trying to use it
  const isGsapLoaded = typeof gsap !== 'undefined';
  
  console.log("GSAP loaded:", isGsapLoaded);
  
  // If GSAP is available, use it for animations
  if (isGsapLoaded) {
    try {
      // Animation for project cards
      gsap.from(".project-card", {
        duration: 0.8,
        opacity: 0,
        y: 50,
        stagger: 0.1,
        ease: "power3.out"
      });
      
      // Check if ScrollTrigger is available
      if (gsap.ScrollTrigger) {
        gsap.from(".project-card", {
          scrollTrigger: {
            trigger: ".projects-grid",
            start: "top 80%"
          }
        });
      }
    } catch (error) {
      console.error("Error using GSAP:", error);
      // Apply CSS animations as fallback
      applyFallbackAnimations();
    }
  } else {
    console.warn("GSAP not loaded. Using CSS animations instead.");
    // Apply CSS animations as fallback
    applyFallbackAnimations();
  }
  
  // Apply CSS-based fallback animations
  function applyFallbackAnimations() {
    document.querySelectorAll('.project-card').forEach((card, index) => {
      card.style.opacity = "0";
      card.classList.add('fade-in-animation');
      card.style.animationDelay = `${index * 0.1}s`;
      setTimeout(() => {
        card.style.opacity = "1";
      }, 100);
    });
  }
  
  // Initialize the filter system
  function filterProjects(category) {
    let visibleCount = 0;
    
    projectCards.forEach(card => {
      const cardCategory = card.getAttribute('data-category');
      
      if (category === 'all' || cardCategory === category) {
        // Show the card
        if (isGsapLoaded) {
          try {
            gsap.to(card, {
              duration: 0.4,
              opacity: 1,
              scale: 1,
              display: 'block',
              ease: "power3.out"
            });
          } catch (error) {
            card.style.display = 'block';
            card.style.opacity = 1;
          }
        } else {
          card.style.display = 'block';
          card.style.opacity = 1;
        }
        visibleCount++;
      } else {
        // Hide the card
        if (isGsapLoaded) {
          try {
            gsap.to(card, {
              duration: 0.4,
              opacity: 0,
              scale: 0.95,
              display: 'none',
              ease: "power3.out"
            });
          } catch (error) {
            card.style.opacity = 0;
            setTimeout(() => {
              card.style.display = 'none';
            }, 400);
          }
        } else {
          card.style.opacity = 0;
          setTimeout(() => {
            card.style.display = 'none';
          }, 400);
        }
      }
    });
    
    // Toggle no results message
    if (visibleCount === 0 && noResults) {
      if (isGsapLoaded) {
        try {
          gsap.to(noResults, {
            duration: 0.4,
            display: 'block',
            opacity: 1,
            y: 0,
            ease: "power3.out"
          });
        } catch (error) {
          noResults.style.display = 'block';
          noResults.style.opacity = 1;
        }
      } else {
        noResults.style.display = 'block';
        noResults.style.opacity = 1;
      }
    } else if (noResults) {
      if (isGsapLoaded) {
        try {
          gsap.to(noResults, {
            duration: 0.4,
            opacity: 0,
            y: 20,
            onComplete: () => {
              noResults.style.display = 'none';
            },
            ease: "power3.out"
          });
        } catch (error) {
          noResults.style.opacity = 0;
          setTimeout(() => {
            noResults.style.display = 'none';
          }, 400);
        }
      } else {
        noResults.style.opacity = 0;
        setTimeout(() => {
          noResults.style.display = 'none';
        }, 400);
      }
    }
  }
  
  // Add click event to filter buttons
  if (filterButtons.length > 0) {
    filterButtons.forEach(button => {
      button.addEventListener('click', () => {
        // Remove active class from all buttons
        filterButtons.forEach(btn => btn.classList.remove('active'));
        
        // Add active class to clicked button
        button.classList.add('active');
        
        // Get the filter category
        const filterCategory = button.getAttribute('data-filter');
        
        // Filter the projects
        filterProjects(filterCategory);
      });
    });
  } else {
    console.warn("Filter buttons not found.");
  }
  
  // Reset filter functionality
  if (resetFilterBtn) {
    resetFilterBtn.addEventListener('click', () => {
      // Activate "All" button
      filterButtons.forEach(btn => {
        if (btn.getAttribute('data-filter') === 'all') {
          btn.classList.add('active');
        } else {
          btn.classList.remove('active');
        }
      });
      
      // Show all projects
      filterProjects('all');
    });
  }
  
  // Project card hover effects
  if (projectCards.length > 0) {
    projectCards.forEach(card => {
      const projectLink = card.querySelector('.project-link');
      if (projectLink) {
        card.addEventListener('mouseenter', () => {
          projectLink.style.transform = 'translateY(-3px)';
        });
        
        card.addEventListener('mouseleave', () => {
          projectLink.style.transform = 'translateY(0)';
        });
      }
    });
  }
});