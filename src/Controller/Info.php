<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class Info
 */
class Info extends ControllerBase
{
    protected function display()
    {
        $view = $this->getView('Info');

        if (!isset($_GET['i']) || !$_GET['i'] || !Tools::isPositiveNumber($_GET['i'])) {
            $this->smt->fail404('404 Media Request Not Found');
        }

        $pageid = (int) $_GET['i'];

        $media = $this->smt->database->getMedia($pageid);
        if (!$media || !isset($media[0]) || !is_array($media[0])) {
            $this->smt->fail404('404 Media Not Found');
        }
        $media = $media[0];

        $this->smt->useBootstrap = true;

        $this->smt->title = 'Info: ' . Tools::stripPrefix($media['title']);
        $this->smt->useBootstrap = true;
        $this->smt->useJquery = true;
        $this->smt->includeHeader();
        $this->smt->includeMediumMenu();

        /** @noinspection PhpIncludeInspection */
        include($view);

        $this->smt->includeFooter();
    }
}
