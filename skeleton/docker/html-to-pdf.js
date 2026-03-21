#!/usr/bin/env node
/**
 * html-to-pdf.js
 * Usage: node html-to-pdf.js <input.html> <output.pdf>
 * Placer dans /usr/local/bin/ et chmod +x
 */

const puppeteer = require('puppeteer');
const path      = require('path');
const fs        = require('fs');

(async () => {
    const [,, inputHtml, outputPdf] = process.argv;

    if (!inputHtml || !outputPdf) {
        console.error('Usage: node html-to-pdf.js <input.html> <output.pdf>');
        process.exit(1);
    }

    const absolutePath = path.resolve(inputHtml);

    if (!fs.existsSync(absolutePath)) {
        console.error('Input file not found: ' + absolutePath);
        process.exit(1);
    }

    const browser = await puppeteer.launch({
        executablePath: process.env.PUPPETEER_EXECUTABLE_PATH || '/usr/bin/chromium',
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
        ],
    });

    const page = await browser.newPage();

    await page.goto('file://' + absolutePath, { waitUntil: 'networkidle0' });

    await page.pdf({
        path:   outputPdf,
        format: 'A4',
        margin: { top: '10mm', bottom: '10mm', left: '10mm', right: '10mm' },
        printBackground: true,
    });

    await browser.close();
})();
