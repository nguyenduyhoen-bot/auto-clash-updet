<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức yêu cầu không hợp lệ']);
    exit;
}

$planCode = trim($_POST['plan_code'] ?? '');
$customerName = trim($_POST['customer_name'] ?? '');
$customerPhone = trim($_POST['customer_phone'] ?? '');
$customerEmail = trim($_POST['customer_email'] ?? '');
$customerNote = trim($_POST['customer_note'] ?? '');

if (empty($planCode) || empty($customerName) || empty($customerPhone)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ Họ tên, Số điện thoại / Zalo và chọn Gói key']);
    exit;
}

// Lấy thông tin gói
$plans = getPlans();
$selectedPlan = null;
foreach ($plans as $p) {
    if ($p['plan_code'] === $planCode) {
        $selectedPlan = $p;
        break;
    }
}

if (!$selectedPlan) {
    echo json_encode(['success' => false, 'message' => 'Gói key đã chọn không tồn tại']);
    exit;
}

$orderCode = generateOrderCode();
$amount = (int)$selectedPlan['price'];
$planName = $selectedPlan['name'];

// Cấu hình ngân hàng từ Settings
$bankId = getSetting('bank_id', 'MBBank');
$bankAccount = getSetting('bank_account', '0338996239');
$bankOwner = getSetting('bank_owner', 'NGUYEN DUY THIEN');

// Tạo link QR VietQR
$qrUrl = "https://img.vietqr.io/image/{$bankId}-{$bankAccount}-compact2.png?amount={$amount}&addInfo=" . urlencode($orderCode) . "&accountName=" . urlencode($bankOwner);

// Lưu vào Database
$db = getDB();
$savedToDb = false;

if ($db) {
    try {
        $stmt = $db->prepare("
            INSERT INTO orders (order_code, plan_code, plan_name, customer_name, customer_phone, customer_email, customer_note, amount, payment_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $orderCode,
            $planCode,
            $planName,
            $customerName,
            $customerPhone,
            $customerEmail,
            $customerNote,
            $amount
        ]);
        $savedToDb = true;
    } catch (Exception $e) {
        error_log("Failed to insert order: " . $e->getMessage());
    }
}

echo json_encode([
    'success'       => true,
    'order_code'    => $orderCode,
    'plan_name'     => $planName,
    'amount'        => $amount,
    'amount_format' => formatCurrency($amount),
    'bank_id'       => $bankId,
    'bank_account'  => $bankAccount,
    'bank_owner'    => $bankOwner,
    'qr_url'        => $qrUrl,
    'saved_to_db'   => $savedToDb,
    'message'       => 'Đã tạo đơn hàng thành công! Vui lòng quét mã VietQR hoặc chuyển khoản đúng nội dung bên dưới.'
]);
