document.addEventListener("DOMContentLoaded", () => {
    // Project filtering functionality
    const filterButtons = document.querySelectorAll('.filter-btn');
    const projectCards = document.querySelectorAll('.project-card');
    const noResults = document.querySelector('.no-results');
    const resetFilterBtn = document.querySelector('.reset-filter-btn');
    
    // Animation for project cards
    gsap.from(".project-card", {
      duration: 0.8,
      opacity: 0,
      y: 50,
      stagger: 0.1,
      ease: "power3.out",
      scrollTrigger: {
        trigger: ".projects-grid",
        start: "top 80%"
      }
    });
    
    // Initialize the filter system
    function filterProjects(category) {
      let visibleCount = 0;
      
      projectCards.forEach(card => {
        const cardCategory = card.getAttribute('data-category');
        
        if (category === 'all' || cardCategory === category) {
          // Show the card with animation
          gsap.to(card, {
            duration: 0.4,
            opacity: 1,
            scale: 1,
            display: 'block',
            ease: "power3.out"
          });
          visibleCount++;
        } else {
          // Hide the card with animation
          gsap.to(card, {
            duration: 0.4,
            opacity: 0,
            scale: 0.95,
            display: 'none',
            ease: "power3.out"
          });
        }
      });
      
      // Toggle no results message
      if (visibleCount === 0) {
        gsap.to(noResults, {
          duration: 0.4,
          display: 'block',
          opacity: 1,
          y: 0,
          ease: "power3.out"
        });
      } else {
        gsap.to(noResults, {
          duration: 0.4,
          opacity: 0,
          y: 20,
          onComplete: () => {
            noResults.style.display = 'none';
          },
          ease: "power3.out"
        });
      }
    }
    
    // Add click event to filter buttons
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
    projectCards.forEach(card => {
      card.addEventListener('mouseenter', () => {
        card.querySelector('.project-link').style.animation = 'pulse 1s infinite';
      });
      
      card.addEventListener('mouseleave', () => {
        card.querySelector('.project-link').style.animation = 'none';
      });
    });
    
    // Helper function to check if element is in viewport
    function isInViewport(element) {
      const rect = element.getBoundingClientRect();
      return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
      );
    }
    
    // Add scroll animations for project filters
    window.addEventListener('scroll', () => {
      const filterSection = document.querySelector('.project-filters');
      
      if (isInViewport(filterSection)) {
        gsap.to(filterSection, {
          duration: 0.5,
          backgroundColor: 'rgba(245, 245, 245, 0.8)',
          borderRadius: '40px',
          ease: "power2.out"
        });
      } else {
        gsap.to(filterSection, {
          duration: 0.5,
          backgroundColor: 'rgba(245, 245, 245, 0)',
          ease: "power2.out"
        });
      }
    });
    
    // Add dark mode compatibility for scroll animations
    document.querySelector('body').addEventListener('classChange', () => {
      const isDarkMode = document.body.classList.contains('dark-mode');
      const filterSection = document.querySelector('.project-filters');
      
      if (isDarkMode) {
        filterSection.style.backgroundColor = 'rgba(68, 68, 68, 0.8)';
      } else {
        filterSection.style.backgroundColor = 'rgba(245, 245, 245, 0.8)';
      }
    });
  });