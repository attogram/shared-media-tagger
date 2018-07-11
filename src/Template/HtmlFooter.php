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
    <br />
    <br />
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
<br />
<br />
</div><?php /* end container div from HtmlHeader.php */ ?>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"
integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
</body>
</html>

