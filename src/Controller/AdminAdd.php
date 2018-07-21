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

        if (!empty($_GET['s'])) {
            print '<PRE class="bg-white">_GET<BR>';
            print_r($_GET);
            Tools::shutdown();
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
}
