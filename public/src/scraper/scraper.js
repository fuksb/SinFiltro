#!/usr/bin/env node
/**
 * Puppeteer Scraper for SinFiltro
 * Usage: node scraper.js <url>
 * Output: JSON with extracted text or error
 */

const puppeteer = require('puppeteer');

async function scrapeUrl(url) {
    if (!url) {
        console.error('No URL provided');
        process.exit(1);
    }

    // Validate URL
    try {
        new URL(url);
    } catch (e) {
        console.error('Invalid URL');
        process.exit(1);
    }

    let browser;
    try {
        browser = await puppeteer.launch({
            headless: 'new',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--no-first-run',
                '--no-zygote',
                '--single-process',
                '--disable-web-security',
                '--user-agent=Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ]
        });

        const page = await browser.newPage();
        
        // Set viewport
        await page.setViewport({ width: 1280, height: 800 });

        // Navigate with longer timeout for JS-rendered content
        await page.goto(url, { 
            waitUntil: 'networkidle2',
            timeout: 30000 
        });

        // Wait for content to load (additional wait for dynamic content)
        await new Promise(resolve => setTimeout(resolve, 5000));

        // Scroll down to trigger lazy-loaded content
        await page.evaluate(async () => {
            await new Promise((resolve) => {
                let totalHeight = 0;
                const distance = 100;
                const timer = setInterval(() => {
                    const scrollHeight = document.body.scrollHeight;
                    window.scrollBy(0, distance);
                    totalHeight += distance;
                    
                    if(totalHeight >= scrollHeight - window.innerHeight) {
                        clearInterval(timer);
                        resolve();
                    }
                }, 100);
                // Timeout after 3 seconds
                setTimeout(() => { clearInterval(timer); resolve(); }, 3000);
            });
        });

        // Wait a bit more after scrolling
        await new Promise(resolve => setTimeout(resolve, 2000));

        // Try to wait for specific elements that indicate content is loaded
        try {
            await page.waitForSelector('body', { timeout: 5000 });
        } catch (e) {
            // Body not found, continue anyway
        }

        // Extract text content
        const content = await page.evaluate(() => {
            // Remove scripts, styles, and hidden elements
            const elementsToRemove = document.querySelectorAll('script, style, noscript, iframe, nav, footer, header, .cookie-banner, .cookie-consent, #cookie-notice, .popup, .modal');
            elementsToRemove.forEach(el => el.remove());

            // Get main content
            let mainContent = '';
            
            // Try to find main content areas
            const mainElements = document.querySelectorAll('main, article, .content, .main-content, #content, .container main');
            if (mainElements.length > 0) {
                mainContent = mainElements.map(el => el.innerText).join('\n');
            }

            // Fall back to body text
            if (!mainContent || mainContent.length < 100) {
                mainContent = document.body.innerText;
            }

            return mainContent;
        });

        // Also get title
        const title = await page.title();

        // Get metadata
        const metadata = await page.evaluate(() => {
            const description = document.querySelector('meta[name="description"]')?.content || '';
            const ogTitle = document.querySelector('meta[property="og:title"]')?.content || '';
            return { description, ogTitle };
        });

        // Return result
        const result = {
            success: true,
            url: url,
            title: title,
            content: content.trim(),
            metadata: metadata,
            extractedAt: new Date().toISOString()
        };

        console.log(JSON.stringify(result));

    } catch (error) {
        const errorResult = {
            success: false,
            error: error.message,
            url: url
        };
        console.error(JSON.stringify(errorResult));
        process.exit(1);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
}

// Run if called directly
if (require.main === module) {
    const url = process.argv[2];
    scrapeUrl(url);
}

module.exports = { scrapeUrl };
