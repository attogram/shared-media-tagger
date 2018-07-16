<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger;

use Attogram\Router\Router;
use Attogram\SharedMedia\Tagger\Database\DatabaseAdmin;

/**
 * Class TaggerAdmin
 */
class TaggerAdmin extends Tagger
{
    /** @var Commons */
    public $commons;
    /** @var DatabaseAdmin */
    public $database;

    /**
     * TaggerAdmin constructor.
     * @param Router $router
     * @param array $config
     */
    public function __construct(Router $router, array $config = [])
    {
        parent::__construct($router, $config);



        if (empty($_SESSION['user'])) {
            header('Location: ' . Tools::url('login'));
        }

        ini_set('user_agent', 'Shared Media Tagger v' . SHARED_MEDIA_TAGGER);
        $this->commons = new Commons();
        $this->database = new DatabaseAdmin();
        $this->database->getUser();
        $this->commons->setDatabase($this->database);
        $this->database->setCommons($this->commons);
    }

    /**
     * @return string
     */
    public function checkRobotstxt()
    {
        $robotstxt = (
            !empty($_SERVER['DOCUMENT_ROOT'])
                ? $_SERVER['DOCUMENT_ROOT']
                : ''
            ) . '/robots.txt';
        $tagUrl = str_replace('//'.Config::$server, '', Tools::url('tag'));
        $sitemapUrl = Config::$protocol . '//' . Config::$server . Tools::url('sitemap');
        $response = $robotstxt;
        if (!file_exists($robotstxt)) {
            return '<br />❌file not found: ' . $robotstxt
            . '<br />❌rule not found: user-agent: *'
            . '<br />❌rule not found: disallow: ' . $tagUrl
            . '<br />❌rule not found: sitemap: ' . $sitemapUrl
            ;
        }
        $response .= '<br />✅️exists';
        $content = file($robotstxt);
        if (!is_array($content)) {
            return $response . ''
            . '<br />❌rule not found: user-agent: *'
            . '<br />❌rule not found: disallow: ' . $tagUrl
            . '<br />❌rule not found: sitemap: ' . $sitemapUrl
            ;
        }

        $userAgentStar = false;
        $tagDisallow = false;
        $sitemap = false;

        foreach ($content as $line) {
            if (strtolower(trim($line)) == 'sitemap: ' . $sitemapUrl) {
                $sitemap = true;
                $response .= '<br />✅rule ok: sitemap: ' . $sitemapUrl;
                continue;
            }
            if (strtolower(trim($line)) == 'user-agent: *') {
                $userAgentStar = true;
                $response .= '<br />✅rule ok: user-agent: *';
                continue;
            }
            if (!$userAgentStar) {
                continue;
            }
            if (strtolower(trim($line)) == 'disallow: ' . $tagUrl) {
                $tagDisallow = true;
                $response .= '<br />✅rule ok: disallow: ' . $tagUrl;
                continue;
            }
        }
        if (!$sitemap) {
            $response .= '<br />❌rule not found: sitemap: ' . $sitemapUrl;
        }
        if (!$userAgentStar) {
            $response .= '<br />❌rule not found: user-agent: *';
        }
        if (!$tagDisallow) {
            $response .= '<br />❌rule not found: disallow: ' . $tagUrl;
        }

        return $response;
    }

    /**
     * @param $pageid
     * @return bool|string
     */
    public function addMedia($pageid)
    {
        if (!$pageid || !Tools::isPositiveNumber($pageid)) {
            Tools::error('add_media: Invalid PageID');

            return false;
        }

        $response = '<div style="background-color:lightgreen; padding:10px;">'
        . '<p>Add Media: pageid: <b>' . $pageid . '</b></p>';

        // Get media info from API
        $media = $this->commons->getApiImageinfo([$pageid], 0);
        if (!$media) {
            return $response . '<p>ERROR: failed to get media info from API</p></div>';
        }
        $response .= '<p>OK: media: <b>'
            . !empty($media[$pageid]['title']) ? $media[$pageid]['title'] : '?'
            . '</b></p>';

        // Save media
        if (!$this->database->saveMediaToDatabase($media)) {
            return $response . '<p>ERROR: failed to save media to database</p></div>';
        }
        $response .= '<p>OK: Saved media: <b><a href="' . Tools::url('info')
        . '/' . $pageid . '">info/' . $pageid . '</a></b></p>';

        if (!$this->commons->categories) {
            return $response . '<p>No Categories Found</p></div>';
        }
        foreach ($this->commons->categories as $category) {
            $response .= '+'
            . '<a href="' . Tools::url('category')
            . '/' . Tools::categoryUrlencode(Tools::stripPrefix($category['title']))
            . '">' . Tools::stripPrefix($category['title']) . '</a><br />';
        }
        $response .= '</div>';

        return $response;
    }

