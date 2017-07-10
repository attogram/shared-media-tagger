<?php
// Shared Media Tagger
// SMT-Admin

//////////////////////////////////////////////////////////
// Admin Database
class smt_admin_database extends smt {

    //////////////////////////////////////////////////////////
    function insert_category( $name='' ) { 
        if( !$name ) { 
            $this->error('::insert_category: no name found'); 
            return FALSE; 
        }     
        $r = $this->query_as_bool( 
            'INSERT OR IGNORE INTO category (name) VALUES (:name)', 
            array(':name'=>$name) 
        ); 
        if( !$r ) {
            $this->error('::insert_category: insert into category table failed: name=' . $name);
            return FALSE; 
        }        
        $category_id = $this->last_insert_id;
        if( !$category_id ) {
            $this->notice('::insert_category: EXISTS: ' . $name);
            return FALSE;
        }
        $this->notice('SAVED ' . $name);
        $this->vacuum();
        return TRUE;
    }

    //////////////////////////////////////////////////////////
    function save_images_to_database($images='', $category='') {
        if( !$images || !is_array($images) ) { 
            $this->error('::save_images_to_database: no image array'); 
            return FALSE; 
        }
        if( !$category || !is_string($category) ) { 
            $this->error('::save_images_to_database: no category'); 
            return FALSE; 
        }
        $cr = $this->query_as_array('SELECT id FROM category WHERE name = :category', array(':category'=>$category) );
        if( !$cr || !isset($cr[0]['id']) ) {
            $this->error('::save_images_to_database: unable to get category id: ' . $category); 
            return FALSE;             
        }
        $category_id = $cr[0]['id'];
        $this->notice('::save_images_to_database: ' . sizeof($images) . ' images to insert from ' 
            . $category. ' (id:' . $category_id . ')');
            
        $errors = array();

        $this->begin_transaction();
        
        while( list(,$image) = each($images) ) {

            $i = array();
    
            $i[':pageid'] = @$image['pageid'];
            $i[':title'] = @$image['title'];    
            $i[':url'] = @$image['imageinfo'][0]['url'];
            if( !isset($i[':url']) || $i[':url'] == '' ) {
                $this->error('::save_images_to_database: ERROR SKIPPING: pageid=' 
                    . @$i[':pageid'] . ' title=' . @$i[':title'] ); 
                $errors[ $i[':pageid'] ] = $i[':title'];
                continue;
            }    
            
            $i[':descriptionurl'] = @$image['imageinfo'][0]['descriptionurl'];
            $i[':descriptionshorturl'] = @$image['imageinfo'][0]['descriptionshorturl'];
            
            $i[':imagedescription'] = @$image['imageinfo'][0]['extmetadata']['ImageDescription']['value'];
            $i[':artist'] = @$image['imageinfo'][0]['extmetadata']['Artist']['value'];
            $i[':datetimeoriginal'] = @$image['imageinfo'][0]['extmetadata']['DateTimeOriginal']['value'];
            $i[':licenseshortname'] = @$image['imageinfo'][0]['extmetadata']['LicenseShortName']['value'];
            $i[':usageterms'] = @$image['imageinfo'][0]['extmetadata']['UsageTerms']['value'];
            $i[':attributionrequired'] = @$image['imageinfo'][0]['extmetadata']['AttributionRequired']['value'];
            $i[':restrictions'] = @$image['imageinfo'][0]['extmetadata']['Restrictions']['value'];
            
            $i[':licenseuri'] = @$this->open_content_license_uri( $i[':licenseshortname'] );
            $i[':licensename'] = @$this->open_content_license_name( $i[':licenseuri'] );

            $i[':size'] = @$image['imageinfo'][0]['size'];
            $i[':width'] = @$image['imageinfo'][0]['width'];
            $i[':height'] = @$image['imageinfo'][0]['height'];
            $i[':sha1'] = @$image['imageinfo'][0]['sha1'];
            $i[':mime'] = @$image['imageinfo'][0]['mime'];
            
            $i[':thumburl'] = @$image['imageinfo'][0]['thumburl'];
            $i[':thumbwidth'] = @$image['imageinfo'][0]['thumbwidth'];
            $i[':thumbheight'] = @$image['imageinfo'][0]['thumbheight'];
            $i[':thumbmime'] = @$image['imageinfo'][0]['thumbmime'];
            
            $i[':user'] = @$image['imageinfo'][0]['user'];
            $i[':userid'] = @$image['imageinfo'][0]['userid'];
            
            $i[':duration'] = @$image['imageinfo'][0]['duration'];
            $i[':timestamp'] = @$image['imageinfo'][0]['timestamp'];

            if( isset($i['mime']) && $i['mime'] == 'application/pdf' ) { 
                $this->notice('::save_images_to_database() ERROR: skipping pdf'); 
                continue;
            }
                    
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

            $r = $this->query_as_bool($sql, $i);
            
            if( $r === FALSE) { 
                $this->error('::save_images_to_database: FAILED insert into media table'); 
                $this->error('::save_images_to_database: SQL: ' . $sql); 
                $this->error('::save_images_to_database: BIND i: ' . print_r($i,1) ); 
                exit;
                continue;
            } else { 
                $this->notice('::: SAVED: ' . $i[':pageid'] . ' ' . $i[':title'] );
            }
            
            // connect category
            $r = $this->query_as_bool(
                'INSERT OR REPLACE INTO category2media ( category_id, media_pageid ) VALUES ( :category_id, :pageid )',
                array('category_id'=>$category_id, 'pageid'=>$i[':pageid'])
            );
            if( !$r ) { 
                $this->error('::save_images_to_database: insert into category2media table failed. pageid: '
                . $i[':pageid']);
            }
            
        }
        
        $this->commit();
        $this->vacuum();
        
        if( $errors ) { $this->error($errors); }
        return TRUE;
    }

