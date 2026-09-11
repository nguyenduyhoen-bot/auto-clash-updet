<?php
require_once __DIR__ . '/auth.php';
checkAdminAuth();

$db = getDB();

if ($db && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'generate_bulk') {
        $planCode = $_POST['plan_code'] ?? '1MONTH';
        $quantity = (int)($_POST['quantity'] ?? 1);
        if ($quantity > 50) $quantity = 50;
        if ($quantity < 1) $quantity = 1;

        $count = 0;
        for ($i = 0; $i < $quantity; $i++) {
            $key = generateLicenseKey($planCode);
            try {
                $db->prepare("INSERT INTO `keys` (license_key, plan_code, status) VALUES (?, ?, 'available')")->execute([$key, $planCode]);
                $count++;
            } catch (Exception $e) {}
        }
        $_SESSION['flash_msg'] = "Đã tạo thành công {$count} key mới cho gói {$planCode} vào kho!";
    } elseif ($action === 'delete_key') {
        $keyId = (int)($_POST['key_id'] ?? 0);
        if ($keyId > 0) {
            $db->prepare("DELETE FROM `keys` WHERE id = ?")->execute([$keyId]);
            $_SESSION['flash_msg'] = "Đã xóa key khỏi kho.";
        }
    } elseif ($action === 'reset_hwid') {
        $keyId = (int)($_POST['key_id'] ?? 0);
        if ($keyId > 0) {
            $db->prepare("UPDATE `keys` SET user_device_id = NULL, status = 'available', used_at = NULL, expires_at = NULL WHERE id = ?")->execute([$keyId]);
            $_SESSION['flash_msg'] = "Đã gỡ Mã Máy Tính (HWID) thành công! Khách hàng có thể kích hoạt lại Key này trên máy mới.";
        }
    }

    // Áp dụng chuẩn Post/Redirect/Get để khi F5 không bị gửi lại form và không bị tự tạo key
    header("Location: keys.php");
    exit;
}

$msg = $_SESSION['flash_msg'] ?? '';
unset($_SESSION['flash_msg']);

