# Projects Page Implementation Instructions

## Overview
This document provides step-by-step instructions for implementing the Projects page section of the portfolio website. The Projects page will showcase various projects categorized by skill area (Digital Design, Web Development, App Development, Game Development, and Maker Projects).

## Setup Steps

### 1. Create the ProjectsController

1. Navigate to `app/controllers/` directory
2. Create a new file named `ProjectsController.php` with the following content:

```php
<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ProjectsController extends BaseController {

    public function index() {
        // Load the view for the main projects page
        $this->view('projects/index');
    }
    
    public function digitalDesign() {
        // Load the digital design projects view
        $this->view('projects/digital_design');
    }
    
    public function webDev() {
        // Load the web development projects view
        $this->view('projects/web_dev');
    }
    
    public function appDev() {
        // Load the app development projects view
        $this->view('projects/app_dev');
    }
    
    public function gameDev() {
        // Load the game development projects view
        $this->view('projects/game_dev');
    }
    
    public function makerProjects() {
        // Load the maker projects view
        $this->view('projects/maker_projects');
    }
}
```

### 2. Create the Project Views

1. Navigate to `app/views/` directory
2. Create a new directory named `projects` if it doesn't exist
3. Create the following view files:

#### Main Projects Page (index.php)
Create `app/views/projects/index.php` with a layout that shows all project categories with links to their respective pages.

#### Category Pages
Create the following files:
- `app/views/projects/digital_design.php`
- `app/views/projects/web_dev.php`
- `app/views/projects/app_dev.php`
- `app/views/projects/game_dev.php`
- `app/views/projects/maker_projects.php`

### 3. Add CSS Styles

1. Open `public/css/main.css`
2. Add styles for the Projects page at the appropriate location in the file
3. Include styles for both desktop and mobile views
4. Ensure dark mode support for all new elements

### 4. Test the Routes

1. Start your local development server
2. Test the following URLs:
   - `http://localhost/[your-base-path]/projects`
   - `http://localhost/[your-base-path]/projects/digital-design`
   - `http://localhost/[your-base-path]/projects/web-dev`
   - `http://localhost/[your-base-path]/projects/app-dev`
   - `http://localhost/[your-base-path]/projects/game-dev`
   - `http://localhost/[your-base-path]/projects/maker-projects`

### 5. Update Router (if needed)

If the routes don't work correctly, you may need to update the Router class in `app/core/Router.php` to properly handle URL segments.

## Project Model (Optional)

If you want to store project data in a database:

1. Create a `Project` model in `app/models/Project.php`
2. Create a database table `projects` with appropriate fields
3. Update the controller to fetch data from the model

## Design Guidelines

- Follow the established style guide (refer to `cline_docs/styleAesthetic.md`)
- Maintain consistent styling with existing pages
- Ensure responsive design works on all screen sizes
- Test dark/light mode switching

## Testing Checklist

- [ ] All project category links work correctly
- [ ] Navigation shows the active page
- [ ] Responsive design works on mobile devices
- [ ] Dark/light mode switching functions properly
- [ ] All content is properly styled and aligned
- [ ] Images load correctly (if applicable)

## Troubleshooting

- If routes aren't working, check URL format and controller method names
- If styles aren't applying, check for CSS selector conflicts
- If the page layout breaks on mobile, check responsive media queries