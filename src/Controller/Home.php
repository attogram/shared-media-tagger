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
        $this->checkOldUris();
        $data = $this->smt->database->getSite();
        if (empty($data['about'])) {
            $data['about'] = 'This website is temporarily offline.';
        }
        $data['name'] = !empty($data['name']) ? $data['name'] : 'Shared Media Tagger';
        $data['random'] = $this->smt->database->getRandomMedia(4);
        $data['countFiles'] = number_format((float) $this->smt->database->getFileCount());
        $data['countTopics'] = number_format((float) $this->smt->database->getTopicsCount());
        $data['countVotes'] = number_format((float) $this->smt->database->getTotalVotesCount());
        $this->smt->title = Config::$siteName;
        $this->smt->includeHeader();
        $this->smt->includeTemplate('Menu');
        /** @noinspection PhpIncludeInspection */
        include($this->getView('Home'));
        $this->smt->includeFooter();
    }

    private function checkOldUris()
    {
        if (!empty($_GET['i']) && Tools::isPositiveNumber($_GET['i'])) { // v.0 old uris
            Tools::redirect301(Tools::url('info') . '/' . $_GET['i']);
        }
    }
}
