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
        $this->formatAccessable($content);
        $this->formatValuables($content);

        return $content;
    }

    private function formatAccessable(&$body)
    {
        $noAccess = ! UserService::getInstance()->isActivePaying();

        if ($noAccess) {
            while ($this->getInBetween($body, '<!--access_mode_1 start-->', '<!--access_mode_1 end-->', true) !== '') {
                $accessArea = $this->getInBetween($body, '<!--access_mode_1 start-->', '<!--access_mode_1 end-->');
                if ($accessArea) {
                    $body = strtr($body,
                        [$accessArea => '<div class="user-no-access"><i class="icon wb-lock" aria-hidden="true"></i>'.__('You must have a valid subscription to view content in this area!').'</div>']);
                }
            }
        }

        while ($this->getInBetween($body, '<!--access_mode_2 start-->', '<!--access_mode_2 end-->', true) !== '') {
            $accessArea = $this->getInBetween($body, '<!--access_mode_2 start-->', '<!--access_mode_2 end-->');
            $hasAccessArea = $this->getInBetween($accessArea, '<!--access_mode_2 start-->', '<!--access_mode_2 else-->', true);
            $noAccessArea = $this->getInBetween($accessArea, '<!--access_mode_2 else-->', '<!--access_mode_2 end-->', true);
            $body = strtr($body, [$accessArea => $accessArea && $noAccess ? $noAccessArea : $hasAccessArea]);
        }
    }

    private function getInBetween($input, $start, $end, $bodyOnly = false): string
    {
        $substr = substr($input, strpos($input, $start) + strlen($start), strpos($input, $end) - strlen($input));

        return $bodyOnly ? $substr : $start.$substr.$end;
    }

    private function formatValuables(&$body)
    {
        $body = strtr($body, self::$valuables);
    }
}
