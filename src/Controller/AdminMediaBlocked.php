<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

/**
 * Class AdminMediaBlocked
 */
class AdminMediaBlocked extends ControllerBase
{
    protected function display()
    {
        $data = [];
        $data['blocks'] = $this->smt->database->queryAsArray(
            'SELECT * FROM block ORDER BY pageid ASC LIMIT 50' // TODO - pager
        );
        $data['width'] = 220;

        $this->smt->title = 'Blocked Media Admin';
        $this->smt->includeHeader();
        $this->smt->includeMediumMenu();
        $this->smt->includeAdminMenu();

        $view = $this->getView('AdminMediaBlocked');
        /** @noinspection PhpIncludeInspection */
        include($view);

        $this->smt->includeFooter();
    }
}
