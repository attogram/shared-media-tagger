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
        setcookie('admin','1',time()+60*60,'/'); // 1 hour admin cookie
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
    CONSTRAINT su UNIQUE (name) )",

'tag' =>
    "CREATE TABLE IF NOT EXISTS 'tag' (
    'id' INTEGER PRIMARY KEY,
    'position' INTEGER,
    'name' TEXT,
    'display_name' TEXT,
    'color' TEXT,
    'bgcolor' TEXT,
    'hover_color' TEXT,
    'hover_bgcolor' TEXT,
    'hover_text' TEXT,
    'padding' TEXT,
    'font_size' TEXT )",

'tagging' =>
    "CREATE TABLE IF NOT EXISTS 'tagging' (
    'id' INTEGER PRIMARY KEY,
    'tag_id' INTEGER,
    'media_pageid' INTEGER,
    'count' INTEGER,
    CONSTRAINT tmu UNIQUE (tag_id, media_pageid) )",

'category' =>
    "CREATE TABLE IF NOT EXISTS 'category' (
    'id' INTEGER PRIMARY KEY,
    'name' TEXT,
    'pageid' INTEGER,
    'files' INTEGER,
    'subcats' INTEGER,
    CONSTRAINT cu UNIQUE (name) )",

'category2media' =>
    "CREATE TABLE IF NOT EXISTS 'category2media' (
    'id' INTEGER PRIMARY KEY,
    'category_id' INTEGER,
    'media_pageid' INTEGER,
    CONSTRAINT tmu UNIQUE (category_id, media_pageid) )",

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
    'size' TEXT,
    'width' INTEGER,
    'height' INTEGER,
    'sha1' TEXT,
    'mime' TEXT,
    'thumburl' TEXT,
    'thumbwidth' INTEGER,
    'thumbheight' INTEGER,
    'thumbmime' INTEGER,
    'user' TEXT,
    'userid' TEXT,
    'duration' TEXT,
    'timestamp' TEXT )",

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
    'thumb' TEXT )",

'user' =>
    "CREATE TABLE IF NOT EXISTS 'user' (
    'id' INTEGER PRIMARY KEY,
    'ip' TEXT,
    'host' TEXT,
    'user_agent' TEXT,
    'page_views' INTEGER,
    'last' TEXT,
    CONSTRAINT uc UNIQUE (ip, host, user_agent) )",

'user_tagging' =>
    "CREATE TABLE IF NOT EXISTS 'user_tagging' (
    'id' INTEGER PRIMARY KEY,
    'user_id' INTEGER,
    'tag_id' INTEGER,
    'media_pageid' INTEGER,
    'count' INTEGER,
    CONSTRAINT utu UNIQUE (user_id, tag_id, media_pageid) )",


// Default Demo Site setup

'default_site' => "INSERT INTO site (id, name, about) VALUES (1, 'Shared Media Tagger Demo', 'This is a demonstration of the Shared Media Tagger software.')",

'default_tag1' => "INSERT INTO tag (id, position, name, display_name) VALUES (1, 1, '☹️ Worst',  '☹️')",
'default_tag2' => "INSERT INTO tag (id, position, name, display_name) VALUES (2, 2, '🙁 Bad',    '🙁')",
'default_tag3' => "INSERT INTO tag (id, position, name, display_name) VALUES (3, 3, '😐 Unsure', '😐')",
'default_tag4' => "INSERT INTO tag (id, position, name, display_name) VALUES (4, 4, '🙂 Good',   '🙂')",
'default_tag5' => "INSERT INTO tag (id, position, name, display_name) VALUES (5, 5, '😊 Best',   '😊')",

'c1' => "INSERT INTO category (id,name,pageid) VALUES (1,'Category:Test patterns',202140);",
'c2' => "INSERT INTO category (id,name,pageid) VALUES (2,'Category:Calibration videos',8461838);",
'c3' => "INSERT INTO category (id,name,pageid) VALUES (3,'Category:Audio files for calibration',14878939);",

'm1' => <<<EOT

