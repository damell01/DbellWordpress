(function ($) {
    "use strict";

    var scannerModalId = 'scannerModalBackdrop';

    function closeScannerModal() {
        $('#' + scannerModalId).remove();
        $('body').removeClass('scanner-modal-open');
    }

    function openScannerModal() {
        if ($('#' + scannerModalId).length) {
            return;
        }

        var modalHtml = '' +
            '<div id="' + scannerModalId + '" class="scanner-modal-backdrop" role="dialog" aria-modal="true" aria-label="Website Scanner">' +
                '<div class="scanner-modal-panel">' +
                    '<div class="scanner-modal-header">' +
                        '<div class="scanner-modal-title">DBell Free Website Scanner</div>' +
                        '<div class="scanner-modal-actions">' +
                            '<a class="btn btn-sm btn-secondary rounded-pill px-3" href="scan.html" target="_blank" rel="noopener">Open Full Page</a>' +
                            '<button class="scanner-modal-close" type="button" aria-label="Close scanner">&times;</button>' +
                        '</div>' +
                    '</div>' +
                    '<iframe class="scanner-modal-iframe" src="WebsiteScan/audit?embed=1" title="DBell Website Scanner"></iframe>' +
                '</div>' +
            '</div>';

        $('body').append(modalHtml).addClass('scanner-modal-open');
    }

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();
    
    
    // Initiate the wowjs
    new WOW().init();


    // Sticky Navbar
    $(window).scroll(function () {
        if ($(this).scrollTop() > 45) {
            $('.navbar').addClass('sticky-top shadow-sm');
        } else {
            $('.navbar').removeClass('sticky-top shadow-sm');
        }
    });


    // Scroll progress bar
    $(window).scroll(function () {
        var scrollTop = $(this).scrollTop();
        var docHeight = $(document).height() - $(window).height();
        var scrollPercent = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
        $('#scrollProgress').css('width', scrollPercent + '%');
    });

    
    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });


    // Skills
    $('.skill').waypoint(function () {
        $('.progress .progress-bar').each(function () {
            $(this).css("width", $(this).attr("aria-valuenow") + '%');
        });
    }, {offset: '80%'});


    // Facts counter
    $('[data-toggle="counter-up"]').counterUp({
        delay: 10,
        time: 2000
    });


    // Testimonials carousel
    $(".testimonial-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1000,
        margin: 25,
        dots: false,
        loop: true,
        nav : true,
        navText : [
            '<i class="bi bi-chevron-left"></i>',
            '<i class="bi bi-chevron-right"></i>'
        ],
        responsive: {
            0:{
                items:1
            },
            992:{
                items:2
            }
        }
    });


    // Portfolio isotope and filter
    var portfolioIsotope = $('.portfolio-container').isotope({
        itemSelector: '.portfolio-item',
        layoutMode: 'fitRows'
    });
    $('#portfolio-flters li').on('click', function () {
        $("#portfolio-flters li").removeClass('active');
        $(this).addClass('active');

        portfolioIsotope.isotope({filter: $(this).data('filter')});
    });


    // Sticky mobile CTA bar — show after scrolling past hero
    $(window).on('scroll.stickyCta', function () {
        if ($(this).scrollTop() > 500) {
            $('#stickyCta').addClass('visible').attr('aria-hidden', 'false');
        } else {
            $('#stickyCta').removeClass('visible').attr('aria-hidden', 'true');
        }
    });

    // Open scanner in modal from any scan link unless user wants new tab
    $(document).on('click', 'a[href="scan.html"]', function (e) {
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.which === 2) {
            return;
        }
        e.preventDefault();
        openScannerModal();
    });

    // Close scanner modal events
    $(document).on('click', '.scanner-modal-close', function () {
        closeScannerModal();
    });
    $(document).on('click', '.scanner-modal-backdrop', function (e) {
        if ($(e.target).is('.scanner-modal-backdrop')) {
            closeScannerModal();
        }
    });
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            closeScannerModal();
        }
    });


    // Free Audit form — AJAX submission
    $('#auditForm').on('submit', function (e) {
        e.preventDefault();

        var name      = $('#auditName').val().trim();
        var email     = $('#auditEmail').val().trim();
        var website   = $('#auditWebsite').val().trim();
        var challenge = $('#auditChallenge').val();
        var $errorDiv = $('#auditFormError');

        // Basic client-side validation with email format check
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        var errors = [];

        if (!name)  { $('#auditName').addClass('is-invalid');  errors.push('Name is required.'); }
        if (!email) { $('#auditEmail').addClass('is-invalid'); errors.push('Email is required.'); }
        else if (!emailRegex.test(email)) { $('#auditEmail').addClass('is-invalid'); errors.push('Please enter a valid email address.'); }

        if (errors.length) {
            $errorDiv.html(errors.join('<br>')).removeClass('d-none');
            return;
        }

        $errorDiv.addClass('d-none');

        var message = 'FREE AUDIT REQUEST\n';
        if (website)   message += 'Website: ' + website + '\n';
        if (challenge) message += 'Biggest Challenge: ' + challenge;

        var $btn = $(this).find('[type=submit]');
        $btn.prop('disabled', true).text('Sending…');

        $.ajax({
            url: 'contact.php',
            type: 'POST',
            data: { name: name, email: email, message: message },
            dataType: 'json',
            success: function (data) {
                if (data && data.type === 'success') {
                    $('#auditForm').addClass('d-none');
                    $('#auditFormSuccess').removeClass('d-none');
                } else {
                    var msg = (data && data.message) ? data.message : 'Something went wrong. Please try again.';
                    $errorDiv.html(msg).removeClass('d-none');
                    $btn.prop('disabled', false).text('Send Me My Free Audit →');
                }
            },
            error: function () {
                // Graceful fallback — redirect to contact page
                window.location.href = 'contact.html';
            }
        });
    });

    // Remove invalid class on input
    $('#auditName, #auditEmail').on('input', function () {
        $(this).removeClass('is-invalid');
        if (!$('#auditName').hasClass('is-invalid') && !$('#auditEmail').hasClass('is-invalid')) {
            $('#auditFormError').addClass('d-none');
        }
    });
    
})(jQuery);

