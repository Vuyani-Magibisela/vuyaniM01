## Style & Aesthetic Guide

### Color Palette

#### Primary Colors
- **Yellow Accent** - `#f5b642` - Used for highlights, CTAs, and interactive elements
- **Yellow Background** - `#f5ce42` - Used for section backgrounds (e.g., main employment)
- **Green Accent** - `#27ae60` - Used for status indicators and tags

#### UI Colors
- **Light Theme**
  - Background: `#f9f9f9`
  - Text: `#333333`
  - Secondary Text: `#666666`
  - Borders: `#dddddd`
  - Card Background: `#ffffff`
  - Input Background: `#f1f1f1`

- **Dark Theme**
  - Background: `#333333`
  - Text: `#f9f9f9`
  - Secondary Text: `#cccccc`
  - Borders: `#555555`
  - Card Background: `#3a3a3a` / `#444444`
  - Input Background: `#444444`

### Typography

#### Font Families
- **Primary Font**: 'Courier New', monospace - Used for all text elements
- **Fallback**: Default system monospace font

#### Font Sizes
- Hero Title: 2.5rem (desktop), 2rem (mobile)
- Section Headings: 1.8rem (desktop), 1.5rem (mobile)
- Card Headings: 1.3rem (desktop), 1.1rem (mobile)
- Body Text: 0.95rem
- Small Text: 0.8rem

#### Font Weights
- Bold: 700 - Used for headings and emphasis
- Regular: 400 - Used for body text

### UI Components

#### Buttons
- **Primary Button** (CTA):
  - Background: `#f5b642`
  - Text: White
  - Padding: 10px 30px
  - Border-radius: 30px
  - Hover: Scale up slightly, darken background

#### Cards
- **Standard Cards**:
  - Border-radius: 10px
  - Shadow: 0 5px 15px rgba(0, 0, 0, 0.05)
  - Hover: Transform translateY(-5px), increase shadow
  - Transitions: 0.3s for all properties

- **Expertise Cards**:
  - Left Accent: 5px solid #f5b642 (visible on hover)
  - Circle Icon Container: 60px diameter
  - Border-radius: 12px

#### Navigation
- **Desktop**:
  - Horizontal layout
  - Text links with hover effect
  - Active page: Yellow background pill with white text
  
- **Mobile**:
  - Burger menu icon
  - Slide-in panel from right
  - Button-style navigation items

#### Form Elements
- Input fields: Light gray background, rounded corners
- Subtle focus effect
- Consistent padding and spacing

### Layout Principles

#### Spacing
- **Section Spacing**: 40px margin between sections
- **Component Spacing**: 20-25px gap between cards/elements
- **Internal Padding**: 15-25px padding within cards and containers

#### Grid System
- Card-based layouts using CSS Grid
- Desktop: 4 columns for services, 2 columns for larger content
- Tablet: 2 columns
- Mobile: 1 column

#### Responsive Breakpoints
- Mobile: Up to 576px
- Tablet: 577px to 768px
- Small Desktop: 769px to 992px
- Large Desktop: 993px and above

### Animation Guidelines

#### Transitions
- Duration: 0.3s for most UI transitions
- Timing function: ease-out or ease-in-out
- Properties: transform, opacity, color, background-color

#### Hover Effects
- Cards: Slight rise effect with increased shadow
- Buttons: Scale effect (1.02-1.05)
- Links: Color change
- Special elements: Background color shift

#### Page Load Animations
- Hero elements: Fade in and slide up
- Content sections: Staggered reveal on scroll

### Imagery

#### Style Guidelines
- Clean, minimal design
- Consistent subject matter per category
- Uniform aspect ratios when possible
- Consistent color treatment

#### Iconography
- Circle containers for category icons
- Yellow highlight on hover
- Consistent sizing

### Design Principles

1. **Consistency** - Maintain uniform styles, spacing, and interactions
2. **Simplicity** - Clean, uncluttered layouts with clear hierarchy
3. **Contrast** - Ensure readability with proper text/background contrast
4. **Feedback** - Provide visual feedback for interactive elements
5. **Hierarchy** - Clear distinction between different levels of content
6. **Accessibility** - Ensure proper contrast ratios and readable text sizes