<?php
/**
 * Bayyanoor Deployment Health Check
 * Endpoint: /api/health
 *
 * Verifies: frontend theme, LMS plugin, WP core, config, API entry,
 *           login, LMS classes, and template integrity.
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

$checks    = [];
$allPassed = true;

// 1. Frontend: Check that theme files exist.
$themeDir = __DIR__ . '/../wp-content/themes/bayyanoor-theme';
$checks['frontend'] = [
    'status' => is_dir($themeDir) && file_exists("$themeDir/style.css"),
    'detail' => is_dir($themeDir) ? 'Theme directory found' : 'MISSING theme directory',
];

// 2. Plugin: Check bayyanoor-lms exists.
$pluginDir = __DIR__ . '/../wp-content/plugins/bayyanoor-lms';
$checks['plugin_lms'] = [
    'status' => is_dir($pluginDir) && file_exists("$pluginDir/bayyanoor-lms.php"),
    'detail' => is_dir($pluginDir) ? 'LMS plugin found' : 'MISSING LMS plugin',
];

// 3. WordPress Core: Check wp-includes exists.
$wpIncludes = __DIR__ . '/../wp-includes';
$checks['wp_core'] = [
    'status' => is_dir($wpIncludes) && file_exists("$wpIncludes/version.php"),
    'detail' => is_dir($wpIncludes) ? 'WordPress core found' : 'MISSING wp-includes',
];

// 4. Config: Check wp-config.php exists and is readable.
$wpConfig = __DIR__ . '/../wp-config.php';
$checks['config'] = [
    'status' => file_exists($wpConfig) && is_readable($wpConfig),
    'detail' => file_exists($wpConfig) ? 'Config file accessible' : 'MISSING wp-config.php',
];

// 5. API Entry: Check api/index.php exists.
$checks['api_entry'] = [
    'status' => file_exists(__DIR__ . '/index.php'),
    'detail' => file_exists(__DIR__ . '/index.php') ? 'API entry point exists' : 'MISSING api/index.php',
];

// 6. Vercel Config: Check vercel.json exists.
$checks['vercel_config'] = [
    'status' => file_exists(__DIR__ . '/../vercel.json'),
    'detail' => file_exists(__DIR__ . '/../vercel.json') ? 'Vercel config exists' : 'MISSING vercel.json',
];

// 7. Login endpoint: Check wp-login.php exists.
$checks['login'] = [
    'status' => file_exists(__DIR__ . '/../wp-login.php'),
    'detail' => file_exists(__DIR__ . '/../wp-login.php') ? 'Login endpoint available' : 'MISSING wp-login.php',
];

// 8. LMS Class files integrity.
$requiredClasses = [
    'class-bayyanoor-db.php',
    'class-bayyanoor-cpt.php',
    'class-bayyanoor-setup.php',
    'class-bayyanoor-streaks.php',
    'class-bayyanoor-mastery.php',
    'class-bayyanoor-hifz.php',
    'class-bayyanoor-frontend.php',
    'class-bayyanoor-pages.php',
    'class-bayyanoor-auth.php',
    'class-bayyanoor-ai.php',
    'class-bayyanoor-gamification.php',
    'class-bayyanoor-ajax-api.php',
    'class-bayyanoor-admin.php',
];
$missingClasses = [];
foreach ($requiredClasses as $class) {
    if (!file_exists("$pluginDir/includes/$class")) {
        $missingClasses[] = $class;
    }
}
$checks['lms_classes'] = [
    'status' => empty($missingClasses),
    'detail' => empty($missingClasses)
        ? 'All ' . count($requiredClasses) . ' LMS classes present'
        : 'MISSING: ' . implode(', ', $missingClasses),
];

// 9. Template files.
$checks['templates'] = [
    'status' => file_exists("$pluginDir/templates/app-shell.php"),
    'detail' => file_exists("$pluginDir/templates/app-shell.php")
        ? 'App shell template found'
        : 'MISSING app-shell.php',
];

// Aggregate results.
foreach ($checks as $check) {
    if (!$check['status']) {
        $allPassed = false;
    }
}

http_response_code($allPassed ? 200 : 500);

echo json_encode([
    'healthy'   => $allPassed,
    'timestamp' => date('c'),
    'version'   => '2.0.0',
    'checks'    => $checks,
], JSON_PRETTY_PRINT);
