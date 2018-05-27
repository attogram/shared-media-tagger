<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger;

/**
 * Class TaggerAdmin
 */
class TaggerAdmin extends Tagger
{
    /** @var Commons */
    public $commons;
    public $tablesCurrent;
    public $sqlCurrent;
    public $sqlNew;

    /** @var DatabaseAdmin */
    public $database;
    public $databaseFile;

    /*
     * TaggerAdmin constructor.
     */
    public function __construct()
    {
        parent::__construct();
        ini_set('user_agent', 'Shared Media Tagger v' . SHARED_MEDIA_TAGGER);
        $this->setAdminCookie();
        $this->commons = new Commons();
        $this->database = new DatabaseAdmin();
        $this->commons->setDatabase($this->database);
        $this->database->setCommons($this->commons);
    }

    /**
     * includeAdminMenu
     */
    public function includeAdminMenu()
    {
        $admin = Tools::url('admin');
        $space = ' &nbsp; &nbsp; ';
        print '<div class="menu admin">'
            . '<a href="' . $admin . '">ADMIN</a>'
            . $space . '<a href="' . $admin . 'site.php">SITE</a>'
            . $space . '<a href="' . $admin . 'tag.php">TAG</a>'
            . $space . '<a href="' . $admin . 'category.php">CATEGORY</a>'
            . $space . '<a href="' . $admin . 'media.php">MEDIA</a>'
            . $space . '<a href="' . $admin . 'curate.php">CURATE</a>'
            . $space . '<a href="' . $admin . 'user.php">USER</a>'
            . $space . '<a href="' . $admin . 'create.php">CREATE</a>'
            . $space . '<a href="' . $admin . 'export.php">EXPORT</a>'
            . $space . '<a href="' . $admin . 'database.php">DATABASE</a>'
            . '</div>';
    }

    /**
     * setAdminCookie
     */
    public function setAdminCookie()
    {
        if (isset($_COOKIE['admin']) && $_COOKIE['admin'] == '1') {
            return;
        }
        setcookie('admin', '1', time()+28800, '/'); // 8 hour admin cookie
    }

