## Technology Stack

### Backend
- **PHP 7.4+** - Custom MVC Framework
  - Custom routing system for clean URLs
  - Controller-based architecture
  - Object-oriented programming approach
  - Session management with security features
  - Password hashing with password_hash() (bcrypt)
- **MySQL (PDO)**
  - Database connectivity via PHP Data Objects
  - Prepared statements for security
  - User authentication and authorization

### Frontend
- **HTML5**
  - Semantic markup structure
  - Accessibility considerations implemented
- **CSS3**
  - Custom responsive design (no frameworks)
  - Mobile-first approach
  - Flexbox and Grid layouts
  - Media queries for responsive breakpoints
  - CSS variables for theming
  - Admin panel styling system (ADDED Nov 17, 2025)
    - Component-based architecture (buttons, cards, forms, tables, modals)
    - Responsive sidebar layout
    - Statistics dashboard styling
    - Theme-aware (light/dark mode support)
- **JavaScript (ES6+)**
  - Vanilla JavaScript for DOM manipulation
  - Event-driven interactions
  - Theme switching functionality
  - GSAP library for animations
    - ScrollTrigger for scroll-based animations
    - Tween animations for UI elements
  - Mobile navigation functionality
  - Admin panel interactivity (ADDED Nov 17, 2025)
    - AJAX operations for seamless UX
    - Slug generation from titles
    - Image/file upload with drag & drop
    - Gallery management (add, remove, reorder)
    - Modal dialog system
    - Form validation
    - Character counters

### Infrastructure
- **Apache (via XAMPP)**
  - URL rewriting with mod_rewrite
  - .htaccess configuration
- **Git** - Version control

### Architecture Decisions
- **Custom MVC Framework** - Chosen for learning purposes and complete control over the architecture, rather than using an established framework like Laravel or CodeIgniter.
- **PDO for Database** - Selected for security benefits (prepared statements) and abstraction layer that allows potential database switching.
- **Vanilla CSS/JS** - Used instead of frameworks to minimize dependencies and optimize performance while maintaining control over styling.
- **Dark/Light Theme** - Implemented with CSS variables and JavaScript for state management with cookie persistence.
- **Mobile-First Responsive Design** - Ensures optimal viewing experience across devices with progressive enhancement.
- **Custom Component Architecture** - Created reusable view partials (header, footer, admin_sidebar) for consistency across pages.
- **Rich Text Editing with Quill.js** - Chosen for its clean API, extensibility, and professional editing experience without heavy dependencies.
- **AJAX-First Admin Panel** - Implemented seamless operations (featured toggle, user management, category CRUD) for better UX.
- **File Upload System** - Built custom upload handling with validation (file type, size limits) for security and control.
- **Gallery Management** - Implemented drag & drop multi-image gallery with ordering for flexible project showcasing.
- **Session Management** - Custom Session class with comprehensive security features:
  - CSRF token protection on all state-changing operations
  - Session timeout tracking (30-minute inactivity)
  - Brute force protection via login attempt tracking
  - Secure cookie configuration (HttpOnly, Secure, SameSite)
  - Session fixation prevention through regeneration
  - Flash message system for user feedback
- **Authentication System** - Role-based access control with security best practices:
  - Bcrypt password hashing with cost factor 12
  - Username or email login support
  - Remember Me functionality with secure token hashing (SHA-256)
  - Login attempt tracking (5 attempts = 15-minute lockout)
  - Token regeneration on each Remember Me use
  - Middleware for protected routes (requireAuth, requireAdmin)
  - Database-stored user sessions with secure token management

### Third-Party Libraries
- **Font Awesome** - Used for icons throughout the application (public and admin)
- **GSAP (GreenSock Animation Platform)** - Implemented for smooth animations and transitions
  - ScrollTrigger plugin for scroll-based animations
- **Quill.js v1.3.6** - Rich text editor for admin panel (ADDED Nov 17, 2025)
  - Snow theme for clean, professional editing interface
  - Toolbar with: headers, bold, italic, underline, strike, lists, blockquote, code-block, color, background, links, images
  - Used for blog posts and project descriptions
  - Content stored as HTML in database