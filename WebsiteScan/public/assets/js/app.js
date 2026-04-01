// SiteScope - Main App JS

window.SiteScopeShowAuditLoading = function(form) {
    var existingOverlay = document.querySelector('.audit-loading-overlay');
    if (existingOverlay) {
        return existingOverlay;
    }

    var overlay = document.createElement('div');
    overlay.className = 'audit-loading-overlay';
    overlay.setAttribute('aria-live', 'polite');
    overlay.innerHTML =
        '<div class="audit-loading-card">' +
            '<div class="audit-loading-icon mb-3" data-loading-icon>' +
                '<div class="spinner-border text-primary" role="status" aria-hidden="true"></div>' +
            '</div>' +
            '<h3 data-loading-title>Building Your Report</h3>' +
            '<p data-loading-copy>' + ((form && form.getAttribute('data-loading-message')) || 'Scanning your site...') + '</p>' +
            '<div class="audit-loading-progress-wrap">' +
                '<div class="audit-loading-progress-meta">' +
                    '<span>Progress</span>' +
                    '<strong data-loading-progress-text>0%</strong>' +
                '</div>' +
                '<div class="audit-loading-progress-bar">' +
                    '<span data-loading-progress-fill style="width:0%"></span>' +
                '</div>' +
            '</div>' +
            '<div class="audit-loading-steps">' +
                '<div class="audit-loading-step is-active" data-step>Checking SEO</div>' +
                '<div class="audit-loading-step" data-step>Checking speed</div>' +
                '<div class="audit-loading-step" data-step>Checking contact info</div>' +
                '<div class="audit-loading-step" data-step>Finalizing your report</div>' +
            '</div>' +
        '</div>';
    document.body.appendChild(overlay);

    var steps = overlay.querySelectorAll('[data-step]');
    var currentStep = 0;
    if (steps.length > 1) {
        window.setInterval(function() {
            steps[currentStep].classList.remove('is-active');
            currentStep = (currentStep + 1) % steps.length;
            steps[currentStep].classList.add('is-active');
        }, 1400);
    }

    return overlay;
};

window.SiteScopeRunAuditLoadingSequence = function(overlay) {
    if (!overlay || overlay.dataset.sequenceStarted === '1') {
        return;
    }

    overlay.dataset.sequenceStarted = '1';

    var steps = overlay.querySelectorAll('[data-step]');
    var title = overlay.querySelector('[data-loading-title]');
    var copy = overlay.querySelector('[data-loading-copy]');
    var icon = overlay.querySelector('[data-loading-icon]');
    var progressText = overlay.querySelector('[data-loading-progress-text]');
    var progressFill = overlay.querySelector('[data-loading-progress-fill]');
    var stepMessages = [
        'Reviewing the page structure and SEO basics.',
        'Checking performance signals and speed insights.',
        'Looking for contact details, forms, and trust signals.',
        'Packaging everything into a clean report.'
    ];
    var stepPercents = [18, 46, 74, 92];

    var setProgress = function(percent) {
        var safePercent = Math.max(0, Math.min(100, percent));
        if (progressText) {
            progressText.textContent = safePercent + '%';
        }
        if (progressFill) {
            progressFill.style.width = safePercent + '%';
        }
    };

    setProgress(4);

    steps.forEach(function(step, index) {
        window.setTimeout(function() {
            steps.forEach(function(otherStep, otherIndex) {
                otherStep.classList.toggle('is-active', otherIndex === index);
                if (otherIndex < index) {
                    otherStep.classList.add('is-complete');
                }
            });

            if (copy && stepMessages[index]) {
                copy.textContent = stepMessages[index];
            }
            setProgress(stepPercents[index] || 0);
        }, index * 420);
    });

    window.setTimeout(function() {
        steps.forEach(function(step) {
            step.classList.remove('is-active');
            step.classList.add('is-complete');
        });

        overlay.classList.add('is-complete');
        if (title) {
            title.textContent = 'Report Ready';
        }
        if (copy) {
            copy.textContent = 'Opening your audit results now.';
        }
        if (icon) {
            icon.innerHTML = '<div class="audit-loading-complete-mark"><i class="bi bi-check2"></i></div>';
        }
        setProgress(100);
    }, 1800);
};

window.SiteScopeHandleAuditSubmit = function(event, form) {
    form = form || (event ? event.target : null);
    if (!form) {
        return true;
    }

    if (form.dataset.submitting === '2') {
        return true;
    }

    if (form.dataset.submitting === '1') {
        if (event) {
            event.preventDefault();
        }
        return false;
    }

    if (event) {
        event.preventDefault();
    }

    form.dataset.submitting = '1';

    var submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        var label = submitButton.querySelector('.submit-label, #btnText');
        var loading = submitButton.querySelector('.submit-loading, #btnLoading');
        if (label) label.classList.add('d-none');
        if (loading) loading.classList.remove('d-none');
    }

    var overlay = window.SiteScopeShowAuditLoading(form);
    window.SiteScopeRunAuditLoadingSequence(overlay);

    window.requestAnimationFrame(function() {
        window.requestAnimationFrame(function() {
            window.setTimeout(function() {
                form.dataset.submitting = '2';
                form.submit();
            }, 2250);
        });
    });

    return false;
};

document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
        setTimeout(function() {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    // Animate score bars on page load
    document.querySelectorAll('.progress-bar').forEach(function(bar) {
        var target = bar.style.width;
        bar.style.width = '0%';
        setTimeout(function() {
            bar.style.transition = 'width 1s ease-out';
            bar.style.width = target;
        }, 100);
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // URL normalizer hint
    var urlInput = document.querySelector('input[name="website_url"]');
    if (urlInput) {
        urlInput.addEventListener('blur', function() {
            var val = this.value.trim();
            if (val && !val.match(/^https?:\/\//i)) {
                this.value = 'https://' + val;
            }
        });
    }

    // Accordion icon rotation
    document.querySelectorAll('.accordion-button').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var icon = this.querySelector('.acc-icon');
            if (!icon) return;
            var isExpanding = this.classList.contains('collapsed');
            icon.style.transform = isExpanding ? 'rotate(90deg)' : 'rotate(0deg)';
        });
    });

    // Audit submit loading states
    document.querySelectorAll('.audit-submit-form').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            window.SiteScopeHandleAuditSubmit(event, form);
        });
    });

});
