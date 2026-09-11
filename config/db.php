<?php
// ========================================================
// AutoClash - Cấu Hình Kết Nối Database & Hệ Thống
// ========================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cấu hình Database MySQL Hosting InfinityFree
define('DB_HOST', 'sql311.infinityfree.com');
define('DB_PORT', '3306');
define('DB_NAME', 'if0_42847466_autococ');
define('DB_USER', 'if0_42847466');
define('DB_PASS', 'NkvKPFIZjJQA');

// Tự động khởi tạo bảng dữ liệu nếu database mới tạo và chưa có bảng
function checkAndInitTables($pdo) {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    try {
        $check = $pdo->query("SHOW TABLES LIKE 'orders'")->fetch();
        if (!$check) {
            $sqlFile = __DIR__ . '/../database.sql';
            if (file_exists($sqlFile)) {
                $sqlContent = file_get_contents($sqlFile);
                $queries = array_filter(array_map('trim', explode(';', $sqlContent)));
                foreach ($queries as $query) {
                    if (!empty($query)) {
                        try {
                            $pdo->exec($query);
                        } catch (Exception $ex) {
                            // ignore if table exists
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("Auto init tables error: " . $e->getMessage());
    }
}

// Hàm kết nối PDO
function getDB() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        // Tự động kiểm tra và khởi tạo các bảng SQL
        checkAndInitTables($pdo);

        return $pdo;
    } catch (PDOException $e) {
        // Ghi log lỗi kết nối
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

// Lấy giá trị cài đặt từ bảng settings (hoặc trả về fallback)
function getSetting($key, $default = '') {
    $versionJsonPath = __DIR__ . '/../version.json';
    $latestVersion = 'v2.5.5';
    if (file_exists($versionJsonPath)) {
        $vData = json_decode(file_get_contents($versionJsonPath), true);
        if (!empty($vData['version'])) {
            $latestVersion = 'v' . ltrim($vData['version'], 'v');
        }
    }

    $defaults = [
        'site_name'        => 'AutoClash - Phần Mềm Auto Clash of Clans Tự Động Hóa',
        'bank_id'          => 'MBBank',
        'bank_account'     => '0338996239',
        'bank_owner'       => 'NGUYEN DUY THIEN',
        'zalo_contact'     => '0338996239',
        'telegram_contact' => 'https://t.me/autoclashvn',
        'download_link'    => 'download.php',
        'app_version'      => $latestVersion
    ];

    $db = getDB();
    if ($db) {
        try {
            $stmt = $db->prepare("SELECT key_value FROM settings WHERE key_name = ? LIMIT 1");
            $stmt->execute([$key]);
            $row = $stmt->fetch();
            if ($row && $row['key_value'] !== null) {
                // If app_version in DB is older than version.json, prefer version.json
                if ($key === 'app_version') {
                    $dbVer = trim($row['key_value']);
                    if (version_compare(ltrim($dbVer, 'v'), ltrim($latestVersion, 'v'), '<')) {
                        return $latestVersion;
                    }
                }
                return $row['key_value'];
            }
        } catch (Exception $e) {
            // fallback nếu bảng chưa tồn tại
        }
    }

    return $defaults[$key] ?? $default;
}

// Lấy danh sách các gói key
function getPlans() {
    $fallbackPlans = [
        [
            'plan_code'     => '1DAY',
            'name'          => 'Gói Dùng Thử 1 Ngày',
            'price'         => 5000,
            'duration_days' => 1,
            'description'   => 'Trải nghiệm full tính năng auto cày vàng/dầu, đập tường, treo máy 24/7.',
            'badge'         => 'DÙNG THỬ'
        ],
        [
            'plan_code'     => '1MONTH',
            'name'          => 'Gói Tiết Kiệm 1 Tháng',
            'price'         => 50000,
            'duration_days' => 30,
            'description'   => 'Gói phổ biến nhất! Tiết kiệm 65%, hỗ trợ kỹ thuật và cập nhật kịch bản liên tục.',
            'badge'         => 'PHỔ BIẾN NHẤT'
        ],
        [
            'plan_code'     => 'LIFETIME',
            'name'          => 'Gói Vĩnh Viễn VIP',
            'price'         => 250000,
            'duration_days' => 0,
            'description'   => 'Mua 1 lần dùng trọn đời, hỗ trợ VIP riêng biệt, ưu tiên kịch bản meta mới.',
            'badge'         => 'TIẾT KIỆM 90%'
        ]
    ];

    $db = getDB();
    if ($db) {
        try {
            $stmt = $db->query("SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC, price ASC");
            $rows = $stmt->fetchAll();
            if ($rows && count($rows) > 0) {
                return $rows;
            }
        } catch (Exception $e) {
            // fallback
        }
    }

    return $fallbackPlans;
}

// Định dạng tiền tệ VND
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . 'đ';
}

// Hàm tạo mã đơn hàng ngẫu nhiên duy nhất (VD: AC78921)
function generateOrderCode() {
    return 'AC' . strtoupper(substr(uniqid(), -6));
}

// Hàm tạo key bản quyền tự động
function generateLicenseKey($planCode) {
    $prefix = 'AC-' . strtoupper($planCode);
    $part1 = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
    $part2 = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
    $part3 = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
    return "{$prefix}-{$part1}-{$part2}-{$part3}";
}
