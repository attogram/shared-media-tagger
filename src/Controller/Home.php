<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class Home
 */
class Home extends ControllerBase
{
    protected function display()
    {
        $view = $this->getView('Home');

        $data = $this->smt->database->getSite();
        if (empty($data['about'])) {
            $data['about'] = 'Site Offline: Database is not accessible.';
        }
        $data['name'] = !empty($data['name']) ? $data['name'] : 'Shared Media Tagger';

        $data['random'] = $this->smt->database->getRandomMedia(4);

        $this->smt->title = Config::$siteName;
        $this->smt->useBootstrap = true;
        $this->smt->useJquery = true;
        $this->smt->includeHeader();
        $this->smt->includeMenu();
        /** @noinspection PhpIncludeInspection */
        include($view);
        $this->smt->includeFooter();
    }
}
