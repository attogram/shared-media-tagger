<?php
/**
 * Shared Media Tagger
 * Add Media To Collection - View
 *
 * @var array $data
 */
declare(strict_types = 1);

?>
<form>
    <div class="row bg-info pt-3 pb-2">
        <div class="col-8">
            <input class="form-control"
                   id="q"
                   name="q"
                   type="text"
                   value="<?=
                    !empty($data['query'])
                       ? htmlentities($data['query'])
                       : ''
                    ?>">
        </div>
        <div class="col-4">
            <button type="submit" name="t" value="topics"
                    class="btn btn-dark float-left m-1">Topics</button>
            <button type="submit" name="t" value="media"
                    class="btn btn-dark foat-left m-1">Media</button>
        </div>
    </div>
</form>

<?php
if (count($data['results'])) {
    switch ($data['type']) {
        case 'topics':
            $this->smt->includeTemplate('AdminAddTopics', $data['results']);
            break;
        case 'media':
            $this->smt->includeTemplate('AdminAddMedia', $data['results']);
            break;
    }
}
?>

