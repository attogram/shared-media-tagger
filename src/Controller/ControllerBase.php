<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tagger;
use Attogram\SharedMedia\Tagger\TaggerAdmin;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class Base
 */
class ControllerBase
{
    /** @var Tagger|TaggerAdmin */
    public $smt;

    /**
     * Base constructor.
     * @param Tagger|TaggerAdmin $smt
     */
    public function __construct(Tagger $smt)
    {
        $this->smt = $smt;
        $this->display();
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getView($name)
    {
        $view = Config::$sourceDirectory . '/View/' . $name . '.php';
        if (!is_readable($view)) {
            Tools::error404('Page View Not Found');
        }

        return $view;
    }

    /**
     * display
     */
    protected function display()
    {
        //
    }
}
