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
    private $databaseFile;
    private $sqlCurrent;
    private $sqlNew;
    private $tablesCurrent;

    /**
     * @param Database|DatabaseAdmin $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * @return bool
     */
    public function createTables()
    {
        if (!file_exists($this->database->databaseName)) {
            if (!touch($this->database->databaseName)) {
                Tools::error('ERROR: can not touch database name: ' . $this->database->databaseName);

                return false;
            }
        }
        $this->setDatabaseFile($this->database->databaseName);
        $this->setNewStructures(Config::getDatabaseTables());
        $this->update();

        return true;
    }

    /**
     * @param $file
     * @return bool
     */
    private function setDatabaseFile($file)
    {
        $this->databaseFile = $file;
        $this->tablesCurrent = [];
        $this->sqlCurrent = [];
        $this->setTableInfo();

        return $this->database->databaseLoaded();
    }

    /**
     * @param array $tables
     * @return bool
     */
    private function setNewStructures(array $tables = [])
    {
        if (!$tables || !is_array($tables)) {
            Tools::error('$tables array is invalid');

            return false;
        }
        $errors = 0;
        $count = 0;
        foreach ($tables as $tableName => $tableSql) {
            $count++;
            if (!$tableName || !is_string($tableName)) {
                Tools::error("#$count - Invalid table name");
                $errors++;
                continue;
            }
            if (!$tableSql || !is_string($tableSql)) {
                Tools::error("#$count - Invalid table sql");
                $errors++;
                continue;
            }
            $this->setNewStructure($tableName, $tableSql);
        }

        return $errors ? false : true;
    }

    /**
     * @param $tableName
     * @param $sql
     */
    private function setNewStructure($tableName, $sql)
    {
        $sql = $this->normalizeSql($sql);
        $this->sqlNew[$tableName] = $sql;
    }

    /**
     * @return bool
     */
    private function update()
    {
        $toUpdate = [];
        foreach (array_keys($this->sqlNew) as $name) {
            $old = $this->normalizeSql(
                !empty($this->sqlCurrent[$name]) ? $this->sqlCurrent[$name] : ''
            );
            $new = $this->normalizeSql(
                !empty($this->sqlNew[$name]) ? $this->sqlNew[$name] : ''
            );
            if ($old == $new) {
                continue;
            }
            $toUpdate[] = $name;
        }
        if (!$toUpdate) {
            return true;
        }
        foreach ($toUpdate as $tableName) {
            $this->updateTable($tableName);
        }

        return true;
    }

    /**
     * @param $tableName
     * @return bool
     */
    private function updateTable($tableName)
    {
        $tmpName = '_STSU_TMP_' . $tableName;
        $backupName = '_STSU_BACKUP_' . $tableName;
        $this->database->queryAsBool("DROP TABLE IF EXISTS '$tmpName'");
        $this->database->queryAsBool("DROP TABLE IF EXISTS '$backupName'");
        $this->database->beginTransaction();
        $sql = $this->sqlNew[$tableName];
        $sql = str_ireplace("CREATE TABLE '$tableName'", "CREATE TABLE '$tmpName'", $sql);
        if (!$this->database->queryAsBool($sql)) {
            Tools::error('ERROR: can not create tmp table:<br />' . $sql);
            return false;
        }
        // Get Columns of new table
        $this->setTableColumnInfo($tmpName);
        $newCols = $this->tablesCurrent[$tmpName];
        // Only use Columns both in new and old tables
        $cols =[];
        foreach ($newCols as $newCol) {
            if (isset($this->tablesCurrent[$tableName][$newCol['name']])) {
                $cols[] = $newCol['name'];
            }
        }
        if ($cols) {
            $oldSize = $this->getTableSize($tableName);
            $cols = implode($cols, ', ');
            $sql = "INSERT INTO '$tmpName' ( $cols ) SELECT $cols FROM $tableName";
            if (!$this->database->queryAsBool($sql)) {
                Tools::error('ERROR: can not insert into tmp table: ' . $tmpName
                    . '<br />' . $sql);
                return false;
            }
            $newSize = $this->getTableSize($tmpName);
            if ($newSize == $oldSize) {
            } else {
                Tools::error("ERROR: Inserted new $newSize rows, from $oldSize old rows");
            }
            if (!$this->database->queryAsBool("ALTER TABLE $tableName RENAME TO $backupName")) {
                Tools::error('ERROR: can not rename '.$tableName.' to '.$backupName);

                return false;
            }
        }
        if (!$this->database->queryAsBool("ALTER TABLE $tmpName RENAME TO $tableName")) {
            Tools::error('ERROR: can not rename ' . $tmpName . ' to ' . $backupName);

            return false;
        }
        $this->database->commit();
        $this->database->queryAsBool("DROP TABLE IF EXISTS '$tmpName'");
        $this->database->queryAsBool("DROP TABLE IF EXISTS '$backupName'");
        $this->database->vacuum();

        return true;
    }

    /**
     * setTableInfo
     */
    private function setTableInfo()
    {
        $tables = $this->database->queryAsArray("SELECT name, sql FROM sqlite_master WHERE type = 'table'");
        foreach ($tables as $table) {
            if (preg_match('/^_STSU_/', $table['name'])) {
                continue; // tmp and backup tables
            }
            $this->sqlCurrent[$table['name']] = $this->normalizeSql($table['sql']);
            $this->setTableColumnInfo($table['name']);
        }
    }

    /**
     * @param $tableName
     */
    private function setTableColumnInfo($tableName)
    {
        $columns = $this->database->queryAsArray("PRAGMA table_info($tableName)");
        foreach ($columns as $column) {
            $this->tablesCurrent[$tableName][$column['name']] = $column;
        }
    }

    /**
     * @param $sql
     * @return string
     */
    private function normalizeSql($sql)
    {
        $sql = preg_replace('/\s+/', ' ', $sql); // remove all excessive spaces and control chars
        $sql = str_replace('"', "'", $sql); // use only single quote '
        $sql = str_ireplace('CREATE TABLE IF NOT EXISTS', 'CREATE TABLE', $sql); // standard create syntax

        return trim($sql);
    }

    /**
     * @param $tableName
     * @return int
     */
    private function getTableSize($tableName)
    {
        $size = $this->database->queryAsArray('SELECT count(rowid) AS count FROM ' . $tableName);
        if (isset($size[0]['count'])) {
            return $size[0]['count'];
        }
        Tools::error('Can not get table size: ' . $tableName);

        return 0;
    }

    /**
     * @return array
     */
    public function seedDemo()
    {
        $results = [];
        foreach (Config::getSeedDemoSetup() as $name => $sql) {
            $seed = $this->database->queryAsBool($sql);
            if ($seed) {
                $result = 'OK: ' . $this->database->lastInsertId;
            } else {
                $result = 'ERROR: ' . implode(', ', $this->database->lastError);
            }
            $results[] = "<br />$name<br />$result<br />$sql";
        }

        return $results;
    }
}
