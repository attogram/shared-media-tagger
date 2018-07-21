<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class AdminMedia
 */
class AdminMedia extends ControllerBase
{
    protected function display()
    {
        $this->smt->title = 'Media Admin';
        $this->smt->includeHeader();
        $this->smt->includeTemplate('Menu');
        $this->smt->includeTemplate('AdminMenu');

        $data = [];
        $data['result'] = '';

        if (!empty($_GET['am'])) {
            $data['result'] = $this->smt->addMedia($_GET['am']);
            $this->smt->database->updateTopicsLocalFilesCount();
        }
        if (!empty($_GET['media'])) {
            $data['result'] = $this->multiDeleteMedia($_GET['media']);
            $this->smt->database->updateTopicsLocalFilesCount();
        }
        if (!empty($_GET['dm'])) {
            $data['result'] = $this->smt->database->deleteMedia($_GET['dm']);
            $this->smt->database->updateTopicsLocalFilesCount();
        }
        if (!empty($_GET['dc'])) {
            $data['result'] = $this->deleteMediaInTopic(Tools::topicUrldecode($_GET['dc']));
            $this->smt->database->updateTopicsLocalFilesCount();
        }

        /** @noinspection PhpIncludeInspection */
        include($this->getView('AdminMedia'));

        $this->smt->includeFooter();
    }

    /**
     * @param $list
     * @return bool|string
     */
    public function multiDeleteMedia($list)
    {
        if (!is_array($list)) {
            Tools::error('multi_delete_media: No list array found');
            return false;
        }
        $response = '<br />Deleting &amp; Blocking ' . sizeof($list) . ' Media files:';
        foreach ($list as $mediaId) {
            $response .= $this->smt->database->deleteMedia($mediaId);
        }

        return $response;
    }

    /**
     * @param string $topic_name
     * @return string
     */
    public function deleteMediaInTopic($topic_name)
    {
        if (!$topic_name || !is_string($topic_name)) {
            return 'Invalid Topic Name';
        }
        $return = '<br />Deleting Media in <b>' . $topic_name . '</b>';
        $media = $this->smt->database->getMediaInTopic($topic_name);
        $return .= '<br /><b>' . count($media) . '</b> Media files found in Topic';
        foreach ($media as $pageid) {
            $return .= '<br />Deleting #' . $pageid;
            $return .= $this->smt->database->deleteMedia($pageid, true);
        }
        $return .= '<br />';

        return $return;
    }
}
