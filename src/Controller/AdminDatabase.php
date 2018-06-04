<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\DatabaseUpdater;

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
                    $data['result'] = '<p>Creating Database tables:</p>'
                        . $databaseUpdater->createTables();
                    break;
                case 'seed':
                    /** @noinspection PhpUndefinedVariableInspection */
                    $data['result'] = '<p>Seeding Demo Setup:</p>'
                        . implode('<br />', $databaseUpdater->seedDemo());
                    break;
                case 'd':
                    $data['result'] = '<p>Dropping Database tables:</p>'
                        . print_r($this->smt->database->dropTables(), true);
                    break;
                case 'em':
                    $data['result'] = '<p>Emptying Media tables:</p>'
                        . print_r($this->smt->database->emptyMediaTables(), true);
                    break;
                case 'ec':
                    $data['result'] = '<p>Emptying Category tables:</p>'
                        . print_r($this->smt->database->emptyCategoryTables(), true);
                    break;
                case 'et':
                    $data['result'] = '<p>Emptying Tagging tables:</p>'
                        . print_r($this->smt->database->emptyTaggingTables(), true);
                    break;
                case 'eu':
                    $data['result'] = '<p>Emptying User tables:</p>'
                        . print_r($this->smt->database->emptyUserTables(), true);
                    break;
            }
        }

        $this->smt->title = 'Database Admin';
        $this->smt->includeHeader();
        $this->smt->includeMediumMenu();
        $this->smt->includeAdminMenu();

        /** @noinspection PhpIncludeInspection */
        include($view);

        $this->smt->includeFooter();
    }
}
