<?php
// Shared Media Tagger
// Database Admin

$f = __DIR__.'/../smt.php'; 
if(!file_exists($f)||!is_readable($f)){ print 'Site down for maintenance'; exit; } require_once($f);
$f = __DIR__.'/smt-admin.php'; 
if(!file_exists($f)||!is_readable($f)){ print 'Site down for maintenance'; exit; } require_once($f);
$smt = new smt_admin('Database Admin');
$smt->include_header();
$smt->include_menu();
$smt->include_admin_menu();
print '<div class="box white">';

?>
<p>Database Admin:</p>
<p>- <a href="sqladmin.php" target="sqlite">SQLite ADMIN</a></p>
<p>- <a href="database.php?a=c">CREATE tables</a></p>

<div style="color:darkred; background-color:lightpink; padding:10px; display:inline-block;">
DANGER ZONE:
<br >
<br />- <a onclick="return confirm('Confirm: EMPTY Media tables?');" href="database.php?a=em">EMPTY Media tables</a>
<br />
<br />- <a onclick="return confirm('Confirm: EMPTY Category tables?');" href="database.php?a=ec">EMPTY Category tables</a>
<br />
<br />- <a onclick="return confirm('Confirm: DROP tables?');" href="database.php?a=d">DROP tables</a>
</div>
<?php

if( isset($_GET['a']) ) {
    print '<hr /><pre>';
    switch( $_GET['a'] ) {
        case 'c': 
            print '<p>Creating Database tables:</p>'; print $smt->create_tables(); break;
        case 'd':
            print '<p>Dropping Database tables:</p>'; print $smt->drop_tables(); break;
        case 'em': 
            print '<p>Emptying Media tables:</p>'; print_r( $smt->empty_media_tables() ); break;
        case 'ec':
            print '<p>Emptying Category tables:</p>'; print_r( $smt->empty_category_tables() ); break;
    }
}

print '</pre></div>';
$smt->include_footer();
