<?php
// Shared Media Tagger (SMT)

define('__SMT__', '0.7.57');

ob_start('ob_gzhandler');

$init = __DIR__.'/_setup.php'; // optional Site Setup options
if( is_readable($init) ) {
    include($init);
}

//////////////////////////////////////////////////////////
// SMT - Utils
class smt_utils {

    var $debug; // debug mode TRUE / FALSE;
    var $protocol; // http: or https:
    var $timer;
    var $timer_results;
    var $links; // array of [page_name] = page_url

    //////////////////////////////////////////////////////////
    function time_now() {
        return gmdate('Y-m-d H:i:s');
    }

    //////////////////////////////////////////////////////////
    function start_timer( $name ) {
        $this->timer[$name] = microtime(1);
    }

    //////////////////////////////////////////////////////////
    function end_timer( $name ) {
        if( !isset($this->timer[$name]) ) {
            $this->timer_results[$name] = 0;
            return;
        }
        $result = microtime(1) - $this->timer[$name];
        if( isset($this->timer_results[$name]) ) {
            $this->timer_results[$name] += $result;
            return;
        }
        $this->timer_results[$name] = $result;
    }

    //////////////////////////////////////////////////////////
    function log_message( $message, $type ) {
        switch( $type ) {
            case 'debug':  $class = 'debug';  $head = ''; break;
            case 'notice': $class = 'notice'; $head = ''; break;
            case 'error':  $class = 'error';  $head = 'ERROR:'; break;
            case 'fail':   $class = 'fail';   $head = 'GURU MEDITATION FAILURE:'; break;
            default: return;
        }
        if( is_array($message) ) {
            $message = '<pre>' . htmlentities(print_r($message,1)) . '</pre>';
        }
        print '<div class="message ' . $class . '"><b>' . $head . '</b> ' . $message . '</div>';
    }

    //////////////////////////////////////////////////////////
    function debug( $message='' ) {
        if( !$this->debug ) { return; }
        $this->log_message( $message, 'debug' );
    }

    //////////////////////////////////////////////////////////
    function notice( $message='' ) {
        $this->log_message( $message, 'notice' );
    }

    //////////////////////////////////////////////////////////
    function error( $message='' ) {
        $this->log_message( $message, 'error' );
    }

    //////////////////////////////////////////////////////////
    function fail( $message='' ) {
        $this->log_message( $message, 'fail' );
        exit;
    }

    //////////////////////////////////////////////////////////
    function fail404 ( $message='', $extra='' ) {
        header('HTTP/1.0 404 Not Found');
        $this->include_header( /*show_site_header*/FALSE );
        $this->include_menu( /*show_counts*/FALSE );
        if( !$message || !is_string($message) ) {
            $message = '404 Not Found';
        }
        print '<div class="box white center" style="padding:30px 0px 30px 0px;">'
        . '<h1>' . $message . '</h1>';
        if( $extra && is_string($extra) ) {
            print '<br />' . $extra;
        }
        print '</div>';
        $this->include_footer( /*show_site_footer*/FALSE );
        exit;
    }

    //////////////////////////////////////////////////////////
    function is_positive_number( $number='') {
        if ( preg_match('/^[0-9]*$/', $number )) { return TRUE; }
        return FALSE;
    }

    /////////////////////////////////////////////////////////
    function truncate( $string, $length=50 ) {
        if( strlen($string) <= $length ) {
            return $string;
        }
        return substr( $string, 0, $length-11 ) . '...' . substr( $string, -8);
    }

    //////////////////////////////////////////////////////////
    function centerpad( $string, $length ) {
        if( !$length ) {
            return $string;
        }
        if( strlen($string) >= $length ) {
            return $string;
        }
        return str_pad($string, $length, ' ', STR_PAD_BOTH);
    }

    //////////////////////////////////////////////////////////
    function get_protocol() {
        if( isset($this->protocol) ) {
            return $this->protocol;
        }
        if(
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        ) {
            return $this->protocol = 'https:';
        }
        return $this->protocol = 'http:';
    }

    //////////////////////////////////////////////////////////
    function seconds_to_time( $raw_seconds ) {
        if( !$raw_seconds ) { return '0 seconds'; }
        $hours = floor($raw_seconds / 3600);
        $minutes = floor(($raw_seconds / 60) % 60);
        $seconds = $raw_seconds % 60;
        $seconds += round( $raw_seconds - floor($raw_seconds), 2);
        $resonse = array();
        if( $hours ) { $response[] = $hours . ' hours'; }
        if( $minutes ) { $response[] = $minutes . ' minutes'; }
        if( $seconds ) { $response[] = $seconds . ' seconds'; }
        return implode($response, ', ');
    }

    //////////////////////////////////////////////////////////
    function is_selected($one, $two) {
        if( $one == $two ) {
            return ' selected="selected"';
        }
    }

    //////////////////////////////////////////////////////////
    function url( $link='' ) {
        if( !$link || !isset($this->links[$link]) ) {
            $this->error("::url: Link Not Found: $link");
            return FALSE;
        }
        return $this->links[$link];
    }

} //end class smt_utils

//////////////////////////////////////////////////////////
// SMT - Database Utils
class smt_database EXTENDS smt_utils {
    var $database_name;
    var $db;
    var $sql_count;
    var $last_insert_id;
    var $last_error;

    //////////////////////////////////////////////////////////
    function init_database() {
        $this->debug('::init_database()');
        if( !in_array('sqlite', PDO::getAvailableDrivers() ) ) {
            $this->error('::init_database: ERROR: no sqlite Driver');
            return $this->db = FALSE;
        }
        try {
            return $this->db = new PDO('sqlite:'. $this->database_name);
        } catch(PDOException $e) {
            $this->error('::init_database: ' . $this->database_name . '  ERROR: '. $e->getMessage());
            return $this->db = FALSE;
        }
    }

