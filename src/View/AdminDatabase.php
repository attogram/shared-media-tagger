<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Database Admin
 *
 * @var array $data
 */
?>
<div class="white">
    <p>Database Admin:</p>
    <ul>
        <li>File: <?= $data['databaseName'] ?></li>
        <li>Permissions: <?= $data['databaseWriteable']
            ? '✔️OK: WRITEABLE'
            : '❌ERROR: READ ONLY' ?></li>
        <li>Size: <?= $data['databaseSize'] ?> bytes</li>
    </ul>
    <hr />

    <pre class="error"><?= $data['result'] ?></pre>

    <ul>
        <li><a href="?a=create">CREATE tables</a></li>
        <li><a href="?a=seed">SEED demo setup</a></li>
    </ul>
    <br />
    <br />
    <div style="color:darkred;background-color:lightpink;padding:10px;display:inline-block;">
        DANGER ZONE:
        <br />
        <br />- <a onclick="return confirm('Confirm: EMPTY Tagging tables?');"
                   href="?a=et">EMPTY Tagging tables</a>
        <br />
        <br />- <a onclick="return confirm('Confirm: EMPTY User tables?');"
                   href="?a=eu">EMPTY User tables</a>
        <br />
        <br />- <a onclick="return confirm('Confirm: EMPTY Media tables?');"
                   href="?a=em">EMPTY Media tables</a>
        <br />
        <br />- <a onclick="return confirm('Confirm: EMPTY Category tables?');"
                   href="?a=ec">EMPTY Category tables</a>
        <br />
        <br />- <a onclick="return confirm('Confirm: DROP tables?');"
                   href="?a=d">DROP ALL tables</a>
    </div>
</div>
