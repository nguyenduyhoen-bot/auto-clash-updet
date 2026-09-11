<?php
require_once __DIR__ . '/auth.php';
checkAdminAuth();

$db = getDB();
$dbError = '';
$actionMsg = '';

// Tự động kiểm tra và khởi tạo Database nếu chưa có bảng
if ($db) {
    try {
        $check = $db->query("SHOW TABLES LIKE 'orders'")->fetch();
        if (!$check) {
            // Import schema
            $sqlFile = __DIR__ . '/../database.sql';
            if (file_exists($sqlFile)) {
                $sqlContent = file_get_contents($sqlFile);
                $db->exec($sqlContent);
                $actionMsg = 'Đã tự động khởi tạo các bảng cơ sở dữ liệu thành công!';
            }
        }
    } catch (Exception $e) {
        $dbError = 'Cơ sở dữ liệu chưa sẵn sàng: ' . $e->getMessage();
    }
} else {
    $dbError = 'Không thể kết nối đến MySQL. Hãy chắc chắn MySQL đang chạy trong XAMPP (Port 3306) và database "autoclash_db" đã được tạo.';
}

// Xử lý Action Duyệt đơn / Cấp key / Hủy đơn
if ($db && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $orderId = (int)($_POST['order_id'] ?? 0);

    if ($action === 'approve' && $orderId > 0) {
        // Lấy thông tin đơn
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if ($order) {
            // Tìm key có sẵn trong kho
            $stmtKey = $db->prepare("SELECT * FROM `keys` WHERE plan_code = ? AND status = 'available' LIMIT 1");
            $stmtKey->execute([$order['plan_code']]);
            $availKey = $stmtKey->fetch();

            if ($availKey) {
                $licenseKey = $availKey['license_key'];
                // Đánh dấu key đã dùng
                $db->prepare("UPDATE `keys` SET status = 'used', order_code = ?, used_at = NOW() WHERE id = ?")->execute([$order['order_code'], $availKey['id']]);
            } else {
                // Tạo key mới tự động
                $licenseKey = generateLicenseKey($order['plan_code']);
                $db->prepare("INSERT INTO `keys` (license_key, plan_code, status, order_code, used_at) VALUES (?, ?, 'used', ?, NOW())")->execute([
                    $licenseKey, $order['plan_code'], $order['order_code']
                ]);
            }

            // Cập nhật đơn hàng
            $db->prepare("UPDATE orders SET payment_status = 'paid', license_key = ? WHERE id = ?")->execute([$licenseKey, $orderId]);
            $actionMsg = "Đã duyệt thanh toán thành công đơn hàng #{$order['order_code']} và cấp key: {$licenseKey}";
        }
    } elseif ($action === 'cancel' && $orderId > 0) {
        $db->prepare("UPDATE orders SET payment_status = 'cancelled' WHERE id = ?")->execute([$orderId]);
        $actionMsg = "Đã hủy đơn hàng #{$orderId}";
    } elseif ($action === 'delete' && $orderId > 0) {
        $db->prepare("DELETE FROM orders WHERE id = ?")->execute([$orderId]);
        $actionMsg = "Đã xóa đơn hàng #{$orderId}";
    } elseif ($action === 'save_settings') {
        $bankId = trim($_POST['bank_id'] ?? 'MBBank');
        $bankAccount = trim($_POST['bank_account'] ?? '0338996239');
        $bankOwner = trim($_POST['bank_owner'] ?? 'NGUYEN DUY THIEN');
        $zalo = trim($_POST['zalo_contact'] ?? '0338996239');

        $db->prepare("INSERT INTO settings (key_name, key_value) VALUES ('bank_id', ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)")->execute([$bankId]);
        $db->prepare("INSERT INTO settings (key_name, key_value) VALUES ('bank_account', ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)")->execute([$bankAccount]);
        $db->prepare("INSERT INTO settings (key_name, key_value) VALUES ('bank_owner', ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)")->execute([$bankOwner]);
        $db->prepare("INSERT INTO settings (key_name, key_value) VALUES ('zalo_contact', ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)")->execute([$zalo]);

        $actionMsg = "Đã lưu cài đặt thanh toán và liên hệ thành công!";
    } elseif ($action === 'change_password') {
        $currentPass = trim($_POST['current_password'] ?? '');
        $newPass = trim($_POST['new_password'] ?? '');
        $confirmPass = trim($_POST['confirm_password'] ?? '');
        $currentAdminUser = $_SESSION['admin_user'] ?? 'admin';

        if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
            $dbError = 'Vui lòng điền đầy đủ các trường thông tin đổi mật khẩu!';
        } elseif (strlen($newPass) < 6) {
            $dbError = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
        } elseif ($newPass !== $confirmPass) {
            $dbError = 'Mật khẩu xác nhận không trùng khớp với mật khẩu mới!';
        } else {
            // Kiểm tra mật khẩu hiện tại trong DB
            try {
                $stmt = $db->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
                $stmt->execute([$currentAdminUser]);
                $admin = $stmt->fetch();

                $isPasswordValid = false;
                if ($admin && !empty($admin['password'])) {
                    $isPasswordValid = password_verify($currentPass, $admin['password']);
                }

                if (!$isPasswordValid) {
                    $dbError = 'Mật khẩu hiện tại không chính xác!';
                } else {
                    $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                    if ($admin) {
                        $stmtUpdate = $db->prepare("UPDATE admins SET password = ? WHERE username = ?");
                        $stmtUpdate->execute([$newHash, $currentAdminUser]);
                    } else {
                        $stmtInsert = $db->prepare("INSERT INTO admins (username, password, full_name) VALUES (?, ?, 'Quản Trị Viên AutoClash')");
                        $stmtInsert->execute([$currentAdminUser, $newHash]);
                    }
                    $actionMsg = "Đổi mật khẩu thành công! Bạn có thể sử dụng mật khẩu mới cho các lần đăng nhập tiếp theo.";
                }
            } catch (Exception $e) {
                $dbError = 'Lỗi cập nhật mật khẩu: ' . $e->getMessage();
            }
        }
    }

    if (!empty($actionMsg)) $_SESSION['action_msg'] = $actionMsg;
    if (!empty($dbError)) $_SESSION['db_error'] = $dbError;
    header("Location: index.php");
    exit;
}

