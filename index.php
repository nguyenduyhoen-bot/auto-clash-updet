<?php
require_once __DIR__ . '/config/db.php';

$siteName     = getSetting('site_name', 'AutoClash - Phần Mềm Auto Clash of Clans Tự Động Hóa');
$bankId       = getSetting('bank_id', 'MBBank');
$bankAccount  = getSetting('bank_account', '0338996239');
$bankOwner    = getSetting('bank_owner', 'NGUYEN DUY THIEN');
$zaloContact  = getSetting('zalo_contact', '0338996239');
$appVersion   = getSetting('app_version', 'v2.5.0');
$downloadLink = getSetting('download_link', '../AutoClash_Setup.exe');
$plans        = getPlans();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($siteName) ?></title>
  <meta name="description" content="Tải bộ cài đặt AutoClash và mua key kích hoạt tự động qua VietQR. Auto cày tài nguyên, nâng tường, thả quân kịch bản thông minh cho Clash of Clans.">
  <link rel="icon" type="image/x-icon" href="firegost.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body>
  <!-- Background Glow & Grid Effects -->
  <div class="bg-grid"></div>
  <div class="bg-glow bg-glow-1"></div>
  <div class="bg-glow bg-glow-2"></div>
  <div class="bg-glow bg-glow-3"></div>

  <!-- Navbar -->
  <header class="navbar">
    <div class="nav-container">
      <a href="#hero" class="nav-logo">
        <div class="logo-icon"><i class="fa-solid fa-fire-flame-curved"></i></div>
        <div class="logo-text">Auto<span>Clash</span></div>
        <span class="badge-version"><?= htmlspecialchars($appVersion) ?></span>
      </a>
      <nav class="nav-links">
        <a href="#pricing">Bảng giá Key</a>
        <a href="#activate">Kích hoạt Key</a>
        <a href="#lookup">Tra cứu Key</a>
        <a href="#download">Tải bộ cài</a>
        <a href="#changelog">Lịch sử cập nhật</a>
        <a href="#guide">Hướng dẫn</a>
      </nav>
      <div class="nav-actions">
        <a href="#pricing" class="btn btn-glow"><i class="fa-solid fa-key"></i> Mua Key</a>
        <a href="#activate" class="btn btn-outline"><i class="fa-solid fa-bolt"></i> Kích Hoạt</a>
        <a href="admin/login.php" class="btn btn-admin-nav" title="Đăng nhập Quản trị viên" target="_blank"><i class="fa-solid fa-user-shield"></i> Admin</a>
      </div>
      <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle menu">
        <i class="fa-solid fa-bars"></i>
      </button>
    </div>
  </header>

  <!-- Mobile Menu Dropdown -->
  <div class="mobile-menu" id="mobileMenu">
    <a href="#pricing" class="mobile-link">Bảng giá Key</a>
    <a href="#activate" class="mobile-link">Kích hoạt Key</a>
    <a href="#lookup" class="mobile-link">Tra cứu Key</a>
    <a href="#download" class="mobile-link">Tải bộ cài</a>
    <a href="#changelog" class="mobile-link">Lịch sử cập nhật</a>
    <a href="#guide" class="mobile-link">Hướng dẫn</a>
    <a href="admin/login.php" class="mobile-link admin-mobile-link" target="_blank"><i class="fa-solid fa-user-shield"></i> Đăng Nhập Quản Trị (Admin)</a>
    <div class="mobile-btns">
      <a href="#pricing" class="btn btn-outline w-full"><i class="fa-solid fa-key"></i> Mua Key</a>
      <a href="#activate" class="btn btn-glow w-full"><i class="fa-solid fa-bolt"></i> Kích Hoạt Key</a>
    </div>
  </div>

  <main>
    <!-- Hero Section -->
    <section id="hero" class="hero-section">
      <div class="container hero-content">
        <div class="hero-badge">
          <span class="pulse-dot"></span>
          <span>Bản Cập Nhật <?= htmlspecialchars($appVersion) ?> Mới Nhất • Tự Động Hóa 100%</span>
        </div>
        <h1 class="hero-title">
          Tối Ưu Hóa & Cày Game <br>
          <span class="gradient-text">Clash of Clans</span> Tự Động 24/7
        </h1>
        <p class="hero-desc">
          AutoClash mang lại giải pháp auto farm tài nguyên cực nhanh, auto nâng cấp tường, tự động thả quân thông minh theo kịch bản và hỗ trợ đa giả lập LDPlayer, BlueStacks, Nox mượt mà trên Windows.
        </p>
        <div class="hero-cta">
          <a href="#download" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-download"></i> Tải Bản Cài Đặt (Full ZIP / EXE)
          </a>
          <a href="#pricing" class="btn btn-glow btn-lg">
            <i class="fa-solid fa-cart-shopping"></i> Mua Key Kích Hoạt
          </a>
          <a href="#activate" class="btn btn-outline btn-lg" style="border-color: #4f8cff; color: #4f8cff;">
            <i class="fa-solid fa-bolt"></i> Kích Hoạt Key Qua Web
          </a>
          <a href="#lookup" class="btn btn-outline btn-lg">
            <i class="fa-solid fa-magnifying-glass"></i> Tra Cứu Đơn & Key
          </a>
        </div>
        <div class="hero-highlights">
          <div class="highlight-item"><i class="fa-solid fa-shield-halved"></i> An Toàn Tuyệt Đối</div>
          <div class="highlight-item"><i class="fa-solid fa-wifi"></i> Tự Kết Nối Lại Khi Mất Mạng</div>
          <div class="highlight-item"><i class="fa-solid fa-cloud-arrow-down"></i> Tự Cập Nhật Từ Xa</div>
        </div>
      </div>
    </section>

    <!-- Pricing Section (Dynamic from Database) -->
    <section id="pricing" class="section pricing-section">
      <div class="container">
        <div class="section-header">
          <div class="section-tag">Bảng Giá Key Tự Động</div>
          <h2 class="section-title">Chọn Gói Bản Quyền <span class="gradient-text">AutoClash</span> Phù Hợp</h2>
          <p class="section-subtitle">Thanh toán tự động qua VietQR - Hệ thống kích hoạt key tự động hoặc liên hệ Zalo Admin <strong><?= htmlspecialchars($zaloContact) ?></strong> để nhận key nhanh nhất!</p>
        </div>

        <div class="pricing-grid">
          <?php foreach ($plans as $plan): ?>
            <?php 
              $isHighlight = ($plan['plan_code'] === '1MONTH');
              $badgeText = $plan['badge'] ?? '';
            ?>
            <div class="pricing-card <?= $isHighlight ? 'popular' : '' ?>">
              <?php if (!empty($badgeText)): ?>
                <div class="card-badge"><?= htmlspecialchars($badgeText) ?></div>
              <?php endif; ?>
              <div class="pricing-header">
                <h3 class="plan-name"><?= htmlspecialchars($plan['name']) ?></h3>
                <div class="plan-price">
                  <span class="currency">₫</span>
                  <span class="amount"><?= number_format($plan['price'], 0, ',', '.') ?></span>
                  <span class="period">/ <?= $plan['duration_days'] > 0 ? $plan['duration_days'] . ' Ngày' : 'Vĩnh Viễn' ?></span>
                </div>
                <p class="plan-summary"><?= htmlspecialchars($plan['description']) ?></p>
              </div>

              <ul class="plan-features">
                <li><i class="fa-solid fa-check"></i> Cày Vàng, Dầu, Dầu đen tối đa công suất</li>
                <li><i class="fa-solid fa-check"></i> Auto nâng cấp tường thông minh</li>
                <li><i class="fa-solid fa-check"></i> Hỗ trợ đa giả lập không giới hạn</li>
                <li><i class="fa-solid fa-check"></i> Cập nhật kịch bản liên tục</li>
                <?php if ($plan['plan_code'] === 'LIFETIME'): ?>
                  <li class="highlight"><i class="fa-solid fa-star"></i> Mua 1 lần dùng trọn đời, hỗ trợ VIP</li>
                <?php else: ?>
                  <li><i class="fa-solid fa-check"></i> Hỗ trợ kỹ thuật qua Zalo 24/7</li>
                <?php endif; ?>
              </ul>

              <button type="button" class="btn <?= $isHighlight ? 'btn-primary' : 'btn-outline' ?> w-full btn-buy-plan" 
                      onclick="openBuyModal(this)"
                      data-plan="<?= htmlspecialchars($plan['plan_code']) ?>"
                      data-name="<?= htmlspecialchars($plan['name']) ?>"
                      data-price="<?= $plan['price'] ?>"
                      data-price-format="<?= formatCurrency($plan['price']) ?>">
                <i class="fa-solid fa-cart-shopping"></i> Mua Gói Này
              </button>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Pricing to Activation Callout Banner -->
        <div class="pricing-activation-callout">
          <div class="callout-content">
            <div class="callout-icon"><i class="fa-solid fa-bolt"></i></div>
            <div class="callout-text">
              <h4>Bạn đã sở hữu mã Key bản quyền?</h4>
              <p>Kích hoạt bản quyền trực tuyến ngay trên website để liên kết với máy tính của bạn và bắt đầu auto không giới hạn!</p>
            </div>
          </div>
          <div class="callout-actions">
            <a href="#activate" class="btn btn-glow btn-lg"><i class="fa-solid fa-bolt"></i> Kích Hoạt Key Qua Web</a>
            <a href="#lookup" class="btn btn-outline btn-lg"><i class="fa-solid fa-magnifying-glass"></i> Tra Cứu Key Đã Mua</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Key Activation Section (Kích Hoạt Key Qua Web) -->
    <section id="activate" class="section activate-section">
      <div class="container">
        <div class="section-header">
          <div class="section-tag"><i class="fa-solid fa-bolt"></i> Kích Hoạt Trực Tuyến</div>
          <h2 class="section-title">Kích Hoạt Key <span class="gradient-text">AutoClash Trực Tuyến</span></h2>
          <p class="section-subtitle">
            Kích hoạt mã License Key trực tiếp qua website để liên kết với máy tính của bạn an toàn, nhanh chóng và tự động 100%.
          </p>
        </div>

        <div class="activate-box">
          <form id="activateForm" class="activate-form">
            <div class="activate-grid">
              <div class="form-group">
                <label for="activateKey"><i class="fa-solid fa-key"></i> Mã License Key <span class="req">*</span></label>
                <div class="input-with-icon">
                  <i class="fa-solid fa-barcode"></i>
                  <input type="text" id="activateKey" placeholder="Ví dụ: AC-1MONTH-9921-7782-ABCD" required autocomplete="off">
                </div>
                <small class="field-hint">Nhập mã Key bạn đã mua qua VietQR hoặc nhận được từ Zalo Admin.</small>
              </div>

              <div class="form-group">
                <label for="activateHwid"><i class="fa-solid fa-laptop-code"></i> Mã Máy Tính (HWID) <span class="opt">(Tùy chọn)</span></label>
                <div class="input-with-icon">
                  <i class="fa-solid fa-desktop"></i>
                  <input type="text" id="activateHwid" placeholder="Nhập mã HWID hiển thị trong AutoClash.exe..." autocomplete="off">
                </div>
                <small class="field-hint">Mở <strong>AutoClash.exe</strong> để copy mã HWID máy tính của bạn (để trống nếu chỉ muốn tra cứu tình trạng key).</small>
              </div>
            </div>

            <div class="activate-actions">
              <button type="submit" class="btn btn-glow btn-lg" id="btnActivateKey">
                <i class="fa-solid fa-bolt"></i> Kích Hoạt Key Ngay
              </button>
              <button type="button" class="btn btn-outline btn-lg" id="btnCheckKeyStatus">
                <i class="fa-solid fa-magnifying-glass"></i> Kiểm Tra Tình Trạng Key
              </button>
            </div>
          </form>

          <!-- Kết quả Kích hoạt / Tra cứu -->
          <div id="activateResult" class="activate-result" style="display: none;"></div>

          <!-- Hướng dẫn nhanh -->
          <div class="activate-instructions">
            <div class="instruction-header">
              <i class="fa-solid fa-circle-question"></i>
              <span>Làm sao để lấy Mã Máy (HWID) của bạn?</span>
            </div>
            <div class="instruction-steps">
              <div class="i-step">
                <div class="i-num">1</div>
                <p>Khởi chạy file <strong>AutoClash.exe</strong> trên máy tính của bạn.</p>
              </div>
              <div class="i-step">
                <div class="i-num">2</div>
                <p>Tại giao diện khởi động, copy dòng <strong>Mã Máy (HWID)</strong> hiển thị trên màn hình.</p>
              </div>
              <div class="i-step">
                <div class="i-num">3</div>
                <p>Dán vào ô <strong>Mã Máy Tính (HWID)</strong> phía trên cùng với License Key và bấm <strong>Kích Hoạt Key Ngay</strong>.</p>
              </div>
              <div class="i-step">
                <div class="i-num">4</div>
                <p>Mở lại AutoClash trên máy, hệ thống sẽ tự nhận diện bản quyền Pro không cần nhập lại!</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Order Tracking & Key Lookup Section -->
    <section id="lookup" class="section lookup-section">
      <div class="container">
        <div class="section-header">
          <div class="section-tag">Tra Cứu Bản Quyền</div>
          <h2 class="section-title">Kiểm Tra Đơn Hàng & <span class="gradient-text">Lấy Key Kích Hoạt</span></h2>
          <p class="section-subtitle">Nhập Mã đơn hàng hoặc Số điện thoại / Zalo bạn đã dùng để đặt mua key bên dưới.</p>
        </div>

        <div class="lookup-box">
          <form id="lookupForm" class="lookup-form">
            <div class="lookup-input-group">
              <i class="fa-solid fa-magnifying-glass"></i>
              <input type="text" id="lookupQuery" placeholder="Nhập Mã đơn (VD: AC7892) hoặc Số điện thoại/Zalo..." required>
              <button type="submit" class="btn btn-primary" id="btnLookup">
                <i class="fa-solid fa-search"></i> Tra Cứu Ngay
              </button>
            </div>
          </form>

          <div id="lookupResult" class="lookup-result" style="display: none;"></div>
        </div>
      </div>
    </section>

    <!-- Download Section -->
    <section id="download" class="section download-section">
      <div class="container">
        <div class="download-wrapper">
          <div class="download-info">
            <div class="section-tag">Tải Về Trực Tiếp</div>
            <h2 class="section-title">Tải Phần Mềm <br><span class="gradient-text">AutoClash v2.5.0 Chính Thức</span></h2>
            <p class="section-subtitle">
              Gói cài đặt đầy đủ 100% các tệp thực thi AutoClash_Core, kết nối ADB, công cụ AI OCR và phím tắt Desktop. Tải về là chạy ngay không lỗi!
            </p>
            <div class="download-specs">
              <div class="spec-item"><i class="fa-brands fa-windows"></i> Hỗ trợ Windows 10 / 11 (64-bit)</div>
              <div class="spec-item"><i class="fa-solid fa-box-archive"></i> Đầy đủ 100% tệp bot cốt lõi & ADB</div>
              <div class="spec-item"><i class="fa-solid fa-shield-halved"></i> 100% Sạch sẽ, không dính virus/mã độc</div>
            </div>
            <div class="download-actions" style="flex-wrap: wrap; gap: 12px;">
              <a href="download.php" class="btn btn-primary btn-lg" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-color: #10b981;">
                <i class="fa-solid fa-file-zipper"></i> Tải Bản Cài (.ZIP) - Khuyên Dùng
              </a>
              <a href="download.php?format=exe" class="btn btn-outline btn-lg">
                <i class="fa-brands fa-windows"></i> Tải Trình Cài (.EXE)
              </a>
              <a href="https://zalo.me/<?= htmlspecialchars($zaloContact) ?>" target="_blank" class="btn btn-glow btn-lg">
                <i class="fa-brands fa-whatsapp"></i> Hỗ Trợ Zalo Admin
              </a>
            </div>
            <div style="margin-top: 15px; font-size: 0.85rem; color: #94a3b8; display: flex; align-items: center; gap: 8px;">
              <i class="fa-solid fa-circle-check" style="color: #10b981;"></i>
              <span>Khuyên dùng bản <strong>.ZIP</strong>: Trình duyệt Chrome không bao giờ chặn nhầm virus, giải nén là chơi được ngay!</span>
            </div>
          </div>
          <div class="download-graphic">
            <div class="installer-card">
              <img src="firegost.ico" alt="AutoClash Icon" class="installer-icon">
              <h4>AutoClash_v2.5.0_Full</h4>
              <p>Phiên bản <?= htmlspecialchars($appVersion) ?> • Windows Edition</p>
              <div class="installer-steps">
                <div class="step-mini"><i class="fa-solid fa-check"></i> Đầy đủ file Bot cốt lõi AutoClash_Core</div>
                <div class="step-mini"><i class="fa-solid fa-check"></i> Tự cấu hình môi trường ADB & Giả lập</div>
                <div class="step-mini"><i class="fa-solid fa-check"></i> Script tạo Shortcut Desktop 1-Click</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Changelog / Version History Section -->
    <section id="changelog" class="section changelog-section">
      <div class="container">
        <div class="section-header">
          <div class="section-tag"><i class="fa-solid fa-clock-rotate-left"></i> Nhật Ký Cập Nhật</div>
          <h2 class="section-title">Lịch Sử Cập Nhật & <span class="gradient-text">Nâng Cấp Phiên Bản</span></h2>
          <p class="section-subtitle">
            Theo dõi lộ trình phát triển và các tính năng mới nhất được cập nhật tự động từ xa cho AutoClash.
          </p>
        </div>

        <div class="changelog-timeline">
          <!-- Release v2.5.0 (Latest) -->
          <div class="changelog-card current-release">
            <div class="changelog-badge-row">
              <span class="badge-latest"><i class="fa-solid fa-sparkles"></i> MỚI NHẤT</span>
              <span class="version-tag">Phiên bản v2.5.0</span>
              <span class="release-date"><i class="fa-regular fa-calendar-check"></i> Phát hành: 06/09/2026</span>
            </div>
            <h3 class="changelog-headline">Nâng Cấp AI OCR Tesseract, Tích Hợp Remote Auto-Updater & Bộ Cài Setup Hoàn Chỉnh</h3>
            <div class="changelog-grid">
              <div class="change-item">
                <div class="change-icon"><i class="fa-solid fa-cloud-arrow-down"></i></div>
                <div class="change-text">
                  <strong>Remote Auto-Updater Từ Xa:</strong>
                  <p>Hệ thống tự động kiểm tra phiên bản mới từ máy chủ <code>autococ.infinityfree.me</code>. Người dùng chỉ cần 1 cú click để cập nhật mà không cần cài đặt lại thủ công.</p>
                </div>
              </div>
              <div class="change-item">
                <div class="change-icon"><i class="fa-solid fa-microchip"></i></div>
                <div class="change-text">
                  <strong>Công Nghệ AI OCR Tesseract:</strong>
                  <p>Nhận diện chính xác 100% số lượng Vàng, Dầu và Hắc Dầu trong trận đánh. Tự động bỏ qua các nhà nghèo tài nguyên, chỉ đánh nhà khủng.</p>
                </div>
              </div>
              <div class="change-item">
                <div class="change-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                <div class="change-text">
                  <strong>Bộ Cài Đặt Setup Wizard Toàn Diện:</strong>
                  <p>Tích hợp trực tiếp ADB Platform-Tools và thư viện đồ họa OpenCV. Khắc phục triệt để lỗi thiếu DLL, lỗi khởi chạy trên Windows 10/11.</p>
                </div>
              </div>
              <div class="change-item">
                <div class="change-icon"><i class="fa-solid fa-shield-cat"></i></div>
                <div class="change-text">
                  <strong>Tối Ưu Kịch Bản Đập Tường & Cày Cuốc:</strong>
                  <p>Nâng cấp AI thông minh tìm kiếm góc thả quân tối ưu, tự động đập tường nâng cấp Village 24/7 không cần giám sát.</p>
                </div>
              </div>
            </div>
            <div class="changelog-footer">
              <a href="#download" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-download"></i> Tải Ngay Bản v2.5.0
              </a>
              <span class="status-note"><i class="fa-solid fa-circle-check"></i> Đang hoạt động ổn định trên máy chủ</span>
            </div>
          </div>

          <!-- Release v2.4.0 -->
          <div class="changelog-card">
            <div class="changelog-badge-row">
              <span class="badge-stable"><i class="fa-solid fa-shield"></i> ỔN ĐỊNH</span>
              <span class="version-tag">Phiên bản v2.4.0</span>
              <span class="release-date"><i class="fa-regular fa-calendar"></i> Phát hành: 01/09/2026</span>
            </div>
            <h3 class="changelog-headline">Nâng Cấp Giao Diện Dark Gaming & Hỗ Trợ Đa Trình Giả Lập</h3>
            <div class="changelog-grid">
              <div class="change-item">
                <div class="change-icon"><i class="fa-solid fa-palette"></i></div>
                <div class="change-text">
                  <strong>Giao Diện Dark Gaming Thể Thao Điện Tử:</strong>
                  <p>Thiết kế lại toàn bộ bảng điều khiển AutoClash với giao diện Dark Mode cao cấp, hiển thị chỉ số cày cuốc theo thời gian thực.</p>
                </div>
              </div>
              <div class="change-item">
                <div class="change-icon"><i class="fa-solid fa-desktop"></i></div>
                <div class="change-text">
                  <strong>Tương Thích Mọi Giả Lập:</strong>
                  <p>Hỗ trợ hoàn hảo LDPlayer 9, BlueStacks 5, NoxPlayer, MEmu với tính năng tự động phát hiện cổng kết nối ADB.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Release v2.0.0 -->
          <div class="changelog-card">
            <div class="changelog-badge-row">
              <span class="badge-milestone"><i class="fa-solid fa-flag-checkered"></i> KHỞI ĐẦU</span>
              <span class="version-tag">Phiên bản v2.0.0</span>
              <span class="release-date"><i class="fa-regular fa-calendar"></i> Phát hành: 15/08/2026</span>
            </div>
            <h3 class="changelog-headline">Phát Hành AutoClash Thế Hệ Mới Với Thuật Toán Anti-Ban</h3>
            <div class="changelog-grid">
              <div class="change-item">
                <div class="change-icon"><i class="fa-solid fa-user-secret"></i></div>
                <div class="change-text">
                  <strong>Mô Phỏng Thao Tác Người Thật (Human-Like):</strong>
                  <p>Tạo các chuyển động chuột ngẫu nhiên và độ trễ ngẫu nhiên, loại bỏ hoàn toàn khả năng bị hệ thống Supercell phát hiện bot.</p>
                </div>
              </div>
              <div class="change-item">
                <div class="change-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                <div class="change-text">
                  <strong>Tự Động Train Lính & Xin Quân Bang Hội:</strong>
                  <p>Tự động yêu cầu lính clan castle và luyện quân liên tục theo công thức meta phổ biến nhất.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Installation Guide Section -->
    <section id="guide" class="section">
      <div class="container">
        <div class="section-header">
          <div class="section-tag">Chỉ 3 Bước Đơn Giản</div>
          <h2 class="section-title">Hướng Dẫn Cài Đặt & Kích Hoạt</h2>
          <p class="section-subtitle">Bất kỳ ai cũng có thể bắt đầu auto chỉ sau 2 phút cài đặt.</p>
        </div>

        <div class="guide-steps">
          <div class="guide-card">
            <div class="guide-num">01</div>
            <h3>Tải & Chạy Bộ Cài</h3>
            <p>Tải file <code>AutoClash_Setup.exe</code> về máy tính và nhấp đúp để mở trình cài đặt đồ họa Wizard thông minh.</p>
          </div>
          <div class="guide-card">
            <div class="guide-num">02</div>
            <h3>Cài Đặt Tự Động</h3>
            <p>Chọn thư mục bạn muốn cài đặt và bấm <strong>Cài đặt ngay</strong>. Trình cài đặt sẽ tự cấu hình mọi thứ.</p>
          </div>
          <div class="guide-card">
            <div class="guide-num">03</div>
            <h3>Mở Auto & Thưởng Thức</h3>
            <p>Mở biểu tượng AutoClash trên Desktop, kết nối với giả lập LDPlayer hoặc BlueStacks và bắt đầu cày cuốc!</p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Modal Mua Key & Quét QR VietQR -->
  <div class="modal" id="orderModal" style="display: none;">
    <div class="modal-backdrop" id="modalBackdrop" onclick="closeBuyModal()"></div>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h3 id="modalTitle"><i class="fa-solid fa-bolt"></i> Đặt Mua Key AutoClash</h3>
          <button type="button" class="btn-close" id="modalClose" onclick="closeBuyModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <!-- Step 1: Điền thông tin -->
        <div class="modal-body" id="orderStep1">
          <form id="orderForm">
            <input type="hidden" id="formPlanCode" name="plan_code" value="1MONTH">
            
            <div class="form-group">
              <label><i class="fa-solid fa-box"></i> Gói Bản Quyền Đã Chọn</label>
              <div class="plan-selected-badge" id="selectedPlanDisplay">Gói Tiết Kiệm 1 Tháng - 50.000đ</div>
            </div>

            <div class="form-group">
              <label><i class="fa-solid fa-user"></i> Họ và Tên của bạn <span class="req">*</span></label>
              <input type="text" id="formCustName" name="customer_name" placeholder="Ví dụ: Nguyễn Văn An" required>
            </div>

            <div class="form-group">
              <label><i class="fa-brands fa-whatsapp"></i> Số Điện Thoại / Zalo Nhận Key <span class="req">*</span></label>
              <input type="text" id="formCustPhone" name="customer_phone" placeholder="Ví dụ: 0987654321 (để kích hoạt & hỗ trợ)" required>
            </div>

            <div class="form-group">
              <label><i class="fa-solid fa-envelope"></i> Email (Không bắt buộc)</label>
              <input type="email" id="formCustEmail" name="customer_email" placeholder="email@gmail.com">
            </div>

            <button type="submit" class="btn btn-primary w-full btn-lg" id="btnSubmitOrder">
              <i class="fa-solid fa-qrcode"></i> Tạo Đơn Hàng & Lấy Mã VietQR
            </button>
          </form>
        </div>

        <!-- Step 2: Quét mã QR thanh toán -->
        <div class="modal-body" id="orderStep2" style="display: none;">
          <div class="qr-payment-wrapper">
            <div class="qr-box">
              <img id="qrImage" src="" alt="Mã VietQR Thanh Toán">
              <div class="qr-pulse"></div>
            </div>

            <div class="qr-details">
              <div class="order-alert alert-info">
                <i class="fa-solid fa-info-circle"></i>
                <span>Vui lòng quét mã VietQR hoặc chuyển khoản đúng số tiền và nội dung bên dưới:</span>
              </div>

              <div class="pay-row">
                <span class="pay-label">Mã đơn hàng:</span>
                <span class="pay-val code-highlight" id="resOrderCode">AC123456</span>
              </div>
              <div class="pay-row">
                <span class="pay-label">Số tiền cần chuyển:</span>
                <span class="pay-val val-price" id="resAmount">50.000đ</span>
              </div>
              <div class="pay-row">
                <span class="pay-label">Ngân hàng thụ hưởng:</span>
                <span class="pay-val"><?= htmlspecialchars($bankId) ?></span>
              </div>
              <div class="pay-row">
                <span class="pay-label">Số tài khoản:</span>
                <span class="pay-val copy-field" onclick="copyText('<?= htmlspecialchars($bankAccount) ?>')">
                  <?= htmlspecialchars($bankAccount) ?> <i class="fa-regular fa-copy"></i>
                </span>
              </div>
              <div class="pay-row">
                <span class="pay-label">Chủ tài khoản:</span>
                <span class="pay-val"><?= htmlspecialchars($bankOwner) ?></span>
              </div>
              <div class="pay-row">
                <span class="pay-label">Nội dung chuyển:</span>
                <span class="pay-val code-highlight copy-field" id="resTransferContent" onclick="copyContent()">
                  <span id="contentVal">AC123456</span> <i class="fa-regular fa-copy"></i>
                </span>
              </div>

              <div class="payment-actions">
                <a href="https://zalo.me/<?= htmlspecialchars($zaloContact) ?>" target="_blank" class="btn btn-glow w-full">
                  <i class="fa-solid fa-check-circle"></i> Báo Đã Chuyển Khoản Qua Zalo
                </a>
                <button type="button" class="btn btn-outline w-full" id="btnCheckOrderNow">
                  <i class="fa-solid fa-arrows-rotate"></i> Kiểm Tra Trạng Thái Kích Hoạt Key
                </button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Toast Notification -->
  <div id="toast" class="toast"></div>

  <!-- Footer -->
  <footer class="footer">
    <div class="container footer-content">
      <div class="footer-brand">
        <div class="nav-logo">
          <div class="logo-icon"><i class="fa-solid fa-fire-flame-curved"></i></div>
          <div class="logo-text">Auto<span>Clash</span></div>
        </div>
        <p>Phần mềm hỗ trợ chơi Clash of Clans hàng đầu cho game thủ Việt Nam.</p>
        <p style="margin-top: 10px; font-size: 0.85rem; color: #64748b;">
          Liên hệ Hỗ Trợ: <strong><?= htmlspecialchars($zaloContact) ?></strong> • MBBank: <strong><?= htmlspecialchars($bankAccount) ?></strong>
        </p>
      </div>
      <div class="footer-links">
        <h4>Liên Kết Nhanh</h4>
        <div class="f-links-grid">
          <a href="#pricing"><i class="fa-solid fa-angle-right"></i> Bảng Giá Key</a>
          <a href="#activate"><i class="fa-solid fa-angle-right"></i> Kích Hoạt Key</a>
          <a href="#lookup"><i class="fa-solid fa-angle-right"></i> Tra Cứu Đơn Hàng</a>
          <a href="#download"><i class="fa-solid fa-angle-right"></i> Tải Bộ Cài Đặt</a>
        </div>
      </div>
      <div class="footer-admin-col">
        <h4>Khu Vực Quản Trị</h4>
        <p style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 12px;">Dành riêng cho Ban Quản Trị AutoClash quản lý kho key, duyệt đơn và cấu hình.</p>
        <a href="admin/login.php" target="_blank" class="btn btn-outline btn-sm footer-admin-btn">
          <i class="fa-solid fa-user-shield"></i> Đăng Nhập Quản Trị (Admin)
        </a>
      </div>
    </div>
    <div class="container footer-bottom">
      <p>&copy; <?= date('Y') ?> AutoClash Team. All rights reserved.</p>
      <p><a href="admin/login.php" target="_blank" class="f-admin-text"><i class="fa-solid fa-lock"></i> Đăng Nhập Admin</a></p>
    </div>
  </footer>

  <script src="assets/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
