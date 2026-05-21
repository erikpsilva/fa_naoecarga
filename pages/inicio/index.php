<?php
require_once ROOT . '/config/database.php';
try {
    $stmtBanner = getDbConnection()->prepare("SELECT arquivo FROM banners WHERE pagina = 'home'");
    $stmtBanner->execute();
    $bannerHome = ($stmtBanner->fetchColumn() ?: 'images/bannerHome.jpg');
} catch (Exception $e) {
    $bannerHome = 'images/bannerHome.jpg';
}
try {
    $stmtT = getDbConnection()->query("SELECT * FROM testemunhos ORDER BY id ASC");
    $testemunhosDb = $stmtT->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $testemunhosDb = [];
}
$bh = ['titulo' => 'Bioética', 'subtitulo' => 'Nos ajude a ajudar os animais usados na ciência.', 'texto' => 'Promovemos a substituição do uso prejudicial de animais em pesquisas, ensino e testes, por um futuro mais justo e consciente.'];
try { $r = getDbConnection()->query("SELECT * FROM conteudo_banner_home WHERE id = 1")->fetch(); if ($r) $bh = $r; } catch (Exception $e) {}
$ci = ['pretitulo' => null, 'titulo' => 'O que é <strong>bioética.</strong>', 'texto' => "É uma ponte que conecta Ciência e Ética\nA Bioética ajuda na construção de futuro um onde o avanço do conhecimento caminhe junto com o avanço moral da sociedade", 'imagem' => 'images/imgRato.jpg', 't1_titulo' => 'Menos sofrimento', 't1_texto' => 'Redução do uso de animais na ciência.', 't2_titulo' => 'Mais ciência', 't2_texto' => 'Fomento a métodos modernos e eficazes.', 't3_titulo' => 'Mais consciência', 't3_texto' => 'Formação de pessoas críticas e preparadas.'];
try { $r = getDbConnection()->query("SELECT * FROM conteudo_intro WHERE id = 1")->fetch(); if ($r) $ci = $r; } catch (Exception $e) {}
$cp = ['pretitulo' => 'Por que nos apoiar?', 'titulo' => 'Três frentes <strong>um só propósito</strong>', 'texto1' => '<p>Animais ainda sofrem todos os dias em nome da ciência, mesmo quando isso já poderia ser evitado.</p><p>Ao apoiar essa causa, você ajuda a mudar essa realidade de dentro para fora: formando profissionais, influenciando decisões e reduzindo o uso de animais de forma efetiva.</p><p>Cada contribuição gera impacto real. Menos sofrimento. Mais ciência. Mais consciência.</p>', 't1_titulo' => '+500 mil', 't1_texto' => 'Animais impactados diretamente por ano.', 't2_titulo' => 'Atuação', 't2_texto' => 'em comissões de éticas e políticas públicas.', 't3_titulo' => 'Formação', 't3_texto' => 'que transforma e multiplica o impacto', 'texto2' => '<p>O Fórum Animal trabalha para transformar a ciência, reduzindo e substituindo o uso de animais em pesquisas e ensino.</p><p>Em vez de atuar apenas nas consequências, atuamos na origem do problema: nas decisões que autorizam o uso de animais.</p><p>Por meio da atuação em comissões de ética (CEUAs), da formação de representantes da sociedade e da promoção de métodos alternativos, conseguimos gerar mudanças reais dentro de universidades, laboratórios e políticas públicas.</p><p>Esse trabalho já contribuiu para avanços importantes, como a proibição de testes em cosméticos e a adoção de métodos mais éticos e eficazes na ciência.</p><p>Ao apoiar essa causa, você não está apenas ajudando animais individualmente, você está ajudando a transformar todo o sistema que impacta milhões deles.</p>', 'botao_texto' => 'Saiba mais sobre bioética', 'botao_link' => '#bioetica'];
try { $r = getDbConnection()->query("SELECT * FROM conteudo_apoiar WHERE id = 1")->fetch(); if ($r) $cp = $r; } catch (Exception $e) {}
$ca = ['pretitulo' => 'Apadrinhe', 'titulo' => 'Veja porque sua doação <strong>pode mudar vidas.</strong>', 'texto' => '<p>Somos financiados exclusivamente por pessoas que acreditam na ciência sem animais. Atuamos onde as decisões realmente acontecem: nas comissões de ética, regulações e normas científicas.</p><p>Nossa missão é reduzir e substituir o uso de animais em pesquisa e ensino. É dentro das <strong>CEUAs (Comissões de Ética no Uso de Animais)</strong> que esse impacto começa.</p><p>Nosso principal instrumento é o <em>Curso de Formação em Proteção dos Animais nas CEUAs</em>, em parceria com a UFPR. Ele prepara representantes da sociedade civil para atuar nessas comissões, influenciando diretamente a aprovação de projetos com animais.</p><p>Cada representante pode impactar centenas ou até <strong>milhares de animais por ano</strong> em uma única instituição.</p>', 'imagem' => 'images/imgCientista.jpg', 'botao_texto' => 'QUERO APADRINHAR', 'botao_valor' => 120.00];
try { $r = getDbConnection()->query("SELECT * FROM conteudo_apadrinhe WHERE id = 1")->fetch(); if ($r) $ca = $r; } catch (Exception $e) {}

