## Technology Stack

### Backend
- **PHP 7.4+** - Custom MVC Framework
  - Custom routing system for clean URLs
  - Controller-based architecture
  - Object-oriented programming approach
- **MySQL (PDO)**
  - Database connectivity via PHP Data Objects
  - Prepared statements for security

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
- **JavaScript (ES6+)**
  - Vanilla JavaScript for DOM manipulation
  - Event-driven interactions
  - Theme switching functionality
  - GSAP library for animations
    - ScrollTrigger for scroll-based animations
    - Tween animations for UI elements
  - Mobile navigation functionality

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
- **Custom Component Architecture** - Created reusable view partials (header, footer) for consistency across pages.

### Third-Party Libraries
- **Font Awesome** - Used for icons throughout the application
- **GSAP (GreenSock Animation Platform)** - Implemented for smooth animations and transitions
  - ScrollTrigger plugin for scroll-based animations