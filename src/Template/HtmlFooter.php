<?php
/**
 * Shared Media Tagger
 * Html Footer Template
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

?>
<footer>
    <div class="menu" style="line-height:2; font-size:80%;">
    <?php
    if (empty(Config::$setup['hide_hosted_by']) || !Config::$setup['hide_hosted_by']) {
        $serverName = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null; ?>
        <span class="nobr">
            Hosted by
            <b><a href="//<?= $serverName ?>/"><?= $serverName ?></a></b>
        </span>
    <?php } ?>
    &nbsp; &nbsp; &nbsp; &nbsp;
    <span class="nobr">
        Powered by
        <b><a target="c" href="<?= Tools::url('github_smt') ?>">Shared Media Tagger
        v<?= SHARED_MEDIA_TAGGER ?></a></b>
    </span>
    </div>
</footer>
<?php if (!empty($_SESSION['user'])) { ?>
<p class="grey center">
    <a href="<?= Tools::url('admin') ?>">🔧 Admin: <b><?= $_SESSION['user'] ?></b></a>
    &nbsp; &nbsp;
    <a href="<?= Tools::url('logout') ?>">Logout</a>
</p>
<?php } ?>
<?= $this->customSiteFooter ?>
</body>
</html>
