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

        // Import Media From Topic
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

        // Delete Topic
        if (isset($_GET['d']) && $_GET['d']) {
            print '<div class="container-fluid bg-white">';
            $this->smt->database->deleteTopic($_GET['d']);
            $this->smt->database->updateTopicsLocalFilesCount();
            print '</div>';
            $this->smt->includeFooter();
            Tools::shutdown();
        }

        // Import Subtopics from Topic
        if (isset($_GET['sc']) && $_GET['sc']) {
            print '<div class="container-fluid bg-white">';
            $this->smt->commons->getSubcats(Tools::topicUrldecode($_GET['sc']));
            $this->smt->database->updateTopicsLocalFilesCount();
            print '</div>';
            $this->smt->includeFooter();
            Tools::shutdown();
        }


        $cats = $this->smt->database->queryAsArray(
            'SELECT * 
            FROM category
            WHERE hidden != "1"
            ORDER BY local_files DESC, 
                     files DESC, 
                     name ASC
            LIMIT 500'
        );

        if (!is_array($cats)) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $cats = [];
        }

        /** @noinspection PhpIncludeInspection */
        include($this->getView('AdminTopic'));

        $this->smt->includeFooter();
    }
}
