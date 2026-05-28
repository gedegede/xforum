document.addEventListener('DOMContentLoaded', function() {
    var body = document.getElementById('pm-chat-body');
    var form = document.getElementById('pm-chat-form');
    var refreshCount = document.getElementById('pm-refresh-count');
    var refreshNow = document.getElementById('pm-refresh-now');
    if (!body || !form) return;

    var partnerUid = body.dataset.partnerUid || '';
    var remain = 60;

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function appendMessage(message) {
        if (!message || !message.pmid || body.querySelector('[data-pmid="' + message.pmid + '"]')) return;
        var row = document.createElement('div');
        row.className = 'pm-bubble-row' + (Number(message.uid) === Number(body.dataset.currentUid || 0) ? ' is-mine' : '');
        row.dataset.pmid = message.pmid;
        row.innerHTML = '<div class="pm-bubble"><div class="pm-bubble-text">' + escapeHtml(message.content).replace(/\n/g, '<br>') + '</div><div class="pm-bubble-time">' + formatTime(message.dateline) + '</div></div>';
        body.appendChild(row);
        body.dataset.lastPmid = String(Math.max(Number(body.dataset.lastPmid || 0), Number(message.pmid || 0)));
        scrollToBottom();
    }

    function formatTime(timestamp) {
        var date = new Date(Number(timestamp || 0) * 1000);
        var pad = function(num) { return String(num).padStart(2, '0'); };
        return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()) + ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes()) + ':' + pad(date.getSeconds());
    }

    function scrollToBottom() {
        body.scrollTop = body.scrollHeight;
    }

    scrollToBottom();

    form.addEventListener('submit', function(e) {
        var textarea = form.querySelector('[name="content"]');
        if (!textarea || !textarea.value.trim()) {
            e.preventDefault();
            return;
        }
        if (Number(body.dataset.page || 1) < Number(body.dataset.pages || 1)) return;
        e.preventDefault();
        var data = new FormData(form);
        data.append('csrf_token', window.getCsrfToken ? window.getCsrfToken() : '');
        fetch(form.action, {method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'}, body: data})
            .then(function(res) { return res.json(); })
            .then(function(res) {
                if (!res.success) {
                    showMessageModal('提示', res.message || '发送失败');
                    return;
                }
                textarea.value = '';
                if (res.credit_change && typeof window.showCreditToast === 'function') {
                    window.showCreditToast(res.credit_change);
                }
                remain = 60;
                pollMessages();
                if (refreshCount) refreshCount.textContent = remain + 's';
            })
            .catch(function() { showMessageModal('提示', '网络错误，请重试'); });
    });

    function pollMessages() {
        fetch('index.php?c=pm&a=poll&uid=' + partnerUid + '&after=' + (body.dataset.lastPmid || 0), {headers: {'X-Requested-With': 'XMLHttpRequest'}})
            .then(function(res) { return res.json(); })
            .then(function(res) {
                if (!res.success || !Array.isArray(res.messages)) return;
                res.messages.forEach(appendMessage);
            });
    }

    if (refreshNow) {
        refreshNow.addEventListener('click', function() {
            remain = 60;
            pollMessages();
            if (refreshCount) refreshCount.textContent = remain + 's';
        });
    }

    setInterval(function() {
        remain--;
        if (remain <= 0) {
            remain = 60;
            pollMessages();
        }
        if (refreshCount) refreshCount.textContent = remain + 's';
    }, 1000);
});
