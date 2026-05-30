window.getCsrfToken = function() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') || '' : '';
};

window.submitPostUrl = function(url) {
    if (!url) return;

    var form = document.createElement('form');
    form.method = 'post';
    form.action = url;

    var token = window.getCsrfToken ? window.getCsrfToken() : '';
    if (token) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'csrf_token';
        input.value = token;
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
};

document.addEventListener('click', function(e) {
    var link = e.target.closest('[data-post-link]');
    if (!link) return;
    e.preventDefault();
    window.submitPostUrl(link.getAttribute('href') || link.dataset.postUrl);
});

(function() {
    if (typeof window.fetch !== 'function') return;

    var nativeFetch = window.fetch.bind(window);
    window.fetch = function(input, init) {
        init = init || {};
        var method = (init.method || (input && input.method) || 'GET').toUpperCase();
        var token = window.getCsrfToken ? window.getCsrfToken() : '';

        if (token && method !== 'GET' && method !== 'HEAD' && method !== 'OPTIONS') {
            if (init.headers instanceof Headers) {
                init.headers.set('X-CSRF-Token', token);
            } else {
                init.headers = Object.assign({}, init.headers || {}, {
                    'X-CSRF-Token': token
                });
            }
        }

        return nativeFetch(input, init);
    };
})();

var messageModal = document.getElementById('message-modal');
var messageModalFooter = document.getElementById('message-modal-footer');

window.showMessageModal = function(title, content, onClose) {
    if (!messageModal) return;
    document.getElementById('message-modal-title').textContent = title || '提示';
    document.getElementById('message-modal-content').textContent = content || '';
    messageModal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    var confirmBtn = messageModal.querySelector('.confirm-btn');
    var cancelBtn = messageModal.querySelector('.cancel-btn');

    if (onClose) {
        if (confirmBtn) {
            confirmBtn.textContent = '确定';
            confirmBtn.onclick = function() {
                closeMessageModal();
                if (typeof onClose === 'function') onClose();
            };
        }
        if (cancelBtn) cancelBtn.classList.remove('hidden');
        if (confirmBtn) confirmBtn.classList.remove('hidden');
        if (messageModalFooter) messageModalFooter.classList.remove('hidden');
    } else {
        if (confirmBtn) {
            confirmBtn.textContent = '关闭';
            confirmBtn.onclick = closeMessageModal;
            confirmBtn.classList.remove('hidden');
        }
        if (cancelBtn) cancelBtn.classList.add('hidden');
        if (messageModalFooter) messageModalFooter.classList.remove('hidden');
    }
};

window.showConfirmModal = function(title, content, onConfirm) {
    if (!messageModal) return;
    document.getElementById('message-modal-title').textContent = title || '确认';
    document.getElementById('message-modal-content').textContent = content || '';
    messageModal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    var confirmBtn = messageModal.querySelector('.confirm-btn');
    var cancelBtn = messageModal.querySelector('.cancel-btn');

    if (confirmBtn) {
        confirmBtn.textContent = '确定';
        confirmBtn.classList.remove('hidden');
        confirmBtn.onclick = function() {
            closeMessageModal();
            if (typeof onConfirm === 'function') onConfirm();
        };
    }
    if (cancelBtn) cancelBtn.classList.remove('hidden');
    if (messageModalFooter) messageModalFooter.classList.remove('hidden');
};

window.closeMessageModal = function() {
    if (!messageModal) return;
    messageModal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
};

if (messageModal) {
    messageModal.addEventListener('click', function(e) {
        if (e.target === messageModal) {
            closeMessageModal();
        }
    });
}

(function() {
    var tipTimer = null;

    window.showTip = function(content, type) {
        if (!content) return;

        var tip = document.getElementById('site-tip');
        if (!tip) {
            tip = document.createElement('div');
            tip.id = 'site-tip';
            tip.innerHTML = '<div id="site-tip-panel"></div>';
            document.body.appendChild(tip);
        }

        var panel = document.getElementById('site-tip-panel');
        panel.textContent = content;
        panel.className = type === 'success' ? 'site-tip-panel site-tip-success' : 'site-tip-panel site-tip-danger';

        tip.style.display = 'block';
        requestAnimationFrame(function() {
            tip.style.opacity = '1';
            tip.style.transform = 'translateX(-50%) translateY(0)';
        });

        if (tipTimer) clearTimeout(tipTimer);
        tipTimer = setTimeout(function() {
            tip.style.opacity = '0';
            tip.style.transform = 'translateX(-50%) translateY(-20px)';
            setTimeout(function() {
                tip.style.display = 'none';
            }, 300);
        }, 3000);
    };

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            showTip(alert.textContent.trim(), alert.classList.contains('alert-success') ? 'success' : 'danger');
            alert.remove();
        });
    });
})();

(function() {
    var toast = document.getElementById('credit-toast');
    var toastText = document.getElementById('credit-toast-text');
    var toastTimer = null;

    window.showCreditToast = function(amount) {
        if (!toast || !toastText || !amount) return;

        var isPositive = amount > 0;
        var prefix = isPositive ? '+' : '';
        var color = isPositive ? '#ffffff' : '#ffffff';
        var bg = isPositive
            ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)'
            : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
        var shadow = isPositive
            ? '0 10px 40px rgba(16, 185, 129, 0.3), 0 2px 8px rgba(0, 0, 0, 0.1)'
            : '0 10px 40px rgba(239, 68, 68, 0.3), 0 2px 8px rgba(0, 0, 0, 0.1)';

        toastText.textContent = '金币 ' + prefix + amount;
        toast.firstElementChild.style.background = bg;
        toast.firstElementChild.style.boxShadow = shadow;

        toast.style.display = 'block';
        requestAnimationFrame(function() {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(-50%) translateY(0)';
        });

        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(function() {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(-20px)';
            setTimeout(function() {
                toast.style.display = 'none';
            }, 400);
        }, 2000);
    };
})();
