<?php
// Shared Media Tagger
// SMT Admin

//////////////////////////////////////////////////////////
// SMT Admin - Utils
class smt_admin_utils extends smt {

    //////////////////////////////////////////////////////////
    function set_admin_cookie() {
        if( isset($_COOKIE['admin']) && $_COOKIE['admin'] == '1' ) {
            return;
        }
        setcookie('admin','1',time()+60*60*4,'/'); // 4 hour admin cookie
        //$this->notice('Admin cookie set');
    }

    //////////////////////////////////////////////////////////
    function check_robotstxt() {

        $robotstxt = $this->install_directory . '/robots.txt';

        $tag_url = str_replace('//'.$this->server, '', $this->url('tag'));
        $sitemap_url = $this->get_protocol() . $this->url('home') . 'sitemap.php';
        $report_url = str_replace('//'.$this->server, '', $this->url('contact')) . '?r=*';

        $response = $robotstxt;

        if( !file_exists($robotstxt) ) {
            return '<br />❌file not found: ' . $robotstxt
            . '<br />❌rule not found: user-agent: *'
            . '<br />❌rule not found: disallow: ' . $tag_url
            . '<br />❌rule not found: disallow: ' . $report_url
            . '<br />❌rule not found: sitemap: ' . $sitemap_url
            ;
        }
        $response .= '<br />✔️exists';

        $content = file($robotstxt);
        if( !is_array($content) ) {
            return $response .= ''
            . '<br />❌rule not found: user-agent: *'
            . '<br />❌rule not found: disallow: ' . $tag_url
            . '<br />❌rule not found: disallow: ' . $report_url
            . '<br />❌rule not found: sitemap: ' . $sitemap_url
            ;
        }

        $user_agent_star = FALSE;
        $tag_disallow = FALSE;
        $sitemap = FALSE;
        $report_disallow = FALSE;

        foreach( $content as $line ) {

            if( strtolower(trim($line)) == 'sitemap: ' . $sitemap_url ) {
                $sitemap = TRUE;
                $response .= '<br />✔️rule ok: sitemap: ' . $sitemap_url;
                continue;
            }

            if( strtolower(trim($line)) == 'user-agent: *' ) {
                $user_agent_star = TRUE;
            $response .= '<br />✔️rule ok: user-agent: *';
                continue;
            }
            if( !$user_agent_star ) {
                continue;
            }

            if( strtolower(trim($line)) == 'disallow: ' . $tag_url ) {
                $tag_disallow = TRUE;
                $response .= '<br />✔️rule ok: disallow: ' . $tag_url;
                continue;
            }
            if( strtolower(trim($line)) == 'disallow: ' . $report_url ) {
                $report_disallow = TRUE;
                $response .= '<br />✔️rule ok: disallow: ' . $report_url;
                continue;
            }

        }
        if( !$sitemap ) {
             $response .= '<br />❌rule not found: sitemap: ' . $sitemap_url;
        }
        if( !$user_agent_star ) {
            $response .= '<br />❌rule not found: user-agent: *';
        }
        if( !$tag_disallow ) {
            $response .= '<br />❌rule not found: disallow: ' . $tag_url;
        }
        if( !$report_disallow ) {
            $response .= '<br />❌rule not found: disallow: ' . $report_url;
        }
        return $response;
    }

} // end class smt_admin_utils

//////////////////////////////////////////////////////////
// SMT Admin - Database Utils
class smt_admin_database_utils extends smt_admin_utils {

    //////////////////////////////////////////////////////////
    function empty_tagging_tables() {
        $sqls = array(
            'DELETE FROM tagging',
            'DELETE FROM user_tagging',
        );
        $response = array();
        foreach( $sqls as $sql ) {
            if( $this->query_as_bool($sql) ) {
                $response[] = 'OK: ' . $sql;
            } else {
                $response[] = 'FAIL: ' . $sql;
            }
        }
        $this->vacuum();
        return $response;
    }

    //////////////////////////////////////////////////////////
    function empty_user_tables() {
        $sqls = array(
            'DELETE FROM user',
            'DELETE FROM tagging',
            'DELETE FROM user_tagging',
        );
        $response = array();
        foreach( $sqls as $sql ) {
            if( $this->query_as_bool($sql) ) {
                $response[] = 'OK: ' . $sql;
            } else {
                $response[] = 'FAIL: ' . $sql;
            }
        }
        $this->vacuum();
        return $response;
    }

    //////////////////////////////////////////////////////////
    function create_tables() {
        $this->debug('smt_db_init:create_database()');
        if( !file_exists($this->database_name) ) {
            if( !@touch($this->database_name) ) {
                $this->error('smt_db_init:create_database(): ERROR: can not touch database name: '
                    .$this->database_name);
                return FALSE;
            }
        }

        $tables = array(

'site' =>
    "CREATE TABLE IF NOT EXISTS 'site' (
    'id' INTEGER PRIMARY KEY,
    'name' TEXT,
    'about' TEXT,
    CONSTRAINT su UNIQUE (name)
)",

'tag' =>
    "CREATE TABLE IF NOT EXISTS 'tag' (
    'id' INTEGER PRIMARY KEY,
    'position' INTEGER,
    'name' TEXT,
    'display_name' TEXT
)",

'tagging' =>
    "CREATE TABLE IF NOT EXISTS 'tagging' (
    'id' INTEGER PRIMARY KEY,
    'tag_id' INTEGER,
    'media_pageid' INTEGER,
    'count' INTEGER,
    'updated' TEXT DEFAULT '0000-00-00 00:00:00' NOT NULL,
    CONSTRAINT tmu UNIQUE (tag_id, media_pageid)
)",

'category' =>
    "CREATE TABLE IF NOT EXISTS 'category' (
    'id' INTEGER PRIMARY KEY,
    'name' TEXT,
    'pageid' INTEGER,
    'files' INTEGER,
    'subcats' INTEGER,
    'local_files' INTEGER DEFAULT '0' NOT NULL,
    'missing' INTEGER DEFAULT '0' NOT NULL,
    'hidden' INTEGER DEFAULT '0' NOT NULL,
    'force' INTEGER,
    'updated' TEXT DEFAULT '0000-00-00 00:00:00' NOT NULL,
    CONSTRAINT cu UNIQUE (name)
)",

