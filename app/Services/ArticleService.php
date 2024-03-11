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
        $mode1Start = '<!--access_mode_1 start-->';
        $mode1End = '<!--access_mode_1 end-->';
        $mode2Start = '<!--access_mode_2 start-->';
        $mode2End = '<!--access_mode_2 end-->';
        $mode2Else = '<!--access_mode_2 else-->';

        if ($noAccess) {
            while (($accessArea = $this->getInBetween($body, $mode1Start, $mode1End)) !== '') {
                $replacement = '<div class="user-no-access"><i class="icon wb-lock" aria-hidden="true"></i>'.__('You must have a valid subscription to view the content in this area!').'</div>';
                $body = str_replace($mode1Start.$accessArea.$mode1End, $replacement, $body);
            }
        }

        while (($accessArea = $this->getInBetween($body, $mode2Start, $mode2End)) !== '') {
            $hasAccessArea = $this->getInBetween($accessArea, '', $mode2Else);
            $noAccessArea = $this->getInBetween($accessArea, $mode2Else, '');
            $body = strtr($body, [$mode2Start.$accessArea.$mode2End => $noAccess ? $noAccessArea : $hasAccessArea]);
        }
    }

    private function getInBetween(string $input, string $start, string $end): string
    {
        $startPos = stripos($input, $start);
        $endPos = stripos($input, $end, $startPos !== false ? $startPos + strlen($start) : 0);

        if ($startPos === false || $endPos === false) {
            return '';
        }

        return substr($input, $startPos + strlen($start), $endPos - ($startPos + strlen($start)));
    }

    private function formatValuables(string &$body): void
    {
        $body = strtr($body, self::$valuables);
    }
}
