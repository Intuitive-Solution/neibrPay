# NeibrPay Logo Update - Bonsai Design System

## New SVG Logo Component

Created a new `NeibrPayLogo.vue` component that replaces the old PNG logo with a clean, SVG-based design that matches the Bonsai-inspired design system.

### Features

- **SVG-based**: Scalable vector graphics for crisp display at any size
- **Bonsai Green Accent**: Uses #00C27A as primary brand color
- **Responsive Variants**:
  - Full logo with text (expanded sidebar)
  - Icon-only version (collapsed sidebar)
- **Multiple Sizes**: Small, Medium, Large variants
- **Clean Design**: Modern house icon with wave element
- **Hover Effects**: Subtle color transitions on interaction

### Usage

```vue
<!-- Full Logo -->
<NeibrPayLogo />

<!-- Icon Only (collapsed state) -->
<NeibrPayLogo icon-only />

<!-- Size Variants -->
<NeibrPayLogo size="sm" />
<NeibrPayLogo size="md" />
<!-- default -->
<NeibrPayLogo size="lg" />
```

### Color Scheme

- **Primary Brand**: #00C27A (Bonsai Green)
- **Text**:
  - "Neibr" - #1F2937 (Dark Gray)
  - "Pay" - #00C27A (Bonsai Green)
- **Wave Accent**: #00C27A with 60% opacity

### Component Location

- **Component**: `/apps/admin-web/src/components/NeibrPayLogo.vue`
- **Used In**: `/apps/admin-web/src/components/AppSidebar.vue`

### Old Logo Reference

The old PNG logo (`/apps/admin-web/public/owner-logo.png`) featured:

- Blue background
- Green house icon
- White and green wave patterns

The new logo maintains the house and wave concept but with:

- Transparent background
- Cleaner, more modern styling
- Consistent with Bonsai design system
- Better scalability

### Future Enhancements

If needed, you can:

1. Export the SVG logo as a PNG using a design tool or headless browser
2. Replace `/apps/admin-web/public/owner-logo.png` with the new design
3. Update favicon and app icons to match the new branding
4. Create different color variants for dark mode (if implemented)

### Design Principles Applied

✅ Clean & Minimal design
✅ Bonsai green accent (#00C27A)
✅ Professional typography
✅ Scalable vector format
✅ Consistent with overall UI redesign