    //////////////////////////////////////////////////////////
    function delete_image($pageid) { 
        if( !$this->query_as_bool(
                'DELETE FROM images WHERE pageid = :pageid LIMIT 1', 
                array(':pageid'=>$pageid) 
            ) ) {
            return FALSE; } 
        $this->debug('delete_image: DELETED ' . $pageid);
        $this->vacuum();
        $this->get_image_count();
        return TRUE;
    }
    
    //////////////////////////////////////////////////////////
    function empty_media_tables() {
        $sql = array(
            'DELETE FROM tagging',
            'DELETE FROM category2media',
            'DELETE FROM media',
        );
        $r = array();
        foreach( $sql as $s ) {
            if( $this->query_as_bool($s, $bind=array() ) ) {
                $r[] = 'OK: ' . $s;
            } else {
                $r[] = 'FAIL: ' . $s;
            }
        }
        $this->vacuum();
        return $r;
    }
    
    //////////////////////////////////////////////////////////
    function empty_category_tables() {
        $sql = array(
            'DELETE FROM category2media',
            'DELETE FROM category',
        );
        $r = array();
        foreach( $sql as $s ) {
            if( $this->query_as_bool($s, $bind=array() ) ) {
                $r[] = 'OK: ' . $s;
            } else {
                $r[] = 'FAIL: ' . $s;
            }
        }
        $this->vacuum();
        return $r;
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

'default_site' => "INSERT INTO site (id, name, about) VALUES (1, 'Shared Media Tagger', 'This is a demonstration of the Shared Media Tagger software.')",

'default_tag1' => "INSERT INTO tag (id, position, name, display_name) VALUES (1, 1, '☹️ Worst',  '☹️')",
'default_tag2' => "INSERT INTO tag (id, position, name, display_name) VALUES (2, 2, '🙁 Bad',    '🙁')",
'default_tag3' => "INSERT INTO tag (id, position, name, display_name) VALUES (3, 3, '😐 Unsure', '😐')",
'default_tag4' => "INSERT INTO tag (id, position, name, display_name) VALUES (4, 4, '🙂 Good',   '🙂')",
'default_tag5' => "INSERT INTO tag (id, position, name, display_name) VALUES (5, 5, '😊 Best',   '😊')",


); // end tables array

        $r = false;
        while( list($name,$create) = each($tables) ) {
            if( $this->query_as_bool($create) ) {
                $r .= "<br /><b>OK: $name</b>: $create";
            } else {
                $r .= "<br /><b>FAIL: $name<b/>: $create";
            }
        }
        $this->vacuum();
        return $r;
    }

    //////////////////////////////////////////////////////////
    function drop_tables() { 

        $sqls = array(
        'DROP TABLE IF EXISTS site',
        'DROP TABLE IF EXISTS tag',
        'DROP TABLE IF EXISTS tagging',
        'DROP TABLE IF EXISTS category',
        'DROP TABLE IF EXISTS category2media',
        'DROP TABLE IF EXISTS media',
        'DROP TABLE IF EXISTS contact',
        );
        $r = false;
        while( list(,$sql) = each($sqls) ) {
            if( $this->query_as_bool($sql) ) {
                $r .= "<b>OK:</b> $sql<br />";
            } else {
                $r .= "<b>FAIL:<b/> $sql<br />";
            }
        }
        $this->vacuum();
        return $r;
    }


} // END class smt_api

