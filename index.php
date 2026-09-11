<?php
require_once __DIR__ . '/config/db.php';

$siteName     = getSetting('site_name', 'AutoClash - Phần Mềm Auto Clash of Clans Tự Động Hóa');
$bankId       = getSetting('bank_id', 'MBBank');
$bankAccount  = getSetting('bank_account', '0338996239');
$bankOwner    = getSetting('bank_owner', 'NGUYEN DUY THIEN');
$zaloContact  = getSetting('zalo_contact', '0338996239');
$downloadLink = getSetting('download_link', 'download.php');
$plans        = getPlans();

// Tự động lấy danh sách phát hành chính thức từ GitHub
$githubReleases = getGitHubReleases('nguyenduyhoen-bot/auto-clash-updet');
if (!empty($githubReleases[0]['tag_name'])) {
    $appVersion = $githubReleases[0]['tag_name'];
} else {
    $appVersion = getSetting('app_version', 'v1.1.3');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($siteName) ?></title>
  <link rel="icon" type="image/x-icon" href="favicon.ico?v=<?= time() ?>">
  <link rel="shortcut icon" href="favicon.ico?v=<?= time() ?>">
  <link rel="icon" type="image/png" href="assets/img/logo.png?v=<?= time() ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body>
  <!-- Background Ambient Effects -->
  <div class="bg-grid"></div>
  <div class="bg-glow bg-glow-1"></div>
  <div class="bg-glow bg-glow-2"></div>
  <div class="bg-glow bg-glow-3"></div>

  <!-- Navbar -->
  <header class="navbar">
    <div class="nav-container">
      <a href="#hero" class="nav-logo">
        <img src="assets/img/logo.png" alt="AutoClash Logo" class="site-logo-img">
        <div class="logo-text">Auto<span>Clash</span></div>
      </a>
      <nav class="nav-links">
        <a href="#pricing">Bảng giá Key</a>
        <a href="#download">Tải bộ cài</a>
        <a href="#guide">Hướng dẫn</a>
        <a href="#changelog">Nhật ký cập nhật</a>
      </nav>
      <div class="nav-actions">
        <a href="#pricing" class="btn btn-glow"><i class="fa-solid fa-bolt"></i> Mua Key Ngay</a>
      </div>
      <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle menu">
        <i class="fa-solid fa-bars"></i>
      </button>
    </div>
  </header>

  <!-- Mobile Menu Dropdown -->
  <div class="mobile-menu" id="mobileMenu">
    <a href="#pricing" class="mobile-link">Bảng giá Key</a>
    <a href="#download" class="mobile-link">Tải bộ cài</a>
    <a href="#guide" class="mobile-link">Hướng dẫn</a>
    <a href="#changelog" class="mobile-link">Nhật ký cập nhật</a>
    <div class="mobile-btns">
      <a href="#pricing" class="btn btn-glow w-full"><i class="fa-solid fa-bolt"></i> Mua Key Ngay</a>
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
          AutoClash mang lại giải pháp auto farm tài nguyên cực nhanh, tự động nâng cấp tường, thả quân kịch bản thông minh và hỗ trợ đa giả lập LDPlayer, BlueStacks, Nox mượt mà trên Windows.
        </p>
        <div class="hero-cta">
          <a href="#download" class="btn btn-primary btn-lg btn-hero-download">
            <i class="fa-solid fa-cloud-arrow-down"></i> Tải Về Miễn Phí (<?= htmlspecialchars($appVersion) ?>)
          </a>
          <a href="#pricing" class="btn btn-glow btn-lg">
            <i class="fa-solid fa-bolt"></i> Bảng Giá & Mua Key
          </a>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="section pricing-section">
      <div class="container">
        <div class="section-header">
          <div class="section-tag">Bảng Giá Tự Động</div>
          <h2 class="section-title">Chọn Gói Bản Quyền <span class="gradient-text">AutoClash</span> Phù Hợp</h2>
          <p class="section-subtitle">Thanh toán tự động 24/7 qua VietQR - Nhận key bản quyền ngay tức thì!</p>
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
      </div>
    </section>



    <!-- Download Section -->
    <section id="download" class="section download-section">
      <div class="container">
        <div class="download-wrapper">
          <div class="download-info">
            <div class="section-tag">Tải Về Trực Tiếp</div>
            <h2 class="section-title">Tải Phần Mềm <br><span class="gradient-text">AutoClash <?= htmlspecialchars($appVersion) ?> Chính Thức</span></h2>
            <p class="section-subtitle">
              Gói cài đặt đầy đủ 100% các tệp thực thi AutoClash, kết nối ADB, công cụ AI OCR và 5 thư mục cốt lõi. Tải về giải nén là chạy ngay không phát sinh lỗi!
            </p>
            <div class="download-specs">
              <div class="spec-item"><i class="fa-brands fa-windows"></i> Hỗ trợ Windows 10 / 11 (64-bit)</div>
              <div class="spec-item"><i class="fa-solid fa-box-archive"></i> Đầy đủ 5 thư mục: attack_script, cv2, numps, templates, tkl_data</div>
              <div class="spec-item"><i class="fa-solid fa-shield-halved"></i> 100% Sạch sẽ, không dính virus/mã độc</div>
            </div>
            <div class="download-actions">
              <a href="download.php" class="btn btn-primary btn-lg btn-download-zip">
                <i class="fa-solid fa-file-zipper"></i> Tải Bản Cài (.ZIP) - Khuyên Dùng
              </a>
              <a href="download.php?format=exe" class="btn btn-outline btn-lg">
                <i class="fa-brands fa-windows"></i> Tải Trình Cài (.EXE)
              </a>
              <a href="https://zalo.me/<?= htmlspecialchars($zaloContact) ?>" target="_blank" class="btn btn-outline btn-lg">
                <i class="fa-brands fa-whatsapp"></i> Hỗ Trợ Zalo Admin
              </a>
            </div>
            <div class="download-note">
              <i class="fa-solid fa-circle-check"></i>
              <span>Khuyên dùng bản <strong>.ZIP</strong>: Trình duyệt không chặn nhầm, chỉ cần giải nén là chơi được ngay!</span>
            </div>
          </div>
          <div class="download-graphic">
            <div class="installer-card">
              <img src="assets/img/logo.png" alt="AutoClash Icon" class="installer-icon">
              <h4>AutoClash_<?= htmlspecialchars($appVersion) ?>_Full</h4>
              <p>Phiên bản <?= htmlspecialchars($appVersion) ?> • Windows Edition</p>
              <div class="installer-steps">
                <div class="step-mini"><i class="fa-solid fa-check"></i> Đầy đủ 5 thư mục cốt lõi không thiếu file</div>
                <div class="step-mini"><i class="fa-solid fa-check"></i> Tự cấu hình môi trường ADB & Giả lập</div>
                <div class="step-mini"><i class="fa-solid fa-check"></i> Tự động kiểm tra và cập nhật từ xa</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Installation Guide Section -->
    <section id="guide" class="section guide-section">
      <div class="container">
        <div class="section-header">
          <div class="section-tag">Chỉ 3 Bước Đơn Giản</div>
          <h2 class="section-title">Hướng Dẫn Cài Đặt & Sử Dụng</h2>
          <p class="section-subtitle">Bất kỳ ai cũng có thể bắt đầu auto chỉ sau 2 phút cài đặt.</p>
        </div>

        <div class="guide-steps">
          <div class="guide-card">
            <div class="guide-num">01</div>
            <h3>Tải Bộ Cài Đặt</h3>
            <p>Tải file <code>.ZIP</code> hoặc <code>AutoClash_Setup.exe</code> về máy tính từ mục tải về phía trên.</p>
          </div>
          <div class="guide-card">
            <div class="guide-num">02</div>
            <h3>Giải Nén / Cài Đặt</h3>
            <p>Giải nén thư mục hoặc mở trình cài đặt Setup. Bộ cài đã chuẩn bị sẵn đầy đủ 5 thư mục cốt lõi.</p>
          </div>
          <div class="guide-card">
            <div class="guide-num">03</div>
            <h3>Khởi Chạy & Auto</h3>
            <p>Mở <code>AutoClash.exe</code>, nhập key bản quyền, kết nối giả lập (LDPlayer, BlueStacks) và cày cuốc!</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Changelog Section: Đồng bộ tự động từ GitHub Releases -->
    <section id="changelog" class="section changelog-section">
      <div class="container">
        <div class="section-header">
          <div class="section-tag"><i class="fa-brands fa-github"></i> Dữ Liệu Phát Hành GitHub</div>
          <h2 class="section-title">Nhật Ký Cập Nhật & <span class="gradient-text">Lịch Sử Phiên Bản</span></h2>
          <p class="section-subtitle">
            Toàn bộ các tính năng mới và bản vá lỗi được đồng bộ tự động từ kho lưu trữ GitHub 
            <a href="https://github.com/nguyenduyhoen-bot/auto-clash-updet/releases" target="_blank" style="color: #60a5fa; font-weight: 600; text-decoration: none;">
              nguyenduyhoen-bot/auto-clash-updet <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 11px;"></i>
            </a>
          </p>
        </div>

        <div class="changelog-timeline" id="changelogTimeline" data-repo="nguyenduyhoen-bot/auto-clash-updet">
          <?php if (empty($githubReleases)): ?>
            <div class="changelog-card current-release">
              <div class="changelog-badge-row">
                <span class="badge-latest"><i class="fa-solid fa-sparkles"></i> MỚI NHẤT</span>
                <span class="version-tag">Phiên bản v1.1.3</span>
                <span class="release-date"><i class="fa-regular fa-calendar-check"></i> 11/09/2026</span>
              </div>
              <h3 class="changelog-headline">AutoClash v1.1.3 - Delta Update</h3>
              <div class="changelog-grid">
                <div class="change-item">
                  <div class="change-icon"><i class="fa-solid fa-rocket"></i></div>
                  <div class="change-text">
                    <p>Cập nhật siêu tốc các file sửa lỗi, không cần tải lại toàn bộ gói cài đặt nặng.</p>
                  </div>
                </div>
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($githubReleases as $idx => $rel): ?>
              <?php 
                $isLatest = ($idx === 0);
                $tagName = $rel['tag_name'] ?? 'v1.0.0';
                $relName = !empty($rel['name']) ? $rel['name'] : "AutoClash {$tagName}";
                $pubDate = !empty($rel['published_at']) ? date('d/m/Y', strtotime($rel['published_at'])) : date('d/m/Y');
                $bodyText = $rel['body'] ?? '';
                $rawLines = explode("\n", str_replace("\r", "", $bodyText));
                
                $headline = $relName;
                $bullets = [];
                foreach ($rawLines as $line) {
                    $trimLine = trim($line);
                    if (empty($trimLine)) continue;
                    
                    if (str_starts_with($trimLine, '#')) {
                        $clean = trim(ltrim($trimLine, '# '));
                        if (!empty($clean) && $clean !== $tagName && $headline === $relName) {
                            $headline = $clean;
                        }
                    } elseif (str_starts_with($trimLine, '-') || str_starts_with($trimLine, '*')) {
                        $bullets[] = trim(ltrim($trimLine, '-* '));
                    } else {
                        $bullets[] = $trimLine;
                    }
                }
              ?>
              <div class="changelog-card <?= $isLatest ? 'current-release' : '' ?>">
                <div class="changelog-badge-row">
                  <?php if ($isLatest): ?>
                    <span class="badge-latest"><i class="fa-solid fa-sparkles"></i> MỚI NHẤT</span>
                  <?php else: ?>
                    <span class="badge-stable"><i class="fa-solid fa-shield-halved"></i> ỔN ĐỊNH</span>
                  <?php endif; ?>
                  <span class="version-tag"><?= htmlspecialchars($tagName) ?></span>
                  <span class="release-date"><i class="fa-regular fa-calendar-check"></i> <?= htmlspecialchars($pubDate) ?></span>
                </div>
                
                <h3 class="changelog-headline"><?= htmlspecialchars($headline) ?></h3>

                <?php if (!empty($bullets)): ?>
                  <div class="changelog-grid">
                    <?php foreach ($bullets as $bIdx => $bullet): ?>
                      <?php
                        $iconClass = 'fa-solid fa-circle-check';
                        if (stripos($bullet, 'fix') !== false || stripos($bullet, 'lỗi') !== false) {
                            $iconClass = 'fa-solid fa-wrench';
                        } elseif (stripos($bullet, 'exe') !== false || stripos($bullet, 'tải') !== false || stripos($bullet, 'cài') !== false) {
                            $iconClass = 'fa-solid fa-download';
                        } elseif (stripos($bullet, 'ico') !== false || stripos($bullet, 'icon') !== false || stripos($bullet, 'giao diện') !== false) {
                            $iconClass = 'fa-solid fa-palette';
                        } elseif (stripos($bullet, 'bản quyền') !== false || stripos($bullet, 'key') !== false) {
                            $iconClass = 'fa-solid fa-shield-halved';
                        } elseif (stripos($bullet, 'delta') !== false || stripos($bullet, 'nhanh') !== false || stripos($bullet, 'tốc') !== false) {
                            $iconClass = 'fa-solid fa-bolt';
                        }
                      ?>
                      <div class="change-item">
                        <div class="change-icon"><i class="<?= $iconClass ?>"></i></div>
                        <div class="change-text">
                          <p><?= htmlspecialchars($bullet) ?></p>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <?php if (!empty($rel['html_url'])): ?>
                  <div class="changelog-footer" style="margin-top: 18px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: flex-end;">
                    <a href="<?= htmlspecialchars($rel['html_url']) ?>" target="_blank" class="btn btn-outline btn-sm" style="font-size: 12px; padding: 6px 14px; gap: 6px; display: inline-flex; align-items: center;">
                      <i class="fa-brands fa-github"></i> Xem bản phát hành trên GitHub <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 10px;"></i>
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
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
                <span class="pay-label">Ngân hàng:</span>
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
          <img src="assets/img/logo.png" alt="AutoClash Logo" class="site-logo-img">
          <div class="logo-text">Auto<span>Clash</span></div>
        </div>
        <p>Phần mềm hỗ trợ chơi Clash of Clans hàng đầu cho game thủ Việt Nam.</p>
        <p style="margin-top: 10px; font-size: 0.85rem; color: #64748b;">
          Hỗ Trợ Zalo: <strong><?= htmlspecialchars($zaloContact) ?></strong> • MBBank: <strong><?= htmlspecialchars($bankAccount) ?></strong>
        </p>
      </div>
      <div class="footer-links">
        <h4>Liên Kết Nhanh</h4>
        <div class="f-links-grid">
          <a href="#pricing"><i class="fa-solid fa-angle-right"></i> Bảng Giá Key</a>
          <a href="#download"><i class="fa-solid fa-angle-right"></i> Tải Bộ Cài Đặt</a>
          <a href="#guide"><i class="fa-solid fa-angle-right"></i> Hướng Dẫn Cài Đặt</a>
        </div>
      </div>
      <div class="footer-admin-col">
        <h4>Hỗ Trợ Khách Hàng</h4>
        <p style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 12px;">Hệ thống thanh toán tự động 24/7. Hỗ trợ kỹ thuật cấu hình trực tiếp qua Zalo.</p>
        <a href="https://zalo.me/<?= htmlspecialchars($zaloContact) ?>" target="_blank" class="btn btn-outline btn-sm">
          <i class="fa-brands fa-whatsapp"></i> Nhắn Tin Zalo Admin
        </a>
      </div>
    </div>
    <div class="container footer-bottom">
      <p>&copy; <?= date('Y') ?> AutoClash Team. All rights reserved.</p>
      <p><a href="admin/login.php" target="_blank" class="f-admin-text"><i class="fa-solid fa-lock"></i> Đăng Nhập Quản Trị</a></p>
    </div>
  </footer>

  <script src="assets/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
