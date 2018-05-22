<?php

declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger;

/**
 * Class sharedMediaTaggerAdmin
 */
class SharedMediaTaggerAdmin extends SharedMediaTagger
{
    protected $tablesCurrent;
    protected $sqlCurrent;
    protected $sqlNew;
    public $commonsApiUrl;
    public $apiCount;
    public $propImageinfo;
    public $totalHits;
    public $continue;
    public $sroffset;
    public $batchComplete;
    public $commonsResponse;
    public $categories;
    public $categoryId;
    public $databaseFile;

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
        $robotstxt = $this->installDirectory . '/robots.txt';
        $tagUrl = str_replace('//'.$this->server, '', $this->url('tag'));
        $sitemapUrl = $this->getProtocol() . $this->url('home') . 'sitemap.php';
        $reportUrl = str_replace('//'.$this->server, '', $this->url('contact')) . '?r=*';
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

    /**
     * @return array
     */
    public function getDatabaseTables()
    {
        return [
            'site' =>
                "CREATE TABLE IF NOT EXISTS 'site' (
                'id' INTEGER PRIMARY KEY,
                'name' TEXT,
                'about' TEXT,
                'header' TEXT,
                'footer' TEXT,
                'use_cdn' BOOLEAN NOT NULL DEFAULT '0',
                'curation' BOOLEAN NOT NULL DEFAULT '0',
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT su UNIQUE (name) )",
            'tag' =>
                "CREATE TABLE IF NOT EXISTS 'tag' (
                'id' INTEGER PRIMARY KEY,
                'position' INTEGER,
                'name' TEXT,
                'display_name' TEXT,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP )",
            'tagging' =>
                "CREATE TABLE IF NOT EXISTS 'tagging' (
                'id' INTEGER PRIMARY KEY,
                'tag_id' INTEGER,
                'media_pageid' INTEGER,
                'count' INTEGER,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT tmu UNIQUE (tag_id, media_pageid) )",
            'category' =>
                "CREATE TABLE IF NOT EXISTS 'category' (
                'id' INTEGER PRIMARY KEY,
                'name' TEXT,
                'curated' BOOLEAN NOT NULL DEFAULT '0',
                'pageid' INTEGER,
                'files' INTEGER,
                'subcats' INTEGER,
                'local_files' INTEGER DEFAULT '0',
                'curated_files' INTEGER DEFAULT '0',
                'missing' INTEGER DEFAULT '0',
                'hidden' INTEGER DEFAULT '0',
                'force' INTEGER,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT cu UNIQUE (name) )",
            'category2media' =>
                "CREATE TABLE IF NOT EXISTS 'category2media' (
                'id' INTEGER PRIMARY KEY,
                'category_id' INTEGER,
                'media_pageid' INTEGER,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT tmu UNIQUE (category_id, media_pageid) )",
            'media' =>
                "CREATE TABLE IF NOT EXISTS 'media' (
                'pageid' INTEGER PRIMARY KEY,
                'curated' BOOLEAN NOT NULL DEFAULT '0',
                'title' TEXT,
                'url' TEXT,
                'descriptionurl' TEXT,
                'descriptionshorturl' TEXT,
                'imagedescription' TEXT,
                'artist' TEXT,
                'datetimeoriginal' TEXT,
                'licenseuri' TEXT,
                'licensename' TEXT,
                'licenseshortname' TEXT,
                'usageterms' TEXT,
                'attributionrequired' TEXT,
                'restrictions' TEXT,
                'size' INTEGER,
                'width' INTEGER,
                'height' INTEGER,
                'sha1' TEXT,
                'mime' TEXT,
                'thumburl' TEXT,
                'thumbwidth' INTEGER,
                'thumbheight' INTEGER,
                'thumbmime' TEXT,
                'user' TEXT,
                'userid' INTEGER,
                'duration' REAL,
                'timestamp' TEXT,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP )",
            'contact' =>
                "CREATE TABLE IF NOT EXISTS 'contact' (
                'id' INTEGER PRIMARY KEY,
                'comment' TEXT,
                'datetime' TEXT,
                'ip' TEXT )",
            'block' =>
                "CREATE TABLE IF NOT EXISTS 'block' (
                'pageid' INTEGER PRIMARY KEY,
                'title' TEXT,
                'thumb' TEXT,
                'ns' INTEGER,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP )",
            'user' =>
                "CREATE TABLE IF NOT EXISTS 'user' (
                'id' INTEGER PRIMARY KEY,
                'ip' TEXT,
                'host' TEXT,
                'user_agent' TEXT,
                'page_views' INTEGER,
                'last' TEXT,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT uc UNIQUE (ip, host, user_agent) )",
            'user_tagging' =>
                "CREATE TABLE IF NOT EXISTS 'user_tagging' (
                'id' INTEGER PRIMARY KEY,
                'user_id' INTEGER,
                'tag_id' INTEGER,
                'media_pageid' INTEGER,
                'count' INTEGER,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT utu UNIQUE (user_id, tag_id, media_pageid) )",
            'network' =>
                "CREATE TABLE IF NOT EXISTS 'network' (
                'id' INTEGER PRIMARY KEY,
                'site_id' INTEGER NOT NULL,
                'ns' INTEGER NOT NULL,
                'pageid' INTEGER,
                'name' TEXT,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT nu UNIQUE (ns, pageid) )",
            'network_site' =>
                "CREATE TABLE IF NOT EXISTS 'network_site' (
                'id' INTEGER PRIMARY KEY,
                'url' TEXT,
                'name' TEXT,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT nsu UNIQUE (url) )",
        ];
    }

    /**
     * @return array
     */
    public function getDefaultDatabaseSetup()
    {
        return [
            'default_site' =>
                "INSERT INTO site (
                    id, name, about
                ) VALUES (
                    1,
                    'Shared Media Tagger Demo',
                    'This is a demonstration of the Shared Media Tagger software.'
                )",
            'default_tag1' =>
                "INSERT INTO tag (id, position, name, display_name) VALUES (1, 1, '☹️ Worst',  '☹️')",
            'default_tag2' =>
                "INSERT INTO tag (id, position, name, display_name) VALUES (2, 2, '🙁 Bad',    '🙁')",
            'default_tag3' =>
                "INSERT INTO tag (id, position, name, display_name) VALUES (3, 3, '😐 Unsure', '😐')",
            'default_tag4' =>
                "INSERT INTO tag (id, position, name, display_name) VALUES (4, 4, '🙂 Good',   '🙂')",
            'default_tag5' =>
                "INSERT INTO tag (id, position, name, display_name) VALUES (5, 5, '😊 Best',   '😊')",
        ];
    }

    // SQLiteTableStructureUpdater

    /**
     * @return bool
     */
    public function createTables()
    {
        if (!file_exists($this->databaseName)) {
            if (!touch($this->databaseName)) {
                $this->error('ERROR: can not touch database name: ' . $this->databaseName);

                return false;
            }
        }
        $this->setDatabaseFile($this->databaseName);
        $this->setNewStructures($this->getDatabaseTables());
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

        return $this->databaseLoaded();
    }

    /**
     * @param array $tables
     * @return bool
     */
    public function setNewStructures(array $tables = [])
    {
        if (!$tables || !is_array($tables)) {
            $this->error('$tables array is invalid');

            return false;
        }
        $errors = 0;
        $count = 0;
        foreach ($tables as $tableName => $tableSql) {
            $count++;
            if (!$tableName || !is_string($tableName)) {
                $this->error("#$count - Invalid table name");
                $errors++;
                continue;
            }
            if (!$tableSql || !is_string($tableSql)) {
                $this->error("#$count - Invalid table sql");
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
            $this->notice('OK: ' . sizeof($this->sqlNew) . ' tables up-to-date');

            return true;
        }
        $this->notice(sizeof($toUpdate) . ' tables to update: ' . implode($toUpdate, ', '));
        foreach ($toUpdate as $tableName) {
            $this->updateTable($tableName);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function databaseLoaded()
    {
        //if (!$this->db) {
        //    $this->open_database(); // @TODO find
        //}
        if (!$this->db) {
            return false;
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
        $this->queryAsBool("DROP TABLE IF EXISTS '$tmpName'");
        $this->queryAsBool("DROP TABLE IF EXISTS '$backupName'");
        $this->beginTransaction();
        $sql = $this->sqlNew[$tableName];
        $sql = str_ireplace("CREATE TABLE '$tableName'", "CREATE TABLE '$tmpName'", $sql);
        if (!$this->queryAsBool($sql)) {
            $this->error('ERROR: can not create tmp table:<br />' . $sql);
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
            if (!$this->queryAsBool($sql)) {
                $this->error('ERROR: can not insert into tmp table: ' . $tmpName
                . '<br />' . $sql);
                return false;
            }
            $newSize = $this->getTableSize($tmpName);
            if ($newSize == $oldSize) {
            } else {
                $this->error("ERROR: Inserted new $newSize rows, from $oldSize old rows");
            }
            if (!$this->queryAsBool("ALTER TABLE $tableName RENAME TO $backupName")) {
                $this->error('ERROR: can not rename '.$tableName.' to '.$backupName);

                return false;
            }
        }
        if (!$this->queryAsBool("ALTER TABLE $tmpName RENAME TO $tableName")) {
            $this->error('ERROR: can not rename ' . $tmpName . ' to ' . $backupName);

            return false;
        }
        $this->commit();
        $this->notice('OK: Table Structure Updated: ' . $tableName . ': +' . number_format((float) $newSize) . ' rows');
        $this->queryAsBool("DROP TABLE IF EXISTS '$tmpName'");
        $this->queryAsBool("DROP TABLE IF EXISTS '$backupName'");
        $this->vacuum();

        return true;
    }

    /**
     *
     */
    protected function setTableInfo()
    {
        $tables = $this->queryAsArray("SELECT name, sql FROM sqlite_master WHERE type = 'table'");
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
        $columns = $this->queryAsArray("PRAGMA table_info($tableName)");
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
        $size = $this->queryAsArray('SELECT count(rowid) AS count FROM ' . $tableName);
        if (isset($size[0]['count'])) {
            return $size[0]['count'];
        }
        $this->error('Can not get table size: ' . $tableName);

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
            if ($this->queryAsBool($sql)) {
                $response[] = 'OK: ' . $sql;
            } else {
                $response[] = 'FAIL: ' . $sql;
            }
        }
        $this->vacuum();

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
            if ($this->queryAsBool($sql)) {
                $response[] = 'OK: ' . $sql;
            } else {
                $response[] = 'FAIL: ' . $sql;
            }
        }
        $this->vacuum();

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
            if ($this->queryAsBool($sql)) {
                $response .= "<b>OK:</b> $sql<br />";
            } else {
                $response .= "<b>FAIL:<b/> $sql<br />";
            }
        }
        $this->vacuum();

        return $response;
    }

    // SMT Admin - Commons API

    /**
     * @param $url
     * @param string $key
     * @return bool
     */
    public function callCommons($url, $key = '')
    {
        if (!$url) {
            $this->error('::call_commons: ERROR: no url');

            return false;
        }
        $getResponse = @file_get_contents($url);

        if ($getResponse === false) {
            $this->error('Cannnot reach API endpoint'
                . '<br />URL: <a target="commons" href="' . $url . '">' . $url  .'</a>'
                . '<br />Exiting.');
            print '</div>';
            $this->includeFooter();

            exit;
        }
        $this->apiCount++;
        $this->commonsResponse = json_decode($getResponse, true);
        if (!$this->commonsResponse) {
            $this->error('::call_commons: ERROR: json_decode failed. Error: ' . json_last_error());
            $this->error('::call_commons: ERROR: ' . $this->smtJsonLastErrorMsg());

            return false;
        }

        if (empty($this->commonsResponse['query'][$key])
            || !$this->commonsResponse['query'][$key]
            || !is_array($this->commonsResponse['query'][$key])
        ) {
            $this->error("::call_commons: WARNING: missing key: $key");
        }

        $this->totalHits = $this->continue = $this->batchComplete = false;

        if (isset($this->commonsResponse['batchcomplete'])) {
            $this->batchComplete = true;
        }

        if (isset($this->commonsResponse['query']['searchinfo']['totalhits'])) {
            $this->totalHits = $this->commonsResponse['query']['searchinfo']['totalhits'];
            $this->notice('::call_commmons: totalhits=' . $this->totalHits);
        }
        if (isset($this->commonsResponse['continue'])) {
            $this->continue = $this->commonsResponse['continue']['continue'];
        }
        if (isset($this->commonsResponse['sroffset'])) {
            $this->sroffset = $this->commonsResponse['continue']['sroffset'];
        }
        if (isset($this->commonsResponse['warnings'])) {
            $this->error('::call_commons: ' . print_r($this->commonsResponse['warnings'], true));
            $this->error('::call_commons: url: ' . $url);
        }
        return true;
    }

    /**
     * @return mixed|string
     */
    public function smtJsonLastErrorMsg()
    {
        static $errors = [
            JSON_ERROR_NONE             => null,
            JSON_ERROR_DEPTH            => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH   => 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR        => 'Unexpected control character found',
            JSON_ERROR_SYNTAX           => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8             => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        ];
        $error = json_last_error();
        return array_key_exists($error, $errors) ? $errors[$error] : "Unknown error ({$error})";
    }

    // SMT Admin - Media

    /**
     * @param $pageid
     * @return bool|string
     */
    public function addMedia($pageid)
    {
        if (!$pageid || !$this->isPositiveNumber($pageid)) {
            $this->error('add_media: Invalid PageID');

            return false;
        }

        $response = '<div style="background-color:lightgreen; padding:10px;">'
        . '<p>Add Media: pageid: <b>' . $pageid . '</b></p>';

        // Get media info from API
        $media = $this->getApiImageinfo([$pageid], 0);
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
            $this->error('::save_media_to_database: no media array');
            return false;
        }

        $errors = [];

        $this->beginTransaction();

        foreach ($media as $id => $mediaFile) {
            $new = [];
            $new[':pageid'] = @$mediaFile['pageid'];
            $new[':title'] = @$mediaFile['title'];

            $new[':url'] = @$mediaFile['imageinfo'][0]['url'];
            if (!isset($new[':url']) || $new[':url'] == '') {
                $this->error('::save_media_to_database: ERROR: NO URL: SKIPPING: pageid='
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

            $response = $this->queryAsBool($sql, $new);

            if ($response === false) {
                $this->error('::save_media_to_database: STOPPING IMPORT');
                $this->error('::save_media_to_database: FAILED insert into media table');
                return false;
            }

            $this->notice('SAVED MEDIA: ' . $new[':pageid'] . ' = <a href="' . $this->url('info')
            . '?i=' . $new[':pageid'] . '">' . $this->stripPrefix($new[':title']) . '</a>');

            if (!$this->linkMediaCategories($new[':pageid'])) {
                $this->error('::: FAILED to link media categories - p:' . $new[':pageid']);
            }
            //$this->notice('::: LINKED ' . sizeof($this->categories) . ' categories');
        } // end while each media

        $this->commit();
        $this->vacuum();

        //$this->notice('END of save_media_to_database: ' . sizeof($media) . ' files');
        if ($errors) {
            $this->error($errors);
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

        $categorymembers = $this->getApiCategorymembers($category);
        if (!$categorymembers) {
            $this->error('::getMediaFromCategory: No Media Found');

            return false;
        }

        $blocked = $this->queryAsArray(
            'SELECT pageid FROM block WHERE pageid IN ('
                . implode($categorymembers, ',')
            . ')'
        );
        if ($blocked) {
            $this->error('ERROR: ' . sizeof($blocked) . ' BLOCKED MEDIA FILES');
            foreach ($blocked as $bpageid) {
                if (($key = array_search($bpageid['pageid'], $categorymembers)) !== false) {
                    unset($categorymembers[$key]);
                }
            }
        }

        $chunks = array_chunk($categorymembers, 50);
        foreach ($chunks as $chunk) {
            $this->saveMediaToDatabase($this->getApiImageinfo($chunk));
        }
        $this->updateCategoryLocalFilesCount($category);
        $this->saveCategoryInfo($category);

        return true;
    }

    /**
     * @see https://www.mediawiki.org/wiki/API:Categorymembers
     * @param $category
     * @return array
     */
    public function getApiCategorymembers($category)
    {
        $url = $this->commonsApiUrl . '?action=query&format=json'
        . '&list=categorymembers'
        . '&cmtype=file'
        . '&cmprop=ids'
        . '&cmlimit=500'
        . '&cmtitle=' . urlencode($category);
        if (!$this->callCommons($url, 'categorymembers')
            || !isset($this->commonsResponse['query']['categorymembers'])
        ) {
            $this->error('::get_api_categorymembers: ERROR: call');
            return [];
        }
        $pageids = [];
        foreach ($this->commonsResponse['query']['categorymembers'] as $cat) {
            $pageids[] = $cat['pageid'];
        }
        if (!$pageids) {
            return [];
        }
        return $pageids;
    }

    /**
     * @param $pageids
     * @param int $recurseCount
     * @return array
     */
    public function getApiImageinfo($pageids, $recurseCount = 0)
    {
        $call = $this->commonsApiUrl . '?action=query&format=json'
        . $this->propImageinfo
        . '&iiurlwidth=' . $this->sizeMedium
        . '&iilimit=50'
        . '&pageids=' . implode('|', $pageids);
        if (!$this->callCommons($call, 'pages')
            || !isset($this->commonsResponse['query']['pages'])
        ) {
            $this->error('::get_api_imageinfo: ERROR: call');

            return [];
        }

        $pages = $this->commonsResponse['query']['pages'];

        $errors = [];
        foreach ($pages as $media) {
            if (!isset($media['imageinfo'][0]['url'])) {
                $errors[] = $media['pageid'];
                unset($pages[ $media['pageid'] ]);
            }
        }

        if (!$recurseCount) {
            return $pages;
        }

        if ($recurseCount > 5) {
            $this->error('::get_api_imageinfo: TOO MUCH RECURSION: ' . $recurseCount);

            return $pages;
        }
        $recurseCount++;
        if ($errors) {
            $this->error('::get_api_imageinfo: CALL #' . $recurseCount . ': ' . sizeof($errors) . ' EMPTY files');
            $second = $this->getApiImageinfo($errors, $recurseCount);
            $this->notice('::get_api_imageinfo: CALL #' . $recurseCount . ': GOT: ' . sizeof($second) . ' files');
            $pages = array_merge($pages, $second);
            $this->notice('::get_api_imageinfo: CALL #' . $recurseCount . ': total pages: '
                . sizeof($pages) . ' files');
        }

        return $pages;
    }

    /**
     * @param $pageid
     * @param bool $noBlock
     * @return bool|string
     */
    public function deleteMedia($pageid, $noBlock = false)
    {
        if (!$pageid || !$this->isPositiveNumber($pageid)) {
            $this->error('delete_media: Invalid PageID');
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
            if ($this->queryAsBool($sql, $bind)) {
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
        if ($this->queryAsBool($sql, $bind)) {
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
            if ($this->queryAsBool($sql)) {
                $response[] = 'OK: ' . $sql;
            } else {
                $response[] = 'FAIL: ' . $sql;
            }
        }
        $this->vacuum();
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
        if (!$pageid || !$this->isPositiveNumber($pageid)) {
            $this->error('::get_categories_from_media: invalid pageid');
            return false;
        }
        $call = $this->commonsApiUrl . '?action=query&format=json'
        . '&prop=categories'
        . '&pageids=' . $pageid
        ;
        if (!$this->callCommons($call, 'pages')) {
            $this->error('::get_categories_from_media: nothing found');
            return false;
        }
        $this->categories = !empty($this->commonsResponse['query']['pages'][$pageid]['categories'])
            ? $this->commonsResponse['query']['pages'][$pageid]['categories']
            : null;

        return true;
    }

    /**
     * @param $pageid
     * @return bool
     */
    public function linkMediaCategories($pageid)
    {
        if (!$pageid || !$this->isPositiveNumber($pageid)) {
            $this->error('link_media_categories: invalid pageid');

            return false;
        }

        if (!$this->getCategoriesFromMedia($pageid)) {
            $this->error('link_media_categories: unable to get categories from API');

            return false;
        }

        // Remove any old category links for this media
        $this->queryAsBool(
            'DELETE FROM category2media WHERE media_pageid = :pageid',
            [':pageid' => $pageid]
        );

        foreach ($this->categories as $category) {
            if (!isset($category['title']) || !$category['title']) {
                $this->error('link_media_categories: ERROR: missing category title');
                continue;
            }
            if (!isset($category['ns']) || $category['ns'] != '14') {
                $this->error('link_media_categories: ERROR: invalid category namespace');
                continue;
            }

            $categoryId = $this->getCategoryIdFromName($category['title']);
            if (!$categoryId) {
                if (!$this->insertCategory($category['title'], true, 1)) {
                    $this->error('link_media_categories: FAILED to insert ' . $category['title']);
                    continue;
                }
                $categoryId = $this->categoryId;
            }

            if (!$this->linkMediaToCategory($pageid, $categoryId)) {
                $this->error('link_media_categories: FAILED to link category');
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
        $response = $this->queryAsBool(
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
            $this->error('::find_categories: invalid search string: ' . $search);
            return false;
        }
        $call = $this->commonsApiUrl . '?action=query&format=json'
        . '&list=search'
        . '&srnamespace=14' // 6 = File   14 = Category
        . '&srprop=size|snippet' // titlesnippet|timestamp|title
        . '&srlimit=500'
        . '&srsearch=' . urlencode($search);
        if (!$this->callCommons($call, 'search')) {
            $this->error('::find_categories: nothing found');
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
            $this->error('::get_category_info: no category');
            return false;
        }
        $call = $this->commonsApiUrl . '?action=query&format=json'
        . '&prop=categoryinfo'
        . '&titles=' . urlencode($category);    // cicontinue
        if (!$this->callCommons($call, 'pages')) {
            $this->error('::get_category_info: API: nothing found');
            return false;
        }
        if (isset($this->commonsResponse['query']['pages'])) {
            return $this->commonsResponse['query']['pages'];
        }
        $this->error('::get_category_info: API: no pages');
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
            if (!$this->insertCategory($categoryName, /*getinfo*/false, /*local_files*/1)) {
                $this->error('save_category_info: new category INSERT FAILED: ' . $categoryName);
                return false;
            }
            $this->notice('save_category_info: NEW CATEGORY: '  . $categoryName);
            $categoryRow = $this->getCategory($categoryName);
        }

        $categoryInfo = $this->getCategoryInfo($categoryName);
        foreach ($categoryInfo as $onesy) {
            $categoryInfo = $onesy; // is always just 1 result
        }

        $bind = [];

        if (@$categoryInfo['pageid'] != @$categoryRow['pageid']) {
            $bind[':pageid'] = $categoryInfo['pageid'];
        }

        if ($categoryInfo['categoryinfo']['files'] != $categoryRow['files']) {
            $bind[':files'] = $categoryInfo['categoryinfo']['files'];
        }

        if ($categoryInfo['categoryinfo']['subcats'] != $categoryRow['subcats']) {
            $bind[':subcats'] = $categoryInfo['categoryinfo']['subcats'];
        }

        $hidden = 0;
        if (isset($categoryInfo['categoryinfo']['hidden'])) {
            $hidden = 1;
        }
        if ($hidden != $categoryRow['hidden']) {
            $bind[':hidden'] = $hidden;
        }

        $missing = 0;
        if (isset($categoryInfo['categoryinfo']['missing'])) {
            $missing = 1;
        }
        if ($missing != $categoryRow['missing']) {
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

        $result = $this->queryAsBool($sql, $bind);

        if ($result) {
            return true;
        }
        $this->error('get_category_info: UPDATE/INSERT FAILED: ' . print_r($this->lastError, true));

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
            $this->error('insert_category: no name found');

            return false;
        }

        if (!$this->queryAsBool(
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
            $this->error('insert_category: FAILED to insert: ' . $name);

            return false;
        }

        $this->categoryId = $this->lastInsertId;

        if ($fillInfo) {
            $this->saveCategoryInfo($name);
        }

        $this->notice(
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
            $this->error('::get_subcats: ERROR - no category');
            return false;
        }
        $this->notice('::get_subcats: ' . $category);
        $call = $this->commonsApiUrl . '?action=query&format=json&cmlimit=50'
        . '&list=categorymembers'
        . '&cmtype=subcat'
        . '&cmprop=title'
        . '&cmlimit=500'
        . '&cmtitle=' . urlencode($category) ;
        if (!$this->callCommons($call, 'categorymembers')
            || !isset($this->commonsResponse['query']['categorymembers'])
            || !is_array($this->commonsResponse['query']['categorymembers'])
        ) {
            $this->error('::get_subcats: Nothing Found');

            return false;
        }
        foreach ($this->commonsResponse['query']['categorymembers'] as $subcat) {
            $this->insertCategory($subcat['title']);
        }

        return true;
    }

    /**
     * @param $categoryNameArray
     */
    public function importCategories($categoryNameArray)
    {
        $this->notice("import_categories( category_name_array )");
        $this->beginTransaction();
        foreach ($categoryNameArray as $categoryName) {
            $categoryName = $this->categoryUrldecode($categoryName);
            $this->insertCategory($categoryName);
        }
        $this->commit();
        $this->vacuum();
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
            $this->error("update_category_local_files_count( $categoryName ) - Category Not Found in Database");

            return false;
        }
        if ($this->queryAsBool($sql, $bind)) {
            $this->notice('UPDATE CATEGORY SIZE: ' . $bind[':local_files'] . ' files in ' . $categoryName);

            return true;
        }
        $this->error("update_category_local_files_count( $categoryName ) - UPDATE ERROR");

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

        $categoryNewSizes = $this->queryAsArray($sql);
        if (!$categoryNewSizes) {
            $this->error('NOT FOUND: Updated 0 Categories Local Files count');

            return;
        }

        $updates = 0;
        $this->beginTransaction();
        foreach ($categoryNewSizes as $cat) {
            if ($cat['local_files'] == $cat['size']) {
                continue;
            }
            if ($this->insertCategoryLocalFilesCount($cat['id'], $cat['size'])) {
                $updates++;
            } else {
                $this->error('ERROR: UPDATE FAILED: Category ID:' . $cat['id'] . ' local_files=' . $cat['size']);
            }
        }
        $this->commit();
        $this->notice('Updated ' . $updates . ' Categories Local Files count');
        $this->vacuum();
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
        if ($this->queryAsBool($sql, $bind)) {
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
        if (!$this->isPositiveNumber($categoryId)) {
            return false;
        }
        $bind = [':category_id' => $categoryId];
        if ($this->queryAsBool('DELETE FROM category WHERE id = :category_id', $bind)) {
            $this->notice('DELETED Category #'. $categoryId);
        } else {
            $this->error('UNABLE to delete category #' . $categoryId);

            return false;
        }
        if ($this->queryAsBool('DELETE FROM category2media WHERE category_id = :category_id', $bind)) {
            $this->notice('DELETED Links to Category #'. $categoryId);
        } else {
            $this->error('UNABLE to delete links to category #' . $categoryId);

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
            if ($this->queryAsBool($sql)) {
                $response[] = 'OK: ' . $sql;
            } else {
                $response[] = 'FAIL: ' . $sql;
            }
        }
        $this->vacuum();
        return $response;
    }

    // SMT Admin - Block

    /**
     * @return int
     */
    public function getBlockCount()
    {
        $count = $this->queryAsArray('SELECT count(block.pageid) AS count FROM block');
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
        $block = $this->queryAsArray(
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
     * SharedMediaTaggerAdmin constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->debug = false;
        $this->commonsApiUrl = 'https://commons.wikimedia.org/w/api.php';
        ini_set('user_agent', 'Shared Media Tagger v' . __SMT__);
        $this->apiCount = 0;
        $this->propImageinfo = '&prop=imageinfo'
            . '&iiprop=url|size|mime|thumbmime|user|userid|sha1|timestamp|extmetadata'
            . '&iiextmetadatafilter=LicenseShortName|UsageTerms|AttributionRequired|'
            . 'Restrictions|Artist|ImageDescription|DateTimeOriginal';
        $this->setAdminCookie();
    }

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
