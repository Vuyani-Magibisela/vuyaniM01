document.addEventListener("DOMContentLoaded", () => {

    // ===== Toast notification function (must be defined first) =====
    function showSubscriptionToast(message, type = 'info') {
      const toast = document.createElement('div');
      toast.className = `subscription-toast subscription-toast-${type}`;
      toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>`;

      document.body.appendChild(toast);

      // Animate in
      setTimeout(() => toast.classList.add('show'), 10);

      // Remove after 5 seconds
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
          if (document.body.contains(toast)) {
            document.body.removeChild(toast);
          }
        }, 300);
      }, 5000);
    }

    // Add toast CSS dynamically
    const toastStyle = document.createElement('style');
    toastStyle.textContent = `
      .subscription-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 12px;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s ease;
        z-index: 9999;
      }
      .subscription-toast.show {
        opacity: 1;
        transform: translateY(0);
      }
      .subscription-toast-success {
        border-left: 4px solid #10b981;
      }
      .subscription-toast-success i {
        color: #10b981;
        font-size: 20px;
      }
      .subscription-toast-error {
        border-left: 4px solid #ef4444;
      }
      .subscription-toast-error i {
        color: #ef4444;
        font-size: 20px;
      }
      .subscription-toast span {
        color: #333;
        font-size: 14px;
      }
    `;
    document.head.appendChild(toastStyle);

    // ===== Newsletter form subscription handler =====
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
      newsletterForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const emailInput = newsletterForm.querySelector('input[type="email"]');
        const email = emailInput.value.trim();
        const submitButton = newsletterForm.querySelector('button[type="submit"]');

        if (!email) {
          showSubscriptionToast('Please enter your email address.', 'error');
          return;
        }

        // Disable button during request
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';

        try {
          // Get base URL from window or default
          const baseUrl = window.baseUrl || '';

          const response = await fetch(`${baseUrl}/subscription/subscribe`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email })
          });

          // Validate response before parsing
          const contentType = response.headers.get('Content-Type') || '';
          if (!response.ok || !contentType.includes('application/json')) {
            console.error('Subscribe response error:', response.status, contentType);
            const text = await response.text();
            console.error('Response body (first 500 chars):', text.substring(0, 500));
            showSubscriptionToast('Server error. Please try again later.', 'error');
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
            return;
          }

          const responseText = await response.text();
          let data;
          try {
            data = JSON.parse(responseText);
          } catch (parseError) {
            // Server may have prepended PHP warnings before the JSON
            console.error('JSON parse failed. Raw response:', responseText.substring(0, 500));
            const jsonStart = responseText.indexOf('{');
            if (jsonStart > 0) {
              console.warn('Found JSON at offset', jsonStart, '— PHP warnings likely prepended');
              data = JSON.parse(responseText.substring(jsonStart));
            } else {
              throw parseError;
            }
          }

          if (data.success) {
            // Show success message in form
            newsletterForm.innerHTML = `
              <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <p>${data.message}</p>
              </div>`;
            showSubscriptionToast(data.message, 'success');
          } else {
            // Show error message
            showSubscriptionToast(data.message, 'error');
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
          }
        } catch (error) {
          console.error('Subscription error:', error);
          console.error('This usually means the server returned non-JSON (PHP warning/error in response body)');
          showSubscriptionToast('An error occurred. Please try again later.', 'error');
          submitButton.disabled = false;
          submitButton.innerHTML = originalButtonText;
        }
      });
    }

    // ===== Handle post-verification redirect (?subscribed=1 or ?subscription_error=1) =====
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('subscribed') === '1') {
      showSubscriptionToast('Your subscription is confirmed! Welcome to the blog.', 'success');
      if (newsletterForm) {
        newsletterForm.innerHTML = `
          <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <p>You're subscribed! You'll receive new post notifications.</p>
          </div>`;
      }
      window.history.replaceState({}, '', window.location.pathname);
    } else if (urlParams.get('subscription_error') === '1') {
      showSubscriptionToast('Verification failed. The link may have expired or is invalid.', 'error');
      window.history.replaceState({}, '', window.location.pathname);
    }

    // ===== Smooth scroll for article anchor links =====
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

    // ===== Image lightbox =====
    const articleImages = document.querySelectorAll('.article-content img:not([class])');
    articleImages.forEach(img => {
      img.style.cursor = 'pointer';
      img.addEventListener('click', () => {
        const lightbox = document.createElement('div');
        lightbox.className = 'lightbox-modal';

        const closeBtn = document.createElement('button');
        closeBtn.className = 'lightbox-close';
        closeBtn.innerHTML = '&times;';

        const imgContainer = document.createElement('div');
        imgContainer.className = 'lightbox-image-container';

        const imgEl = document.createElement('img');
        imgEl.src = img.src;
        imgEl.alt = img.alt;

        imgContainer.appendChild(imgEl);
        lightbox.appendChild(closeBtn);
        lightbox.appendChild(imgContainer);
        document.body.appendChild(lightbox);
        document.body.style.overflow = 'hidden';

        setTimeout(() => lightbox.classList.add('active'), 10);

        function closeLightbox() {
          lightbox.classList.remove('active');
          setTimeout(() => {
            document.body.removeChild(lightbox);
            document.body.style.overflow = '';
          }, 300);
        }

        closeBtn.addEventListener('click', closeLightbox);
        lightbox.addEventListener('click', (e) => {
          if (e.target === lightbox) closeLightbox();
        });
        document.addEventListener('keydown', (e) => {
          if (e.key === 'Escape') closeLightbox();
        });
      });
    });

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

    // ===== GSAP Animations (guarded — won't crash if GSAP isn't loaded) =====
    if (typeof gsap !== 'undefined') {
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

      // Article page animations
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

      // Resource filtering with GSAP animations
      const filterButtons = document.querySelectorAll('.filter-btn');
      const resourceCards = document.querySelectorAll('.resource-card');

      if (filterButtons.length > 0 && resourceCards.length > 0) {
        filterButtons.forEach(button => {
          button.addEventListener('click', () => {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            const filterValue = button.getAttribute('data-filter');

            resourceCards.forEach(card => {
              if (filterValue === 'all' || card.getAttribute('data-category') === filterValue) {
                card.style.display = 'flex';
                gsap.to(card, { opacity: 1, y: 0, duration: 0.4, ease: "power2.out" });
              } else {
                gsap.to(card, {
                  opacity: 0, y: 20, duration: 0.4, ease: "power2.in",
                  onComplete: () => { card.style.display = 'none'; }
                });
              }
            });
          });
        });
      }
    }

  });