'category2media' =>
    "CREATE TABLE IF NOT EXISTS 'category2media' (
    'id' INTEGER PRIMARY KEY,
    'category_id' INTEGER,
    'media_pageid' INTEGER,
    CONSTRAINT tmu UNIQUE (category_id, media_pageid)
)",

'media' =>
    "CREATE TABLE IF NOT EXISTS 'media' (
    'pageid' INTEGER PRIMARY KEY,
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
    'skin' REAL,
    'updated' TEXT DEFAULT '0000-00-00 00:00:00' NOT NULL
)",

'media_upgrade_301' => 'ALTER TABLE media ADD COLUMN ahash TEXT',
'media_upgrade_302' => 'ALTER TABLE media ADD COLUMN dhash TEXT',
'media_upgrade_303' => 'ALTER TABLE media ADD COLUMN phash TEXT',



'contact' =>
    "CREATE TABLE IF NOT EXISTS 'contact' (
    'id' INTEGER PRIMARY KEY,
    'comment' TEXT,
    'datetime' TEXT,
    'ip' TEXT
)",

'block' =>
    "CREATE TABLE IF NOT EXISTS 'block' (
    'pageid' INTEGER PRIMARY KEY,
    'title' TEXT,
    'thumb' TEXT,
    'ns' INTEGER,
    'updated' TEXT DEFAULT '0000-00-00 00:00:00' NOT NULL
)",

'user' =>
    "CREATE TABLE IF NOT EXISTS 'user' (
    'id' INTEGER PRIMARY KEY,
    'ip' TEXT,
    'host' TEXT,
    'user_agent' TEXT,
    'page_views' INTEGER,
    'last' TEXT,
    CONSTRAINT uc UNIQUE (ip, host, user_agent)
)",

'user_tagging' =>
    "CREATE TABLE IF NOT EXISTS 'user_tagging' (
    'id' INTEGER PRIMARY KEY,
    'user_id' INTEGER,
    'tag_id' INTEGER,
    'media_pageid' INTEGER,
    'count' INTEGER,
    'updated' TEXT DEFAULT '0000-00-00 00:00:00' NOT NULL,
    CONSTRAINT utu UNIQUE (user_id, tag_id, media_pageid)
)",


'network' =>
    "CREATE TABLE IF NOT EXISTS 'network' (
    'id' INTEGER PRIMARY KEY,
    'site_id' INTEGER NOT NULL,
    'ns' INTEGER NOT NULL,
    'pageid' INTEGER,
    'name' TEXT,
    CONSTRAINT nu UNIQUE (ns, pageid)
)",

'network_site' =>
    "CREATE TABLE IF NOT EXISTS 'network_site' (
    'id' INTEGER PRIMARY KEY,
    'url' TEXT,
    CONSTRAINT nsu UNIQUE (url)
)",

// Default Demo Site setup

'default_site' => "INSERT INTO site (id, name, about) VALUES (1, 'Shared Media Tagger Demo', 'This is a demonstration of the Shared Media Tagger software.')",

'default_tag1' => "INSERT INTO tag (id, position, name, display_name) VALUES (1, 1, '☹️ Worst',  '☹️')",
'default_tag2' => "INSERT INTO tag (id, position, name, display_name) VALUES (2, 2, '🙁 Bad',    '🙁')",
'default_tag3' => "INSERT INTO tag (id, position, name, display_name) VALUES (3, 3, '😐 Unsure', '😐')",
'default_tag4' => "INSERT INTO tag (id, position, name, display_name) VALUES (4, 4, '🙂 Good',   '🙂')",
'default_tag5' => "INSERT INTO tag (id, position, name, display_name) VALUES (5, 5, '😊 Best',   '😊')",


); // end tables array

        $response = false;
        while( list($name,$create) = each($tables) ) {
            if( $this->query_as_bool($create) ) {
                $response .= "<br /><b>OK: $name</b>: $create";
            } else {
                $response .= "<br /><b>FAIL: $name</b>: $create";
            }
        }
        $this->vacuum();
        return $response;
    }

    //////////////////////////////////////////////////////////
    function drop_tables() {

        $sqls = array(
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
        );
        $response = false;
        while( list(,$sql) = each($sqls) ) {
            if( $this->query_as_bool($sql) ) {
                $response .= "<b>OK:</b> $sql<br />";
            } else {
                $response .= "<b>FAIL:<b/> $sql<br />";
            }
        }
        $this->vacuum();
        return $response;
    }

} // END class smt_admin_database_utils

//////////////////////////////////////////////////////////
// SMT Admin - Commons API
class smt_commons_API extends smt_admin_database_utils {

    var $commons_api_url;
    var $api_count;
    var $prop_imageinfo;
    var $totalhits;
    var $continue;
    var $sroffset;
    var $batchcomplete;
    var $commons_response;

    //////////////////////////////////////////////////////////
    function call_commons($url, $key='') {

        $this->debug('call_commons( url:<a target="commons" href="'.$url.'">'
        . $this->truncate(str_replace('https://commons.wikimedia.org/w/api.php?action=query&format=json','',$url), 100)."</a>, $key )");

        if( !$url ) { $this->error('::call_commons: ERROR: no url'); return FALSE; }
        $this->start_timer('call_commons');
        $get_response = @file_get_contents($url);
        $this->end_timer('call_commons');

        if( $get_response === FALSE ) {
            $this->error('Cannnot reach API endpoint'
                . '<br />URL: <a target="commons" href="' . $url . '">' . $url  .'</a>'
                . '<br />Exiting.');
            print '</div>';
            $this->include_footer();
            exit;
        }
        $this->api_count++;
        $this->commons_response = json_decode($get_response,TRUE); // assoc
        if( !$this->commons_response ) {
            $this->error('::call_commons: ERROR: json_decode failed. Error: ' . json_last_error() );
            $this->error('::call_commons: ERROR: ' . $this->smt_json_last_error_msg() );
            return FALSE;
        }

        if( !@$this->commons_response['query'][$key] || !is_array($this->commons_response['query'][$key])  ) {
            $this->error("::call_commons: WARNING: missing key: $key");
            //return FALSE;
        }

        $this->totalhits = $this->continue = $this->batchcomplete = FALSE;

        if( isset($this->commons_response['batchcomplete']) ) {
            $this->batchcomplete = TRUE;
            //$this->notice('::call_commmons: batchcomplete=' . $this->batchcomplete);
        }

        if( isset($this->commons_response['query']['searchinfo']['totalhits']) ) {
            $this->totalhits = $this->commons_response['query']['searchinfo']['totalhits'];
            $this->notice('::call_commmons: totalhits=' . $this->totalhits);

        }
        if( isset($this->commons_response['continue']) ) {
            $this->continue = $this->commons_response['continue']['continue'];
            //$this->notice('::call_commmons: continue=' . $this->continue  );
        }
        if( isset($this->commons_response['sroffset']) ) {
            $this->sroffset = $this->commons_response['continue']['sroffset'];
            //$this->notice('::call_commmons: sroffset=' . $this->sroffset  );
        }
        if( isset($this->commons_response['warnings']) ) {
            $this->error('::call_commons: ' . print_r($this->commons_response['warnings'],1) );
            $this->error('::call_commons: url: ' . $url);
        }
           return TRUE;
    } // end function call_commons()

