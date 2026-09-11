<?php
/**
 * AutoClash Secure Download Streamer
 * Tải trực tiếp bộ cài AutoClash từ GitHub Releases CDN tốc độ cao
 */

$format = strtolower($_GET['format'] ?? 'zip');

$versionJsonPath = __DIR__ . '/version.json';
$setupUrl = 'https://github.com/hoanbaby/Auto-clash-updet/releases/download/v2.5.5/AutoClash_Setup.exe';
$zipUrl = 'https://github.com/hoanbaby/Auto-clash-updet/releases/download/v2.5.5/AutoClash_v2.5.5.zip';

if (file_exists($versionJsonPath)) {
    $vData = json_decode(file_get_contents($versionJsonPath), true);
    if (!empty($vData['setup_download_url'])) {
        $setupUrl = $vData['setup_download_url'];
    }
    if (!empty($vData['full_zip_download_url'])) {
        $zipUrl = $vData['full_zip_download_url'];
    } elseif (!empty($vData['download_url'])) {
        $zipUrl = $vData['download_url'];
    }
}

if ($format === 'exe') {
    header('Location: ' . $setupUrl);
    exit;
}

// Mặc định tải Portable .ZIP (khuyên dùng, không bị trình duyệt chặn)
header('Location: ' . $zipUrl);
exit;

