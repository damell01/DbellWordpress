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
        var stepInterval = window.setInterval(function() {
            steps[currentStep].classList.remove('is-active');
            currentStep = (currentStep + 1) % steps.length;
            steps[currentStep].classList.add('is-active');
        }, 1600);
        overlay.dataset.stepIntervalId = String(stepInterval);
    }

    return overlay;
};

window.SiteScopeClearAuditLoadingTimers = function(overlay) {
    if (!overlay) {
        return;
    }

    ['stepIntervalId', 'progressIntervalId', 'copyIntervalId'].forEach(function(key) {
        var rawId = overlay.dataset[key];
        if (rawId) {
            window.clearInterval(Number(rawId));
            delete overlay.dataset[key];
        }
    });
};

window.SiteScopeRunAuditLoadingSequence = function(overlay) {
    if (!overlay || overlay.dataset.sequenceStarted === '1') {
        return;
    }

    overlay.dataset.sequenceStarted = '1';

    var steps = overlay.querySelectorAll('[data-step]');
    var copy = overlay.querySelector('[data-loading-copy]');
    var progressText = overlay.querySelector('[data-loading-progress-text]');
    var progressFill = overlay.querySelector('[data-loading-progress-fill]');
    var progressValue = 4;
    var stepMessages = [
        'Reviewing the page structure and SEO basics.',
        'Checking performance signals and speed insights.',
        'Looking for contact details, forms, and trust signals.',
        'Assembling the final report so it opens fully ready.'
    ];
    var holdMessages = [
        'Still scanning the site and checking deeper signals.',
        'Reviewing key pages for SEO, speed, and conversion issues.',
        'Final checks are running so the report opens fully ready.'
    ];
    var holdMessageIndex = 0;

    var setProgress = function(percent) {
        progressValue = Math.max(0, Math.min(100, percent));
        if (progressText) {
            progressText.textContent = progressValue + '%';
        }
        if (progressFill) {
            progressFill.style.width = progressValue + '%';
        }
    };

    overlay._setAuditProgress = setProgress;

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

            setProgress([14, 36, 61, 82][index] || 82);
        }, index * 950);
    });

    window.setTimeout(function() {
        var progressInterval = window.setInterval(function() {
            if (progressValue < 94) {
                setProgress(progressValue + (progressValue < 88 ? 2 : 1));
            }
        }, 950);

        var copyInterval = window.setInterval(function() {
            if (copy) {
                copy.textContent = holdMessages[holdMessageIndex % holdMessages.length];
            }
            holdMessageIndex++;
        }, 2400);

        overlay.dataset.progressIntervalId = String(progressInterval);
        overlay.dataset.copyIntervalId = String(copyInterval);
    }, 3200);
};

window.SiteScopeCompleteAuditLoading = function(overlay, message) {
    if (!overlay) {
        return;
    }

    window.SiteScopeClearAuditLoadingTimers(overlay);

    var steps = overlay.querySelectorAll('[data-step]');
    var title = overlay.querySelector('[data-loading-title]');
    var copy = overlay.querySelector('[data-loading-copy]');
    var icon = overlay.querySelector('[data-loading-icon]');

    steps.forEach(function(step) {
        step.classList.remove('is-active');
        step.classList.add('is-complete');
    });

    overlay.classList.add('is-complete');
    if (title) {
        title.textContent = 'Report Ready';
    }
    if (copy) {
        copy.textContent = message || 'Opening your audit results now.';
    }
    if (icon) {
        icon.innerHTML = '<div class="audit-loading-complete-mark"><i class="bi bi-check2"></i></div>';
    }

    if (typeof overlay._setAuditProgress === 'function') {
        overlay._setAuditProgress(100);
    }
};

window.SiteScopeFailAuditLoading = function(overlay, message) {
    if (!overlay) {
        return;
    }

    window.SiteScopeClearAuditLoadingTimers(overlay);

    var title = overlay.querySelector('[data-loading-title]');
    var copy = overlay.querySelector('[data-loading-copy]');
    var icon = overlay.querySelector('[data-loading-icon]');

    if (title) {
        title.textContent = 'Scan Stopped';
    }
    if (copy) {
        copy.textContent = message || 'The audit could not be completed.';
    }
    if (icon) {
        icon.innerHTML = '<div class="audit-loading-complete-mark" style="background:#ef4444;"><i class="bi bi-x-lg"></i></div>';
    }
};

window.SiteScopeHandleAuditSubmit = function(event, form) {
    form = form || (event ? event.target : null);
    if (!form) {
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
    var label = submitButton ? submitButton.querySelector('.submit-label, #btnText') : null;
    var loading = submitButton ? submitButton.querySelector('.submit-loading, #btnLoading') : null;

    if (submitButton) {
        submitButton.disabled = true;
    }
    if (label) {
        label.classList.add('d-none');
    }
    if (loading) {
        loading.classList.remove('d-none');
    }

    var overlay = window.SiteScopeShowAuditLoading(form);
    window.SiteScopeRunAuditLoadingSequence(overlay);

    window.fetch(form.action, {
        method: (form.method || 'POST').toUpperCase(),
        body: new FormData(form),
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    }).then(function(response) {
        return response.json().catch(function() {
            return {
                success: false,
                message: 'The server returned an unexpected response.'
            };
        }).then(function(payload) {
            return {
                ok: response.ok,
                payload: payload
            };
        });
    }).then(function(result) {
        if (!result.ok || !result.payload || !result.payload.success || !result.payload.redirect_url) {
            throw new Error((result.payload && result.payload.message) || 'The audit could not be completed.');
        }

        window.SiteScopeCompleteAuditLoading(overlay, result.payload.message || 'Opening your audit results now.');
        window.setTimeout(function() {
            window.location.href = result.payload.redirect_url;
        }, 450);
    }).catch(function(error) {
        window.SiteScopeFailAuditLoading(overlay, error && error.message ? error.message : 'The audit could not be completed.');

        form.dataset.submitting = '0';
        if (submitButton) {
            submitButton.disabled = false;
        }
        if (label) {
            label.classList.remove('d-none');
        }
        if (loading) {
            loading.classList.add('d-none');
        }

        window.setTimeout(function() {
            if (overlay && overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
            window.alert(error && error.message ? error.message : 'The audit could not be completed.');
        }, 800);
    });

    return false;
};

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
        setTimeout(function() {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });

    document.querySelectorAll('.progress-bar').forEach(function(bar) {
        var target = bar.style.width;
        bar.style.width = '0%';
        setTimeout(function() {
            bar.style.transition = 'width 1s ease-out';
            bar.style.width = target;
        }, 100);
    });

    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    var urlInput = document.querySelector('input[name="website_url"]');
    if (urlInput) {
        urlInput.addEventListener('blur', function() {
            var val = this.value.trim();
            if (val && !val.match(/^https?:\/\//i)) {
                this.value = 'https://' + val;
            }
        });
    }

    document.querySelectorAll('.accordion-button').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var icon = this.querySelector('.acc-icon');
            if (!icon) {
                return;
            }
            var isExpanding = this.classList.contains('collapsed');
            icon.style.transform = isExpanding ? 'rotate(90deg)' : 'rotate(0deg)';
        });
    });

    document.querySelectorAll('.audit-submit-form').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            window.SiteScopeHandleAuditSubmit(event, form);
        });
    });
});
