<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class AdminTopicMass
 */
class AdminTopicMass extends ControllerBase
{
    protected function display()
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(1000);
        }
        $data = [];
        $data['topics'] = $this->smt->database->queryAsArray(
            'SELECT * FROM topic ORDER BY updated ASC, pageid DESC LIMIT 50'
        );
        $data['refresh'] = Tools::url('admin') . '/add?ti[]='
            . implode('&amp;ti[]=', array_column($data['topics'], 'pageid'));

        $this->smt->title = 'Topic Mass Admin';
        $this->smt->includeHeader();
        $this->smt->includeTemplate('Menu');
        $this->smt->includeTemplate('AdminMenu');
        /** @noinspection PhpIncludeInspection */
        include($this->getView('AdminTopicMass'));
        $this->smt->includeFooter();
    }
}
