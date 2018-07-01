<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

/**
 * Class AdminHome
 */
class AdminHome extends ControllerBase
{
    protected function display()
    {
        $this->smt->title = 'Admin';
        $this->smt->includeHeader();
        $this->smt->includeMediumMenu();
        $this->smt->includeAdminMenu();

        /** @noinspection PhpIncludeInspection */
        include($this->getView('AdminHome'));

        $this->smt->includeFooter();
    }
}
