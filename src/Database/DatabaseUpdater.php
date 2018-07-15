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
        if (!is_dir(Config::$databaseDirectory)) {
            Tools::error(
                'Database Directory Not Found.  Please create the directory <kbd>'
                . Config::$databaseDirectory
                . '</kbd> and make it writeable by the webserver.'
            );

            return;
        }
        if (!is_writable(Config::$databaseDirectory)) {
            Tools::error(
                'Database Directory Not Writeable.  Please make the directory <kbd>'
                . realpath(Config::$databaseDirectory)
                . '</kbd> writeable by the webserver.'
            );

            return;
        }

        if (!file_exists($this->database->databaseName)) {
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            if (!@touch($this->database->databaseName)) {
                Tools::error(
                    'Site Offline.  Unable to create database file: '
                    . $this->database->databaseName
                );

                return;
            }
        }

        $sqlFile = file_get_contents(Config::$sourceDirectory . '/Sql/database.sql');
        $sqls = explode(';', $sqlFile);
        foreach ($sqls as $sql) {
            $this->database->queryAsBool($sql);
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
        }
    }
}