    //////////////////////////////////////////////////////////
    function query_as_array( $sql, $bind=array() ) {
        $this->start_timer('query_as_array');
        $this->debug("query_as_array( <pre>$sql</pre>, bind:".sizeof($bind)." ) ");

        if( !$this->db ) { $this->init_database(); }
        if( !$this->db ) { $this->end_timer('query_as_array'); return FALSE; }

        $statement = $this->db->prepare($sql);
        if( !$statement ) {
            $this->debug('::query_as_array(): ERROR PREPARE'); // '. $this->db->errorInfo()[2]);
            $this->end_timer('query_as_array');
            return array();
        }
        while( $xbind = each($bind) ) {
            $this->debug('::query_as_array(): bindParam '. $xbind[0] .' = ' . $xbind[1]);
            $statement->bindParam( $xbind[0], $xbind[1]);
        }
        $this->start_timer('sql');
        if( !$statement->execute() ) {
            $this->error('::query_as_array(): ERROR EXECUTE: '.print_r($this->db->errorInfo(),1));
            $this->end_timer('sql');
            return array();
        }
        $this->end_timer('sql');

        $response = $statement->fetchAll(PDO::FETCH_ASSOC);
        if( !$response && $this->db->errorCode() != '00000') {
            $this->error('::query_as_array(): ERROR FETCH: '.print_r($this->db->errorInfo(),1));
            $response = array();
        }

        $this->debug('query_as_array: OK:'. count($response). ': ' . htmlentities($sql)
        . ' | response: <pre>' . htmlentities(print_r($response,1)) . '</pre>' );

        $this->end_timer('query_as_array');
        return $response;
    }

    //////////////////////////////////////////////////////////
    function query_as_bool( $sql, $bind=array() ) {
        $this->start_timer('query_as_bool');
        $this->debug("query_as_bool: <pre>$sql</pre>");
        if( $bind ) { $this->debug('query_as_bool: BIND: <pre>' . htmlentities(print_r($bind,1)) . '</pre>' ); }

        if( !$this->db ) { $this->init_database(); }
        if( !$this->db ) { $this->end_timer('query_as_bool'); return FALSE; }
        $this->last_insert_id = $this->last_error = FALSE;
        $this->sql_count++;
        $statement = $this->db->prepare($sql);
        if( !$statement ) {
            $this->last_error = $this->db->errorInfo();
            $this->debug('query_as_bool: prepare failed. SQL:<br />'
                . trim($sql) . '<br />error: ' . print_r($this->last_error,1) );
            $this->end_timer('query_as_bool');
            return FALSE;
        }
        while( $xbind = each($bind) ) {
            $statement->bindParam( $xbind[0], $xbind[1] );
        }
        $this->start_timer('sql');
        if( !$statement->execute() ) {
            $this->end_timer('sql');
            $this->last_error = $this->db->errorInfo();
            $this->debug($this->last_error);
            if( $this->last_error[0] == '00000' ) {
                $this->debug('NULL EVENT: ' . trim($sql));
                $this->end_timer('sql');
                $this->end_timer('query_as_bool');
                return TRUE;
            }
            $this->debug('query_as_bool: prepare failed. SQL: '
                . trim($sql) . '<br />error: ' . print_r($this->last_error,1) );
            $this->end_timer('sql');
            $this->end_timer('query_as_bool');
            return FALSE;
        }
        $this->end_timer('sql');
        $this->last_error = $this->db->errorInfo();
        $this->last_insert_id = $this->db->lastInsertId();
        $this->debug('OK: ' . trim($sql));
        $this->end_timer('query_as_bool');
        return TRUE;
    } // end function query_as_bool()

    //////////////////////////////////////////////////////////
    function vacuum() {
        $this->start_timer('vacuum');
        if( $this->query_as_bool('VACUUM') ) {
            $this->end_timer('vacuum');
            return TRUE;
        }
        $this->error('FAILED to VACUUM');
        $this->end_timer('vacuum');
        return FALSE;
    }

    //////////////////////////////////////////////////////////
    function begin_transaction() {
        if( $this->query_as_bool('BEGIN TRANSACTION') ) {
            return TRUE;
        }
        $this->error('FAILED to BEGIN TRANSACTION');
        return FALSE;
    }

    //////////////////////////////////////////////////////////
    function commit() {
        if( $this->query_as_bool('COMMIT') ) {
            return TRUE;
        }
        $this->error('FAILED to COMMIT');
        return FALSE;
    }

} // end class smt_database_utils

//////////////////////////////////////////////////////////
// SMT - Media
class smt_media EXTENDS smt_database {

    var $image_count;

    //////////////////////////////////////////////////////////
    function get_media($pageid) {
        $this->debug("smt-db:get_media($pageid)");
        if( !$pageid || !$this->is_positive_number($pageid) ) {
            $this->error('get_media: ERROR no id');
            return FALSE;
        }
        $sql = 'SELECT * FROM media WHERE pageid = :pageid';
        return $this->query_as_array( $sql, array(':pageid'=>$pageid) );
    }

    //////////////////////////////////////////////////////////
    function get_thumbnail( $media='', $thumb_width='' ) {

        if( !$thumb_width || !$this->is_positive_number($thumb_width) ) {
            $thumb_width = $this->size_thumb;
        }

        $default = array(
            'url' => 'data:image/gif;base64,R0lGOD lhCwAOAMQfAP////7+/vj4+Hh4eHd3d/v'
                    .'7+/Dw8HV1dfLy8ubm5vX19e3t7fr 6+nl5edra2nZ2dnx8fMHBwYODg/b29np6e'
                    . 'ujo6JGRkeHh4eTk5LCwsN3d3dfX 13Jycp2dnevr6////yH5BAEAAB8ALAAAAAA'
                    . 'LAA4AAAVq4NFw1DNAX/o9imAsB tKpxKRd1+YEWUoIiUoiEWEAApIDMLGoRCyWi'
                    . 'KThenkwDgeGMiggDLEXQkDoTh CKNLpQDgjeAsY7MHgECgx8YR8oHwNHfwADBACG'
                    . 'h4EDA4iGAYAEBAcQIg0Dk gcEIQA7',
            'width' => $thumb_width,
            'height' => $thumb_width);


        if( !$media || !is_array($media) ) {
            return $default;
        }

        $width = @$media['width'];
        if( !$width ) { $width = $this->size_thumb; }
        $height = @$media['height'];
        if( !$height ) { $height = $this->size_thumb; }

        //$this->notice("::get_thumbnail: new-w:$thumb_width  old-w:$width old-h:$height");
        if( $thumb_width >= $width ) {
            //$this->notice('::get_thumbnail: new-w >= old-w');
            return array('url'=>@$media['thumburl'], 'width'=>@$width, 'height'=>@$height);
        }


        if( $height > $thumb_width ) {
            //$this->notice("WARNING: TALL THUMB");
        }

        //$title = $media['title'];
        $mime = $media['mime'];

        $filename = $this->strip_prefix($media['title']);
        $filename = str_replace(' ','_',$filename);

        $md5 = md5($filename);
        $thumb_url = 'https://upload.wikimedia.org/wikipedia/commons/thumb'
        . '/' . $md5[0]
        . '/' . $md5[0] . $md5[1]
        . '/' . urlencode($filename)
        . '/' . $thumb_width . 'px-' . urlencode($filename);

        $ratio = $width / $height;
        $thumb_height = round($thumb_width / $ratio);

        switch( $mime ) {
            case 'application/ogg':
                $thumb_url = str_replace('px-','px--',$thumb_url);
                $thumb_url .= '.jpg';
                break;
            case 'video/webm':
                $thumb_url = str_replace('px-','px--',$thumb_url);
                $thumb_url .= '.jpg';
                break;
            case 'image/svg+xml':
                $thumb_url .= '.png';
                break;
        }

        return array('url'=>$thumb_url, 'width'=>$thumb_width, 'height'=>$thumb_height);
    }

