<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class AdminCreate
 */
class AdminCreate extends ControllerBase
{
    protected function display()
    {
        $view = $this->getView('AdminCreate');

        $this->smt->title = 'Create';
        $this->smt->includeHeader();
        $this->smt->includeMediumMenu();
        $this->smt->includeAdminMenu();

        $numberOfImages = 4;
        $thumbWidth = 50;
        $montageWidth = 100;
        $montageHeight = 100;
        $montageImagesPerRow = 2;
        $montageIndexStep = $thumbWidth;

        $showFooter = false;
        $footerHeight = 0;

        $mimetypes[] = 'image/jpeg';
        $mimetypes[] = 'image/gif';
        $mimetypes[] = 'image/png';

        $tagId = (!empty($_GET['t']) && Tools::isPositiveNumber($_GET['t']))
            ? (int)$_GET['t']
            : 'R';

        if (empty($_GET['montage'])) {
            print '</div>';
            $this->smt->includeFooter();

            return;
        }

        if (!function_exists('imagecreatetruecolor')) {
            Tools::error('PHP GD Library NOT FOUND');
            print '</div>';
            $this->smt->includeFooter();
            Tools::shutdown();
        }
        switch ($tagId) {
            default:
                $sql = '
                    SELECT m.*, t.count
                    FROM  media AS m, tagging AS t, tag as tg
                    WHERE t.media_pageid = m.pageid
                    AND   t.tag_id = :tag_id
                    AND   tg.id = t.tag_id
                    AND   m.thumbmime IN ("' . implode($mimetypes, '", "') . '")
                    AND   m.thumburl LIKE "%325px%"
                    ORDER BY RANDOM()
                    LIMIT ' . $numberOfImages;
                $bind = [':tag_id' => $tagId];
                break;
            case 'R':
                $sql = '
                    SELECT m.*
                    FROM media AS m
                    ORDER BY RANDOM()
                    LIMIT ' . $numberOfImages;
                $bind = [];
                break;
        }

        $images = $this->smt->database->queryAsArray($sql, $bind);
        if (!$images) {
            Tools::error('No images found in criteria');
            print '</div>';
            $this->smt->includeFooter();
            Tools::shutdown();
        }

        $montage = imagecreatetruecolor($montageWidth, $montageHeight + $footerHeight);
        $xIndex = $yIndex = 0;
        foreach ($images as $image) {
            $url = str_replace('325px', $thumbWidth.'px', $image['thumburl']);

            switch ($image['thumbmime']) {
                case 'image/gif':
                    $currentImage = @imagecreatefromgif($url);
                    break;
                case 'image/jpeg':
                    $currentImage = @imagecreatefromjpeg($url);
                    break;
                case 'image/png':
                    $currentImage = @imagecreatefrompng($url);
                    break;
                default:
                    Tools::error('unknown mime type');
                    continue;
            }
            if (!$currentImage) {
                Tools::error('Can not get image: ' . $url);
                continue;
            }
            if (imagesx($currentImage) < $thumbWidth) {
                $currentImage = imagescale(
                    $currentImage,
                    $thumbWidth,
                    imagesy($currentImage)
                );
            }
            if (imagesy($currentImage) < $thumbWidth) {
                $currentImage = imagescale(
                    $currentImage,
                    imagesx($currentImage),
                    $thumbWidth
                );
            }

            imagecopy(
                $montage, // Destination image link resource
                $currentImage, // Source image link resource
                $xIndex * $montageIndexStep, // x-coordinate of destination point
                $yIndex * $montageIndexStep, // y-coordinate of destination point
                0, // x-coordinate of source point
                0, // y-coordinate of source point
                $montageIndexStep, // Source width
                $montageIndexStep  // Source height
            );
            imagedestroy($currentImage);
            $xIndex++;
            if ($xIndex > ($montageImagesPerRow - 1)) {
                $xIndex = 0;
                $yIndex++;
            }
        }

        if ($showFooter) {
            $yellow = imagecolorallocate($montage, 255, 255, 0);
            imagestring(
                $montage,
                4, // font 1-5
                5, // x
                $montageHeight + 6, // y
                $this->smt->site_name,
                $yellow
            );
            imagestring(
                $montage,
                2, // font 1-5
                5, // x
                $montageHeight + 24, // y
                str_replace('//', '', $this->smt->site_url),
                $yellow
            );
        }

        ob_start();
        imagepng($montage);
        $imageData = ob_get_contents();
        ob_end_clean();

        imagedestroy($montage);

        $dataUrl = 'data:image/png;base64,' . base64_encode($imageData);

        /** @noinspection PhpIncludeInspection */
        include($view);

        $this->smt->includeFooter();
    }
}