INSERT INTO "media" ("pageid","title","url","descriptionurl","descriptionshorturl","imagedescription","artist","datetimeoriginal","licenseuri","licensename","licenseshortname","usageterms","attributionrequired","restrictions","size","width","height","sha1","mime","thumburl","thumbwidth","thumbheight","thumbmime","user","userid","duration","timestamp") VALUES ('45898475','File:TP-CBS-rep.png','https://upload.wikimedia.org/wikipedia/commons/b/b8/TP-CBS-rep.png','https://commons.wikimedia.org/wiki/File:TP-CBS-rep.png','https://commons.wikimedia.org/w/index.php?curid=45898475','Test pattern design used by CBS owned stations from the late 1940''s and also by stations not necessarily affiliated with the network.','<a href="//commons.wikimedia.org/w/index.php?title=User:Wbwn&amp;action=edit&amp;redlink=1" class="new" title="User:Wbwn (page does not exist)">Wbwn</a>','2015-12-25','http://creativecommons.org/licenses/by-sa/4.0/','CC BY-SA 4.0','CC BY-SA 4.0','Creative Commons Attribution-Share Alike 4.0','true','','97501','648','486','a25a3839ed8412238bbde244fed172f2f2a40269','image/png','https://upload.wikimedia.org/wikipedia/commons/thumb/b/b8/TP-CBS-rep.png/325px-TP-CBS-rep.png','325','244','image/png','Wbwn','1797161',NULL,'2015-12-26T20:09:45Z');

EOT
,