    //////////////////////////////////////////////////////////
    function get_random_unreviewed_media($limit=1) {
        $sql = '
            SELECT m.*
            FROM media AS m
            LEFT JOIN tagging AS t ON t.media_pageid = m.pageid
            WHERE t.media_pageid IS NULL
            ORDER BY RANDOM()
            LIMIT :limit';
        return $this->query_as_array( $sql, array('limit'=>$limit) );
    }

    //////////////////////////////////////////////////////////
    function get_random_media($limit=1) {
        $unreviewed = $this->get_random_unreviewed_media($limit);
        if( $unreviewed ) {
            if( mt_rand(1,5) != 1 ) {
                return $unreviewed;
            }
        }
        $sql = 'SELECT * FROM media ORDER BY RANDOM() LIMIT :limit';
        return $this->query_as_array($sql, array('limit'=>$limit));
    }

    //////////////////////////////////////////////////////////
    function get_image_count( $redo=FALSE ) {
        if( isset($this->image_count) && !$redo ) {
            return $this->image_count;
        }
        $response = $this->query_as_array('SELECT count(pageid) AS count FROM media');
        if( !$response ) {
            $this->debug('::get_image_count() ERROR query failed.');
            return 0;
        }
        return $this->image_count = $response[0]['count'];
    }

} // END class media

//////////////////////////////////////////////////////////
// SMT - Admin
class smt_site_admin EXTENDS smt_media {

    //////////////////////////////////////////////////////////
    function is_admin() {
        if( isset($_COOKIE['admin']) && $_COOKIE['admin'] == 1 ) {
            return TRUE;
        }
        return FALSE;
    }

    //////////////////////////////////////////////////////////
    function admin_logoff() {
        if( !$this->is_admin() ) {
            return;
        }
        unset($_COOKIE['admin']);
        setcookie('admin', null, -1, '/');
    }

    //////////////////////////////////////////////////////////
    function display_admin_media_list_functions() {
        return
        '<div class="left pre white" style="display:inline-block; border:1px solid red; margin:2px; padding:2px;">'
        . '<input type="submit" value="Delete selected media">'
        . '<script type="text/javascript" language="javascript">'
        . "function checkAll(formname, checktoggle) { var checkboxes = new Array();
        checkboxes = document[formname].getElementsByTagName('input');
        for (var i=0; i<checkboxes.length; i++) { if (checkboxes[i].type == 'checkbox') { checkboxes[i].checked = checktoggle; } } }
        </script>"
        . ' &nbsp; <a onclick="javascript:checkAll(\'media\', true);" href="javascript:void();">check all</a>'
        . ' &nbsp; <a onclick="javascript:checkAll(\'media\', false);" href="javascript:void();">uncheck all</a>'
        . '</div>';
    }

    //////////////////////////////////////////////////////////
    function display_admin_media_functions( $media_id ) {
        if( !$this->is_admin() ) {
            return;
        }
        if( !$this->is_positive_number($media_id) ) {
            return;
        }
        return ''
        . '<div class="attribution left" style=" display:inline-block; float:right;">'
        . '<a style="font-size:140%;" href="' . $this->url('admin') . 'media.php?dm=' . $media_id
        . '" title="Delete" target="admin" onclick="return confirm(\'Confirm: Delete Media #'
        . $media_id . ' ?\');">❌</a>'
        . '<input type="checkbox" name="media[]" value="' . $media_id . '" />'
        . '<a style="font-size:170%;" href="' . $this->url('admin') . 'media.php?am=' . $media_id
        . '" title="Refresh" target="admin" onclick="return confirm(\'Confirm: Refresh Media #'
        . $media_id . ' ?\');">♻</a>'

        . ' <a style="font-size:140%;" href="' . $this->url('admin')
        . 'media-analysis.php?skin=' . $media_id. '">👙</a>'

        . ' <a style="font-size:140%;" href="' . $this->url('admin')
        . 'media-analysis.php?hash=' . $media_id. '">H</a>'
        . '</div>';
    }

    //////////////////////////////////////////////////////////
    function display_admin_category_functions( $category_name ) {
        if( !$this->is_admin() ) { return; }
        $category = $this->get_category($category_name);
        if( !$category ) {
            return '<p>ADMIN: category not in database</p>';
        }
        $response = '<br clear="all" />
<div class="left pre white" style="display:inline-block; border:1px solid red; padding:10px;">
<input type="submit" value="Delete selected media">
<script type="text/javascript" language="javascript">
'
. "function checkAll(formname, checktoggle) { var checkboxes = new Array();
checkboxes = document[formname].getElementsByTagName('input');
for (var i=0; i<checkboxes.length; i++) { if (checkboxes[i].type == 'checkbox') { checkboxes[i].checked = checktoggle; } } }
</script>"
. ' &nbsp; <a onclick="javascript:checkAll(\'media\', true);" href="javascript:void();">check all</a>'
. ' &nbsp;&nbsp; <a onclick="javascript:checkAll(\'media\', false);" href="javascript:void();">uncheck all</a>'
. '<br /><br /><a target="commons" href="https://commons.wikimedia.org/wiki/'
. $this->category_urlencode($category['name']) . '">VIEW ON COMMONS</a>
<br /><br /><a href="' . $this->url('admin') . 'category.php/?c='
. $this->category_urlencode($category['name']) . '">Get Category Info</a>
<br /><br /><a href="' . $this->url('admin') . 'category.php/?i='
. $this->category_urlencode($category['name'])
. '" onclick="return confirm(\'Confirm: Import Media To Category?\');">Import Media to Category</a>
<br /><br /><a href="' . $this->url('admin') . 'media.php?dc='
. $this->category_urlencode($category['name'])
. '" onclick="return confirm(\'Confirm: Clear Media from Category?\');">Clear Media from Category</a>
<br /><br /><a href="' . $this->url('admin') . 'category.php/?d=' . urlencode($category['id'])
. '" onclick="return confirm(\'Confirm: Delete Category?\');">Delete Category</a>
<br /><pre>' . print_r($category,1) . '</pre>
</form>
</div><br /><br />';
        return $response;
    }

} // END class smt_admin

