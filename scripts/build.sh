#!/bin/bash
# Coolify Build Script for SinFiltro

echo "=== Starting build process ==="

# Install Node.js dependencies for Puppeteer
echo "Installing Node.js dependencies..."
cd /app/www/src/scraper
npm install --production

echo "=== Build complete ==="
