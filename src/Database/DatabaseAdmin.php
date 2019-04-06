<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Database;

use Attogram\SharedMedia\Tagger\Commons;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class DatabaseAdmin
 */
class DatabaseAdmin extends Database
{
    public $topicId;

    /** @var Commons */
    private $commons;

    /**
     * DatabaseAdmin constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Commons $commons
     */
    public function setCommons($commons)
    {
        $this->commons = $commons;
    }

    /**
     * @param array $media
     * @return bool
     */
    public function saveMediaToDatabase(array $media)
    {
        if (!$media || !is_array($media)) {
            Tools::error('saveMediaToDatabase: no media array');

            return false;
        }
        $errors = [];
        $this->beginTransaction();
        foreach ($media as $id => $mediaFile) {
            $new = [];
            $new[':pageid'] = !empty($mediaFile['pageid']) ? $mediaFile['pageid'] : '';
            $new[':title'] = !empty($mediaFile['title']) ? $mediaFile['title'] : '';
            $new[':url'] = !empty($mediaFile['imageinfo'][0]['url']) ? $mediaFile['imageinfo'][0]['url'] : '';
            if (!isset($new[':url']) || $new[':url'] == '') {
                Tools::error(
                    '::save_media_to_database: ERROR: NO URL: SKIPPING: pageid='
                    . (!empty($new[':pageid']) ? $new[':pageid'] : '?')
                    . ' title='
                    . (!empty($new[':title']) ? $new[':title'] : '?')
                );
                $errors[ $new[':pageid'] ] = $new[':title'];
                continue;
            }
            $new[':descriptionurl'] = !empty($mediaFile['imageinfo'][0]['descriptionurl'])
                ? $mediaFile['imageinfo'][0]['descriptionurl'] : '';
            $new[':descriptionshorturl'] = !empty($mediaFile['imageinfo'][0]['descriptionshorturl'])
                ? $mediaFile['imageinfo'][0]['descriptionshorturl'] : '';
            $new[':imagedescription'] = !empty($mediaFile['imageinfo'][0]['extmetadata']['ImageDescription']['value'])
                ? $mediaFile['imageinfo'][0]['extmetadata']['ImageDescription']['value'] : '';
            $new[':artist'] = !empty($mediaFile['imageinfo'][0]['extmetadata']['Artist']['value'])
                ? $mediaFile['imageinfo'][0]['extmetadata']['Artist']['value'] : '';
            $new[':datetimeoriginal'] = !empty($mediaFile['imageinfo'][0]['extmetadata']['DateTimeOriginal']['value'])
                ? $mediaFile['imageinfo'][0]['extmetadata']['DateTimeOriginal']['value'] : '';
            $new[':licenseshortname'] = !empty($mediaFile['imageinfo'][0]['extmetadata']['LicenseShortName']['value'])
                ? $mediaFile['imageinfo'][0]['extmetadata']['LicenseShortName']['value'] : '';
            $new[':usageterms'] = !empty($mediaFile['imageinfo'][0]['extmetadata']['UsageTerms']['value'])
                ? $mediaFile['imageinfo'][0]['extmetadata']['UsageTerms']['value'] : '';
            $new[':attributionrequired'] =
                !empty($mediaFile['imageinfo'][0]['extmetadata']['AttributionRequired']['value'])
                ? $mediaFile['imageinfo'][0]['extmetadata']['AttributionRequired']['value'] : '';
            $new[':restrictions'] = !empty($mediaFile['imageinfo'][0]['extmetadata']['Restrictions']['value'])
                ? $mediaFile['imageinfo'][0]['extmetadata']['Restrictions']['value'] : '';
            $new[':licenseuri'] = Tools::openContentLicenseUri($new[':licenseshortname']);
            $new[':licensename'] = Tools::openContentLicenseName($new[':licenseuri']);
            $new[':size'] = !empty($mediaFile['imageinfo'][0]['size'])
                ? $mediaFile['imageinfo'][0]['size'] : '';
            $new[':width'] = !empty($mediaFile['imageinfo'][0]['width'])
                ? $mediaFile['imageinfo'][0]['width'] : '';
            $new[':height'] = !empty($mediaFile['imageinfo'][0]['height'])
                ? $mediaFile['imageinfo'][0]['height'] : '';
            $new[':sha1'] = !empty($mediaFile['imageinfo'][0]['sha1'])
                ? $mediaFile['imageinfo'][0]['sha1'] : '';
            $new[':mime'] = !empty($mediaFile['imageinfo'][0]['mime'])
                ? $mediaFile['imageinfo'][0]['mime'] : '';
            $new[':thumburl'] = !empty($mediaFile['imageinfo'][0]['thumburl'])
                ? $mediaFile['imageinfo'][0]['thumburl'] : '';
            $new[':thumbwidth'] = !empty($mediaFile['imageinfo'][0]['thumbwidth'])
                ? $mediaFile['imageinfo'][0]['thumbwidth'] : '';
            $new[':thumbheight'] = !empty($mediaFile['imageinfo'][0]['thumbheight'])
                ? $mediaFile['imageinfo'][0]['thumbheight'] : '';
            $new[':thumbmime'] = !empty($mediaFile['imageinfo'][0]['thumbmime'])
                ? $mediaFile['imageinfo'][0]['thumbmime'] : '';
            $new[':user'] = !empty($mediaFile['imageinfo'][0]['user'])
                ? $mediaFile['imageinfo'][0]['user'] : '';
            $new[':userid'] = !empty($mediaFile['imageinfo'][0]['userid'])
                ? $mediaFile['imageinfo'][0]['userid'] : '';
            $new[':duration'] = !empty($mediaFile['imageinfo'][0]['duration'])
                ? $mediaFile['imageinfo'][0]['duration'] : '';
            $new[':timestamp'] = !empty($mediaFile['imageinfo'][0]['timestamp'])
                ? $mediaFile['imageinfo'][0]['timestamp'] : '';
            $sql = "INSERT OR REPLACE INTO media (
                        pageid, title, url,
                        descriptionurl, descriptionshorturl, imagedescription,
                        artist, datetimeoriginal,
                        licenseuri, licensename, licenseshortname, usageterms, attributionrequired, restrictions,
                        size, width, height, sha1, mime,
                        thumburl, thumbwidth, thumbheight, thumbmime,
                        user, userid, duration, timestamp
                    ) VALUES (
                        :pageid, :title, :url,
                        :descriptionurl, :descriptionshorturl, :imagedescription,
                        :artist, :datetimeoriginal,
                        :licenseuri, :licensename, :licenseshortname, :usageterms, :attributionrequired, :restrictions,
                        :size, :width, :height, :sha1, :mime,
                        :thumburl, :thumbwidth, :thumbheight, :thumbmime,
                        :user, :userid, :duration, :timestamp
                    )";
            $response = $this->queryAsBool($sql, $new);
            if ($response === false) {
                Tools::error('saveMediaToDatabase: STOPPING IMPORT: FAILED insert into media table');

                return false;
            }
            Tools::notice('SAVED MEDIA: ' . $new[':pageid'] . ' = <a href="' . Tools::url('info')
                . '/' . $new[':pageid'] . '">' . Tools::stripPrefix($new[':title']) . '</a>');
            if (!$this->linkMediaTopics($new[':pageid'])) {
                Tools::error('::: FAILED to link media topics - p:' . $new[':pageid']);
            }
        }
        $this->commit();
        $this->vacuum();
        if ($errors) {
            Tools::error($errors);
        }

