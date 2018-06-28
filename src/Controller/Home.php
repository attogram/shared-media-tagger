<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class Home
 */
class Home extends ControllerBase
{
    protected function display()
    {
        $view = $this->getView('Home');
        $this->smt->title = Config::$siteName;

        if (isset($_GET['i']) && Tools::isPositiveNumber($_GET['i'])) {
            $media = $this->smt->database->getMedia($_GET['i']);
        } else {
            $media = $this->smt->database->getRandomMedia(1);
        }
        if (empty($media[0])) {
            $this->smt->includeHeader(true);
            $this->smt->includeMenu();
            Tools::error('No Media Files found... yet');
            $this->smt->includeFooter();
            Tools::shutdown();
        }

        $media = $media[0];

        $data = [];
        $data['tags'] = $this->smt->displayTags($media['pageid']);
        $data['media']  = $this->smt->displayMedia($media);
        $data['width']  = Config::$sizeMedium;
        $data['reviews']  = $this->smt->displayReviews($this->smt->database->getReviews($media['pageid']));
        $data['categories']  = $this->smt->displayCategories($media['pageid']);
        $data['admin'] =  $this->smt->displayAdminMediaFunctions($media['pageid']);

        $this->smt->includeHeader(false);
        $this->smt->includeSmallMenu();
        /** @noinspection PhpIncludeInspection */
        include($view);
        $this->smt->includeFooter();
    }
}
