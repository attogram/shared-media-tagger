<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\TaggerAdmin;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class AdminCurate
 */
class AdminCurate extends ControllerBase
{
    protected function display()
    {
        $view = $this->getView('AdminCurate');

        $data = [];

        if (isset($_GET) && $_GET) {
            $this->curateBatch($this->smt);
        }

        $uncuratedCount = $this->getUncuratedCount($this->smt);

        $pageLimit = 20; // # of files per page

        if (isset($_GET['l']) && Tools::isPositiveNumber($_GET['l'])) {
            $pageLimit = (int) $_GET['l'];
        }
        if ($pageLimit > 1000) {
            $pageLimit = 1000;
        }
        if ($pageLimit < 1) {
            $pageLimit = 1;
        }

        $sql = "SELECT *
                FROM media
                WHERE curated != '1'
                ORDER BY updated ASC
                LIMIT " . $pageLimit;

        if (isset($_GET['i']) && Tools::isPositiveNumber($_GET['i'])) {
            $data['medias'] = $this->smt->database->getMedia($_GET['i']);
        } else {
            $data['medias'] = $this->smt->database->queryAsArray($sql);
        }

        $data['menu'] = '<div style="background-color:#ddd; color:black; padding-left:10px;">'
            . '<input type="submit" value="          Curate Marked Files        " />'
            . ' <span style="display:inline-block; font-size:90%;">Mark ALL '
            . ' <a href="javascript:mark_all_keep();">[KEEP]</a>'
            . ' <a href="javascript:mark_all_delete();">[DELETE]</a>'
            . ' <a href="javascript:mark_all_que();">[QUE]</a></span>'
            . ' - <a href="./curate?l=' . $pageLimit.'">' . $pageLimit . '</a> of '
            . number_format((float) $uncuratedCount) . ' in que'
            . '</div>';

        $this->smt->title = 'Curation Admin';
        $this->smt->includeHeader();
        $this->smt->includeTemplate('MenuSmall');
        $this->smt->includeAdminMenu();

        /** @noinspection PhpIncludeInspection */
        include($view);

        $this->smt->includeFooter();
    }

    /**
     * @param TaggerAdmin $smt
     */
    public function curateBatch(TaggerAdmin $smt)
    {
        if (!empty($_GET['keep'])) {
            $this->curateKeep($_GET['keep'], $smt);
        }
        if (!empty($_GET['delete'])) {
            $this->curateDelete($_GET['delete'], $smt);
        }
    }

    /**
     * @param array $id_array
     * @param TaggerAdmin $smt
     * @return bool
     */
    public function curateKeep(array $id_array, TaggerAdmin $smt)
    {
        if (!is_array($id_array) || !$id_array) {
            return false;
        }
        $ids = implode($id_array, ', ');
        $sql = "UPDATE media SET curated = '1', updated = CURRENT_TIMESTAMP WHERE pageid IN ($ids)";
        if ($smt->database->queryAsBool($sql)) {
            Tools::notice('Curate: KEEP ' . sizeof($id_array));

            return true;
        }
        Tools::error('ERROR setting media curated to KEEP: ' . $smt->database->lastError);

        return false;
    }

    /**
     * @param array $id_array
     * @param TaggerAdmin $smt
     * @return bool
     */
    public function curateDelete(array $id_array, TaggerAdmin $smt)
    {
        if (!is_array($id_array) || !$id_array) {
            return false;
        }
        foreach ($id_array as $pageid) {
            $smt->database->deleteMedia($pageid);
        }
        Tools::notice('Curate: DELETE ' . sizeof($id_array));
        $smt->database->updateCategoriesLocalFilesCount();

        return true;
    }

    /**
     * @param TaggerAdmin $smt
     * @return string
     */
    public function getUncuratedCount(TaggerAdmin $smt)
    {
        $count = $smt->database->queryAsArray(
            "SELECT count(pageid) AS count FROM media WHERE curated != '1'"
        );
        if (isset($count[0]['count'])) {
            return $count[0]['count'];
        }
        Tools::error($smt->database->lastError);

        return 'ERR';
    }
}
