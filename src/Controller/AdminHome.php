<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\DatabaseUpdater;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class AdminHome
 */
class AdminHome extends ControllerBase
{
    protected function display()
    {
        $view = $this->getView('AdminHome');

        $siteCount = $this->smt->database->queryAsArray(
            'SELECT count(id) AS count FROM site'
        );
        if (!$siteCount) {
            Tools::notice('Welcome to your new Site!  Creating new site setup...');
            $databaseUpdater = new DatabaseUpdater();
            $databaseUpdater->setDatabase($this->smt->database);
            $databaseUpdater->createTables();
            $databaseUpdater->seedDemo();

        }

        $this->smt->title = 'Admin';
        $this->smt->includeHeader();
        $this->smt->includeMediumMenu();
        $this->smt->includeAdminMenu();

        /** @noinspection PhpIncludeInspection */
        include($view);

        $this->smt->includeFooter();
    }
}
