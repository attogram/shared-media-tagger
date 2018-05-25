<?php

declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger;

/**
 * Class Commons
 */
class Commons
{
    private $commonsApiUrl;
    private $propImageinfo;
    public $totalHits;
    public $continue;
    public $sroffset;
    public $batchComplete;
    public $commonsResponse;

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
     * @param $url
     * @param string $key
     * @return bool
     */
    public function callCommons($url, $key = '')
    {
        if (!$url) {
            Tools::error('::call_commons: ERROR: no url');

            return false;
        }
        $getResponse = @file_get_contents($url);

        if ($getResponse === false) {
            Tools::error('Cannnot reach API endpoint'
                . '<br />URL: <a target="commons" href="' . $url . '">' . $url  .'</a>'
                . '<br />Exiting.');
            print '</div>';

            exit;
        }
        $this->commonsResponse = json_decode($getResponse, true);
        if (!$this->commonsResponse) {
            Tools::error('::call_commons: ERROR: json_decode failed. Error: ' . json_last_error());
            Tools::error('::call_commons: ERROR: ' . $this->smtJsonLastErrorMsg());

            return false;
        }

        if (empty($this->commonsResponse['query'][$key])
            || !$this->commonsResponse['query'][$key]
            || !is_array($this->commonsResponse['query'][$key])
        ) {
            Tools::error("::call_commons: WARNING: missing key: $key");
        }

        $this->totalHits = $this->continue = $this->batchComplete = false;

        if (isset($this->commonsResponse['batchcomplete'])) {
            $this->batchComplete = true;
        }
        if (isset($this->commonsResponse['query']['searchinfo']['totalhits'])) {
            $this->totalHits = $this->commonsResponse['query']['searchinfo']['totalhits'];
            Tools::notice('::call_commmons: totalhits=' . $this->totalHits);
        }
        if (isset($this->commonsResponse['continue'])) {
            $this->continue = $this->commonsResponse['continue']['continue'];
        }
        if (isset($this->commonsResponse['sroffset'])) {
            $this->sroffset = $this->commonsResponse['continue']['sroffset'];
        }
        if (isset($this->commonsResponse['warnings'])) {
            Tools::error('::call_commons: ' . print_r($this->commonsResponse['warnings'], true));
            Tools::error('::call_commons: url: ' . $url);
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
    public function getApiCategorymembers($category)
    {
        $url = $this->commonsApiUrl . '?action=query&format=json'
            . '&list=categorymembers'
            . '&cmtype=file'
            . '&cmprop=ids'
            . '&cmlimit=500'
            . '&cmtitle=' . urlencode($category);
        if (!$this->callCommons($url, 'categorymembers')
            || !isset($this->commonsResponse['query']['categorymembers'])
        ) {
            Tools::error('::get_api_categorymembers: ERROR: call');

            return [];
        }
        $pageids = [];
        foreach ($this->commonsResponse['query']['categorymembers'] as $cat) {
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
            . '&iiurlwidth=' . $this->sizeMedium // @TODO get size
            . '&iilimit=50'
            . '&pageids=' . implode('|', $pageids);
        if (!$this->callCommons($call, 'pages')
            || !isset($this->commonsResponse['query']['pages'])
        ) {
            Tools::error('::get_api_imageinfo: ERROR: call');

            return [];
        }

        $pages = $this->commonsResponse['query']['pages'];

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
            Tools::error('::get_api_imageinfo: TOO MUCH RECURSION: ' . $recurseCount);

            return $pages;
        }
        $recurseCount++;
        if ($errors) {
            Tools::error('::get_api_imageinfo: CALL #' . $recurseCount . ': ' . sizeof($errors) . ' EMPTY files');
            $second = $this->getApiImageinfo($errors, $recurseCount);
            Tools::notice('::get_api_imageinfo: CALL #' . $recurseCount . ': GOT: ' . sizeof($second) . ' files');
            $pages = array_merge($pages, $second);
            Tools::notice('::get_api_imageinfo: CALL #' . $recurseCount . ': total pages: '
                . sizeof($pages) . ' files');
        }

        return $pages;
    }
}
