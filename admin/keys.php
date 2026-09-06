<?php
require_once __DIR__ . '/auth.php';
checkAdminAuth();

$db = getDB();
$msg = '';

if ($db && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'generate_bulk') {
        $planCode = $_POST['plan_code'] ?? '1MONTH';
        $quantity = (int)($_POST['quantity'] ?? 1);
        if ($quantity > 50) $quantity = 50;

        $count = 0;
        for ($i = 0; $i < $quantity; $i++) {
            $key = generateLicenseKey($planCode);
            try {
                $db->prepare("INSERT INTO `keys` (license_key, plan_code, status) VALUES (?, ?, 'available')")->execute([$key, $planCode]);
                $count++;
            } catch (Exception $e) {}
        }
        $msg = "Đã tạo thành công {$count} key mới cho gói {$planCode} vào kho!";
    } elseif ($action === 'delete_key') {
        $keyId = (int)($_POST['key_id'] ?? 0);
        if ($keyId > 0) {
            $db->prepare("DELETE FROM `keys` WHERE id = ?")->execute([$keyId]);
            $msg = "Đã xóa key khỏi kho.";
        }
    }
}

$keysList = [];
if ($db) {
    try {
        $stmt = $db->query("SELECT * FROM `keys` ORDER BY id DESC LIMIT 100");
        $keysList = $stmt->fetchAll();
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kho Key Bản Quyền - AutoClash Admin</title>
    <link rel="icon" type="image/x-icon" href="../firegost.ico">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-body: #0b0f19;
            --bg-card: #131b2e;
            --primary: #f59e0b;
            --border: #1e293b;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --success: #10b981;
            --danger: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background: var(--bg-body); color: var(--text); min-height: 100vh; }
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
        .nav-links a { color: var(--text-muted); text-decoration: none; font-weight: 500; }
        .nav-links a.active { color: var(--primary); }
        .container { max-width: 1300px; margin: 30px auto; padding: 0 20px; }
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 30px;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 12px 16px; background: #0e1424; color: var(--text-muted); font-weight: 600; border-bottom: 1px solid var(--border); }
        td { padding: 14px 16px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .key-code { font-family: monospace; font-size: 1rem; color: #fde68a; background: #090d16; padding: 4px 10px; border-radius: 4px; border: 1px solid rgba(245,158,11,0.3); }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; }
        .badge-avail { background: rgba(16, 185, 129, 0.2); color: #34d399; }
        .badge-used { background: rgba(107, 114, 128, 0.2); color: #9ca3af; }
        .form-row { display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap; }
        .form-group { flex: 1; min-width: 180px; }
        .form-group label { display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 6px; }
        .form-group select, .form-group input {
            width: 100%; padding: 10px 14px; background: #0b0f19; border: 1px solid var(--border); border-radius: 8px; color: #fff; outline: none;
        }
        .btn-submit {
            padding: 11px 24px;
            background: var(--primary);
            color: #000;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .alert-success { background: rgba(16, 185, 129, 0.15); border: 1px solid var(--success); color: #6ee7b7; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>

<header class="navbar">
    <div class="nav-brand">
        <img src="../firegost.ico" alt="AutoClash">
        <h2>AutoClash <span>Admin</span></h2>
    </div>
    <div class="nav-links">
        <a href="index.php"><i class="fas fa-shopping-cart"></i> Đơn Hàng</a>
        <a href="keys.php" class="active"><i class="fas fa-key"></i> Kho Key</a>
        <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> Xem Trang Chủ</a>
        <a href="logout.php" style="color: #f87171;"><i class="fas fa-sign-out-alt"></i> Thoát</a>
    </div>
</header>

<div class="container">

    <?php if (!empty($msg)): ?>
        <div class="alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Bulk generate -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-plus-circle"></i> Tạo Thêm Key Mới Vào Kho</h3>
        </div>
        <form method="POST" class="form-row">
            <input type="hidden" name="action" value="generate_bulk">
            <div class="form-group">
                <label>Chọn gói bản quyền</label>
                <select name="plan_code">
                    <option value="1DAY">Gói Dùng Thử 1 Ngày (1DAY)</option>
                    <option value="1MONTH" selected>Gói 1 Tháng (1MONTH)</option>
                    <option value="LIFETIME">Gói Vĩnh Viễn VIP (LIFETIME)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Số lượng key cần tạo</label>
                <input type="number" name="quantity" value="5" min="1" max="50">
            </div>
            <button type="submit" class="btn-submit">
                <i class="fas fa-magic"></i> Tạo Key Ngay
            </button>
        </form>
    </div>

    <!-- Key list -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-database"></i> Danh Sách Key Trong Kho (<?= count($keysList) ?> Key)</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mã License Key</th>
                        <th>Gói</th>
                        <th>Trạng Thái</th>
                        <th>Đơn Hàng Gán</th>
                        <th>Ngày Tạo</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($keysList)): ?>
                        <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 20px;">Kho key trống</td></tr>
                    <?php else: ?>
                        <?php foreach ($keysList as $k): ?>
                            <tr>
                                <td>#<?= $k['id'] ?></td>
                                <td><span class="key-code"><?= htmlspecialchars($k['license_key']) ?></span></td>
                                <td><strong><?= htmlspecialchars($k['plan_code']) ?></strong></td>
                                <td>
                                    <?php if ($k['status'] === 'available'): ?>
                                        <span class="badge badge-avail">Sẵn sàng</span>
                                    <?php else: ?>
                                        <span class="badge badge-used">Đã sử dụng</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($k['order_code'] ?? 'Chưa gán') ?></td>
                                <td style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($k['created_at']) ?></td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Xóa key này?');">
                                        <input type="hidden" name="action" value="delete_key">
                                        <input type="hidden" name="key_id" value="<?= $k['id'] ?>">
                                        <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer;">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>
