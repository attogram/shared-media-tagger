<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger;

use Attogram\SharedMedia\Tagger\Database\Database;
use Attogram\SharedMedia\Tagger\Database\DatabaseAdmin;

/**
 * Class Commons
 */
class Commons
{

    /** @var Database|DatabaseAdmin */
    private $database;
    private $propImageinfo;

    public $topics;
    public $commonsApiUrl;
    public $totalHits;
    public $continue;
    public $sroffset;
    public $batchComplete;
    public $response;

    /**
     * Commons constructor.
     */
    public function __construct()
    {
        $this->commonsApiUrl = 'https://commons.wikimedia.org/w/api.php';
        $this->propImageinfo = '&prop=imageinfo'
            . '&iiprop=url|size|mime|thumbmime|user|userid|sha1|timestamp|extmetadata'
            . '&iiextmetadatafilter=LicenseShortName|UsageTerms|AttributionRequired|'
            . 'Restrictions|Artist|ImageDescription|DateTimeOriginal';
    }

    /**
     * @param Database|DatabaseAdmin $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * @param $url
     * @param string $key
     * @return bool
     */
    public function callCommons($url, $key = '')
    {
        if (!$url) {
            Tools::error('callCommons: ERROR: no url');

            return false;
        }
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $getResponse = @file_get_contents($url);
        if ($getResponse === false) {
            Tools::error('Cannnot reach API endpoint'
                . '<br />URL: <a target="c" href="' . $url . '">' . $url  .'</a>'
                . '<br />Exiting.');
            print '</div>';

            exit;
        }
        $this->response = json_decode($getResponse, true);
        if (!$this->response) {
            Tools::error('callCommons: ERROR: json_decode failed. Error: ' . json_last_error());
            Tools::error('callCommons: ERROR: ' . $this->smtJsonLastErrorMsg());

            return false;
        }
        if (empty($this->response['query'][$key])
            || !$this->response['query'][$key]
            || !is_array($this->response['query'][$key])
        ) {
            Tools::error("callCommons: WARNING: missing key: $key");
        }
        $this->totalHits = $this->continue = $this->batchComplete = false;
        if (isset($this->response['batchcomplete'])) {
            $this->batchComplete = true;
        }
        if (isset($this->response['query']['searchinfo']['totalhits'])) {
            $this->totalHits = $this->response['query']['searchinfo']['totalhits'];
            Tools::notice('callCommons: totalhits=' . $this->totalHits);
        }
        if (isset($this->response['continue'])) {
            $this->continue = $this->response['continue']['continue'];
        }
        if (isset($this->response['sroffset'])) {
            $this->sroffset = $this->response['continue']['sroffset'];
        }
        if (isset($this->response['warnings'])) {
            Tools::error('callCommons: ' . print_r($this->response['warnings'], true));
            Tools::error('callCommons: url: ' . $url);
        }

        return true;
    }

    /**
     * @return mixed|string
     */
    public function smtJsonLastErrorMsg()
    {
        static $errors = [
            JSON_ERROR_NONE           => null,
            JSON_ERROR_DEPTH          => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR      => 'Unexpected control character found',
            JSON_ERROR_SYNTAX         => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        ];
        $error = json_last_error();

        return array_key_exists($error, $errors) ? $errors[$error] : "Unknown error ({$error})";
    }

    /**
     * @see https://www.mediawiki.org/wiki/API:Categorymembers
     * @param $category
     * @return array
     */
    public function getApiTopicmembers($topic)
    {
        $url = $this->commonsApiUrl . '?action=query&format=json'
            . '&list=categorymembers'
            . '&cmtype=file'
            . '&cmprop=ids'
            . '&cmlimit=500'
            . '&cmtitle=' . urlencode($topic);
        if (!$this->callCommons($url, 'categorymembers')
            || !isset($this->response['query']['categorymembers'])
        ) {
            Tools::error('getApiTopicmembers: ERROR: call');

            return [];
        }
        $pageids = [];
        foreach ($this->response['query']['categorymembers'] as $cat) {
            $pageids[] = $cat['pageid'];
        }
        if (!$pageids) {
            return [];
        }

        return $pageids;
    }

