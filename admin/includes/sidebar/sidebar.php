<aside class="sidebar">
    <nav class="sidebar__nav">
        <ul class="sidebar__menu">

            <li class="sidebar__item">
                <a href="<?= BASE_URL ?>/admin/inicio"
                   class="sidebar__link <?= ($subRoute === 'inicio') ? 'sidebar__link--active' : '' ?>">
                    Início
                </a>
            </li>

            <li class="sidebar__item">
                <a href="<?= BASE_URL ?>/admin/meusdados"
                   class="sidebar__link <?= ($subRoute === 'meusdados') ? 'sidebar__link--active' : '' ?>">
                    Meus Dados
                </a>
            </li>

            <?php if ($_SESSION['usuario']['nivel_acesso'] === 'admin'): ?>
            <li class="sidebar__item">
                <a href="<?= BASE_URL ?>/admin/cadastrarusuario"
                   class="sidebar__link <?= ($subRoute === 'cadastrarusuario') ? 'sidebar__link--active' : '' ?>">
                    Cadastrar Usuário
                </a>
            </li>

            <li class="sidebar__item">
                <a href="<?= BASE_URL ?>/admin/administrarusuarios"
                   class="sidebar__link <?= ($subRoute === 'administrarusuarios') ? 'sidebar__link--active' : '' ?>">
                    Administrar Usuários
                </a>
            </li>

            <li class="sidebar__item">
                <a href="<?= BASE_URL ?>/admin/doadores"
                   class="sidebar__link <?= ($subRoute === 'doadores') ? 'sidebar__link--active' : '' ?>">
                    Doadores
                </a>
            </li>

            <li class="sidebar__item">
                <a href="<?= BASE_URL ?>/admin/exportar-leads"
                   class="sidebar__link <?= ($subRoute === 'exportar-leads') ? 'sidebar__link--active' : '' ?>">
                    Exportar Leads
                </a>
            </li>

            <?php
            $conteudoSubs = ['conteudo-banner', 'conteudo-intro', 'conteudo-apoiar', 'conteudo-apadrinhe', 'calculadora',
                             'conteudo-extra-01', 'conteudo-extra-02', 'conteudo-extra-03', 'conteudo-extra-04'];
            $conteudoAberto = in_array($subRoute, $conteudoSubs);
            ?>
            <li class="sidebar__item">
                <button class="sidebar__link--parent <?= $conteudoAberto ? 'is-open is-active' : '' ?>" id="toggleConteudo">
                    Conteúdo <span class="sidebar__arrow">&#9660;</span>
                </button>
                <ul class="sidebar__sub <?= $conteudoAberto ? 'is-open' : '' ?>" id="subConteudo">
                    <li>
                        <a href="<?= BASE_URL ?>/admin/conteudo-banner"
                           class="sidebar__subLink <?= ($subRoute === 'conteudo-banner') ? 'sidebar__subLink--active' : '' ?>">
                            Banner Home
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/conteudo-intro"
                           class="sidebar__subLink <?= ($subRoute === 'conteudo-intro') ? 'sidebar__subLink--active' : '' ?>">
                            Introdução
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/conteudo-apoiar"
                           class="sidebar__subLink <?= ($subRoute === 'conteudo-apoiar') ? 'sidebar__subLink--active' : '' ?>">
                            Por que apoiar
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/conteudo-apadrinhe"
                           class="sidebar__subLink <?= ($subRoute === 'conteudo-apadrinhe') ? 'sidebar__subLink--active' : '' ?>">
                            Apadrinhe
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/calculadora"
                           class="sidebar__subLink <?= ($subRoute === 'calculadora') ? 'sidebar__subLink--active' : '' ?>">
                            Calculadora de Animais
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/conteudo-extra-01"
                           class="sidebar__subLink <?= ($subRoute === 'conteudo-extra-01') ? 'sidebar__subLink--active' : '' ?>">
                            Conteúdo Extra 01
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/conteudo-extra-02"
                           class="sidebar__subLink <?= ($subRoute === 'conteudo-extra-02') ? 'sidebar__subLink--active' : '' ?>">
                            Conteúdo Extra 02
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/conteudo-extra-03"
                           class="sidebar__subLink <?= ($subRoute === 'conteudo-extra-03') ? 'sidebar__subLink--active' : '' ?>">
                            Conteúdo Extra 03
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/conteudo-extra-04"
                           class="sidebar__subLink <?= ($subRoute === 'conteudo-extra-04') ? 'sidebar__subLink--active' : '' ?>">
                            Conteúdo Extra 04
                        </a>
                    </li>
                </ul>
            </li>

            <li class="sidebar__item">
                <a href="<?= BASE_URL ?>/admin/testemunhos"
                   class="sidebar__link <?= ($subRoute === 'testemunhos') ? 'sidebar__link--active' : '' ?>">
                    Testemunhos
                </a>
            </li>

            <li class="sidebar__item">
                <a href="<?= BASE_URL ?>/admin/banners"
                   class="sidebar__link <?= ($subRoute === 'banners') ? 'sidebar__link--active' : '' ?>">
                    Banners
                </a>
            </li>

            <li class="sidebar__item">
                <a href="<?= BASE_URL ?>/admin/controle-links"
                   class="sidebar__link <?= ($subRoute === 'controle-links') ? 'sidebar__link--active' : '' ?>">
                    Controle de Links
                </a>
            </li>

            <li class="sidebar__item">
                <a href="<?= BASE_URL ?>/admin/configuracoes"
                   class="sidebar__link <?= ($subRoute === 'configuracoes') ? 'sidebar__link--active' : '' ?>">
                    Configurações
                </a>
            </li>
            <?php endif; ?>

        </ul>
    </nav>
</aside>

<div class="sidebar__overlay" id="sidebarOverlay"></div>
<script>
(function(){
    var btn = document.getElementById('toggleConteudo');
    var sub = document.getElementById('subConteudo');
    if (btn && sub) {
        btn.addEventListener('click', function(){
            this.classList.toggle('is-open');
            this.classList.toggle('is-active');
            sub.classList.toggle('is-open');
        });
    }
})();
</script>