'c2m1' => <<<EOT
INSERT INTO "category2media" ("id","category_id","media_pageid") VALUES ('1','1','45898475');
EOT
,


); // end tables array

        $response = false;
        while( list($name,$create) = each($tables) ) {
            if( $this->query_as_bool($create) ) {
                $response .= "<br /><b>OK: $name</b>: $create";
            } else {
                $response .= "<br /><b>FAIL: $name<b/>: $create";
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
        $this->notice('::call_commons: key='.$key.' url=<a target="commons" href="'.$url.'">'.$url.'</a>');
        if( !$url ) { $this->error('::call_commons: ERROR: no url'); return FALSE; }
        $get_response = file_get_contents($url);
        if( $get_response === FALSE ) {
            $this->error('::call_commons: ERROR: get failed');
            return FALSE;
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
        if( !$pageid || !$this->is_positive_number($pageid) ) {
            $this->error('add_media: Invalid PageID');
            return FALSE;
        }
        $response = '<div style="background-color:lightgreen; padding:10px;">'
        . '<p>Add Media: pageid: <b>' . $pageid . '</b></p>';

        // Get media
        $media = $this->get_api_imageinfo( array($pageid), /*$recurse_count=*/0 );
        //$this->notice($media);

        if( !$media ) {
            $response .= '<p>ERROR: failed to get media info</p></div>';
            return $response;
        }
        $response .= '<p>OK: media: <b>' . @$media[$pageid]['title'] . '</b></p>';

        // Remove media - old version, if present
        $this->query_as_bool(
            'DELETE FROM media WHERE pageid = :pageid',
            array(':pageid'=>$pageid)
        );

        // Save media
        if( !$this->save_media_to_database($media) ) {
            $response .= '<p>ERROR: failed to save media to database</p></div>';
            return $response;
        }
        $response .= '<p>OK: Saved media: <b><a href="' . $this->url('info')
        . '?i=' . $pageid . '">info.php?i=' . $pageid . '</a></b></p>';


        // Get Categories
        if( !$this->get_categories_from_media( $pageid ) ) {
            $response .= '<p>ERROR: failed to get categories</p></div>';
            return $response;
        }
        $cats = @$this->commons_response['query']['pages'][$pageid]['categories'];
        if( !$cats || !is_array($cats) ) {
            $response .= '<p>No Categories found</p></div>';
            return $response;
        }
        $found_categories = array();
        foreach( $cats as $cat ) {
            if( !isset($cat['title']) || !$cat['title'] ) {
                $this->error('add_media: ERROR: missing category title');
                continue;
            }
            if( !isset($cat['ns']) || $cat['ns'] != '14' ) {
                $this->error('add_media: ERROR: invalid category namespace');
                continue;
            }
            $found_categories[] = $cat['title'];
        }

        // Remove old category list - if present
        $this->query_as_bool(
            'DELETE FROM category2media WHERE media_pageid = :pageid',
            array(':pageid'=>$pageid)
        );

        foreach( $found_categories as $cat ) {

            $cat_id = $this->get_category_id_from_name($cat);
            if( !$cat_id ) {
                if( !$this->insert_category( $cat ) ) {
                    $this->error('add_media: ERROR: can not insert ' . $cat);
                    continue;
                }
                $cat_id = $this->last_insert_id;
            }

            if( !$this->link_media_category( $pageid, $cat_id ) ) {
                $this->error("add_media: ERROR: can not link pageid:$pageid to category:$cat_id");
                continue;
            }

            $response .= 'OK: +<a href="' . $this->url('category')
            . '?c=' . $this->category_urlencode($this->strip_prefix($cat)) . '">'
            . $this->strip_prefix($cat) . '</a><br />';

        } // end foreach cats

        //$response .= $this->display_thumbnail_box($media[$pageid]);

        $response .= '</div>';
        return $response;
    }

    //////////////////////////////////////////////////////////
    function save_media_to_database($images='', $category='') {

        //$this->notice('save_media_to_database: ' . print_r($images,1) );

        if( !$images || !is_array($images) ) {
            $this->error('::save_media_to_database: no media array');
            return FALSE;
        }

        $category_id = 0;
        if( $category ) {
            //if( !$category || !is_string($category) ) {
            //  $this->error('::save_media_to_database: no category');
            //  return FALSE;
            //}
            $cat_id = $this->query_as_array('SELECT id FROM category WHERE name = :category', array(':category'=>$category) );
            if( !$cat_id || !isset($cat_id[0]['id']) ) {
                $this->error('::save_media_to_database: unable to get category id: ' . $category);
                return FALSE;
            }
            $category_id = $cat_id[0]['id'];
        }

        $this->notice('::save_media_to_database: ' . sizeof($images) . ' images to insert. Category: '
            . $category. ' (category_id:' . $category_id . ')');

        $errors = array();

        $this->begin_transaction();

        while( list(,$image) = each($images) ) {

            //$this->notice(':;save_media_to_database: LOOP: image=' . print_r($image,1));
            $new = array();

            $new[':pageid'] = @$image['pageid'];
            $new[':title'] = @$image['title'];
            $new[':url'] = @$image['imageinfo'][0]['url'];
            if( !isset($new[':url']) || $new[':url'] == '' ) {
                $this->error('::save_media_to_database: ERROR: NO URL: SKIPPING: pageid='
                    . @$new[':pageid'] . ' title=' . @$new[':title'] );
                $errors[ $new[':pageid'] ] = $new[':title'];
                continue;
            }

            $new[':descriptionurl'] = @$image['imageinfo'][0]['descriptionurl'];
            $new[':descriptionshorturl'] = @$image['imageinfo'][0]['descriptionshorturl'];

            $new[':imagedescription'] = @$image['imageinfo'][0]['extmetadata']['ImageDescription']['value'];
            $new[':artist'] = @$image['imageinfo'][0]['extmetadata']['Artist']['value'];
            $new[':datetimeoriginal'] = @$image['imageinfo'][0]['extmetadata']['DateTimeOriginal']['value'];
            $new[':licenseshortname'] = @$image['imageinfo'][0]['extmetadata']['LicenseShortName']['value'];
            $new[':usageterms'] = @$image['imageinfo'][0]['extmetadata']['UsageTerms']['value'];
            $new[':attributionrequired'] = @$image['imageinfo'][0]['extmetadata']['AttributionRequired']['value'];
            $new[':restrictions'] = @$image['imageinfo'][0]['extmetadata']['Restrictions']['value'];

            $new[':licenseuri'] = @$this->open_content_license_uri( $new[':licenseshortname'] );
            $new[':licensename'] = @$this->open_content_license_name( $new[':licenseuri'] );

            $new[':size'] = @$image['imageinfo'][0]['size'];
            $new[':width'] = @$image['imageinfo'][0]['width'];
            $new[':height'] = @$image['imageinfo'][0]['height'];
            $new[':sha1'] = @$image['imageinfo'][0]['sha1'];
            $new[':mime'] = @$image['imageinfo'][0]['mime'];

            $new[':thumburl'] = @$image['imageinfo'][0]['thumburl'];
            $new[':thumbwidth'] = @$image['imageinfo'][0]['thumbwidth'];
            $new[':thumbheight'] = @$image['imageinfo'][0]['thumbheight'];
            $new[':thumbmime'] = @$image['imageinfo'][0]['thumbmime'];

            $new[':user'] = @$image['imageinfo'][0]['user'];
            $new[':userid'] = @$image['imageinfo'][0]['userid'];

            $new[':duration'] = @$image['imageinfo'][0]['duration'];
            $new[':timestamp'] = @$image['imageinfo'][0]['timestamp'];

            //if( isset($new['mime']) && $new['mime'] == 'application/pdf' ) {
            //    $this->notice('::save_media_to_database() ERROR: skipping pdf');
            //    continue;
            //}

            $sql = "INSERT OR REPLACE INTO MEDIA (
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

            $response = $this->query_as_bool($sql, $new);

            if( $response === FALSE) {
                $this->error('::save_media_to_database: FAILED insert into media table');
                $this->error('::save_media_to_database: SQL: ' . $sql);
                $this->error('::save_media_to_database: BIND i: ' . print_r($new,1) );
                $this->error("STOPPING IMPORT");
                exit;
            }

            $this->notice('::: SAVED: ' . $new[':pageid'] . ' ' . $new[':title'] );

            // connect category
            if( $category ) {
                $response = $this->link_media_category( $new[':pageid'], $category_id );
                if( !$response ) {
                    $this->error('::save_media_to_database: insert into category2media table failed. pageid: '
                    . $new[':pageid']);
                }
            } // end if category

        } // end while each media

        $this->commit();
        $this->vacuum();

        if( $errors ) { $this->error($errors); }
        return TRUE;
    }

    ////////////////////////////////////////////////////
    function delete_media( $pageid ) {
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
                $response .= '<br />OK: ' . $sql;
            } else {
                $response .= '<br />ERROR: ' . $sql;
            }
        }

        $sql = 'INSERT INTO block (pageid, title, thumb) VALUES (:pageid, :title, :thumb)';
        $bind = array(
            ':pageid'=>$pageid,
            ':title'=>@$media[0]['title'],
            ':thumb'=>@$media[0]['thumburl'],
        );
        if( $this->query_as_bool($sql, $bind) ) {
            $response .= '<br />OK: ' . $sql;
        } else {
            $response .= '<br />ERROR: ' . $sql;
        }

        $response .= '</div>';
        return $response;
    }

    //////////////////////////////////////////////////////////
    function get_media_from_category( $category='' ) {

        $category = trim($category);
        if( !$category ) { return false; }
        $category = ucfirst($category);
        if ( !preg_match('/^[Category:]/i', $category)) {
            $category = 'Category:' . $category;
        }

        $this->notice("::get_media_from_category( $category )");

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
                $this->error("Skipping BLOCKED Media: " . $bpageid['pageid']);
            }
        }

        $chunks = array_chunk( $categorymembers, 50 );
        foreach( $chunks as $chunk ) {
            $this->notice('::get_media_from_category: TRY CHUNK: ' . sizeof($chunk));
            $this->save_media_to_database( $this->get_api_imageinfo($chunk), $category );
        }

    } // end function get_media_from_category()

    //////////////////////////////////////////////////////////
    function get_api_categorymembers( $category ) {
        $this->notice('::get_api_categorymembers: ' . $category);
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
            $this->notice('::get_api_categorymembers: No files found');
            return array();
        }
        $this->notice('::get_api_categorymembers: GOT: ' . sizeof($pageids) );
        return $pageids;
    }

    //////////////////////////////////////////////////////////
    function get_api_imageinfo( $pageids, $recurse_count=0 ) {
        $this->notice('::get_api_imageinfo: pageids size: ' . sizeof($pageids) . ' recurse=' . $recurse_count);
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
        $this->notice('::get_api_imageinfo: CALL #' . $recurse_count . ': GOT: ' . sizeof($pages) . ' files');

        $errors = array();
        foreach( $pages as $media ) {
            if( !isset($media['imageinfo'][0]['url']) ) {
                $errors[] = $media['pageid'];
                unset( $pages[ $media['pageid'] ] );
            }
        }

        if( !$recurse_count ) {
            $this->notice('::get_api_imageinfo: NO RECURSION.  returning');
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
class smt_admin_category extends smt_admin_media {

    var $categories;

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
        return TRUE;
    }

    //////////////////////////////////////////////////////////
    function link_media_category( $pageid, $category_id ) {
        $response = $this->query_as_bool(
            'INSERT OR REPLACE INTO category2media ( category_id, media_pageid ) VALUES ( :category_id, :pageid )',
            array('category_id'=>$category_id, 'pageid'=>$pageid)
        );
        if( !$response ) {
            $this->error('::link_media_category: ERROR: insert failed. pageid: '
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
        if( !$category || $category=='' || !is_string($category) ) {
            $this->error('::get_category_info: ERROR - no category');
            return FALSE;
        }
        $this->notice('::get_category_info: ' . $category);
        $call = $this->commons_api_url . '?action=query&format=json'
        . '&prop=categoryinfo'
        . '&titles=' . urlencode($category);    // cicontinue
        if( !$this->call_commons($call, 'pages') ) {
            $this->error('::get_category_info: nothing found');
            return FALSE;
        }
        return $this->commons_response['query']['pages'];
    } // end function get_category_info()

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

    //////////////////////////////////////////////////////////
    function insert_category( $name='' ) {
        if( !$name ) {
            $this->error('::insert_category: no name found');
            return FALSE;
        }
        $response = $this->query_as_bool(
            'INSERT INTO category (name) VALUES (:name)',
            array(':name'=>$name)
        );
        if( !$response ) {
            $this->error('::insert_category: insert into category table failed: name=' . $name);
            return FALSE;
        }
        if( !$this->last_insert_id ) {
            $this->notice('::insert_category: EXISTS: ' . $name);
            return FALSE;
        }
        $this->notice('::insert_category: SAVED ' . $this->last_insert_id . ' = ' . $name);
        $this->vacuum();
        return TRUE;
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
        . $space . '<a href="' . $admin . 'database.php">DATABASE</a>'
        . '</div>';

    } //end function include_admin_menu()

} // end class smt_admin