    /**
     * @param $pageids
     * @param int $recurseCount
     * @return array
     */
    public function getApiImageinfo($pageids, $recurseCount = 0)
    {
        $call = $this->commonsApiUrl . '?action=query&format=json'
            . $this->propImageinfo
            . '&iiurlwidth=' . Config::$sizeMedium // @TODO get size
            . '&iilimit=50'
            . '&pageids=' . implode('|', $pageids);
        if (!$this->callCommons($call, 'pages') || !isset($this->response['query']['pages'])) {
            Tools::error('getApiImageinfo: ERROR: call');

            return [];
        }
        $pages = $this->response['query']['pages'];
        $errors = [];
        foreach ($pages as $media) {
            if (!isset($media['imageinfo'][0]['url'])) {
                $errors[] = $media['pageid'];
                unset($pages[ $media['pageid'] ]);
            }
        }
        if (!$recurseCount) {
            return $pages;
        }
        if ($recurseCount > 5) {
            Tools::error('getApiImageinfo: TOO MUCH RECURSION: ' . $recurseCount);

            return $pages;
        }
        $recurseCount++;
        if ($errors) {
            Tools::error('getApiImageinfo: CALL #' . $recurseCount . ': ' . sizeof($errors) . ' EMPTY files');
            $second = $this->getApiImageinfo($errors, $recurseCount);
            Tools::notice('getApiImageinfo: CALL #' . $recurseCount . ': GOT: ' . sizeof($second) . ' files');
            $pages = array_merge($pages, $second);
            Tools::notice('getApiImageinfo: CALL #' . $recurseCount . ': total pages: '
                . sizeof($pages) . ' files');
        }

        return $pages;
    }

    // Admin

    /**
     * @param int|string $pageid
     * @return bool
     */
    public function getTopicsFromMedia($pageid)
    {
        if (!$pageid || !Tools::isPositiveNumber($pageid)) {
            Tools::error('getTopicsFromMedia: invalid pageid');

            return false;
        }
        $call = $this->commonsApiUrl . '?action=query&format=json'
            . '&prop=categories'
            . '&pageids=' . $pageid
        ;
        if (!$this->callCommons($call, 'pages')) {
            Tools::error('getTopicsFromMedia: nothing found');

            return false;
        }
        $this->topics = !empty($this->response['query']['pages'][$pageid]['categories'])
            ? $this->response['query']['pages'][$pageid]['categories']
            : null;

        return true;
    }

    /**
     * @param string $category
     * @return array
     */
    public function getTopicInfo($category)
    {
        if (!$category || $category=='' || !is_string($category)) {
            Tools::error('getTopicInfo: no category');

            return [];
        }
        $call = $this->commonsApiUrl
            . '?action=query&format=json&prop=categoryinfo&titles=' . urlencode($category); // @TODO cicontinue
        if (!$this->callCommons($call, 'pages')) {
            Tools::error('getTopicInfo: API: nothing found');

            return [];
        }
        if (isset($this->response['query']['pages'])) {
            return $this->response['query']['pages'];
        }
        Tools::error('getTopicInfo: API: no pages');

        return [];
    }

    /**
     * @param string $category
     * @return bool
     */
    public function getSubcats($category)
    {
        if (!$category || $category=='' || !is_string($category)) {
            Tools::error('getSubcats: ERROR - no category');

            return false;
        }
        Tools::notice('getSubcats: ' . $category);
        $call = $this->commonsApiUrl . '?action=query&format=json&cmlimit=50'
            . '&list=categorymembers'
            . '&cmtype=subcat'
            . '&cmprop=title'
            . '&cmlimit=500'
            . '&cmtitle=' . urlencode($category) ;
        if (!$this->callCommons($call, 'categorymembers')
            || !isset($this->response['query']['categorymembers'])
            || !is_array($this->response['query']['categorymembers'])
        ) {
            Tools::error('getSubcats: Nothing Found');

            return false;
        }
        foreach ($this->response['query']['categorymembers'] as $subcat) {
            $this->database->insertTopic($subcat['title']);
        }

        return true;
    }

    /**
     * @param string $search
     * @return bool
     */
    public function findTopics($search = '')
    {
        if (!$search || $search == '' || !is_string($search)) {
            Tools::error('findTopics: invalid search string: ' . $search);

            return false;
        }
        $call = $this->commonsApiUrl . '?action=query&format=json'
            . '&list=search'
            . '&srnamespace=14' // 6 = File   14 = Topic
            . '&srprop=size|snippet' // titlesnippet|timestamp|title
            . '&srlimit=500'
            . '&srsearch=' . urlencode($search);
        if (!$this->callCommons($call, 'search')) {
            Tools::error('findTopics: nothing found');

            return false;
        }

        return true;
    }
}
