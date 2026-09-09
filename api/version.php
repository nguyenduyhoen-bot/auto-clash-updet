<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/../config/db.php';

$appVersion = getSetting('app_version', 'v2.5.1');
$cleanVersion = ltrim($appVersion, 'v');

echo json_encode([
    'version'       => $cleanVersion,
    'release_date'  => date('d/m/Y'),
    'download_url'  => 'https://github.com/hoanbaby/Auto-clash-updet/releases/download/v2.5.1/AutoClash_Setup.exe',
    'setup_url'     => 'https://github.com/hoanbaby/Auto-clash-updet/releases/download/v2.5.1/AutoClash_Setup.exe',
    'changelog'     => [
        "- Tích hợp tính năng tự động thông báo khi có bản cập nhật mới",
        "- Bảo mật toàn diện đường dẫn cập nhật từ xa máy chủ AutoClash Cloud",
        "- Cải tiến nhận diện AI OCR Tesseract và tối ưu hóa kịch bản auto farm",
        "- Tối ưu hóa hiệu năng và kết nối giả lập LDPlayer, BlueStacks, Nox"
    ],
    'checksum_sha256' => '',
    'force_update'  => false
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