// Tự động dọn dẹp nếu có máy tính bị gắn trùng lặp nhiều key (đảm bảo 1 Máy chỉ 1 Key)
if ($db) {
    try {
        $dupCheck = $db->query("
            SELECT user_device_id, COUNT(*) as cnt 
            FROM `keys` 
            WHERE user_device_id IS NOT NULL AND status = 'used' 
            GROUP BY user_device_id 
            HAVING cnt > 1
        ");
        $dupRows = $dupCheck->fetchAll();
        foreach ($dupRows as $dup) {
            $hwidVal = $dup['user_device_id'];
            // Lấy danh sách key của máy này, ưu tiên LIFETIME trước
            $kStmt = $db->prepare("
                SELECT id, plan_code 
                FROM `keys` 
                WHERE user_device_id = ? AND status = 'used' 
                ORDER BY (plan_code = 'LIFETIME') DESC, id DESC
            ");
            $kStmt->execute([$hwidVal]);
            $allKeysForHwid = $kStmt->fetchAll();
            // Giữ lại key đầu tiên, các key còn lại gỡ HWID trả về kho available
            for ($idx = 1; $idx < count($allKeysForHwid); $idx++) {
                $db->prepare("UPDATE `keys` SET user_device_id = NULL, status = 'available', used_at = NULL, expires_at = NULL WHERE id = ?")
                   ->execute([$allKeysForHwid[$idx]['id']]);
            }
        }
    } catch (Exception $e) {}
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-body: #0a0e17;
            --bg-card: #111827;
            --bg-surface: #172033;
            --primary: #f59e0b;
            --border: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(245, 158, 11, 0.35);
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --success: #10b981;
            --danger: #ef4444;
            --accent-blue: #3b82f6;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background: var(--bg-body); color: var(--text); min-height: 100vh; padding-bottom: 60px; }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 36px;
            background: #0e1424;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(12px);
        }
        .nav-brand { display: flex; align-items: center; gap: 12px; }
        .nav-brand img { width: 36px; height: 36px; filter: drop-shadow(0 0 10px rgba(120, 80, 255, 0.6)); }
        .nav-brand h2 { font-size: 1.3rem; font-weight: 800; color: #fff; }
        .nav-brand span { color: var(--primary); }
        .nav-links { display: flex; align-items: center; gap: 24px; }
        .nav-links a { color: var(--text-muted); text-decoration: none; font-weight: 500; font-size: 0.95rem; transition: color 0.2s; display: flex; align-items: center; gap: 8px; }
        .nav-links a:hover, .nav-links a.active { color: var(--primary); }
        .btn-exit { color: #f87171 !important; }
        .btn-exit:hover { color: #ef4444 !important; }

        .container { max-width: 1350px; margin: 30px auto; padding: 0 24px; }

        .alert-success {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #6ee7b7;
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }
        .card-header h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-header h3 i { color: var(--primary); }

        /* Generator Form Styling */
        .gen-form {
            display: flex;
            align-items: flex-end;
            gap: 20px;
            flex-wrap: wrap;
        }
        .form-col-plan {
            flex: 0 0 320px;
        }
        .form-col-qty {
            flex: 0 0 160px;
        }
        .form-col-btn {
            flex: 0 0 auto;
        }
        .form-group label {
            display: block;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
        }
        .form-group select, .form-group input {
            width: 100%;
            height: 46px;
            padding: 0 16px;
            background: #090d16;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            color: #fff;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group select:focus, .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
        }
        .btn-gen-key {
            height: 46px;
            padding: 0 28px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #111;
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35);
            white-space: nowrap;
        }
        .btn-gen-key:hover {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.5);
        }

        /* Table Styling */
        .table-responsive {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--border);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            white-space: nowrap;
        }
        th {
            padding: 16px 20px;
            background: #0e1424;
            color: var(--text-muted);
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        td {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            vertical-align: middle;
            white-space: nowrap;
            font-size: 0.92rem;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255, 255, 255, 0.02); }

        .key-badge-row {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #090d16;
            padding: 6px 12px;
            border-radius: 8px;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .key-code {
            font-family: 'JetBrains Mono', Consolas, monospace;
            font-size: 0.95rem;
            font-weight: 600;
            color: #fde68a;
            letter-spacing: 0.5px;
        }
        .btn-copy-mini {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 2px 4px;
            transition: color 0.2s;
            font-size: 0.85rem;
        }
        .btn-copy-mini:hover { color: #fff; }

        .plan-tag {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .plan-1DAY { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
        .plan-1MONTH { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .plan-LIFETIME { background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3); }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 9999px;
            font-size: 0.82rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .status-avail {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.35);
        }
        .status-bound {
            background: rgba(99, 102, 241, 0.15);
            color: #a5b4fc;
            border: 1px solid rgba(99, 102, 241, 0.35);
        }

        .hwid-badge-row {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #090d16;
            padding: 6px 12px;
            border-radius: 8px;
            border: 1px solid rgba(59, 130, 246, 0.25);
        }
        .hwid-code {
            font-family: 'JetBrains Mono', Consolas, monospace;
            font-size: 0.88rem;
            font-weight: 500;
            color: #93c5fd;
        }
        .hwid-empty {
            color: var(--text-muted);
            font-size: 0.88rem;
            font-style: italic;
        }

        .date-text { font-size: 0.88rem; color: #cbd5e1; }
        .date-highlight { font-size: 0.88rem; color: #34d399; font-weight: 600; }

        .action-cluster {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-unlink {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.4);
            color: #fbbf24;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .btn-unlink:hover {
            background: #f59e0b;
            color: #111;
            box-shadow: 0 2px 10px rgba(245, 158, 11, 0.4);
        }
        .btn-del {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #f87171;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-del:hover {
            background: #ef4444;
            color: #fff;
            box-shadow: 0 2px 10px rgba(239, 68, 68, 0.4);
        }

        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #1e293b;
            border: 1px solid var(--primary);
            color: #fff;
            padding: 12px 20px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            display: none;
            z-index: 10000;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<header class="navbar">
    <div class="nav-brand">
        <img src="../assets/img/logo.png" alt="AutoClash Logo">
        <h2>AutoClash <span>Admin</span></h2>
    </div>
    <div class="nav-links">
        <a href="index.php"><i class="fas fa-shopping-cart"></i> Đơn Hàng</a>
        <a href="keys.php" class="active"><i class="fas fa-key"></i> Kho Key</a>
        <a href="index.php#change-password"><i class="fas fa-lock"></i> Đổi Mật Khẩu</a>
        <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> Xem Trang Chủ</a>
        <a href="logout.php" class="btn-exit"><i class="fas fa-sign-out-alt"></i> Thoát</a>
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
        <form method="POST" class="gen-form">
            <input type="hidden" name="action" value="generate_bulk">
            <div class="form-group form-col-plan">
                <label>Chọn gói bản quyền</label>
                <select name="plan_code">
                    <option value="1DAY">Gói Dùng Thử 1 Ngày (1DAY)</option>
                    <option value="1MONTH" selected>Gói 1 Tháng (1MONTH)</option>
                    <option value="LIFETIME">Gói Vĩnh Viễn VIP (LIFETIME)</option>
                </select>
            </div>
            <div class="form-group form-col-qty">
                <label>Số lượng key cần tạo</label>
                <input type="number" name="quantity" value="5" min="1" max="50">
            </div>
            <div class="form-group form-col-btn">
                <button type="submit" class="btn-gen-key">
                    <i class="fas fa-wand-magic-sparkles"></i> Tạo Key Ngay
                </button>
            </div>
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
                        <th style="width: 60px;">ID</th>
                        <th>Mã License Key</th>
                        <th style="width: 120px;">Gói</th>
                        <th style="width: 140px;">Trạng Thái</th>
                        <th>Mã Máy Đã Gắn (HWID)</th>
                        <th>Hạn Sử Dụng</th>
                        <th>Ngày Tạo</th>
                        <th style="text-align: right; width: 140px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($keysList)): ?>
                        <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 36px;">Kho key hiện đang trống. Hãy tạo key mới ở phía trên.</td></tr>
                    <?php else: ?>
                        <?php foreach ($keysList as $k): ?>
                            <tr>
                                <td style="color: var(--text-muted); font-weight: 600;">#<?= $k['id'] ?></td>
                                <td>
                                    <div class="key-badge-row">
                                        <span class="key-code" id="k_<?= $k['id'] ?>"><?= htmlspecialchars($k['license_key']) ?></span>
                                        <button type="button" class="btn-copy-mini" title="Sao chép mã Key" onclick="copyText('<?= htmlspecialchars($k['license_key']) ?>')">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <span class="plan-tag plan-<?= htmlspecialchars($k['plan_code']) ?>">
                                        <?= htmlspecialchars($k['plan_code']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $isExpired = (!empty($k['expires_at']) && strtotime($k['expires_at']) < time());
                                    if ($isExpired): 
                                    ?>
                                        <span class="status-pill" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35);"><i class="fas fa-clock"></i> Đã hết hạn</span>
                                    <?php elseif (!empty($k['user_device_id'])): ?>
                                        <span class="status-pill status-bound"><i class="fas fa-link"></i> Đã gắn máy</span>
                                    <?php elseif ($k['status'] === 'used'): ?>
                                        <span class="status-pill" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.35);"><i class="fas fa-receipt"></i> Đã cấp (Chờ gắn)</span>
                                    <?php else: ?>
                                        <span class="status-pill status-avail"><i class="fas fa-check-circle"></i> Sẵn sàng</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($k['user_device_id'])): ?>
                                        <div class="hwid-badge-row">
                                            <span class="hwid-code"><?= htmlspecialchars($k['user_device_id']) ?></span>
                                            <button type="button" class="btn-copy-mini" title="Sao chép HWID" onclick="copyText('<?= htmlspecialchars($k['user_device_id']) ?>')">
                                                <i class="fa-regular fa-copy"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="hwid-empty">Chưa gắn máy</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($k['expires_at'])): ?>
                                        <span class="date-highlight"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($k['expires_at']))) ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 0.88rem;">Chưa kích hoạt</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="date-text"><?= htmlspecialchars(date('d/m/Y', strtotime($k['created_at']))) ?></span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-cluster" style="justify-content: flex-end;">
                                        <?php if (!empty($k['user_device_id'])): ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn GỠ MÃ MÁY TÍNH (HWID) để khách hàng có thể kích hoạt key này sang máy khác?');">
                                                <input type="hidden" name="action" value="reset_hwid">
                                                <input type="hidden" name="key_id" value="<?= $k['id'] ?>">
                                                <button type="submit" class="btn-unlink" title="Gỡ HWID để liên kết máy mới">
                                                    <i class="fas fa-unlink"></i> Gỡ HWID
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn XÓA vĩnh viễn key này khỏi kho?');">
                                            <input type="hidden" name="action" value="delete_key">
                                            <input type="hidden" name="key_id" value="<?= $k['id'] ?>">
                                            <button type="submit" class="btn-del" title="Xóa key">
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

</div>

<div id="toast" class="toast"></div>

<script>
function copyText(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Đã sao chép: ' + text);
    }).catch(() => {
        const temp = document.createElement('textarea');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        showToast('Đã sao chép: ' + text);
    });
}

function showToast(msg) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 2500);
}
</script>

</body>
</html>
