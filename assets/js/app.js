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
            orderModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal() {
        if (orderModal) {
            orderModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

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
