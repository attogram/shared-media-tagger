<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;

/**
 * Class Search
 */
class Search extends ControllerBase
{
    protected function display()
    {
        $this->smt->title = 'Search - ' . Config::$siteName;
        $this->smt->includeHeader();
        $this->smt->includeTemplate('MenuSmall');

        $data = [];
        $data['query'] = '';
        if (!empty($_GET['q'])) {
            $data['query'] = (string) trim($_GET['q']);
        }
        if (strlen($data['query']) > 256) {
            $data['query'] = substr($data['query'], 0, 256);
        }

        $data['results'] = $this->search($data['query']);

        /** @noinspection PhpIncludeInspection */
        include($this->getView('Search'));
        $this->smt->includeFooter();
    }

    /**
     * @param string $query
     * @return array
     */
    private function search(string $query)
    {
        if (empty($query)) {
            return [];
        }

        return $this->smt->database->queryAsArray(
            'SELECT * 
            FROM media 
            WHERE title LIKE :query
            OR imagedescription LIKE :query
            OR artist LIKE :query
            LIMIT :limit',
            [
                ':limit' => 20, // @TODO paging
                ':query' => "%$query%"
            ]
        );
    }
}
