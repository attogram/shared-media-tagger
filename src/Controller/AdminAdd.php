<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Api\Category as ApiTopic;
use Attogram\SharedMedia\Api\Media as ApiMedia;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Add Media To Collection - Controller
 */
class AdminAdd extends ControllerBase
{
    protected function display()
    {
        $this->smt->title = 'Add Media To Collection';
        $this->smt->includeHeader();
        $this->smt->includeTemplate('Menu');
        $this->smt->includeTemplate('AdminMenu');

        if (!empty($_GET['ti'])      // add topic info
            || !empty($_GET['tm'])  // add media from topic
            || !empty($_GET['ts'])  // add subtopics from topic
            || !empty($_GET['m'])   // add media
        ) {
            $this->doSubmit();
        }

        $data = [];
        $data['query'] = '';
        $data['results'] = [];

        if (!empty($_GET['q'])) {
            $data['query'] = (string) trim($_GET['q']);

            $data['type'] = 'topics';
            if (!empty($_GET['t'])) {
                $data['type'] = (string) trim($_GET['t']);
            }

            switch ($data['type']) {
                case 'topics':
                    $data['results'] = $this->searchTopics($data['query'], 250);
                    break;
                case 'media':
                    $data['results'] = $this->searchMedia($data['query'], 20);
                    break;
                default:
                    break;
            }
        }

        /** @noinspection PhpIncludeInspection */
        include($this->getView('AdminAdd'));

        $this->smt->includeFooter();
        Tools::shutdown();
    }

    /**
     * @param string $query
     * @param int $limit
     * @return array
     */
    private function searchTopics(string $query, $limit = 50)
    {
        return $this->apiSearch(new ApiTopic(), $query, $limit);
    }

    /**
     * @param string $query
     * @param int $limit
     * @return array
     */
    private function searchMedia(string $query, $limit = 10)
    {
        return $this->apiSearch(new ApiMedia(), $query, $limit);
    }

    /**
     * @param ApiTopic|ApiMedia $api
     * @param string $query
     * @param int $limit
     * @return array
     */
    private function apiSearch($api, string $query, $limit = 10)
    {
        $api->setLimit($limit);
        $items = $api->search($query);
        if (!empty($items)) {
            return $items;
        }

        return [];
    }

    private function doSubmit()
    {
        // ti[] - Add Topic Info (via array of Pageids)
        if (!empty($_GET['ti'])) {
            $this->addTopics($_GET['ti']);
        }

        // tm[] - Add Media From Topic (via Pageid)
        if (!empty($_GET['tm'])) {
            $this->addMediaFromTopic($_GET['tm']);
        }

        // ts[] - Add Subtopics From Topic (via Pageid)
        if (!empty($_GET['ts'])) {
            $this->addSubtopicsFromTopic($_GET['ts']);
        }

        // m[] - Add Media (via Pageid)
        if (!empty($_GET['m'])) {
            $this->addMedia($_GET['m']);
        }

        $this->smt->database->updateTopicsLocalFilesCount();

        $this->smt->includeTemplate('Menu');
        $this->smt->includeTemplate('HtmlFooter');
        Tools::shutdown();
    }

    /**
     * @param array $pageids
     */
    private function addMedia(array $pageids)
    {
        Tools::debug('Adding ' . count($pageids) . ' Media');
        $apiMedia = new ApiMedia();
        $apiMedia->setPageid(implode('|', $pageids));
        $medias = $apiMedia->info();
        foreach ($medias as $media) {
            Tools::debug($media);
        }
    }

    /**
     * @param array $pageids
     */
    private function addTopics(array $pageids)
    {
        $topicCount = count($pageids);
        $pageidString = implode('|', $pageids);
        $apiTopic = new ApiTopic();
        $apiTopic->setPageid($pageidString);
        $apiTopic->setLimit($topicCount);
        $topics = $apiTopic->info();
        foreach ($topics as $topic) {
            $this->saveTopic($topic);
        }
    }

