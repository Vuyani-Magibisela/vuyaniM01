# Codebase Summary

## Key Components and Their Interactions

### MVC Structure
- **Controllers:** Handle routing, user input, and business logic.
- **Models:** Manage data, database interactions, and validation.
- **Views:** Render HTML/CSS/JS for the frontend, using templates and partials.
- **Helpers:** Utility functions for authentication, validation, etc.

### Public Assets
- **CSS, JS, Images:** Served from `public/` for fast, cacheable delivery.
- **Uploads:** User-uploaded files (e.g., resources) stored in a secure subfolder.

### Config & Database
- **config/:** Environment, database, and mail settings.
- **database/:** Migrations, seeders, and SQL scripts for MySQL.

### User Authentication
- **Registration, login, email verification, session management.**
- **Dashboard:** User can view/download resources, manage profile.

### Blog & Resources
- **Articles:** Markdown or WYSIWYG, with embedded YouTube/videos.
- **Resources:** Downloadable files, login required, tracked per user.

### Contact
- **Form:** Sends email to Vuyani, confirmation to user.
- **Social Links:** WhatsApp, Instagram, TikTok, Email, etc.

---

## Data Flow

1. **User requests page** → Controller routes request → Model fetches data → View renders page.
2. **Form submissions** (contact, login, register) → Controller validates input → Model processes data → View returns result/confirmation.
3. **Resource download** → If not logged in, redirect to login/signup → After login, allow download and log in dashboard.

---

## External Dependencies

- **PHPMailer:** For sending emails (contact, verification).
- **Google Analytics:** For tracking user behavior and downloads.
- **Google Fonts:** For typography (Roboto/Montserrat).
- **(Future) Python scripts:** For 3D rendering features.

---

## Recent Significant Changes

- 2025-04-14: Project documentation scaffolded and initialized (projectRoadmap.md, currentTask.md, techStack.md, codebaseSummary.md).

---

## User Feedback Integration

- User feedback and requests will be tracked in projectRoadmap.md and reflected in this summary as features or structure evolve.
- Additional reference documents (e.g., styleAesthetic.md, wireframes.md) will be noted here as they are created.

---

## Notes

- This summary will be updated after each major structural or architectural change.
- See techStack.md for rationale behind technology choices.