//////////////////////////////////////////////////////////
// Wikimedia Commons API
class smt_commons_API extends smt_admin_database {

    //////////////////////////////////////////////////////////
    function call_commons($url, $key='') {
        $this->notice('::call_commons: key='.$key.' url=<a target="commons" href="'.$url.'">'.$url.'</a>');

		if( !$url ) { $this->error('::call_commons: ERROR: no url'); return FALSE; } 
        //if( !$key ) { $this->error('::call_commons: ERROR: no key'); return FALSE; } 
        $x = file_get_contents($url);
        if( $x === FALSE ) {
            $this->error('::call_commons: ERROR: get failed');
            return FALSE;
        }
        $this->debug('::call_commons: called: ' . $url);
        $this->debug($x);
        $this->api_count++;
        $d = json_decode($x,TRUE); // assoc
        if( !$d ) {
            $this->error('::call_commons: ERROR: json_decode failed. Error: ' . json_last_error() );
            $this->error('::call_commons: ERROR: ' . $this->smt_json_last_error_msg() );
            return FALSE;
        }
        $this->commons_response = $d;
        //$this->notice('::call_commons: response:' . print_r($this->commons_response,1) );
        
        if( !$d['query'][$key] || !is_array($d['query'][$key])  ) { 
            $this->error("::call_commons: ERROR: missing key: $key");
            //$this->notice($this->commons_response);
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
        
        return $this->commons_response;
        
    } // end function call_commons()

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
        . '&srlimit=500' // max 50 for bots
        . '&srsearch=' . urlencode($search);
        $r = $this->call_commons($call, 'search');
        //$this->notice('::find_categories: RAW return: ' . print_r($this->commons_response,1) );
        if( !$r || !is_array($r) ) {
            $this->error('::find_categories: nothing found');
            return FALSE;
        }
        return $this->commons_response['query']['search'];
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
        $r = $this->call_commons($call, 'pages');
        //$this->notice('::get_category_info: RAW return: ' . print_r($this->commons_response,1) );
        if( !$r || !is_array($r) ) {
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
        $d = $this->call_commons($call, 'categorymembers');
        if( !$d || !isset($this->commons_response['query']['categorymembers']) || !is_array($this->commons_response['query']['categorymembers']) ) {
            $this->error('::get_subcats: Nothing Found');
            return FALSE;
        }
        foreach( $this->commons_response['query']['categorymembers'] as $subcat ) {
            $this->insert_category( $subcat['title'] );
        }
    }
    
    //////////////////////////////////////////////////////////
    function get_media_from_category( $category='' ) {
        
        $this->notice("::get_media_from_category( 1: $category )");
        $category = trim($category);
        if( !$category ) { return false; } 
        $category = ucfirst($category);
        if ( !preg_match('/^[Category:]/i', $category)) { 
            $category = 'Category:' . $category; 
        } 
        $this->notice("::get_media_from_category( 2: $category )");
        
        $categorymembers = $this->get_api_categorymembers( $category );
        if( !$categorymembers ) { 
            $this->error('::get_media_from_category: No Media Found');
            return FALSE;
        }
        
        $chunks = array_chunk( $categorymembers, 50 );
        foreach( $chunks as $chunk ) {
            $this->notice('::get_media_from_category: TRY CHUNK: ' . sizeof($chunk));
            $this->save_images_to_database( $this->get_api_imageinfo($chunk), $category );    
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
        $call = $this->call_commons($url, 'categorymembers');
        if( !$call || !isset( $this->commons_response['query']['categorymembers']) ) { 
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
        $r = $this->call_commons($call, 'pages');
        if( !$r || !isset($this->commons_response['query']['pages']) ) { 
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
    function get_api_images_by_search($search, $limit='50') {
        
        if( !$limit || !$this->is_positive_number($limit) ) { return false; }
        if( !$search || $search=='' ) { return false; } 

        $call = $this->commons_api_url . '?action=query&format=json'
        . '&list=search'
        . '&srnamespace=6|14' // 6 = File   14 = Category
        . '&srprop='
        . '&srlimit=' . $limit // max 50
        . '&srsearch=' . urlencode($search)
        ;

        $r = $this->call_commons($call, 'search');
        $totalhits = $this->commons_response['query']['searchinfo']['totalhits'];
        $this->debug("::get_api_images_by_search: totalhits=$totalhits");

        $file = array();
        $category = array();
        while( list(,$x) = each($this->commons_response['query']['search']) ) { 
            switch( $x['ns'] ) { 
                case '6': $file[] = $x['title']; break;
                case '14': $category[] = $x['title']; break;
            } 
        } 

        reset($category);
        while( list(,$x) = each($category) ) {
            $this->insert_category($x);
        }     

        $this->notice('Categories: ' . print_r($category,1));
        $this->notice('Files: ' . print_r($file,1));
        
        $ir = $this->get_api_image(false, implode('|',$file) );

        $this->notice('IMPORT DONE: ' . print_r($ir,1) );
		
		$this->include_footer();
        exit;
    }

    //////////////////////////////////////////////////////////
    function get_api_image($pageids='',$titles='') {
        $this->debug("::get_api_image($pageids)");
        //if( !$pageids || !$this->is_number($pageids) ) {  // needs to allow  123|456|789  format
        if( !$pageids && !$titles) { 
            $this->debug('::get_api_image: Error: missing pageids or titles');
            return false; 
        } 
        if( $pageids && $titles) { 
            $this->debug('::get_api_image: Error: pageids AND titles');
            return false; 
        } 
    
        $call = $this->commons_api_url . '?action=query&format=json&iilimit=500'
            . $this->prop_imageinfo
            . '&iiurlwidth=' . $this->size_medium;

        if( $pageids ){ $call .= '&pageids=' . $pageids; } 
        if( $titles ){ $call .= '&titles=' . urlencode($titles); } 

        $r = $this->call_commons($call, 'pages');
        if( !$r ) { 
            $this->error('::get_api_image: ERROR call');
            return false;
        } 
        $this->save_images_to_database($r);
        return $r;
    }

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

}

//////////////////////////////////////////////////////////
// SMT Admin
class smt_admin extends smt_commons_API {
    var $commons_api_url;
    var $commons_response;
    var $prop_imageinfo;
    var $totalhits;
    var $continue;
    var $sroffset;
    var $batchcomplete;
    var $categories;
    var $api_count;

    //////////////////////////////////////////////////////////
    function __construct( $title='' ) {
        parent::__construct( $title );
        $this->commons_api_url = 'https://commons.wikimedia.org/w/api.php';
        ini_set('user_agent','Shared Media Tagger v' . __SMT__);
        $this->api_count = 0;
        $this->prop_imageinfo = '&prop=imageinfo'
        . '&iiprop=url|size|mime|thumbmime|user|userid|sha1|timestamp|extmetadata'         
        . '&iiextmetadatafilter=LicenseShortName|UsageTerms|AttributionRequired|Restrictions|Artist|ImageDescription|DateTimeOriginal';
        $this->set_admin_cookie();
    }
    
    //////////////////////////////////////////////////////////
    function set_admin_cookie() {
        if( isset($_COOKIE['admin']) && $_COOKIE['admin'] == '1' ) {
            return;
        }
        setcookie('admin','1',time()+60*60,'/'); // 1 hour admin cookie
        //$this->notice('Admin cookie set');
    }

    //////////////////////////////////////////////////////////
    function include_admin_menu() {
        
        $a = $this->url('admin');
?>
<div class="menu admin" >
<a href="<?php print $a; ?>">ADMIN</a>
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a href="<?php print $a; ?>site.php">SITE</a>
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a href="<?php print $a; ?>category.php">CATEGORY</a>
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a href="<?php print $a; ?>media.php">MEDIA</a>
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a href="<?php print $a; ?>database.php">DATABASE</a>
</div>
<?php        
    } //end function include_admin_menu()

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
            $by      = $match[1] ? 'by' : '';
            $sa      = $match[2] ? 'sa' : '';
            $port    = isset($match[6]) ? $match[6] : '';
            $version = $match[4];
            
            // just "CC" is not enough
            if (!($by or $sa) or !$version) return;
            
            // only 1.0 had pure SA-license without BY
            if ($version == "1.0" && !$by) {
                $condition = "sa";
            } else {
                $condition = $sa ? "by-sa" : "by";
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

}