//////////////////////////////////////////////////////////
// SMT - User
class smt_user EXTENDS smt_site_admin {

    var $user_id;
    var $user_count;

    //////////////////////////////////////////////////////////
    function get_user_count() {
        if( isset($this->user_count) ) {
            return $this->user_count;
        }
        $count = $this->query_as_array('SELECT count(id) AS count FROM user');
        if( isset($count[0]['count']) ) {
            return $this->user_count = $count[0]['count'];
        }
        return $this->user_count = 0;
    }

    //////////////////////////////////////////////////////////
    function get_users( $limit=100, $orderby='last DESC, page_views DESC' ) {
        $sql = 'SELECT * FROM user';
        $sql .= ' ORDER BY ' . $orderby;
        $sql .= ' LIMIT ' . $limit;
        $users = $this->query_as_array($sql);
        if( isset($users[0]) ) {
            return $users;
        }
        return array();
    } // end function get_users

    //////////////////////////////////////////////////////////
    function get_user( $create_new=FALSE ) {
        $ip_address = @$_SERVER['REMOTE_ADDR'];
        $host = @$_SERVER['REMOTE_HOST'];
        if( !$host ) {
            $host = $ip_address;
        }
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];

        $user = $this->query_as_array(
            'SELECT id FROM user WHERE ip = :ip_address AND host = :host AND user_agent = :user_agent',
            array( ':ip_address'=>$ip_address, ':host'=>$host, ':user_agent'=>$user_agent )
        );
        if( !isset($user[0]['id']) ) {
            if( $create_new ) {
                return $this->new_user($ip_address, $host, $user_agent); // testing
            }
            $this->user_id = 0;
            return FALSE;
        }
        $this->user_id = $user[0]['id'];
        //$this->save_user_view(); // testing
        return TRUE;
    } // end function get_user_info()

    //////////////////////////////////////////////////////////
    function get_user_tag_count( $user_id=FALSE ) {
        $sql = 'SELECT sum(count) AS sum FROM user_tagging';
        $bind = array();
        if( $user_id > 0 ) {
            $sql .= ' WHERE user_id = :user_id';
            $bind[':user_id'] = $user_id;
        }
        $count = $this->query_as_array($sql, $bind);
        if( isset($count[0]['sum']) ) {
            return $count[0]['sum'];
        }
        return 0;
    }

    //////////////////////////////////////////////////////////
    function get_user_tagging( $user_id ) {
        $tags = $this->query_as_array(
            'SELECT m.*, ut.tag_id, ut.count
            FROM user_tagging AS ut, media AS m
            WHERE ut.user_id = :user_id
            AND ut.media_pageid = m.pageid
            ORDER BY ut.media_pageid

            LIMIT 100  -- TMP

            ',
            array(':user_id'=>$user_id)
        );
        if( $tags ) {
            return $tags;
        }
        return array();
    }

    //////////////////////////////////////////////////////////
    function save_user_last_tag_time() {
        return $this->query_as_bool(
            'UPDATE user SET last = :last WHERE id = :user_id',
            array(':user_id'=>$this->user_id, ':last'=>gmdate('Y-m-d H:i:s'))
        );
    }

    //////////////////////////////////////////////////////////
    function save_user_view() {
        if( !$this->user_id ) {
            return FALSE;
        }
        $view = $this->query_as_bool(
                'UPDATE user SET page_views = page_views + 1, last = :last WHERE id = :id',
                array( ':id' => $this->user_id, ':last'=>gmdate('Y-m-d H:i:s') )
        );
        if( $view ) {
            return TRUE;
        }
        return FALSE;
    }

    //////////////////////////////////////////////////////////
    function new_user( $ip_address, $host, $user_agent ) {
        if(
            $this->query_as_bool(
                'INSERT INTO user (
                    ip, host, user_agent, page_views, last
                ) VALUES (
                    :ip_address, :host, :user_agent, 0, :last
                )',
                array(
                    ':ip_address'=>$ip_address,
                    ':host'=>$host,
                    ':user_agent'=>$user_agent,
                    ':last'=>gmdate('Y-m-d H:i:s')
                )
            )
        ) {
            $this->user_id = $this->last_insert_id;
            return TRUE;
        }
        $this->user_id = 0;
        //$this->notice('new_user: FAILED to create user');
        return FALSE;
    } // end function new_user()

} // end class smt_user

//////////////////////////////////////////////////////////
// SMT - Category
class smt_category EXTENDS smt_user {

    var $category_count;

    //////////////////////////////////////////////////////////
    function display_categories( $media_id ) {

        if( !$media_id || !$this->is_positive_number($media_id) ) {
            return FALSE;
        }

        $cats = $this->get_image_categories($media_id);

        $response = '<div class="categories" style="width:' . $this->size_medium . 'px;">';

        if( !$cats ) { return $response . '<em>Uncategorized</em></div>'; }

        $hidden = array();
        foreach($cats as $cat ) {
            if( $this->is_hidden_category($cat) ) {
                $hidden[] = $cat;
                continue;
            }
            $response .= ''
            . '+<a href="' . $this->url('category')
            . '?c=' . $this->category_urlencode( $this->strip_prefix($cat) ) . '">'
            . $this->strip_prefix($cat) . '</a><br />';
        }

        if( !$hidden ) {
            return $response . '</div>';
        }

        $response .= '<br /><div style="font-size:80%;">';

        foreach( $hidden as $hcat ) {
            $response .= '+<a href="' . $this->url('category')
            . '?c=' . $this->category_urlencode( $this->strip_prefix($hcat) ) . '">'
            . $this->strip_prefix($hcat) . '</a><br />';
        }
        return $response . '</div></div>';

    } // end function display_categories()

    //////////////////////////////////////////////////////////
    function strip_prefix( $string ) {
        if( !$string || !is_string($string) ) {
            return $string;
        }
        return preg_replace(
            array( '/^File:/', '/^Category:/' ),
            '',
            $string
        );
    }

    //////////////////////////////////////////////////////////
    function category_urldecode($category) {
        return str_replace(
            '_',
            ' ',
            urldecode($category)
        );
    }

    //////////////////////////////////////////////////////////
    function category_urlencode($category) {
        return str_replace(
            '+',
            '_',
            str_replace(
                '%3A',
                ':',
                urlencode($category)
            )
        );
    }

    //////////////////////////////////////////////////////////
    function get_category( $name ) {

        $this->debug("get_category( $name )");

        $response = $this->query_as_array(
            'SELECT * FROM category WHERE name = :name',
            array(':name'=>$name)
        );
        if( !isset($response[0]['id']) ) {
            $this->debug("get_category( $name ) = ERROR: Category Not Found in Database");
            return array();
        }
        $this->debug("get_category( $name ) = <pre>" . print_r($response[0],1) . '</pre>');
        return $response[0];
    }

