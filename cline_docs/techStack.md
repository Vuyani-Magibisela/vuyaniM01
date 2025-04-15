# Technology Stack

## PHPMailer
- **Purpose**: Handle email communication for the contact form
- **Installation**: Installed via Composer with the following dependency:
  ```json
  "phpmailer/phpmailer": "^6.9.2"
- **Usage**: 
  - Autoloaded via Composer's autoloader
  - Configured with SMTP settings for secure email sending
  - Integrated into the contact form for email submission

## File Structure
- **config/**:
  - Contains database and route configurations
- **app/Views/**:
  - Holds all view files including the base layout and contact form
- **public/**:
  - Will contain assets like CSS and JavaScript

## Development Workflow
- Using Composer for dependency management
- Following MVC principles for code organization
- Implementing proper error handling and logging
