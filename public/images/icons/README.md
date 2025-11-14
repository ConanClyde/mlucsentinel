# PWA Icons - MLUC Sentinel

## Current Status
🔴 **Placeholder icons needed!** The PWA manifest references icon files that don't exist yet.

## How to Generate Icons

### Option 1: Use the Icon Generator (Easiest)
1. Open `public/images/icons/generate-icons.html` in your browser
2. Click "Generate All Icons" or "Download All"
3. Save each icon to this directory with the correct name:
   - `icon-72x72.png`
   - `icon-96x96.png`
   - `icon-128x128.png`
   - `icon-144x144.png`
   - `icon-152x152.png`
   - `icon-192x192.png`
   - `icon-384x384.png`
   - `icon-512x512.png`

### Option 2: Use Your Own Logo
1. Create a square logo (minimum 512x512px recommended)
2. Use an online tool like:
   - **PWA Asset Generator**: https://www.pwabuilder.com/
   - **Real Favicon Generator**: https://realfavicongenerator.net/
   - **Favicon.io**: https://favicon.io/favicon-converter/
3. Upload your logo and download the generated icons
4. Place them in this directory with the names listed above

### Option 3: Manual Creation
1. Open your preferred image editor (Photoshop, GIMP, Figma, etc.)
2. Create square canvases for each size listed above
3. Design your icon (keep it simple and recognizable)
4. Export as PNG files
5. Save to this directory

## Design Guidelines

### Icon Design Best Practices
- **Simple**: Icons should be clear at small sizes
- **Recognizable**: Use your brand colors and logo
- **Square**: All icons must be square (1:1 ratio)
- **Padding**: Leave ~10% padding around the edges
- **Background**: Include a background color (transparent can look bad)

### Current Placeholder
The included generator creates a simple "M" letter icon with:
- Background: Dark gradient (#1b1b18 to #3a3a36)
- Text: Light (#EDEDEC)
- Font: Bold Arial

### Recommended Colors
Match your app's theme:
- **Primary**: `#1b1b18` (dark)
- **Accent**: `#EDEDEC` (light)
- **Secondary**: `#3a3a36` (medium gray)

## Required Sizes

| Size | Purpose |
|------|---------|
| 72x72 | Android small icon |
| 96x96 | Android medium icon |
| 128x128 | Chrome Web Store |
| 144x144 | Microsoft Tile |
| 152x152 | iOS Safari |
| 192x192 | Android large icon, PWA install |
| 384x384 | Android extra large |
| 512x512 | Splash screens, PWA install |

## Maskable Icons
For best Android experience, create maskable versions:
- Safe zone: 80% of the icon (40px padding on 512px canvas)
- Full bleed: Design can extend to edges
- Center your logo in the safe zone

## After Generating Icons
1. Place all PNG files in this directory
2. Clear your browser cache
3. Reinstall the PWA
4. Verify icons appear correctly on:
   - Browser tabs
   - Home screen
   - App switcher
   - Splash screen

## Need Help?
- [PWA Icon Guidelines](https://web.dev/app-icons/)
- [Maskable Icons](https://maskable.app/)
- [PWA Best Practices](https://web.dev/pwa-checklist/)