    //////////////////////////////////////////////////////////
    function get_category_size( $category_name ) {
        $this->debug("get_category_size( $category_name )");
        $response = $this->query_as_array(
            'SELECT count(c2m.id) AS size
            FROM category2media AS c2m, category AS c
            WHERE c.name = :name
            AND c2m.category_id = c.id
            ',
            array(':name'=>$category_name)
        );
        if( !isset($response[0]['size']) ) {
            $this->error('get_category_size: no size found. returning 0');
            return 0;
        }
        $this->debug("get_category_size( $category_name ) = " . $response[0]['size']);
        return $response[0]['size'];
    }

    //////////////////////////////////////////////////////////
    function get_categories_count( $redo=FALSE, $hidden=0 ) {
        if( isset($this->category_count) && !$redo ) {
            return $this->category_count;
        }
        $sql = 'SELECT count(distinct(c2m.category_id)) AS count
                FROM category2media AS c2m, category AS c
                WHERE c.id = c2m.category_id
                AND c.hidden = ' . ($hidden ? '1' : '0');
        $response = $this->query_as_array($sql);
        if( !$response ) {
            $this->debug('::get_categories_count() ERROR query failed');
            return 0;
        }
        return $this->category_count = $response[0]['count'];
    }

    //////////////////////////////////////////////////////////
    function get_category_list() {
        $sql = 'SELECT name FROM category ORDER BY name';
        $response = $this->query_as_array( $sql );
        $return = array();
        if( !$response || !is_array($response) ) { return $return; }
        while( $name = each($response) ) {
            $return[] = $name['value']['name'];
        }
        return $return;

    }

    //////////////////////////////////////////////////////////
    function get_image_categories( $pageid ) {
        //$this->notice('::get_image_categories: pageid=' . $pageid);
        $error = array('Category database unavailable');
        if( !$pageid|| !$this->is_positive_number($pageid) ) {
            return $error;
        }
        $response = $this->query_as_array(
            'SELECT category.name
            FROM category, category2media
            WHERE category2media.category_id = category.id
            AND category2media.media_pageid = :pageid
            ORDER BY category.name',
            array(':pageid'=>$pageid)
        );
        if( !isset( $response[0]['name'] ) ) {
            $this->debug('::get_image_categories: ' . print_r($response,1) );
            return $error;
        }
        $cats = array();
        foreach( $response as $cat ) {
            $cats[] = $cat['name'];
        }
        return $cats;
    }

    //////////////////////////////////////////////////////////
    function get_category_id_from_name( $category_name ) {

        $response = $this->query_as_array(
            'SELECT id FROM category WHERE name = :name',
            array(':name'=>$category_name)
        );

        if( !isset($response[0]['id']) ) {
            $this->debug("get_category_id_from_name( $category_name ) ERROR = 0");
            return 0;
        }
        $this->debug("get_category_id_from_name( $category_name ) = " . $response[0]['id']);
        return $response[0]['id'];
    }

    //////////////////////////////////////////////////////////
    function get_media_in_category( $category_name ) {
        $category_id = $this->get_category_id_from_name( $category_name );
        if( !$category_id ) {
            $this->error('::get_media_in_category: No ID found for: ' . $category_name);
            return array();
        }
        $response = $this->query_as_array(
            'SELECT media_pageid
            FROM category2media
            WHERE category_id = :category_id
            ORDER BY media_pageid',
            array(':category_id'=>$category_id)
        );
        if( $response === FALSE ) {
            $this->error('ERROR: unable to access categor2media table.');
            return array();
        }
        if( !$response ) {
            //$this->notice('get_media_in_category: No Media Found in ' . $category_name);
            return array();
        }
        $return = array();
        foreach( $response as $media ) {
            $return[] = $media['media_pageid'];
        }
        return $return;
    }

    //////////////////////////////////////////////////////////
    function get_count_local_files_per_category( $category_id_array ) {
        if( !is_array($category_id_array) ) {
            $this->error('get_count_local_files_per_category: invalid category array');
            return 0;
        }
        $locals = $this->query_as_array(
            'SELECT count(category_id) AS count
            FROM category2media
            WHERE category_id IN ( :category_id )',
            array( ':category_id'=> implode($category_id_array, ', ') )
        );
        if( $locals && isset($locals[0]['count']) ) {
            return $locals[0]['count'];
        }
        return 0;
    }

    //////////////////////////////////////////////////////////
    function is_hidden_category( $category_name ) {
        //$this->notice("is_hidden_category( $category_name )");
        if( !$category_name ) {
            $this->debug('ERROR: is_hidden_category: category_name NOT FOUND');
            return FALSE;
        }
        $sql = 'SELECT id FROM category WHERE hidden = 1 AND name = :category_name';
        $bind = array(':category_name'=>$category_name);
        if( $this->query_as_array($sql, $bind) ) {
            return TRUE;
        }
        return FALSE;
    }


} // END class category

//////////////////////////////////////////////////////////
// SMT - Tag
class smt_tag EXTENDS smt_category {

    //////////////////////////////////////////////////////////
    function display_tags( $media_id ) {
        $tags = $this->get_tags();
        $response = '<div class="nobr" style="display:block; margin:auto;">';
        foreach( $tags as $tag ) {
            $response .=  ''
            . '<div class="tagbutton tag' . $tag['position'] . '">'
            . '<a href="' . $this->url('tag') . '?m=' . $media_id
                . '&amp;t=' . $tag['id'] . '" title="' . $tag['name'] . '">'
            . $tag['display_name']
            . '</a></div>';
        }
        return $response . '</div>';
    }

    /////////////////////////////////////////////////////////
    function display_reviews( $reviews ) {
        if( !$reviews ) {
            return; // 'unreviewed';
        }
        //$review_count = 0;
        $response = '';
        foreach( $reviews as $review ) {
            $response .= ''
            . '+<a href="' . $this->url('reviews')
            . '?o=reviews.' . urlencode($review['name'])
            . '">'
            . $review['count'] . ' ' . $review['name']
            . '</a><br />';
            //$review_count += $review['count'];
        }
        //$response = '<div style="display:inline-block; text-align:left;">'
        //. '<em><b>' . $review_count . '</b> reviews</em>' . $response . '</div>';
        return $response;
    }

