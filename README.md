# Vuyani Magibisela Portfolio Website

A modern, responsive portfolio website showcasing the professional work, skills, and expertise of Vuyani Magibisela as an ICT Trainer, Web/App Developer, Maker, and 3D Artist.

## Project Overview

This portfolio website is built using a custom PHP MVC framework with a focus on clean design, responsive layouts, and optimal user experience across all devices. The site features a light/dark mode theme switcher and a mobile-friendly navigation system.

### Key Features

- **Custom MVC Architecture**: Hand-crafted PHP framework for optimal control and performance
- **Responsive Design**: Mobile-first approach ensuring optimal viewing on all devices
- **Dark/Light Mode**: User preference-based theme switching with cookie persistence
- **Modern UI/UX**: Clean, minimalist design with subtle animations and transitions
- **Content Sections**: Home, Clients, Projects, Blog, and Contact pages

## Technology Stack

### Backend
- PHP 7.4+ (Custom MVC Framework)
- MySQL (PDO)

### Frontend
- HTML5 & CSS3
- Vanilla JavaScript (ES6+)
- GSAP for animations
- Font Awesome for icons

### Infrastructure
- Apache Server (XAMPP)
- Git version control

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- Composer (optional, for future dependencies)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/vuyani-portfolio.git
   cd vuyani-portfolio
   ```

2. **Set up the database**
   - Create a MySQL database named `vuyanim`
   - Import the SQL file from `database/init.sql` (if available)
   - Update database credentials in `app/config/database.php`

3. **Configure the webserver**
   - Point your webserver to the `public` directory
   - Ensure mod_rewrite is enabled
   - Make sure `.htaccess` files are allowed

4. **Update base URL**
   - Edit `app/config/config.php` and update the `$baseUrl` to match your environment

5. **File permissions**
   - Ensure the `public/resources` directory is writable if you plan to upload files

6. **Test the installation**
   - Navigate to the site in your web browser
   - You should see the homepage without any errors

## Project Structure

```
portfolio-website/
├── app/                     # Application code
│   ├── config/              # Configuration files
│   ├── controllers/         # Controller classes
│   ├── core/                # Framework core classes
│   ├── models/              # Data models
│   └── views/               # View templates
├── cline_docs/              # Project documentation
├── database/                # Database migrations and seeds
├── public/                  # Publicly accessible files
│   ├── css/                 # Stylesheets
│   ├── js/                  # JavaScript files
│   ├── images/              # Image assets
│   ├── fonts/               # Custom fonts
│   ├── resources/           # Downloadable resources
│   ├── .htaccess            # Apache config
│   └── index.php            # Application entry point
└── README.md                # This file
```

## Development Guidelines

### Coding Standards
- Follow PSR-4 for autoloading
- Use camelCase for methods and variables
- Use PascalCase for classes
- Use snake_case for view files

### CSS Organization
- Base styles: General typography and resets
- Layout components: Structural elements
- Modules: Reusable components
- Pages: Page-specific styles
- Themes: Light/dark mode variations
- Media queries: Responsive adjustments

### Git Workflow
- Use feature branches for new functionality
- Create pull requests for code review
- Maintain a clean commit history with descriptive messages

## Features in Development

Current development is focused on:
- Projects page implementation
- Blog functionality
- Contact form with email integration
- Authentication system for admin access
- Admin panel for content management

## Credits

- Design & Development: Vuyani Magibisela
- Icons: Font Awesome
- Animations: GSAP (GreenSock Animation Platform)

## License

[MIT License](https://opensource.org/licenses/MIT)

Copyright (c) 2025 Vuyani Magibisela

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.