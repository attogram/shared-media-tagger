<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;

/**
 * Class Sitemap
 */
class Sitemap extends ControllerBase
{
    protected function display()
    {
        $data = [];
        $data['protocol'] = Config::$protocol . '//' . Config::$server;
        $data['time'] = gmdate('Y-m-d');
        $data['topics'] = $this->smt->database->queryAsArray(
            'SELECT DISTINCT(c2m.category_id), c.name
            FROM topic2media AS c2m, topic AS c
            WHERE c2m.category_id = c.id'
        );
        $data['media'] = $this->smt->database->queryAsArray(
            'SELECT pageid FROM media'
        );

        header('Content-type: application/xml');
        /** @noinspection PhpIncludeInspection */
        include($this->getView('Sitemap'));
    }
}
