import fs from 'node:fs';
import path from 'node:path';

// In production, Laravel uses `public/build/manifest.json`.
// If `public/hot` exists, Laravel will try to load assets from the Vite dev server instead
// (which makes CSS/JS "not load" on deployed servers).

console.log('🧹 Cleaning up Vite dev server files...');

// Remove public/hot file (forces Laravel to use built assets)
try {
    const hotFile = path.join('public', 'hot');
    if (fs.existsSync(hotFile)) {
        fs.rmSync(hotFile, { force: true });
        console.log('✅ Removed public/hot');
    } else {
        console.log('ℹ️  public/hot does not exist (already clean)');
    }
} catch (error) {
    console.warn('⚠️  Could not remove public/hot:', error.message);
}

// Verify manifest.json exists and copy from .vite/ if needed
try {
    const manifestFile = path.join('public', 'build', 'manifest.json');
    const viteManifestFile = path.join('public', 'build', '.vite', 'manifest.json');
    
    let manifest;
    
    // Check if manifest exists at root location
    if (fs.existsSync(manifestFile)) {
        manifest = JSON.parse(fs.readFileSync(manifestFile, 'utf-8'));
        const assetCount = Object.keys(manifest).length;
        console.log(`✅ Found manifest.json with ${assetCount} assets`);
    } 
    // Check if manifest exists in .vite subdirectory (newer Vite versions)
    else if (fs.existsSync(viteManifestFile)) {
        console.log('ℹ️  Found manifest in .vite/ directory, copying to root...');
        manifest = JSON.parse(fs.readFileSync(viteManifestFile, 'utf-8'));
        
        // Copy manifest to root location (Laravel expects it there)
        fs.copyFileSync(viteManifestFile, manifestFile);
        
        const assetCount = Object.keys(manifest).length;
        console.log(`✅ Copied manifest.json to root with ${assetCount} assets`);
    } 
    // Neither location found
    else {
        console.error('❌ ERROR: manifest.json not found in either location!');
        console.error('   Expected: public/build/manifest.json');
        console.error('   Or: public/build/.vite/manifest.json');
        console.error('   Make sure vite build completed successfully.');
        process.exit(1);
    }
} catch (error) {
    console.error('❌ ERROR: Could not read/copy manifest.json:', error.message);
    process.exit(1);
}

console.log('✨ Cleanup complete! Ready for production deployment.');


