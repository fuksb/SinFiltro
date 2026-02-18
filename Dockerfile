FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    chromium \
    chromium-driver

# Set environment for Puppeteer
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium
ENV CHROME_BIN=/usr/bin/chromium

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Install Node.js dependencies for scraper
RUN cd src/scraper && npm install puppeteer

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port
EXPOSE 80

# Environment variables (set these in Coolify dashboard)
# ENV GEMINI_API_KEY=your_key_here
# ENV CLAUDE_API_KEY=your_key_here

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
