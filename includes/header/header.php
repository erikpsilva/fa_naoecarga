<header class="header">
    <div class="container">
        <div class="header__content">
            <a class="header__brand" href="<?= BASE_URL ?>/inicio" aria-label="Fórum Nacional de Proteção e Defesa Animal">
                <img class="header__logo" src="<?= BASE_URL ?>/images/logoForumAnimal.png" alt="Fórum Nacional de Proteção e Defesa Animal">
            </a>

            <nav class="header__nav" aria-label="Menu principal">
                <a class="header__navLink header__navLink--active" href="#bioetica">Bioética</a>
                <a class="header__navLink" href="#calculadora">Calculadora de impacto</a>
                <a class="header__navLink" href="#apadrinhe">Apadrinhe</a>
                <a class="header__navLink" href="#testemunhos">Testemunhos</a>
            </nav>

            <button class="header__menuButton" type="button" aria-label="Abrir menu">
                <span class="header__menuText">Menu</span>
                <i class="icon icon-menumobile" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    <div class="header__mobileMenu" aria-hidden="true">
        <button class="header__mobileClose" type="button" aria-label="Fechar menu">&times;</button>
        <img class="header__mobileLogo" src="<?= BASE_URL ?>/images/logoForumAnimal.png" alt="Fórum Nacional de Proteção e Defesa Animal">
        <nav class="header__mobileNav" aria-label="Menu mobile">
            <a class="header__mobileNavLink" href="#bioetica">Bioética</a>
            <a class="header__mobileNavLink" href="#calculadora">Calculadora de impacto</a>
            <a class="header__mobileNavLink" href="#apadrinhe">Apadrinhe</a>
            <a class="header__mobileNavLink" href="#testemunhos">Testemunhos</a>
        </nav>
    </div>
</header>
