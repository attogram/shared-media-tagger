<?php
/**
 * Shared Media Tagger
 * Html Footer Template
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

$serverName = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;

?>
    <div class="row bg-dark pt-4 text-center text-secondary">
        <div class="h6 col-12 small mb-5">
            <p>
                Hosted by:
                <a class="text-white-50" href="//<?= $serverName ?>/">
                    <?= $serverName ?>
                </a>
            </p>
            <p>
                Powered by:
                <a class="text-white-50" target="c" href="<?= Tools::url('github_smt') ?>">
                    Shared Media Tagger v<?= SHARED_MEDIA_TAGGER ?>
                </a>
            </p>
            <?php if (Tools::isAdmin()) { ?>
            <p>
                <a class="text-white-50" href="<?= Tools::url('admin') ?>">
                    🔧 Admin: <?= $_SESSION['user'] ?>
                </a>
            </p>
            <p>
                <a class="text-white-50" href="<?= Tools::url('logout') ?>">
                    Logout
                </a>
            </p>
            <?php } ?>
        </div>
    </div>
    <?php if (!empty($this->customSiteFooter)) { ?>
    <div class="row">
        <div class="col-12">
            <?= $this->customSiteFooter ?>
        </div>
    </div>
    <?php } ?>
</div><?php /* end container div from HtmlHeader.php */ ?>
</body>
</html>

