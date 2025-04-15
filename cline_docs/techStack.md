# Tech Stack & Architecture Decisions

## Overview
This document outlines the key technology choices and architectural decisions for the Vuyani Magibisela Portfolio Website, based on the PRD and design references.

---

## Frontend
- **HTML5, CSS3, JavaScript**
  - Rationale: Universal, performant, and flexible for custom UI/UX.
- **CSS Framework:** Custom (with utility classes, media queries)
  - Rationale: Full control over branding (yellow/black), minimal bloat.
- **Interactivity:** Vanilla JS (with possible Alpine.js for lightweight reactivity)
  - Rationale: Simple, no heavy frameworks needed for planned features.
- **Typography:** Google Fonts (Roboto or Montserrat)
  - Rationale: Modern, accessible, and visually consistent.

---

## Backend
- **Language:** PHP (8.x+)
  - Rationale: Widely supported, ideal for MVC, easy deployment.
- **Architecture:** Custom lightweight MVC
  - Rationale: Full control, easy to extend, future-proof for new features.
- **Mail:** PHPMailer or similar
  - Rationale: Reliable email sending for contact form and verification.

---

## Database
- **MySQL**
  - Rationale: Robust, scalable, well-supported for user data, blog, resources.

---

## 3D Features (Future)
- **Python (3.x)**
  - Rationale: For advanced 3D rendering or interactive demos.

---

## Testing
- **PHPUnit** (backend/unit tests)
- **Custom JS tests** (frontend/unit tests)
- **TDD Workflow:** Write tests before/alongside features

---

## Authentication & Security
- **Sessions:** PHP native sessions
- **Password Hashing:** PHP password_hash()
- **Email Verification:** Token-based via email
- **Input Validation:** Server-side and client-side
- **Security:** Prepared statements (PDO), CSRF tokens, XSS protection, HTTPS

---

## Analytics & SEO
- **Google Analytics**
- **Meta tags, sitemap, alt text, clean URLs**

---

## Version Control
- **Git** (with .gitignore for sensitive files and vendor/)

---

## Rationale for Choices
- All technologies are chosen for reliability, scalability, and ease of deployment on common hosting.
- The stack is minimal and maintainable, with room for future enhancements (paywall, courses, 3D, etc.).

---

## Notes
- All major tech changes will be documented here.
- See codebaseSummary.md for how these technologies are integrated into the project structure.
