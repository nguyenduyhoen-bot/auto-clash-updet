<?php
// ========================================================
// AutoClash / AutoCOCVN - API Kiểm Tra Bản Quyền Nhanh
// ========================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/db.php';

$inputRaw = file_get_contents('php://input');
$inputData = json_decode($inputRaw, true);

$licenseKey = trim($inputData['license_key'] ?? $inputData['key'] ?? $_POST['license_key'] ?? $_POST['key'] ?? $_GET['key'] ?? '');
$hwid       = trim($inputData['hwid'] ?? $inputData['device_id'] ?? $_POST['hwid'] ?? $_POST['device_id'] ?? $_GET['hwid'] ?? '');

if (empty($licenseKey) || empty($hwid)) {
    echo json_encode([
        'valid'   => false,
        'message' => 'Thiếu thông tin License Key hoặc HWID!'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$db = getDB();
if (!$db) {
    echo json_encode([
        'valid'   => false,
        'message' => 'Lỗi kết nối cơ sở dữ liệu!'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $db->prepare("
        SELECT k.*, p.name AS plan_name, p.duration_days 
        FROM `keys` k 
        LEFT JOIN `plans` p ON k.plan_code = p.plan_code 
        WHERE UPPER(k.license_key) = UPPER(?) AND k.user_device_id = ?
        LIMIT 1
    ");
    $stmt->execute([$licenseKey, $hwid]);
    $keyRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$keyRow) {
        echo json_encode([
            'valid'   => false,
            'message' => 'Bản quyền không hợp lệ hoặc chưa được đăng ký cho thiết bị này!'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($keyRow['status'] === 'expired' || (!empty($keyRow['expires_at']) && strtotime($keyRow['expires_at']) < time())) {
        echo json_encode([
            'valid'      => false,
            'status'     => 'expired',
            'expires_at' => $keyRow['expires_at'],
            'message'    => 'Bản quyền đã hết hạn sử dụng!'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'valid'       => true,
        'status'      => 'active',
        'license_key' => $keyRow['license_key'],
        'plan_name'   => $keyRow['plan_name'] ?? 'Bản Quyền Chính Thức',
        'expires_at'  => $keyRow['expires_at'] ?? '2099-12-31 23:59:59',
        'message'     => 'Bản quyền hợp lệ!'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'valid'   => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
