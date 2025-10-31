#!/usr/bin/env node

/**
 * Simple script to generate favicon files
 * This creates SVG favicons that match the NeibrPay logo
 * For ICO format, use an online converter like realfavicongenerator.net
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const publicDir = path.join(__dirname, '../public');

// Favicon SVG (32x32) - matches the logo
const faviconSVG = `<svg width="32" height="32" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path
    d="M20 8L8 18V32C8 33.1046 8.89543 34 10 34H16V26C16 24.8954 16.8954 24 18 24H22C23.1046 24 24 24.8954 24 26V34H30C31.1046 34 32 33.1046 32 32V18L20 8Z"
    fill="#00C27A"
  />
  <path
    d="M20 8L8 18V32C8 33.1046 8.89543 34 10 34H16V26C16 24.8954 16.8954 24 18 24H22C23.1046 24 24 24.8954 24 26V34H30C31.1046 34 32 33.1046 32 32V18L20 8Z"
    stroke="#00C27A"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
  />
  <path
    d="M4 36C8 34 12 36 16 36C20 36 24 34 28 34C32 34 36 36 36 36"
    stroke="#00C27A"
    stroke-width="2"
    stroke-linecap="round"
    opacity="0.6"
  />
</svg>`;

// Apple touch icon SVG (180x180)
const appleTouchIconSVG = `<svg width="180" height="180" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect width="40" height="40" fill="white" rx="8"/>
  <path
    d="M20 8L8 18V32C8 33.1046 8.89543 34 10 34H16V26C16 24.8954 16.8954 24 18 24H22C23.1046 24 24 24.8954 24 26V34H30C31.1046 34 32 33.1046 32 32V18L20 8Z"
    fill="#00C27A"
  />
  <path
    d="M20 8L8 18V32C8 33.1046 8.89543 34 10 34H16V26C16 24.8954 16.8954 24 18 24H22C23.1046 24 24 24.8954 24 26V34H30C31.1046 34 32 33.1046 32 32V18L20 8Z"
    stroke="#00C27A"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
  />
  <path
    d="M4 36C8 34 12 36 16 36C20 36 24 34 28 34C32 34 36 36 36 36"
    stroke="#00C27A"
    stroke-width="2"
    stroke-linecap="round"
    opacity="0.6"
  />
</svg>`;

console.log('🎨 Generating NeibrPay favicon files...\n');

// Write SVG favicon
fs.writeFileSync(path.join(publicDir, 'favicon.svg'), faviconSVG);
console.log('✅ Created favicon.svg');

// Write SVG for apple touch icon
fs.writeFileSync(path.join(publicDir, 'apple-touch-icon.svg'), appleTouchIconSVG);
console.log('✅ Created apple-touch-icon.svg');

console.log('\n📋 Next steps to create ICO/PNG files:');
console.log('1. Open http://localhost:5173/generate-favicon.html in your browser');
console.log('2. Click the download buttons to get PNG files');
console.log('3. Convert PNG to ICO using: https://realfavicongenerator.net/');
console.log('4. Or use the SVG files directly (modern browsers support SVG favicons)');
console.log('\n💡 The SVG favicons are already set up and will work in modern browsers!');

