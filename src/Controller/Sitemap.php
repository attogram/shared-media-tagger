<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class Sitemap
 */
class Sitemap extends ControllerBase
{
    protected function display()
    {
        $view = $this->getView('Sitemap');
        $data = [];
        $data['protocol'] = Config::$protocol . '//' . Config::$server;
        $data['time'] = gmdate('Y-m-d');
        $data['categories'] = $this->smt->database->queryAsArray(
            'SELECT DISTINCT(c2m.category_id), c.name
            FROM category2media AS c2m, category AS c
            WHERE c2m.category_id = c.id'
        );
        $data['media'] = $this->smt->database->queryAsArray(
            'SELECT pageid FROM media'
        );
        header('Content-type: application/xml');
        /** @noinspection PhpIncludeInspection */
        include($view);
    }
}
