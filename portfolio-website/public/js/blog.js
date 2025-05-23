document.addEventListener("DOMContentLoaded", () => {
    // Animation for blog elements
    gsap.from(".featured-post-card", {
      duration: 0.8,
      opacity: 0,
      y: 30,
      stagger: 0.15,
      ease: "power3.out",
      scrollTrigger: {
        trigger: ".featured-posts",
        start: "top 80%"
      }
    });
    
    gsap.from(".post-card", {
      duration: 0.8,
      opacity: 0,
      y: 30,
      stagger: 0.1,
      ease: "power3.out",
      scrollTrigger: {
        trigger: ".posts-grid",
        start: "top 80%"
      }
    });
    
    gsap.from(".sidebar-widget", {
      duration: 0.8,
      opacity: 0,
      x: 30,
      stagger: 0.15,
      ease: "power3.out",
      scrollTrigger: {
        trigger: ".blog-sidebar",
        start: "top 80%"
      }
    });
    
    // Handle newsletter form submission
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
      newsletterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const emailInput = newsletterForm.querySelector('input[type="email"]');
        const email = emailInput.value.trim();
        
        if (email) {
          // Show success message
          newsletterForm.innerHTML = '<p class="success-message">Thank you for subscribing!</p>';
          
          // In a real app, you would send this to the server via AJAX
          console.log('Newsletter subscription for:', email);
        }
      });
    }
    
    // Handle resource filtering
    const filterButtons = document.querySelectorAll('.filter-btn');
    const resourceCards = document.querySelectorAll('.resource-card');
    
    if (filterButtons.length > 0 && resourceCards.length > 0) {
      filterButtons.forEach(button => {
        button.addEventListener('click', () => {
          // Remove active class from all buttons
          filterButtons.forEach(btn => btn.classList.remove('active'));
          
          // Add active class to clicked button
          button.classList.add('active');
          
          // Get filter value
          const filterValue = button.getAttribute('data-filter');
          
          // Filter resources
          resourceCards.forEach(card => {
            if (filterValue === 'all' || card.getAttribute('data-category') === filterValue) {
              card.style.display = 'flex';
              gsap.to(card, { 
                opacity: 1, 
                y: 0, 
                duration: 0.4, 
                ease: "power2.out" 
              });
            } else {
              gsap.to(card, { 
                opacity: 0, 
                y: 20, 
                duration: 0.4, 
                ease: "power2.in",
                onComplete: () => {
                  card.style.display = 'none';
                }
              });
            }
          });
        });
      });
    }
    
    // Animate article content elements on article page
    const articleContent = document.querySelector('.article-content');
    if (articleContent) {
      gsap.from('.article-header', {
        duration: 0.8,
        opacity: 0,
        y: 20,
        ease: "power3.out"
      });
      
      gsap.from('.article-content > *', {
        duration: 0.5,
        opacity: 0,
        y: 20,
        stagger: 0.05,
        ease: "power3.out",
        delay: 0.3
      });
      
      gsap.from('.article-tags, .article-share, .article-author-bio', {
        duration: 0.6,
        opacity: 0,
        y: 20,
        stagger: 0.15,
        ease: "power3.out",
        scrollTrigger: {
          trigger: '.article-content',
          start: "bottom 80%"
        }
      });
      
      gsap.from('.related-post-card', {
        duration: 0.6,
        opacity: 0,
        y: 30,
        stagger: 0.1,
        ease: "power3.out",
        scrollTrigger: {
          trigger: '.related-posts',
          start: "top 80%"
        }
      });
    }
    
    // Add smooth scroll behavior for article links
    const articleLinks = document.querySelectorAll('.article-content a[href^="#"]');
    articleLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        
        const targetId = link.getAttribute('href');
        const targetElement = document.querySelector(targetId);
        
        if (targetElement) {
          window.scrollTo({
            top: targetElement.offsetTop - 100,
            behavior: 'smooth'
          });
        }
      });
    });
    
    // Adjust image lightbox effect
    const articleImages = document.querySelectorAll('.article-content img:not([class])');
    articleImages.forEach(img => {
      img.style.cursor = 'pointer';
      img.addEventListener('click', () => {
        // Create lightbox modal
        const lightbox = document.createElement('div');
        lightbox.className = 'lightbox-modal';
        
        // Create close button
        const closeBtn = document.createElement('button');
        closeBtn.className = 'lightbox-close';
        closeBtn.innerHTML = '&times;';
        
        // Create image container
        const imgContainer = document.createElement('div');
        imgContainer.className = 'lightbox-image-container';
        
        // Create image element
        const imgEl = document.createElement('img');
        imgEl.src = img.src;
        imgEl.alt = img.alt;
        
        // Append elements to lightbox
        imgContainer.appendChild(imgEl);
        lightbox.appendChild(closeBtn);
        lightbox.appendChild(imgContainer);
        
        // Append lightbox to body
        document.body.appendChild(lightbox);
        
        // Prevent body scrolling
        document.body.style.overflow = 'hidden';
        
        // Show lightbox with animation
        setTimeout(() => {
          lightbox.classList.add('active');
        }, 10);
        
        // Handle close button click
        closeBtn.addEventListener('click', closeLightbox);
        
        // Handle background click
        lightbox.addEventListener('click', (e) => {
          if (e.target === lightbox) {
            closeLightbox();
          }
        });
        
        // Handle escape key press
        document.addEventListener('keydown', (e) => {
          if (e.key === 'Escape') {
            closeLightbox();
          }
        });
        
        function closeLightbox() {
          lightbox.classList.remove('active');
          setTimeout(() => {
            document.body.removeChild(lightbox);
            document.body.style.overflow = '';
          }, 300);
        }
      });
    });
    
    // Add CSS for lightbox
    if (articleImages.length > 0) {
      const style = document.createElement('style');
      style.textContent = `
        .lightbox-modal {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background-color: rgba(0, 0, 0, 0.9);
          display: flex;
          align-items: center;
          justify-content: center;
          z-index: 1000;
          opacity: 0;
          transition: opacity 0.3s;
        }
        
        .lightbox-modal.active {
          opacity: 1;
        }
        
        .lightbox-image-container {
          max-width: 90%;
          max-height: 90%;
        }
        
        .lightbox-image-container img {
          max-width: 100%;
          max-height: 90vh;
          object-fit: contain;
        }
        
        .lightbox-close {
          position: absolute;
          top: 20px;
          right: 20px;
          background: none;
          border: none;
          color: white;
          font-size: 2rem;
          cursor: pointer;
        }
      `;
      document.head.appendChild(style);
    }
  });