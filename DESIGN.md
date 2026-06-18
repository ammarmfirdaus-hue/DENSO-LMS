# DESIGN SYSTEM

## Denso Learning System (DLS)

### Version 1.0 - Soft Launching

---

# 1. DESIGN OVERVIEW

Denso Learning System (DLS) is a web-based Learning Management System developed to support internal learning activities at PT Denso Indonesia.

The design system is created to ensure consistency, scalability, usability, and maintainability across all modules, screens, and future system enhancements.

The design language combines:

* Enterprise Software Standards
* Corporate Identity of PT Denso
* Modern Learning Experience
* Mobile-First Design Philosophy
* Accessibility Best Practices

The system prioritizes:

* Clarity
* Consistency
* Efficiency
* Accessibility
* Reliability

---

# 2. BRAND & STYLE

The design system is built on a Corporate-Modern aesthetic that bridges industrial precision with educational clarity.

The visual language reflects PT Denso's manufacturing excellence while maintaining a lightweight and approachable learning experience.

The interface should communicate:

* Professionalism
* Reliability
* Structure
* Productivity
* Learning Progress

The visual approach adopts selected iOS-inspired patterns:

* Clean whitespace
* Simple hierarchy
* Lightweight interfaces
* Minimal visual noise
* Clear navigation

The objective is to reduce cognitive load while maintaining enterprise-grade usability.

---

# 3. DESIGN PRINCIPLES

## 3.1 Clarity First

Every screen must communicate its purpose immediately.

Users should always understand:

* Where they are
* What they can do
* What action should be taken next

---

## 3.2 Consistency

All components must behave consistently across the application.

This includes:

* Colors
* Typography
* Buttons
* Forms
* Navigation
* Icons

---

## 3.3 Efficiency

Users should complete learning tasks with minimal interactions.

Workflows must prioritize:

* Speed
* Simplicity
* Productivity

---

## 3.4 Accessibility

Interfaces must remain usable under various environments:

* Factory floor
* Office environment
* Mobile devices
* Desktop devices

---

## 3.5 Trust & Reliability

The interface should communicate stability and confidence.

System information must always appear:

* Accurate
* Organized
* Professional

---

# 4. COLOR SYSTEM

## Primary Color

DENSO Red

Purpose:

* Branding
* Corporate Identity
* High-level emphasis

```yaml
Primary: #B4000B
On Primary: #FFFFFF
```

---

## Secondary Color

Indigo

Purpose:

* Interactive elements
* Call-to-action buttons
* Navigation

```yaml
Secondary: #4355B9
On Secondary: #FFFFFF
```

---

## Success Color

Green

Purpose:

* Progress
* Completion
* Success status

```yaml
Tertiary: #00671A
```

---

## Error Color

Red

Purpose:

* Validation errors
* Failed actions

```yaml
Error: #BA1A1A
```

---

## Neutral Palette

Purpose:

* Backgrounds
* Containers
* Text hierarchy

Background:

```yaml
#FCF9F8
```

Primary Text:

```yaml
#1B1C1C
```

Secondary Text:

```yaml
#5E3F3B
```

---

# 5. TYPOGRAPHY SYSTEM

Font Family:

```text
Inter
```

Fallback:

```text
Arial
Helvetica
sans-serif
```

---

## Headline Large

Used for:

* Main page titles

Properties:

```yaml
Size: 28px
Weight: 700
Line Height: 34px
```

---

## Headline Medium

Used for:

* Section titles

Properties:

```yaml
Size: 22px
Weight: 700
Line Height: 28px
```

---

## Headline Small

Used for:

* Card titles
* Modal titles

Properties:

```yaml
Size: 18px
Weight: 700
Line Height: 24px
```

---

## Body Large

Used for:

* Learning content
* Descriptions

Properties:

```yaml
Size: 16px
Weight: 400
```

---

## Body Medium

Used for:

* Default content

Properties:

```yaml
Size: 14px
Weight: 400
```

---

## Labels

Used for:

* Buttons
* Navigation
* Form labels

Properties:

```yaml
Size: 12px-14px
Weight: 500
```

---

# 6. GRID & SPACING SYSTEM

