<?php
/**
 * AutoClash Secure Download Streamer
 * Tải trực tiếp bộ cài AutoClash từ GitHub Releases CDN tốc độ cao
 */

$format = strtolower($_GET['format'] ?? 'exe');

if ($format === 'zip') {
    // Tải bản Portable .ZIP
    header('Location: https://github.com/hoanbaby/Auto-clash-updet/releases/download/v2.5.1/AutoClash_v2.5.1.zip');
    exit;
}

// Mặc định tải bộ cài đặt AutoClash_Setup.exe
header('Location: https://github.com/hoanbaby/Auto-clash-updet/releases/download/v2.5.1/AutoClash_Setup.exe');
exit;
