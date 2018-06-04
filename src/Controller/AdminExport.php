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
            case 'network':
                $data['result'] = $this->networkExport();
                break;
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
     * @return string
     */
    public function networkExport()
    {
        $cr = "\n";
        $tab = ", ";
        $site = Config::$protocol . Config::$siteUrl;

        $export = '# SMT NETWORK URL: ' . $site . $cr
            . '# SMT NETWORK ID : ' . md5($site) . $cr
            . '# SMT VERSION    : ' . SHARED_MEDIA_TAGGER . $cr
            . '# EXPORT TIME    : ' . Tools::timeNow() . ' UTC' . $cr
            . '# FIELDS         : PAGEID, NAMESPACEID, "NAME"' . $cr;

        $cats = $this->smt->database->queryAsArray('
        SELECT pageid, name
        FROM category
        WHERE local_files > 0
        ORDER BY name');
        foreach ($cats as $cat) {
            if (!$cat['pageid']) {
                $cat['pageid'] = 'NULL';
            }
            if (!$cat['name']) {
                $cat['name'] = 'NULL';
            }
            $export .= $cat['pageid'] . $tab . '14' . $tab . '"' . $cat['name'] . '"' . $cr;
        }
        unset($cats);

        $medias = $this->smt->database->queryAsArray(
            'SELECT pageid, title
                FROM media
                ORDER BY title'
        );
        foreach ($medias as $media) {
            if (!$media['pageid']) {
                $media['pageid'] = 'NULL';
            }
            if (!$media['title']) {
                $media['title'] = 'NULL';
            }
            $export .= $media['pageid'] . $tab . '6' . $tab . '"' . $media['title'] . '"' . $cr;
        }
        unset($medias);

        return $export;
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
            . '* Collection ID: <code>' . md5(Config::$siteName . $reportName) . '</code>' . $cr
            . '* Collection Size: ' . sizeof($medias) . $cr
            . '* Created on: ' . Tools::timeNow() . ' UTC' . $cr
            . '* Created with: Shared Media Tagger v' . SHARED_MEDIA_TAGGER . $cr
            . '<gallery caption="' . $reportName . '" widths="100px" heights="100px" perrow="6">' . $cr;

        foreach ($medias as $media) {
            $export .= $media['title'] . '|+' . $media['count'] . $cr;
        }

        return $export;
    }
}
