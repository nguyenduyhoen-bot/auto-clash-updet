<?php
// ========================================================
// AutoClash / AutoCOCVN - API Kích Hoạt Bản Quyền Trực Tuyến
// ========================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/db.php';

// Nhận dữ liệu từ JSON body hoặc Form POST
$inputRaw = file_get_contents('php://input');
$inputData = json_decode($inputRaw, true);

$licenseKey = trim($inputData['license_key'] ?? $inputData['key'] ?? $_POST['license_key'] ?? $_POST['key'] ?? $_GET['key'] ?? '');
$hwid       = trim($inputData['hwid'] ?? $inputData['device_id'] ?? $_POST['hwid'] ?? $_POST['device_id'] ?? $_GET['hwid'] ?? '');

if (empty($licenseKey)) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng cung cấp mã License Key!'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$db = getDB();
if (!$db) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi kết nối máy chủ dữ liệu, vui lòng thử lại sau!'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // 1. Tìm thông tin Key và Gói tương ứng
    $stmt = $db->prepare("
        SELECT k.*, p.name AS plan_name, p.duration_days 
        FROM `keys` k 
        LEFT JOIN `plans` p ON k.plan_code = p.plan_code 
        WHERE UPPER(k.license_key) = UPPER(?) 
        LIMIT 1
    ");
    $stmt->execute([$licenseKey]);
    $keyRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$keyRow) {
        echo json_encode([
            'success' => false,
            'message' => 'Mã License Key không tồn tại trên hệ thống! Vui lòng kiểm tra lại.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $planName     = $keyRow['plan_name'] ?? ($keyRow['plan_code'] === 'LIFETIME' ? 'Gói Vĩnh Viễn VIP' : 'Gói Tiêu Chuẩn');
    $durationDays = isset($keyRow['duration_days']) ? intval($keyRow['duration_days']) : 30;
    $secretKey    = 'AutoCOC_Sec_2026_!@#';

    $actionReq = trim($inputData['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '');

    // Nếu người dùng yêu cầu kích hoạt nhưng không nhập HWID
    if ($actionReq === 'activate' && empty($hwid)) {
        echo json_encode([
            'success' => false,
            'message' => 'Vui lòng nhập Mã Máy Tính (HWID)! Quy định: Mỗi 1 Key bản quyền chỉ được kích hoạt và gắn liền với duy nhất 01 Máy tính.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Nếu người dùng chỉ kiểm tra thông tin hoặc chưa nhập HWID
    if (empty($hwid)) {
        if ($keyRow['status'] === 'available') {
            echo json_encode([
                'success'       => true,
                'action'        => 'info',
                'status'        => 'available',
                'license_key'   => $keyRow['license_key'],
                'plan_name'     => $planName,
                'plan_code'     => $keyRow['plan_code'],
                'duration_days' => $durationDays,
                'message'       => 'Key hợp lệ và chưa kích hoạt! Mỗi 1 Key chỉ gắn với 1 Mã Máy (HWID), hãy nhập Mã máy từ AutoClash để tiến hành liên kết và kích hoạt.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } elseif ($keyRow['status'] === 'used') {
            $isExpired = (!empty($keyRow['expires_at']) && strtotime($keyRow['expires_at']) < time());
            echo json_encode([
                'success'        => true,
                'action'         => 'info',
                'status'         => $isExpired ? 'expired' : 'used',
                'license_key'    => $keyRow['license_key'],
                'plan_name'      => $planName,
                'user_device_id' => $keyRow['user_device_id'],
                'expires_at'     => $keyRow['expires_at'],
                'message'        => $isExpired 
                    ? 'Key đã hết hạn sử dụng vào ngày ' . date('d/m/Y H:i', strtotime($keyRow['expires_at'])) 
                    : 'Key này đã kích hoạt và gắn cố định với Mã Máy (HWID): ' . $keyRow['user_device_id'] . ' (Hạn dùng: ' . date('d/m/Y H:i', strtotime($keyRow['expires_at'])) . ')'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            echo json_encode([
                'success'     => false,
                'action'      => 'info',
                'status'      => 'expired',
                'license_key' => $keyRow['license_key'],
                'plan_name'   => $planName,
                'message'     => 'Key này đã hết hạn sử dụng! Vui lòng mua key mới trên website.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // 2. Trường hợp: Key mới sẵn sàng kích hoạt
    if ($keyRow['status'] === 'available') {
        if ($durationDays > 0) {
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$durationDays} days"));
        } else {
            // Gói Vĩnh Viễn
            $expiresAt = '2099-12-31 23:59:59';
        }

        // Cập nhật gán cố định mã máy HWID và kích hoạt key (Mỗi 1 Key gắn với 1 HWID)
        $updateStmt = $db->prepare("
            UPDATE `keys` 
            SET `status` = 'used', 
                `user_device_id` = ?, 
                `used_at` = NOW(), 
                `expires_at` = ? 
            WHERE `id` = ?
        ");
        $updateStmt->execute([$hwid, $expiresAt, $keyRow['id']]);

        // Tạo chữ ký bảo mật token
        $token = hash_hmac('sha256', "{$keyRow['license_key']}|{$hwid}|{$expiresAt}", $secretKey);

        echo json_encode([
            'success'       => true,
            'status'        => 'active',
            'message'       => 'Kích hoạt bản quyền thành công! Key đã được gắn cố định với máy tính này (1 Key / 1 Máy tính).',
            'license_key'   => $keyRow['license_key'],
            'plan_name'     => $planName,
            'plan_code'     => $keyRow['plan_code'],
            'duration_days' => $durationDays,
            'expires_at'    => $expiresAt,
            'hwid'          => $hwid,
            'token'         => $token,
            'binding_note'  => 'Mỗi 1 key gắn với 1 Mã Máy Tính (HWID)'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 3. Trường hợp: Key đã từng được kích hoạt trước đó
    if ($keyRow['status'] === 'used') {
        // Kiểm tra xem có đúng máy tính đã đăng ký không (1 Key = 1 HWID)
        if ($keyRow['user_device_id'] !== $hwid) {
            echo json_encode([
                'success' => false,
                'status'  => 'device_mismatch',
                'message' => 'Key này đã được kích hoạt và gắn cố định với một Mã Máy Tính khác! Quy định: Mỗi 1 Key chỉ sử dụng cho 01 máy tính duy nhất (không thể dùng chung máy khác).'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Kiểm tra thời hạn sử dụng
        if (!empty($keyRow['expires_at']) && strtotime($keyRow['expires_at']) < time()) {
            // Đã hết hạn -> Cập nhật trạng thái
            $db->prepare("UPDATE `keys` SET `status` = 'expired' WHERE `id` = ?")->execute([$keyRow['id']]);
            echo json_encode([
                'success'    => false,
                'status'     => 'expired',
                'message'    => 'Key bản quyền này đã hết hạn sử dụng vào ngày ' . date('d/m/Y', strtotime($keyRow['expires_at'])) . '. Vui lòng gia hạn thêm key mới!'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Còn hạn trên đúng máy này -> Tái xác nhận thành công
        $expiresAt = $keyRow['expires_at'] ?? '2099-12-31 23:59:59';
        $token = hash_hmac('sha256', "{$keyRow['license_key']}|{$hwid}|{$expiresAt}", $secretKey);

        echo json_encode([
            'success'       => true,
            'status'        => 'active',
            'message'       => 'Bản quyền hợp lệ! Đã xác thực thành công thiết bị.',
            'license_key'   => $keyRow['license_key'],
            'plan_name'     => $planName,
            'plan_code'     => $keyRow['plan_code'],
            'duration_days' => $durationDays,
            'expires_at'    => $expiresAt,
            'hwid'          => $hwid,
            'token'         => $token
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 4. Trường hợp: Key đã hết hạn
    if ($keyRow['status'] === 'expired') {
        echo json_encode([
            'success' => false,
            'status'  => 'expired',
            'message' => 'Key bản quyền này đã hết hạn sử dụng! Vui lòng mua key mới trên website.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi xử lý hệ thống: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
