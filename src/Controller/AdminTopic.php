<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class AdminTopic
 */
class AdminTopic extends ControllerBase
{
    protected function display()
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(1000);
        }

        $this->smt->title = 'Topic Admin';
        $this->smt->includeHeader();
        $this->smt->includeTemplate('Menu');
        $this->smt->includeTemplate('AdminMenu');


        // Import images from a topic
        if (isset($_GET['i']) && $_GET['i']) {
            print '<div class="container-fluid bg-white">';
            $topicName = Tools::topicUrldecode($_GET['i']);
            $catUrl = '<a href="' . Tools::url('topic')
                . '/' . Tools::topicUrlencode(Tools::stripPrefix($topicName)) . '">'
                . htmlentities((string) Tools::stripPrefix($topicName)) . '</a>';
            Tools::debug('Importing media from <b>' . $catUrl . '</b>');
            $this->smt->database->getMediaFromTopic($topicName);
            $this->smt->database->updateTopicsLocalFilesCount();
            Tools::debug('Imported media from <b>' . $catUrl . '</b>');
            print '</div>';
            $this->smt->includeFooter();
            Tools::shutdown();
        }

        if (isset($_POST['cats']) && $_POST['cats']) {
            print '<div class="container-fluid bg-white">';
            $this->smt->importTopics($_POST['cats']);
            $this->smt->database->updateTopicsLocalFilesCount();
            print '</div>';
            $this->smt->includeFooter();
            Tools::shutdown();
        }

        if (isset($_GET['c']) && $_GET['c']) {
            print '<div class="container-fluid bg-white">';
            if ($this->smt->database->saveTopicInfo(urldecode($_GET['c']))) {
                Tools::notice(
                    'OK: Refreshed Topic: <b><a href="' . Tools::url('topic')
                    . '/' . Tools::stripPrefix(Tools::topicUrlencode($_GET['c'])) . '">'
                    . htmlentities((string) Tools::topicUrldecode($_GET['c'])) . '</a></b>'
                );
            }
            print '</div>';
            $this->smt->includeFooter();
            Tools::shutdown();
        }

        if (isset($_GET['d']) && $_GET['d']) {
            print '<div class="container-fluid bg-white">';
            $this->smt->database->deleteTopic($_GET['d']);
            $this->smt->database->updateTopicsLocalFilesCount();
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
            $this->smt->commons->getSubcats(Tools::topicUrldecode($_GET['sc']));
            $this->smt->database->updateTopicsLocalFilesCount();
            print '</div>';
            $this->smt->includeFooter();
            Tools::shutdown();
        }

        $orderBy = ' ORDER BY hidden ASC, local_files DESC, files DESC, name ASC ';

        if (isset($_GET['g']) && $_GET['g']=='all') {
            print '<div class="container-fluid bg-white">';
            Tools::notice('refresh Info for all topics');
            $toget = [];
            $cats = $this->smt->database->queryAsArray('SELECT * FROM topic ' . $orderBy);
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
            $topicInfo = $this->smt->commons->getTopicInfo($_GET['c']);
            //Tools::debug('got topicInfo: <pre>' . print_r($topicInfo, true) . '</pre>');

            Tools::error('@TODO - import topicInfo to DB');
            print '</div>';
            $this->smt->includeFooter();
            Tools::shutdown();
        }


        if (isset($_GET['sca']) && $_GET['sca']=='all') {
            $sql = 'SELECT * FROM category WHERE subcats > 0 ' . $orderBy;
            Tools::notice('SHOWING only topics with subtopics');
        } elseif (isset($_GET['wf'])) {
            $sql = 'SELECT * FROM category WHERE files > 0 ' . $orderBy;
            Tools::notice('SHOWING only topics with files');
        } elseif (isset($_GET['s'])) {
            $sql = 'SELECT * FROM category WHERE name LIKE :search ' . $orderBy;
            $bind = [':search'=>'%' . $_GET['s']. '%'];
            Tools::notice('SHOWING only topics with search text: ' . $_GET['s']);
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
        include($this->getView('AdminTopic'));

        $this->smt->includeFooter();
    }
}
