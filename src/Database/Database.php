<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Database;

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;
use PDO;
use PDOException;

/**
 * Class Database
 */
class Database
{
    /** @var string */
    public $databaseName;
    /** @var array */
    public $lastError;
    /** @var int */
    public $lastInsertId;
    /** @var int */
    public $userId;

    /** @var PDO */
    private $db;
    /** @var int */
    private $imageCount;
    /** @var int */
    private $tagId;
    /** @var array */
    private $tags;
    /** @var string */
    private $tagName;
    /** @var int */
    private $totalFilesReviewedCount;
    /** @var int */
    private $totalVotesCount;
    /** @var bool */
    private $noSqlite = false;

    /**
     * Database constructor.
     */
    public function __construct()
    {
        $this->databaseName = Config::$databaseDirectory . '/media.sqlite';
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
            if (!$this->noSqlite) {
                Tools::error('ERROR: sqlite Driver Not Found');
            }
            $this->noSqlite = true;

            return $this->db = false;
        }
        try {
            return $this->db = new PDO('sqlite:' . $this->databaseName);
        } catch (PDOException $error) {
            //Tools::error('Error Initalizing Database: ' . $error->getMessage());

            return $this->db = false;
        }
    }

    /**
     * @param string $sql
     * @param array $bind
     * @return array
     */
    public function queryAsArray($sql, array $bind = [])
    {
        if (!$this->db) {
            $this->initDatabase();
        }
        if (!$this->db) {
            return [];
        }

        $statement = $this->db->prepare($sql);
        if (!$statement) {
            return [];
        }
        foreach ($bind as $name => &$value) {
            $statement->bindParam($name, $value);
        }

        if (!$statement->execute()) {
            Tools::error('queryAsArray: ERROR EXECUTE: ' . print_r($this->db->errorInfo(), true));

            return [];
        }
        $response = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (!$response && $this->db->errorCode() != '00000') {
            Tools::error('queryAsArray: ERROR FETCH: ' . print_r($this->db->errorInfo(), true));

            return [];
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
        foreach ($bind as $name => &$value) {
            $statement->bindParam($name, $value);
        }

        if (!$statement->execute()) {
            $this->lastError = $this->db->errorInfo();
            if ($this->lastError[0] == '00000') {
                $this->lastInsertId = $this->db->lastInsertId();

                return true;
            }

            return false;
        }
        $this->lastInsertId = $this->db->lastInsertId();

        return true;
    }


    /**
     * @return array
     */
    public function getSite()
    {
        $site = $this->queryAsArray('SELECT * FROM site WHERE id = 1');
        if ($site && isset($site[0])) {
            return $site[0];
        }

        return $this->createSite();
    }

    /**
     * @return array
     */
    private function createSite()
    {
        $this->queryAsBool(
            "INSERT INTO site (id, name) VALUES (1, 'Shared Media Tagger')"
        );

        return ['id' => 1, 'name' => 'Shared Media Tagger'];
    }

    /**
     * @param bool $redo
     * @param int $hidden
     * @return int
     */
    public function getTopicsCount($redo = false, $hidden = 0)
    {
        if (isset($this->categoryCount) && !$redo) {
            return $this->categoryCount;
        }
        $sql = 'SELECT count(distinct(c2m.category_id)) AS count
                FROM topic2media AS c2m, topic AS c
                WHERE c.id = c2m.category_id
                AND c.hidden = ' . ($hidden ? '1' : '0');
        if (Config::$siteInfo['curation'] == 1) {
            $hidden = $hidden ? '1' : '0';
            $sql = "SELECT count(distinct(c2m.category_id)) AS count
                    FROM topic2media AS c2m, topic AS c, media AS m
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
    public function getFileCount($redo = false)
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
     * @param int|string $userId
     * @return int
     */
    public function getUserTagCount($userId = 0)
    {
        if (empty($userId) || !Tools::isPositiveNumber($userId)) {
            return 0;
        }
        $count = $this->queryAsArray(
            'SELECT COUNT(id) AS count FROM tagging WHERE user_id = :user_id',
            [':user_id' => $userId]
        );
        if (isset($count[0]['count'])) {
            return $count[0]['count'];
        }

        return 0;
    }

    /**
     * @param int $tagId
     * @return int
     */
    public function getTaggingCount($tagId = 0)
    {
        $sql = 'SELECT COUNT(id) AS count FROM tagging';
        $bind = [];
        if ($tagId > 0) {
            $sql .= ' WHERE tag_id = :tag_id';
            $bind[':tag_id'] = $tagId;
        }
        $count = $this->queryAsArray($sql, $bind);
        if (isset($count[0]['count'])) {
            return $count[0]['count'];
        }

        return 0;
    }

    /**
     * @return int
     */
    public function getTotalVotesCount()
    {
        if (isset($this->totalVotesCount)) {
            return $this->totalVotesCount;
        }
        $response = $this->queryAsArray('SELECT COUNT(id) AS total FROM tagging');
        if (isset($response[0]['total'])) {
            return $this->totalVotesCount = $response[0]['total'];
        }

        return $this->totalVotesCount = 0;
    }

    /**
     * @param int $limit
     * @return array|bool
     */
    public function getRandomMedia($limit = 1)
    {
        $unreviewed = $this->getRandomUnreviewedMedia($limit);
        if ($unreviewed) {
            return $unreviewed;
        }

        $where = '';
        if (Config::$siteInfo['curation'] == 1) {
            $where = "WHERE curated == '1'";
        }

        return $this->queryAsArray(
            'SELECT * FROM media ' . $where . ' ORDER BY RANDOM() LIMIT :limit',
            ['limit' => $limit]
        );
    }

    /**
     * @param int $limit
     * @return array|bool
     */
    private function getRandomUnreviewedMedia($limit = 1)
    {
        $curationAnd = '';
        $curationWhere = '';
        if (Config::$siteInfo['curation'] == 1) {
            $curationAnd = "AND curated == '1'";
            $curationWhere = "WHERE curated == '1'";
        }

        $bind = [];
        $bind[':limit'] = $limit;

        $sqlPre = 'SELECT * FROM media WHERE media.pageid NOT IN (';
        $sqlPost =") $curationAnd ORDER BY RANDOM() LIMIT :limit";

        // Unreviewed by all users
        $notIn = 'SELECT DISTINCT(media_pageid) FROM tagging';
        $unreviewed = $this->queryAsArray($sqlPre . $notIn . $sqlPost, $bind);
        if ($unreviewed) {
            return $unreviewed;
        }

        // Unreviwed by current user
        $notIn .= ' WHERE tagging.user_id = :user_id';
        $bind[':user_id'] = $this->userId;
        $unreviewed = $this->queryAsArray($sqlPre . $notIn . $sqlPost, $bind);
        if ($unreviewed) {
            return $unreviewed;
        }

        // Just Random
        unset($bind[':user_id']);
        $sql = "SELECT * FROM media $curationWhere ORDER BY RANDOM() LIMIT :limit";
        return $this->queryAsArray($sql, $bind);
    }

    /**
     * @param int $limit
     * @return array|bool
     */
    public function getUsers($limit = 100)
    {
        $sql = 'SELECT * FROM user ORDER BY id DESC LIMIT :limit';
        $users = $this->queryAsArray($sql, [':limit' => $limit]);
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
            'SELECT id 
            FROM user 
            WHERE ip = :ip_address 
            AND host = :host 
            AND user_agent = :user_agent',
            [
                ':host' => $host,
                ':ip_address' => $ipAddress,
                ':user_agent' => $userAgent,
            ]
        );
        if (!isset($user[0]['id'])) {
            if ($createNew) {
                return $this->newUser($ipAddress, $host, $userAgent);
            }
            $this->userId = 0;

            return false;
        }
        $this->userId = $user[0]['id'];

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
                ip, host, user_agent, last
            ) VALUES (
                :ip_address, :host, :user_agent, :last
            )',
            [
                ':host' => $host,
                ':ip_address' => $ipAddress,
                ':last' => Tools::timeNow(),
                ':user_agent' => $userAgent,
            ]
        )
        ) {
            $this->userId = $this->lastInsertId;

            return true;
        }
        $this->userId = 0;

        return false;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getTopic($name)
    {
        $response = $this->queryAsArray(
            'SELECT * FROM topic WHERE name = :name',
            [':name' => $name]
        );
        if (!isset($response[0]['id'])) {
            return [];
        }

        return $response[0];
    }

    /**
     * @param string $topicName
     * @return int
     */
    public function getTopicSize($topicName)
    {
        $sql = 'SELECT count(c2m.id) AS size
                FROM topic2media AS c2m, topic AS c
                WHERE c.name = :name
                AND c2m.category_id = c.id';
        if (Config::$siteInfo['curation'] == 1) {
            $sql = "SELECT count(c2m.id) AS size
                    FROM topic2media AS c2m, topic AS c, media as m
                    WHERE c.name = :name
                    AND c2m.category_id = c.id
                    AND m.pageid = c2m.media_pageid
                    AND m.curated = '1'";
        }
        $response = $this->queryAsArray($sql, [':name' => $topicName]);
        if (isset($response[0]['size'])) {
            return $response[0]['size'];
        }
        Tools::error("getTopicSize($topicName) ERROR: 0 size");

        return 0;
    }

    /**
     * @param $pageid
     * @param bool $onlyHidden
     * @return array
     */
    public function getMediaTopics($pageid, $onlyHidden = false)
    {
        if (!$pageid|| !Tools::isPositiveNumber($pageid)) {
            return [];
        }
        $response = $this->queryAsArray(
            'SELECT topic.name
            FROM topic, topic2media
            WHERE topic.hidden = :hidden
            AND topic2media.category_id = topic.id
            AND topic2media.media_pageid = :pageid
            ORDER BY topic.name',
            [
                ':pageid' => $pageid,
                ':hidden' => ($onlyHidden ? 1 : 0),
            ]
        );
        if (!isset($response[0]['name'])) {
            return [];
        }
        $topics = [];
        foreach ($response as $topic) {
            $topics[] = $topic['name'];
        }

        return $topics;
    }

    /**
     * @param string $topicName
     * @return int
     */
    public function getTopicIdFromName($topicName)
    {
        $response = $this->queryAsArray(
            'SELECT id FROM topic WHERE name = :name',
            [':name' => $topicName]
        );
        if (!isset($response[0]['id'])) {
            return 0;
        }

        return $response[0]['id'];
    }

    /**
     * @param string $topicName
     * @return array
     */
    public function getMediaInTopic($topicName)
    {
        $topicId = $this->getTopicIdFromName($topicName);
        if (!$topicId) {
            Tools::error('getMediaInTopic: No ID found for: ' . $topicName);

            return [];
        }
        $sql = 'SELECT media_pageid
                FROM topic2media
                WHERE category_id = :category_id
                ORDER BY media_pageid';
        $response = $this->queryAsArray($sql, [':category_id' => $topicId]);
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
     * @param array $topicIdArray
     * @return int
     * @TODO UNUSED
     */
    public function getCountLocalFilesPerTopic($topicIdArray)
    {
        if (!is_array($topicIdArray)) {
            Tools::error('getCountLocalFilesPerTopic: invalid topic array');

            return 0;
        }
        $locals = $this->queryAsArray(
            'SELECT count(category_id) AS count
            FROM topic2media
            WHERE category_id IN ( :category_id )',
            [':category_id' => implode($topicIdArray, ', ')]
        );
        if ($locals && isset($locals[0]['count'])) {
            return $locals[0]['count'];
        }

        return 0;
    }

    /**
     * @param string $topicName
     * @return bool
     */
    public function isHiddenTopic($topicName)
    {
        if (!$topicName) {
            return false;
        }
        if ($this->queryAsArray(
            'SELECT id FROM topic WHERE hidden = 1 AND name = :name',
            [':name' => $topicName]
        )
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param string $name
     * @return int
     */
    public function getTagIdByName($name)
    {
        if (isset($this->tagId[$name])) {
            return $this->tagId[$name];
        }
        $tag = $this->queryAsArray(
            'SELECT id FROM tag WHERE name = :name LIMIT 1',
            [':name' => $name]
        );
        if (isset($tag[0]['id'])) {
            return $this->tagId[$name] = $tag[0]['id'];
        }

        return $this->tagId[$name] = 0;
    }

    /**
     * @param int|string $tagId
     * @return string
     */
    public function getTagNameById($tagId)
    {
        if (isset($this->tagName[$tagId])) {
            return $this->tagName[$tagId];
        }
        $tag = $this->queryAsArray(
            'SELECT name FROM tag WHERE id = :id LIMIT 1',
            [':id' => $tagId]
        );
        if (isset($tag[0]['name'])) {
            return $this->tagName[$tagId] = $tag[0]['name'];
        }

        return $this->tagName[$tagId] = (string) $tagId;
    }

    /**
     * @param string $order - DESC / ASC - default ASC
     * @return array
     */
    public function getTags(string $order = '')
    {
        if (isset($this->tags)) {
            reset($this->tags);

            return $this->tags;
        }
        if (empty($order)) {
            $order = 'ASC';
        }
        $tags = $this->queryAsArray('SELECT * FROM tag ORDER BY position ' . $order);
        if (!$tags) {
            return $this->tags = [];
        }

        return $this->tags = $tags;
    }

    /**
     * @param int|string $pageid
     * @return array
     */
    public function getVotes($pageid)
    {
        return $this->queryAsArray(
            'SELECT count(t.id) AS count, t.tag_id, tag.*
            FROM tagging AS t, tag
            WHERE t.media_pageid = :media_pageid
            AND tag.id = t.tag_id
            GROUP BY t.tag_id
            ORDER BY tag.position',
            [
                ':media_pageid' => $pageid,
            ]
        );
    }

    /**
     * @param int|string $topicId
     * @return array
     */
    public function getDbVotesPerTopic($topicId)
    {
        return $this->queryAsArray(
            'SELECT count(t.id) AS count, tag.*
            FROM tagging AS t,
                 tag,
                 topic2media AS c2m
            WHERE tag.id = t.tag_id
            AND c2m.media_pageid = t.media_pageid
            AND c2m.category_id = :category_id
            GROUP BY (tag.id)
            ORDER BY tag.position ASC',
            [':category_id' => $topicId]
        );
    }

    /**
     * @return int
     */
    public function getTotalFilesReviewedCount()
    {
        if (isset($this->totalFilesReviewedCount)) {
            return $this->totalFilesReviewedCount;
        }
        $response = $this->queryAsArray(
            'SELECT COUNT(DISTINCT(media_pageid)) AS total FROM tagging'
        );
        if (isset($response[0]['total'])) {
            return $this->totalFilesReviewedCount = $response[0]['total'];
        }

        return $this->totalFilesReviewedCount = 0;
    }

    /**
     * @param int|string $pageid
     * @return array
     */
    public function getMedia($pageid)
    {
        if (!$pageid || !Tools::isPositiveNumber($pageid)) {
            Tools::error('getMedia: ERROR no id');

            return [];
        }
        $sql = 'SELECT * FROM media WHERE pageid = :pageid';

        if (Config::$siteInfo['curation'] == 1) {
            $sql .= " AND curated = '1'";
        }

        return $this->queryAsArray($sql, [':pageid' => $pageid]);
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
}
