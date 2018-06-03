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
            $createdTables = $databaseUpdater->createTables();
            $seededDemo = implode('<br />', $databaseUpdater->seedDemo());
            Tools::notice('<pre>Created Tables:<br />' . $createdTables . '</pre>');
            Tools::notice('<pre>Seeded Demo:<br />' . $seededDemo . '</pre>');
        }

        $data = [];
        $data['messageCount'] = $this->getMessageCount();

        $this->smt->title = 'Admin';
        $this->smt->includeHeader();
        $this->smt->includeMediumMenu();
        $this->smt->includeAdminMenu();

        /** @noinspection PhpIncludeInspection */
        include($view);

        $this->smt->includeFooter();
    }

    /**
     * @return int
     */
    private function getMessageCount()
    {
        $msgCount = 0;
        $result = $this->smt->database->queryAsArray(
            'SELECT count(id) AS count FROM contact'
        );
        if (isset($result[0]['count'])) {
            $msgCount = $result[0]['count'];
        }

        return $msgCount;
    }
}
