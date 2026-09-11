// ========================================================
// AutoClash - Frontend JavaScript
// Quản lý Modal Mua Key, VietQR & Tra Cứu Key Kích Hoạt
// ========================================================

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Menu Toggle
    const mobileToggle = document.getElementById('mobileToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    if (mobileToggle && mobileMenu) {
        mobileToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
        });

        document.querySelectorAll('.mobile-link').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
            });
        });
    }

    // 2. FAQ Accordion
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        if (question) {
            question.addEventListener('click', () => {
                const isActive = item.classList.contains('active');
                faqItems.forEach(i => i.classList.remove('active'));
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        }
    });

    // Tự động kiểm tra và đồng bộ Changelog mới nhất từ GitHub Releases
    initGitHubChangelogLive();

    // 3. Modal Elements
    const orderModal = document.getElementById('orderModal');
    const modalBackdrop = document.getElementById('modalBackdrop');
    const modalClose = document.getElementById('modalClose');
    const orderStep1 = document.getElementById('orderStep1');
    const orderStep2 = document.getElementById('orderStep2');
    const formPlanCode = document.getElementById('formPlanCode');
    const selectedPlanDisplay = document.getElementById('selectedPlanDisplay');
    const orderForm = document.getElementById('orderForm');

    let currentOrderCode = '';

    function openModal() {
        if (orderModal) {
            orderModal.style.display = 'flex';
            orderModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal() {
        if (orderModal) {
            orderModal.style.display = 'none';
            orderModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    window.closeBuyModal = closeModal;
    window.openBuyModal = function(btn) {
        if (!btn) return;
        const planCode = btn.dataset.plan || '1MONTH';
        const planName = btn.dataset.name || 'Gói 1 Tháng';
        const priceFormat = btn.dataset.priceFormat || '50.000đ';

        if (formPlanCode) formPlanCode.value = planCode;
        if (selectedPlanDisplay) {
            selectedPlanDisplay.innerHTML = `<strong>${planName}</strong> - <span style="color: #10b981; font-weight: 700;">${priceFormat}</span>`;
        }

        if (orderStep1) orderStep1.style.display = 'block';
        if (orderStep2) orderStep2.style.display = 'none';

        openModal();
    };

    if (modalClose) modalClose.addEventListener('click', closeModal);
    if (modalBackdrop) modalBackdrop.addEventListener('click', closeModal);

    // 4. Click "Mua Gói Này" buttons
    const buyBtns = document.querySelectorAll('.btn-buy-plan');
    buyBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const planCode = btn.dataset.plan;
            const planName = btn.dataset.name;
            const priceFormat = btn.dataset.priceFormat;

            if (formPlanCode) formPlanCode.value = planCode;
            if (selectedPlanDisplay) {
                selectedPlanDisplay.innerHTML = `<strong>${planName}</strong> - <span style="color: #10b981; font-weight: 700;">${priceFormat}</span>`;
            }

            // Reset view to Step 1
            if (orderStep1) orderStep1.style.display = 'block';
            if (orderStep2) orderStep2.style.display = 'none';

            openModal();
        });
    });

    // 5. Submit Order Form -> api/create_order.php
    if (orderForm) {
        orderForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btnSubmit = document.getElementById('btnSubmitOrder');
            const originalText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang tạo đơn hàng...';
            btnSubmit.disabled = true;

            const formData = new FormData(orderForm);

            try {
                const res = await fetch('api/create_order.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    currentOrderCode = data.order_code;
                    showToast('Tạo đơn hàng thành công!', 'success');

                    // Cập nhật Step 2
                    document.getElementById('resOrderCode').textContent = data.order_code;
                    document.getElementById('resAmount').textContent = data.amount_format;
                    document.getElementById('contentVal').textContent = data.order_code;
                    document.getElementById('qrImage').src = data.qr_url;

                    if (orderStep1) orderStep1.style.display = 'none';
                    if (orderStep2) orderStep2.style.display = 'block';
                } else {
                    showToast(data.message || 'Lỗi khi tạo đơn hàng', 'error');
                }
            } catch (err) {
                showToast('Lỗi kết nối máy chủ: ' + err.message, 'error');
            } finally {
                btnSubmit.innerHTML = originalText;
                btnSubmit.disabled = false;
            }
        });
    }

    // 6. Check Order in Modal
    const btnCheckOrderNow = document.getElementById('btnCheckOrderNow');
    if (btnCheckOrderNow) {
        btnCheckOrderNow.addEventListener('click', () => {
            if (!currentOrderCode) return;
            checkOrderStatus(currentOrderCode);
        });
    }

    // 7. Order Lookup Form (Tra Cứu Key)
    const lookupForm = document.getElementById('lookupForm');
    const lookupQuery = document.getElementById('lookupQuery');
    const lookupResult = document.getElementById('lookupResult');

    if (lookupForm) {
        lookupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const query = lookupQuery.value.trim();
            if (!query) return;

            const btnLookup = document.getElementById('btnLookup');
            const originalText = btnLookup.innerHTML;
            btnLookup.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang tìm...';
            btnLookup.disabled = true;

            await checkOrderStatus(query);

            btnLookup.innerHTML = originalText;
            btnLookup.disabled = false;
        });
    }

    async function checkOrderStatus(query) {
        if (!lookupResult) return;
        lookupResult.style.display = 'block';
        lookupResult.innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang kiểm tra hệ thống...</div>';

        try {
            const res = await fetch(`api/check_order.php?query=${encodeURIComponent(query)}`);
            const data = await res.json();

            if (data.success && data.orders && data.orders.length > 0) {
                let html = '<div class="lookup-cards">';
                data.orders.forEach(ord => {
                    const isPaid = (ord.payment_status === 'paid');
                    const isCancelled = (ord.payment_status === 'cancelled');

                    html += `
                        <div class="lookup-card ${isPaid ? 'paid' : ''}">
                            <div class="lookup-card-header">
                                <div>
                                    <div class="l-code">Mã đơn: <strong>#${ord.order_code}</strong></div>
                                    <div class="l-plan">${ord.plan_name} • ${ord.amount_format}</div>
                                </div>
                                <div class="l-status">
                                    ${isPaid ? '<span class="badge badge-paid"><i class="fa-solid fa-check"></i> ĐÃ THANH TOÁN</span>' :
                                      isCancelled ? '<span class="badge badge-cancelled"><i class="fa-solid fa-ban"></i> ĐÃ HỦY</span>' :
                                      '<span class="badge badge-pending"><i class="fa-solid fa-clock"></i> ĐANG CHỜ DUYỆT</span>'}
                                </div>
                            </div>
                            <div class="lookup-card-body">
                                <div class="l-info-row">
                                    <span>Khách hàng: <strong>${ord.customer_name}</strong> (${ord.customer_phone})</span>
                                    <span>Thời gian tạo: ${ord.created_at}</span>
                                </div>
                                ${isPaid && ord.license_key ? `
                                    <div class="key-display-box">
                                        <div class="k-label"><i class="fa-solid fa-key"></i> KEY BẢN QUYỀN CỦA BẠN:</div>
                                        <div class="k-code-row">
                                            <input type="text" readonly value="${ord.license_key}" class="k-input" id="key_${ord.order_code}">
                                            <button type="button" class="btn btn-copy" onclick="copyKey('key_${ord.order_code}')">
                                                <i class="fa-regular fa-copy"></i> Sao chép
                                            </button>
                                        </div>
                                        <div style="margin-top: 10px; display: flex; gap: 8px; flex-wrap: wrap;">
                                            <button type="button" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px; cursor: pointer; border-radius: 6px;" onclick="goToActivate('${ord.license_key}')">
                                                ⚡ Kích Hoạt Key Vào Máy Tính (HWID)
                                            </button>
                                        </div>
                                        <div style="margin-top: 8px; font-size: 12.5px; color: #fbbf24; line-height: 1.5;">
                                            ⚠️ <strong>LƯU Ý QUAN TRỌNG:</strong> Mã <code>${ord.license_key}</code> là mã đặt mua trên web. Bạn cần bấm nút <strong>"Kích Hoạt Key Vào Máy Tính"</strong> ở trên, điền <strong>Mã Máy (HWID)</strong> hiển thị trên tool AutoClash để nhận <strong>Mã Kích Hoạt / File license.key</strong> bỏ vào tool (tránh lỗi <em>Incorrect padding</em>)!
                                        </div>
                                    </div>
                                ` : !isPaid && !isCancelled ? `
                                    <div class="pending-notice">
                                        <i class="fa-solid fa-hourglass-half"></i> 
                                        Đơn hàng đang chờ thanh toán hoặc đang được Admin duyệt. Bạn vui lòng chuyển khoản đúng mã <strong>${ord.order_code}</strong> hoặc nhắn tin Zalo Admin để được kích hoạt ngay!
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                lookupResult.innerHTML = html;
                lookupResult.scrollIntoView({ behavior: 'smooth' });
            } else {
                lookupResult.innerHTML = `
                    <div class="lookup-empty">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <p>${data.message || 'Không tìm thấy thông tin đơn hàng.'}</p>
                    </div>
                `;
            }
        } catch (err) {
            lookupResult.innerHTML = `<div class="lookup-empty"><i class="fa-solid fa-triangle-exclamation"></i> Lỗi kết nối: ${err.message}</div>`;
        }
    }

    // 8. Online Key Activation & Verification Form
    const activateForm = document.getElementById('activateForm');
    const activateKey = document.getElementById('activateKey');
    const activateHwid = document.getElementById('activateHwid');
    const activateResult = document.getElementById('activateResult');
    const btnActivateKey = document.getElementById('btnActivateKey');
    const btnCheckKeyStatus = document.getElementById('btnCheckKeyStatus');

    if (activateForm) {
        activateForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const key = activateKey ? activateKey.value.trim() : '';
            const hwid = activateHwid ? activateHwid.value.trim() : '';

            if (!key) {
                showToast('Vui lòng nhập mã License Key!', 'error');
                if (activateKey) activateKey.focus();
                return;
            }

            if (!hwid) {
                showToast('Mỗi 1 Key gắn với 1 Mã Máy Tính (HWID)! Vui lòng dán mã HWID từ AutoClash.exe để kích hoạt.', 'error');
                if (activateHwid) activateHwid.focus();
                return;
            }

            await executeKeyAction(key, hwid, false);
        });
    }

    if (btnCheckKeyStatus) {
        btnCheckKeyStatus.addEventListener('click', async () => {
            const key = activateKey ? activateKey.value.trim() : '';
            const hwid = activateHwid ? activateHwid.value.trim() : '';

            if (!key) {
                showToast('Vui lòng nhập mã License Key để kiểm tra!', 'error');
                if (activateKey) activateKey.focus();
                return;
            }

            await executeKeyAction(key, hwid, true);
        });
    }

    async function executeKeyAction(key, hwid, isCheckOnly) {
        if (!activateResult) return;

        activateResult.style.display = 'block';
        activateResult.innerHTML = '<div class="activate-loading"><i class="fa-solid fa-spinner fa-spin"></i> Đang kết nối máy chủ xác thực bản quyền...</div>';

        const btn = isCheckOnly ? btnCheckKeyStatus : btnActivateKey;
        const oldText = btn ? btn.innerHTML : '';
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';
            btn.disabled = true;
        }

        try {
            const res = await fetch('api/activate_key.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    license_key: key,
                    hwid: hwid,
                    action: isCheckOnly ? 'info' : 'activate'
                })
            });

            const data = await res.json();

            if (data.success) {
                if (data.status === 'active') {
                    // Kích hoạt thành công vào đúng 1 máy
                    showToast('Kích hoạt Key bản quyền thành công!', 'success');
                    activateResult.innerHTML = `
                        <div class="act-card act-success">
                            <div class="act-header">
                                <div class="act-icon"><i class="fa-solid fa-circle-check"></i></div>
                                <div>
                                    <h4 class="act-title">🎉 KÍCH HOẠT THÀNH CÔNG (1 KEY / 1 MÁY)!</h4>
                                    <p class="act-subtitle">Mã Key này đã được gắn cố định với Mã Máy Tính (HWID) của bạn.</p>
                                </div>
                                <span class="act-badge badge-active"><i class="fa-solid fa-shield-halved"></i> ĐANG HOẠT ĐỘNG</span>
                            </div>
                            <div class="act-body">
                                <div class="act-row">
                                    <span class="act-label">Mã Key:</span>
                                    <span class="act-val code-val">${data.license_key}</span>
                                </div>
                                <div class="act-row">
                                    <span class="act-label">Gói bản quyền:</span>
                                    <span class="act-val plan-val">${data.plan_name}</span>
                                </div>
                                <div class="act-row">
                                    <span class="act-label">Mã máy đã gắn (HWID):</span>
                                    <span class="act-val code-val" style="color: #34d399;">${data.hwid}</span>
                                </div>
                                <div class="act-row">
                                    <span class="act-label">Hạn sử dụng:</span>
                                    <span class="act-val exp-val">${data.expires_at}</span>
                                </div>
                                ${data.license_code ? `
                                <div style="margin-top: 15px; padding: 15px; background: rgba(0,0,0,0.25); border-radius: 8px; border: 1px dashed rgba(79, 140, 255, 0.4);">
                                    <div style="font-weight: bold; margin-bottom: 8px; color: #4f8cff; display: flex; align-items: center; gap: 6px;">
                                        <i class="fa-solid fa-key"></i> Mã Kích Hoạt Phần Mềm (Dán vào ô License Key trên AutoClash):
                                    </div>
                                    <textarea readonly id="txtLicenseCode" style="width:100%; height:75px; background:#111216; color:#93c5fd; font-family:monospace; font-size:12px; border:1px solid #334155; border-radius:6px; padding:8px; resize:none;" onclick="this.select()">${data.license_code}</textarea>
                                    <div style="display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap;">
                                        <button type="button" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px; cursor: pointer;" onclick="copyLicenseCode()">
                                            <i class="fa-regular fa-copy"></i> 📋 Sao Chép Mã Kích Hoạt Vào Tool
                                        </button>
                                        <a href="${data.download_url}" class="btn btn-secondary" style="padding: 8px 16px; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                                            <i class="fa-solid fa-download"></i> 📥 Tải File license.key
                                        </a>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                            <div class="act-footer">
                                <i class="fa-solid fa-circle-info"></i>
                                <span><strong>Hướng dẫn:</strong> Bấm <em>"Sao Chép Mã Kích Hoạt"</em> rồi dán vào ô <strong>License Key</strong> trên phần mềm AutoClash rồi bấm <strong>[Vào tool]</strong> (hoặc tải file <code>license.key</code> bỏ vào thư mục AutoClash).</span>
                            </div>
                        </div>
                    `;
                } else if (data.status === 'available') {
                    // Key còn trống, hợp lệ
                    showToast('Key hợp lệ và sẵn sàng liên kết với máy tính!', 'success');
                    activateResult.innerHTML = `
                        <div class="act-card act-available">
                            <div class="act-header">
                                <div class="act-icon" style="color: #3b82f6;"><i class="fa-solid fa-circle-info"></i></div>
                                <div>
                                    <h4 class="act-title">MÃ KEY HỢP LỆ & SẴN SÀNG KÍCH HOẠT</h4>
                                    <p class="act-subtitle">${data.message}</p>
                                </div>
                                <span class="act-badge badge-avail"><i class="fa-solid fa-box-open"></i> CHƯA SỬ DỤNG</span>
                            </div>
                            <div class="act-body">
                                <div class="act-row">
                                    <span class="act-label">Mã Key:</span>
                                    <span class="act-val code-val">${data.license_key}</span>
                                </div>
                                <div class="act-row">
                                    <span class="act-label">Gói bản quyền:</span>
                                    <span class="act-val plan-val">${data.plan_name}</span>
                                </div>
                                <div class="act-row">
                                    <span class="act-label">Thời hạn:</span>
                                    <span class="act-val">${data.duration_days > 0 ? data.duration_days + ' Ngày' : 'Vĩnh Viễn'}</span>
                                </div>
                            </div>
                            <div class="act-footer" style="background: rgba(59, 130, 246, 0.1); border-color: rgba(59, 130, 246, 0.3);">
                                <i class="fa-solid fa-arrow-up"></i>
                                <span>Để kích hoạt, hãy dán <strong>Mã Máy Tính (HWID)</strong> ở ô phía trên rồi bấm <strong>Kích Hoạt Key Vào Máy Này</strong>!</span>
                            </div>
                        </div>
                    `;
                } else {
                    // Thông tin key đang dùng
                    activateResult.innerHTML = `
                        <div class="act-card act-info">
                            <div class="act-header">
                                <div class="act-icon"><i class="fa-solid fa-circle-info"></i></div>
                                <div>
                                    <h4 class="act-title">THÔNG TIN BẢN QUYỀN ĐÃ KÍCH HOẠT</h4>
                                    <p class="act-subtitle">${data.message}</p>
                                </div>
                                <span class="act-badge badge-used">${data.status === 'used' ? 'ĐÃ GẮN VỚI MÁY' : 'HẾT HẠN'}</span>
                            </div>
                            <div class="act-body">
                                <div class="act-row">
                                    <span class="act-label">Mã Key:</span>
                                    <span class="act-val code-val">${data.license_key}</span>
                                </div>
                                <div class="act-row">
                                    <span class="act-label">Gói bản quyền:</span>
                                    <span class="act-val plan-val">${data.plan_name}</span>
                                </div>
                                ${data.user_device_id ? `
                                <div class="act-row">
                                    <span class="act-label">Mã máy đã gắn (HWID):</span>
                                    <span class="act-val code-val">${data.user_device_id}</span>
                                </div>` : ''}
                                ${data.expires_at ? `
                                <div class="act-row">
                                    <span class="act-label">Hạn sử dụng:</span>
                                    <span class="act-val exp-val">${data.expires_at}</span>
                                </div>` : ''}
                            </div>
                        </div>
                    `;
                }
            } else {
                showToast(data.message || 'Xác thực thất bại!', 'error');
                activateResult.innerHTML = `
                    <div class="act-card act-error">
                        <div class="act-header">
                            <div class="act-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                            <div>
                                <h4 class="act-title">KHÔNG THỂ KÍCH HOẠT</h4>
                                <p class="act-subtitle">${data.message || 'Mã Key không hợp lệ hoặc đã xảy ra lỗi.'}</p>
                            </div>
                        </div>
                        <div class="act-body" style="font-size: 13.5px; color: var(--text-sub); line-height: 1.5;">
                            <p><i class="fa-solid fa-circle-exclamation" style="color: #ef4444;"></i> Lưu ý: <strong>Mỗi 1 Key chỉ gắn với 1 Mã Máy Tính (HWID) duy nhất</strong>. Nếu bạn vừa thay đổi phần cứng hoặc cài lại Windows, vui lòng liên hệ Admin qua Zalo để được hỗ trợ chuyển máy.</p>
                        </div>
                    </div>
                `;
            }

            activateResult.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        } catch (err) {
            showToast('Lỗi kết nối máy chủ: ' + err.message, 'error');
            activateResult.innerHTML = `
                <div class="act-card act-error">
                    <div class="act-header">
                        <div class="act-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <div>
                            <h4 class="act-title">LỖI KẾT NỐI MÁY CHỦ</h4>
                            <p class="act-subtitle">${err.message}</p>
                        </div>
                    </div>
                </div>
            `;
        } finally {
            if (btn) {
                btn.innerHTML = oldText;
                btn.disabled = false;
            }
        }
    }
});

// Toast notification function
function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    if (!toast) return;

    toast.textContent = message;
    toast.className = 'toast active ' + type;

    setTimeout(() => {
        toast.className = 'toast';
    }, 3500);
}

// Global helper: Copy key
window.copyKey = function(elementId) {
    const input = document.getElementById(elementId);
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        showToast('Đã sao chép License Key vào bộ nhớ tạm!', 'success');
    }).catch(() => {
        document.execCommand('copy');
        showToast('Đã sao chép License Key!', 'success');
    });
};

// Global helper: Switch License Hub Tab
window.switchLicenseTab = function(tab) {
    const tabBtnActivate = document.getElementById('tabBtnActivate');
    const tabBtnLookup = document.getElementById('tabBtnLookup');
    const paneActivate = document.getElementById('paneActivate');
    const paneLookup = document.getElementById('paneLookup');

    if (tab === 'activate') {
        if (tabBtnActivate) tabBtnActivate.classList.add('active');
        if (tabBtnLookup) tabBtnLookup.classList.remove('active');
        if (paneActivate) paneActivate.style.display = 'block';
        if (paneLookup) paneLookup.style.display = 'none';
    } else {
        if (tabBtnLookup) tabBtnLookup.classList.add('active');
        if (tabBtnActivate) tabBtnActivate.classList.remove('active');
        if (paneLookup) paneLookup.style.display = 'block';
        if (paneActivate) paneActivate.style.display = 'none';
    }
};

// Global helper: Go to activate section with key filled
window.goToActivate = function(key) {
    if (typeof window.switchLicenseTab === 'function') {
        window.switchLicenseTab('activate');
    }
    const actKey = document.getElementById('activateKey');
    const actHwid = document.getElementById('activateHwid');
    const actSection = document.getElementById('license-hub') || document.getElementById('activate');
    if (actKey) actKey.value = key;
    if (actSection) actSection.scrollIntoView({ behavior: 'smooth' });
    if (actHwid) {
        setTimeout(() => actHwid.focus(), 600);
    }
    showToast('Đã điền mã Key vào ô kích hoạt. Vui lòng dán Mã Máy (HWID) từ tool!', 'info');
};

// Global helper: Copy content
window.copyContent = function() {
    const content = document.getElementById('contentVal');
    if (!content) return;
    navigator.clipboard.writeText(content.textContent.trim()).then(() => {
        showToast('Đã sao chép nội dung chuyển khoản: ' + content.textContent.trim(), 'success');
    });
};

// Global helper: Copy text
window.copyText = function(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Đã sao chép: ' + text, 'success');
    });
};

// Global helper: Copy license code for AutoClash tool
window.copyLicenseCode = function() {
    const el = document.getElementById('txtLicenseCode');
    if (!el) return;
    el.select();
    el.setSelectionRange(0, 999999);
    navigator.clipboard.writeText(el.value).then(() => {
        showToast('Đã sao chép Mã Kích Hoạt! Hãy dán vào ô License Key trên AutoClash.', 'success');
    }).catch(() => {
        document.execCommand('copy');
        showToast('Đã sao chép Mã Kích Hoạt!', 'success');
    });
};

// Tự động kiểm tra và nạp Changelog trực tiếp từ GitHub Releases
async function initGitHubChangelogLive() {
    const timeline = document.getElementById('changelogTimeline');
    if (!timeline) return;

    const repo = timeline.dataset.repo || 'nguyenduyhoen-bot/auto-clash-updet';
    try {
        const res = await fetch(`https://api.github.com/repos/${repo}/releases`, {
            headers: { 'Accept': 'application/vnd.github.v3+json' }
        });
        if (!res.ok) return;
        const releases = await res.json();
        if (!Array.isArray(releases) || releases.length === 0) return;

        let html = '';
        releases.forEach((rel, idx) => {
            const isLatest = (idx === 0);
            const tagName = rel.tag_name || 'v1.0.0';
            const relName = rel.name || `AutoClash ${tagName}`;
            let pubDate = '';
            if (rel.published_at) {
                const d = new Date(rel.published_at);
                pubDate = d.toLocaleDateString('vi-VN');
            }

            const body = rel.body || '';
            const lines = body.split(/\r?\n/).map(l => l.trim()).filter(Boolean);
            let headline = relName;
            const bullets = [];

            lines.forEach(l => {
                if (l.startsWith('#')) {
                    const clean = l.replace(/^[#\s]+/, '').trim();
                    if (clean && clean !== tagName && headline === relName) {
                        headline = clean;
                    }
                } else if (l.startsWith('-') || l.startsWith('*')) {
                    bullets.push(l.replace(/^[-*\s]+/, '').trim());
                } else {
                    bullets.push(l);
                }
            });

            const badgeHtml = isLatest
                ? `<span class="badge-latest"><i class="fa-solid fa-sparkles"></i> MỚI NHẤT</span>`
                : `<span class="badge-stable"><i class="fa-solid fa-shield-halved"></i> ỔN ĐỊNH</span>`;

            let bulletsHtml = '';
            if (bullets.length > 0) {
                bulletsHtml = '<div class="changelog-grid">' + bullets.map(b => {
                    let icon = 'fa-solid fa-circle-check';
                    const lb = b.toLowerCase();
                    if (lb.includes('fix') || lb.includes('lỗi')) icon = 'fa-solid fa-wrench';
                    else if (lb.includes('exe') || lb.includes('tải') || lb.includes('cài')) icon = 'fa-solid fa-download';
                    else if (lb.includes('ico') || lb.includes('icon') || lb.includes('giao diện')) icon = 'fa-solid fa-palette';
                    else if (lb.includes('bản quyền') || lb.includes('key')) icon = 'fa-solid fa-shield-halved';
                    else if (lb.includes('delta') || lb.includes('nhanh') || lb.includes('tốc')) icon = 'fa-solid fa-bolt';

                    return `
                        <div class="change-item">
                            <div class="change-icon"><i class="${icon}"></i></div>
                            <div class="change-text"><p>${escapeHtml(b)}</p></div>
                        </div>
                    `;
                }).join('') + '</div>';
            }

            const footerHtml = rel.html_url ? `
                <div class="changelog-footer" style="margin-top: 18px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: flex-end;">
                    <a href="${rel.html_url}" target="_blank" class="btn btn-outline btn-sm" style="font-size: 12px; padding: 6px 14px; gap: 6px; display: inline-flex; align-items: center;">
                        <i class="fa-brands fa-github"></i> Xem bản phát hành trên GitHub <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 10px;"></i>
                    </a>
                </div>
            ` : '';

            html += `
                <div class="changelog-card ${isLatest ? 'current-release' : ''}">
                    <div class="changelog-badge-row">
                        ${badgeHtml}
                        <span class="version-tag">${escapeHtml(tagName)}</span>
                        <span class="release-date"><i class="fa-regular fa-calendar-check"></i> ${pubDate}</span>
                    </div>
                    <h3 class="changelog-headline">${escapeHtml(headline)}</h3>
                    ${bulletsHtml}
                    ${footerHtml}
                </div>
            `;
        });

        timeline.innerHTML = html;
    } catch (err) {
        // Fallback to PHP rendered HTML
    }
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
