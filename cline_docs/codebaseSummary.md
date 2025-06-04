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

### Blog Section Implementation
-   **Controllers Created:**
    -   `BlogController.php` - Main controller handling all blog functionality
        -   `index()` - Main blog listing page with featured posts
        -   `article($slug)` - Individual article display with view tracking
        -   `resources()` - Downloadable resources page
        -   `downloadResource($resourceId)` - Handle file downloads with authentication
        -   `category($slug)` - Category-filtered blog posts
-   **Models Developed:**
    -   `BlogPost.php` - Handles blog post data operations
        -   `getRecentPosts()` - Fetch latest published posts
        -   `getPostBySlug()` - Get single post with tags and author info
        -   `getRelatedPosts()` - Find related posts by category
        -   `incrementViews()` - Track post view counts
        -   `getFeaturedPosts()` - Get featured posts for homepage
        -   `getPostsByCategory()` - Filter posts by category
    -   `Resource.php` - Manages downloadable resources
        -   `getAllPublished()` - Get all published resources
        -   `getById()` - Fetch specific resource details
        -   `incrementDownloads()` - Track download statistics
-   **Views (Templates) Created:**
    -   `blog/index.php` - Main blog listing page
        -   Featured posts grid
        -   Recent posts with pagination
        -   Sidebar with categories, newsletter signup, and resources
        -   Responsive grid layout
    -   `blog/article.php` - Single article page
        -   Full article content with rich formatting
        -   Author bio section with social links
        -   Social sharing buttons (Twitter, Facebook, LinkedIn)
        -   Tags display and related posts
        -   Breadcrumb navigation
    -   `blog/resources.php` - Downloads/resources page
        -   Filterable resource grid
        -   File type indicators and download counts
        -   Login-protected resources
        -   Resource metadata (file size, type, downloads)
-   **Database Configuration Enhanced:**
    -   Updated `Database.php` - Improved connection handling
        -   Singleton pattern for database connections
        -   Robust error handling and logging
        -   Configuration loading from separate file
        -   Connection persistence and cleanup
    -   Enhanced `database.php` config - Added charset and PDO options
        -   UTF-8 support for international content
        -   Proper PDO error modes and fetch settings
-   **Styling & Design (CSS):**
    -   Comprehensive blog styles added to `main.css`
        -   Blog header and navigation - Clean, centered layout
        -   Featured posts grid - Card-based design with hover effects
        -   Blog content layout - Two-column design (content + sidebar)
        -   Article pages - Typography-focused single-column layout
        -   Sidebar widgets - Newsletter, categories, and resources
        -   Resources page - Filterable grid with file type indicators
        -   Dark/light mode support - Full theme compatibility
        -   Responsive design - Mobile-first approach with breakpoints
        -   Animations and transitions - Smooth hover effects and loading animations
-   **JavaScript Functionality:**
    -   `blog.js` - Interactive blog features
        -   GSAP animations - Staggered loading animations for posts and widgets
        -   Newsletter subscription - Form handling with success feedback
        -   Resource filtering - Dynamic filtering by resource type
        -   Image lightbox - Click-to-expand images in articles
        -   Smooth scrolling - Enhanced navigation within articles
        -   Social sharing - Functional share buttons
-   **Dummy Data System:**
    -   `blog_dummy_data.php` - Comprehensive test data
        -   5 detailed blog posts with full content (3D modeling, CSS Grid, Arduino, Typography, PWA)
        -   5 downloadable resources with metadata
        -   Featured posts selection
        -   Categories and tags structure
        -   Author information and publication dates

### User Feedback Integration and Its Impact on Development
-   Not applicable yet.
