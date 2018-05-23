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
    /** @var bool */
    public $curation;

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
        if ($this->curation == 1) {
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
        if ($this->curation == 1) {
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
        if ($this->curation == 1) {
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
        if ($this->curation == 1) {
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
}
