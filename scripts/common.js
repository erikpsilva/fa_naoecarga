$(document).ready(function() {
    var $header      = $('.header');
    var $navLinks    = $header.find('.header__navLink');
    var $mobileMenu  = $header.find('.header__mobileMenu');
    var $menuButton  = $header.find('.header__menuButton');
    var $mobileClose = $header.find('.header__mobileClose');
    var $mobileLinks = $header.find('.header__mobileNavLink');

    if (!$header.length) return;

    var SCROLL_ENTER  = 80;
    var STICKY_HEIGHT = 62;
    var $defaultActive = $navLinks.filter('.header__navLink--active').first();

    // ─── MENU MOBILE ─────────────────────────────────────────────────────────

    function openMobileMenu() {
        $mobileMenu.addClass('header__mobileMenu--open').attr('aria-hidden', 'false');
        $('body').css('overflow', 'hidden');
    }

    function closeMobileMenu() {
        $mobileMenu.removeClass('header__mobileMenu--open').attr('aria-hidden', 'true');
        $('body').css('overflow', '');
    }

    $menuButton.on('click', openMobileMenu);
    $mobileClose.on('click', closeMobileMenu);

    // Scroll suave nos links do menu mobile
    $mobileLinks.on('click', function(e) {
        var href = $(this).attr('href');
        if (href && href.charAt(0) === '#') {
            e.preventDefault();
            var $target = $(href);
            closeMobileMenu();
            if ($target.length) {
                setTimeout(function() {
                    var scrollTo = Math.max(0, $target.offset().top - STICKY_HEIGHT);
                    $('html, body').animate({ scrollTop: scrollTo }, 650, 'swing');
                }, 380);
            }
        }
    });

    // ─── SCROLL SUAVE NO DESKTOP ─────────────────────────────────────────────

    $navLinks.on('click', function(e) {
        var href = $(this).attr('href');
        if (href && href.charAt(0) === '#') {
            e.preventDefault();
            var $target = $(href);
            if ($target.length) {
                var scrollTo = Math.max(0, $target.offset().top - STICKY_HEIGHT);
                $('html, body').animate({ scrollTop: scrollTo }, 650, 'swing');
            }
        }
    });

    // ─── HEADER STICKY + SEÇÃO ATIVA ─────────────────────────────────────────

    var sections = ['testemunhos', 'apadrinhe', 'calculadora', 'bioetica'];

    function updateActiveNav(scrollTop) {
        var current = '';
        for (var i = 0; i < sections.length; i++) {
            var $section = $('#' + sections[i]);
            if ($section.length && scrollTop >= $section.offset().top - STICKY_HEIGHT - 40) {
                current = sections[i];
                break;
            }
        }
        $navLinks.removeClass('header__navLink--active');
        $mobileLinks.removeClass('header__mobileNavLink--active');
        if (current) {
            $navLinks.filter('[href="#' + current + '"]').addClass('header__navLink--active');
            $mobileLinks.filter('[href="#' + current + '"]').addClass('header__mobileNavLink--active');
        }
    }

    function onScroll() {
        if ($mobileMenu.hasClass('header__mobileMenu--open')) return;

        var scrollTop = $(window).scrollTop();

        if (scrollTop > SCROLL_ENTER) {
            $header.addClass('header--sticky');
            updateActiveNav(scrollTop);
        } else if (scrollTop < 5) {
            $header.removeClass('header--sticky');
            $navLinks.removeClass('header__navLink--active');
            $defaultActive.addClass('header__navLink--active');
            $mobileLinks.removeClass('header__mobileNavLink--active');
        }
    }

    $(window).on('scroll.header', onScroll);

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') closeMobileMenu();
    });

    onScroll();
});
