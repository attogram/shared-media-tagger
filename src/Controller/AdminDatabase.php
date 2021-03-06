<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Database\DatabaseUpdater;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class AdminDatabase
 */
class AdminDatabase extends ControllerBase
{
    protected function display()
    {
        $this->smt->title = 'Database Admin';
        $this->smt->includeHeader();
        $this->smt->includeTemplate('Menu');
        $this->smt->includeTemplate('AdminMenu');

        $data = [];
        $data['action'] = !empty($_GET['a']) ? $_GET['a'] : '';
        $data['databaseName'] = realpath($this->smt->database->databaseName);
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
                    $this->smt->database->emptyTopicTables();
                    $data['result'] = 'Emptied Topic Tables';
                    break;
                case 'et':
                    $this->smt->database->emptyTaggingTables();
                    $data['result'] = 'Emptied Tagging Tables';
                    break;
                case 'eu':
                    $this->smt->database->emptyUserTables();
                    $data['result'] = 'Emptied User tables';
                    break;
                case 'migrate':
                    $data['result'] = $this->migration();
                    break;
            }
        }

        /** @noinspection PhpIncludeInspection */
        include($this->getView('AdminDatabase'));

        $this->smt->includeFooter();
    }

    /**
     * @return string
     */
    private function migration()
    {
        if (empty($_GET['migrate'])) {
            return 'ERROR: Migration Not Found';
        }
        $migrationNumber = Tools::safeString($_GET['migrate']);


        $namespace = 'Attogram\\SharedMedia\\Tagger\\Database\\';
        $migrationClass = $namespace . 'Migration' . $migrationNumber;

        if (!class_exists($migrationClass)) {
            return 'ERROR: Migration Class Not Found: ' . $migrationClass;
        }

        $class = new $migrationClass();
        $result = $class->migrate($this->smt->database);

        return $result;
    }
}
