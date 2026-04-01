// SiteScope - Admin JS

document.addEventListener('DOMContentLoaded', function() {
    // Mobile sidebar toggle
    var toggleBtn = document.getElementById('sidebarToggle');
    var sidebar   = document.querySelector('.admin-sidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    // Confirm destructive actions
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // Auto-dismiss inline alerts
    document.querySelectorAll('.admin-topbar .alert').forEach(function(alert) {
        setTimeout(function() { alert.style.display = 'none'; }, 4000);
    });

    // Animate stat card values (count up)
    document.querySelectorAll('.stat-card-value').forEach(function(el) {
        var target = parseInt(el.textContent.replace(/,/g, ''), 10);
        if (isNaN(target) || target === 0) return;
        var start    = 0;
        var duration = 800;
        var step     = target / (duration / 16);
        var timer = setInterval(function() {
            start += step;
            if (start >= target) {
                start = target;
                clearInterval(timer);
            }
            el.textContent = Math.round(start).toLocaleString();
        }, 16);
    });
});