    //////////////////////////////////////////////////////////
    function smt_json_last_error_msg() {
        static $errors = array(
            JSON_ERROR_NONE             => null,
            JSON_ERROR_DEPTH            => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH   => 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR        => 'Unexpected control character found',
            JSON_ERROR_SYNTAX           => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8             => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        );
        $error = json_last_error();
        return array_key_exists($error, $errors) ? $errors[$error] : "Unknown error ({$error})";
    }

} // end class smt_commons_API

//////////////////////////////////////////////////////////
// SMT Admin - Media
class smt_admin_media extends smt_commons_API {

    //////////////////////////////////////////////////////////
    function add_media($pageid) {

        $this->debug("add_media( $pageid )");

        if( !$pageid || !$this->is_positive_number($pageid) ) {
            $this->error('add_media: Invalid PageID');
            return FALSE;
        }

        $response = '<div style="background-color:lightgreen; padding:10px;">'
        . '<p>Add Media: pageid: <b>' . $pageid . '</b></p>';

        // Get media info from API
        $media = $this->get_api_imageinfo( array($pageid), /*$recurse_count=*/0 );
        if( !$media ) {
            return $response . '<p>ERROR: failed to get media info from API</p></div>';
        }
        $response .= '<p>OK: media: <b>' . @$media[$pageid]['title'] . '</b></p>';

        // Save media
        if( !$this->save_media_to_database($media) ) {
            return $response . '<p>ERROR: failed to save media to database</p></div>';
        }
        $response .= '<p>OK: Saved media: <b><a href="' . $this->url('info')
        . '?i=' . $pageid . '">info.php?i=' . $pageid . '</a></b></p>';

        if( !$this->categories ) {
            return $response . '<p>No Categories Found</p></div>';
        }

        foreach( $this->categories as $category ) {
            $response .= '+'
            . '<a href="' . $this->url('category')
            . '?c=' . $this->category_urlencode($this->strip_prefix($category['title']))
            . '">' . $this->strip_prefix($category['title']) . '</a><br />';
        }

        //$response .= $this->display_thumbnail_box($media[$pageid]);

        $response .= '</div>';

        return $response;
    }

    //////////////////////////////////////////////////////////
    function save_media_to_database( $media=array() ) {

        $this->debug('save_media_to_database( media:'.sizeof($media).' )');

        if( !$media || !is_array($media) ) {
            $this->error('::save_media_to_database: no media array');
            return FALSE;
        }

        $errors = array();

        $this->begin_transaction();

        while( list(,$media_file) = each($media) ) {

            $new = array();
            $new[':pageid'] = @$media_file['pageid'];
            $new[':title'] = @$media_file['title'];

            $new[':url'] = @$media_file['imageinfo'][0]['url'];
            if( !isset($new[':url']) || $new[':url'] == '' ) {
                $this->error('::save_media_to_database: ERROR: NO URL: SKIPPING: pageid='
                    . @$new[':pageid'] . ' title=' . @$new[':title'] );
                $errors[ $new[':pageid'] ] = $new[':title'];
                continue;
            }

            $new[':descriptionurl'] = @$media_file['imageinfo'][0]['descriptionurl'];
            $new[':descriptionshorturl'] = @$media_file['imageinfo'][0]['descriptionshorturl'];

            $new[':imagedescription'] = @$media_file['imageinfo'][0]['extmetadata']['ImageDescription']['value'];
            $new[':artist'] = @$media_file['imageinfo'][0]['extmetadata']['Artist']['value'];
            $new[':datetimeoriginal'] = @$media_file['imageinfo'][0]['extmetadata']['DateTimeOriginal']['value'];
            $new[':licenseshortname'] = @$media_file['imageinfo'][0]['extmetadata']['LicenseShortName']['value'];
            $new[':usageterms'] = @$media_file['imageinfo'][0]['extmetadata']['UsageTerms']['value'];
            $new[':attributionrequired'] = @$media_file['imageinfo'][0]['extmetadata']['AttributionRequired']['value'];
            $new[':restrictions'] = @$media_file['imageinfo'][0]['extmetadata']['Restrictions']['value'];

            $new[':licenseuri'] = @$this->open_content_license_uri( $new[':licenseshortname'] );
            $new[':licensename'] = @$this->open_content_license_name( $new[':licenseuri'] );

            $new[':size'] = @$media_file['imageinfo'][0]['size'];
            $new[':width'] = @$media_file['imageinfo'][0]['width'];
            $new[':height'] = @$media_file['imageinfo'][0]['height'];
            $new[':sha1'] = @$media_file['imageinfo'][0]['sha1'];
            $new[':mime'] = @$media_file['imageinfo'][0]['mime'];

            $new[':thumburl'] = @$media_file['imageinfo'][0]['thumburl'];
            $new[':thumbwidth'] = @$media_file['imageinfo'][0]['thumbwidth'];
            $new[':thumbheight'] = @$media_file['imageinfo'][0]['thumbheight'];
            $new[':thumbmime'] = @$media_file['imageinfo'][0]['thumbmime'];

            $new[':user'] = @$media_file['imageinfo'][0]['user'];
            $new[':userid'] = @$media_file['imageinfo'][0]['userid'];

            $new[':duration'] = @$media_file['imageinfo'][0]['duration'];
            $new[':timestamp'] = @$media_file['imageinfo'][0]['timestamp'];
            $new[':updated'] = $this->time_now();

            $sql = "INSERT OR REPLACE INTO MEDIA (
                        pageid, title, url,
                        descriptionurl, descriptionshorturl, imagedescription,
                        artist, datetimeoriginal,
                        licenseuri, licensename, licenseshortname, usageterms, attributionrequired, restrictions,
                        size, width, height, sha1, mime,
                        thumburl, thumbwidth, thumbheight, thumbmime,
                        user, userid, duration, timestamp, updated
                    ) VALUES (
                        :pageid, :title, :url,
                        :descriptionurl, :descriptionshorturl, :imagedescription,
                        :artist, :datetimeoriginal,
                        :licenseuri, :licensename, :licenseshortname, :usageterms, :attributionrequired, :restrictions,
                        :size, :width, :height, :sha1, :mime,
                        :thumburl, :thumbwidth, :thumbheight, :thumbmime,
                        :user, :userid, :duration, :timestamp, :updated
                    )";

            $response = $this->query_as_bool($sql, $new);

            if( $response === FALSE) {
                $this->error('::save_media_to_database: STOPPING IMPORT');
                $this->error('::save_media_to_database: FAILED insert into media table');
                $this->debug('::save_media_to_database: SQL: ' . $sql);
                $this->debug('::save_media_to_database: BIND i: ' . print_r($new,1) );
                return FALSE;
            }

            $this->notice('SAVED MEDIA: ' . $new[':pageid'] . ' = <a href="' . $this->url('info')
            . '?i=' . $new[':pageid'] . '">' . $this->strip_prefix($new[':title']) . '</a>');

            if( !$this->link_media_categories($new[':pageid']) ) {
                $this->error('::: FAILED to link media categories - p:' . $new[':pageid']);
            }
            //$this->notice('::: LINKED ' . sizeof($this->categories) . ' categories');
        } // end while each media

