$(document).ready(function() {

    // ─── CALCULADORA DE IMPACTO ───────────────────────────────────────────────

    var $calcPanel       = $('.homeCalculator__panel');
    var $valueButtons    = $calcPanel.find('.homeCalculator__value');
    var $freqButtons     = $calcPanel.find('.homeCalculator__frequencyButton');
    var $customInput     = $calcPanel.find('.homeCalculator__input');

    var _cfg = window.CALC_CONFIG || {};
    var _animals = _cfg.animals || [
        { slug: 'rato',    pct: 0.65 },
        { slug: 'peixe',   pct: 0.20 },
        { slug: 'galinha', pct: 0.07 },
        { slug: 'outros',  pct: 0.08 },
    ];
    var ANIMALS = _animals.map(function(a) { return a.slug; });
    var PERCENTAGES = {};
    _animals.forEach(function(a) { PERCENTAGES[a.slug] = a.pct; });
    var COST_PER_ANIMAL = _cfg.custoAnimal || 15;

    function getAmount() {
        var $active = $valueButtons.filter('.homeCalculator__value--active');
        if ($active.length) {
            return parseInt($active.text().replace(/\D/g, ''), 10) || 0;
        }
        return parseInt($customInput.val().replace(/\D/g, ''), 10) || 0;
    }

    function isMensal() {
        var $active = $freqButtons.filter('.homeCalculator__frequencyButton--active');
        return $active.text().trim().toLowerCase() === 'mensal';
    }

    function formatCount(n) {
        var rounded = Math.round(n * 10) / 10;
        return rounded % 1 === 0 ? rounded.toString() : rounded.toFixed(1);
    }

    function animateNumber($el, toValue) {
        var from = parseFloat($el.text()) || 0;
        if (from === toValue) return;
        $({ n: from }).animate({ n: toValue }, {
            duration: 450,
            easing: 'swing',
            step: function() { $el.text(formatCount(this.n)); },
            complete: function() { $el.text(formatCount(toValue)); }
        });
    }

    function calculateAndDisplay() {
        var amount = getAmount();
        var effective = isMensal() ? amount * 12 : amount;

        var counts = {};
        ANIMALS.forEach(function(a) {
            counts[a] = (effective * PERCENTAGES[a]) / COST_PER_ANIMAL;
        });

        $('.homeAnimalResult').each(function() {
            var animal  = $(this).data('animal');
            var $number = $(this).find('.homeAnimalResult__number');
            if (animal && counts[animal] !== undefined) {
                animateNumber($number, counts[animal]);
            }
        });
    }

    // Botões de valor predefinido
    $valueButtons.on('click', function() {
        $valueButtons.removeClass('homeCalculator__value--active');
        $(this).addClass('homeCalculator__value--active');
        $customInput.val('');
        calculateAndDisplay();
    });

    function updateDonateButton() {
        var label = isMensal() ? 'FAZER DOAÇÃO MENSAL' : 'FAZER DOAÇÃO';
        $calcPanel.find('.homeCalculator__donate').text(label);
    }

    // Botões de frequência
    $freqButtons.on('click', function() {
        $freqButtons.removeClass('homeCalculator__frequencyButton--active');
        $(this).addClass('homeCalculator__frequencyButton--active');
        calculateAndDisplay();
        updateDonateButton();
    });

    // Máscara do campo personalizado — formato R$ X.XXX (sem centavos)
    $customInput.on('keydown', function(e) {
        var allowed = [8, 46, 37, 38, 39, 40, 9]; // backspace, del, setas, tab
        if (allowed.indexOf(e.which) !== -1 || e.ctrlKey || e.metaKey) return;
        var isDigit = (e.which >= 48 && e.which <= 57) || (e.which >= 96 && e.which <= 105);
        if (!isDigit) e.preventDefault();
    }).on('input', function() {
        var caretPos = this.selectionStart;
        var oldLen   = this.value.length;
        var raw      = this.value.replace(/\D/g, '');

        if (!raw) {
            $(this).val('');
            $valueButtons.removeClass('homeCalculator__value--active');
            calculateAndDisplay();
            return;
        }

        var num       = parseInt(raw, 10);
        var prefix    = 'R$ ';
        var numStr    = num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        var formatted = prefix + numStr;

        $(this).val(formatted);

        var newLen  = formatted.length;
        var newCaret = Math.max(prefix.length, caretPos + (newLen - oldLen));
        this.setSelectionRange(newCaret, newCaret);

        $valueButtons.removeClass('homeCalculator__value--active');
        calculateAndDisplay();
    });

    // ─── Modal de dados do doador ─────────────────────────────────────────────
    var $doacaoModal = $('#doacaoModal');
    var $form        = $('#doacaoModalForm');
    var $subtitle    = $('#doacaoModalSubtitle');
    var $submitBtn   = $('#doacaoSubmit');
    var pendingAmount = 0;
    var pendingTipo   = '';

    // Máscara de telefone (plugin DigitalBush: 9 = dígito)
    $('#doacaoTelefone').mask('(99) 99999-9999');

    function openDoacaoModal(amount, tipo) {
        pendingAmount = amount;
        pendingTipo   = tipo;
        var tipoLabel   = tipo === 'mensal' ? 'Doação mensal de R$ ' + amount : 'Doação única de R$ ' + amount;
        var emailLabel  = tipo === 'mensal' ? 'E-mail do Mercado Livre' : 'E-mail';
        $subtitle.text(tipoLabel);
        $('label[for="doacaoEmail"]').text(emailLabel);
        $form[0].reset();
        $form.find('.doacaoModal__input').removeClass('is-invalid');
        $submitBtn.text('Ir para o pagamento').prop('disabled', false).css('opacity', '');
        $doacaoModal.addClass('is-open');
        $('body').css('overflow', 'hidden');
        $('#doacaoNome').focus();
    }

    function closeDoacaoModal() {
        $doacaoModal.removeClass('is-open');
        $('body').css('overflow', '');
    }

    $doacaoModal.on('click', '.doacaoModal__overlay, .doacaoModal__close', closeDoacaoModal);
    $(document).on('keydown', function(e) { if (e.key === 'Escape') closeDoacaoModal(); });

    $form.on('submit', function(e) {
        e.preventDefault();

        var nome     = $.trim($('#doacaoNome').val());
        var email    = $.trim($('#doacaoEmail').val());
        var telefone = $.trim($('#doacaoTelefone').val());
        var valid    = true;

        $form.find('.doacaoModal__input').removeClass('is-invalid');

        if (!nome)  { $('#doacaoNome').addClass('is-invalid');  valid = false; }
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            $('#doacaoEmail').addClass('is-invalid'); valid = false;
        }
        if (!valid) return;

        $submitBtn.text('Aguarde...').prop('disabled', true).css('opacity', '0.7');

        $.ajax({
            url: (window.APP_BASE_URL || '') + '/services/criar_pagamento.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                valor:    pendingAmount,
                tipo:     pendingTipo,
                nome:     nome,
                email:    email,
                telefone: telefone,
                lgpd_tag: localStorage.getItem('lgpd_choice') || 'LGPD_NOK'
            }),
            success: function(res) {
                if (res.checkout_url) {
                    window.location.href = res.checkout_url;
                } else {
                    alert('Não foi possível iniciar o pagamento. Tente novamente.');
                    $submitBtn.text('Ir para o pagamento').prop('disabled', false).css('opacity', '');
                }
            },
            error: function() {
                alert('Erro de comunicação. Verifique sua conexão e tente novamente.');
                $submitBtn.text('Ir para o pagamento').prop('disabled', false).css('opacity', '');
            }
        });
    });

    // Botão de doação da calculadora — abre a modal
    $calcPanel.on('click', '.homeCalculator__donate', function(e) {
        e.preventDefault();
        var amount = getAmount();
        if (amount <= 0) {
            alert('Selecione ou digite um valor para continuar.');
            return;
        }
        openDoacaoModal(amount, isMensal() ? 'mensal' : 'unica');
    });

    // Botão "Quero Apadrinhar" — cobrança única com valor fixo do admin
    $(document).on('click', '.homeSponsor__button', function(e) {
        e.preventDefault();
        var amount = parseFloat($(this).data('valor'));
        if (!amount || amount <= 0) return;
        openDoacaoModal(amount, 'unica');
    });

    // Estado inicial (R$60 mensal)
    calculateAndDisplay();
    updateDonateButton();


    // ─── DEPOIMENTOS ─────────────────────────────────────────────────────────

    var $testimonials = $('.homeTestimonials__slider');

    if ($testimonials.length) {
        var $modal = $(
            '<div class="homeTestimonialModal">' +
                '<div class="homeTestimonialModal__overlay"></div>' +
                '<div class="homeTestimonialModal__box">' +
                    '<button class="homeTestimonialModal__close" type="button" aria-label="Fechar">&times;</button>' +
                    '<p class="homeTestimonialModal__quote"></p>' +
                    '<div class="homeTestimonialModal__author"></div>' +
                '</div>' +
            '</div>'
        );
        $('body').append($modal);

        function isTextClamped($el) {
            var clone = $el.clone().css({
                'display':            'block',
                'overflow':           'visible',
                '-webkit-line-clamp': 'unset',
                'line-clamp':         'unset',
                'height':             'auto',
                'max-height':         'none',
                'position':           'absolute',
                'visibility':         'hidden',
                'left':               '-9999px',
                'width':              $el.outerWidth() + 'px'
            }).appendTo('body');

            var isClamped = clone[0].offsetHeight > $el[0].offsetHeight;
            clone.remove();
            return isClamped;
        }

        function checkTruncation() {
            $testimonials.find('.slick-slide:not(.slick-cloned) .homeTestimonial').each(function() {
                var $card  = $(this);
                var $quote = $card.find('.homeTestimonial__quote');
                var $btn   = $card.find('.homeTestimonial__button');
                $btn.toggle(isTextClamped($quote));
            });
        }

        $testimonials.on('init', function() {
            checkTruncation();
        }).slick({
            arrows:         true,
            dots:           true,
            infinite:       true,
            slidesToShow:   3,
            slidesToScroll: 1,
            responsive: [
                { breakpoint: 992, settings: { slidesToShow: 2 } },
                { breakpoint: 768, settings: { arrows: false, slidesToShow: 1 } }
            ]
        });

        $(document).on('click', '.homeTestimonial__button', function() {
            var $card       = $(this).closest('.homeTestimonial');
            var $quote      = $card.find('.homeTestimonial__quote');
            var $authorHtml = $card.find('.homeTestimonial__author').html();

            $modal.find('.homeTestimonialModal__quote').text($quote.text());
            $modal.find('.homeTestimonialModal__author').html($authorHtml);
            $modal.addClass('homeTestimonialModal--open');
            $('body').css('overflow', 'hidden');
        });

        function closeModal() {
            $modal.removeClass('homeTestimonialModal--open');
            $('body').css('overflow', '');
        }

        $modal.on('click', '.homeTestimonialModal__overlay, .homeTestimonialModal__close', closeModal);

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    }

    // ─── COOKIE CONSENT (LGPD) ───────────────────────────────────────────────
    var $cookiesOverlay = $('.cookiesOverlay');
    var $cookiesModal   = $('.cookiesModal');

    function hideCookieModal() {
        $cookiesOverlay.hide();
        $cookiesModal.hide();
    }

    if (!localStorage.getItem('lgpd_choice')) {
        $cookiesOverlay.show();
        $cookiesModal.show();
    }

    $cookiesModal.on('click', '.cookiesModal__button--primary', function() {
        localStorage.setItem('lgpd_choice', 'LGPD_OK');
        hideCookieModal();
    });

    $cookiesModal.on('click', '.cookiesModal__button--outline', function() {
        localStorage.setItem('lgpd_choice', 'LGPD_NOK');
        hideCookieModal();
    });
});
