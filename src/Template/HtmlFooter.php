<?php
/**
 * Shared Media Tagger
 * Html Footer Template
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

$serverName = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;

?>
    <div class="row bg-dark text-right text-secondary mt-1 mb-3 small">
        <div class="col">
            Powered by <a class="text-white-50" target="c" href="<?=
            Tools::url('github_smt')
            ?>">Shared Media Tagger v<?=
                SHARED_MEDIA_TAGGER
            ?></a>
            <br />
            Hosted by <a class="text-white-50" href="//<?=
                $serverName
            ?>/"><?=
                $serverName
            ?></a>
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

