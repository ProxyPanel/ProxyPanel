<?php

namespace App\Services;

class ArticleService extends BaseService
{
    private static $article;
    private static $valuables;

    public function __construct($article)
    {
        parent::__construct();
        self::$article = $article;
        $siteName = sysConfig('website_name');
        $siteUrl = sysConfig('website_url');
        $subscribe = auth()->user()->subscribe;
        $subUrl = route('sub', $subscribe->code);

        self::$valuables = [
            '{{siteName}}'           => $siteName,
            '{{urlEndcodeSiteName}}' => urlencode($siteName),
            '{{urlEndcodeSiteUrl}}'  => urlencode($siteUrl),
            '{{siteUrl}}'            => $siteUrl,
            '{{subUrl}}'             => $subUrl,
            '{{urlEncodeSubUrl}}'    => urlencode($subUrl),
            '{{base64SubUrl}}'       => base64url_encode($subUrl),
        ];
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        $content = self::$article->content;
        if (! UserService::getInstance()->isActivePaying()) {
            $this->formatAccessable($content);
        }

        $this->formatValuables($content);

        return $content;
    }

    private function formatAccessable(&$body)
    {
        while (strpos($body, '<!--access start-->') !== false) {
            $accessData = $this->getInBetween($body, '<!--access start-->', '<!--access end-->');
            if ($accessData) {
                $body = strtr($body, [
                    $accessData => '<div class="user-no-access"><i class="icon wb-lock" aria-hidden="true"></i>'.__('You must have a valid subscription to view content in this area!').'</div>',
                ]);
            }
        }
    }

    private function getInBetween($input, $start, $end): string
    {
        $substr = substr($input, strlen($start) + strpos($input, $start), (strlen($input) - strpos($input, $end)) * (-1));

        return $start.$substr.$end;
    }

    private function formatValuables(&$body)
    {
        $body = strtr($body, self::$valuables);
    }
}
