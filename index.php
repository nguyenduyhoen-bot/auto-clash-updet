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
        <a href="#features">Tính năng</a>
        <a href="#pricing">Bảng giá Key</a>
        <a href="#lookup">Tra cứu Key</a>
        <a href="#download">Tải bộ cài</a>
        <a href="#guide">Hướng dẫn</a>
        <a href="#faq">Hỏi đáp</a>
      </nav>
      <div class="nav-actions">
        <a href="#pricing" class="btn btn-glow"><i class="fa-solid fa-key"></i> Mua Key</a>
        <a href="#download" class="btn btn-primary"><i class="fa-solid fa-download"></i> Tải Ngay</a>
      </div>
      <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle menu">
        <i class="fa-solid fa-bars"></i>
      </button>
    </div>
  </header>

  <!-- Mobile Menu Dropdown -->
  <div class="mobile-menu" id="mobileMenu">
    <a href="#features" class="mobile-link">Tính năng</a>
    <a href="#pricing" class="mobile-link">Bảng giá Key</a>
    <a href="#lookup" class="mobile-link">Tra cứu Key</a>
    <a href="#download" class="mobile-link">Tải bộ cài</a>
    <a href="#guide" class="mobile-link">Hướng dẫn</a>
    <a href="#faq" class="mobile-link">Hỏi đáp</a>
    <div class="mobile-btns">
      <a href="#pricing" class="btn btn-outline w-full"><i class="fa-solid fa-key"></i> Mua Key</a>
      <a href="#download" class="btn btn-primary w-full"><i class="fa-solid fa-download"></i> Tải Bộ Cài</a>
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
            <i class="fa-solid fa-download"></i> Tải Bộ Cài Đặt (Setup.exe)
          </a>
          <a href="#pricing" class="btn btn-glow btn-lg">
            <i class="fa-solid fa-bolt"></i> Mua Key Kích Hoạt
          </a>
          <a href="#lookup" class="btn btn-outline btn-lg">
            <i class="fa-solid fa-magnifying-glass"></i> Tra Cứu Đơn & Key
          </a>
        </div>
        <div class="hero-highlights">
          <div class="highlight-item"><i class="fa-solid fa-shield-halved"></i> An Toàn Tuyệt Đối</div>
          <div class="highlight-item"><i class="fa-solid fa-wifi"></i> Tự Kết Nối Lại Khi Mất Mạng</div>
          <div class="highlight-item"><i class="fa-solid fa-cloud-arrow-down"></i> Tự Cập Nhật Từ Xa</div>
          <div class="highlight-item"><i class="fa-solid fa-gift"></i> Nhận Thử 1 Ngày Free</div>
        </div>

        <!-- Tool Mockup Visual -->
        <div class="hero-preview">
          <div class="mockup-header">
            <div class="window-dots">
              <span class="dot dot-red"></span>
              <span class="dot dot-yellow"></span>
              <span class="dot dot-green"></span>
            </div>
            <div class="mockup-title">AutoClash Pro <?= htmlspecialchars($appVersion) ?> - Control Center</div>
            <div class="mockup-status"><span class="status-live"></span> Sẵn sàng hoạt động</div>
          </div>
          <div class="mockup-body">
            <div class="mockup-sidebar">
              <div class="mockup-nav active"><i class="fa-solid fa-gauge-high"></i> Bảng điều khiển</div>
              <div class="mockup-nav"><i class="fa-solid fa-crosshairs"></i> Cài đặt đánh trận</div>
              <div class="mockup-nav"><i class="fa-solid fa-cubes-stacked"></i> Nâng cấp tường</div>
              <div class="mockup-nav"><i class="fa-solid fa-robot"></i> Kịch bản quân</div>
              <div class="mockup-nav"><i class="fa-solid fa-sliders"></i> Cấu hình ADB</div>
            </div>
            <div class="mockup-main">
              <div class="mockup-stat-row">
                <div class="m-card">
                  <div class="m-label">Vàng thu hoạch hôm nay</div>
                  <div class="m-val val-gold"><i class="fa-solid fa-coins"></i> 18.500.000</div>
                </div>
                <div class="m-card">
                  <div class="m-label">Dầu thường tích lũy</div>
                  <div class="m-val val-elixir"><i class="fa-solid fa-droplet"></i> 16.200.000</div>
                </div>
                <div class="m-card">
                  <div class="m-label">Dầu đen cướp được</div>
                  <div class="m-val val-dark"><i class="fa-solid fa-gem"></i> 145.000</div>
                </div>
                <div class="m-card">
                  <div class="m-label">Số tường đã đập lên cấp</div>
                  <div class="m-val val-wall"><i class="fa-solid fa-layer-group"></i> 24 viên</div>
                </div>
              </div>
              <div class="mockup-terminal">
                <div class="terminal-line"><span class="term-time">[22:00:12]</span> <span class="term-info">[INFO]</span> Kết nối thành công giả lập 127.0.0.1:5555 qua ADB</div>
                <div class="terminal-line"><span class="term-time">[22:00:15]</span> <span class="term-success">[MATCH]</span> Phát hiện nhà hoang: 950.000 Vàng / 880.000 Dầu! Bắt đầu đánh trận...</div>
                <div class="terminal-line"><span class="term-time">[22:02:40]</span> <span class="term-success">[VICTORY]</span> Đạt 3 sao! Thu hoạch 100% khoáng sản. Trở về làng.</div>
                <div class="terminal-line"><span class="term-time">[22:02:50]</span> <span class="term-accent">[WALL]</span> Tự động nâng cấp 2 viên tường cấp 14 bằng Vàng dư thừa!</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section">
      <div class="container">
        <div class="section-header">
          <div class="section-tag">Công nghệ dẫn đầu</div>
          <h2 class="section-title">Tại Sao Các Top Game Thủ Chọn <span class="gradient-text">AutoClash</span>?</h2>
          <p class="section-subtitle">Được lập trình tối ưu hóa bằng Python và C++ cho hiệu suất cực cao, mượt mà và an toàn tuyệt đối với tài khoản game.</p>
        </div>

        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon icon-gold"><i class="fa-solid fa-sack-dollar"></i></div>
            <h3 class="feature-title">Auto Tìm Nhà Hoang & Lọc Khoáng</h3>
            <p class="feature-desc">Tự động lướt qua hàng nghìn đối thủ trong vài giây để chọn đúng nhà nhiều tài nguyên nhất theo cấu hình của bạn.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon icon-wall"><i class="fa-solid fa-cubes-stacked"></i></div>
            <h3 class="feature-title">Auto Nâng Cấp Tường (Wall Farm)</h3>
            <p class="feature-desc">Tự động đập tường liên tục mỗi khi vàng hoặc dầu đầy. Không lo bị cướp tài nguyên khi vắng nhà!</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon icon-bot"><i class="fa-solid fa-chess-knight"></i></div>
            <h3 class="feature-title">Thả Quân Thông Minh Chuẩn Kịch Bản</h3>
            <p class="feature-desc">Mô phỏng thao tác vuốt thả quân chính xác như người thật, tự động kích hoạt chiêu Tướng (King, Queen, Warden, RC).</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon icon-time"><i class="fa-solid fa-clock-rotate-left"></i></div>
            <h3 class="feature-title">Treo Máy 24/7 Ổn Định Tuyệt Đối</h3>
            <p class="feature-desc">Tích hợp cơ chế tự động reconnect khi mất mạng, tự khởi động lại giả lập khi đơ lag, giúp tài khoản luôn được canh giữ.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon icon-cloud"><i class="fa-solid fa-cloud-arrow-down"></i></div>
            <h3 class="feature-title">Hệ Thống Tự Cập Nhật Từ Xa</h3>
            <p class="feature-desc">Mỗi khi Supercell ra bản update mới, phần mềm tự tải và cập nhật kịch bản nhận diện mà không cần bạn cài lại thủ công.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon icon-shield"><i class="fa-solid fa-shield-virus"></i></div>
            <h3 class="feature-title">Bảo Mật & An Toàn 100%</h3>
            <p class="feature-desc">Không can thiệp vào mã nguồn file game APK, hoạt động an toàn qua ADB nhận diện hình ảnh AI (Tesseract OCR).</p>
          </div>
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

        <!-- Free Trial Banner -->
        <div class="trial-banner">
          <div class="trial-icon"><i class="fa-solid fa-gift"></i></div>
          <div class="trial-content">
            <h3>Bạn Muốn Dùng Thử Trước Khi Mua?</h3>
            <p>Admin tặng ngay <strong>Key dùng thử 1 ngày miễn phí</strong> cho tất cả anh em lần đầu liên hệ trải nghiệm!</p>
          </div>
          <a href="https://zalo.me/<?= htmlspecialchars($zaloContact) ?>" target="_blank" class="btn btn-glow">
            <i class="fa-solid fa-comment-dots"></i> Nhắn Zalo Nhận Key Thử (<?= htmlspecialchars($zaloContact) ?>)
          </a>
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
            <h2 class="section-title">Tải Trình Cài Đặt <br><span class="gradient-text">AutoClash Setup Wizard</span></h2>
            <p class="section-subtitle">
              Bộ cài đặt hoàn chỉnh tích hợp sẵn ADB, công cụ nhận diện OCR và tự tạo Shortcut ngoài màn hình. Không cần cài đặt rườm rà.
            </p>
            <div class="download-specs">
              <div class="spec-item"><i class="fa-brands fa-windows"></i> Hỗ trợ Windows 10 / 11 (64-bit)</div>
              <div class="spec-item"><i class="fa-solid fa-box-archive"></i> Dung lượng: ~10.4 MB (Siêu nhẹ)</div>
              <div class="spec-item"><i class="fa-solid fa-shield-halved"></i> 100% Sạch sẽ, không virus/trojan</div>
            </div>
            <div class="download-actions">
              <a href="<?= htmlspecialchars($downloadLink) ?>" class="btn btn-primary btn-lg" download>
                <i class="fa-solid fa-download"></i> Tải AutoClash_Setup.exe
              </a>
              <a href="https://zalo.me/<?= htmlspecialchars($zaloContact) ?>" target="_blank" class="btn btn-outline btn-lg">
                <i class="fa-brands fa-whatsapp"></i> Hỗ Trợ Cài Đặt Zalo
              </a>
            </div>
          </div>
          <div class="download-graphic">
            <div class="installer-card">
              <img src="firegost.ico" alt="AutoClash Icon" class="installer-icon">
              <h4>AutoClash_Setup.exe</h4>
              <p>Phiên bản <?= htmlspecialchars($appVersion) ?> • Windows Installer</p>
              <div class="installer-steps">
                <div class="step-mini"><i class="fa-solid fa-check"></i> Tự cấu hình môi trường ADB</div>
                <div class="step-mini"><i class="fa-solid fa-check"></i> Cài đặt Tesseract OCR tự động</div>
                <div class="step-mini"><i class="fa-solid fa-check"></i> Tạo Shortcut màn hình Desktop</div>
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

    <!-- FAQ Section -->
    <section id="faq" class="section faq-section">
      <div class="container">
        <div class="section-header">
          <div class="section-tag">Thắc Mắc Phổ Biến</div>
          <h2 class="section-title">Câu Hỏi Thường Gặp (FAQ)</h2>
        </div>

        <div class="faq-list">
          <div class="faq-item">
            <div class="faq-question">
              <span>Sử dụng AutoClash có bị khóa tài khoản Clash of Clans không?</span>
              <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
              Phần mềm AutoClash sử dụng cơ chế nhận diện hình ảnh qua ADB và mô phỏng thao tác vuốt chạm từ bên ngoài như người thật. Phần mềm hoàn toàn KHÔNG can thiệp, hook hay sửa đổi file APK của Supercell nên cực kỳ an toàn.
            </div>
          </div>
          <div class="faq-item">
            <div class="faq-question">
              <span>Hỗ trợ những phần mềm giả lập Android nào?</span>
              <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
              AutoClash tối ưu tốt nhất cho LDPlayer 9, LDPlayer 4, BlueStacks 5, NoxPlayer và MEmu Play. Bạn chỉ cần bật tính năng "ADB Debugging" trong cài đặt giả lập là tool tự nhận diện.
            </div>
          </div>
          <div class="faq-item">
            <div class="faq-question">
              <span>Sau khi thanh toán mua key thì nhận key như thế nào?</span>
              <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
              Sau khi bạn quét mã VietQR và thanh toán thành công, bạn chỉ cần nhập Mã đơn hàng hoặc Số điện thoại vào ô <strong>Tra cứu Key</strong> trên trang web để lấy key ngay lập tức. Ngoài ra bạn có thể nhắn Zalo <strong><?= htmlspecialchars($zaloContact) ?></strong> để Admin kích hoạt ưu tiên trong 30 giây!
            </div>
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
          Liên hệ Admin: <strong><?= htmlspecialchars($zaloContact) ?> (Thiện)</strong> • MBBank: <strong><?= htmlspecialchars($bankAccount) ?></strong>
        </p>
      </div>
      <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> AutoClash Team. All rights reserved.</p>
        <p><a href="admin/login.php" target="_blank" style="color: #64748b; text-decoration: none; font-size: 0.85rem;"><i class="fa-solid fa-lock"></i> Đăng Nhập Quản Trị Admin</a></p>
      </div>
    </div>
  </footer>

  <script src="assets/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