Base Unit:

```text
8px
```

Spacing Scale:

```yaml
8px
16px
24px
32px
40px
48px
```

---

## Container Margins

Mobile:

```yaml
16px
```

Desktop:

```yaml
24px
```

---

## Vertical Rhythm

Small Gap:

```yaml
8px
```

Medium Gap:

```yaml
16px
```

Large Gap:

```yaml
24px
```

---

# 7. RESPONSIVE BREAKPOINTS

| Device       | Width   |
| ------------ | ------- |
| Mobile Small | <576px  |
| Mobile Large | ≥576px  |
| Tablet       | ≥768px  |
| Laptop       | ≥992px  |
| Desktop      | ≥1200px |
| Wide Screen  | ≥1400px |

Strategy:

Mobile First

---

# 8. SHAPES & BORDER RADIUS

Buttons:

```yaml
12px
```

Input Fields:

```yaml
12px
```

Cards:

```yaml
16px
```

Badges:

```yaml
9999px
```

---

# 9. ELEVATION & DEPTH

## Card Shadow

```css
0 4px 12px rgba(0,0,0,0.05)
```

---

## Modal Shadow

```css
0 8px 24px rgba(0,0,0,0.08)
```

---

## Hover Shadow

```css
0 6px 16px rgba(0,0,0,0.08)
```

---

# 10. ACCESSIBILITY STANDARDS

Minimum Touch Area:

```yaml
44px x 44px
```

Minimum Contrast:

```yaml
WCAG AA
```

All forms must support:

* Keyboard Navigation
* Focus Indicators
* Error Messages
* Screen Reader Compatibility

Color alone must never communicate information.

---

# 11. ICONOGRAPHY

Icon Library:

```text
Bootstrap Icons
```

Sizes:

```yaml
Small: 16px
Medium: 20px
Default: 24px
Large: 32px
```

Icons must support content and never replace text labels.

---

# 12. MOTION & INTERACTION

Fast:

```yaml
150ms
```

Normal:

```yaml
250ms
```

Slow:

```yaml
350ms
```

Hover Effects:

* Color transition
* Shadow transition

Avoid excessive animations.

---

# 13. COMPONENT STANDARDS

## Buttons

### Primary

* Secondary Color Background
* White Text
* 12px Radius

### Secondary

* White Background
* Secondary Border

### Tertiary

* Text Only

---

## Cards

### Course Card

Contains:

* Thumbnail
* Title
* Teacher
* Progress
* Status

---

### Statistic Card

Contains:

* Value
* Description
* Trend Indicator

---

## Forms

Contains:

* Label
* Input
* Validation Message

Focus State:

```yaml
Secondary Color Border
```

---

# 14. LMS COMPONENTS

## Course Card

Displays:

* Course Thumbnail
* Course Title
* Description
* Teacher
* Progress

---

## Session Card

Displays:

* Session Number
* Session Title
* Completion Status

---

## Material Viewer

Supports:

* PDF
* Video
* Image
* Document

---

## Quiz Component

Supports:

* Multiple Choice
* Single Choice

---

## Final Assessment Component

Supports:

* Course Evaluation
* Pass/Fail Result

---

## Progress Tracker

Displays:

* Progress %
* Completed Sessions
* Remaining Sessions

---

# 15. NAVIGATION SYSTEM

## Mobile

Bottom Navigation

Contains:

* Dashboard
* Course
* Activity
* Profile

---

## Desktop

Sidebar Navigation

Contains:

* Dashboard
* Course
* Activity
* Profile
* Settings

---

# 16. DATA TABLE STANDARDS

Features:

* Search
* Pagination
* Sorting
* Responsive Layout

Default Rows:

```yaml
10
```

Row Height:

```yaml
48px
```

---

# 17. DATA VISUALIZATION

Library:

```text
Chart.js
```

Allowed Charts:

* Bar Chart
* Line Chart
* Doughnut Chart

Charts must prioritize readability over decoration.

---

# 18. FEEDBACK & ALERTS

Library:

```text
Bootstrap 5 Modal
Bootstrap 5 Toast
Bootstrap 5 Alert
```

Success:

