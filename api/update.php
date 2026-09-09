<?php
/**
 * AutoClash Remote Update File Streamer
 * Cho phép Updater.exe tải gói cập nhật .zip an toàn, vượt qua giới hạn 10MB của InfinityFree
 */

$filename = basename($_GET['file'] ?? 'update_v2.5.0.zip');

// Kiểm tra nếu có các file parts
$partPrefix = __DIR__ . '/../update_v2.5.0.part';
$parts = [];
for ($i = 1; $i <= 10; $i++) {
    $p = $partPrefix . $i;
    if (file_exists($p)) {
        $parts[] = $p;
    } else {
        break;
    }
}

if (!empty($parts)) {
    // Tính tổng dung lượng các parts
    $totalSize = 0;
    foreach ($parts as $p) {
        $totalSize += filesize($p);
    }

    if (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $totalSize);

    // Stream liên tục từng part
    foreach ($parts as $p) {
        $handle = fopen($p, 'rb');
        if ($handle) {
            while (!feof($handle)) {
                echo fread($handle, 64 * 1024);
                flush();
            }
            fclose($handle);
        }
    }
    exit;
}

// Fallback: Tìm file đơn lẻ
$candidates = [
    __DIR__ . '/' . $filename,
    __DIR__ . '/../' . $filename,
    __DIR__ . '/../../' . $filename
];

$targetFile = null;
foreach ($candidates as $c) {
    if (file_exists($c)) {
        $targetFile = $c;
        break;
    }
}

if (!$targetFile) {
    http_response_code(404);
    die(json_encode(['error' => 'Update package not found'], JSON_UNESCAPED_UNICODE));
}

if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Description: File Transfer');
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($targetFile));

$handle = fopen($targetFile, 'rb');
while (!feof($handle)) {
    echo fread($handle, 64 * 1024);
    flush();
}
fclose($handle);
exit;