    /**
     * @return string
     */
    public function checkRobotstxt()
    {
        $robotstxt = Config::$installDirectory . '/robots.txt';
        $tagUrl = str_replace('//'.Config::$server, '', Tools::url('tag'));
        $sitemapUrl = Config::$protocol . Tools::url('home') . 'sitemap.php';
        $reportUrl = str_replace('//'.Config::$server, '', Tools::url('contact')) . '?r=*';
        $response = $robotstxt;
        if (!file_exists($robotstxt)) {
            return '<br />❌file not found: ' . $robotstxt
            . '<br />❌rule not found: user-agent: *'
            . '<br />❌rule not found: disallow: ' . $tagUrl
            . '<br />❌rule not found: disallow: ' . $reportUrl
            . '<br />❌rule not found: sitemap: ' . $sitemapUrl
            ;
        }
        $response .= '<br />✔️exists';
        $content = file($robotstxt);
        if (!is_array($content)) {
            return $response . ''
            . '<br />❌rule not found: user-agent: *'
            . '<br />❌rule not found: disallow: ' . $tagUrl
            . '<br />❌rule not found: disallow: ' . $reportUrl
            . '<br />❌rule not found: sitemap: ' . $sitemapUrl
            ;
        }

        $userAgentStar = false;
        $tagDisallow = false;
        $sitemap = false;
        $reportDisallow = false;

        foreach ($content as $line) {
            if (strtolower(trim($line)) == 'sitemap: ' . $sitemapUrl) {
                $sitemap = true;
                $response .= '<br />✔️rule ok: sitemap: ' . $sitemapUrl;
                continue;
            }
            if (strtolower(trim($line)) == 'user-agent: *') {
                $userAgentStar = true;
                $response .= '<br />✔️rule ok: user-agent: *';
                continue;
            }
            if (!$userAgentStar) {
                continue;
            }
            if (strtolower(trim($line)) == 'disallow: ' . $tagUrl) {
                $tagDisallow = true;
                $response .= '<br />✔️rule ok: disallow: ' . $tagUrl;
                continue;
            }
            if (strtolower(trim($line)) == 'disallow: ' . $reportUrl) {
                $reportDisallow = true;
                $response .= '<br />✔️rule ok: disallow: ' . $reportUrl;
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
        if (!$reportDisallow) {
            $response .= '<br />❌rule not found: disallow: ' . $reportUrl;
        }

        return $response;
    }

    // Dataabase Create

    /**
     * @return bool
     */
    public function createTables()
    {
        if (!file_exists($this->database->databaseName)) {
            if (!touch($this->database->databaseName)) {
                Tools::error('ERROR: can not touch database name: ' . $this->database->databaseName);

                return false;
            }
        }
        $this->setDatabaseFile($this->database->databaseName);
        $this->setNewStructures(Config::getDatabaseTables());
        $this->update();

        return true;
    }

    /**
     * @param $file
     * @return bool
     */
    public function setDatabaseFile($file)
    {
        $this->databaseFile = $file;
        $this->tablesCurrent = [];
        $this->sqlCurrent = [];
        $this->setTableInfo();

        return $this->database->databaseLoaded();
    }

    /**
     * @param array $tables
     * @return bool
     */
    public function setNewStructures(array $tables = [])
    {
        if (!$tables || !is_array($tables)) {
            Tools::error('$tables array is invalid');

            return false;
        }
        $errors = 0;
        $count = 0;
        foreach ($tables as $tableName => $tableSql) {
            $count++;
            if (!$tableName || !is_string($tableName)) {
                Tools::error("#$count - Invalid table name");
                $errors++;
                continue;
            }
            if (!$tableSql || !is_string($tableSql)) {
                Tools::error("#$count - Invalid table sql");
                $errors++;
                continue;
            }
            $this->setNewStructure($tableName, $tableSql);
        }

        return $errors ? false : true;
    }

    /**
     * @param $tableName
     * @param $sql
     */
    public function setNewStructure($tableName, $sql)
    {
        $sql = $this->normalizeSql($sql);
        $this->sqlNew[$tableName] = $sql;
    }

    /**
     * @return bool
     */
    public function update()
    {
        $toUpdate = [];
        foreach (array_keys($this->sqlNew) as $name) {
            $old = $this->normalizeSql(
                !empty($this->sqlCurrent[$name]) ? $this->sqlCurrent[$name] : ''
            );
            $new = $this->normalizeSql(
                !empty($this->sqlNew[$name]) ? $this->sqlNew[$name] : ''
            );
            if ($old == $new) {
                continue;
            }
            $toUpdate[] = $name;
        }
        if (!$toUpdate) {
            Tools::notice('OK: ' . sizeof($this->sqlNew) . ' tables up-to-date');

            return true;
        }
        Tools::notice(sizeof($toUpdate) . ' tables to update: ' . implode($toUpdate, ', '));
        foreach ($toUpdate as $tableName) {
            $this->updateTable($tableName);
        }

        return true;
    }

    /**
     * @param $tableName
     * @return bool
     */
    protected function updateTable($tableName)
    {
        $tmpName = '_STSU_TMP_' . $tableName;
        $backupName = '_STSU_BACKUP_' . $tableName;
        $this->database->queryAsBool("DROP TABLE IF EXISTS '$tmpName'");
        $this->database->queryAsBool("DROP TABLE IF EXISTS '$backupName'");
        $this->database->beginTransaction();
        $sql = $this->sqlNew[$tableName];
        $sql = str_ireplace("CREATE TABLE '$tableName'", "CREATE TABLE '$tmpName'", $sql);
        if (!$this->database->queryAsBool($sql)) {
            Tools::error('ERROR: can not create tmp table:<br />' . $sql);
            return false;
        }
        // Get Columns of new table
        $this->setTableColumnInfo($tmpName);
        $newCols = $this->tablesCurrent[$tmpName];
        // Only use Columns both in new and old tables
        $cols =[];
        foreach ($newCols as $newCol) {
            if (isset($this->tablesCurrent[$tableName][$newCol['name']])) {
                $cols[] = $newCol['name'];
            }
        }
        if (!$cols) {
            $newSize = 0;
        } else {
            $oldSize = $this->getTableSize($tableName);
            $cols = implode($cols, ', ');
            $sql = "INSERT INTO '$tmpName' ( $cols ) SELECT $cols FROM $tableName";
            if (!$this->database->queryAsBool($sql)) {
                Tools::error('ERROR: can not insert into tmp table: ' . $tmpName
                . '<br />' . $sql);
                return false;
            }
            $newSize = $this->getTableSize($tmpName);
            if ($newSize == $oldSize) {
            } else {
                Tools::error("ERROR: Inserted new $newSize rows, from $oldSize old rows");
            }
            if (!$this->database->queryAsBool("ALTER TABLE $tableName RENAME TO $backupName")) {
                Tools::error('ERROR: can not rename '.$tableName.' to '.$backupName);

                return false;
            }
        }
        if (!$this->database->queryAsBool("ALTER TABLE $tmpName RENAME TO $tableName")) {
            Tools::error('ERROR: can not rename ' . $tmpName . ' to ' . $backupName);

            return false;
        }
        $this->database->commit();
        Tools::notice('OK: Table Structure Updated: ' . $tableName . ': +' . number_format((float) $newSize) . ' rows');
        $this->database->queryAsBool("DROP TABLE IF EXISTS '$tmpName'");
        $this->database->queryAsBool("DROP TABLE IF EXISTS '$backupName'");
        $this->database->vacuum();

        return true;
    }

    /**
     * setTableInfo
     */
    protected function setTableInfo()
    {
        $tables = $this->database->queryAsArray("SELECT name, sql FROM sqlite_master WHERE type = 'table'");
        foreach ($tables as $table) {
            if (preg_match('/^_STSU_/', $table['name'])) {
                continue; // tmp and backup tables
            }
            $this->sqlCurrent[$table['name']] = $this->normalizeSql($table['sql']);
            $this->setTableColumnInfo($table['name']);
        }
    }

    /**
     * @param $tableName
     */
    protected function setTableColumnInfo($tableName)
    {
        $columns = $this->database->queryAsArray("PRAGMA table_info($tableName)");
        foreach ($columns as $column) {
            $this->tablesCurrent[$tableName][$column['name']] = $column;
        }
    }

    /**
     * @param $sql
     * @return string
     */
    protected function normalizeSql($sql)
    {
        $sql = preg_replace('/\s+/', ' ', $sql); // remove all excessive spaces and control chars
        $sql = str_replace('"', "'", $sql); // use only single quote '
        $sql = str_ireplace('CREATE TABLE IF NOT EXISTS', 'CREATE TABLE', $sql); // standard create syntax

        return trim($sql);
    }

    /**
     * @param $tableName
     * @return int
     */
    protected function getTableSize($tableName)
    {
        $size = $this->database->queryAsArray('SELECT count(rowid) AS count FROM ' . $tableName);
        if (isset($size[0]['count'])) {
            return $size[0]['count'];
        }
        Tools::error('Can not get table size: ' . $tableName);

        return 0;
    }

    // Media

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
        . '?i=' . $pageid . '">info.php?i=' . $pageid . '</a></b></p>';

        if (!$this->commons->categories) {
            return $response . '<p>No Categories Found</p></div>';
        }
        foreach ($this->commons->categories as $category) {
            $response .= '+'
            . '<a href="' . Tools::url('category')
            . '?c=' . Tools::categoryUrlencode(Tools::stripPrefix($category['title']))
            . '">' . Tools::stripPrefix($category['title']) . '</a><br />';
        }
        $response .= '</div>';

        return $response;
    }

    // Category

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
}