```yaml
Green
```

Warning:

```yaml
Orange
```

Error:

```yaml
Red
```

Info:

```yaml
Blue
```

---

# 19. EMPTY STATES

Every module must provide:

* Icon
* Title
* Description
* Recommended Action

Examples:

* No Course Available
* No Quiz Available
* No Learning History
* No Notifications

---

# 20. DASHBOARD STANDARDS

Dashboard must prioritize:

* Information Hierarchy
* Progress Visibility
* Learning Metrics

Avoid excessive decorative elements.

---

# 21. ENTERPRISE UI STANDARDS

Forms:

* Simple
* Structured
* Easy to Scan

Tables:

* Data Focused
* Searchable
* Action Oriented

Learning Screens:

* Minimal Distraction
* Content First

Administrative Screens:

* Data First
* Productivity First

---

# 22. TECHNOLOGY ALIGNMENT

Frontend

* HTML5
* CSS3
* Bootstrap 5 Offline
* Vanilla JavaScript

Backend

* PHP Native 8.3

Database

* Microsoft SQL Server

Libraries

* DataTables Offline
* Chart.js Offline
* Bootstrap 5 Modal
* Bootstrap 5 Toast
* Bootstrap 5 Alert
* DomPDF / mPDF

---

# 23. DESIGN SYSTEM GOVERNANCE

All future components must comply with:

* Color System
* Typography System
* Grid System
* Accessibility Rules
* Enterprise UI Standards

Any new component must be documented before implementation.

---

---
name: Denso Learning System
colors:
  surface: '#fcf9f8'
  surface-dim: '#dcd9d9'
  surface-bright: '#fcf9f8'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f6f3f2'
  surface-container: '#f0eded'
  surface-container-high: '#eae7e7'
  surface-container-highest: '#e5e2e1'
  on-surface: '#1b1c1c'
  on-surface-variant: '#5e3f3b'
  inverse-surface: '#303030'
  inverse-on-surface: '#f3f0ef'
  outline: '#936e69'
  outline-variant: '#e9bcb6'
  surface-tint: '#c0000d'
  primary: '#b4000b'
  on-primary: '#ffffff'
  primary-container: '#e20613'
  on-primary-container: '#fff3f2'
  inverse-primary: '#ffb4aa'
  secondary: '#4355b9'
  on-secondary: '#ffffff'
  secondary-container: '#8596ff'
  on-secondary-container: '#11278e'
  tertiary: '#00671a'
  on-tertiary: '#ffffff'
  tertiary-container: '#178229'
  on-tertiary-container: '#deffd6'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdad5'
  primary-fixed-dim: '#ffb4aa'
  on-primary-fixed: '#410001'
  on-primary-fixed-variant: '#930007'
  secondary-fixed: '#dee0ff'
  secondary-fixed-dim: '#bac3ff'
  on-secondary-fixed: '#00105c'
  on-secondary-fixed-variant: '#293ca0'
  tertiary-fixed: '#94f990'
  tertiary-fixed-dim: '#78dc77'
  on-tertiary-fixed: '#002204'
  on-tertiary-fixed-variant: '#005313'
  background: '#fcf9f8'
  on-background: '#1b1c1c'
  surface-variant: '#e5e2e1'
typography:
  headline-lg:
    fontFamily: Inter
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 34px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Inter
    fontSize: 22px
    fontWeight: '700'
    lineHeight: 28px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '700'
    lineHeight: 24px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.02em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  container-margin: 16px
  gutter: 16px
  stack-sm: 8px
  stack-md: 16px
  stack-lg: 24px
---

## Brand & Style

The design system is built on a "Corporate-Modern" aesthetic that bridges industrial precision with educational clarity. The brand personality is professional, reliable, and efficient—reflecting PT. DENSO’s manufacturing excellence—while maintaining an airy and lightweight feel to reduce cognitive load for employees and educators.

The visual language follows iOS-inspired patterns: high-quality whitespace, intuitive navigation, and subtle depth. It aims to evoke a sense of organized progress and institutional trust, ensuring that the transition from the factory floor or classroom to the digital learning environment feels seamless and premium.

## Colors

