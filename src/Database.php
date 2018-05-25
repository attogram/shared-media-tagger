<?php

declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger;

use PDO;
use PDOException;

/**
 * Class Database
 */
class Database
{
    /** @var string */
    public $databaseName;
    /** @var PDO */
    public $db;
    /** @var int */
    public $lastInsertId;
    /** @var string */
    public $lastError;

    /** @var string */
    private $databaseFile;
    private $tablesCurrent;
    private $sqlCurrent;
    private $sqlNew;

    /** @var int */
    private $userCount;
    /** @var int */
    private $totalReviewCount;
    /** @var int */
    private $categoryCount;
    /** @var int */
    private $imageCount;


    /**
     * Database constructor.
     */
    public function __construct()
    {
        $this->databaseName = realpath(__DIR__ . '/..') . '/db/media.sqlite';
    }

    /**
     * @return bool
     */
    public function databaseLoaded()
    {
        if (!$this->db) {
            $this->initDatabase();
        }
        if ($this->db instanceof PDO) {
            return true;
        }

        return false;
    }

    /**
     * @return bool|PDO
     */
    private function initDatabase()
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers())) {
            Tools::error('::initDatabase: ERROR: no sqlite Driver');

            return $this->db = false;
        }
        try {
            return $this->db = new PDO('sqlite:' . $this->databaseName);
        } catch (PDOException $error) {
            Tools::error('::initDatabase: ' . $this->databaseName . '  ERROR: ' . $error->getMessage());

            return $this->db = false;
        }
    }

    /**
     * @param string $sql
     * @param array $bind
     * @return array|bool
     */
    public function queryAsArray($sql, array $bind = [])
    {
        if (!$this->db) {
            $this->initDatabase();
        }
        if (!$this->db) {
            return false;
        }

        $statement = $this->db->prepare($sql);
        if (!$statement) {
            return [];
        }
        foreach ($bind as $name => $value) {
            $statement->bindParam($name, $value);
        }
        if (!$statement->execute()) {
            Tools::error('::queryAsArray(): ERROR EXECUTE: ' . print_r($this->db->errorInfo(), true));

            return [];
        }
        $response = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (!$response && $this->db->errorCode() != '00000') {
            Tools::error('::queryAsArray(): ERROR FETCH: '  .print_r($this->db->errorInfo(), true));
            $response = [];
        }

        return $response;
    }

    /**
     * @param string $sql
     * @param array $bind
     * @return bool
     */
    public function queryAsBool($sql, array $bind = [])
    {
        if (!$this->db) {
            $this->initDatabase();
        }
        if (!$this->db) {
            return false;
        }
        $this->lastInsertId = $this->lastError = false;
        $statement = $this->db->prepare($sql);
        if (!$statement) {
            $this->lastError = $this->db->errorInfo();

            return false;
        }
        foreach ($bind as $name => $value) {
            $statement->bindParam($name, $value);
        }
        if (!$statement->execute()) {
            $this->lastError = $this->db->errorInfo();
            if ($this->lastError[0] == '00000') {
                return true;
            }

            return false;
        }
        $this->lastError = $this->db->errorInfo();
        $this->lastInsertId = $this->db->lastInsertId();

        return true;
    }

    /**
     * @return bool
     */
    public function vacuum()
    {
        if ($this->queryAsBool('VACUUM')) {
            return true;
        }
        Tools::error('FAILED to VACUUM');

        return false;
    }

    /**
     * @return bool
     */
    public function beginTransaction()
    {
        if ($this->queryAsBool('BEGIN TRANSACTION')) {
            return true;
        }
        Tools::error('FAILED to BEGIN TRANSACTION');

        return false;
    }

    /**
     * @return bool
     */
    public function commit()
    {
        if ($this->queryAsBool('COMMIT')) {
            return true;
        }
        Tools::error('FAILED to COMMIT');

        return false;
    }

    // Counts

    /**
     * @param bool $redo
     * @param int $hidden
     * @return int
     */
    public function getCategoriesCount($redo = false, $hidden = 0)
    {
        if (isset($this->categoryCount) && !$redo) {
            return $this->categoryCount;
        }
        $sql = 'SELECT count(distinct(c2m.category_id)) AS count
                FROM category2media AS c2m, category AS c
                WHERE c.id = c2m.category_id
                AND c.hidden = ' . ($hidden ? '1' : '0');
        if (Config::$siteInfo['curation'] == 1) {
            $hidden = $hidden ? '1' : '0';
            $sql = "SELECT count(distinct(c2m.category_id)) AS count
                    FROM category2media AS c2m, category AS c, media AS m
                    WHERE c.id = c2m.category_id
                    AND c.hidden = '$hidden'
                    AND c2m.media_pageid = m.pageid
                    AND m.curated = '1'";
        }
        $response = $this->queryAsArray($sql);
        if (!$response) {
            return 0;
        }

        return $this->categoryCount = $response[0]['count'];
    }

    /**
     * @param bool $redo
     * @return int
     */
    public function getImageCount($redo = false)
    {
        if (isset($this->imageCount) && !$redo) {
            return $this->imageCount;
        }
        $sql = 'SELECT count(pageid) AS count FROM media';
        if (Config::$siteInfo['curation'] == 1) {
            $sql .= " WHERE curated = '1'";
        }
        $response = $this->queryAsArray($sql);
        if (!$response) {
            return 0;
        }

        return $this->imageCount = $response[0]['count'];
    }

    /**
     * @return int
     */
    public function getUserCount()
    {
        if (isset($this->userCount)) {
            return $this->userCount;
        }
        $count = $this->queryAsArray('SELECT count(id) AS count FROM user');
        if (isset($count[0]['count'])) {
            return $this->userCount = $count[0]['count'];
        }

        return $this->userCount = 0;
    }

    /**
     * @param int $userId
     * @return int
     */
    public function getUserTagCount($userId = 0)
    {
        $sql = 'SELECT sum(count) AS sum FROM user_tagging';
        $bind = [];
        if ($userId > 0) {
            $sql .= ' WHERE user_id = :user_id';
            $bind[':user_id'] = $userId;
        }
        $count = $this->queryAsArray($sql, $bind);
        if (isset($count[0]['sum'])) {
            return $count[0]['sum'];
        }

        return 0;
    }

    /**
     * @param int $tagId
     * @return int
     */
    public function getTaggingCount($tagId = 0)
    {
        $sql = 'SELECT SUM(count) AS count FROM tagging';
        $bind = [];
        if ($tagId > 0) {
            $sql .= ' WHERE tag_id = :tag_id';
            $bind[':tag_id'] = $tagId;
        }
        $count = $this->queryAsArray($sql, $bind);
        if (!isset($count[0]['count'])) {
            return 0;
        }

        return $count[0]['count'];
    }

    /**
     * @return int
     */
    public function getTotalReviewCount()
    {
        if (isset($this->totalReviewCount)) {
            return $this->totalReviewCount;
        }
        $response = $this->queryAsArray('SELECT SUM(count) AS total FROM tagging');
        if (isset($response[0]['total'])) {
            return $this->totalReviewCount = $response[0]['total'];
        }

        return $this->totalReviewCount = 0;
    }

    // Random

    /**
     * @param int $limit
     * @return array|bool
     */
    public function getRandomMedia($limit = 1)
    {
        if (mt_rand(1, 7) == 1) { // 1 in 7 chance of getting UNREVIEWED media
            $unreviewed = $this->getRandomUnreviewedMedia($limit);
            if ($unreviewed) {
                return $unreviewed;
            }
        }
        $where = '';
        if (Config::$siteInfo['curation'] == 1) {
            $where = "WHERE curated == '1'";
        }
        $sql = 'SELECT *
                FROM media ' . $where . '
                ORDER BY RANDOM()
                LIMIT :limit';
        return $this->queryAsArray($sql, ['limit' => $limit]);
    }

    /**
     * @param int $limit
     * @return array|bool
     */
    public function getRandomUnreviewedMedia($limit = 1)
    {
        $and = '';
        if (Config::$siteInfo['curation'] == 1) {
            $and = "AND curated == '1'";
        }
        $sql = "
            SELECT m.*
            FROM media AS m
            LEFT JOIN tagging AS t ON t.media_pageid = m.pageid
            WHERE t.media_pageid IS NULL $and
            ORDER BY RANDOM()
            LIMIT :limit";

        return $this->queryAsArray($sql, ['limit' => $limit]);
    }

    // Media

    /**
     * @param int|string $pageid
     * @return array|bool
     */
    public function getMedia($pageid)
    {
        if (!$pageid || !Tools::isPositiveNumber($pageid)) {
            Tools::error('getMedia: ERROR no id');

            return false;
        }
        $sql = 'SELECT * FROM media WHERE pageid = :pageid';

        if (Config::$siteInfo['curation'] == 1 && !Tools::isAdmin()) {
            $sql .= " AND curated = '1'";
        }
        return $this->queryAsArray($sql, [':pageid'=>$pageid]);
    }

    // User

    /**
     * @param int $limit
     * @param string $orderby
     * @return array|bool
     */
    public function getUsers($limit = 100, $orderby = 'last DESC, page_views DESC')
    {
        $sql = 'SELECT * FROM user ORDER BY ' . $orderby . ' LIMIT ' . $limit;
        $users = $this->queryAsArray($sql);
        if (isset($users[0])) {
            return $users;
        }

        return [];
    }

    /**
     * @param bool $createNew
     * @return bool
     */
    public function getUser($createNew = false)
    {
        $ipAddress = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
        $host = !empty($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : null;
        if (!$host) {
            $host = $ipAddress;
        }
        $userAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        $user = $this->queryAsArray(
            'SELECT id FROM user WHERE ip = :ip_address AND host = :host AND user_agent = :user_agent',
            [':ip_address' => $ipAddress, ':host' => $host, ':user_agent' => $userAgent]
        );
        if (!isset($user[0]['id'])) {
            if ($createNew) {
                return $this->newUser($ipAddress, $host, $userAgent);
            }
            Config::$userId = 0;

            return false;
        }
        Config::$userId = $user[0]['id'];

        return true;
    }

    /**
     * @param $ipAddress
     * @param $host
     * @param $userAgent
     * @return bool
     */
    private function newUser($ipAddress, $host, $userAgent)
    {
        if ($this->queryAsBool(
            'INSERT INTO user (
                ip, host, user_agent, page_views, last
            ) VALUES (
                :ip_address, :host, :user_agent, 0, :last
            )',
            [
                ':ip_address' => $ipAddress,
                ':host' => $host,
                ':user_agent' => $userAgent,
                ':last' => Tools::timeNow(),
            ]
        )
        ) {
            Config::$userId = $this->lastInsertId;

            return true;
        }
        Config::$userId = 0;

        return false;
    }

    /**
     * @param $userId
     * @return array
     */
    public function getUserTagging($userId)
    {
        $tags = $this->queryAsArray(
            'SELECT m.*, ut.tag_id, ut.count
            FROM user_tagging AS ut, media AS m
            WHERE ut.user_id = :user_id
            AND ut.media_pageid = m.pageid
            ORDER BY ut.media_pageid
            LIMIT 100', // @TODO TMP LIMIT
            [':user_id' => $userId]
        );
        if ($tags) {
            return $tags;
        }

        return [];
    }

    /**
     * @return bool
     */
    public function saveUserLastTagTime()
    {
        return $this->queryAsBool(
            'UPDATE user SET last = :last WHERE id = :user_id',
            [
                ':user_id' => Config::$userId,
                ':last' => Tools::timeNow(),
            ]
        );
    }

    /**
     * @return bool
     * @TODO - unused
     */
    public function saveUserView()
    {
        if (!Config::$userId) {
            return false;
        }
        $view = $this->queryAsBool(
            'UPDATE user SET page_views = page_views + 1, last = :last WHERE id = :id',
            [
                ':id' => Config::$userId,
                ':last' => Tools::timeNow()
            ]
        );
        if ($view) {
            return true;
        }

        return false;
    }

    // Admin

    /**
     * @return bool
     */
    public function createTables()
    {
        if (!file_exists($this->databaseName)) {
            if (!touch($this->databaseName)) {
                Tools::error('ERROR: can not touch database name: ' . $this->databaseName);

                return false;
            }
        }
        $this->setDatabaseFile($this->databaseName);
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

        return $this->databaseLoaded();
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
            Tools::notice('OK: ' . sizeof($this->sqlNew) . ' tables up-to-date');

            return true;
        }
        Tools::notice(sizeof($toUpdate) . ' tables to update: ' . implode($toUpdate, ', '));
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
        $this->queryAsBool("DROP TABLE IF EXISTS '$tmpName'");
        $this->queryAsBool("DROP TABLE IF EXISTS '$backupName'");
        $this->beginTransaction();
        $sql = $this->sqlNew[$tableName];
        $sql = str_ireplace("CREATE TABLE '$tableName'", "CREATE TABLE '$tmpName'", $sql);
        if (!$this->queryAsBool($sql)) {
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
        if (!$cols) {
            $newSize = 0;
        } else {
            $oldSize = $this->getTableSize($tableName);
            $cols = implode($cols, ', ');
            $sql = "INSERT INTO '$tmpName' ( $cols ) SELECT $cols FROM $tableName";
            if (!$this->queryAsBool($sql)) {
                Tools::error('ERROR: can not insert into tmp table: ' . $tmpName
                    . '<br />' . $sql);
                return false;
            }
            $newSize = $this->getTableSize($tmpName);
            if ($newSize == $oldSize) {
            } else {
                Tools::error("ERROR: Inserted new $newSize rows, from $oldSize old rows");
            }
            if (!$this->queryAsBool("ALTER TABLE $tableName RENAME TO $backupName")) {
                Tools::error('ERROR: can not rename '.$tableName.' to '.$backupName);

                return false;
            }
        }
        if (!$this->queryAsBool("ALTER TABLE $tmpName RENAME TO $tableName")) {
            Tools::error('ERROR: can not rename ' . $tmpName . ' to ' . $backupName);

            return false;
        }
        $this->commit();
        Tools::notice('OK: Table Structure Updated: ' . $tableName . ': +' . number_format((float) $newSize) . ' rows');
        $this->queryAsBool("DROP TABLE IF EXISTS '$tmpName'");
        $this->queryAsBool("DROP TABLE IF EXISTS '$backupName'");
        $this->vacuum();

        return true;
    }

    /**
     *
     */
    private function setTableInfo()
    {
        $tables = $this->queryAsArray("SELECT name, sql FROM sqlite_master WHERE type = 'table'");
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
        $columns = $this->queryAsArray("PRAGMA table_info($tableName)");
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
        $size = $this->queryAsArray('SELECT count(rowid) AS count FROM ' . $tableName);
        if (isset($size[0]['count'])) {
            return $size[0]['count'];
        }
        Tools::error('Can not get table size: ' . $tableName);

        return 0;
    }

    /**
     * @return array
     */
    public function emptyTaggingTables()
    {
        $sqls = [
            'DELETE FROM tagging',
            'DELETE FROM user_tagging',
        ];
        $response = [];
        foreach ($sqls as $sql) {
            if ($this->queryAsBool($sql)) {
                $response[] = 'OK: ' . $sql;
            } else {
                $response[] = 'FAIL: ' . $sql;
            }
        }
        $this->vacuum();

        return $response;
    }

    /**
     * @return array
     */
    public function emptyUserTables()
    {
        $sqls = [
            'DELETE FROM user',
            'DELETE FROM tagging',
            'DELETE FROM user_tagging',
        ];
        $response = [];
        foreach ($sqls as $sql) {
            if ($this->queryAsBool($sql)) {
                $response[] = 'OK: ' . $sql;
            } else {
                $response[] = 'FAIL: ' . $sql;
            }
        }
        $this->vacuum();

        return $response;
    }

    /**
     * @return bool|string
     */
    public function dropTables()
    {
        $sqls = [
            'DROP TABLE IF EXISTS block',
            'DROP TABLE IF EXISTS category',
            'DROP TABLE IF EXISTS category2media',
            'DROP TABLE IF EXISTS contact',
            'DROP TABLE IF EXISTS media',
            'DROP TABLE IF EXISTS site',
            'DROP TABLE IF EXISTS tag',
            'DROP TABLE IF EXISTS tagging',
            'DROP TABLE IF EXISTS user',
            'DROP TABLE IF EXISTS user_tagging',
            'DROP TABLE IF EXISTS network',
            'DROP TABLE IF EXISTS network_site',
        ];
        $response = false;
        foreach ($sqls as $id => $sql) {
            if ($this->queryAsBool($sql)) {
                $response .= "<b>OK:</b> $sql<br />";
            } else {
                $response .= "<b>FAIL:<b/> $sql<br />";
            }
        }
        $this->vacuum();

        return $response;
    }
}
