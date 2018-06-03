<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class About
 */
class Contact extends ControllerBase
{
    protected function display()
    {
        $view = $this->getView('Contact');

        $data = [];

        if (isset($_POST['c'])) {
            $data['result'] = $this->saveMessage();
        }

        $data['headline'] = '<p>Contact us today!</p>';
        $data['innertext'] = "* My Question or Comment:\n\n\n";
        $data['footer'] = "\n* My Contact information:\n\n";

        if (isset($_GET['r']) && Tools::isPositiveNumber($_GET['r'])) {
            $pageid = (int)$_GET['r'];
            $media = $this->smt->database->getMedia($pageid);
            if (!$media || !isset($media[0])) {
                Tools::notice('ERROR: no media ID #' . $pageid . ' found.');
            }
            $media = $media[0];
            $data['headline'] = '<p>REPORT file #' . $pageid . '<br />'
                . $this->smt->displayThumbnail($media) . '</p>';
            $data['innertext'] = "* REPORT file #$pageid:\n* Reason:\n\n\n";
        }

        $this->smt->title = 'Contact - ' . Config::$siteName;
        $this->smt->includeHeader();
        $this->smt->includeMediumMenu();

        /** @noinspection PhpIncludeInspection */
        include($view);

        $this->smt->includeFooter();
    }

    /**
     * @return string
     */
    private function saveMessage()
    {
        $comment = urldecode($_POST['c']);
        $insert = $this->smt->database->queryAsBool(
            'INSERT INTO contact (comment, datetime, ip) VALUES (:comment, CURRENT_TIMESTAMP, :ip)',
            [
                ':comment' => $comment,
                ':ip' => !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
            ]
        );
        if ($insert) {
            return '<p>Thank you for your message.  You sent the following:</p>'
                . '<pre style="background-color:lightsalmon;">' . htmlentities((string) $comment) . '</pre>';
        }
        return '<p>Error accessing database.  Try again later.</p>'
            . print_r($this->smt->database->lastError);
    }
}
