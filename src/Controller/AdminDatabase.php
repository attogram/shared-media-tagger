<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Database\DatabaseUpdater;

/**
 * Class AdminDatabase
 */
class AdminDatabase extends ControllerBase
{
    protected function display()
    {
        $view = $this->getView('AdminDatabase');

        $data = [];
        $data['action'] = !empty($_GET['a']) ? $_GET['a'] : '';
        $data['databaseName'] = $this->smt->database->databaseName;
        $data['databaseWriteable'] = is_writeable($this->smt->database->databaseName);
        $data['databaseSize'] = file_exists($this->smt->database->databaseName)
            ? number_format((float) filesize($this->smt->database->databaseName))
            : 'null';

        $data['result'] = '';

        if (!empty($data['action'])) {
            switch ($data['action']) {
                case 'create':
                case 'seed':
                    $databaseUpdater = new DatabaseUpdater();
                    $databaseUpdater->setDatabase($this->smt->database);
                    break;
            }
            switch ($data['action']) {
                case 'create':
                    /** @noinspection PhpUndefinedVariableInspection */
                    $databaseUpdater->createTables();
                    $data['result'] = 'Created Database Tables';
                    break;
                case 'seed':
                    /** @noinspection PhpUndefinedVariableInspection */
                    $databaseUpdater->seedDemo();
                    $data['result'] = 'Demo Setup Seeded';
                    break;
                case 'd':
                    $this->smt->database->dropTables();
                    $data['result'] = 'Dropped All Database Tables';
                    break;
                case 'em':
                    $this->smt->database->emptyMediaTables();
                    $data['result'] = 'Emptied Media Tables';
                    break;
                case 'ec':
                    $this->smt->database->emptyCategoryTables();
                    $data['result'] = 'Emptied Category Tables';
                    break;
                case 'et':
                    $this->smt->database->emptyTaggingTables();
                    $data['result'] = 'Emptied Tagging Tables';
                    break;
                case 'eu':
                    $this->smt->database->emptyUserTables();
                    $data['result'] = 'Emptied User tables';
                    break;
            }
        }

        $this->smt->title = 'Database Admin';
        $this->smt->includeHeader();
        $this->smt->includeTemplate('MenuSmall');
        $this->smt->includeAdminMenu();

        /** @noinspection PhpIncludeInspection */
        include($view);

        $this->smt->includeFooter();
    }
}