        $this->commit();
        $this->vacuum();

        //$this->notice('END of save_media_to_database: ' . sizeof($media) . ' files');
        if( $errors ) { $this->error($errors); }
        return TRUE;
    } // end function save_media_to_database()

    //////////////////////////////////////////////////////////
    function get_media_from_category( $category='' ) {

        $this->debug("get_media_from_category( $category )");

        $category = trim($category);
        if( !$category ) { return false; }
        $category = ucfirst($category);
        if ( !preg_match('/^[Category:]/i', $category)) {
            $category = 'Category:' . $category;
        }

        $categorymembers = $this->get_api_categorymembers( $category );
        if( !$categorymembers ) {
            $this->error('::get_media_from_category: No Media Found');
            return FALSE;
        }

        $blocked = $this->query_as_array(
            'SELECT pageid FROM block WHERE pageid IN ('
                . implode($categorymembers, ',')
            . ')');
        if( $blocked ) {
            $this->error('ERROR: ' . sizeof($blocked) . ' BLOCKED MEDIA FILES');
            foreach( $blocked as $bpageid ) {
                if(($key = array_search($bpageid['pageid'], $categorymembers)) !== false) {
                    unset($categorymembers[$key]);
                }
            }
        }

        $chunks = array_chunk( $categorymembers, 50 );
        foreach( $chunks as $chunk ) {
            //$this->notice('::get_media_from_category: TRY CHUNK: ' . sizeof($chunk));
            $this->save_media_to_database( $this->get_api_imageinfo($chunk) );
        }

        $this->debug('END of get_media_from_category: ' . sizeof($categorymembers) . ' files');

        $this->update_category_local_files_count( $category );

        $this->save_category_info( $category );

    } // end function get_media_from_category()

    //////////////////////////////////////////////////////////
    function get_api_categorymembers( $category ) {

        $this->debug("get_api_categorymembers( $category )");

        $url = $this->commons_api_url . '?action=query&format=json'
        . '&list=categorymembers'  // https://www.mediawiki.org/wiki/API:Categorymembers
        . '&cmtype=file'
        . '&cmprop=ids'
        . '&cmlimit=500'
        . '&cmtitle=' . urlencode($category);
        if( !$this->call_commons($url, 'categorymembers')
            || !isset( $this->commons_response['query']['categorymembers'])
        ) {
            $this->error('::get_api_categorymembers: ERROR: call');
            return array();
        }
        $pageids = array();
        foreach( $this->commons_response['query']['categorymembers']  as $x ) {
            $pageids[] = $x['pageid'];
        }
        if( !$pageids ) {
            //$this->notice('::get_api_categorymembers: No files found');
            return array();
        }
        //$this->notice('::get_api_categorymembers: GOT: ' . sizeof($pageids) );
        return $pageids;
    }

    //////////////////////////////////////////////////////////
    function get_api_imageinfo( $pageids, $recurse_count=0 ) {

        $this->debug("get_api_imageinfo( pageids, $recurse_count )");


        //$this->notice('::get_api_imageinfo: pageids size: ' . sizeof($pageids) . ' recurse=' . $recurse_count);
        $call = $this->commons_api_url . '?action=query&format=json'
        . $this->prop_imageinfo
        . '&iiurlwidth=' . $this->size_medium
        . '&iilimit=50'
        . '&pageids=' . implode('|',$pageids);
        if( !$this->call_commons($call, 'pages')
            || !isset($this->commons_response['query']['pages'])
        ) {
            $this->error('::get_api_imageinfo: ERROR: call');
            return array();
        }

        $pages = $this->commons_response['query']['pages'];
        //$this->notice('::get_api_imageinfo: CALL #' . $recurse_count . ': GOT: ' . sizeof($pages) . ' files');

        $errors = array();
        foreach( $pages as $media ) {
            if( !isset($media['imageinfo'][0]['url']) ) {
                $errors[] = $media['pageid'];
                unset( $pages[ $media['pageid'] ] );
            }
        }

        if( !$recurse_count ) {
            //$this->notice('::get_api_imageinfo: NO RECURSION.  returning');
            return $pages;
        }

        if( $recurse_count > 5 ) {
            $this->error('::get_api_imageinfo: TOO MUCH RECURSION: ' . $recurse_count);
            return $pages;
        }
        $recurse_count++;
        if( $errors ) {
            $this->error('::get_api_imageinfo: CALL #' . $recurse_count . ': ' . sizeof($errors) . ' EMPTY files');
            $second = $this->get_api_imageinfo( $errors, $recurse_count );
            $this->notice('::get_api_imageinfo: CALL #' . $recurse_count . ': GOT: ' . sizeof($second) . ' files');
            $pages = array_merge($pages, $second);
            $this->notice('::get_api_imageinfo: CALL #' . $recurse_count . ': total pages: ' . sizeof($pages) . ' files');
        }

        return $pages;
    }

    ////////////////////////////////////////////////////
    function delete_media( $pageid, $no_block=FALSE ) {

        $this->debug("delete_media( $pageid, $no_block )");

        if( !$pageid || !$this->is_positive_number($pageid) ) {
            $this->error('delete_media: Invalid PageID');
            return FALSE;
        }
        $response = '<div style="white-space:nowrap;  font-family:monospace; background-color:lightsalmon;">'
        . 'Deleting Media :pageid = ' . $pageid;

        $media = $this->get_media($pageid);
        if( !$media ) {
            $response .= '<p>Media Not Found</p></div>';
            return $response;
        }

        $sqls = array();
        $sqls[] = 'DELETE FROM media WHERE pageid = :pageid';
        $sqls[] = 'DELETE FROM category2media WHERE media_pageid = :pageid';
        $sqls[] = 'DELETE FROM tagging WHERE media_pageid = :pageid';
        $sqls[] = 'DELETE FROM user_tagging WHERE media_pageid = :pageid';
        $bind = array(':pageid'=>$pageid);
        foreach( $sqls as $sql ) {
            if( $this->query_as_bool($sql, $bind) ) {
                //$response .= '<br />OK: ' . $sql;
            } else {
                $response .= '<br />ERROR: ' . $sql;
            }
        }

        if( $no_block ) {
            return $response . '</div>';
        }

        $sql = 'INSERT INTO block (pageid, title, thumb) VALUES (:pageid, :title, :thumb)';
        $bind = array(
            ':pageid'=>$pageid,
            ':title'=>@$media[0]['title'],
            ':thumb'=>@$media[0]['thumburl'],
        );
        if( $this->query_as_bool($sql, $bind) ) {
            //$response .= '<br />OK: ' . $sql;
        } else {
            $response .= '<br />ERROR: ' . $sql;
        }

        return $response . '</div>';

    }

    //////////////////////////////////////////////////////////
    function empty_media_tables() {
        $sqls = array(
            'DELETE FROM tagging',
            'DELETE FROM user_tagging',
            'DELETE FROM category2media',
            'DELETE FROM media',
            'DELETE FROM block',
        );
        $response = array();
        foreach( $sqls as $sql ) {
            if( $this->query_as_bool($sql) ) {
                $response[] = 'OK: ' . $sql;
            } else {
                $response[] = 'FAIL: ' . $sql;
            }
        }
        $this->vacuum();
        return $response;
    }

    //////////////////////////////////////////////////////////
    // modified from: https://github.com/gbv/image-attribution - MIT License
    function open_content_license_name($uri) {
        if ($uri == 'http://creativecommons.org/publicdomain/zero/1.0/') {
            return "CC0";
        } else if($uri == 'https://creativecommons.org/publicdomain/mark/1.0/') {
            return "Public Domain";
        } else if(preg_match('/^http:\/\/creativecommons.org\/licenses\/(((by|sa)-?)+)\/([0-9.]+)\/(([a-z]+)\/)?/',$uri,$match)) {
            $license = "CC ".strtoupper($match[1])." ".$match[4];
            if (isset($match[6])) $license .= " ".$match[6];
            return $license;
        } else {
            return;
        }
    }

    //////////////////////////////////////////////////////////
    // modified from: https://github.com/gbv/image-attribution - MIT License
    function open_content_license_uri($license) {
        $license = strtolower(trim($license));

        // CC Zero
        if (preg_match('/^(cc0|cc[ -]zero)$/', $license)) {
            return 'http://creativecommons.org/publicdomain/zero/1.0/';
        }
        // Public Domain
        elseif (preg_match('/^(cc )?(pd|pdm|public[ -]domain)( mark( 1\.0)?)?$/', $license)) {
            return 'https://creativecommons.org/publicdomain/mark/1.0/';
        }
        // No restrictions (for instance images imported from Flickr Commons)
        elseif ($license == "no restrictions") {
            return 'https://creativecommons.org/publicdomain/mark/1.0/';
        }
        // CC licenses.
        // see <https://wiki.creativecommons.org/wiki/License_Versions>
        // See <https://wiki.creativecommons.org/wiki/Jurisdiction_Database>
        elseif (preg_match('/^cc([ -]by)?([ -]sa)?([ -]([1-4]\.0|2\.5))([ -]([a-z][a-z]))?$/', $license, $match)) {
            $byline = $match[1] ? 'by' : '';
            $sharealike = $match[2] ? 'sa' : '';
            $port = isset($match[6]) ? $match[6] : '';
            $version = $match[4];

            // just "CC" is not enough
            if (!($byline or $sharealike) or !$version) return;

            // only 1.0 had pure SA-license without BY
            if ($version == "1.0" && !$byline) {
                $condition = "sa";
            } else {
                $condition = $sharealike ? "by-sa" : "by";
            }

            // ported versions only existed in 2.0, 2.5, and 3.0
            if ($port) {
                if ($version == "1.0" or $version == "4.0") return;
                # TODO: check whether port actually exists at given version, for instance 2.5 had less ports!
            }

            // build URI
            $uri = "http://creativecommons.org/licenses/$condition/$version/";
            if ($port) $uri .= "$port/";

            return $uri;
        }
        // TODO: GFLD and other licenses
        else {
            return;
        }
    }

} // end class smt_admin_media