    /**
     * @param $categoryNameArray
     */
    public function importCategories($categoryNameArray)
    {
        Tools::notice("import_categories( category_name_array )");
        $this->database->beginTransaction();
        foreach ($categoryNameArray as $categoryName) {
            $categoryName = Tools::categoryUrldecode($categoryName);
            $this->database->insertCategory($categoryName);
        }
        $this->database->commit();
        $this->database->vacuum();
    }

    /**
     * getSearchResults
     */
    public function getSearchResults()
    {
        $search = urldecode($_GET['scommons']);

        if (!$this->commons->findCategories($search)) {
            Tools::notice('Error: no categories found');

            return;
        }
        $cats = isset($this->commons->response['query']['search'])
            ? $this->commons->response['query']['search']
            : null;
        if (!$cats || !is_array($cats)) {
            Tools::notice('Error: no categories returned');

            return;
        }
        print '<p>Searched "' . $search . '": showing <b>' . sizeof($cats) . '</b> of <b>'
            . $this->commons->totalHits . '</b> categories</p>';
        print '
    <script type="text/javascript" language="javascript">// <![CDATA[
    function checkAll(formname, checktoggle)
    {
      var checkboxes = new Array();
      checkboxes = document[formname].getElementsByTagName(\'input\');
      for (var i=0; i<checkboxes.length; i++)  {
        if (checkboxes[i].type == \'checkbox\')   {
          checkboxes[i].checked = checktoggle;
        }
      }
    }
    // ]]></script>
    <a onclick="javascript:checkAll(\'cats\', true);" href="javascript:void();">check all</a>
    <a onclick="javascript:checkAll(\'cats\', false);" href="javascript:void();">uncheck all</a>
    ';

        print '<form action="" name="cats" method="POST">'
            . '<input type="submit" value="  save to database  "><br /><br />';

        foreach ($cats as $id => $cat) {
            print '<input type="checkbox" name="cats[]" value="' . urlencode($cat['title']) . '"><strong>'
                . $cat['title']
                . '</strong><small> '
                . '<a target="c" href="https://commons.wikimedia.org/wiki/'
                . Tools::categoryUrlencode($cat['title']) . '">(view)</a> '
                . ' (' . $cat['snippet'] . ')'
                . ' (size:' . $cat['size'] . ')</small><br />';
        }
        print '</form>';
    }

    /**
     * @return bool
     */
    public function saveTag()
    {
        $sql = 'UPDATE tag
                SET position = :position,
                    score = :score,
                    name = :name,
                    display_name = :display_name
                WHERE id = :id';
        $bind = [
            ':id' => !empty($_GET['tagid']) ? $_GET['tagid'] : null,
            ':position' => !empty($_GET['position']) ? $_GET['position'] : null,
            ':score' => !empty($_GET['score']) ? $_GET['score'] : null,
            ':name' => !empty($_GET['name']) ? $_GET['name'] : null,
            ':display_name' => !empty($_GET['display_name']) ? $_GET['display_name'] : null,
        ];

        if ($this->database->queryAsBool($sql, $bind)) {
            Tools::notice('OK: Saved Tag ID#'.$_GET['tagid']);

            return true;
        }
        Tools::notice('save_tag: Can Not Save Tag Data.<br />'.$sql.'<br/>  bind: <pre>'
            . print_r($bind, true) . ' </pre>');

        return false;
    }

    /**
     * @return bool
     */
    public function saveSiteInfo()
    {
        $bind = $set = [];
        foreach ($_POST as $name => $value) {
            switch ($name) {
                case 'id':
                    $bind[':id'] = $value;
                    break;
                case 'name':
                case 'about':
                case 'header':
                case 'footer':
                    $set[] = "$name = :$name";
                    $bind[":$name"] = $value;
                    break;
                case 'curation':
                    if ($value == 'on') {
                        $set[] = "curation = '1'";
                    } else {
                        $set[] = "curation = '0'";
                    }
                    break;
            }
        }

        $set[] = "updated = '" . gmdate('Y-m-d H:i:s') . "'";

        $sql = 'UPDATE site SET ' . implode($set, ', ') . ' WHERE id = :id';

        if ($this->database->queryAsBool($sql, $bind)) {
            Tools::notice('OK: Site Info Saved');

            return true;
        }
        Tools::error('Unable to update site: ' . print_r($this->database->lastError, true));

        return false;
    }
}
