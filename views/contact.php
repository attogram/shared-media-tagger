<?php
/**
 * Shared Media Tagger
 * Contact
 *
 * @var \Attogram\SharedMedia\Tagger\Tagger $smt
 */

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

$smt->title = 'Contact - ' . Config::$siteName;
$smt->includeHeader();
$smt->includeMediumMenu();

print '<div class="box white">';

if (isset($_POST['c'])) {
    $comment = urldecode($_POST['c']);
    $remoteAddr = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    $insert = $smt->database->queryAsBool(
        'INSERT INTO contact (comment, datetime, ip) VALUES (:comment, CURRENT_TIMESTAMP, :ip)',
        [':comment' => $comment, ':ip' => $remoteAddr]
    );
    if ($insert) {
        print '<p>Thank you for your message.</p>';
        print '<p>You sent the following:</p>';
        print '<pre style="background-color:lightsalmon;">' . htmlentities($comment) . '</pre>';
        print '</div>';
        $smt->includeFooter();
        exit;
    }
    print '<p>Error accessing database.  Try again later.</p>';
}

$headline = '<p>Contact us today!</p>';
$innertext = '* My Question or Comment:



';
$footer = '
* My Contact information:


';

if (isset($_GET['r']) && Tools::isPositiveNumber($_GET['r'])) {
    $pageid = (int)$_GET['r'];
    $media = $smt->database->getMedia($pageid);
    if (!$media || !isset($media[0])) {
        Tools::notice('ERROR: no media ID #' . $pageid . ' found.');
        goto form;
    }
    $media = $media[0];
    $headline = '<p>REPORT file #' . $pageid . '<br />' . $smt->displayThumbnail($media) . '</p>';
    $innertext = '
* REPORT file #' . $pageid . ':
* Reason:


';
}


form:

print $headline . '<form method="POST">
<textarea name="c" rows="12" cols="60">' . $innertext . $footer . '</textarea>
<p><input type="submit" value="           Send message          "></p>
</form><br /></div>';

$smt->includeFooter();
