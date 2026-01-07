import fs from 'node:fs';

// In production, Laravel uses `public/build/manifest.json`.
// If `public/hot` exists, Laravel will try to load assets from the Vite dev server instead
// (which makes CSS/JS "not load" on deployed servers).
try {
    fs.rmSync('public/hot', { force: true });
} catch {
    // ignore
}


