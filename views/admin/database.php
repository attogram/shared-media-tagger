<?php
/**
 * Shared Media Tagger
 * Database Admin
 *
 * @var Attogram\SharedMedia\Tagger\TaggerAdmin $smt
 */

$smt->title = 'Database Admin';
$smt->includeHeader();
$smt->includeMediumMenu();
$smt->includeAdminMenu();
print '<div class="box white">';

if (isset($_GET['a'])) {
    print '<hr /><pre>';
    switch ($_GET['a']) {
        case 'c':
            print '<p>Creating Database tables:</p>';
            print $smt->database->createTables();
            break;
        case 'd':
            print '<p>Dropping Database tables:</p>';
            print $smt->database->dropTables();
            break;
        case 'em':
            print '<p>Emptying Media tables:</p>';
            print_r($smt->emptyMediaTables());
            break;
        case 'ec':
            print '<p>Emptying Category tables:</p>';
            print_r($smt->emptyCategoryTables());
            break;
        case 'et':
            print '<p>Emptying Tagging tables:</p>';
            print_r($smt->database->emptyTaggingTables());
            break;
        case 'eu':
            print '<p>Emptying User tables:</p>';
            print_r($smt->database->emptyUserTables());
            break;
    }
}
print '</pre><hr />';

?>
<p>Database Admin:</p>
<p>- <a href="sqladmin.php" target="sqlite">SQLite ADMIN</a></p>
<p>- <a href="reports.php" >Reports</a></p>
<?php print '
<ul>
<li>File: ' . $smt->database->databaseName . '</li>
<li>Permissions: '
. (is_writeable($smt->database->databaseName) ? '✔️OK: WRITEABLE' : '❌ERROR: READ ONLY')
. '</li>
<li>Size: '
. (file_exists($smt->database->databaseName) ? number_format(filesize($smt->database->databaseName)) : 'NULL')
. ' bytes</li>

<li>Download URL: <a href="' . $smt->url('admin')  . 'db/media.sqlite">'
    . $smt->url('admin')  . 'db/media.sqlite</a></li>
</ul>';
?>
<br />
<p>- <a href="database.php?a=c">CREATE tables</a></p>
<br /><br /><br />
<div style="color:darkred; background-color:lightpink; padding:10px; display:inline-block;">
DANGER ZONE:
<br />
<br />- <a onclick="return confirm('Confirm: EMPTY Tagging tables?');" href="database.php?a=et">EMPTY Tagging tables</a>
<br />
<br />- <a onclick="return confirm('Confirm: EMPTY User tables?');" href="database.php?a=eu">EMPTY User tables</a>
<br />
<br />- <a onclick="return confirm('Confirm: EMPTY Media tables?');" href="database.php?a=em">EMPTY Media tables</a>
<br />
<br />- <a onclick="return confirm('Confirm: EMPTY Category tables?');"
           href="database.php?a=ec">EMPTY Category tables</a>
<br />
<br />- <a onclick="return confirm('Confirm: DROP tables?');" href="database.php?a=d">DROP ALL tables</a>
</div>
<?php

print '</div>';

$smt->includeFooter();
