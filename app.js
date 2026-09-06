// =========================================================
// AUTOCLASH WEB APPLICATION INTERACTIONS (JS)
// =========================================================

document.addEventListener('DOMContentLoaded', () => {
  // 1. Mobile Menu Toggle
  const mobileToggle = document.getElementById('mobileToggle');
  const mobileMenu = document.getElementById('mobileMenu');
  const mobileLinks = document.querySelectorAll('.mobile-link');

  if (mobileToggle && mobileMenu) {
    mobileToggle.addEventListener('click', () => {
      mobileMenu.classList.toggle('active');
    });

    mobileLinks.forEach(link => {
      link.addEventListener('click', () => {
        mobileMenu.classList.remove('active');
      });
    });
  }

  // 2. FAQ Accordion Toggle
  const faqItems = document.querySelectorAll('.faq-item');
  faqItems.forEach(item => {
    const question = item.querySelector('.faq-question');
    question.addEventListener('click', () => {
      const isActive = item.classList.contains('active');
      faqItems.forEach(i => i.classList.remove('active'));
      if (!isActive) {
        item.classList.add('active');
      }
    });
  });

  // 3. Purchase Key Modal & VietQR Dynamic Generation
  const buyModal = document.getElementById('buyModal');
  const modalClose = document.getElementById('modalClose');
  const buyButtons = document.querySelectorAll('.buy-key-btn');
  const modalPlanTitle = document.getElementById('modalPlanTitle');
  const modalPlanName = document.getElementById('modalPlanName');
  const modalPlanPrice = document.getElementById('modalPlanPrice');
  const qrImage = document.getElementById('qrImage');
  const transferContent = document.getElementById('transferContent');

  const BANK_ACCOUNT = '0338996239';
  const BANK_BIN = '970422'; // MBBank

  buyButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const planName = btn.getAttribute('data-plan') || 'Gói 1 Tháng';
      const price = parseInt(btn.getAttribute('data-price') || '50000');
      
      const formattedPrice = new Intl.NumberFormat('vi-VN').format(price) + ' VNĐ';
      const contentStr = 'AUTOCLASH ' + planName.toUpperCase().replace(/\s+/g, '');

      modalPlanTitle.textContent = `Kích Hoạt ${planName}`;
      modalPlanName.textContent = planName;
      modalPlanPrice.textContent = formattedPrice;
      transferContent.textContent = contentStr;

      // Sinh mã VietQR chuẩn xác
      const qrUrl = `https://api.vietqr.io/image/${BANK_BIN}-${BANK_ACCOUNT}-compact2.png?amount=${price}&addInfo=${encodeURIComponent(contentStr)}`;
      qrImage.src = qrUrl;

      buyModal.classList.add('active');
    });
  });

  if (modalClose) {
    modalClose.addEventListener('click', () => {
      buyModal.classList.remove('active');
    });
  }

  window.addEventListener('click', (e) => {
    if (e.target === buyModal) {
      buyModal.classList.remove('active');
    }
  });

  // 4. Clipboard Copy with Toast
  const toast = document.getElementById('toast');
  const btnCopyAcc = document.getElementById('btnCopyAcc');
  const btnCopyContent = document.getElementById('btnCopyContent');

  function showToast(msg) {
    if (!toast) return;
    toast.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => {
      toast.classList.remove('show');
    }, 2500);
  }

  function copyToClipboard(text, successMsg) {
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(() => {
        showToast(successMsg);
      }).catch(() => {
        fallbackCopyText(text, successMsg);
      });
    } else {
      fallbackCopyText(text, successMsg);
    }
  }

  function fallbackCopyText(text, successMsg) {
    const tempInput = document.createElement('textarea');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    try {
      document.execCommand('copy');
      showToast(successMsg);
    } catch (err) {
      alert('Không thể sao chép: ' + text);
    }
    document.body.removeChild(tempInput);
  }

  if (btnCopyAcc) {
    btnCopyAcc.addEventListener('click', () => {
      copyToClipboard(BANK_ACCOUNT, 'Đã sao chép số tài khoản MBBank: ' + BANK_ACCOUNT);
    });
  }

  if (btnCopyContent) {
    btnCopyContent.addEventListener('click', () => {
      const content = transferContent.textContent.trim();
      copyToClipboard(content, 'Đã sao chép nội dung chuyển khoản: ' + content);
    });
  }
});