//////////////////////////////////////////////////////////
class smt_admin_media_analysis extends smt_admin_media {

    //////////////////////////////////////////////////////////
    function get_media_skin_percentage( $pageid ) {

        if( !function_exists('imagecreatetruecolor') ) {
            $this->error('get_media_skin_percentage: PHP GD Library NOT FOUND');
            return FALSE;
        }

        $file = $this->query_as_array(
            'SELECT * FROM media WHERE pageid = :pageid',
            array(':pageid'=>$pageid)
        );
        if( !$file ) {
            $this->error('get_media_skin_percentage: Media NOT FOUND');
            return FALSE;
        }
        $file_url = $file[0]['thumburl'];
        $this->start_timer('skin_detection');

        require_once('./use/skin-detection.php');
        $skin = new SkinDetection($file_url);

        $skin_percentage = $skin->get_skin_percentage();

        $this->end_timer('skin_detection');
        $this->update_media_skin_percentage( $pageid, $skin_percentage );
    }

    //////////////////////////////////////////////////////////
    function update_media_skin_percentage( $pageid, $skin ) {
        //$this->notice("update_media_skin_percentage( $pageid, $skin )");
        if( !$this->is_positive_number($pageid) ) {
            $this->error("update_media_skin_percentage: pageid NOT FOUND");
            return FALSE;
        }
        if( !$skin || $skin == 'NAN' || $skin == '0.0' ) {
            $skin = '0';
        }
        $result = $this->query_as_bool(
            'UPDATE media SET skin = :skin, updated = :updated WHERE pageid = :pageid',
            array(':skin'=>$skin, ':updated'=>$this->time_now(), ':pageid'=>$pageid)
        );
        if( $result ) {
            $this->notice('Updated Skin Percentage for <a href="'
            . $this->url('info') . '?i=' . $pageid . '">'
            . $pageid . '</a>: ' . $skin . ' %');
            return TRUE;
        }
        $this->error("update_media_skin_percentage( $pageid, $skin ) update FAILED");
        return FALSE;
    }

} // end class smt_admin_media_analysis

