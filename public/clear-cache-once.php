<?php
/**
 * ONE-TIME CACHE CLEAR SCRIPT
 * 
 * INSTRUCTIONS:
 * 1. Upload this file to your public/ directory
 * 2. Set a password below (change 'your-password-here')
 * 3. Visit: https://seminairexpo.com/admin/public/clear-cache-once.php?token=your-password-here
 * 4. DELETE THIS FILE IMMEDIATELY AFTER USE!
 * 
 * SECURITY: This script will only work with the correct token.
 */

// SET YOUR PASSWORD HERE (change this!)
// Example: $SECRET_TOKEN = 'mySecretPassword123';
// Choose any strong password you want
$SECRET_TOKEN = 'change-this-password-before-uploading';

// Get token from URL
$token = $_GET['token'] ?? '';

// Security check
if ($token !== $SECRET_TOKEN) {
    http_response_code(403);
    die('❌ Invalid token. Check the token in the URL matches the one in the file.');
}

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Clear caches
$results = [];

try {
    Artisan::call('config:clear');
    $results[] = '✅ Config cache cleared';
} catch (\Exception $e) {
    $results[] = '⚠️ Config cache: ' . $e->getMessage();
}

try {
    Artisan::call('cache:clear');
    $results[] = '✅ Application cache cleared';
} catch (\Exception $e) {
    $results[] = '⚠️ Application cache: ' . $e->getMessage();
}

try {
    Artisan::call('route:clear');
    $results[] = '✅ Route cache cleared';
} catch (\Exception $e) {
    $results[] = '⚠️ Route cache: ' . $e->getMessage();
}

try {
    Artisan::call('view:clear');
    $results[] = '✅ View cache cleared';
} catch (\Exception $e) {
    $results[] = '⚠️ View cache: ' . $e->getMessage();
}

try {
    Artisan::call('clear-compiled');
    $results[] = '✅ Compiled classes cleared';
} catch (\Exception $e) {
    $results[] = '⚠️ Compiled classes: ' . $e->getMessage();
}

// Display results
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cache Cleared</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            padding: 5px 0;
        }
    </style>
</head>
<body>
    <div class="success">
        <h2>✅ Cache Cleared Successfully!</h2>
        <ul>
            <?php foreach ($results as $result): ?>
                <li><?php echo htmlspecialchars($result); ?></li>
            <?php endforeach; ?>
        </ul>
        <p><strong>Environment:</strong> <?php echo config('app.env'); ?></p>
        <p><strong>Debug Mode:</strong> <?php echo config('app.debug') ? 'Enabled' : 'Disabled'; ?></p>
    </div>
    
    <div class="warning">
        <h3>⚠️ SECURITY WARNING</h3>
        <p><strong>DELETE THIS FILE NOW!</strong></p>
        <p>This script should only be used once. Delete <code>public/clear-cache-once.php</code> immediately to prevent unauthorized access.</p>
    </div>
    
    <div class="info">
        <h3>ℹ️ Next Steps</h3>
        <ol>
            <li>Check if your site is working correctly</li>
            <li>Delete this file: <code>public/clear-cache-once.php</code></li>
            <li>Verify your <code>.env</code> has <code>APP_ENV=production</code></li>
        </ol>
    </div>
</body>
</html>

