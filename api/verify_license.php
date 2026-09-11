<?php
// ========================================================
// AutoClash / AutoCOCVN - API Kiểm Tra & Xác Thực Bản Quyền
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

$licenseKey = strtoupper(trim($inputData['license_key'] ?? $inputData['key'] ?? $_POST['license_key'] ?? $_POST['key'] ?? $_GET['key'] ?? ''));
$hwid       = strtoupper(trim($inputData['hwid'] ?? $inputData['device_id'] ?? $_POST['hwid'] ?? $_POST['device_id'] ?? $_GET['hwid'] ?? ''));

$db = getDB();
if (!$db) {
    echo json_encode([
        'valid'   => false,
        'success' => false,
        'message' => 'Lỗi kết nối cơ sở dữ liệu máy chủ!'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // 1. Nếu có HWID nhưng để trống License Key: Tự động tra cứu key đã kích hoạt qua web cho máy này
    if (empty($licenseKey) && !empty($hwid)) {
        $stmt = $db->prepare("
            SELECT k.*, p.name AS plan_name, p.duration_days 
            FROM `keys` k 
            LEFT JOIN `plans` p ON k.plan_code = p.plan_code 
            WHERE UPPER(TRIM(k.user_device_id)) = UPPER(TRIM(?)) 
              AND k.status = 'used' 
              AND (k.expires_at IS NULL OR k.expires_at > NOW())
            ORDER BY k.id DESC 
            LIMIT 1
        ");
        $stmt->execute([$hwid]);
        $boundKey = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($boundKey) {
            echo json_encode([
                'valid'       => true,
                'success'     => true,
                'status'      => 'active',
                'license_key' => $boundKey['license_key'],
                'plan_name'   => $boundKey['plan_name'] ?? 'Bản Quyền Chính Thức',
                'expires_at'  => $boundKey['expires_at'] ?? '2099-12-31 23:59:59',
                'hwid'        => $hwid,
                'message'     => 'Nhận diện thành công bản quyền đã kích hoạt qua Web!'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            echo json_encode([
                'valid'   => false,
                'success' => false,
                'message' => 'Chưa có bản quyền nào được kích hoạt cho máy này! Vui lòng nhập License Key.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // 2. Kiểm tra thông tin bắt buộc
    if (empty($licenseKey)) {
        echo json_encode([
            'valid'   => false,
            'success' => false,
            'message' => 'Vui lòng cung cấp mã License Key!'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $db->prepare("
        SELECT k.*, p.name AS plan_name, p.duration_days 
        FROM `keys` k 
        LEFT JOIN `plans` p ON k.plan_code = p.plan_code 
        WHERE UPPER(TRIM(k.license_key)) = UPPER(TRIM(?))
        LIMIT 1
    ");
    $stmt->execute([$licenseKey]);
    $keyRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$keyRow) {
        echo json_encode([
            'valid'   => false,
            'success' => false,
            'message' => 'Mã License Key không tồn tại trên hệ thống!'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $planName = $keyRow['plan_name'] ?? 'Bản Quyền Chính Thức';

    // 3. Nếu Key chưa sử dụng và có HWID -> Tự động kích hoạt & gán máy
    if ($keyRow['status'] === 'available' && !empty($hwid)) {
        // KIỂM TRA MÃ MÁY (HWID) ĐÃ CÓ KEY NÀO ĐANG HOẠT ĐỘNG CHƯA
        $checkHwidStmt = $db->prepare("
            SELECT id, license_key, plan_code, expires_at 
            FROM `keys` 
            WHERE UPPER(TRIM(user_device_id)) = UPPER(TRIM(?)) 
              AND id != ? 
              AND status = 'used'
              AND (expires_at IS NULL OR expires_at > NOW())
            ORDER BY id DESC
        ");
        $checkHwidStmt->execute([$hwid, $keyRow['id']]);
        $existingActiveKeys = $checkHwidStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($existingActiveKeys)) {
            $currentKey = $existingActiveKeys[0];
            if ($currentKey['plan_code'] === 'LIFETIME' && $keyRow['plan_code'] !== 'LIFETIME') {
                echo json_encode([
                    'valid'       => true,
                    'success'     => true,
                    'status'      => 'active',
                    'license_key' => $currentKey['license_key'],
                    'plan_name'   => 'Gói Vĩnh Viễn VIP',
                    'expires_at'  => $currentKey['expires_at'] ?? '2099-12-31 23:59:59',
                    'hwid'        => $hwid,
                    'message'     => "Máy tính này đã kích hoạt bản quyền Vĩnh Viễn VIP! Không cần gán thêm key mới."
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            foreach ($existingActiveKeys as $oldK) {
                $isExpired = (!empty($oldK['expires_at']) && strtotime($oldK['expires_at']) < time());
                $newOldStatus = $isExpired ? 'expired' : 'available';
                $db->prepare("UPDATE `keys` SET user_device_id = NULL, status = ? WHERE id = ?")->execute([$newOldStatus, $oldK['id']]);
            }
        }

        $durationDays = isset($keyRow['duration_days']) ? intval($keyRow['duration_days']) : 30;
        $expiresAt = ($durationDays > 0) ? date('Y-m-d H:i:s', strtotime("+{$durationDays} days")) : '2099-12-31 23:59:59';
        
        $db->prepare("UPDATE `keys` SET status = 'used', user_device_id = ?, used_at = NOW(), expires_at = ? WHERE id = ?")
           ->execute([$hwid, $expiresAt, $keyRow['id']]);

        echo json_encode([
            'valid'       => true,
            'success'     => true,
            'status'      => 'active',
            'license_key' => $keyRow['license_key'],
            'plan_name'   => $planName,
            'expires_at'  => $expiresAt,
            'hwid'        => $hwid,
            'message'     => 'Kích hoạt bản quyền thành công! Đã gắn cố định vào máy tính này.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 4. Nếu Key đã sử dụng: Kiểm tra trùng khớp HWID (1 Key = 1 Máy)
    if ($keyRow['status'] === 'used') {
        if (!empty($hwid) && strtoupper(trim($keyRow['user_device_id'])) !== strtoupper(trim($hwid))) {
            echo json_encode([
                'valid'   => false,
                'success' => false,
                'status'  => 'device_mismatch',
                'message' => 'Key này đã được kích hoạt trên máy khác! Quy định: Mỗi 1 Key chỉ dùng cho 01 máy tính duy nhất.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Kiểm tra hạn sử dụng
        if (!empty($keyRow['expires_at']) && strtotime($keyRow['expires_at']) < time()) {
            echo json_encode([
                'valid'      => false,
                'success'    => false,
                'status'     => 'expired',
                'expires_at' => $keyRow['expires_at'],
                'message'    => 'Bản quyền đã hết hạn sử dụng vào ngày ' . date('d/m/Y H:i', strtotime($keyRow['expires_at']))
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'valid'       => true,
            'success'     => true,
            'status'      => 'active',
            'license_key' => $keyRow['license_key'],
            'plan_name'   => $planName,
            'expires_at'  => $keyRow['expires_at'] ?? '2099-12-31 23:59:59',
            'hwid'        => $keyRow['user_device_id'],
            'message'     => 'Bản quyền hợp lệ!'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 5. Nếu Key đã hết hạn
    if ($keyRow['status'] === 'expired') {
        echo json_encode([
            'valid'   => false,
            'success' => false,
            'status'  => 'expired',
            'message' => 'Bản quyền đã hết hạn sử dụng!'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'valid'   => false,
        'success' => false,
        'message' => 'Key chưa được kích hoạt với máy tính nào! Vui lòng kích hoạt qua Web trước.'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'valid'   => false,
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
