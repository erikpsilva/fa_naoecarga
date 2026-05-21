<?php
require_once ROOT . '/config/database.php';

try {
    $pdo  = getDbConnection();
    $stmt = $pdo->prepare(
        "SELECT chave, valor FROM configuracoes
         WHERE chave IN ('link_instagram','link_facebook','link_youtube','link_doe_agora','link_doe_agora_target','link_seja_voluntario','link_seja_voluntario_target')"
    );
    $stmt->execute();
    $footerLinks = [];
    foreach ($stmt->fetchAll() as $row) {
        $footerLinks[$row['chave']] = $row['valor'];
    }
    $stmtInst    = $pdo->query("SELECT nome, url, target FROM footer_links_institucional ORDER BY ordem ASC LIMIT 5");
    $instLinks   = $stmtInst->fetchAll();
} catch (Exception $e) {
    $footerLinks = [];
    $instLinks   = [];
}

$linkInstagram     = !empty($footerLinks['link_instagram'])     ? $footerLinks['link_instagram']     : null;
$linkFacebook      = !empty($footerLinks['link_facebook'])      ? $footerLinks['link_facebook']      : null;
$linkYoutube       = !empty($footerLinks['link_youtube'])       ? $footerLinks['link_youtube']       : null;
$linkDoeAgora           = !empty($footerLinks['link_doe_agora'])             ? $footerLinks['link_doe_agora']             : '#calculadora';
$linkDoeAgoraTarget     = ($footerLinks['link_doe_agora_target'] ?? '') === '_blank' ? '_blank' : '_self';
$linkSejaVoluntario     = !empty($footerLinks['link_seja_voluntario'])         ? $footerLinks['link_seja_voluntario']       : '#apadrinhe';
$linkSejaVoluntarioTarget = ($footerLinks['link_seja_voluntario_target'] ?? '') === '_blank' ? '_blank' : '_self';
?>
<footer class="footer">
    <div class="footerCta">
        <div class="container">
            <div class="footerCta__content">
                <div class="footerCta__message">
                    <i class="icon icon-maisciencia footerCta__icon" aria-hidden="true"></i>
                    <p class="footerCta__text">
                        <strong>Você pode fazer parte dessa transformação.</strong>
                        Apoie, doe, apadrinhe e ajude a construir um futuro ético e consciente.
                    </p>
                </div>

                <div class="footerCta__actions">
                    <a class="footerCta__button footerCta__button--light"
                       href="<?= htmlspecialchars($linkDoeAgora) ?>"
                       target="<?= $linkDoeAgoraTarget ?>"
                       <?= $linkDoeAgoraTarget === '_blank' ? 'rel="noopener noreferrer"' : '' ?>>
                        <i class="icon icon-maisciencia" aria-hidden="true"></i>
                        Doe agora
                    </a>
                    <a class="footerCta__button footerCta__button--outline"
                       href="<?= htmlspecialchars($linkSejaVoluntario) ?>"
                       target="<?= $linkSejaVoluntarioTarget ?>"
                       <?= $linkSejaVoluntarioTarget === '_blank' ? 'rel="noopener noreferrer"' : '' ?>>Seja voluntário</a>
                </div>
            </div>
        </div>
    </div>

    <div class="footerMain">
        <div class="container">
            <div class="row align-items-start">
                <div class="col-md-4">
                    <a class="footerMain__brand" href="<?= BASE_URL ?>/inicio" aria-label="Fórum Nacional de Proteção e Defesa Animal">
                        <img class="footerMain__logo" src="<?= BASE_URL ?>/images/logoForumAnimal.png" alt="Fórum Nacional de Proteção e Defesa Animal">
                    </a>
                </div>

                <div class="col-md-4">
                    <div class="footerMain__social">
                        <h3 class="footerMain__title">Redes sociais</h3>
                        <div class="footerMain__socialList">
                            <?php if ($linkInstagram): ?>
                            <a class="footerMain__socialLink" href="<?= htmlspecialchars($linkInstagram) ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                                <i class="icon icon-instagram" aria-hidden="true"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($linkFacebook): ?>
                            <a class="footerMain__socialLink" href="<?= htmlspecialchars($linkFacebook) ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                                <i class="icon icon-facebook" aria-hidden="true"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($linkYoutube): ?>
                            <a class="footerMain__socialLink" href="<?= htmlspecialchars($linkYoutube) ?>" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
                                <i class="icon icon-youtube" aria-hidden="true"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($instLinks)): ?>
                <div class="col-md-4">
                    <div class="footerMain__institutional">
                        <h3 class="footerMain__title">Institucional</h3>
                        <?php foreach ($instLinks as $instLink): ?>
                            <?php if (empty($instLink['nome']) && empty($instLink['url'])) continue; ?>
                            <a class="footerMain__link"
                               href="<?= htmlspecialchars($instLink['url'] ?: '#') ?>"
                               target="<?= $instLink['target'] ?>"
                               <?= $instLink['target'] === '_blank' ? 'rel="noopener noreferrer"' : '' ?>>
                                <?= htmlspecialchars($instLink['nome']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="footerMain__copyright">
                Fórum Animal 2026 &copy; Todos os direitos reservados
            </div>
        </div>
    </div>
</footer>
