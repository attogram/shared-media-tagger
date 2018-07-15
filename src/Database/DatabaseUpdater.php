<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Database;

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class DatabaseUpdater
 */
class DatabaseUpdater
{
    /** @var Database|DatabaseAdmin */
    private $database;

    /**
     * @param Database|DatabaseAdmin $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * Create Database and Tables
     */
    public function createTables()
    {
        if (!file_exists($this->database->databaseName)) {
            if (!@touch($this->database->databaseName)) {
                Tools::error('Can Not Create Database in ' . dirname($this->database->databaseName));

                return;
            }
        }
        //Tools::debug('New Database opened: ' . $this->database->databaseName);

        $sqlFile = file_get_contents(Config::$sourceDirectory . '/Sql/database.sql');
        $sqls = explode(';', $sqlFile);
        foreach ($sqls as $sql) {
            $this->database->queryAsBool($sql);
            //Tools::debug('Table created:<br /><textarea rows="10" cols="70">' . $sql . '</textarea>');
        }
    }

    /**
     * Seed Demo
     */
    public function seedDemo()
    {
        $sqlFile = file_get_contents(Config::$sourceDirectory . '/Sql/demo.sql');
        $sqls = explode(';', $sqlFile);
        foreach ($sqls as $sql) {
            $this->database->queryAsBool($sql);
            //Tools::debug('Demo seed:<br /><textarea rows="10" cols="70">' . $sql . '</textarea>');
        }
    }
}
