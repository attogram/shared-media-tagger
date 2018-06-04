<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Database Admin
 *
 * @var array $data
 */
?>
<div class="box white">
    <p>Database Admin:</p>
    <ul>
        <li>File: <?= $data['databaseName'] ?></li>
        <li>Permissions: <?= $data['databaseWriteable']
            ? '✔️OK: WRITEABLE'
            : '❌ERROR: READ ONLY' ?></li>
        <li>Size: <?= $data['databaseSize'] ?> bytes</li>
    </ul>
    <hr />

    <pre><?= $data['result'] ?></pre>

    <ul>
        <li><a href="sqladmin" target="sqlite">SQLite ADMIN</a></li>
        <li><a href="database?a=create">CREATE tables</a></li>
        <li><a href="database?a=seed">SEED demo setup</a></li>
    </ul>
    <br />
    <br />
    <div style="color:darkred;background-color:lightpink;padding:10px;display:inline-block;">
        DANGER ZONE:
        <br />
        <br />- <a onclick="return confirm('Confirm: EMPTY Tagging tables?');"
                   href="database?a=et">EMPTY Tagging tables</a>
        <br />
        <br />- <a onclick="return confirm('Confirm: EMPTY User tables?');"
                   href="database?a=eu">EMPTY User tables</a>
        <br />
        <br />- <a onclick="return confirm('Confirm: EMPTY Media tables?');"
                   href="database?a=em">EMPTY Media tables</a>
        <br />
        <br />- <a onclick="return confirm('Confirm: EMPTY Category tables?');"
                   href="database?a=ec">EMPTY Category tables</a>
        <br />
        <br />- <a onclick="return confirm('Confirm: DROP tables?');"
                   href="database?a=d">DROP ALL tables</a>
    </div>
</div>