        return true;
    }

    /**
     * @param string $topic
     * @return bool
     */
    public function getMediaFromTopic($topic = '')
    {
        $topic = trim($topic);
        if (!$topic) {
            return false;
        }
        $topic = ucfirst($topic);
        if (!preg_match('/^[Category:]/i', $topic)) {
            $topic = 'Topic:' . $topic;
        }
        $topicmembers = $this->commons->getApiTopicmembers($topic);
        if (!$topicmembers) {
            Tools::error('::getMediaFromTopic: No Media Found');

            return false;
        }
        $blocked = $this->queryAsArray(
            'SELECT pageid FROM block WHERE pageid IN ('
            . implode($topicmembers, ',')
            . ')'
        );
        if ($blocked) {
            Tools::error('ERROR: ' . sizeof($blocked) . ' BLOCKED MEDIA FILES');
            foreach ($blocked as $bpageid) {
                if (($key = array_search($bpageid['pageid'], $topicmembers)) !== false) {
                    unset($topicmembers[$key]);
                }
            }
        }
        $chunks = array_chunk($topicmembers, 50);
        foreach ($chunks as $chunk) {
            $this->saveMediaToDatabase($this->commons->getApiImageinfo($chunk));
        }
        $this->updateTopicLocalFilesCount($topic);
        $this->saveTopicInfo($topic);

        return true;
    }

    /**
     * @param int|string $pageid
     * @param bool $noBlock
     * @return bool|string
     */
    public function deleteMedia($pageid, $noBlock = false)
    {
        if (!$pageid || !Tools::isPositiveNumber($pageid)) {
            Tools::error('delete_media: Invalid PageID');
            return false;
        }
        $response = '<div style="white-space:nowrap;font-family:monospace;color:black;background-color:lightsalmon;">'
            . 'Deleting Media :pageid = ' . $pageid;
        $media = $this->getMedia($pageid);
        if (!$media) {
            $response .= '<p>Media Not Found</p></div>';

            return $response;
        }
        $sqls = [];
        $sqls[] = 'DELETE FROM media WHERE pageid = :pageid';
        $sqls[] = 'DELETE FROM topic2media WHERE media_pageid = :pageid';
        $sqls[] = 'DELETE FROM tagging WHERE media_pageid = :pageid';
        $bind = [':pageid' => $pageid];
        foreach ($sqls as $sql) {
            if ($this->queryAsBool($sql, $bind)) {
                //$response .= '<br />OK: ' . $sql;
            } else {
                $response .= '<br />ERROR: ' . $sql;
            }
        }
        if ($noBlock) {
            return $response . '</div>';
        }
        $sql = 'INSERT INTO block (pageid, title, thumb) VALUES (:pageid, :title, :thumb)';
        $bind = [
            ':pageid' => $pageid,
            ':title' => !empty($media[0]['title']) ? $media[0]['title'] : null,
            ':thumb' => !empty($media[0]['thumburl']) ? $media[0]['thumburl'] : null,
        ];
        if ($this->queryAsBool($sql, $bind)) {
            //$response .= '<br />OK: ' . $sql;
        } else {
            $response .= '<br />ERROR: ' . $sql;
        }

        return $response . '</div>';
    }

    /**
     * @param int|string $pageid
     * @return bool
     */
    public function linkMediaTopics($pageid)
    {
        if (!$pageid || !Tools::isPositiveNumber($pageid)) {
            Tools::error('link_media_topics: invalid pageid');

            return false;
        }
        if (!$this->commons->getTopicsFromMedia($pageid)) {
            Tools::error('linkMediaTopics: unable to get topics from API');

            return false;
        }
        // Remove any old topic links for this media
        $this->queryAsBool(
            'DELETE FROM topic2media WHERE media_pageid = :pageid',
            [':pageid' => $pageid]
        );
        foreach ($this->commons->topics as $topic) {
            if (!isset($topic['title']) || !$topic['title']) {
                Tools::error('linkMediaTopics: ERROR: missing topic title');
                continue;
            }
            if (!isset($topic['ns']) || $topic['ns'] != '14') {
                Tools::error('linkMediaTopics: ERROR: invalid topic namespace');
                continue;
            }
            $topicId = $this->getTopicIdFromName($topic['title']);
            if (!$topicId) {
                if (!$this->insertTopic($topic['title'], true, 1)) {
                    Tools::error('linkMediaTopics: FAILED to insert ' . $topic['title']);
                    continue;
                }
                $topicId = $this->topicId;
            }
            if (!$this->linkMediaToTopic($pageid, $topicId)) {
                Tools::error('linkMediaTopics: FAILED to link topic');
                continue;
            }
        }

        return true;
    }

    /**
     * @param int|string $pageid
     * @param int|string $topicId
     * @return bool
     */
    private function linkMediaToTopic($pageid, $topicId)
    {
        $response = $this->queryAsBool(
            'INSERT INTO topic2media (category_id, media_pageid) VALUES (:topic_id, :pageid)',
            ['topic_id' => $topicId, 'pageid' => $pageid]
        );
        if (!$response) {
            return false;
        }

        return true;
    }

    /**
     * @param string $name
     * @param bool $fillInfo
     * @param int $localFiles
     * @return bool
     */
    public function insertTopic($name = '', $fillInfo = true, $localFiles = 0)
    {
        if (!$name) {
            Tools::error('insert_topic: no name found');

            return false;
        }

        if (!$this->queryAsBool(
            'INSERT INTO topic (
                name, local_files, hidden, missing
            ) VALUES (
                :name, :local_files, :hidden, :missing
            )',
            [
                ':name' => $name,
                ':local_files' => $localFiles,
                ':hidden' => '0',
                ':missing' => '0'
            ]
        )
        ) {
            Tools::error('insert_topic: FAILED to insert: ' . $name);

            return false;
        }
        $this->topicId = $this->lastInsertId;
        if ($fillInfo) {
            $this->saveTopicInfo($name);
        }
        Tools::notice(
            'SAVED TOPIC: ' . $this->topicId . ' = +<a href="'
            . Tools::url('topic') . '/'
            . Tools::topicUrlencode(Tools::stripPrefix($name))
            . '">' . htmlentities((string) Tools::stripPrefix($name)) . '</a>'
        );

        return true;
    }

    /**
     * @param string $topicName
     * @return bool
     */
    public function saveTopicInfo($topicName)
    {
        $topicName = Tools::topicUrldecode($topicName);
        $topicRow = $this->getTopic($topicName);
        if (!$topicRow) {
            if (!$this->insertTopic($topicName, false, 1)) {
                Tools::error('saveTopicInfo: new topic INSERT FAILED: ' . $topicName);

                return false;
            }
            Tools::notice('saveTopicInfo: NEW TOPIC: '  . $topicName);
            $topicRow = $this->getTopic($topicName);
        }
        $topicInfo = $this->commons->getTopicInfo($topicName);
        foreach ($topicInfo as $onesy) {
            $topicInfo = $onesy; // is always just 1 result
        }
        $bind = [];
        if (!empty($topicInfo['pageid']) && ($topicInfo['pageid'] != $topicRow['pageid'])) {
            $bind[':pageid'] = $topicInfo['pageid'];
        }
        if ($topicInfo['categoryinfo']['files'] != $topicRow['files']) {
            $bind[':files'] = $topicInfo['categoryinfo']['files'];
        }
        if ($topicInfo['categoryinfo']['subcats'] != $topicRow['subcats']) {
            $bind[':subcats'] = $topicInfo['categoryinfo']['subcats'];
        }
        $hidden = 0;
        if (isset($topicInfo['categoryinfo']['hidden'])) {
            $hidden = 1;
        }
        if ($hidden != $topicRow['hidden']) {
            $bind[':hidden'] = $hidden;
        }
        $missing = 0;
        if (isset($topicInfo['categoryinfo']['missing'])) {
            $missing = 1;
        }
        if ($missing != $topicRow['missing']) {
            $bind[':missing'] = $missing;
        }
        if (!$bind) {
            return true;
        }
        $sql = 'UPDATE topic SET ';
        $sets = [];
        foreach (array_keys($bind) as $set) {
            $sets[] = str_replace(':', '', $set) . ' = ' . $set;
        }
        $sql .= implode($sets, ', ');
        $sql .= ' WHERE id = :id';
        $bind[':id'] = $topicRow['id'];
        $result = $this->queryAsBool($sql, $bind);
        if ($result) {
            return true;
        }
        Tools::error(
            'saveTopicInfo: UPDATE/INSERT FAILED: ' . print_r($this->lastError, true)
        );

        return false;
    }

    /**
     * @param string $topicName
     * @return bool
     */
    public function updateTopicLocalFilesCount($topicName)
    {
        $sql = 'UPDATE topic SET local_files = :local_files WHERE id = :id';
        $bind[':local_files'] = $this->getTopicSize($topicName);
        if (is_int($topicName)) {
            $bind['id'] = $topicName;
        } else {
            $bind[':id'] = $this->getTopicIdFromName($topicName);
        }

        if (!$bind[':id']) {
            Tools::error("update_topic_local_files_count( $topicName ) - Topic Not Found in Database");

            return false;
        }
        if ($this->queryAsBool($sql, $bind)) {
            Tools::notice('UPDATE TOPIC SIZE: ' . $bind[':local_files'] . ' files in ' . $topicName);

            return true;
        }
        Tools::error("update_topic_local_files_count( $topicName ) - UPDATE ERROR");

        return false;
    }

    /**
     * updateTopicsLocalFilesCount
     */
    public function updateTopicsLocalFilesCount()
    {
        $sql = '
            SELECT c.id, c.local_files, count(c2m.category_id) AS size
            FROM topic AS c
            LEFT JOIN topic2media AS c2m ON c.id = c2m.category_id
            GROUP BY c.id
            ORDER by c.local_files ASC';

        $topicNewSizes = $this->queryAsArray($sql);
        if (!$topicNewSizes) {
            Tools::error('NOT FOUND: Updated 0 Topics Local Files count');

            return;
        }
        $updates = 0;
        $this->beginTransaction();
        foreach ($topicNewSizes as $cat) {
            if ($cat['local_files'] == $cat['size']) {
                continue;
            }
            if ($this->insertTopicLocalFilesCount($cat['id'], $cat['size'])) {
                $updates++;
            } else {
                Tools::error('ERROR: UPDATE FAILED: Topic ID:' . $cat['id'] . ' local_files=' . $cat['size']);
            }
        }
        $this->commit();
        Tools::notice('Updated ' . $updates . ' Topics Local Files count');
        $this->vacuum();
    }

    /**
     * @param int|string $topicId
     * @param int|string $topicSize
     * @return bool
     */
    private function insertTopicLocalFilesCount($topicId, $topicSize)
    {
        $sql = 'UPDATE topic SET local_files = :topic_size WHERE id = :topic_id';
        $bind[':topic_size'] = $topicSize;
        $bind[':topic_id'] = $topicId;
        if ($this->queryAsBool($sql, $bind)) {
            return true;
        }

        return false;
    }

    /**
     * @param int|string $topicId
     * @return bool
     */
    public function deleteTopic($topicId)
    {
        if (!Tools::isPositiveNumber($topicId)) {
            return false;
        }
        $bind = [':topic_id' => $topicId];
        if ($this->queryAsBool('DELETE FROM topic WHERE id = :topic_id', $bind)) {
            Tools::notice('DELETED Topic #' . $topicId);
        } else {
            Tools::error('UNABLE to delete topic #' . $topicId);

            return false;
        }
        if ($this->queryAsBool('DELETE FROM topic2media WHERE category_id = :topic_id', $bind)) {
            Tools::notice('DELETED Links to Topic #' . $topicId);
        } else {
            Tools::error('UNABLE to delete links to topic #' . $topicId);

            return false;
        }

        return true;
    }

    // Empty / Drop

    /**
     * @param array $sqls
     * @return array
     */
    private function runSqls($sqls)
    {
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
    public function emptyTopicTables()
    {
        return $this->runSqls(
            [
                'DELETE FROM topic2media',
                'DELETE FROM topic',
            ]
        );
    }

    /**
     * @return array
     */
    public function emptyTaggingTables()
    {
        return $this->runSqls(
            [
                'DELETE FROM tagging',
            ]
        );
    }

    /**
     * @return array
     */
    public function emptyMediaTables()
    {
        return $this->runSqls(
            [
                'DELETE FROM tagging',
                'DELETE FROM topic2media',
                'DELETE FROM media',
                'DELETE FROM block',
            ]
        );
    }

    /**
     * @return array
     */
    public function emptyUserTables()
    {
        return $this->runSqls(
            [
                'DELETE FROM user',
                'DELETE FROM tagging',
            ]
        );
    }

    /**
     * @return array
     */
    public function dropTables()
    {
        return $this->runSqls(
            [
                'DROP TABLE IF EXISTS block',
                'DROP TABLE IF EXISTS topic',
                'DROP TABLE IF EXISTS topic2media',
                'DROP TABLE IF EXISTS media',
                'DROP TABLE IF EXISTS site',
                'DROP TABLE IF EXISTS tag',
                'DROP TABLE IF EXISTS tagging',
                'DROP TABLE IF EXISTS user',
            ]
        );
    }

    // Block

    /**
     * @return int
     */
    public function getBlockCount()
    {
        $count = $this->queryAsArray('SELECT count(pageid) AS count FROM block');
        if (isset($count[0]['count'])) {
            return $count[0]['count'];
        }

        return 0;
    }
}
