## Current Objectives

- Implement the "Projects" page functionality and view.

## Context

- The Home page is complete and functional with proper responsive design.
- The Clients page has been successfully implemented with both desktop and mobile views.
- Mobile navigation with burger menu has been implemented across the site.
- Dark/light mode switching now functions correctly on all existing pages.
- The next section to develop according to the roadmap is the "Projects" page, which will showcase different project categories based on skill sets.

## Next Steps

1. **Project Structure Planning:**
   - Analyze the Site Map Diagram to understand the Projects section hierarchy
   - Note that Projects should be divided into: DigitalDesign (3D Design, Graphic), WebDev, AppDev, GameDev, and MakerProjects

2. **Controller Implementation:**
   - Ensure `ProjectsController.php` exists in `app/controllers/` and inherits from `BaseController` 
   - Create an `index` method to handle the main projects page request
   - Add methods for each subcategory: `digitalDesign()`, `webDev()`, `appDev()`, `gameDev()`, and `makerProjects()`

3. **View Creation:**
   - Create the main view file `app/views/projects/index.php` with the structure for all project categories
   - Create individual view files for each subcategory:
     - `app/views/projects/digital_design.php`
     - `app/views/projects/web_dev.php`
     - `app/views/projects/app_dev.php`
     - `app/views/projects/game_dev.php`
     - `app/views/projects/maker_projects.php`
   - Ensure proper layout with consistent styling that matches the rest of the site

4. **Route Configuration:**
   - Verify the `Router` (`app/core/Router.php`) can correctly route requests like `/projects` to the `ProjectsController@index` method
   - Add routes for subcategory URLs like `/projects/digital-design`, `/projects/web-dev`, etc.

5. **Model Development (if needed):**
   - If project data needs to be dynamic, create a `Project.php` model in `app/models/` inheriting from `BaseModel`
   - Implement methods to fetch project data by category
   - Update the `ProjectsController` to use this model

6. **UI/UX Implementation:**
   - Design a visually appealing layout for showcasing projects
   - Implement filtering or sorting options if needed
   - Ensure responsive design for both desktop and mobile views
   - Maintain consistent styling with the home and clients pages

7. **Dark/Light Mode Support:**
   - Ensure the Projects page and all its components support dark/light mode switching
   - Test the theme toggle functionality on the Projects page

8. **Testing:**
   - Test all navigation links and routes to ensure they load the correct pages
   - Verify that the Projects page and its subcategories display correctly on different screen sizes
   - Test the dark/light mode toggle on the Projects page