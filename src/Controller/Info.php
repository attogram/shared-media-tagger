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

        $vars = $this->smt->router->getVars();
        if (empty($vars[0]) || !Tools::isPositiveNumber($vars[0])) {
            $this->smt->fail404('404 Media Request Not Found');
        }

        $pageid = (int) $vars[0];

        $media = $this->smt->database->getMedia($pageid);
        if (!$media || !isset($media[0]) || !is_array($media[0])) {
            $this->smt->fail404('404 Media Not Found');
        }
        $media = $media[0];

        $data = [];
        $data['admin'] =  $this->smt->displayAdminMediaFunctions($media['pageid']);

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
