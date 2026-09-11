<?php
require_once __DIR__ . '/../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!';
    } else {
        $db = getDB();
        $authenticated = false;

        if ($db) {
            try {
                $stmt = $db->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $admin = $stmt->fetch();
                if ($admin && password_verify($password, $admin['password'])) {
                    $authenticated = true;
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_user'] = $admin['username'];
                    $_SESSION['admin_name'] = $admin['full_name'];
                }
            } catch (Exception $e) {
                // fallback below if database not set up
            }
        }

        if ($authenticated) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Tài khoản hoặc mật khẩu không chính xác!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../favicon.ico?v=<?= time() ?>">
    <link rel="shortcut icon" href="../favicon.ico?v=<?= time() ?>">
    <link rel="icon" type="image/png" href="../assets/img/logo.png?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-dark: #090d16;
            --card-bg: #111827;
            --primary: #f59e0b;
            --primary-glow: rgba(245, 158, 11, 0.4);
            --border: #1f2937;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-image: radial-gradient(circle at 50% 20%, rgba(245, 158, 11, 0.08), transparent 50%);
        }
        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            text-align: center;
        }
        .logo-box {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 24px;
        }
        .logo-box img { width: 42px; height: 42px; }
        .logo-box h2 { font-size: 1.5rem; font-weight: 800; color: #fff; }
        .logo-box h2 span { color: var(--primary); }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            background: #0d131f;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: #fff;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.2s;
        }
        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 10px var(--primary-glow);
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
        }
        .error-alert {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid #ef4444;
            color: #fca5a5;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .hint {
            margin-top: 24px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .hint a { color: var(--primary); text-decoration: none; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-box">
        <img src="../assets/img/logo.png" alt="AutoClash Logo" style="object-fit: contain; filter: drop-shadow(0 0 10px rgba(120, 80, 255, 0.6));">
        <h2>AutoClash <span>Admin</span></h2>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error-alert">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label><i class="fas fa-user"></i> Tên đăng nhập</label>
            <input type="text" name="username" placeholder="Nhập tên đăng nhập" required autofocus>
        </div>

        <div class="form-group">
            <label><i class="fas fa-lock"></i> Mật khẩu</label>
            <input type="password" name="password" placeholder="Nhập mật khẩu" required>
        </div>

        <button type="submit" class="btn-submit">
            <i class="fas fa-sign-in-alt"></i> Đăng Nhập
        </button>
    </form>

    <div class="hint">
        <p><a href="../index.php"><i class="fas fa-arrow-left"></i> Quay lại trang chủ</a></p>
    </div>
</div>

</body>
</html>
