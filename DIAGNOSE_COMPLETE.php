<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$report = [
    'timestamp' => date('Y-m-d H:i:s'),
    'diagnostics' => [],
    'errors' => [],
    'warnings' => [],
    'critical' => [],
    'suggestions' => []
];

$basePath = __DIR__;

function check_file_exists($path, $label) {
    global $report;
    $fullPath = $path;
    if (file_exists($fullPath)) {
        $report['diagnostics'][] = "✓ {$label}: EXISTS ({$fullPath})";
        return true;
    } else {
        $report['critical'][] = "✗ {$label}: MISSING ({$fullPath})";
        return false;
    }
}

function check_file_readable($path, $label) {
    global $report;
    if (file_exists($path) && is_readable($path)) {
        $report['diagnostics'][] = "✓ {$label}: READABLE";
        return true;
    } else {
        $report['errors'][] = "✗ {$label}: NOT READABLE";
        return false;
    }
}

function check_dir_exists($path, $label) {
    global $report;
    if (is_dir($path)) {
        $report['diagnostics'][] = "✓ {$label}: DIR EXISTS";
        return true;
    } else {
        $report['critical'][] = "✗ {$label}: DIR MISSING ({$path})";
        return false;
    }
}

function check_php_syntax($file, $label) {
    global $report;
    if (!file_exists($file)) {
        $report['errors'][] = "✗ {$label}: FILE NOT FOUND";
        return false;
    }
    $output = shell_exec("php -l " . escapeshellarg($file) . " 2>&1");
    if (strpos($output, 'No syntax errors') !== false) {
        $report['diagnostics'][] = "✓ {$label}: SYNTAX OK";
        return true;
    } else {
        $report['errors'][] = "✗ {$label}: SYNTAX ERROR\n$output";
        return false;
    }
}

function check_file_contains($file, $pattern, $label) {
    global $report;
    if (!file_exists($file)) {
        $report['errors'][] = "✗ {$label}: FILE NOT FOUND";
        return false;
    }
    $content = file_get_contents($file);
    if (strpos($content, $pattern) !== false) {
        $report['diagnostics'][] = "✓ {$label}: PATTERN FOUND";
        return true;
    } else {
        $report['warnings'][] = "⚠ {$label}: PATTERN NOT FOUND";
        return false;
    }
}

echo "=== ANALYTEE DIAGNOSTIC REPORT ===\n";
echo "Start time: " . date('Y-m-d H:i:s') . "\n\n";

echo "[1] CHECKING ROOT FILES\n";
check_file_exists($basePath . '/index.html', 'ROOT: index.html');
check_file_exists($basePath . '/.htaccess', 'ROOT: .htaccess');
check_file_exists($basePath . '/.env', 'ROOT: .env');
check_file_exists($basePath . '/faq_feed.php', 'ROOT: faq_feed.php');

echo "\n[2] CHECKING DASHBOARD CORE\n";
check_dir_exists($basePath . '/dashboard', 'DASHBOARD: /dashboard');
check_file_exists($basePath . '/dashboard/index.php', 'DASHBOARD: index.php');
check_file_exists($basePath . '/dashboard/bootstrap.php', 'DASHBOARD: bootstrap.php');
check_file_exists($basePath . '/dashboard/auth.php', 'DASHBOARD: auth.php');
check_file_exists($basePath . '/dashboard/db.php', 'DASHBOARD: db.php');
check_file_exists($basePath . '/dashboard/env.php', 'DASHBOARD: env.php');
check_file_exists($basePath . '/dashboard/security.php', 'DASHBOARD: security.php');
check_file_exists($basePath . '/dashboard/security-config.php', 'DASHBOARD: security-config.php');
check_file_exists($basePath . '/dashboard/layout.php', 'DASHBOARD: layout.php');

echo "\n[3] CHECKING CORE CLASSES\n";
check_file_exists($basePath . '/dashboard/core/init.php', 'CORE: init.php');
check_file_exists($basePath . '/dashboard/core/LoggerManager.php', 'CORE: LoggerManager.php');
check_file_exists($basePath . '/dashboard/core/CSRFManager.php', 'CORE: CSRFManager.php');
check_file_exists($basePath . '/dashboard/core/Sanitizer.php', 'CORE: Sanitizer.php');
check_file_exists($basePath . '/dashboard/core/AuditLog.php', 'CORE: AuditLog.php');

echo "\n[4] CHECKING FAQ MODULE\n";
check_dir_exists($basePath . '/dashboard/faq', 'FAQ: /faq');
check_file_exists($basePath . '/dashboard/faq/index.php', 'FAQ: index.php');
check_file_exists($basePath . '/dashboard/faq/create.php', 'FAQ: create.php');
check_file_exists($basePath . '/dashboard/faq/edit.php', 'FAQ: edit.php');
check_file_exists($basePath . '/dashboard/faq/store.php', 'FAQ: store.php');
check_file_exists($basePath . '/dashboard/faq/update.php', 'FAQ: update.php');
check_file_exists($basePath . '/dashboard/faq/delete.php', 'FAQ: delete.php');
check_file_exists($basePath . '/dashboard/faq/models/FAQModel.php', 'FAQ: FAQModel.php');

