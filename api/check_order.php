<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$query = trim($_GET['query'] ?? $_POST['query'] ?? '');

if (empty($query)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập Mã đơn hàng hoặc Số điện thoại / Zalo để tra cứu']);
    exit;
}

$db = getDB();
if (!$db) {
    echo json_encode(['success' => false, 'message' => 'Hệ thống cơ sở dữ liệu chưa kết nối. Vui lòng liên hệ Admin qua Zalo: ' . getSetting('zalo_contact')]);
    exit;
}

try {
    $stmt = $db->prepare("
        SELECT order_code, plan_name, customer_name, customer_phone, amount, payment_status, license_key, created_at
        FROM orders
        WHERE order_code = ? OR customer_phone = ?
        ORDER BY id DESC
        LIMIT 5
    ");
    $stmt->execute([$query, $query]);
    $orders = $stmt->fetchAll();

    if (!$orders || count($orders) === 0) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng nào khớp với mã hoặc số điện thoại: ' . htmlspecialchars($query)]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'orders'  => array_map(function($o) {
            $o['amount_format'] = formatCurrency($o['amount']);
            return $o;
        }, $orders)
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn dữ liệu: ' . $e->getMessage()]);
}
