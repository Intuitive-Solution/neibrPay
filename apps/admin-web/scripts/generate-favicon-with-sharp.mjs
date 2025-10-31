#!/usr/bin/env node

/**
 * Generate favicon files from the NeibrPay logo SVG using Sharp
 * Creates favicon.ico and apple-touch-icon.png that match the logo
 */

import fs from 'fs';
import path from 'path';
import sharp from 'sharp';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const publicDir = path.join(__dirname, '../public');

// SVG content for favicon (icon-only version matching the logo)
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

// SVG for apple touch icon (180x180)
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

async function generateFavicon() {
  console.log('🎨 Generating NeibrPay favicon files...\n');

  try {
    // Generate favicon.ico (32x32)
    console.log('Generating favicon.ico (32x32)...');
    const faviconBuffer = await sharp(Buffer.from(faviconSVG))
      .resize(32, 32)
      .png()
      .toBuffer();

    // Sharp doesn't support ICO directly, so we'll save as PNG first
    // Then convert to ICO using a simple approach
    const faviconPNGPath = path.join(publicDir, 'favicon.png');
    await sharp(faviconBuffer).toFile(faviconPNGPath);
    
    // For ICO, we need to use a different approach
    // Create ICO file by copying PNG (most browsers accept PNG as ICO)
    // Or we can keep the PNG and reference it
    const faviconICOPath = path.join(publicDir, 'favicon.ico');
    await sharp(faviconBuffer).toFile(faviconICOPath);
    console.log('✅ Created favicon.ico');

    // Generate apple-touch-icon.png (180x180)
    console.log('Generating apple-touch-icon.png (180x180)...');
    const appleIconPath = path.join(publicDir, 'apple-touch-icon.png');
    await sharp(Buffer.from(appleTouchIconSVG))
      .resize(180, 180)
      .png()
      .toFile(appleIconPath);
    console.log('✅ Created apple-touch-icon.png');

    console.log('\n✨ Done! Favicon files have been generated and match the NeibrPay logo.');
  } catch (error) {
    console.error('❌ Error generating favicon files:', error);
    process.exit(1);
  }
}

generateFavicon();

