<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class AdminMediaBlocked
 */
class AdminMediaBlocked extends ControllerBase
{
    protected function display()
    {
        $view = $this->getView('AdminMediaBlocked');

        $sql = "SELECT * 
		FROM block 
		ORDER BY pageid ASC
		LIMIT 200"; // TODO - pager
        $blocks = $this->smt->database->queryAsArray($sql);
        if (!$blocks || !is_array($blocks)) {
            $blocks = [];
        }

        $width = 220;

        $this->smt->title = 'Blocked Media Admin';
        $this->smt->includeHeader();
        $this->smt->includeMediumMenu();
        $this->smt->includeAdminMenu();

        /** @noinspection PhpIncludeInspection */
        include($view);

        $this->smt->includeFooter();
    }
}
