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
                return;
            }

            if (!hwid) {
                showToast('Vui lòng nhập Mã máy (HWID) từ AutoClash.exe để kích hoạt!', 'error');
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
                    hwid: hwid
                })
            });

            const data = await res.json();

            if (data.success) {
                if (data.status === 'active') {
                    // Kích hoạt thành công
                    showToast('Kích hoạt Key bản quyền thành công!', 'success');
                    activateResult.innerHTML = `
                        <div class="act-card act-success">
                            <div class="act-header">
                                <div class="act-icon"><i class="fa-solid fa-circle-check"></i></div>
                                <div>
                                    <h4 class="act-title">🎉 KÍCH HOẠT THÀNH CÔNG!</h4>
                                    <p class="act-subtitle">Bản quyền của bạn đã được liên kết với máy tính.</p>
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
                                    <span class="act-label">Mã máy (HWID):</span>
                                    <span class="act-val code-val">${data.hwid}</span>
                                </div>
                                <div class="act-row">
                                    <span class="act-label">Hạn sử dụng:</span>
                                    <span class="act-val exp-val">${data.expires_at}</span>
                                </div>
                            </div>
                            <div class="act-footer">
                                <i class="fa-solid fa-circle-info"></i>
                                <span>Bây giờ bạn chỉ cần mở <strong>AutoClash.exe</strong> trên máy tính để bắt đầu cày cuốc tự động ngay!</span>
                            </div>
                        </div>
                    `;
                } else if (data.status === 'available') {
                    // Key còn trống, hợp lệ
                    showToast('Key hợp lệ và sẵn sàng kích hoạt!', 'success');
                    activateResult.innerHTML = `
                        <div class="act-card act-available">
                            <div class="act-header">
                                <div class="act-icon" style="color: #3b82f6;"><i class="fa-solid fa-circle-info"></i></div>
                                <div>
                                    <h4 class="act-title">MÃ KEY HỢP LỆ & SẴN SÀNG</h4>
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
                                <span>Hãy nhập thêm <strong>Mã Máy (HWID)</strong> ở ô phía trên rồi bấm <strong>Kích Hoạt Key Ngay</strong> để hoàn tất!</span>
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
                                    <h4 class="act-title">THÔNG TIN BẢN QUYỀN</h4>
                                    <p class="act-subtitle">${data.message}</p>
                                </div>
                                <span class="act-badge badge-used">${data.status === 'used' ? 'ĐÃ KÍCH HOẠT' : 'HẾT HẠN'}</span>
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
                                    <span class="act-label">Mã máy (HWID):</span>
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
                showToast(data.message || 'Kích hoạt thất bại!', 'error');
                activateResult.innerHTML = `
                    <div class="act-card act-error">
                        <div class="act-header">
                            <div class="act-icon"><i class="fa-solid fa-circle-xmark"></i></div>
                            <div>
                                <h4 class="act-title">XÁC THỰC THẤT BẠI</h4>
                                <p class="act-subtitle">${data.message || 'Mã Key không hợp lệ hoặc đã xảy ra lỗi.'}</p>
                            </div>
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