This design system utilizes a strategic color hierarchy to balance brand heritage with functional usability.

- **Primary (DENSO Red):** Reserved for brand identification, high-level headers, and critical brand touchpoints. It is used sparingly to prevent visual fatigue.
- **Accent (Indigo):** The workhorse for interactivity. Used for primary call-to-action buttons, active states, and navigation links to clearly distinguish "brand" from "action."
- **Success (Green):** Applied to progress bars, completion badges, and positive status indicators, reinforcing a sense of achievement.
- **Neutrals:** A dominant white background ensures a "clean" feel, while very light gray provides logical separation between content modules. Primary text uses a soft black to maintain high legibility without the harshness of pure black.

## Typography

The system uses **Inter** for its exceptional legibility and systematic, functional appearance. The typographic scale is optimized for mobile-first consumption, ensuring that lesson content and instructions are easy to read in various lighting conditions (e.g., factory floors or bright offices).

- **Headlines (Bold):** Used for screen titles (e.g., "Materi Pelatihan") and module headers.
- **Body (Regular):** Used for course descriptions, instructions, and general content.
- **Labels (Medium):** Used for navigation items, button text, and metadata labels to provide a slight visual distinction from body text.

All Indonesian text should prioritize clarity and directness, using standard professional terminology (Bahasa Indonesia Baku).

## Layout & Spacing

The layout operates on an **8px grid system** to ensure mathematical harmony across all screen sizes. 

- **Mobile Philosophy:** A fluid grid with 16px side margins. Content cards typically span the full width of the safe area.
- **Vertical Rhythm:** Elements are stacked using multiples of 8px. Smaller components (like icons and labels) use 8px spacing, while distinct sections use 24px to maintain an "airy" feel.
- **Touch Targets:** All interactive elements maintain a minimum height of 44px to accommodate comfortable thumb interaction on mobile devices.

## Elevation & Depth

This design system uses a "Soft Layering" approach to create hierarchy without clutter. Depth is communicated through:

- **Tonal Separation:** Using the light gray (`#F5F5F5`) background to sit "behind" white (`#FFFFFF`) cards.
- **Subtle Shadows:** Cards and primary buttons utilize a soft, diffused shadow (Y: 4, Blur: 12, Opacity: 0.05) to appear slightly lifted from the surface.
- **High-Level Headers:** The top navigation bar uses a subtle 1px bottom border or a backdrop blur (glassmorphism) when scrolling to maintain context without heavy visual weight.

## Shapes

The shape language is friendly yet structured. A consistent corner radius of **12px to 16px** is applied to all primary containers, including:
- **Buttons:** 12px for a modern, approachable feel.
- **Cards:** 16px for content modules (e.g., course cards, progress summaries).
- **Input Fields:** 12px to match button styling for a cohesive form-entry experience.

Small elements like tags or badges may use a full pill-shape (circular ends) to contrast against the more structured rectangular cards.

## Components

### Buttons
- **Primary:** Indigo background, white text, 12px rounded corners. Used for "Mulai Belajar" or "Kirim Jawaban."
- **Secondary:** White background with Indigo outline or light gray fill.
- **Tertiary:** Text-only with Medium weight for low-priority actions like "Batal."

### Cards
- **Course Card:** White background, subtle shadow, 16px radius. Includes a thumbnail image at the top, a "Success Green" progress bar at the bottom, and "Bold" titles.
- **Statistic Card:** Centered typography with "Label Medium" descriptors for tracking employee performance metrics.

### Inputs & Forms
- **Text Fields:** 12px radius, light gray border (1px). Focus state uses an Indigo border.
- **Selection:** Large, tappable list items for quiz options, using 8px spacing between choices.

### Navigation
- **Bottom Tab Bar:** Minimal icons (24px) with "Label-sm" text. Active state is highlighted in Indigo.
- **Status Bar:** iOS standard (9:41 style) for consistent system-level integration.

### Feedback Elements
- **Progress Bars:** 8px height, rounded ends. Track is light gray; fill is Success Green.
- **Badges:** Small, pill-shaped tags for "Wajib" (Required) or "Selesai" (Completed) status.

END OF DOCUMENT
