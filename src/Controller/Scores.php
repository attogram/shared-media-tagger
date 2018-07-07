<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;

/**
 * Class Scores
 */
class Scores extends ControllerBase
{
    protected function display()
    {
        $this->smt->title = 'Top Scores - ' . Config::$siteName;
        $this->smt->includeHeader();
        $this->smt->includeTemplate('MenuSmall');

        /** @noinspection PhpUnusedLocalVariableInspection */
        $scores = $this->smt->database->getMediasByScore(100);

        /** @noinspection PhpIncludeInspection */
        include($this->getView('Scores'));

        $this->smt->includeFooter();
    }
}
