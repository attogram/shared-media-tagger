<?php
/**
 * Shared Media Tagger
 * Media Admin
 *
 * @var Attogram\SharedMedia\Tagger\TaggerAdmin $smt
 */

declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

$smt->title = 'Media Admin';
$smt->includeHeader();
$smt->includeMediumMenu();
$smt->includeAdminMenu();
print '<div class="box white"><p>Media Admin:</p>';

if (isset($_GET['am'])) {
    print $smt->addMedia($_GET['am']);
    $smt->database->updateCategoriesLocalFilesCount();
    print '<hr />';
}

if (isset($_GET['media'])) {
    print multiDeleteMedia($smt, $_GET['media']);
    $smt->database->updateCategoriesLocalFilesCount();
    print '<hr />';
}

if (isset($_GET['dm'])) {
    print $smt->database->deleteMedia($_GET['dm']);
    $smt->database->updateCategoriesLocalFilesCount();
    print '<hr />';
}

if (isset($_GET['dc'])) {
    print deleteMediaInCategory($smt, Tools::categoryUrldecode($_GET['dc']));
    $smt->database->updateCategoriesLocalFilesCount();
    print '<hr />';
}

// MENU ////////////////////////////////////////////
?>
<form action="" method="GET">
* Add Media:
<input type="text" name="am" value="" size="10" />
<input type="submit" value="  Add via pageid  "/>
</form>
<br /><br />
<form action="" method="GET">
* Delete &amp; Block Media:
<input type="text" name="dm" value="" size="10" />
<input type="submit" value="  Delete via pageid  "/>
</form>
<br /><br />
<form action="" method="GET">
* Delete &amp; Block All Media in Category:
<input type="text" name="dc" value="" size="30" />
<input type="submit" value="  Delete via Category Name  "/>
</form>
<br /><br />
* <a href="./media-blocked">View/Edit Blocked Media</a>
<br /><br />
<?php

print '</div>';
$smt->includeFooter();


/**
 * @param \Attogram\SharedMedia\Tagger\TaggerAdmin $smt
 * @param $list
 * @return bool|string
 */
function multiDeleteMedia(\Attogram\SharedMedia\Tagger\TaggerAdmin $smt, $list)
{
    if (!is_array($list)) {
        Tools::error('multi_delete_media: No list array found');
        return false;
    }
    $response = '<p>Deleting &amp; Blocking ' . sizeof($list) . ' Media files:';
    foreach ($list as $mediaId) {
        $response .= $smt->database->deleteMedia($mediaId);
    }

    return $response;
}

/**
 * @param \Attogram\SharedMedia\Tagger\TaggerAdmin $smt
 * @param $category_name
 * @return bool|string
 */
function deleteMediaInCategory(\Attogram\SharedMedia\Tagger\TaggerAdmin $smt, $category_name)
{
    if (!$category_name || !is_string($category_name)) {
        Tools::error('::delete_media_in_category: Invalid Category Name: ' . $category_name);
        return false;
    }
    $return = '<div style="white-space:nowrap; font-family:monospace; background-color:lightsalmon;">'
        . 'Deleting Media in <b>' . $category_name . '</b>';
    $media = $smt->database->getMediaInCategory($category_name);
    $return .= '<br /><b>' . count($media) . '</b> Media files found in Category';
    foreach ($media as $pageid) {
        $return .= '<br />Deleting #' . $pageid;
        $return .= $smt->database->deleteMedia($pageid, true);
    }
    $return .= '</div><br />';

    return $return;
}
