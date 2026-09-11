-- =======================================================
-- Database Schema: AutoClash
-- Hệ thống Website tải bộ cài và Mua bán Key tự động
-- =======================================================


-- 1. Bảng Cài đặt cấu hình hệ thống (Settings)
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `key_name` VARCHAR(100) UNIQUE NOT NULL,
    `key_value` TEXT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dữ liệu mẫu cài đặt ban đầu
INSERT INTO `settings` (`key_name`, `key_value`) VALUES
('site_name', 'AutoClash - Phần Mềm Auto Clash of Clans Đỉnh Cao'),
('bank_id', 'MBBank'),
('bank_account', '0338996239'),
('bank_owner', 'NGUYEN DUY THIEN'),
('zalo_contact', '0338996239'),
('telegram_contact', 'https://t.me/autoclashvn'),
('download_link', 'AutoClash_Setup.exe'),
('app_version', 'v2.5.0')
ON DUPLICATE KEY UPDATE `key_value` = VALUES(`key_value`);

-- 2. Bảng Các gói Key (Plans)
CREATE TABLE IF NOT EXISTS `plans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `plan_code` VARCHAR(50) UNIQUE NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `price` INT NOT NULL DEFAULT 0,
    `duration_days` INT NOT NULL DEFAULT 1, -- 0 = vĩnh viễn
    `description` TEXT NULL,
    `badge` VARCHAR(50) DEFAULT '',
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `plans` (`plan_code`, `name`, `price`, `duration_days`, `description`, `badge`, `is_active`, `sort_order`) VALUES
('1DAY', 'Gói Dùng Thử 1 Ngày', 5000, 1, 'Trải nghiệm full tính năng auto cày vàng/dầu, đập tường, treo máy 24/7.', 'DÙNG THỬ', 1, 1),
('1MONTH', 'Gói Tiết Kiệm 1 Tháng', 50000, 30, 'Gói phổ biến nhất! Tiết kiệm 65%, hỗ trợ kỹ thuật và cập nhật kịch bản liên tục.', 'PHỔ BIẾN NHẤT', 1, 2),
('LIFETIME', 'Gói Vĩnh Viễn VIP', 250000, 0, 'Mua 1 lần dùng trọn đời, hỗ trợ VIP riêng biệt, ưu tiên kịch bản meta mới.', 'TIẾT KIỆM 90%', 1, 3)
ON DUPLICATE KEY UPDATE `price` = VALUES(`price`), `name` = VALUES(`name`);

-- 3. Bảng Tài khoản Quản trị viên (Admins)
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) DEFAULT 'Admin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admins` (`id`, `username`, `password`, `full_name`) VALUES
(1, 'admin', '$2y$10$tZ2y.Gq6.hZz94WqLw758eQy6XU4jN6rIkgW7eT8aZ4M.Xp3O97d2', 'Quản Trị Viên AutoClash')
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);

-- 4. Bảng Kho Key Bản Quyền (License Keys)
CREATE TABLE IF NOT EXISTS `keys` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `license_key` VARCHAR(100) UNIQUE NOT NULL,
    `plan_code` VARCHAR(50) NOT NULL,
    `status` ENUM('available', 'used', 'expired') DEFAULT 'available',
    `order_code` VARCHAR(50) NULL,
    `user_device_id` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `used_at` DATETIME NULL,
    `expires_at` DATETIME NULL,
    INDEX (`license_key`),
    INDEX (`plan_code`),
    INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Một số key mẫu sẵn sàng trong kho
INSERT INTO `keys` (`license_key`, `plan_code`, `status`) VALUES
('AC-1DAY-9812-7719-ABCD', '1DAY', 'available'),
('AC-1DAY-5521-8842-EFGH', '1DAY', 'available'),
('AC-1MONTH-3321-9982-MNPQ', '1MONTH', 'available'),
('AC-1MONTH-4412-8871-RSTU', '1MONTH', 'available'),
('AC-LIFE-VIP1-9999-WXYZ', 'LIFETIME', 'available')
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);

-- 5. Bảng Đơn Hàng Mua Key (Orders)
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_code` VARCHAR(50) UNIQUE NOT NULL,
    `plan_code` VARCHAR(50) NOT NULL,
    `plan_name` VARCHAR(150) NOT NULL,
    `customer_name` VARCHAR(150) NOT NULL,
    `customer_phone` VARCHAR(50) NOT NULL,
    `customer_email` VARCHAR(150) NULL,
    `customer_note` TEXT NULL,
    `amount` INT NOT NULL,
    `payment_status` ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    `license_key` VARCHAR(100) NULL,
    `admin_note` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`order_code`),
    INDEX (`customer_phone`),
    INDEX (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
