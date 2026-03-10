#!/usr/bin/env node
const fs = require('fs');
const path = require('path');

const root = process.cwd();
const src = path.join(root, 'node_modules', 'leaflet', 'dist');
const dest = path.join(root, 'public', 'vendor', 'leaflet');

if (!fs.existsSync(src)) {
  console.error('Run first: npm install leaflet --save-dev');
  process.exit(1);
}

fs.mkdirSync(dest, { recursive: true });
['leaflet.js', 'leaflet.css'].forEach((f) => {
  fs.copyFileSync(path.join(src, f), path.join(dest, f));
});
const imgDest = path.join(dest, 'images');
fs.mkdirSync(imgDest, { recursive: true });
fs.readdirSync(path.join(src, 'images')).forEach((f) => {
  fs.copyFileSync(path.join(src, 'images', f), path.join(imgDest, f));
});
console.log('Leaflet copied to public/vendor/leaflet');
