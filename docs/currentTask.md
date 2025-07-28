## Summary of Recent Fixes

### Problem
The error was caused by an incorrect path resolution in `main.php`. The original path `__DIR__ . '/../../config/config.php'` was creating an invalid path with mixed directory separators, causing a "Permission denied" error.

### Solution
Replaced the problematic path resolution with `dirname(__DIR__, 2) . '/config/config.php'`. This is more reliable, cross-platform compatible, and has cleaner syntax.

### Key Changes
- `main.php`: Changed `require_once __DIR__ . '/../../config/config.php';` to `require_once dirname(__DIR__, 2) . '/config/config.php';`
- `header.php`: Same path fix applied for consistency
- `footer.php`: Added a safety check to ensure `$baseUrl` is available
- Consistent path handling: Used `dirname(__DIR__)` for relative paths to partials and other view files

## Current Objectives

- Implement the "Blog" page functionality and view.

## Context

- The Home page is complete and functional with proper responsive design.
- The Clients page has been successfully implemented with both desktop and mobile views.
- Mobile navigation with burger menu has been implemented across the site.
- Dark/light mode switching now functions correctly on all existing pages.
- The projects section has been successfully implemented with both desktop and mobile views.

## Current Problems

- Fatal error: Declaration of App\Models\Resource::getById($id) must be compatible with App\Models\BaseModel::getById($table, $id) in D:\xampp\htdocs\vuyaniM01\portfolio-website\app\models\Resource.php on line 44

## Next Steps

1.  **Blog Post Creation:**
    -   Implement the ability to create new blog posts.
    -   Include fields for title, content, author, and date.
2.  **Blog Post Display:**
    -   Display blog posts on the main blog page.
    -   Implement pagination for a large number of posts.
3.  **Individual Blog Post View:**
    -   Create a separate view for individual blog posts.
    -   Display the full content of the post, author, and date.
4.  **Styling and Design:**
    -   Ensure the blog page and individual post views are visually appealing.
    -   Maintain consistent styling with the rest of the site.
5.  **Dark/Light Mode Support:**
    -   Ensure the blog page and all its components support dark/light mode switching.
    -   Test the theme toggle functionality on the blog page.
6.  **Testing:**
    -   Test all navigation links and routes to ensure they load the correct pages.
    -   Verify that the blog page and individual posts display correctly on different screen sizes.
    -   Test the dark/light mode toggle on the blog page.
