<?php
/**
 * Shared Media Tagger
 * Database Admin
 *
 * @var Attogram\SharedMedia\Tagger\TaggerAdmin $smt
 */

declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\DatabaseUpdater;

$smt->title = 'Database Admin';
$smt->includeHeader();
$smt->includeMediumMenu();
$smt->includeAdminMenu();

print '<div class="box white">
<p>Database Admin:</p>
<ul><li>File: ' . $smt->database->databaseName . '</li>
<li>Permissions: ' . (is_writeable($smt->database->databaseName)
        ? '✔️OK: WRITEABLE'
        : '❌ERROR: READ ONLY')
. '</li><li>Size: ' . (file_exists($smt->database->databaseName)
        ? number_format((float) filesize($smt->database->databaseName))
        : 'NULL')
. ' bytes</li></ul><hr />';


if (isset($_GET['a'])) {
    print '<pre>';
    switch ($_GET['a']) {
        case 'create':
        case 'seed':
            $databaseUpdater = new DatabaseUpdater();
            $databaseUpdater->setDatabase($smt->database);
            break;
    }
    switch ($_GET['a']) {
        case 'create':
            print '<p>Creating Database tables:</p>';
            print $databaseUpdater->createTables();
            break;
        case 'seed':
            print '<p>Seeding Demo Setup:</p>';
            print implode('<br />', $databaseUpdater->seedDemo());
            break;
        case 'd':
            print '<p>Dropping Database tables:</p>';
            print_r($smt->database->dropTables());
            break;
        case 'em':
            print '<p>Emptying Media tables:</p>';
            print_r($smt->database->emptyMediaTables());
            break;
        case 'ec':
            print '<p>Emptying Category tables:</p>';
            print_r($smt->database->emptyCategoryTables());
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
    print '</pre><hr />';
}


?>
<ul>
    <li><a href="sqladmin" target="sqlite">SQLite ADMIN</a></li>
    <li><a href="database?a=create">CREATE tables</a></li>
    <li><a href="database?a=seed">SEED demo setup</a></li>
</ul>
<br /><br />
<div style="color:darkred; background-color:lightpink; padding:10px; display:inline-block;">
DANGER ZONE:
<br />
<br />- <a onclick="return confirm('Confirm: EMPTY Tagging tables?');" href="database?a=et">EMPTY Tagging tables</a>
<br />
<br />- <a onclick="return confirm('Confirm: EMPTY User tables?');" href="database?a=eu">EMPTY User tables</a>
<br />
<br />- <a onclick="return confirm('Confirm: EMPTY Media tables?');" href="database?a=em">EMPTY Media tables</a>
<br />
<br />- <a onclick="return confirm('Confirm: EMPTY Category tables?');"
           href="database?a=ec">EMPTY Category tables</a>
<br />
<br />- <a onclick="return confirm('Confirm: DROP tables?');" href="database?a=d">DROP ALL tables</a>
</div>
<?php

print '</div>';

$smt->includeFooter();
