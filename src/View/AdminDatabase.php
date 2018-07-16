<?php
/**
 * Shared Media Tagger
 * Database Admin
 *
 * @var array $data
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="row bg-white">
    <div class="col-12 mb-4">
        <p>Database Admin</p>
        <ul>
            <li>File: <kbd><?= $data['databaseName'] ?></kbd></li>
            <li>Permissions: <?= $data['databaseWriteable']
                ? '✅️OK: WRITEABLE'
                : '❌ERROR: READ ONLY' ?></li>
            <li>Size: <?= $data['databaseSize'] ?> bytes</li>
            <li><a href="<?= Tools::url('admin') ?>/reports/">Reports</a></li>
        </ul>
        <pre class="error"><?= $data['result'] ?></pre>
        <div class="bg-warning mt-5 mb-5 pb-2 pl-2">
            <h3>
                WARNING ZONE
            </h3>
            <ul>
                <li><a class="text-dark" href="?a=create">CREATE tables</a></li>
                <li><a class="text-dark" href="?a=seed">SEED demo setup</a></li>
            </ul>
        </div>
        <div class="bg-danger mt-5 mb-5 pb-2 pl-2">
            <h3>
                DANGER ZONE
            </h3>
            <ul>
                <li><a onclick="return confirm('Confirm: EMPTY Tagging tables?');"
                       class="text-dark" href="?a=et">EMPTY Tagging tables</a></li>
                <li><a onclick="return confirm('Confirm: EMPTY User tables?');"
                       class="text-dark" href="?a=eu">EMPTY User tables</a></li>
                <li><a onclick="return confirm('Confirm: EMPTY Media tables?');"
                       class="text-dark" href="?a=em">EMPTY Media tables</a></li>
                <li><a onclick="return confirm('Confirm: EMPTY Category tables?');"
                       class="text-dark" href="?a=ec">EMPTY Category tables</a></li>
                <li><a onclick="return confirm('Confirm: DROP tables?');"
                       class="text-dark" href="?a=d">DROP ALL tables</a></li>
            </ul>
        </div>
    </div>
</div>