    //////////////////////////////////////////////////////////
    function get_tag_id_by_name( $name ) {
        if( isset( $this->tag_id[$name] ) ) {
            return $this->tag_id[$name];
        }
        $tag = $this->query_as_array(
            'SELECT id FROM tag WHERE name = :name LIMIT 1',
            array(':name'=>$name)
        );
        if( isset( $tag[0]['id'] ) ) {
            return $this->tag_id[$name] = $tag[0]['id'];
        }
        return $this->tag_id[$name] = 0;
    }

    //////////////////////////////////////////////////////////
    function get_tag_name_by_id( $tag_id ) {
        if( isset( $this->tag_name[$tag_id] ) ) {
            return $this->tag_name[$tag_id];
        }
        $tag = $this->query_as_array(
            'SELECT name FROM tag WHERE id = :id LIMIT 1',
            array(':id'=>$tag_id)
        );
        if( isset( $tag[0]['name'] ) ) {
            return $this->tag_name[$tag_id] = $tag[0]['name'];
        }
        return $this->tag_name[$tag_id] = $tag_id;
    }

    //////////////////////////////////////////////////////////
    function get_tags() {
        if( isset($this->tags) ) {
            reset($this->tags);
            return $this->tags;
        }
        $tags = $this->query_as_array('
            SELECT * FROM tag ORDER BY position');
        if( !$tags ) {
            $this->debug('Tag database not available');
            return $this->tags = array();
        }
        return $this->tags = $tags;
    }

    //////////////////////////////////////////////////////////
    function get_tagging_count( $tag_id=FALSE ) {
        $sql = 'SELECT SUM(count) AS count FROM tagging';
        $bind = array();
        if( $tag_id ) {
            $sql .= ' WHERE tag_id = :tag_id';
            $bind[':tag_id'] = $tag_id;
        }
        $count = $this->query_as_array($sql, $bind);
        if( !isset($count[0]['count']) ) {
            return '0';
        }
        return $count[0]['count'];
    }

    /////////////////////////////////////////////////////////
    function get_total_review_count() {
        if( isset($this->total_review_count) ) {
            return $this->total_review_count;
        }
        $response = $this->query_as_array('SELECT SUM(count) AS total FROM tagging');
        if( isset($response[0]['total']) ) {
            return $this->total_review_count = $response[0]['total'];
        }
        return $this->total_review_count = 0;
    }

    /////////////////////////////////////////////////////////
    function get_reviews( $pageid ) {
        $reviews = $this->query_as_array('
            SELECT t.tag_id, t.count, tag.*
            FROM tagging AS t, tag
            WHERE t.media_pageid = :media_pageid
            AND tag.id = t.tag_id
            AND t.count > 0
            ORDER BY tag.position
            ', array(':media_pageid'=>$pageid) );
        return $this->display_reviews( $reviews );
    }

    /////////////////////////////////////////////////////////
    function get_reviews_per_category( $category_id ) {
        return $this->display_reviews( $this->get_db_reviews_per_category($category_id) );
    }

    /////////////////////////////////////////////////////////
    function get_db_reviews_per_category( $category_id ) {
        $reviews = $this->query_as_array('
            SELECT SUM(t.count) AS count, tag.*
            FROM tagging AS t,
                 tag,
                 category2media AS c2m
            WHERE tag.id = t.tag_id
            AND c2m.media_pageid = t.media_pageid
            AND c2m.category_id = :category_id
            AND t.count > 0
            GROUP BY (tag.id)
            ORDER BY tag.position
            ', array(':category_id'=>$category_id) );
        return $reviews;
    }

    /////////////////////////////////////////////////////////
    function get_total_files_reviewed_count() {
        if( isset($this->total_files_reviewed_count) ) {
            return $this->total_files_reviewed_count;
        }
        $response = $this->query_as_array('SELECT COUNT( DISTINCT(media_pageid) ) AS total FROM tagging');
        if( isset($response[0]['total']) ) {
            return $this->total_files_reviewed_count = $response[0]['total'];
        }
        return $this->total_files_reviewed_count = 0;
    }


} // END clss smt_tag

//////////////////////////////////////////////////////////
// SMT - Menus
class smt_menus EXTENDS smt_tag {

    //////////////////////////////////////////////////////////
    function include_menu() {
        $space = ' &nbsp; &nbsp; ';
        $count_files = number_format($this->get_image_count());
        $count_categories = number_format($this->get_categories_count());
        $count_reviews = number_format($this->get_total_review_count());
        $count_users = number_format($this->get_user_count());
        print '<div class="menu" style="font-weight:bold;">'
        . '<span class="nobr"><a href="' . $this->url('home') . '">' . $this->site_name . '</a></span>' .  $space
        . '<a href="' . $this->url('browse') . '">🔎' . $count_files . '&nbsp;Files' . '</a>' . $space
        . '<a href="' . $this->url('categories') . '">📂' . $count_categories . '&nbsp;Categories</a>' . $space
        . '<a href="' . $this->url('reviews') . '">🗳' . $count_reviews . '&nbsp;Reviews</a>' . $space
        . '<a href="'. $this->url('users') . ($this->user_id ? '?i=' . $this->user_id : '') . '">'
            . $count_users .'&nbsp;Users</a>' . $space
        . '<a href="' . $this->url('contact') . '">Contact</a>' . $space
        . '<a href="'. $this->url('about') . '">❔About</a>'
        . ($this->is_admin() ? $space . '<a href="' . $this->url('admin') . '">🔧ADMIN</a>' : '')
        . '</div>';
    }

    //////////////////////////////////////////////////////////
    function include_medium_menu() {
        $space = ' &nbsp; &nbsp; ';
        print ''
        . '<div class="menu" style="font-weight:bold;">'
        . '<span class="nobr"><a href="' . $this->url('home') . '">' . $this->site_name . '</a></span>' .  $space
        . '<a href="' . $this->url('browse') . '">🔎Files' . '</a>' . $space
        . '<a href="' . $this->url('categories') . '">📂Categories</a>' . $space
        . '<a href="' . $this->url('reviews') . '">🗳Reviews</a>' . $space
        . '<a href="'. $this->url('users') . ($this->user_id ? '?i=' . $this->user_id : '') . '">Users</a>' . $space
        . '<a href="' . $this->url('contact') . '">Contact</a>' . $space
        . '<a href="'. $this->url('about') . '">❔About</a>'
        . ($this->is_admin() ? $space . '<a href="' . $this->url('admin') . '">🔧ADMIN</a>' : '')
        . '</div>';
    }

    //////////////////////////////////////////////////////////
    function include_small_menu() {
        $space = ' ';
        print '<div class="menujcon">'
        . '<a style="font-weight:bold; font-size:85%;" href="' . $this->url('home') . '">' . $this->site_name . '</a>'
        . '<span style="float:right;">'
        . '<a class="menuj" title="Browse" href="' . $this->url('browse') . '">🔎</a>' . $space
        . '<a class="menuj" title="Categories" href="' . $this->url('categories') . '">📂</a>' . $space
        . '<a class="menuj" title="Reviews" href="' . $this->url('reviews') . '">🗳</a>' . $space
        . '<a class="menuj" title="About" href="' . $this->url('about') . '">❔</a>' . $space
        . ($this->is_admin() ? '<a class="menuj" title="ADMIN" href="' . $this->url('admin') . '">🔧</a>' : '')
        . '</span>'
        . '</div><div style="clear:both;"></div>';
        // 🌐 🏷 📂 🔗 🔎 🔖 🖇 ⛓  ❓  ❔  📢
    }

} // end class menus

//////////////////////////////////////////////////////////
// SMT - Shared Media Tagger
class smt EXTENDS smt_menus {

    var $install_directory;
    var $server;
    var $setup;
    var $site;
    var $site_info;
    var $site_name;
    var $site_url;
    var $size_medium;
    var $size_thumb;
    var $title; // Page <title>
    var $use_bootstrap;
    var $use_jquery;

    //////////////////////////////////////////////////////////
    function __construct() {

        $this->start_timer('page');

        global $setup; // Load the setup array, if present in _setup.php
        $this->setup = array();
        if( is_array($setup) ) {
            $this->setup = $setup;
        }

        $this->install_directory = __DIR__;

        $this->database_name = $this->install_directory . '/admin/db/media.sqlite';

        $this->size_medium = 325;
        $this->size_thumb = 100;

        $this->server = $_SERVER['SERVER_NAME']; // $_SERVER['HTTP_HOST'];

        if( isset($this->setup['site_url']) ) {
            $this->site_url = $setup['site_url'];
        } else {
            $this->site_url = '//' . $this->server . '/';
            if( $base = basename(__DIR__) ) {
                $this->site_url .= $base . '/';
            }
            $this->debug('Site URL Not Set.  Using: ' . $this->site_url);
        }

        $this->set_site_info();

        $this->links = array(
            'home'       => $this->site_url . '',
            'css'        => $this->site_url . 'css.css',
            'jquery'        => $this->site_url . 'use/jquery.min.js',
            'bootstrap_js'  => $this->site_url . 'use/bootstrap/js/bootstrap.min.js',
            'bootstrap_css' => $this->site_url . 'use/bootstrap/css/bootstrap.min.css',
            'info'       => $this->site_url . 'info.php',
            'browse'     => $this->site_url . 'browse.php',
            'categories' => $this->site_url . 'categories.php',
            'category'   => $this->site_url . 'category.php',
            'about'      => $this->site_url . 'about.php',
            'reviews'    => $this->site_url . 'reviews.php',
            'admin'      => $this->site_url . 'admin/',
            'contact'    => $this->site_url . 'contact.php',
            'tag'        => $this->site_url . 'tag.php',
            'users'      => $this->site_url . 'users.php',
            'github_smt' => 'https://github.com/attogram/shared-media-tagger',
        );

        $this->get_user();

        $this->sql_count = 0;
        if( isset($_GET['logoff']) ) {
            $this->admin_logoff();
        }

    } // end function __construct()

    //////////////////////////////////////////////////////////
    function set_site_info() {
        $response = $this->query_as_array('SELECT * FROM site WHERE id = 1');
        if( !$response || !isset($response[0]['name']) ) {
            $this->site_name = 'Shared Media Tagger';
            $this->site_info = array();
            return FALSE;
        }
        $this->site_name = $response[0]['name'];
        $this->site_info = $response[0];
        $this->debug('site_info = ' . print_r($this->site_info,1));
        return TRUE;
    }

    //////////////////////////////////////////////////////////
    function display_thumbnail( $media='' ) {

        $thumb = $this->get_thumbnail($media);

        return '<div style="display:inline-block;text-align:center;">'
            . '<a href="' .  $this->url('info') . '?i=' . $media['pageid'] . '">'
            . '<img src="' . $thumb['url'] . '"'
            . ' width="' . $thumb['width'] . '"'
            . ' height="' . $thumb['height'] . '"'
            . ' title="' . htmlentities($media['title']) . '" /></a>'
            . '</div>'
            ;
    }

    //////////////////////////////////////////////////////////
    function display_thumbnail_box( $media='' ) {
        $return = '<div class="thumbnail_box">'
        . $this->display_thumbnail($media)
        . str_replace(
            ' / ',
            '<br />',
            $this->display_attribution( $media, /*title truncate*/17, /*artist*/21 )
            )
        . $this->display_admin_media_functions( $media['pageid'] )
        //. '<br />'
        . '<div class="thumbnail_reviews left">'
        . $this->get_reviews( $media['pageid'] )
        . '</div>'
        . '</div>';
        return $return;
    }

    //////////////////////////////////////////////////////////
    function display_video( $media ) {
        $mime = $media['mime'];
        $url = $media['url'];
        $width = $media['thumbwidth'];
        $height = $media['thumbheight'];
        $poster = $media['thumburl'];

        if( !$width || $width > $this->size_medium) {
            $height = $this->get_resized_height( $width, $height, $this->size_medium );
            $width = $this->size_medium;
        }
        //$infourl = $this->url('info') . '?i=' . $media['pageid'];
        $divwidth = $width = $media['thumbwidth'];
        if( $divwidth < $this->size_medium )  {
            $divwidth = $this->size_medium;
        }

        $return = '<div style="width:' . $divwidth . 'px; margin:auto;">'
        . '<video width="'. $divwidth . '" height="' . $height . '" poster="' . $poster
        . '" onclick="this.paused ? this.play() : this.pause();" controls loop>'
        . '<source src="' . $url . '" type="' . $mime . '">'
        . '</video>'
        . $this->display_attribution( $media )
        . $this->display_admin_media_functions( $media['pageid'] )
        . '</div>';
        return $return;
    }

    //////////////////////////////////////////////////////////
    function display_audio( $media ) {
        $mime = $media['mime'];
        $url = $media['url'];
        $width = $media['thumbwidth'];
        $height = $media['thumbheight'];
        $poster = $media['thumburl'];

        if( !$width || $width > $this->size_medium) {
            $height = $this->get_resized_height( $width, $height, $this->size_medium );
            $width = $this->size_medium;
        }
        //$infourl = $this->url('info') . '?i=' . $media['pageid'];
        $divwidth = $width = $media['thumbwidth'];
        if( $divwidth < $this->size_medium )  {
            $divwidth = $this->size_medium;
        }

        $return = '<div style="width:' . $divwidth . 'px; margin:auto;">'
        . '<audio width="'. $width . '" height="' . $height . '" poster="' . $poster
        . '" onclick="this.paused ? this.play() : this.pause();" controls loop>'
        . '<source src="' . $url . '" type="' . $mime . '">'
        . '</audio>'
        . $this->display_attribution( $media )
        . $this->display_admin_media_functions( $media['pageid'] )
        . '</div>';
        return $return;
    }

    //////////////////////////////////////////////////////////
    function display_image( $media='' ) {
        if( !$media || !is_array($media) ) {
            $this->error('display_image: ERROR: no image array');
            return FALSE;
        }

        $mime = @$media['mime'];

        $video = array('application/ogg','video/webm');
        if( in_array( $mime, $video ) ) {
            return $this->display_video($media);
        }

        $audio = array('audio/x-flac');
        if( in_array( $mime, $audio ) ) {
            return $this->display_audio($media);
        }

        $url = $media['thumburl'];
        $height = $media['thumbheight'];
        $divwidth = $width = $media['thumbwidth'];
        if( $divwidth < $this->size_medium )  {
            $divwidth = $this->size_medium;
        }
        $infourl =  $this->url('info') . '?i=' . $media['pageid'];

        return  '<div style="width:' . $divwidth . 'px; margin:auto;">'
        . '<a href="' . $infourl . '">'
        . '<img src="'. $url .'" height="'. $height .'" width="'. $width . '" alt=""></a>'
        . $this->display_attribution( $media )
        . $this->display_admin_media_functions( $media['pageid'] )
        . '</div>'
        ;
    }

    /////////////////////////////////////////////////////////
    function display_licensing( $media, $artist_truncate=42 ) {
        if( !$media || !is_array($media) ) {
            $this->error('::display_attribution: Media Not Found');
            return FALSE;
        }
        $artist = @$media['artist'];
        if( !$artist ) {
            $artist = 'Unknown';
            $copyright = '';
        } else {
            $artist = $this->truncate( strip_tags($artist), $artist_truncate );
            $copyright = '&copy; ';
        }
        $licenseshortname = @$media['licenseshortname'];
        switch( $licenseshortname ) {
            case 'No restrictions':
            case 'Public domain':
                $licenseshortname = 'Public Domain';
                $copyright = ''; break;
        }
        return "$copyright $artist / $licenseshortname";
    }

    //////////////////////////////////////////////////////////
    function display_attribution( $media, $title_truncate=250, $artist_truncate=48 ) {
        $infourl = $this->url('info') . '?i=' . $media['pageid'];
        $title = htmlspecialchars($this->strip_prefix($media['title']));
        return '<div class="mediatitle left">'
        . '<a href="' . $infourl . '" title="' . htmlentities($title) . '">'
        . $this->truncate($title, $title_truncate)
        . '</a></div>'
        . '<div class="attribution left">'
        . '<a href="' . $infourl . '">'
        . $this->display_licensing($media, $artist_truncate)
        . '</a></div>';
    }

    //////////////////////////////////////////////////////////
    function display_site_header() {
        print @$this->site_info['header'];
    }

    //////////////////////////////////////////////////////////
    function display_site_footer() {
        print @$this->site_info['footer'];
    }

    //////////////////////////////////////////////////////////
    function include_header( $show_site_header=TRUE ) {

        if( !$this->title ) {
            $this->title = $this->site_name;
        }

        print "<!doctype html>\n"
        . '<html><head><title>' . $this->title . '</title>'
        . '<meta charset="utf-8" />'
        . '<meta name="viewport" content="initial-scale=1" />'
        . '<meta http-equiv="X-UA-Compatible" content="IE=edge" />';
        if( $this->use_bootstrap ) {
            print '<link rel="stylesheet" href="' . $this->url('bootstrap_css') . '" />'
            . '<meta name="viewport" content="width=device-width, initial-scale=1" />'
            . '<!--[if lt IE 9]>'
            . '<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>'
            . '<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>'
            . '<![endif]-->';
        }
        if( $this->use_bootstrap || $this->use_jquery ) {
            print '<script src="' . $this->url('jquery') . '"></script>';
        }
        if( $this->use_bootstrap ) {
            print '<script src="' . $this->url('bootstrap_js') . '"></script>';
        }
        print '<link rel="stylesheet" type="text/css" href="' . $this->url('css') . '" />'
        . '<link rel="icon" type="image/png" href="' . $this->url('home') . 'favicon.ico" />'
        . '</head><body>';

        // Site headers
        if( $this->is_admin() || get_class($this) == 'smt_admin' || !$show_site_header ) {
            return;
        }
        $this->display_site_header();

    } // end function include_header()

    //////////////////////////////////////////////////////////
    function include_footer( $show_site_footer=TRUE ) {

        $this->include_menu();

        print '<footer>'
        . '<div class="menu" style="line-height:2; font-size:80%;">';


        if( !@$this->setup['hide_hosted_by'] ) {
            print '<span class="nobr">Hosted by <b><a href="//' . @$_SERVER['SERVER_NAME'] . '/">'
            . @$_SERVER['SERVER_NAME'] . '</a></b></span>';
        }
        print ' &nbsp; &nbsp; &nbsp; &nbsp; ';
        if( !@$this->setup['hide_powered_by'] ) {
            print '<span class="nobr">Powered by <b>'
            . '<a target="commons" href="https://github.com/attogram/shared-media-tagger">'
            . 'Shared Media Tagger v' . __SMT__ . '</a></b></span>';
        }

        $this->end_timer('page');

        if( $this->is_admin() ) {
            print '<br /><br />'
            . '<div style="text-align:left; word-wrap:none; line-height:1.42; font-family:monospace; font-size:10pt;">'
            . '<a href="' . $this->url('home') . '?logoff">LOGOFF</a>'
            . '<br />' . gmdate('Y-m-d H:i:s') . ' UTC';

            while( list($timer_name,$result) = each($this->timer_results) ) {
                print '<br />TIMER: ' . str_pad( round($result,5), 7, '0' ) . ' - ' . $timer_name;
            }
            print '<br />MEMORY usage: ' . number_format(memory_get_usage())
            . '<br />MEMORY peak : ' . number_format(memory_get_peak_usage());
            print '</div><br /><br /><br />';
        }

        print '</div></footer>';

        // Site footers
        if( $this->is_admin() || get_class($this) == 'smt_admin' || !$show_site_footer ) {
            print '</body></html>';
            return;
        }
        $this->display_site_footer();

        print '</body></html>';
    } // end include_footer()

} // END class smt
