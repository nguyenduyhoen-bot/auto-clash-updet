# ⚡ AutoClash Web - Hệ Thống Website Tải Bộ Cài & Bán Key Bản Quyền (PHP + MySQL)

Trang web thương mại điện tử chuyên nghiệp viết bằng **PHP (PDO) + MySQL**, giao diện Dark Gaming hiện đại, tích hợp thanh toán tự động qua **VietQR (MBBank)** và hệ thống quản trị Admin duyệt đơn & cấp key tự động.

---

## 🌟 Tính Năng Hệ Thống

1. **Giao diện Client (Người dùng)**:
   - Trang đích (Landing page) giới thiệu tính năng tool AutoClash cực kỳ bắt mắt, hiệu ứng glow, animation mượt mà.
   - Nút tải trực tiếp bộ cài đặt đồ họa Windows: `AutoClash_Setup.exe`.
   - Bảng giá các gói key (1 Ngày: 5.000đ, 1 Tháng: 50.000đ, Vĩnh Viễn: 250.000đ).
   - Đặt mua key tự động: Điền họ tên, số điện thoại Zalo -> Hệ thống tạo đơn hàng và sinh mã **VietQR động** kèm chính xác số tiền và mã đơn hàng duy nhất (`ACxxxxxx`).
   - Tra cứu đơn hàng & Lấy Key: Khách nhập mã đơn hoặc SĐT để xem trạng thái đơn, sao chép Key bản quyền 1-click khi đơn được duyệt.

2. **Trang Quản Trị (Admin Panel - `/admin`)**:
   - Thống kê doanh thu, tổng số đơn, đơn chờ duyệt, số lượng key còn sẵn trong kho.
   - Danh sách đơn hàng: Xem thông tin khách hàng, số điện thoại, số tiền.
   - **Nút 1-Click "Duyệt & Cấp Key"**: Tự động lấy key trong kho hoặc sinh key mới gán cho khách hàng.
   - Quản lý Kho Key (`/admin/keys.php`): Tạo hàng loạt key mới theo từng gói.
   - Cấu hình thông tin thanh toán (Ngân hàng, Số tài khoản, Chủ tài khoản, Zalo liên hệ).

---

## 🛠️ Hướng Dẫn Cài Đặt Trên XAMPP / Hosting

### 1. Cấu hình Database
- Mở **phpMyAdmin** (`http://localhost/phpmyadmin`).
- Tạo cơ sở dữ liệu mới tên là: `autoclash_db` (bảng mã `utf8mb4_unicode_ci`).
- Bấm vào tab **Import** và chọn file **`database.sql`** trong thư mục này để nạp các bảng và dữ liệu mẫu.

### 2. Cấu hình kết nối (`config/db.php`)
Mặc định cấu hình chuẩn cho XAMPP:
```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'autoclash_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```
*(Nếu dùng hosting hoặc Laragon, bạn chỉ cần mở file `config/db.php` để sửa lại `DB_USER`, `DB_PASS` phù hợp).*

### 3. Đăng nhập Admin
- Đường dẫn: `http://localhost/web/admin/login.php`
- Tài khoản mặc định:
  - **Tên đăng nhập:** `admin`
  - **Mật khẩu:** `admin123`

---

## 📂 Cấu Trúc Thư Mục Website

```
web/
├── admin/                     # Hệ thống quản trị viên
│   ├── auth.php               # Middleware bảo vệ phiên đăng nhập
│   ├── index.php              # Dashboard thống kê, duyệt đơn hàng
│   ├── keys.php               # Quản lý kho key bản quyền
│   ├── login.php              # Giao diện đăng nhập Admin
│   └── logout.php             # Đăng xuất
├── api/                       # API Backend xử lý AJAX
│   ├── check_order.php        # API tra cứu đơn & trả về License Key
│   └── create_order.php       # API tạo đơn hàng & tạo VietQR
├── assets/
│   ├── css/style.css          # CSS Dark Gaming cao cấp
│   └── js/app.js              # JavaScript xử lý modal, QR, tra cứu
├── config/
│   └── db.php                 # Kết nối MySQL PDO & các hàm tiện ích
├── database.sql               # File cấu trúc cơ sở dữ liệu SQL mẫu
├── firegost.ico               # Icon nhận diện thương hiệu
├── index.php                  # Trang chủ người dùng
└── README.md                  # Hướng dẫn chi tiết
```