// ─── Visibilidade dos blocos ─────────────────────────────────────────────────
$_blocoKeys = ['bloco_banner','bloco_intro','bloco_apoiar','bloco_calculadora','bloco_apadrinhe','bloco_testemunhos'];
$blocos = array_fill_keys($_blocoKeys, true);
try {
    $stmt = getDbConnection()->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('" . implode("','", $_blocoKeys) . "')");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $_r) {
        $blocos[$_r['chave']] = ($_r['valor'] === '1');
    }
} catch (Exception $e) {}

// ─── Calculadora config ───────────────────────────────────────────────────────
$calcCfg = [
    'animal_1_nome' => 'Roedores', 'animal_1_pct' => 65.00, 'animal_1_imagem' => 'uploads/animais/imgRato.png',
    'animal_2_nome' => 'Peixes',   'animal_2_pct' => 20.00, 'animal_2_imagem' => 'uploads/animais/imgPeixe.png',
    'animal_3_nome' => 'Galinhas', 'animal_3_pct' =>  7.00, 'animal_3_imagem' => 'uploads/animais/imgGalinha.png',
    'animal_4_nome' => 'Outros',   'animal_4_pct' =>  8.00, 'animal_4_imagem' => 'uploads/animais/imgOutros.png',
    'valor_btn_1' => 30, 'valor_btn_2' => 60, 'valor_btn_3' => 120,
    'custo_por_animal' => 15.00,
    'calc_pretitulo' => 'Calculadora de impacto',
    'calc_titulo'    => 'Veja quantos animais <strong>você pode ajudar.</strong>',
    'calc_texto'     => '<p>Seu apoio forma pessoas, impulsiona mudanças reais.</p><p>Use a calculadora e veja o impacto real da sua doação.</p>',
];
try {
    $r = getDbConnection()->query("SELECT * FROM calculadora_config WHERE id = 1")->fetch();
    if ($r) $calcCfg = $r;
} catch (Exception $e) {}
function quillInline($html) {
    $html = trim($html);
    // Quill sempre envolve em <p>…</p> — retira essa camada para campos inline
    $html = preg_replace('/^<p>(.*)<\/p>$/s', '$1', $html);
    return $html;
}
function testimonialInitials($nome) {
    $words = preg_split('/\s+/', trim($nome));
    $a = mb_strtoupper(mb_substr($words[0], 0, 1, 'UTF-8'), 'UTF-8');
    $b = count($words) > 1 ? mb_strtoupper(mb_substr(end($words), 0, 1, 'UTF-8'), 'UTF-8') : '';
    return $a . $b;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<?php include ROOT . '/includes/assets.php';?>
<title>Animal não é carga - Início</title>
<style>
.homeHero { background-image: url('<?= BASE_URL . '/' . $bannerHome ?>') !important; }</style>
<style>
.homeHero__title strong, .homeHero__subtitle strong, .homeHero__text strong,
.homeIntro__lead strong, .homePillar__text strong,
.homeSponsor__text strong { font-weight: 600; }
.homeSponsor__text p { margin: 0 0 .8em; }
.homeSponsor__text p:last-child { margin-bottom: 0; }
.homeSupport__paragraphs strong, .homeSupport__textBlock strong { font-weight: 600; }
.homeSupport__paragraphs p { margin: 0 0 .8em; }
.homeSupport__paragraphs p:last-child { margin-bottom: 0; }
.homeSupport__textBlock p { margin: 0 0 .8em; }
.homeSupport__textBlock p:last-child { margin-bottom: 0; }
.homeSupport__button { margin-top: 32px; display: inline-block; }
.homeHero__text p { margin: 0 0 .4em; }
.homeHero__text p:last-child { margin-bottom: 0; }
.homeIntro__lead p { margin: 0 0 .6em; }
.homeIntro__lead p:last-child { margin-bottom: 0; }
</style>
<style>
.doacaoModal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999; align-items: center; justify-content: center; }
.doacaoModal.is-open { display: flex; }
.doacaoModal__overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,.55); }
.doacaoModal__box { position: relative; z-index: 1; background: #fff; border-radius: 12px; padding: 40px 36px; width: 100%; max-width: 440px; margin: 16px; box-shadow: 0 8px 40px rgba(0,0,0,.18); }
.doacaoModal__close { position: absolute; top: 14px; right: 18px; background: none; border: none; font-size: 28px; line-height: 1; cursor: pointer; color: #888; }
.doacaoModal__close:hover { color: #333; }
.doacaoModal__title { font-size: 1.3rem; font-weight: 700; margin: 0 0 4px; }
.doacaoModal__subtitle { font-size: .9rem; color: #666; margin: 0 0 24px; }
.doacaoModal__field { margin-bottom: 16px; }
.doacaoModal__label { display: block; font-size: .82rem; font-weight: 600; margin-bottom: 6px; color: #333; }
.doacaoModal__input { width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 8px; font-size: .95rem; box-sizing: border-box; }
.doacaoModal__input:focus { outline: none; border-color: #a01f2e; }
.doacaoModal__input.is-invalid { border-color: #dc3545; }
.doacaoModal__submit { width: 100%; margin-top: 8px; display: flex; justify-content: center; }
</style>
<style>
.homeAnimalResult__img { width: 62px; height: 62px; object-fit: contain; display: block; }
.homeAnimalResult__label { font-size: .72rem; font-weight: 600; color: #555; margin-top: 6px; text-align: center; letter-spacing: .02em; }
@media (max-width: 768px) {
  .homeAnimalResult__img { width: 45px; height: 45px; }
}
</style>
</head>

<body>

<?php include ROOT . '/includes/header/header.php';?>

<main class="home">
    <?php if ($blocos['bloco_banner']): ?>
    <section class="homeHero">
        <div class="container">
            <div class="homeHero__content">
                <h1 class="homeHero__title"><?= quillInline($bh['titulo']) ?></h1>
                <p class="homeHero__subtitle"><?= quillInline($bh['subtitulo']) ?></p>
                <div class="homeHero__text"><?= $bh['texto'] ?></div>
                <div class="homeHero__actions">
                    <a class="homeButton homeButton--primary" href="#calculadora">Quero apoiar</a>
                    <a class="homeButton homeButton--outline" href="#bioetica">Saiba mais <i class="icon icon-arrowDoiwn" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($blocos['bloco_intro']): ?>
    <section class="homeIntro" id="bioetica">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <div class="homeIntro__content">
                        <?php if (!empty($ci['pretitulo'])): ?>
                        <p class="homeEyebrow"><?= htmlspecialchars($ci['pretitulo']) ?></p>
                        <?php endif; ?>
                        <h2 class="homeTitle"><?= quillInline($ci['titulo']) ?></h2>
                        <div class="homeIntro__lead"><?= $ci['texto'] ?></div>

                        <div class="homeIntro__pillars">
                            <div class="homePillar">
                                <i class="icon icon-menossofrimento homePillar__icon" aria-hidden="true"></i>
                                <p class="homePillar__text"><strong><?= quillInline($ci['t1_titulo']) ?></strong><?= quillInline($ci['t1_texto']) ?></p>
                            </div>
                            <div class="homePillar">
                                <i class="icon icon-maisciencia homePillar__icon" aria-hidden="true"></i>
                                <p class="homePillar__text"><strong><?= quillInline($ci['t2_titulo']) ?></strong><?= quillInline($ci['t2_texto']) ?></p>
                            </div>
                            <div class="homePillar">
                                <i class="icon icon-maisconciencia homePillar__icon" aria-hidden="true"></i>
                                <p class="homePillar__text"><strong><?= quillInline($ci['t3_titulo']) ?></strong><?= quillInline($ci['t3_texto']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <img class="homeIntro__image" src="<?= BASE_URL . '/' . htmlspecialchars($ci['imagem']) ?>" alt="Rato em ambiente de laboratório">
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($blocos['bloco_apoiar']): ?>
    <section class="homeSupport">
        <div class="container">
            <?php if (!empty($cp['pretitulo'])): ?>
            <p class="homeEyebrow"><?= htmlspecialchars($cp['pretitulo']) ?></p>
            <?php endif; ?>
            <h2 class="homeTitle"><?= quillInline($cp['titulo']) ?></h2>
            <div class="homeSupport__paragraphs"><?= $cp['texto1'] ?></div>

            <div class="homeSupport__cards">
                <article class="homeImpactCard">
                    <i class="icon icon-livestock homeImpactCard__icon" aria-hidden="true"></i>
                    <p class="homeImpactCard__text"><strong><?= quillInline($cp['t1_titulo']) ?></strong><?= quillInline($cp['t1_texto']) ?></p>
                </article>
                <article class="homeImpactCard">
                    <i class="icon icon-atuacao homeImpactCard__icon" aria-hidden="true"></i>
                    <p class="homeImpactCard__text"><strong><?= quillInline($cp['t2_titulo']) ?></strong><?= quillInline($cp['t2_texto']) ?></p>
                </article>
                <article class="homeImpactCard">
                    <i class="icon icon-formacao homeImpactCard__icon" aria-hidden="true"></i>
                    <p class="homeImpactCard__text"><strong><?= quillInline($cp['t3_titulo']) ?></strong><?= quillInline($cp['t3_texto']) ?></p>
                </article>
            </div>

            <div class="homeSupport__textBlock"><?= $cp['texto2'] ?></div>

            <?php $cpTarget = ($cp['botao_target'] ?? '_self') === '_blank' ? '_blank' : '_self'; ?>
            <a class="homeButton homeButton--primary homeSupport__button"
               href="<?= htmlspecialchars($cp['botao_link']) ?>"
               target="<?= $cpTarget ?>"<?= $cpTarget === '_blank' ? ' rel="noopener noreferrer"' : '' ?>><?= htmlspecialchars($cp['botao_texto']) ?></a>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($blocos['bloco_calculadora']): ?>
    <section class="homeCalculator" id="calculadora">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-4">
                    <?php if (!empty($calcCfg['calc_pretitulo'])): ?>
                    <p class="homeEyebrow"><?= htmlspecialchars($calcCfg['calc_pretitulo']) ?></p>
                    <?php endif; ?>
                    <h2 class="homeTitle"><?= quillInline($calcCfg['calc_titulo']) ?></h2>
                    <?php if (!empty($calcCfg['calc_texto'])): ?>
                    <div class="homeCalculator__text"><?= $calcCfg['calc_texto'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-8">
                    <div class="homeCalculator__panel">
                        <div class="homeCalculator__form">
                            <h3 class="homeCalculator__label">Escolha um valor</h3>
                            <div class="homeCalculator__values">
                                <button class="homeCalculator__value" type="button">R$<?= (int)$calcCfg['valor_btn_1'] ?></button>
                                <button class="homeCalculator__value homeCalculator__value--active" type="button">R$<?= (int)$calcCfg['valor_btn_2'] ?></button>
                                <button class="homeCalculator__value" type="button">R$<?= (int)$calcCfg['valor_btn_3'] ?></button>
                            </div>
                            <input class="homeCalculator__input" type="text" placeholder="Outro valor (R$)" aria-label="Outro valor">

                            <h3 class="homeCalculator__label homeCalculator__label--spacing">Frequência</h3>
                            <div class="homeCalculator__frequency">
                                <button class="homeCalculator__frequencyButton homeCalculator__frequencyButton--active" type="button">Mensal</button>
                                <button class="homeCalculator__frequencyButton" type="button">Única</button>
                            </div>

                            <a class="homeButton homeButton--primary homeCalculator__donate" href="#">FAZER DOAÇÃO MENSAL</a>
                        </div>

                        <div class="homeCalculator__result">
                            <div class="homeCalculator__tooltip">
                                <i class="icon icon-interrogacao homeCalculator__help" aria-hidden="true"></i>
                                <div class="homeCalculator__tooltipBox" role="tooltip">Trata-se de uma simplificação baseada em estimativas globais, devendo ser interpretada com cautela, uma vez que a obtenção de valores precisos é dificultada pela subnotificação e pela falta de padronização nos sistemas de coleta e reporte de dados entre países.</div>
                            </div>
                            <div class="homeAnimalResult" data-animal="rato"><img class="homeAnimalResult__icon homeAnimalResult__img" src="<?= BASE_URL . '/' . htmlspecialchars($calcCfg['animal_1_imagem']) ?>" alt="<?= htmlspecialchars($calcCfg['animal_1_nome']) ?>"><strong class="homeAnimalResult__number">0</strong><span class="homeAnimalResult__label"><?= htmlspecialchars($calcCfg['animal_1_nome']) ?></span></div>
                            <div class="homeAnimalResult" data-animal="peixe"><img class="homeAnimalResult__icon homeAnimalResult__img" src="<?= BASE_URL . '/' . htmlspecialchars($calcCfg['animal_2_imagem']) ?>" alt="<?= htmlspecialchars($calcCfg['animal_2_nome']) ?>"><strong class="homeAnimalResult__number">0</strong><span class="homeAnimalResult__label"><?= htmlspecialchars($calcCfg['animal_2_nome']) ?></span></div>
                            <div class="homeAnimalResult" data-animal="galinha"><img class="homeAnimalResult__icon homeAnimalResult__img" src="<?= BASE_URL . '/' . htmlspecialchars($calcCfg['animal_3_imagem']) ?>" alt="<?= htmlspecialchars($calcCfg['animal_3_nome']) ?>"><strong class="homeAnimalResult__number">0</strong><span class="homeAnimalResult__label"><?= htmlspecialchars($calcCfg['animal_3_nome']) ?></span></div>
                            <div class="homeAnimalResult" data-animal="outros"><img class="homeAnimalResult__icon homeAnimalResult__img" src="<?= BASE_URL . '/' . htmlspecialchars($calcCfg['animal_4_imagem']) ?>" alt="<?= htmlspecialchars($calcCfg['animal_4_nome']) ?>"><strong class="homeAnimalResult__number">0</strong><span class="homeAnimalResult__label"><?= htmlspecialchars($calcCfg['animal_4_nome']) ?></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($blocos['bloco_apadrinhe']): ?>
    <section class="homeSponsor" id="apadrinhe">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <?php if (!empty($ca['pretitulo'])): ?>
                    <p class="homeEyebrow"><?= htmlspecialchars($ca['pretitulo']) ?></p>
                    <?php endif; ?>
                    <h2 class="homeTitle"><?= quillInline($ca['titulo']) ?></h2>
                    <div class="homeSponsor__text"><?= $ca['texto'] ?></div>
                    <button type="button"
                            class="homeButton homeButton--primary homeSponsor__button"
                            data-valor="<?= htmlspecialchars(number_format((float)($ca['botao_valor'] ?? 120), 2, '.', '')) ?>">
                        <?= htmlspecialchars($ca['botao_texto'] ?? 'QUERO APADRINHAR') ?></button>
                </div>

                <div class="col-lg-5">
                    <img class="homeSponsor__image" src="<?= BASE_URL . '/' . htmlspecialchars($ca['imagem']) ?>" alt="Cientista usando microscópio">
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($blocos['bloco_testemunhos']): ?>
    <section class="homeTestimonials" id="testemunhos">
        <style>
        .homeTestimonial__quote strong, .homeTestimonial__quote b { font-weight: 600; }
        .homeTestimonial__quote p { margin: 0 0 .5em; }
        .homeTestimonial__quote p:last-child { margin-bottom: 0; }
        </style>
        <div class="container">
            <p class="homeEyebrow homeEyebrow--center">Testemunhos</p>
            <h2 class="homeTitle homeTitle--center">Quem já fez, <strong>recomenda!</strong></h2>

            <div class="homeTestimonials__slider">
            <?php if (!empty($testemunhosDb)): ?>
                <?php foreach ($testemunhosDb as $t): ?>
                <div class="homeTestimonials__slide">
                    <article class="homeTestimonial">
                        <div class="homeTestimonial__quote"><?= $t['texto'] ?></div>
                        <div class="homeTestimonial__author">
                            <span class="homeTestimonial__avatar"><?= testimonialInitials($t['nome']) ?></span>
                            <p class="homeTestimonial__name">
                                <strong><?= htmlspecialchars($t['nome']) ?></strong>
                                <?= $t['profissao'] ? htmlspecialchars($t['profissao']) . '<br>' : '' ?>
                                <?= htmlspecialchars($t['edicao']) ?>
                            </p>
                        </div>
                        <button class="homeTestimonial__button" type="button">Ver mais</button>
                    </article>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="homeTestimonials__slide">
                    <article class="homeTestimonial">
                        <p class="homeTestimonial__quote">Curso extremamente completo, com grandes nomes da área e que abrange desde a filosofia da ética até metodologias alternativas. É ótimo para expandir a visão sobre o assunto por diversos ângulos. Recomendo a todos!</p>
                        <div class="homeTestimonial__author"><span class="homeTestimonial__avatar">GL</span><p class="homeTestimonial__name"><strong>Gabriella Lisboa</strong>Aluna da 1ª Edição do Curso</p></div>
                        <button class="homeTestimonial__button" type="button">Ver mais</button>
                    </article>
                </div>
                <div class="homeTestimonials__slide">
                    <article class="homeTestimonial">
                        <p class="homeTestimonial__quote">Confesso que o curso superou minhas expectativas. Alguns professores eu já conhecia e admirava, mas todos são absolutamente inteligentes e transmitem conhecimento com muita facilidade. Esse curso contribuiu demais com minha pesquisa de pós-graduação! Sou muito grata a toda a equipe.</p>
                        <div class="homeTestimonial__author"><span class="homeTestimonial__avatar">GC</span><p class="homeTestimonial__name"><strong>Gabriela Chueiri de Moraes</strong>Médica Veterinária, doutoranda em epidemiologia e saúde única pela USP.<br>Aluna da 1ª Edição do Curso</p></div>
                        <button class="homeTestimonial__button" type="button">Ver mais</button>
                    </article>
                </div>
                <div class="homeTestimonials__slide">
                    <article class="homeTestimonial">
                        <p class="homeTestimonial__quote">Sou presidente de uma CEUA e membro de uma outra. Ambas são CEUAS que trabalham quase que em sua totalidade avaliando propostas que envolvem a utilização de animais de produção em atividades de ensino ou pesquisa. Em ambas as CEUAs sempre tentamos verificar a real necessidade da execução da atividade e, também, sempre buscamos a possibilidade da redução do número de animais utilizados, ou, a utilização de métodos alternativos. O início do curso foi difícil para mim. Achei o discurso dos professores um pouco agressivo demais. Como não sou membro de uma organização protetora de animais, me senti como sendo julgado e culpado por estar atuando fora de uma ONG deste tipo. Mas, entendendo que o objetivo fundamental do curso é a capacitação de representantes de ONGs em CEUAS e o incentivo a que tais representantes sejam, de fato, atuantes; como respeito a ideia, fui em frente. Gostei demais do curso, especialmente das aulas ministradas pelos professores Vicente, Tales (muito top as aulas do módulo 3), Paula e Evelyne. O curso me trouxe várias informações que eu desconhecia e me levou a novas reflexões sobre o conteúdo apresentado, abrindo minha mente para uma nova percepção da experimentação animal como um todo.</p>
                        <div class="homeTestimonial__author"><span class="homeTestimonial__avatar">PA</span><p class="homeTestimonial__name"><strong>Paulo Augusto Esteves</strong>Embrapa Suínos e Aves<br>Aluno da 4ª Edição do Curso</p></div>
                        <button class="homeTestimonial__button" type="button">Ver mais</button>
                    </article>
                </div>
                <div class="homeTestimonials__slide">
                    <article class="homeTestimonial">
                        <p class="homeTestimonial__quote">Sou o coordenador da CEUA Mackenzie em São Paulo. Estou quase terminando o módulo 6 e portanto perto de terminar o curso. O nosso vice coordenador também está matriculado e esse curso está exercendo uma influência muito grande em nossa CEUA. Realmente o curso é muito bom e muito instrumentalização para nossa conduta como líderes de nossa CEUA...</p>
                        <div class="homeTestimonial__author"><span class="homeTestimonial__avatar">MC</span><p class="homeTestimonial__name"><strong>Marcelo Coelho Almeida</strong>Psicólogo, Doutor em Educação Arte e História da Cultura<br>Coordenador da CEUA Mackenzie - Aluno da 5ª Edição</p></div>
                        <button class="homeTestimonial__button" type="button">Ver mais</button>
                    </article>
                </div>
                <div class="homeTestimonials__slide">
                    <article class="homeTestimonial">
                        <p class="homeTestimonial__quote">Terminei o curso e fiquei deslumbrado de tantas informações preciosas.</p>
                        <div class="homeTestimonial__author"><span class="homeTestimonial__avatar">RF</span><p class="homeTestimonial__name"><strong>Rafael Ferreira Muniz</strong>Eng. de Pesca / Eng. de Seg. do Trabalho<br>Aluno da 5ª Edição do Curso</p></div>
                        <button class="homeTestimonial__button" type="button">Ver mais</button>
                    </article>
                </div>
                <div class="homeTestimonials__slide">
                    <article class="homeTestimonial">
                        <p class="homeTestimonial__quote">O Curso para Proteção dos Animais na Ciência oferecido pelo FNPDA é fundamental para quem se propõe a representar os interesses dos animais, pois nos possibilita obter conhecimento técnico, legislação, dicas de especialistas e membros mais experientes, de forma a otimizar nossos esforços para a discussão dos casos apresentados nas CEUAs e a busca de caminhos alternativos que beneficiem os animais não humanos.</p>
                        <div class="homeTestimonial__author"><span class="homeTestimonial__avatar">AT</span><p class="homeTestimonial__name"><strong>Alexandre Terreri</strong>Ativista da causa animal<br>Aluno da 1ª Edição do Curso</p></div>
                        <button class="homeTestimonial__button" type="button">Ver mais</button>
                    </article>
                </div>
                <div class="homeTestimonials__slide">
                    <article class="homeTestimonial">
                        <p class="homeTestimonial__quote">Olá, meu nome é Eduardo, fiz a 3ª edição do curso em 2023. Embora já tivesse estudado um pouco sobre o tema, o curso foi fundamental para meu ingresso e atuação em Comissões de Ética. A diversidade de conteúdos permitiu revisar e expandir conhecimentos essenciais na atuação em defesa dos animais em CEUAs. Mais do que o conteúdo em si, a organização do curso me forneceu segurança e melhores maneiras de argumentar e me portar para alcançar melhores resultados para os animais nas discussões dos protocolos. Vejo como indispensável a proposição de cursos como este, que pode atingir o público docente que utiliza os animais, abrindo portas para novas abordagens experimentais, assim como para reunir, fortalecer e amadurecer pessoas que tem o desejo de ajudar os animais nos contextos de experimentação científica.</p>
                        <div class="homeTestimonial__author"><span class="homeTestimonial__avatar">EH</span><p class="homeTestimonial__name"><strong>Eduardo Henrique Gonçalves</strong>Biólogo, pesquisador em bem-estar animal<br>Aluno da 3ª Edição do Curso</p></div>
                        <button class="homeTestimonial__button" type="button">Ver mais</button>
                    </article>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    <div class="doacaoModal" id="doacaoModal">
        <div class="doacaoModal__overlay"></div>
        <div class="doacaoModal__box">
            <button class="doacaoModal__close" type="button" aria-label="Fechar">&times;</button>
            <h2 class="doacaoModal__title">Complete seus dados</h2>
            <p class="doacaoModal__subtitle" id="doacaoModalSubtitle"></p>
            <form id="doacaoModalForm" novalidate>
                <div class="doacaoModal__field">
                    <label class="doacaoModal__label" for="doacaoNome">Nome completo</label>
                    <input class="doacaoModal__input" type="text" id="doacaoNome" placeholder="Seu nome" autocomplete="name">
                </div>
                <div class="doacaoModal__field">
                    <label class="doacaoModal__label" for="doacaoEmail">E-mail</label>
                    <input class="doacaoModal__input" type="email" id="doacaoEmail" placeholder="seu@email.com" autocomplete="email">
                </div>
                <div class="doacaoModal__field">
                    <label class="doacaoModal__label" for="doacaoTelefone">Telefone</label>
                    <input class="doacaoModal__input" type="tel" id="doacaoTelefone" placeholder="(00) 00000-0000" autocomplete="tel">
                </div>
                <button class="homeButton homeButton--primary doacaoModal__submit" type="submit" id="doacaoSubmit">Ir para o pagamento</button>
            </form>
        </div>
    </div>
    <div class="cookiesOverlay" aria-hidden="true" style="display:none"></div>
    <div class="cookiesModal" role="dialog" aria-label="Aviso de cookies" style="display:none">
        <div class="cookiesModal__content">
            <p class="cookiesModal__text">
                Utilizamos cookies para melhorar sua experiência, personalizar conteúdos e analisar o tráfego do site. Ao continuar navegando, você concorda com nossa <a class="cookiesModal__link" href="#politica-privacidade">Política de Privacidade</a> e uso de cookies.
            </p>
            <div class="cookiesModal__actions">
                <button class="cookiesModal__button cookiesModal__button--outline" type="button">Recusar</button>
                <button class="cookiesModal__button cookiesModal__button--primary" type="button">Aceitar</button>
            </div>
        </div>
    </div>
    <section class="privacyModal" id="politica-privacidade" role="dialog" aria-labelledby="privacyModalTitle" aria-modal="true">
        <div class="privacyModal__overlay"></div>
        <div class="privacyModal__box">
            <div class="privacyModal__header">
                <div>
                    <p class="privacyModal__eyebrow">Fórum Animal</p>
                    <h2 class="privacyModal__title" id="privacyModalTitle">Política de Privacidade</h2>
                </div>
                <a class="privacyModal__close" href="#" aria-label="Fechar">&times;</a>
            </div>

            <div class="privacyModal__content">
                <p>O Fórum Animal valoriza a privacidade e a proteção dos dados pessoais de seus usuários, apoiadores e visitantes. Esta Política de Privacidade tem como objetivo explicar de forma clara como realizamos a coleta, utilização e armazenamento das informações fornecidas em nosso site, em conformidade com a Lei Geral de Proteção de Dados (LGPD – Lei nº 13.709/2018).</p>

                <h3>1. Dados coletados</h3>
                <p>Ao utilizar nossos formulários, poderemos coletar os seguintes dados pessoais:</p>
                <ul>
                    <li>Nome</li>
                    <li>E-mail</li>
                    <li>Telefone</li>
                </ul>
                <p>Essas informações são fornecidas voluntariamente pelo usuário por meio de formulários de contato, cadastro, campanhas, newsletters ou ações promovidas pelo Fórum Animal.</p>

                <h3>2. Finalidade da coleta de dados</h3>
                <p>Os dados coletados são utilizados para:</p>
                <ul>
                    <li>Entrar em contato com o usuário;</li>
                    <li>Enviar informações sobre campanhas, projetos e iniciativas do Fórum Animal;</li>
                    <li>Responder solicitações e dúvidas;</li>
                    <li>Compartilhar novidades, comunicados e conteúdos relacionados às atividades da organização;</li>
                    <li>Melhorar a experiência de navegação e comunicação com nossos usuários.</li>
                </ul>

                <h3>3. Compartilhamento de dados</h3>
                <p>O Fórum Animal não vende, comercializa ou compartilha seus dados pessoais com terceiros, exceto quando necessário para cumprimento de obrigações legais ou operacionais relacionadas aos serviços utilizados pela organização.</p>

                <h3>4. Armazenamento e segurança</h3>
                <p>Adotamos medidas técnicas e organizacionais adequadas para proteger os dados pessoais contra acessos não autorizados, vazamentos, alterações ou qualquer forma de uso inadequado.</p>

                <h3>5. Cookies</h3>
                <p>Nosso site pode utilizar cookies para melhorar a experiência de navegação, analisar acessos e otimizar funcionalidades. O usuário pode gerenciar as permissões de cookies diretamente em seu navegador.</p>

                <h3>6. Direitos do titular dos dados</h3>
                <p>Nos termos da LGPD, o usuário pode, a qualquer momento:</p>
                <ul>
                    <li>Solicitar acesso aos seus dados;</li>
                    <li>Corrigir informações incompletas ou desatualizadas;</li>
                    <li>Solicitar a exclusão de seus dados;</li>
                    <li>Revogar o consentimento concedido anteriormente.</li>
                </ul>

                <h3>7. Contato</h3>
                <p>Em caso de dúvidas sobre esta Política de Privacidade ou sobre o tratamento de dados pessoais, entre em contato conosco pelos canais oficiais do Fórum Animal.</p>

                <h3>8. Atualizações desta política</h3>
                <p>Esta Política de Privacidade poderá ser atualizada periodicamente para refletir melhorias em nossos processos ou adequações legais.</p>
            </div>
        </div>
    </section>
</main>

<?php include ROOT . '/includes/footer/footer.php';?>
<?php include ROOT . '/includes/scripts.php';?>
<?php
$version = time();
$calcJs = [
    'animals' => [
        ['slug' => 'rato',    'nome' => $calcCfg['animal_1_nome'], 'pct' => (float)$calcCfg['animal_1_pct'] / 100],
        ['slug' => 'peixe',   'nome' => $calcCfg['animal_2_nome'], 'pct' => (float)$calcCfg['animal_2_pct'] / 100],
        ['slug' => 'galinha', 'nome' => $calcCfg['animal_3_nome'], 'pct' => (float)$calcCfg['animal_3_pct'] / 100],
        ['slug' => 'outros',  'nome' => $calcCfg['animal_4_nome'], 'pct' => (float)$calcCfg['animal_4_pct'] / 100],
    ],
    'custoAnimal' => (float)$calcCfg['custo_por_animal'],
    'btnValores'  => [(int)$calcCfg['valor_btn_1'], (int)$calcCfg['valor_btn_2'], (int)$calcCfg['valor_btn_3']],
];
echo '<script>window.APP_BASE_URL = "' . BASE_URL . '"; window.CALC_CONFIG = ' . json_encode($calcJs) . ';</script>';
echo '<script src="' . BASE_URL . '/pages/inicio/home.js?' . $version . '"></script>';
?>

</body>
</html>
