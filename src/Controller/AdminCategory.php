<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class AdminCategory
 */
class AdminCategory extends ControllerBase
{
    protected function display()
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(1000);
        }

        $this->smt->title = 'Category Admin';
        $this->smt->includeHeader();
        $this->smt->includeTemplate('Menu');
        $this->smt->includeTemplate('AdminMenu');


        // Import images from a category
        if (isset($_GET['i']) && $_GET['i']) {
            print '<div class="container-fluid bg-white">';
            $categoryName = Tools::categoryUrldecode($_GET['i']);
            $catUrl = '<a href="' . Tools::url('category')
                . '/' . Tools::categoryUrlencode(Tools::stripPrefix($categoryName)) . '">'
                . htmlentities((string) Tools::stripPrefix($categoryName)) . '</a>';
            Tools::debug('Importing media from <b>' . $catUrl . '</b>');
            $this->smt->database->getMediaFromCategory($categoryName);
            $this->smt->database->updateCategoriesLocalFilesCount();
            Tools::debug('Imported media from <b>' . $catUrl . '</b>');
            print '</div>';
            $this->smt->includeFooter();
            Tools::shutdown();
        }

        if (isset($_POST['cats']) && $_POST['cats']) {
            print '<div class="container-fluid bg-white">';
            $this->smt->importCategories($_POST['cats']);
            $this->smt->database->updateCategoriesLocalFilesCount();
            print '</div>';
            $this->smt->includeFooter();
            Tools::shutdown();
        }

        if (isset($_GET['c']) && $_GET['c']) {
            print '<div class="container-fluid bg-white">';
            if ($this->smt->database->saveCategoryInfo(urldecode($_GET['c']))) {
                Tools::notice(
                    'OK: Refreshed Category Info: <b><a href="' . Tools::url('category')
                    . '/' . Tools::stripPrefix(Tools::categoryUrlencode($_GET['c'])) . '">'
                    . htmlentities((string) Tools::categoryUrldecode($_GET['c'])) . '</a></b>'
                );
            }
            print '</div>';
            $this->smt->includeFooter();
            Tools::shutdown();
        }

        if (isset($_GET['d']) && $_GET['d']) {
            print '<div class="container-fluid bg-white">';
            $this->smt->database->deleteCategory($_GET['d']);
            $this->smt->database->updateCategoriesLocalFilesCount();
            print '</div>';
            $this->smt->includeFooter();
            Tools::shutdown();
        }

        if (isset($_GET['scommons']) && $_GET['scommons']) {
            print '<div class="container-fluid bg-white">';
            $this->smt->getSearchResults();
            print '</div>';
            $this->smt->includeFooter();
            Tools::shutdown();
        }

        if (isset($_GET['sc']) && $_GET['sc']) {
            print '<div class="container-fluid bg-white">';
            $this->smt->commons->getSubcats(Tools::categoryUrldecode($_GET['sc']));
            $this->smt->database->updateCategoriesLocalFilesCount();
            print '</div>';
            $this->smt->includeFooter();
            Tools::shutdown();
        }

        $orderBy = ' ORDER BY hidden ASC, local_files DESC, files DESC, name ASC ';

        if (isset($_GET['g']) && $_GET['g']=='all') {
            print '<div class="container-fluid bg-white">';
            Tools::notice('refresh Info for all categories');
            $toget = [];
            $cats = $this->smt->database->queryAsArray('SELECT * FROM category ' . $orderBy);
            foreach ($cats as $cat) {
                if ($cat['subcats'] != '' && $cat['files'] != '' && $cat['pageid'] != '') {
                    continue;
                }
                if (sizeof($toget) == 50) { // @TODO split into blocks
                    break;
                }
                $toget[] = $cat['name'];
            }
            $_GET['c'] = implode('|', $toget);
            //Tools::notice('refreshing: ' . $_GET['c']);
            $categoryInfo = $this->smt->commons->getCategoryInfo($_GET['c']);
            //Tools::debug('got categoryInfo: <pre>' . print_r($categoryInfo, true) . '</pre>');

            Tools::error('@TODO - import categoryInfo to DB');
            print '</div>';
            $this->smt->includeFooter();
            Tools::shutdown();
        }


        if (isset($_GET['sca']) && $_GET['sca']=='all') {
            $sql = 'SELECT * FROM category WHERE subcats > 0 ' . $orderBy;
            Tools::notice('SHOWING only categories with subcategories');
        } elseif (isset($_GET['wf'])) {
            $sql = 'SELECT * FROM category WHERE files > 0 ' . $orderBy;
            Tools::notice('SHOWING only categories with files');
        } elseif (isset($_GET['s'])) {
            $sql = 'SELECT * FROM category WHERE name LIKE :search ' . $orderBy;
            $bind = [':search'=>'%' . $_GET['s']. '%'];
            Tools::notice('SHOWING only categories with search text: ' . $_GET['s']);
        } else {
            $sql = 'SELECT * FROM category ' . $orderBy;
        }
        if (!isset($bind)) {
            $bind = [];
        }
        $cats = $this->smt->database->queryAsArray($sql, $bind);

        if (!is_array($cats)) {
            $cats = [];
        }

        /** @noinspection PhpIncludeInspection */
        include($this->getView('AdminCategory'));

        $this->smt->includeFooter();
    }
}
