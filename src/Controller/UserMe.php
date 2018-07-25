<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class UserMe
 */
class UserMe extends ControllerBase
{
    protected function display()
    {
        $this->smt->includeTemplate('HtmlHeader');
        $this->smt->includeTemplate('Menu');

        $this->page = 1;
        $vars = $this->smt->router->getVars();
        if (!empty($vars[0]) && Tools::isPositiveNumber($vars[0])) {
            $this->page = (int) $vars[0];
        }

        $data['numberMediaVotes'] = $this->smt->database->getUserTagCount($this->smt->database->userId);

        $data['urlName'] = 'me';
        $data['page'] = $this->page;
        $data['pages'] = ceil($data['numberMediaVotes'] / $this->limit);
        $data['limit'] = $this->limit;

        $data['media'] = $this->smt->database->queryAsArray(
            'SELECT tag.score,
                        media.*
                FROM media, 
                     tagging,
                     tag
                WHERE tagging.media_pageid = media.pageid
                AND tagging.tag_id = tag.id
                AND tagging.user_id = :user_id
                LIMIT :limit
                OFFSET :offset',
            [
                ':user_id' => $this->smt->database->userId,
                ':limit' => $this->limit,
                ':offset' => ($this->page * $this->limit) - $this->limit,
            ]
        );

        /** @noinspection PhpIncludeInspection */
        include($this->getView('UserMe'));

        $this->smt->includeTemplate('Menu');
        $this->smt->includeTemplate('HtmlFooter');
    }
}
