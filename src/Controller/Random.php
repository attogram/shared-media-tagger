<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class Random
 */
class Random extends ControllerBase
{
    protected function display()
    {
        $random = $this->smt->database->getRandomMedia();
        $location = './';
        if (isset($random[0]['pageid'])) {
            $location = Tools::url('info') . '/' . $random[0]['pageid'];
        }
        header('Location: ' . $location);
        Tools::shutdown();
    }
}