    /**
     * @param array $topic
     * @return bool
     */
    public function saveTopic(array $topic = [])
    {
        if (empty($topic['title'])) {
            Tools::error('saveTopic: Title Not Found: ' . print_r($topic, true));

            if (empty($topic['pageid'])) {
                return false;
            }

            // get topic with this pageid
            $missingTopic = $this->smt->database->queryAsArray(
                'SELECT * FROM topic WHERE pageid = :pageid',
                [':pageid' => $topic['pageid']]
            );
            if (empty($missingTopic[0])) {
                return false;
            }
            Tools::debug('missingTopic: ' . print_r($missingTopic[0], true));

            $topic = $missingTopic[0];
            $topic['missing'] = 1;
            $topic['title'] = $topic['name'];
            $topic['updated'] = Tools::timeNow();
        }
        $topicName = $topic['title'];
        $topicCurrent = $this->smt->database->getTopic($topicName);
        if (empty($topicCurrent)) {
            return $this->insertTopic($topic);
        }
        $topic['id'] = $topicCurrent['id'];
        return $this->updateTopic($topic);
    }

    /**
     * @param array $topic
     * @return bool
     */
    private function insertTopic(array $topic)
    {
        $fieldsArray = [];
        $valuesArray = [];
        $bind = [];
        foreach ($this->getBind($topic) as $name => $value) {
            $fieldsArray[] = $name;
            $valuesArray[] = ":$name";
            $bind[":$name"] = $value;
        }
        $fields = implode(', ', $fieldsArray);
        $values = implode(', ', $valuesArray);
        $sql = "INSERT INTO topic ($fields) VALUES ($values)";
        if ($this->smt->database->queryAsBool($sql, $bind)) {
            $topicName = Tools::stripPrefix($bind[':name']);
            Tools::debug(
                'Inserted Topic: <a href="'
                . Tools::url('topic') . '/' . Tools::topicUrlencode($topicName)
                . '">c/' . $topicName . '</a>'
            );

            return true;
        }
        Tools::error('Insert Topic FAILED');

        return false;
    }

    /**
     * @param array $topic
     * @return bool
     */
    private function updateTopic(array $topic)
    {
        $setArray = [];
        $bind = [];
        $bind[':id'] = $topic['id'];
        foreach ($this->getBind($topic) as $name => $value) {
            $setArray[] = "$name = :$name";
            $bind[":$name"] = $value;
        }
        $sets = implode(', ', $setArray);
        $sql = "UPDATE topic SET $sets WHERE id = :id";
        if ($this->smt->database->queryAsBool($sql, $bind)) {
            $topicName = Tools::stripPrefix($bind[':name']);
            Tools::debug(
                'Refreshed Topic: <a href="'
                . Tools::url('topic') . '/' . Tools::topicUrlencode($topicName)
                . '">c/' . $topicName . '</a>'
            );

            return true;
        }
        Tools::error(
            'Update Topic FAILED: '
            . print_r($this->smt->database->lastError, true)
        );

        return false;
    }

    /**
     * @param array $topic
     * @return array
     */
    private function getBind(array $topic)
    {
        $bind = [];
        // API to DB mapping
        $fields = [
            'pageid',
            'title',
            'files',
            'subcats',
            'hidden',
            'missing',
            'curated',
            'local_files',
            'curated_files',
        ];
        foreach ($fields as $field) {
            if (empty($topic[$field])) {
                $topic[$field] = '0';
            }
            $bindField = $field;
            if ($field == 'title') {
                $bindField = 'name';
            }
            $bind[$bindField] = $topic[$field];
        }
        $bind['updated'] = Tools::timeNow();

        return $bind;
    }

    /**
     * @param array $pageids
     */
    private function addMediaFromTopic(array $pageids)
    {
        Tools::debug('Adding Media From ' . count($pageids) . ' Topics');
        $apiMedia = new ApiMedia();
        $apiMedia->setPageid(implode('|', $pageids));
        $medias = $apiMedia->getMediaInCategory();
        foreach ($medias as $media) {
            Tools::debug($media);
        }
    }

    /**
     * @param array $pageids
     */
    private function addSubtopicsFromTopic(array $pageids)
    {
        Tools::debug('Adding Subtopics from ' . count($pageids) . ' Topics');
        $apiTopic = new ApiTopic();
        $apiTopic->setPageid(implode('|', $pageids));
        $subcats = $apiTopic->subcats();
        foreach ($subcats as $subcat) {
            Tools::debug($subcat);
        }
    }
}
