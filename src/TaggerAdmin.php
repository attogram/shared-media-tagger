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

    protected $tablesCurrent;
    protected $sqlCurrent;
    protected $sqlNew;

    public $categories;
    public $categoryId;
    public $databaseFile;

    /**
     * SharedMediaTaggerAdmin constructor.
     */
    public function __construct()
    {
        parent::__construct();
        ini_set('user_agent', 'Shared Media Tagger v' . __SMT__);
        $this->setAdminCookie();
        $this->commons = new Commons();
    }

    // SMT Admin - Utils

    /**
     *
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
        $tagUrl = str_replace('//'.Config::$server, '', $this->url('tag'));
        $sitemapUrl = Config::$protocol . $this->url('home') . 'sitemap.php';
        $reportUrl = str_replace('//'.Config::$server, '', $this->url('contact')) . '?r=*';
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

    // SMT Admin - Database Tables

    // SQLiteTableStructureUpdater

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
     *
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

    // SMT Admin - Database Utils

    /**
     * @return array
     */
    public function emptyTaggingTables()
    {
        $sqls = [
            'DELETE FROM tagging',
            'DELETE FROM user_tagging',
        ];
        $response = [];
        foreach ($sqls as $sql) {
            if ($this->database->queryAsBool($sql)) {
                $response[] = 'OK: ' . $sql;
            } else {
                $response[] = 'FAIL: ' . $sql;
            }
        }
        $this->database->vacuum();

        return $response;
    }

    /**
     * @return array
     */
    public function emptyUserTables()
    {
        $sqls = [
            'DELETE FROM user',
            'DELETE FROM tagging',
            'DELETE FROM user_tagging',
        ];
        $response = [];
        foreach ($sqls as $sql) {
            if ($this->database->queryAsBool($sql)) {
                $response[] = 'OK: ' . $sql;
            } else {
                $response[] = 'FAIL: ' . $sql;
            }
        }
        $this->database->vacuum();

        return $response;
    }

    /**
     * @return bool|string
     */
    public function dropTables()
    {
        $sqls = [
            'DROP TABLE IF EXISTS block',
            'DROP TABLE IF EXISTS category',
            'DROP TABLE IF EXISTS category2media',
            'DROP TABLE IF EXISTS contact',
            'DROP TABLE IF EXISTS media',
            'DROP TABLE IF EXISTS site',
            'DROP TABLE IF EXISTS tag',
            'DROP TABLE IF EXISTS tagging',
            'DROP TABLE IF EXISTS user',
            'DROP TABLE IF EXISTS user_tagging',
            'DROP TABLE IF EXISTS network',
            'DROP TABLE IF EXISTS network_site',
        ];
        $response = false;
        foreach ($sqls as $id => $sql) {
            if ($this->database->queryAsBool($sql)) {
                $response .= "<b>OK:</b> $sql<br />";
            } else {
                $response .= "<b>FAIL:<b/> $sql<br />";
            }
        }
        $this->database->vacuum();

        return $response;
    }

    // SMT Admin - Media

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
        if (!$this->saveMediaToDatabase($media)) {
            return $response . '<p>ERROR: failed to save media to database</p></div>';
        }
        $response .= '<p>OK: Saved media: <b><a href="' . $this->url('info')
        . '?i=' . $pageid . '">info.php?i=' . $pageid . '</a></b></p>';

        if (!$this->categories) {
            return $response . '<p>No Categories Found</p></div>';
        }
        foreach ($this->categories as $category) {
            $response .= '+'
            . '<a href="' . $this->url('category')
            . '?c=' . $this->categoryUrlencode($this->stripPrefix($category['title']))
            . '">' . $this->stripPrefix($category['title']) . '</a><br />';
        }
        $response .= '</div>';

        return $response;
    }

    /**
     * @param array $media
     * @return bool
     */
    public function saveMediaToDatabase($media = [])
    {
        if (!$media || !is_array($media)) {
            Tools::error('::save_media_to_database: no media array');
            return false;
        }

        $errors = [];

        $this->database->beginTransaction();

        foreach ($media as $id => $mediaFile) {
            $new = [];
            $new[':pageid'] = @$mediaFile['pageid'];
            $new[':title'] = @$mediaFile['title'];

            $new[':url'] = @$mediaFile['imageinfo'][0]['url'];
            if (!isset($new[':url']) || $new[':url'] == '') {
                Tools::error('::save_media_to_database: ERROR: NO URL: SKIPPING: pageid='
                    . @$new[':pageid'] . ' title=' . @$new[':title']);
                $errors[ $new[':pageid'] ] = $new[':title'];
                continue;
            }

            $new[':descriptionurl'] = @$mediaFile['imageinfo'][0]['descriptionurl'];
            $new[':descriptionshorturl'] = @$mediaFile['imageinfo'][0]['descriptionshorturl'];

            $new[':imagedescription'] = @$mediaFile['imageinfo'][0]['extmetadata']['ImageDescription']['value'];
            $new[':artist'] = @$mediaFile['imageinfo'][0]['extmetadata']['Artist']['value'];
            $new[':datetimeoriginal'] = @$mediaFile['imageinfo'][0]['extmetadata']['DateTimeOriginal']['value'];
            $new[':licenseshortname'] = @$mediaFile['imageinfo'][0]['extmetadata']['LicenseShortName']['value'];
            $new[':usageterms'] = @$mediaFile['imageinfo'][0]['extmetadata']['UsageTerms']['value'];
            $new[':attributionrequired'] = @$mediaFile['imageinfo'][0]['extmetadata']['AttributionRequired']['value'];
            $new[':restrictions'] = @$mediaFile['imageinfo'][0]['extmetadata']['Restrictions']['value'];

            $new[':licenseuri'] = @$this->openContentLicenseUri($new[':licenseshortname']);
            $new[':licensename'] = @$this->openContentLicenseName($new[':licenseuri']);

            $new[':size'] = @$mediaFile['imageinfo'][0]['size'];
            $new[':width'] = @$mediaFile['imageinfo'][0]['width'];
            $new[':height'] = @$mediaFile['imageinfo'][0]['height'];
            $new[':sha1'] = @$mediaFile['imageinfo'][0]['sha1'];
            $new[':mime'] = @$mediaFile['imageinfo'][0]['mime'];

            $new[':thumburl'] = @$mediaFile['imageinfo'][0]['thumburl'];
            $new[':thumbwidth'] = @$mediaFile['imageinfo'][0]['thumbwidth'];
            $new[':thumbheight'] = @$mediaFile['imageinfo'][0]['thumbheight'];
            $new[':thumbmime'] = @$mediaFile['imageinfo'][0]['thumbmime'];

            $new[':user'] = @$mediaFile['imageinfo'][0]['user'];
            $new[':userid'] = @$mediaFile['imageinfo'][0]['userid'];

            $new[':duration'] = @$mediaFile['imageinfo'][0]['duration'];
            $new[':timestamp'] = @$mediaFile['imageinfo'][0]['timestamp'];

            $sql = "INSERT OR REPLACE INTO media (
                        pageid, title, url,
                        descriptionurl, descriptionshorturl, imagedescription,
                        artist, datetimeoriginal,
                        licenseuri, licensename, licenseshortname, usageterms, attributionrequired, restrictions,
                        size, width, height, sha1, mime,
                        thumburl, thumbwidth, thumbheight, thumbmime,
                        user, userid, duration, timestamp
                    ) VALUES (
                        :pageid, :title, :url,
                        :descriptionurl, :descriptionshorturl, :imagedescription,
                        :artist, :datetimeoriginal,
                        :licenseuri, :licensename, :licenseshortname, :usageterms, :attributionrequired, :restrictions,
                        :size, :width, :height, :sha1, :mime,
                        :thumburl, :thumbwidth, :thumbheight, :thumbmime,
                        :user, :userid, :duration, :timestamp
                    )";

            $response = $this->database->queryAsBool($sql, $new);

            if ($response === false) {
                Tools::error('::save_media_to_database: STOPPING IMPORT');
                Tools::error('::save_media_to_database: FAILED insert into media table');
                return false;
            }

            Tools::notice('SAVED MEDIA: ' . $new[':pageid'] . ' = <a href="' . $this->url('info')
            . '?i=' . $new[':pageid'] . '">' . $this->stripPrefix($new[':title']) . '</a>');

            if (!$this->linkMediaCategories($new[':pageid'])) {
                Tools::error('::: FAILED to link media categories - p:' . $new[':pageid']);
            }
            //Tools::notice('::: LINKED ' . sizeof($this->categories) . ' categories');
        } // end while each media

        $this->database->commit();
        $this->database->vacuum();

        //Tools::notice('END of save_media_to_database: ' . sizeof($media) . ' files');
        if ($errors) {
            Tools::error($errors);
        }
        return true;
    }

    /**
     * @param string $category
     * @return bool
     */
    public function getMediaFromCategory($category = '')
    {
        $category = trim($category);
        if (!$category) {
            return false;
        }
        $category = ucfirst($category);
        if (!preg_match('/^[Category:]/i', $category)) {
            $category = 'Category:' . $category;
        }

        $categorymembers = $this->commons->getApiCategorymembers($category);
        if (!$categorymembers) {
            Tools::error('::getMediaFromCategory: No Media Found');

            return false;
        }

        $blocked = $this->database->queryAsArray(
            'SELECT pageid FROM block WHERE pageid IN ('
                . implode($categorymembers, ',')
            . ')'
        );
        if ($blocked) {
            Tools::error('ERROR: ' . sizeof($blocked) . ' BLOCKED MEDIA FILES');
            foreach ($blocked as $bpageid) {
                if (($key = array_search($bpageid['pageid'], $categorymembers)) !== false) {
                    unset($categorymembers[$key]);
                }
            }
        }

        $chunks = array_chunk($categorymembers, 50);
        foreach ($chunks as $chunk) {
            $this->saveMediaToDatabase($this->commons->getApiImageinfo($chunk));
        }
        $this->updateCategoryLocalFilesCount($category);
        $this->saveCategoryInfo($category);

        return true;
    }

    /**
     * @param $pageid
     * @param bool $noBlock
     * @return bool|string
     */
    public function deleteMedia($pageid, $noBlock = false)
    {
        if (!$pageid || !Tools::isPositiveNumber($pageid)) {
            Tools::error('delete_media: Invalid PageID');
            return false;
        }
        $response = '<div style="white-space:nowrap;font-family:monospace;color:black;background-color:lightsalmon;">'
        . 'Deleting Media :pageid = ' . $pageid;

        $media = $this->getMedia($pageid);
        if (!$media) {
            $response .= '<p>Media Not Found</p></div>';
            return $response;
        }

        $sqls = [];
        $sqls[] = 'DELETE FROM media WHERE pageid = :pageid';
        $sqls[] = 'DELETE FROM category2media WHERE media_pageid = :pageid';
        $sqls[] = 'DELETE FROM tagging WHERE media_pageid = :pageid';
        $sqls[] = 'DELETE FROM user_tagging WHERE media_pageid = :pageid';
        $bind = [':pageid' => $pageid];
        foreach ($sqls as $sql) {
            if ($this->database->queryAsBool($sql, $bind)) {
                //$response .= '<br />OK: ' . $sql;
            } else {
                $response .= '<br />ERROR: ' . $sql;
            }
        }

        if ($noBlock) {
            return $response . '</div>';
        }

        $sql = 'INSERT INTO block (pageid, title, thumb) VALUES (:pageid, :title, :thumb)';
        $bind = [
            ':pageid' => $pageid,
            ':title' => !empty($media[0]['title']) ? $media[0]['title'] : null,
            ':thumb' => !empty($media[0]['thumburl']) ? $media[0]['thumburl'] : null,
        ];
        if ($this->database->queryAsBool($sql, $bind)) {
            //$response .= '<br />OK: ' . $sql;
        } else {
            $response .= '<br />ERROR: ' . $sql;
        }

        return $response . '</div>';
    }

    /**
     * @return array
     */
    public function emptyMediaTables()
    {
        $sqls = [
            'DELETE FROM tagging',
            'DELETE FROM user_tagging',
            'DELETE FROM category2media',
            'DELETE FROM media',
            'DELETE FROM block',
        ];
        $response = [];
        foreach ($sqls as $sql) {
            if ($this->database->queryAsBool($sql)) {
                $response[] = 'OK: ' . $sql;
            } else {
                $response[] = 'FAIL: ' . $sql;
            }
        }
        $this->database->vacuum();
        return $response;
    }

    /**
     * @param $uri
     * @return string
     */
    public function openContentLicenseName($uri)
    {
        // modified from: https://github.com/gbv/image-attribution - MIT License
        if ($uri == 'http://creativecommons.org/publicdomain/zero/1.0/') {
            return "CC0";
        } elseif ($uri == 'https://creativecommons.org/publicdomain/mark/1.0/') {
            return "Public Domain";
        } elseif (preg_match(
            '/^http:\/\/creativecommons.org\/licenses\/(((by|sa)-?)+)\/([0-9.]+)\/(([a-z]+)\/)?/',
            $uri,
            $match
        )
        ) {
            $license = "CC ".strtoupper($match[1])." ".$match[4];
            if (isset($match[6])) {
                $license .= " ".$match[6];
            }
            return $license;
        } else {
            return '';
        }
    }

    /**
     * @param $license
     * @return string
     */
    public function openContentLicenseUri($license)
    {
        // modified from: https://github.com/gbv/image-attribution - MIT License
        $license = strtolower(trim($license));

        if (preg_match('/^(cc0|cc[ -]zero)$/', $license)) {
            return 'http://creativecommons.org/publicdomain/zero/1.0/'; // CC Zero
        } elseif (preg_match('/^(cc )?(pd|pdm|public[ -]domain)( mark( 1\.0)?)?$/', $license)) {
            return 'https://creativecommons.org/publicdomain/mark/1.0/'; // Public Domain
        } elseif ($license == "no restrictions") {
            // No restrictions (for instance images imported from Flickr Commons)
            return 'https://creativecommons.org/publicdomain/mark/1.0/';
        } elseif (preg_match('/^cc([ -]by)?([ -]sa)?([ -]([1-4]\.0|2\.5))([ -]([a-z][a-z]))?$/', $license, $match)) {
            // CC licenses.
            // see <https://wiki.creativecommons.org/wiki/License_Versions>
            // See <https://wiki.creativecommons.org/wiki/Jurisdiction_Database>
            $byline = $match[1] ? 'by' : '';
            $sharealike = $match[2] ? 'sa' : '';
            $port = isset($match[6]) ? $match[6] : '';
            $version = $match[4];

            // just "CC" is not enough
            if (!($byline or $sharealike) or !$version) {
                return '';
            }

            // only 1.0 had pure SA-license without BY
            if ($version == "1.0" && !$byline) {
                $condition = "sa";
            } else {
                $condition = $sharealike ? "by-sa" : "by";
            }

            // ported versions only existed in 2.0, 2.5, and 3.0
            if ($port) {
                if ($version == "1.0" or $version == "4.0") {
                    return '';
                }
                # TODO: check whether port actually exists at given version, for instance 2.5 had less ports!
            }

            // build URI
            $uri = "http://creativecommons.org/licenses/$condition/$version/";
            if ($port) {
                $uri .= "$port/";
            }

            return $uri;
        } else {
            // TODO: GFLD and other licenses
            return '';
        }
    }

    // smt_admin_category

    /**
     * @param $pageid
     * @return bool
     */
    public function getCategoriesFromMedia($pageid)
    {
        if (!$pageid || !Tools::isPositiveNumber($pageid)) {
            Tools::error('::get_categories_from_media: invalid pageid');
            return false;
        }
        $call = $this->commons->commonsApiUrl . '?action=query&format=json'
        . '&prop=categories'
        . '&pageids=' . $pageid
        ;
        if (!$this->commons->callCommons($call, 'pages')) {
            Tools::error('::get_categories_from_media: nothing found');
            return false;
        }
        $this->categories = !empty($this->commons->response['query']['pages'][$pageid]['categories'])
            ? $this->commons->response['query']['pages'][$pageid]['categories']
            : null;

        return true;
    }

    /**
     * @param $pageid
     * @return bool
     */
    public function linkMediaCategories($pageid)
    {
        if (!$pageid || !Tools::isPositiveNumber($pageid)) {
            Tools::error('link_media_categories: invalid pageid');

            return false;
        }

        if (!$this->getCategoriesFromMedia($pageid)) {
            Tools::error('link_media_categories: unable to get categories from API');

            return false;
        }

        // Remove any old category links for this media
        $this->database->queryAsBool(
            'DELETE FROM category2media WHERE media_pageid = :pageid',
            [':pageid' => $pageid]
        );

        foreach ($this->categories as $category) {
            if (!isset($category['title']) || !$category['title']) {
                Tools::error('link_media_categories: ERROR: missing category title');
                continue;
            }
            if (!isset($category['ns']) || $category['ns'] != '14') {
                Tools::error('link_media_categories: ERROR: invalid category namespace');
                continue;
            }

            $categoryId = $this->getCategoryIdFromName($category['title']);
            if (!$categoryId) {
                if (!$this->insertCategory($category['title'], true, 1)) {
                    Tools::error('link_media_categories: FAILED to insert ' . $category['title']);
                    continue;
                }
                $categoryId = $this->categoryId;
            }

            if (!$this->linkMediaToCategory($pageid, $categoryId)) {
                Tools::error('link_media_categories: FAILED to link category');
                continue;
            }
        }

        return true;
    }

    /**
     * @param $pageid
     * @param $categoryId
     * @return bool
     */
    public function linkMediaToCategory($pageid, $categoryId)
    {
        $response = $this->database->queryAsBool(
            'INSERT INTO category2media ( category_id, media_pageid ) VALUES ( :category_id, :pageid )',
            ['category_id' => $categoryId, 'pageid' => $pageid]
        );
        if (!$response) {
            return false;
        }
        return true;
    }

    /**
     * @param string $search
     * @return bool
     */
    public function findCategories($search = '')
    {
        if (!$search || $search == '' || !is_string($search)) {
            Tools::error('::find_categories: invalid search string: ' . $search);
            return false;
        }
        $call = $this->commons->commonsApiUrl . '?action=query&format=json'
        . '&list=search'
        . '&srnamespace=14' // 6 = File   14 = Category
        . '&srprop=size|snippet' // titlesnippet|timestamp|title
        . '&srlimit=500'
        . '&srsearch=' . urlencode($search);
        if (!$this->commons->callCommons($call, 'search')) {
            Tools::error('::find_categories: nothing found');
            return false;
        }
        return true;
    }

    /**
     * @param $category
     * @return array|bool
     */
    public function getCategoryInfo($category)
    {
        if (!$category || $category=='' || !is_string($category)) {
            Tools::error('::get_category_info: no category');
            return false;
        }
        $call = $this->commons->commonsApiUrl . '?action=query&format=json'
        . '&prop=categoryinfo'
        . '&titles=' . urlencode($category);    // cicontinue
        if (!$this->commons->callCommons($call, 'pages')) {
            Tools::error('::get_category_info: API: nothing found');
            return false;
        }
        if (isset($this->commons->response['query']['pages'])) {
            return $this->commons->response['query']['pages'];
        }
        Tools::error('::get_category_info: API: no pages');
        return false;
    }

    /**
     * @param $categoryName
     * @return bool
     */
    public function saveCategoryInfo($categoryName)
    {
        $categoryName = $this->categoryUrldecode($categoryName);
        $categoryRow = $this->getCategory($categoryName);
        if (!$categoryRow) {
            if (!$this->insertCategory($categoryName, false, 1)) {
                Tools::error('saveCategoryInfo: new category INSERT FAILED: ' . $categoryName);

                return false;
            }
            Tools::notice('saveCategoryInfo: NEW CATEGORY: '  . $categoryName);
            $categoryRow = $this->getCategory($categoryName);
            if (!$categoryRow) {
                Tools::error('saveCategoryInfo: Category save Failed: ' . $categoryName);

                return false;
            }
        }

        $categoryInfo = $this->getCategoryInfo($categoryName);
        $categoryInfo= @$categoryInfo[0];

        $bind = [];

        if (isset($categoryInfo['pageid'])
            && isset($categoryRow['pageid'])
            && $categoryInfo['pageid'] != $categoryRow['pageid']
        ) {
            $bind[':pageid'] = $categoryInfo['pageid'];
        }

        if (isset($categoryInfo['categoryinfo']['files'])
            && isset($categoryRow['files'])
            && $categoryInfo['categoryinfo']['files'] != $categoryRow['files']
        ) {
            $bind[':files'] = $categoryInfo['categoryinfo']['files'];
        }

        if (isset($categoryInfo['categoryinfo']['subcats'])
            && isset($categoryRow['subcats'])
            && $categoryInfo['categoryinfo']['subcats'] != $categoryRow['subcats']
        ) {
            $bind[':subcats'] = $categoryInfo['categoryinfo']['subcats'];
        }

        $hidden = 0;
        if (isset($categoryInfo['categoryinfo']['hidden'])) {
            $hidden = 1;
        }
        if (isset($categoryRow['hidden']) && $hidden != $categoryRow['hidden']) {
            $bind[':hidden'] = $hidden;
        }

        $missing = 0;
        if (isset($categoryInfo['categoryinfo']['missing'])) {
            $missing = 1;
        }
        if (isset($categoryRow['missing']) && $missing != $categoryRow['missing']) {
            $bind[':missing'] = $missing;
        }

        //$url = '<a href="' . $this->url('category') . '?c='
        //    . $this->categoryUrlencode($this->stripPrefix($categoryName))
        //    . '">' . $categoryName . '</a>';

        if (!$bind) {
            return true; // nothing to update
        }
        $sql = 'UPDATE category SET ';
        $sets = [];
        foreach (array_keys($bind) as $set) {
            $sets[] = str_replace(':', '', $set) . ' = ' . $set;
        }
        $sql .= implode($sets, ', ');
        $sql .= ' WHERE id = :id';

        $bind[':id'] = $categoryRow['id'];

        $result = $this->database->queryAsBool($sql, $bind);

        if ($result) {
            return true;
        }
        Tools::error('get_category_info: UPDATE/INSERT FAILED: ' . print_r($this->database->lastError, true));

        return false;
    }

    /**
     * @param string $name
     * @param bool $fillInfo
     * @param int $localFiles
     * @return bool
     */
    public function insertCategory($name = '', $fillInfo = true, $localFiles = 0)
    {
        if (!$name) {
            Tools::error('insert_category: no name found');

            return false;
        }

        if (!$this->database->queryAsBool(
            'INSERT INTO category (
                name, local_files, hidden, missing
            ) VALUES (
                :name, :local_files, :hidden, :missing
            )',
            [
                ':name' => $name,
                ':local_files' => $localFiles,
                ':hidden' => '0',
                ':missing' => '0'
            ]
        )
        ) {
            Tools::error('insert_category: FAILED to insert: ' . $name);

            return false;
        }

        $this->categoryId = $this->database->lastInsertId;

        if ($fillInfo) {
            $this->saveCategoryInfo($name);
        }

        Tools::notice(
            'SAVED CATEGORY: ' . $this->categoryId . ' = +<a href="'
            . $this->url('category') . '?c='
            . $this->categoryUrlencode($this->stripPrefix($name))
            . '">'
            . htmlentities($this->stripPrefix($name)) . '</a>'
        );

        return true;
    }

    /**
     * @param $category
     * @return bool
     */
    public function getSubcats($category)
    {
        if (!$category || $category=='' || !is_string($category)) {
            Tools::error('::get_subcats: ERROR - no category');
            return false;
        }
        Tools::notice('::get_subcats: ' . $category);
        $call = $this->commons->commonsApiUrl . '?action=query&format=json&cmlimit=50'
        . '&list=categorymembers'
        . '&cmtype=subcat'
        . '&cmprop=title'
        . '&cmlimit=500'
        . '&cmtitle=' . urlencode($category) ;
        if (!$this->commons->callCommons($call, 'categorymembers')
            || !isset($this->commons->response['query']['categorymembers'])
            || !is_array($this->commons->response['query']['categorymembers'])
        ) {
            Tools::error('::get_subcats: Nothing Found');

            return false;
        }
        foreach ($this->commons->response['query']['categorymembers'] as $subcat) {
            $this->insertCategory($subcat['title']);
        }

        return true;
    }

    /**
     * @param $categoryNameArray
     */
    public function importCategories($categoryNameArray)
    {
        Tools::notice("import_categories( category_name_array )");
        $this->database->beginTransaction();
        foreach ($categoryNameArray as $categoryName) {
            $categoryName = $this->categoryUrldecode($categoryName);
            $this->insertCategory($categoryName);
        }
        $this->database->commit();
        $this->database->vacuum();
    }

    /**
     * @param $categoryName
     * @return bool
     */
    public function updateCategoryLocalFilesCount($categoryName)
    {
        $sql = 'UPDATE category SET local_files = :local_files WHERE id = :id';
        $bind[':local_files'] = $this->getCategorySize($categoryName);
        if (is_int($categoryName)) {
            $bind['id'] = $categoryName;
        } else {
            $bind[':id'] = $this->getCategoryIdFromName($categoryName);
        }

        if (!$bind[':id']) {
            Tools::error("update_category_local_files_count( $categoryName ) - Category Not Found in Database");

            return false;
        }
        if ($this->database->queryAsBool($sql, $bind)) {
            Tools::notice('UPDATE CATEGORY SIZE: ' . $bind[':local_files'] . ' files in ' . $categoryName);

            return true;
        }
        Tools::error("update_category_local_files_count( $categoryName ) - UPDATE ERROR");

        return false;
    }

    /**
     *
     */
    public function updateCategoriesLocalFilesCount()
    {
        $sql = '
            SELECT c.id, c.local_files, count(c2m.category_id) AS size
            FROM category AS c
            LEFT JOIN category2media AS c2m ON c.id = c2m.category_id
            GROUP BY c.id
            ORDER by c.local_files ASC';

        $categoryNewSizes = $this->database->queryAsArray($sql);
        if (!$categoryNewSizes) {
            Tools::error('NOT FOUND: Updated 0 Categories Local Files count');

            return;
        }

        $updates = 0;
        $this->database->beginTransaction();
        foreach ($categoryNewSizes as $cat) {
            if ($cat['local_files'] == $cat['size']) {
                continue;
            }
            if ($this->insertCategoryLocalFilesCount($cat['id'], $cat['size'])) {
                $updates++;
            } else {
                Tools::error('ERROR: UPDATE FAILED: Category ID:' . $cat['id'] . ' local_files=' . $cat['size']);
            }
        }
        $this->database->commit();
        Tools::notice('Updated ' . $updates . ' Categories Local Files count');
        $this->database->vacuum();
    }

    /**
     * @param $categoryId
     * @param $categorySize
     * @return bool
     */
    public function insertCategoryLocalFilesCount($categoryId, $categorySize)
    {
        $sql = 'UPDATE category SET local_files = :category_size WHERE id = :category_id';
        $bind[':category_size'] = $categorySize;
        $bind[':category_id'] = $categoryId;
        if ($this->database->queryAsBool($sql, $bind)) {
            return true;
        }

        return false;
    }

    /**
     * @param $categoryId
     * @return bool
     */
    public function deleteCategory($categoryId)
    {
        if (!Tools::isPositiveNumber($categoryId)) {
            return false;
        }
        $bind = [':category_id' => $categoryId];
        if ($this->database->queryAsBool('DELETE FROM category WHERE id = :category_id', $bind)) {
            Tools::notice('DELETED Category #'. $categoryId);
        } else {
            Tools::error('UNABLE to delete category #' . $categoryId);

            return false;
        }
        if ($this->database->queryAsBool('DELETE FROM category2media WHERE category_id = :category_id', $bind)) {
            Tools::notice('DELETED Links to Category #'. $categoryId);
        } else {
            Tools::error('UNABLE to delete links to category #' . $categoryId);

            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function emptyCategoryTables()
    {
        $sqls = [
            'DELETE FROM category2media',
            'DELETE FROM category',
        ];
        $response = [];
        foreach ($sqls as $sql) {
            if ($this->database->queryAsBool($sql)) {
                $response[] = 'OK: ' . $sql;
            } else {
                $response[] = 'FAIL: ' . $sql;
            }
        }
        $this->database->vacuum();
        return $response;
    }

    // SMT Admin - Block

    /**
     * @return int
     */
    public function getBlockCount()
    {
        $count = $this->database->queryAsArray('SELECT count(block.pageid) AS count FROM block');
        if (isset($count[0]['count'])) {
            return $count[0]['count'];
        }
        return 0;
    }

    /**
     * @param $pageid
     * @return bool
     */
    public function isBlocked($pageid)
    {
        $block = $this->database->queryAsArray(
            'SELECT pageid FROM block WHERE pageid = :pageid',
            [':pageid' => $pageid]
        );
        if (isset($block[0]['pageid'])) {
            return true;
        }
        return false;
    }

    // SMT Admin

    /**
     *
     */
    public function includeAdminMenu()
    {
        $admin = $this->url('admin');
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
}