//////////////////////////////////////////////////////////
class smt_admin_category extends smt_admin_media_analysis {

    var $categories;
    var $category_id;

    //////////////////////////////////////////////////////////
    function get_categories_from_media( $pageid ) {

        if( !$pageid || !$this->is_positive_number($pageid) ) {
            $this->error('::get_categories_from_media: invalid pageid');
            return FALSE;
        }
        $call = $this->commons_api_url . '?action=query&format=json'
        . '&prop=categories'
        . '&pageids=' . $pageid
        ;
        if( !$this->call_commons($call, 'pages') ) {
            $this->error('::get_categories_from_media: nothing found');
            return FALSE;
        }
        $this->categories = @$this->commons_response['query']['pages'][$pageid]['categories'];
        $this->debug("get_categories_from_media( $pageid ) = " . sizeof($this->categories) . ' categories');
        return TRUE;
    }

    //////////////////////////////////////////////////////////
    function link_media_categories( $pageid ) {

        $this->debug("link_media_categories( $pageid )");

        if( !$pageid || !$this->is_positive_number($pageid) ) {
            $this->error('link_media_categories: invalid pageid');
            return FALSE;
        }

        if( !$this->get_categories_from_media($pageid) ) {
            $this->error('link_media_categories: unable to get categories from API');
            return FALSE;
        }

        // Remove any old category links for this media
        $this->query_as_bool(
            'DELETE FROM category2media WHERE media_pageid = :pageid',
            array(':pageid'=>$pageid)
        );

        //$this->notice("link_media_categories: DELETED ALL links in category2media");

        foreach( $this->categories as $category ) {

            if( !isset($category['title']) || !$category['title'] ) {
                $this->error('link_media_categories: ERROR: missing category title');
                continue;
            }
            if( !isset($category['ns']) || $category['ns'] != '14' ) {
                $this->error('link_media_categories: ERROR: invalid category namespace');
                continue;
            }

            $category_id = $this->get_category_id_from_name($category['title']);
            if( !$category_id ) {
                //$this->error('link_media_categories: NOT FOUND: ' . $category['title']);
                if( !$this->insert_category( $category['title'], TRUE, 1 ) ) {
                    $this->error('link_media_categories: FAILED to insert ' . $cat);
                    continue;
                }
                $category_id = $this->category_id;
                //$this->notice('link_media_categories: new category_id = ' . $category_id);
            }
            //$this->notice('link_media_categories: pageid:'.$pageid.' = ' . $category['title'] . ' == cat_id:'.$category_id);

            if( !$this->link_media_to_category( $pageid, $category_id ) ) {
                $this->error('link_media_categories: FAILED to link category');
                continue;
            }
            //$this->notice('OK: link_media_categories: p:' . $pageid . ' = c:' . $category_id . ' = ' . $category['title']);
        } // end foreach categories
        return TRUE;
    } // end function link_media_categories()

    //////////////////////////////////////////////////////////
    function link_media_to_category( $pageid, $category_id ) {

        $this->debug("link_media_to_category( $pageid, $category_id )");

        $response = $this->query_as_bool(
            'INSERT INTO category2media ( category_id, media_pageid ) VALUES ( :category_id, :pageid )',
            array('category_id'=>$category_id, 'pageid'=>$pageid)
        );
        if( !$response ) {
            $this->debug('::link_media_to_category: ERROR: insert failed. pageid: '
            . $pageid . ' cat_id: ' . $category_id);
            return FALSE;
        }
        return TRUE;
    }

    //////////////////////////////////////////////////////////
    function find_categories( $search='' ) {
        if( !$search || $search == '' || !is_string($search) ) {
            $this->error('::find_categories: invalid search string: ' . $search);
            return FALSE;
        }
        $call = $this->commons_api_url . '?action=query&format=json'
        . '&list=search'
        . '&srnamespace=14' // 6 = File   14 = Category
        . '&srprop=size|snippet' // titlesnippet|timestamp|title
        . '&srlimit=500'
        . '&srsearch=' . urlencode($search);
        if( !$this->call_commons($call, 'search') ) {
            $this->error('::find_categories: nothing found');
            return FALSE;
        }
        return TRUE;
    } // end function find_categories()

    //////////////////////////////////////////////////////////
    function get_category_info( $category ) {

        $this->debug("get_category_info( $category )");

        if( !$category || $category=='' || !is_string($category) ) {
            $this->error('::get_category_info: no category');
            return FALSE;
        }
        $call = $this->commons_api_url . '?action=query&format=json'
        . '&prop=categoryinfo'
        . '&titles=' . urlencode($category);    // cicontinue
        if( !$this->call_commons($call, 'pages') ) {
            $this->error('::get_category_info: API: nothing found');
            return FALSE;
        }
        if( isset($this->commons_response['query']['pages']) ) {

            $this->debug("get_category_info( $category ) = <pre>" . print_r($this->commons_response['query']['pages'],1) . '</pre>');

            return $this->commons_response['query']['pages'];
        }
        $this->error('::get_category_info: API: no pages');
        return FALSE;
    } // end function get_category_info()