echo "\n[5] CHECKING BLOG MODULE\n";
check_dir_exists($basePath . '/dashboard/blog', 'BLOG: /blog');
check_file_exists($basePath . '/blog/index.php', 'BLOG: /blog/index.php (public)');
check_file_exists($basePath . '/dashboard/blog/index.php', 'BLOG: /dashboard/blog/index.php (admin)');
check_file_exists($basePath . '/dashboard/blog/create.php', 'BLOG: create.php');
check_file_exists($basePath . '/dashboard/blog/models/PostModel.php', 'BLOG: PostModel.php');

echo "\n[6] CHECKING DIRECTORIES\n";
check_dir_exists($basePath . '/dashboard/logs', 'LOGS: /dashboard/logs');
check_dir_exists($basePath . '/dashboard/blog/uploads', 'BLOG: /dashboard/blog/uploads');

echo "\n[7] CHECKING PHP SYNTAX\n";
check_php_syntax($basePath . '/dashboard/bootstrap.php', 'bootstrap.php');
check_php_syntax($basePath . '/dashboard/auth.php', 'auth.php');
check_php_syntax($basePath . '/dashboard/db.php', 'db.php');
check_php_syntax($basePath . '/dashboard/core/LoggerManager.php', 'LoggerManager.php');
check_php_syntax($basePath . '/dashboard/faq/store.php', 'faq/store.php');
check_php_syntax($basePath . '/dashboard/faq/update.php', 'faq/update.php');

echo "\n[8] CHECKING .ENV FILE\n";
if (file_exists($basePath . '/.env')) {
    $envContent = file_get_contents($basePath . '/.env');
    $envLines = array_filter(array_map('trim', explode("\n", $envContent)));
    $report['diagnostics'][] = ".env file exists with " . count($envLines) . " non-empty lines";
    
    $requiredVars = ['DB_HOST', 'DB_NAME', 'DB_USER', 'ADMIN_USER', 'ADMIN_PASS_HASH'];
    foreach ($requiredVars as $var) {
        if (strpos($envContent, $var) !== false) {
            $report['diagnostics'][] = "✓ .env contains: $var";
        } else {
            $report['warnings'][] = "⚠ .env missing: $var";
        }
    }
} else {
    $report['critical'][] = "✗ .env file NOT FOUND";
}

echo "\n[9] CHECKING DATABASE CONNECTION\n";
if (file_exists($basePath . '/.env')) {
    $envPath = $basePath . '/.env';
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $envVars = [];
    foreach ($lines as $line) {
        if (trim($line) === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($k, $v) = explode('=', $line, 2);
            $envVars[trim($k)] = trim($v);
        }
    }
    
    if (isset($envVars['DB_HOST'])) {
        $report['diagnostics'][] = "DB_HOST: " . $envVars['DB_HOST'];
        $report['diagnostics'][] = "DB_NAME: " . ($envVars['DB_NAME'] ?? 'NOT SET');
        $report['diagnostics'][] = "DB_USER: " . ($envVars['DB_USER'] ?? 'NOT SET');
    }
}

echo "\n[10] CHECKING FOR COMMON ERRORS\n";
echo "Scanning for undefined function calls...\n";

$criticalFunctions = [
    'dashboard/faq/store.php' => ['Sanitizer::sanitizeInteger', 'CSRFManager::validateRequest'],
    'dashboard/faq/update.php' => ['Sanitizer::sanitizeInteger', 'CSRFManager::validateRequest'],
];

foreach ($criticalFunctions as $file => $functions) {
    $fullFile = $basePath . '/' . $file;
    if (file_exists($fullFile)) {
        $content = file_get_contents($fullFile);
        foreach ($functions as $func) {
            if (strpos($content, $func) !== false) {
                $report['diagnostics'][] = "✓ $file uses $func";
            }
        }
    }
}

echo "\n=== REPORT GENERATED ===\n\n";

echo "CRITICAL ISSUES:\n";
if (empty($report['critical'])) {
    echo "  (none)\n";
} else {
    foreach ($report['critical'] as $item) {
        echo "  $item\n";
    }
}

echo "\nERRORS:\n";
if (empty($report['errors'])) {
    echo "  (none)\n";
} else {
    foreach ($report['errors'] as $item) {
        echo "  $item\n";
    }
}

echo "\nWARNINGS:\n";
if (empty($report['warnings'])) {
    echo "  (none)\n";
} else {
    foreach ($report['warnings'] as $item) {
        echo "  $item\n";
    }
}

echo "\nSUMMARY:\n";
echo "  Critical: " . count($report['critical']) . "\n";
echo "  Errors: " . count($report['errors']) . "\n";
echo "  Warnings: " . count($report['warnings']) . "\n";
echo "  Checks passed: " . count($report['diagnostics']) . "\n";

file_put_contents($basePath . '/DIAGNOSTIC_REPORT.txt', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "\nFull report saved to: DIAGNOSTIC_REPORT.txt\n";
?>
