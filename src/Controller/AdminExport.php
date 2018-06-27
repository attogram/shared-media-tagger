<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class AdminExport
 */
class AdminExport extends ControllerBase
{
    protected function display()
    {
        $view = $this->getView('AdminExport');

        $data = [];
        $data['tags'] = $this->smt->database->getTags();

        $data['result'] = '';

        $action = !empty($_GET['r']) ? $_GET['r'] : null;
        switch ($action) {
            case 'tag':
                if (!empty($_GET['i'])) {
                    $data['result'] = $this->tagReport($_GET['i']);
                }
                break;
        }

        $this->smt->title = 'Export Admin';
        $this->smt->includeHeader();
        $this->smt->includeMediumMenu();
        $this->smt->includeAdminMenu();

        /** @noinspection PhpIncludeInspection */
        include($view);

        $this->smt->includeFooter();
    }

    /**
     * @param string $tagId
     * @return string
     */
    public function tagReport($tagId = '')
    {
        if (!$tagId || !Tools::isPositiveNumber($tagId)) {
            return 'Tag Report: Tag ID NOT FOUND';
        }

        $tagName = $this->smt->database->getTagNameById($tagId);

        $sql =
            'SELECT m.title, t.count
            FROM media AS m, tagging AS t
            WHERE m.pageid = t.media_pageid
            AND t.tag_id = :tag_id
            LIMIT 200';
        $medias = $this->smt->database->queryAsArray($sql, [':tag_id' => $tagId]);
        $cr = "\n";
        $reportName = 'Tag Report: ' . $tagName . ' - Top ' . sizeof($medias) . ' Files';

        $export = '== ' . $reportName . ' ==' . $cr
            . '* Tagging Site   : ' . Config::$siteName . $cr
            . '* Collection Size: ' . sizeof($medias) . $cr
            . '* Report Date    : ' . Tools::timeNow() . ' UTC' . $cr
            . '* Created with   : Shared Media Tagger v' . SHARED_MEDIA_TAGGER . $cr
            . '<gallery caption="' . $reportName . '" widths="100px" heights="100px" perrow="6">' . $cr;

        foreach ($medias as $media) {
            $export .= $media['title'] . '|+' . $media['count'] . $cr;
        }

        return $export . '</gallery>';
    }
}