    //////////////////////////////////////////////////////////
    function save_category_info( $category_name ) {

        $this->debug("save_category_info( $category_name )");

        $category_name = $this->category_urldecode($category_name);

        $category_row = $this->get_category($category_name);
        if( !$category_row) {
            if( !$this->insert_category($category_name, /*getinfo*/FALSE, /*local_files*/1) ) {
                $this->error('save_category_info: new category INSERT FAILED: ' . $category_name);
                return FALSE;
            }
            $this->notice('save_category_info: NEW CATEGORY: '  . $category_name);
            $category_row = $this->get_category($category_name);
        }
        //$this->notice($category_row);

        $category_info = $this->get_category_info($category_name);
        foreach( $category_info as $onesy ) {
            $category_info = $onesy; // is always just 1 result
        }
        //$this->notice($category_info);

        $bind = array();

        if( @$category_info['pageid'] != @$category_row['pageid'] ) {
            $bind[':pageid'] = $category_info['pageid'];
            //$this->notice('NEW: pageid: ' . $bind[':pageid']);
        }

        if( $category_info['categoryinfo']['files'] != $category_row['files'] ) {
            $bind[':files'] = $category_info['categoryinfo']['files'];
            //$this->notice('NEW: files: ' . $bind[':files']);
        }

        if( $category_info['categoryinfo']['subcats'] != $category_row['subcats'] ) {
            $bind[':subcats'] = $category_info['categoryinfo']['subcats'];
            //$this->notice('NEW: subcats: ' . $bind[':subcats']);
        }

        $hidden = 0;
        if( isset($category_info['categoryinfo']['hidden']) ) {
            $hidden = 1;
        }
        if( $hidden != $category_row['hidden'] ) {
            $bind[':hidden'] = $hidden;
            //$this->notice('NEW: hidden: ' . $bind[':hidden']);
        }

        $missing = 0;
        if( isset($category_info['categoryinfo']['missing']) ) {
            $missing = 1;
        }
        if( $missing != $category_row['missing'] ) {
            $bind[':missing'] = $missing;
            //$this->notice('NEW: missing: ' . $bind[':missing']);
        }

        //$local_files = $this->get_category_size( $category_name );
        //if( $local_files != $category_row['local_files'] ) {
        //    $bind[':local_files'] = $local_files;
        //    $this->notice('NEW: local_files: ' . $bind[':local_files']);
        //}

        $bind[':updated'] = $this->time_now();

        $url = '<a href="' . $this->url('category') . '?c='
            . $this->category_urlencode($this->strip_prefix($category_name))
            . '">' . $category_name . '</a>';

        if( !$bind ) {
            return TRUE; // nothing to update
        }
        $sql = 'UPDATE category SET ';
        $sets = array();
        foreach( array_keys($bind) as $set ) {
            $sets[] = str_replace(':','',$set) . ' = ' . $set;
        }
        $sql .= implode($sets, ', ');
        $sql .= ' WHERE id = :id';

        $bind[':id'] = $category_row['id'];

        $result = $this->query_as_bool($sql, $bind);

        if( $result ) {
            //$this->notice('OK: CATEGORY INFO: ' . $url);
            return TRUE;
        }
        $this->error('get_category_info: UPDATE/INSERT FAILED: ' . print_r($this->last_error,1) );
        return FALSE;


    } // end function save_category_info

    //////////////////////////////////////////////////////////
    function insert_category( $name='', $fill_info=TRUE, $local_files=0 ) {

        $this->debug("insert_category( $name, $fill_info, $local_files )");

        if( !$name ) {
            $this->error('insert_category: no name found');
            return FALSE;
        }

        if( !$this->query_as_bool(
                'INSERT INTO category (
                    name, local_files, hidden, missing, updated
                ) VALUES (
                    :name, :local_files, :hidden, :missing, :updated
                )',
                array(
                    ':name'=>$name,
                    ':local_files'=>$local_files,
                    ':hidden'=>'0',
                    ':missing'=>'0',
                    ':updated'=>$this->time_now()
                ) )

        ) {
            $this->error('insert_category: FAILED to insert: ' . $name);
            return FALSE;
        }

        $this->category_id = $this->last_insert_id;

        if( $fill_info ) {
            $this->save_category_info($name);
        }

