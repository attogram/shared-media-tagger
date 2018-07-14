<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

/**
 * Class AdminSite
 */
class AdminSite extends ControllerBase
{
    protected function display()
    {
        header('X-XSS-Protection:0');
        if (isset($_POST) && $_POST) {
            $this->smt->saveSiteInfo();
        }
        $site = $this->smt->database->getSite();
        $site['id'] = !empty($site['id']) ? (int) $site['id'] : 1;
        $site['name'] = !empty($site['name']) ? htmlentities((string) $site['name']) : '';
        $site['about'] = !empty($site['about']) ? htmlentities((string) $site['about']) : '';
        $site['header'] = !empty($site['header']) ? htmlentities((string) $site['header']) : '';
        $site['footer'] = !empty($site['footer']) ? htmlentities((string) $site['footer']) : '';
        $site['curation'] = !empty($site['curation']) ? $site['curation'] :false;
        $site['updated'] = !empty($site['updated']) ? htmlentities((string) $site['updated']) : '';
        $this->smt->title = 'Site Admin';
        $this->smt->includeHeader();
        $this->smt->includeTemplate('Menu');
        $this->smt->includeTemplate('AdminMenu');
        /** @noinspection PhpIncludeInspection */
        include($this->getView('AdminSite'));
        $this->smt->includeFooter();
    }
}
