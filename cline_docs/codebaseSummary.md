## Codebase Summary

### Key Components and Their Interactions
The application follows a custom MVC pattern. `public/index.php` initializes the core components, including the `Router`, which handles URL routing through `.htaccess`. Controllers in `app/controllers/` interact with Models in `app/models/`, utilizing the `Database` class (`app/core/Database.php`) for MySQL operations. Views are rendered through `app/views/`, with partials like `header.php` and `footer.php` included in the main layout (`main.php`). `BaseController` and `BaseModel` provide reusable functionality across the application.

### Data Flow
-   Request -> `index.php` -> `App` -> `Router` -> Controller -> Model -> Database -> Model -> Controller -> View -> Response.

### External Dependencies
-   The application is primarily self-contained, relying on PHP core functions and PDO.

### Recent Significant Changes
-   Initial project structure setup and documentation creation.
-   Implemented the Home page view (`app/views/home/index.php`).
-   Refactored header and footer into partials (`app/views/partials/header.php`, `app/views/partials/footer.php`) included in the main layout (`app/views/layouts/main.php`).
-   Fixed a path resolution error in `main.php` and `header.php` by replacing `__DIR__ . '/../../config/config.php'` with `dirname(__DIR__, 2) . '/config/config.php'`. This ensures cross-platform compatibility and resolves "Permission denied" errors.
-   Added a safety check in `footer.php` to ensure `$baseUrl` is available.
-   Ensured consistent path handling by using `dirname(__DIR__)` for relative paths to partials and other view files.

### User Feedback Integration and Its Impact on Development
-   Not applicable yet.
