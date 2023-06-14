<?php

namespace App\Services;

use App\Models\Article;

class ArticleService
{
    private static array $valuables;

    public function __construct(private readonly Article $article)
    {
        $siteName = sysConfig('website_name');
        $siteUrl = sysConfig('website_url');
        $subscribe = auth()->user()->subscribe;
        $subUrl = route('sub', $subscribe->code);

        self::$valuables = [
            '{{siteName}}' => $siteName,
            '{{urlEncodeSiteName}}' => urlencode($siteName),
            '{{urlEncodeSiteUrl}}' => urlencode($siteUrl),
            '{{siteUrl}}' => $siteUrl,
            '{{subUrl}}' => $subUrl,
            '{{urlEncodeSubUrl}}' => urlencode($subUrl),
            '{{base64SubUrl}}' => base64url_encode($subUrl),
        ];
    }

    public function getContent(): string
    {
        $content = $this->article->content;
        $this->formatAccessible($content);
        $this->formatValuables($content);

        return $content;
    }

    private function formatAccessible(string &$body): void
    {
        $noAccess = ! (new UserService)->isActivePaying();

        if ($noAccess) {
            while ($this->getInBetween($body, '<!--access_mode_1 start-->', '<!--access_mode_1 end-->', true) !== '') {
                $accessArea = $this->getInBetween($body, '<!--access_mode_1 start-->', '<!--access_mode_1 end-->');
                if ($accessArea) {
                    $body = strtr($body,
                        [$accessArea => '<div class="user-no-access"><i class="icon wb-lock" aria-hidden="true"></i>'.__('You must have a valid subscription to view the content in this area!').'</div>']);
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

    private function getInBetween(string $input, string $start, string $end, bool $bodyOnly = false): string
    {
        $substr = substr($input, strpos($input, $start) + strlen($start), strpos($input, $end) - strlen($input));

        return $bodyOnly ? $substr : $start.$substr.$end;
    }

    private function formatValuables(string &$body): void
    {
        $body = strtr($body, self::$valuables);
    }
}
