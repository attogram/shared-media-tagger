<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class About
 */
class About extends ControllerBase
{
    protected function display()
    {
        $view = $this->getView('About');

        $site = $this->smt->database->getSite();

        $site['about'] = !empty($site['about']) ? $site['about'] : 'Welcome';
        $site['name'] = !empty($site['name']) ? $site['name'] : 'Shared Media Tagger';
        $site['urlHome'] = Tools::url('home');
        $site['urlCategories'] = Tools::url('categories');
        $site['urlReviews'] = Tools::url('reviews');
        $site['urlUsers'] = Tools::url('users');
        $site['urlContact'] =  Tools::url('contact');
        $sites['tags'] = '';
        foreach ($this->smt->database->getTags() as $tag) {
            $sites['tags'] .= $tag['name'] . ', ';
        }

        $this->smt->title = 'About ' . Config::$siteName;
        $this->smt->includeHeader();
        $this->smt->includeMenu();
        /** @noinspection PhpIncludeInspection */
        include($view);
        $this->smt->includeFooter();
    }
}
