const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

async function convertFileToWebp(filePath) {
    const ext = path.extname(filePath);
    if (!/\.(png|jpe?g)$/i.test(ext)) return;
    const webpPath = filePath.slice(0, -ext.length) + '.webp';
    try {
        const inputStat = fs.statSync(filePath);
        const info = await sharp(filePath).webp({ quality: 82, effort: 4 }).toFile(webpPath);
        console.log(`Optimized: ${filePath} (${(inputStat.size/1024).toFixed(1)}KB) -> ${webpPath} (${(info.size/1024).toFixed(1)}KB)`);
    } catch (err) {
        console.error(`Failed to convert ${filePath}:`, err.message);
    }
}

async function processDir(dir) {
    if (!fs.existsSync(dir)) return;
    const entries = fs.readdirSync(dir, { withFileTypes: true });
    for (const entry of entries) {
        const fullPath = path.join(dir, entry.name);
        if (entry.isDirectory()) {
            await processDir(fullPath);
        } else if (/\.(png|jpe?g)$/i.test(entry.name)) {
            await convertFileToWebp(fullPath);
        }
    }
}

async function main() {
    console.log('Optimizing graphic resources to WebP format...');
    await convertFileToWebp('public/fusabot.png');
    await convertFileToWebp('public/logo.png');
    await processDir('storage/app/public');
    console.log('Image optimization to WebP completed.');
}

main();
