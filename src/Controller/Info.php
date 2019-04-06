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
        $pageid = (int) $this->smt->router->getVar(0);
        if (empty($pageid) || !Tools::isPositiveNumber($pageid)) {
            $this->smt->fail404('Media Not Found');
        }

        $media = $this->smt->database->getMedia($pageid);
        if (!$media || !isset($media[0]) || !is_array($media[0])) {
            $this->smt->fail404('Media Not Found');
        }
        $media = $media[0];
        $media['imagedescriptionSafe'] = !empty($media['imagedescription'])
            ? trim(strip_tags($media['imagedescription'])) // @TODO add tag exceptions to strip_tags
            : Tools::stripPrefix($media['title']);


        $rows = 1;
        $rows += substr_count("\n", $media['imagedescriptionSafe']);
        $rows += round(strlen($media['imagedescriptionSafe']) / 70);
        $maxRows = 10;
        if ($rows > $maxRows) {
            $rows = $maxRows;
        }
        $media['imagedescriptionRows'] = $rows;

        if (empty($media['mime'])) {
            $media['mime'] = '';
        }

        $media['displayUrl'] = $media['thumburl'];

        $height = $media['thumbheight'];
        $width = $media['thumbwidth'];

        $aspectRatio = 1;
        if ($width && $height) {
            $aspectRatio = $width / $height;
        }
        if ($aspectRatio < 1) { // Tall media
            $width = round($aspectRatio * 100);
        }
        if ($width > 100) {
            $width = 100;
        }
        $media['displayStyle'] = 'height:100%; width:' . $width . '%;';

        // Licensing
        $fix = [
            'Public domain' => 'Public Domain',
            'CC-BY-SA-3.0' => 'CC BY-SA 3.0'
        ];
        foreach ($fix as $bad => $good) {
            if ($media['usageterms'] == $bad) {
                $media['usageterms'] = $good;
            }
            if ($media['licensename'] == $bad) {
                $media['licensename'] = $good;
            }
            if ($media['licenseshortname'] == $bad) {
                $media['licenseshortname'] = $good;
            }
        }
        $lics = [$media['licensename'], $media['licenseshortname'], $media['usageterms']];
        $media['licensing'] = array_unique($lics);

        // Display
        $this->smt->title = 'Info: ' . Tools::stripPrefix($media['title']);
        $this->smt->includeHeader();
        $this->smt->includeTemplate('Menu');
        /** @noinspection PhpIncludeInspection */
        include($this->getView('Info'));
        $this->smt->includeFooter();
    }





}
