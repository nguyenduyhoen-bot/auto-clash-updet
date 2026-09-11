<?php
// Test download script
$file = __DIR__ . '/../AutoClash_Setup.exe';
if (file_exists($file)) {
    echo "File exists! Size: " . filesize($file);
} else {
    echo "File not found: " . $file;
}
