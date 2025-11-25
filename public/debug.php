<?php
// Debug file to check what's being served
session_start();

$file = __DIR__ . '/../app/views/auth/verifyEmail.php';
$content = file_get_contents($file);

// Search for the cooldown value
if (preg_match('/let\s+cooldown\s*=\s*(\d+)/', $content, $matches)) {
    echo "Cooldown value found: " . $matches[1] . " segundos\n";
    echo "File last modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
    echo "File size: " . filesize($file) . " bytes\n";
} else {
    echo "ERROR: Cooldown value not found in file!\n";
}

echo "\n\nSearching for '90' in file:\n";
if (strpos($content, '90') !== false) {
    echo "WARNING: Found '90' in verifyEmail.php\n";
    // Find all occurrences
    preg_match_all('/.*90.*/', $content, $matches);
    foreach ($matches[0] as $line) {
        echo "  " . trim($line) . "\n";
    }
} else {
    echo "OK: No '90' found in verifyEmail.php\n";
}

echo "\n\nSearching for 'recovery.js':\n";
if (strpos($content, 'recovery.js') !== false) {
    echo "WARNING: recovery.js is still being loaded!\n";
} else {
    echo "OK: recovery.js is not loaded\n";
}

?>
