# Accessibility Strategy - Peanut License Server

This document outlines the accessibility approach for the Peanut License Server frontend, ensuring WCAG 2.1 Level AA compliance across all license management interfaces.

## Table of Contents

- [Compliance Standards](#compliance-standards)
- [Testing Strategy](#testing-strategy)
- [Architecture](#architecture)
- [Key Areas](#key-areas)
- [Testing Process](#testing-process)
- [Local Testing](#local-testing)
- [Known Limitations](#known-limitations)
- [Reporting Issues](#reporting-issues)
- [References](#references)

## Compliance Standards

The Peanut License Server frontend targets **WCAG 2.1 Level AA** accessibility compliance.

### Standards Covered

- **WCAG 2.1 AA** - Web Content Accessibility Guidelines Level AA
- **Section 508** - U.S. Federal accessibility requirements
- **EN 301 549** - European accessibility standard

### Key Principles (POUR)

- **Perceivable** - Information must be presentable to all users
- **Operable** - Components must be navigable via keyboard and assistive technology
- **Understandable** - Content and interfaces must be clear and intuitive
- **Robust** - Code must work across browsers and assistive technologies

## Testing Strategy

### Automated Testing

We use **jest-axe** (powered by axe-core) for automated accessibility testing:

- **Coverage**: 50+ accessibility checks per test
- **Integration**: Tests run in CI/CD pipeline on every PR
- **Scope**: Components, pages, tables, forms, modals, charts

### Test Categories

1. **Component Tests** - Individual UI elements
2. **Page Tests** - Full page scenarios
3. **Integration Tests** - User workflows
4. **Contrast Tests** - Color accessibility
5. **Keyboard Navigation** - Full keyboard operability
6. **Screen Reader** - ARIA and semantic HTML

## Architecture

### Test File Structure

```
frontend/src/test/
├── setup.ts              # Vitest configuration
├── setup-axe.ts          # jest-axe initialization
├── test-utils.tsx        # Custom render utilities
└── accessibility/
    └── components.a11y.test.tsx
```

### Component Accessibility

All components are built with accessibility first:

- **Button** - Focus states, disabled states, aria-labels
- **Input** - Label associations, error states, hints
- **Card** - Semantic structure, heading hierarchy
- **Badge** - Text + color indicators
- **Switch** - ARIA attributes, keyboard control
- **Modal** - Dialog role, focus trap, escape key
- **Table** - Proper headers, row/column associations
- **Layout** - Landmarks (header, nav, main, footer)

### Component Checklist

#### Button Component
- [ ] Has proper focus outline
- [ ] Supports keyboard activation (Space, Enter)
- [ ] Disabled state is accessible
- [ ] Loading state has aria-busy
- [ ] Color contrast >= 4.5:1 for text

#### Input Component
- [ ] Associated with label via htmlFor
- [ ] Required/optional clearly marked
- [ ] Error messages linked via aria-describedby
- [ ] Placeholder not used as label
- [ ] Type attribute matches content

#### Card Component
- [ ] Uses semantic HTML (article, section)
- [ ] Heading hierarchy is correct
- [ ] Content is in logical reading order
- [ ] No layout-only nesting

#### Badge Component
- [ ] Doesn't rely on color alone
- [ ] Includes text label (Active, Expired, etc.)
- [ ] Color contrast >= 3:1
- [ ] Consistent with status standards

#### Table Component
- [ ] Column headers in <th> elements
- [ ] Headers have scope="col"
- [ ] Row headers have scope="row"
- [ ] Sortable columns show aria-sort state
- [ ] Table caption or aria-label describes purpose

#### Modal Component
- [ ] Uses role="dialog"
- [ ] Has aria-labelledby pointing to title
- [ ] Focus trap implemented
- [ ] Escape key closes modal
- [ ] Close button always available

## Key Areas

### License Management Tables

The license data table is the core interface. Accessibility features:

**Headers & Structure**
- All column headers in `<th>` elements with scope="col"
- Sortable columns indicate sort direction via aria-sort="ascending|descending|none"
- Table has caption or aria-label describing purpose
- Column headers are keyboard accessible

**Status Indicators**
- License status (active, expired, suspended) uses **text + color**
- Never color-only indicators - always include text badge
- Color contrast >= 4.5:1 for text, >= 3:1 for UI
- Status badges include aria-label if needed

**Activation Count**
- Shows as "X/Y activations" (e.g., "2/3 activations")
- Bar chart (if used) has accessible description
- Current value announced via aria-current

**Row Actions**
- Edit, delete, pause/resume buttons in each row
- Accessible button labels (not icons alone)
- Confirmation dialogs prevent accidental actions
- Loading states indicated via aria-busy

### Analytics & Charts

Recharts visualization accessibility:

**Descriptions**
- Every chart has a text-based description
- Uses `<figure>` with `<figcaption>`
- Data table alternative available (or in documentation)
- Complex charts have aria-label with key findings

**Color Usage**
- Lines/segments use patterns, not just colors
- Legend clearly identifies all series
- Color palettes tested with color-blind simulators

**Interactive Elements**
- Tooltips keyboard accessible
- Zoom/pan operations have keyboard equivalents
- Download button available for raw data

### Audit Log Display

The audit trail prioritizes readability and context:

**List Structure**
- Semantic `<ul>` or `<ol>` for audit entries
- Each entry as `<li>` with clear structure
- Timestamps in machine-readable format with `<time>`

**Entry Content**
- Action clearly labeled (License Created, Revoked, etc.)
- Who performed action (user/email)
- When (date/time with dateTime attribute)
- What changed (detailed in expandable section)

**Timestamps**
- Use `<time dateTime="2024-03-27T10:30:00Z">March 27, 10:30 AM</time>`
- Machine-readable ISO 8601 format in datetime attribute
- Human-readable format in visible text
- Timezone indicated (or standardized to UTC)

### Dashboard Statistics

Overview cards and metrics:

**Stat Cards**
- Labeled with clear heading: "Total Licenses", "Active", "Expired"
- Number is semantic content, not just visual
- Trend indicator includes text (not arrow only)
- Region has aria-label for context

**Widgets**
- Each widget uses `role="region"` or `<section>`
- aria-label describes widget purpose
- Recent lists use `<ul>` with `<li>` items

**Live Updates**
- Use `role="status"` for dynamic content
- aria-live="polite" for announcements
- aria-atomic="true" for complete updates

### Forms & Filters

All form controls are fully accessible:

**Search Filter**
- Input type="search" (not just text)
- Clear label: "Search licenses"
- Results count announced via aria-live
- Clear button available

**Status Filter**
- Label: "Filter by status"
- Options include "All" as default
- Current selection clear
- Updates page dynamically with aria-live

**Tier Filter**
- Label: "License tier"
- All tiers listed as options
- Multi-select if available, has proper ARIA
- Selected items clearly indicated

**License Creation Form**
```html
<form>
  <div>
    <label for="email">Email Address *</label>
    <input id="email" type="email" required aria-required="true" />
    <span id="email-hint">We'll never share your email</span>
  </div>

  <div>
    <label for="tier">License Tier *</label>
    <select id="tier" required aria-required="true">
      <option value="">Select a tier</option>
      <option value="free">Free - 1 activation</option>
      <option value="pro">Pro - 3 activations</option>
      <option value="agency">Agency - 25 activations</option>
    </select>
  </div>

  <div>
    <label for="years">License Duration</label>
    <input id="years" type="number" min="1" max="5" />
  </div>

  <button type="submit">Create License</button>
</form>
```

### Navigation & Layout

The entire interface uses semantic landmarks:

```html
<body>
  <!-- Skip link (sr-only) for keyboard users -->
  <a href="#main" class="sr-only">Skip to main content</a>

  <!-- Header with logo/branding -->
  <header role="banner">
    <h1>Peanut License Server</h1>
  </header>

  <!-- Main navigation -->
  <nav role="navigation" aria-label="Main">
    <ul>
      <li><a href="/dashboard">Dashboard</a></li>
      <li><a href="/licenses">Licenses</a></li>
      <li><a href="/audit">Audit Log</a></li>
    </ul>
  </nav>

  <!-- Main content -->
  <main id="main">
    <!-- Page content here -->
  </main>

  <!-- Supplementary info (footer) -->
  <footer role="contentinfo">
    <p>Peanut Graphic - License Management</p>
  </footer>
</body>
```

**Landmark Roles**
- `<header role="banner">` - Page header
- `<nav>` - Navigation (multiple navs have aria-label)
- `<main id="main">` - Primary content
- `<footer role="contentinfo">` - Page footer
- `<aside role="complementary">` - Sidebars

### Focus Management

All interactive elements have visible focus:

**Focus Styles**
```css
button:focus,
input:focus,
a:focus {
  outline: 2px solid #0066cc;
  outline-offset: 2px;
}
```

**Modal Focus**
- Focus moves to modal on open
- Focus trapped within modal
- Focus returns to trigger on close

**Dynamic Content**
- New content announced via aria-live
- Focus moved to new content (e.g., loaded list)
- Loading states have aria-busy

### Skip Links

- Visible on focus: "Skip to main content"
- Links to `#main` element
- High z-index (always accessible)
- High contrast styling

## Testing Process

### During Development

1. **Component Level**
   ```bash
   npm run test:a11y -- --watch
   ```
   Tests run on file changes

2. **Manual Testing**
   - Use screen reader (NVDA, JAWS, VoiceOver)
   - Navigate with keyboard only
   - Check color contrast
   - Test zoom at 200%

3. **Browser DevTools**
   - Chrome: DevTools > Lighthouse > Accessibility
   - Firefox: Inspector > Accessibility panel

### Before Commit

```bash
npm run test:a11y
```

All accessibility tests must pass.

### CI/CD Pipeline

1. **PR Checks**
   - ESLint jsx-a11y plugin
   - jest-axe test suite
   - Coverage reporting

2. **Pass/Fail Criteria**
   - No axe violations (serious/critical)
   - jsx-a11y warnings resolved
   - Coverage >= 80%

## Local Testing

### Setup

```bash
cd frontend
npm install
```

### Running Tests

```bash
# Run all a11y tests
npm run test:a11y

# Watch mode
npm run test:a11y -- --watch

# With coverage
npm run test:a11y -- --coverage

# Using script
../scripts/a11y-check.sh

# Script with options
../scripts/a11y-check.sh --watch
../scripts/a11y-check.sh --coverage
```

### Manual Testing

#### Screen Readers

**macOS (VoiceOver)**
- Cmd + F5 to enable
- VO + U for rotor
- VO + Right arrow to navigate

**Windows (NVDA)**
- Download from nvaccess.org
- Insert key for commands
- Browse mode vs focus mode

**Windows (JAWS)**
- Commercial license required
- Most thorough testing
- Enterprise standard

#### Keyboard Navigation

```bash
# Disable mouse
# Tab through interface
# Enter/Space for buttons
# Arrow keys for lists/menus
# Escape for modals
```

#### Color Contrast

- Use WebAIM Color Contrast Checker
- Test all interactive elements
- Target: 4.5:1 for small text, 3:1 for large

#### Zoom Testing

- Browser: Ctrl++ (or Cmd++ on Mac)
- Test at 200% zoom
- Layout should not break
- Text should not overlap

#### Tools

```bash
# Chrome DevTools Lighthouse
- Lighthouse > Accessibility tab
- Automated checks + tips

# WebAIM WAVE Browser Extension
- Visual feedback on issues
- Color contrast analysis

# Axe DevTools Browser Extension
- Similar to jest-axe
- Interactive testing
```

## Known Limitations

### Charts (Recharts)

- Visual charts are complex to make fully accessible
- Workaround: Provide data table alternative
- Limit to simple charts (bar, line)
- Always include text summary

### Color-Blind Simulation

- Tests check color + text
- Some edge cases may exist
- Request feedback from users with color blindness

### Screen Reader Compatibility

- Primary targets: NVDA, JAWS, VoiceOver
- Mobile: TalkBack (Android), VoiceOver (iOS)
- Some proprietary screen readers may vary

### Touch Accessibility

- Focus/hover states may not work on touch
- Test on mobile devices
- Ensure sufficient touch target size (44x44px)

## Reporting Issues

Found an accessibility issue? Please:

1. **Check Existing Issues**
   - Search GitHub issues for duplicates
   - Label: `accessibility`

2. **Open an Issue**
   ```
   Title: [A11Y] Brief description

   Environment:
   - Browser:
   - Screen reader:
   - OS:

   Steps to reproduce:
   1.
   2.
   3.

   Expected:
   Actual:
   ```

3. **Labels**
   - `accessibility` - Accessibility issue
   - `critical` - Blocks access for some users
   - `wxag2.1-a` - Level A violation
   - `wcag2.1-aa` - Level AA violation
   - `wcag2.1-aaa` - Level AAA (enhancement)

## References

### Standards
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [Authoring Practices Guide](https://www.w3.org/WAI/ARIA/apg/)
- [Section 508 Standards](https://www.access-board.gov/ict/)

### Tools
- [jest-axe](https://github.com/nickcolley/jest-axe)
- [axe DevTools](https://www.deque.com/axe/devtools/)
- [WAVE](https://wave.webaim.org/)
- [WebAIM Color Contrast](https://webaim.org/resources/contrastchecker/)

### Resources
- [MDN Accessibility](https://developer.mozilla.org/en-US/docs/Web/Accessibility)
- [A11ycasts](https://www.youtube.com/playlist?list=PLNYkxOF6rcICWx0C9Xc-RgEzwLvePng7V)
- [The A11Y Handbook](https://www.handbook.a11y.co/)
- [inclusive components](https://inclusive-components.design/)

### Related Peanut Docs
- [INTEGRATION.md](./INTEGRATION.md) - API integration patterns
- [DATABASE.md](./DATABASE.md) - Data structures
- [SECURITY.md](./SECURITY.md) - Security measures

---

**Last Updated**: March 27, 2024
**Maintained By**: Peanut Development Team
**Status**: Active - Updated with every release
