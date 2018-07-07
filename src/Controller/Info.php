<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class Info
 */
class Info extends ControllerBase
{
    protected function display()
    {


        $vars = $this->smt->router->getVars();
        if (empty($vars[0]) || !Tools::isPositiveNumber($vars[0])) {
            $this->smt->fail404('404 Media Request Not Found');
        }

        $pageid = (int) $vars[0];

        $media = $this->smt->database->getMedia($pageid);
        if (!$media || !isset($media[0]) || !is_array($media[0])) {
            $this->smt->fail404('404 Media Not Found');
        }
        $media = $media[0];
        $media['imagedescriptionSafe'] = !empty($media['imagedescription'])
            ? trim(strip_tags($media['imagedescription']))
            : Tools::stripPrefix($media['title']);


        $rows = 1;
        $rows += substr_count("\n", $media['imagedescriptionSafe']);
        $rows += round(strlen($media['imagedescriptionSafe']) / 70);
        $maxRows = 10;
        if ($rows > $maxRows) {
            $rows = $maxRows;
        }
        $media['imagedescriptionRows'] = $rows;


        $data = [];


        $this->smt->title = 'Info: ' . Tools::stripPrefix($media['title']);
        $this->smt->useBootstrap = true;
        $this->smt->useJquery = true;
        $this->smt->includeHeader();
        $this->smt->includeTemplate('MenuSmall');

        /** @noinspection PhpIncludeInspection */
        include($this->getView('Info'));

        $this->smt->includeFooter();
    }
}