if (isset($_SESSION['action_msg'])) {
    $actionMsg = $_SESSION['action_msg'];
    unset($_SESSION['action_msg']);
}
if (isset($_SESSION['db_error'])) {
    $dbError = $_SESSION['db_error'];
    unset($_SESSION['db_error']);
}

// Lấy thống kê
$totalOrders = 0;
$pendingOrders = 0;
$totalRevenue = 0;
$availableKeys = 0;
$ordersList = [];

if ($db) {
    try {
        $totalOrders = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $pendingOrders = (int)$db->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'pending'")->fetchColumn();
        $totalRevenue = (int)$db->query("SELECT SUM(amount) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
        $availableKeys = (int)$db->query("SELECT COUNT(*) FROM `keys` WHERE status = 'available'")->fetchColumn();

        $stmt = $db->query("SELECT * FROM orders ORDER BY id DESC LIMIT 50");
        $ordersList = $stmt->fetchAll();
    } catch (Exception $e) {
        // table not initialized yet
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoClash - Quản Trị Hệ Thống</title>
    <link rel="icon" type="image/x-icon" href="../favicon.ico?v=<?= time() ?>">
    <link rel="shortcut icon" href="../favicon.ico?v=<?= time() ?>">
    <link rel="icon" type="image/png" href="../assets/img/logo.png?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-body: #0b0f19;
            --bg-card: #131b2e;
            --primary: #f59e0b;
            --primary-glow: rgba(245, 158, 11, 0.35);
            --border: #1e293b;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background: var(--bg-body); color: var(--text); min-height: 100vh; }
        
        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 32px;
            background: #0e1424;
            border-bottom: 1px solid var(--border);
        }
        .nav-brand { display: flex; align-items: center; gap: 12px; }
        .nav-brand img { width: 36px; height: 36px; }
        .nav-brand h2 { font-size: 1.3rem; font-weight: 800; color: #fff; }
        .nav-brand span { color: var(--primary); }
        .nav-links { display: flex; align-items: center; gap: 20px; }
        .nav-links a { color: var(--text-muted); text-decoration: none; font-weight: 500; transition: color 0.2s; }
        .nav-links a:hover, .nav-links a.active { color: var(--primary); }
        .btn-logout {
            padding: 8px 16px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid #ef4444;
            color: #fca5a5;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .btn-logout:hover { background: #ef4444; color: #fff; }

        /* Container */
        .container { max-width: 1300px; margin: 30px auto; padding: 0 20px; }

        /* Alert */
        .alert {
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
        }
        .alert-success { background: rgba(16, 185, 129, 0.15); border: 1px solid var(--success); color: #6ee7b7; }
        .alert-danger { background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); color: #fca5a5; }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 18px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .stat-icon {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .stat-icon.revenue { background: rgba(16, 185, 129, 0.15); color: var(--success); }
        .stat-icon.orders { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
        .stat-icon.pending { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
        .stat-icon.keys { background: rgba(168, 85, 247, 0.15); color: #c084fc; }
        .stat-info h4 { font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px; }
        .stat-info .num { font-size: 1.6rem; font-weight: 800; color: #fff; }

        /* Section Card */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }
        .card-header h3 { font-size: 1.2rem; font-weight: 700; }
        .card-header a { color: var(--primary); text-decoration: none; font-size: 0.9rem; }

        /* Table */
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem; }
        th { padding: 14px 18px; background: #0e1424; color: var(--text-muted); font-weight: 700; border-bottom: 1px solid var(--border); white-space: nowrap; }
        td { padding: 14px 18px; border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle; white-space: nowrap; }
        tr:hover td { background: rgba(255,255,255,0.02); }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .badge-pending { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid #f59e0b; }
        .badge-paid { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid #10b981; }
        .badge-cancelled { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; }

        .key-tag {
            font-family: monospace;
            background: #0b0f19;
            border: 1px dashed var(--primary);
            color: #fde68a;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            user-select: all;
        }

        /* Action Buttons */
        .action-btns { display: flex; gap: 8px; align-items: center; }
        .btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-approve { background: #10b981; color: #fff; }
        .btn-approve:hover { background: #059669; }
        .btn-cancel { background: #4b5563; color: #fff; }
        .btn-cancel:hover { background: #374151; }
        .btn-delete { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid #ef4444; }
        .btn-delete:hover { background: #ef4444; color: #fff; }

        /* Form Grid */
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 6px; }
        .form-group input {
            width: 100%;
            padding: 10px 14px;
            background: #0b0f19;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: #fff;
            outline: none;
        }
        .form-group input:focus { border-color: var(--primary); }
    </style>
</head>
<body>

<!-- Navbar -->
<header class="navbar">
    <div class="nav-brand">
        <img src="../assets/img/logo.png" alt="AutoClash" style="width: 36px; height: 36px; object-fit: contain; filter: drop-shadow(0 0 10px rgba(120, 80, 255, 0.6));">
        <h2>AutoClash <span>Admin Panel</span></h2>
    </div>
    <div class="nav-links">
        <a href="index.php" class="active"><i class="fas fa-shopping-cart"></i> Đơn Hàng</a>
        <a href="keys.php"><i class="fas fa-key"></i> Kho Key</a>
        <a href="#change-password"><i class="fas fa-lock"></i> Đổi Mật Khẩu</a>
        <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> Xem Trang Chủ</a>
        <span style="color: var(--text-muted);">|</span>
        <span style="color: #fff; font-size: 0.9rem;"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Thoát</a>
    </div>
</header>

<div class="container">

    <!-- Alerts -->
    <?php if (!empty($actionMsg)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($actionMsg) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($dbError)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <span><?= htmlspecialchars($dbError) ?></span>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon revenue"><i class="fas fa-money-bill-wave"></i></div>
            <div class="stat-info">
                <h4>Tổng Doanh Thu</h4>
                <div class="num"><?= formatCurrency($totalRevenue) ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orders"><i class="fas fa-box-open"></i></div>
            <div class="stat-info">
                <h4>Tổng Đơn Hàng</h4>
                <div class="num"><?= $totalOrders ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon pending"><i class="fas fa-hourglass-half"></i></div>
            <div class="stat-info">
                <h4>Đơn Chờ Duyệt</h4>
                <div class="num" style="color: var(--warning);"><?= $pendingOrders ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon keys"><i class="fas fa-key"></i></div>
            <div class="stat-info">
                <h4>Key Sẵn Sàng</h4>
                <div class="num"><?= $availableKeys ?></div>
            </div>
        </div>
    </div>

    <!-- Orders Management -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Danh Sách Đơn Hàng Mua Key Mới Nhất</h3>
            <span>Tự động sắp xếp đơn mới lên đầu</span>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Mã Đơn</th>
                        <th>Gói Key</th>
                        <th>Khách Hàng (Tên / SĐT)</th>
                        <th>Số Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Key Cấp</th>
                        <th>Ngày Tạo</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ordersList)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 8px; display: block;"></i>
                                Chưa có đơn hàng nào được tạo
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ordersList as $o): ?>
                            <tr>
                                <td><strong style="color: var(--primary);">#<?= htmlspecialchars($o['order_code']) ?></strong></td>
                                <td><?= htmlspecialchars($o['plan_name']) ?></td>
                                <td>
                                    <div><strong><?= htmlspecialchars($o['customer_name']) ?></strong></div>
                                    <div style="font-size: 0.85rem; color: var(--text-muted);"><i class="fab fa-whatsapp"></i> <?= htmlspecialchars($o['customer_phone']) ?></div>
                                </td>
                                <td><strong style="color: var(--success);"><?= formatCurrency($o['amount']) ?></strong></td>
                                <td>
                                    <?php if ($o['payment_status'] === 'paid'): ?>
                                        <span class="badge badge-paid"><i class="fas fa-check"></i> Đã Thanh Toán</span>
                                    <?php elseif ($o['payment_status'] === 'cancelled'): ?>
                                        <span class="badge badge-cancelled"><i class="fas fa-times"></i> Đã Hủy</span>
                                    <?php else: ?>
                                        <span class="badge badge-pending"><i class="fas fa-clock"></i> Chờ Duyệt</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($o['license_key'])): ?>
                                        <span class="key-tag"><?= htmlspecialchars($o['license_key']) ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">Chưa cấp</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($o['created_at']) ?></td>
                                <td>
                                    <div class="action-btns">
                                        <?php if ($o['payment_status'] === 'pending'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-approve" title="Duyệt đơn và tự động gán Key">
                                                    <i class="fas fa-check"></i> Duyệt & Cấp Key
                                                </button>
                                            </form>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                                <input type="hidden" name="action" value="cancel">
                                                <button type="submit" class="btn btn-cancel" title="Hủy đơn">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đơn này?');">
                                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn btn-delete" title="Xóa đơn">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Settings Card -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-cog"></i> Cấu Hình Thanh Toán VietQR & Hotline</h3>
            <span>Thông tin hiển thị cho khách quét mã khi mua key</span>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="save_settings">
            <div class="form-grid">
                <div class="form-group">
                    <label>Ngân hàng nhận tiền (Mã VietQR)</label>
                    <input type="text" name="bank_id" value="<?= htmlspecialchars(getSetting('bank_id', 'MBBank')) ?>" required>
                </div>
                <div class="form-group">
                    <label>Số tài khoản ngân hàng</label>
                    <input type="text" name="bank_account" value="<?= htmlspecialchars(getSetting('bank_account', '0338996239')) ?>" required>
                </div>
                <div class="form-group">
                    <label>Tên chủ tài khoản</label>
                    <input type="text" name="bank_owner" value="<?= htmlspecialchars(getSetting('bank_owner', 'NGUYEN DUY THIEN')) ?>" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại / Zalo hỗ trợ</label>
                    <input type="text" name="zalo_contact" value="<?= htmlspecialchars(getSetting('zalo_contact', '0338996239')) ?>" required>
                </div>
            </div>
            <button type="submit" class="btn btn-approve" style="padding: 10px 24px; font-size: 0.95rem;">
                <i class="fas fa-save"></i> Lưu Cấu Hình
            </button>
        </form>
    </div>

    <!-- Change Password Card -->
    <div class="card" id="change-password">
        <div class="card-header">
            <h3><i class="fas fa-key" style="color: var(--primary);"></i> Đổi Mật Khẩu Quản Trị</h3>
            <span>Cập nhật mật khẩu đăng nhập Admin (Tài khoản: <strong style="color: var(--primary);"><?= htmlspecialchars($_SESSION['admin_user'] ?? 'admin') ?></strong>)</span>
        </div>

        <form method="POST" autocomplete="off">
            <input type="hidden" name="action" value="change_password">
            <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
                <div class="form-group">
                    <label><i class="fas fa-lock-open"></i> Mật khẩu hiện tại</label>
                    <div style="position: relative;">
                        <input type="password" name="current_password" id="curr_pwd" placeholder="Nhập mật khẩu hiện tại" required style="padding-right: 42px;">
                        <span onclick="togglePwd('curr_pwd', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);" title="Ẩn/hiện mật khẩu">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-key"></i> Mật khẩu mới</label>
                    <div style="position: relative;">
                        <input type="password" name="new_password" id="new_pwd" placeholder="Tối thiểu 6 ký tự" minlength="6" required style="padding-right: 42px;">
                        <span onclick="togglePwd('new_pwd', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);" title="Ẩn/hiện mật khẩu">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-shield-alt"></i> Xác nhận mật khẩu mới</label>
                    <div style="position: relative;">
                        <input type="password" name="confirm_password" id="confirm_pwd" placeholder="Nhập lại mật khẩu mới" minlength="6" required style="padding-right: 42px;">
                        <span onclick="togglePwd('confirm_pwd', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);" title="Ẩn/hiện mật khẩu">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-approve" style="padding: 10px 24px; font-size: 0.95rem; background: #f59e0b; color: #0b0f19; font-weight: 700;">
                <i class="fas fa-check-double"></i> Cập Nhật Mật Khẩu Mới
            </button>
        </form>
    </div>

</div>

<script>
function togglePwd(id, el) {
    const input = document.getElementById(id);
    const icon = el.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
</body>
</html>
