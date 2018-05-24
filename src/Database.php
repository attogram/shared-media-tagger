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

    /**
     * @param $name
     * @return array|mixed
     */
    public function getCategory($name)
    {
        $response = $this->queryAsArray(
            'SELECT * FROM category WHERE name = :name',
            [':name' => $name]
        );
        if (!isset($response[0]['id'])) {
            return [];
        }

        return $response[0];
    }

    /**
     * @param $categoryName
     * @return int
     */
    public function getCategorySize($categoryName)
    {
        $sql = 'SELECT count(c2m.id) AS size
                FROM category2media AS c2m, category AS c
                WHERE c.name = :name
                AND c2m.category_id = c.id';
        if (Config::$siteInfo['curation'] == 1) {
            $sql = "SELECT count(c2m.id) AS size
                    FROM category2media AS c2m, category AS c, media as m
                    WHERE c.name = :name
                    AND c2m.category_id = c.id
                    AND m.pageid = c2m.media_pageid
                    AND m.curated = '1'";
        }
        $response = $this->queryAsArray($sql, [':name' => $categoryName]);
        if (isset($response[0]['size'])) {
            return $response[0]['size'];
        }
        Tools::error("getCategorySize($categoryName) ERROR: 0 size");

        return 0;
    }

    /**
     * @return array
     * @TODO unused
     */
    public function getCategoryList()
    {
        $response = $this->queryAsArray('SELECT name FROM category ORDER BY name');
        $return = [];
        if (!$response || !is_array($response)) {
            return $return;
        }
        foreach ($response as $name) {
            $return[] = $name['value']['name'];
        }

        return $return;
    }

    /**
     * @param $pageid
     * @return array
     */
    public function getImageCategories($pageid)
    {
        $error = ['Category database unavailable'];
        if (!$pageid|| !Tools::isPositiveNumber($pageid)) {
            return $error;
        }
        $response = $this->queryAsArray(
            'SELECT category.name
            FROM category, category2media
            WHERE category2media.category_id = category.id
            AND category2media.media_pageid = :pageid
            ORDER BY category.name',
            [':pageid' => $pageid]
        );
        if (!isset($response[0]['name'])) {
            return $error;
        }
        $cats = [];
        foreach ($response as $cat) {
            $cats[] = $cat['name'];
        }

        return $cats;
    }

    /**
     * @param $categoryName
     * @return int
     */
    public function getCategoryIdFromName($categoryName)
    {
        $response = $this->queryAsArray(
            'SELECT id FROM category WHERE name = :name',
            [':name' => $categoryName]
        );
        if (!isset($response[0]['id'])) {
            return 0;
        }

        return $response[0]['id'];
    }

    /**
     * @param $categoryName
     * @return array
     */
    public function getMediaInCategory($categoryName)
    {
        $categoryId = $this->getCategoryIdFromName($categoryName);
        if (!$categoryId) {
            Tools::error('::getMediaInCategory: No ID found for: ' . $categoryName);

            return [];
        }
        $sql = 'SELECT media_pageid
                FROM category2media
                WHERE category_id = :category_id
                ORDER BY media_pageid';
        $response = $this->queryAsArray($sql, [':category_id' => $categoryId]);
        if ($response === false) {
            Tools::error('ERROR: unable to access categor2media table.');

            return [];
        }
        if (!$response) {
            return [];
        }
        $return = [];
        foreach ($response as $media) {
            $return[] = $media['media_pageid'];
        }

        return $return;
    }

    /**
     * @param $categoryIdArray
     * @return int
     * @TODO unused
     */
    public function getCountLocalFilesPerCategory($categoryIdArray)
    {
        if (!is_array($categoryIdArray)) {
            Tools::error('getCountLocalFilesPerCategory: invalid category array');

            return 0;
        }
        $locals = $this->queryAsArray(
            'SELECT count(category_id) AS count
            FROM category2media
            WHERE category_id IN ( :category_id )',
            [':category_id' => implode($categoryIdArray, ', ')]
        );
        if ($locals && isset($locals[0]['count'])) {
            return $locals[0]['count'];
        }

        return 0;
    }

    /**
     * @param $categoryName
     * @return bool
     */
    public function isHiddenCategory($categoryName)
    {
        if (!$categoryName) {
            return false;
        }
        $sql = 'SELECT id FROM category WHERE hidden = 1 AND name = :category_name';
        $bind = [':category_name' => $categoryName];
        if ($this->queryAsArray($sql, $bind)) {
            return true;
        }

        return false;
    }

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
}
