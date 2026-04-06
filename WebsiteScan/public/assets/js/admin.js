// DBell Creations CRM - Admin JS

document.addEventListener('DOMContentLoaded', function () {

    // ── Mobile sidebar toggle ────────────────────────
    var toggleBtn = document.getElementById('sidebarToggle');
    var sidebar   = document.querySelector('.admin-sidebar');
    var overlay   = document.getElementById('sidebarOverlay');

    function openSidebar() {
        if (sidebar) sidebar.classList.add('open');
        if (overlay) overlay.classList.add('visible');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('visible');
        document.body.style.overflow = '';
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            if (sidebar && sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Close sidebar on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSidebar();
    });

    // Close sidebar on nav link click (mobile)
    if (sidebar) {
        sidebar.querySelectorAll('.admin-nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 992) closeSidebar();
            });
        });
    }

    // ── Toast auto-dismiss ───────────────────────────
    var toast = document.getElementById('crmToast');
    if (toast) {
        setTimeout(function () {
            toast.classList.remove('show');
            setTimeout(function () { toast.style.display = 'none'; }, 400);
        }, 4500);
    }

    // ── Confirm destructive actions ──────────────────
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // ── Animate stat card values (count up) ──────────
    document.querySelectorAll('.stat-card-value').forEach(function (el) {
        var target = parseInt(el.textContent.replace(/,/g, ''), 10);
        if (isNaN(target) || target === 0) return;
        var start    = 0;
        var duration = 900;
        var step     = target / (duration / 16);
        var timer = setInterval(function () {
            start += step;
            if (start >= target) {
                start = target;
                clearInterval(timer);
            }
            el.textContent = Math.round(start).toLocaleString();
        }, 16);
    });

    // ── Show CRM toast helper (used by inline scripts) ──
    window.crmToast = function (message, type) {
        var t = document.createElement('div');
        t.className = 'crm-toast ' + (type || '');
        t.innerHTML = message;
        document.body.appendChild(t);
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { t.classList.add('show'); });
        });
        setTimeout(function () {
            t.classList.remove('show');
            setTimeout(function () { t.remove(); }, 400);
        }, 4000);
    };

    // ── Status select quick-save feedback ────────────
    document.querySelectorAll('select[data-autosave]').forEach(function (sel) {
        sel.addEventListener('change', function () {
            this.closest('form').submit();
        });
    });

});
