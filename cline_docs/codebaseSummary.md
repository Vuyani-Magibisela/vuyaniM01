## Codebase Summary

### Key Components and Their Interactions
-   The application uses a custom MVC pattern. `public/index.php` bootstraps the application, loading core classes and initializing the `Router`. The `Router` parses the URL (from `$_GET['url']`, handled by `.htaccess`) and dispatches requests to Controllers (`app/controllers/`). Controllers interact with Models (`app/models/`) which use the `Database` class (`app/core/Database.php`) for PDO-based MySQL interactions. Controllers then load Views (`app/views/`). `BaseController` and `BaseModel` provide common functionality.

### Data Flow
-   Request -> `index.php` -> `App` -> `Router` -> Controller -> Model -> Database -> Model -> Controller -> View -> Response.

### External Dependencies
-   The application is primarily self-contained, relying on PHP core functions and PDO.

### Recent Significant Changes
-   Initial project structure setup and documentation creation.

### User Feedback Integration and Its Impact on Development
-   Not applicable yet.