        $this->notice('SAVED CATEGORY: ' . $this->category_id . ' = +<a href="'
            . $this->url('category') . '?c='
            . $this->category_urlencode($this->strip_prefix($name))
            . '">'
            . htmlentities($this->strip_prefix($name)) . '</a>'
            //. " (local_files=$local_files)"
        );
        return TRUE;
    }

    //////////////////////////////////////////////////////////
    function get_subcats( $category ) {
        if( !$category || $category=='' || !is_string($category) ) {
            $this->error('::get_subcats: ERROR - no category');
            return FALSE;
        }
        $this->notice('::get_subcats: ' . $category);
        $call = $this->commons_api_url . '?action=query&format=json&cmlimit=50'
        . '&list=categorymembers'
        . '&cmtype=subcat'
        . '&cmprop=title'
        . '&cmlimit=500'
        . '&cmtitle=' . urlencode($category) ;
        if( !$this->call_commons($call, 'categorymembers')
            || !isset($this->commons_response['query']['categorymembers'])
            || !is_array($this->commons_response['query']['categorymembers'])
        ) {
            $this->error('::get_subcats: Nothing Found');
            return FALSE;
        }
        foreach( $this->commons_response['query']['categorymembers'] as $subcat ) {
            $this->insert_category( $subcat['title'] );
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    function import_categories( $category_name_array ) {

        $this->notice("import_categories( category_name_array )");

        $this->begin_transaction();
        foreach( $category_name_array as $category_name ) {
            $category_name = $this->category_urldecode($category_name);
            $this->insert_category($category_name);
        }
        $this->commit();
        $this->vacuum();
    }

    //////////////////////////////////////////////////////////
    function update_category_local_files_count( $category_name ) {

        $this->debug("update_category_local_files_count( $category_name )");

        $sql = 'UPDATE category SET local_files = :local_files WHERE id = :id';
        $bind[':local_files'] = $this->get_category_size( $category_name );
        if( is_int($category_name) ) {
            $bind['id'] = $category_name;
        } else {
            $bind[':id'] = $this->get_category_id_from_name( $category_name );
        }

        if( !$bind[':id'] ) {
            $this->error("update_category_local_files_count( $category_name ) - Category Not Found in Database");
            return FALSE;
        }
        if( $this->query_as_bool($sql,$bind) ) {
            $this->notice('UPDATE CATEGORY SIZE: ' . $bind[':local_files'] . ' files in ' . $category_name);
            return TRUE;
        }
        $this->error("update_category_local_files_count( $category_name ) - UPDATE ERROR");
        return FALSE;
    }

    //////////////////////////////////////////////////////////
    function update_categories_local_files_count() {


        $sql = '
            SELECT c.id, c.local_files, count(c2m.category_id) AS size
            FROM category AS c
            LEFT JOIN category2media AS c2m ON c.id = c2m.category_id
            GROUP BY c.id
            ORDER by c.local_files ASC';

        $category_new_sizes = $this->query_as_array($sql);
        if( !$category_new_sizes ) {
            $category_new_sizes = array();
            $this->error('NOT FOUND: Updated 0 Categories Local Files count');
            return;
        }

        $updates = 0;
        $this->begin_transaction();
        foreach( $category_new_sizes as $cat ) {

            if( !$cat['size'] ) {
                //$this->delete_category( $cat['id'] );
                //continue;
            }

            if( $cat['local_files'] == $cat['size'] ) {
                continue;
            }

            if( $this->insert_category_local_files_count( $cat['id'], $cat['size'] ) ) {
                $updates++;
            } else {
                $this->error('ERROR: UPDATE FAILED: Category ID:' . $cat['id'] . ' local_files=' . $cat['size']);
            }
        }
        $this->commit();
        $this->notice('Updated ' . $updates . ' Categories Local Files count');
        $this->vacuum();
    }

    //////////////////////////////////////////////////////////
    function insert_category_local_files_count($category_id, $category_size) {
        $sql = 'UPDATE category SET local_files = :category_size, updated = :updated WHERE id = :category_id';
        $bind[':category_size'] = $category_size;
        $bind[':updated'] = $this->time_now();
        $bind[':category_id'] = $category_id;
        if( $this->query_as_bool($sql,$bind) ) {
            return TRUE;
        }
        return FALSE;
    }

    //////////////////////////////////////////////////////////
    function delete_category( $category_id ) {
        if( !$this->is_positive_number($category_id) ) { return FALSE; }
        $bind = array(':category_id'=>$category_id);
        if( $this->query_as_bool('DELETE FROM category WHERE id = :category_id', $bind) ) {
            $this->notice('DELETED Category #'. $category_id);
        } else {
            $this->error('UNABLE to delete category #' . $category_id);
        }
        if( $this->query_as_bool('DELETE FROM category2media WHERE category_id = :category_id', $bind) ) {
            $this->notice('DELETED Links to Category #'. $category_id);
        } else {
            $this->error('UNABLE to delete links to category #' . $category_id);
        }
    }


    //////////////////////////////////////////////////////////
    function empty_category_tables() {
        $sqls = array(
            'DELETE FROM category2media',
            'DELETE FROM category',
        );
        $response = array();
        foreach( $sqls as $sql ) {
            if( $this->query_as_bool($sql) ) {
                $response[] = 'OK: ' . $sql;
            } else {
                $response[] = 'FAIL: ' . $sql;
            }
        }
        $this->vacuum();
        return $response;
    }

} // end class smt_admin_category

//////////////////////////////////////////////////////////
// SMT Admin - Block
class smt_admin_block extends smt_admin_category {

    //////////////////////////////////////////////////////////
    function get_block_count() {
        $count = $this->query_as_array('SELECT count(block.pageid) AS count FROM block');
        if( isset($count[0]['count']) ) {
            return $count[0]['count'];
        }
        return 0;
    } // end function get_block_count()

    //////////////////////////////////////////////////////////
    function is_blocked( $pageid ) {
        $block = $this->query_as_array(
            'SELECT pageid FROM block WHERE pageid = :pageid',
            array(':pageid'=>$pageid)
        );
        if( isset($block[0]['pageid']) ) {
            return TRUE;
        }
        return FALSE;
    } // end function is_blocked()

} // end class smt_admin_database

//////////////////////////////////////////////////////////
// SMT Admin
class smt_admin extends smt_admin_block {

    //////////////////////////////////////////////////////////
    function __construct() {

        parent::__construct();

        $this->debug = FALSE;

        $this->commons_api_url = 'https://commons.wikimedia.org/w/api.php';

        ini_set('user_agent','Shared Media Tagger v' . __SMT__);

        $this->api_count = 0;

        $this->prop_imageinfo = '&prop=imageinfo'
        . '&iiprop=url|size|mime|thumbmime|user|userid|sha1|timestamp|extmetadata'
        . '&iiextmetadatafilter=LicenseShortName|UsageTerms|AttributionRequired|Restrictions|Artist|ImageDescription|DateTimeOriginal';

        $this->set_admin_cookie();

    }

    //////////////////////////////////////////////////////////
    function include_admin_menu() {

        $admin = $this->url('admin');
        $space = ' &nbsp; &nbsp; ';
        print '<div class="menu admin">'
        . '<a href="' . $admin . '">ADMIN</a>'
        . $space . '<a href="' . $admin . 'site.php">SITE</a>'
        . $space . '<a href="' . $admin . 'category.php">CATEGORY</a>'
        . $space . '<a href="' . $admin . 'media.php">MEDIA</a>'
        . $space . '<a href="' . $admin . 'user.php">USER</a>'
        . $space . '<a href="' . $admin . 'create.php">CREATE</a>'
        . $space . '<a href="' . $admin . 'export.php">EXPORT</a>'
        . $space . '<a href="' . $admin . 'database.php">DATABASE</a>'
        . '</div>';

    } //end function include_admin_menu()

} // end class smt_admin
