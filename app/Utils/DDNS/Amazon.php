<?php

namespace App\Utils\DDNS;

use App\Utils\Library\Templates\DNS;
use Arr;
use Cache;
use Http;
use Log;
use RuntimeException;
use SimpleXMLElement;

class Amazon implements DNS
{
    // 开发依据: https://docs.aws.amazon.com/zh_cn/Route53/latest/APIReference/Welcome.html
    private const API_ENDPOINT = 'https://route53.amazonaws.com';

    public const KEY = 'amazon';

    public const LABEL = 'Amazon Route 53';

    private string $accessKeyID;

    private string $secretAccessKey;

    private string $zoneID;

    public function __construct(private readonly string $subdomain)
    {
        $this->accessKeyID = sysConfig('ddns_key');
        $this->secretAccessKey = sysConfig('ddns_secret');
        $this->zoneID = $this->getZoneIdentifier();
    }

    private function getZoneIdentifier(): string
    {
        $zones = Cache::remember('ddns_get_domains', now()->addHour(), function () {
            return array_column($this->sendRequest('ListHostedZones')['HostedZones'] ?? [], 'Name', 'Id');
        });

        foreach ($zones as $zoneID => $zoneName) {
            if (str_contains("$this->subdomain.", $zoneName)) {
                return $zoneID;
            }
        }

        throw new RuntimeException('['.self::LABEL." — ListHostedZones] The subdomain $this->subdomain does not match any domain in your account.");
    }

    private function sendRequest(string $action, string $payload = ''): array
    {
        $timestamp = time();
        $client = Http::timeout(15)->retry(3, 1000)->withHeaders(['Host' => 'route53.amazonaws.com', 'X-Amz-Date' => gmdate("Ymd\THis\Z", $timestamp), 'Accept' => 'application/json'])->baseUrl(self::API_ENDPOINT);

        $uri = match ($action) {
            'ListHostedZones' => '/2013-04-01/hostedzone',
            'ListResourceRecordSets', 'ChangeResourceRecordSets' => "/2013-04-01$this->zoneID/rrset",
        };

        $response = match ($action) {
            'ListHostedZones', 'ListResourceRecordSets' => $client->withHeader('Authorization', $this->generateSignature('GET', $uri, $payload, $timestamp))->get($uri),
            'ChangeResourceRecordSets' => $client->withHeader('Authorization', $this->generateSignature('POST', $uri, $payload, $timestamp))->withBody($payload, 'application/xml')->post($uri),
        };

        $data = $response->json();
        if ($response->successful()) {
            return $data;
        }

        if ($data) {
            Log::error('['.self::LABEL." — $action] 返回错误信息: ".$data['message'] ?? 'Unknown error');
        } else {
            Log::error('['.self::LABEL." — $action] 请求失败");
        }

        exit(400);
    }

    private function generateSignature(string $method, string $uri, string $payload, int $timestamp): string
    { // 签名
        $dateTime = gmdate("Ymd\THis\Z", $timestamp);
        $date = gmdate('Ymd', $timestamp);
        $canonicalRequest = "$method\n$uri\n\naccept:application/json\nhost:route53.amazonaws.com\nx-amz-date:$dateTime\n\naccept;host;x-amz-date\n".hash('sha256', $payload);
        $credentialScope = "$date/us-east-1/route53/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n$dateTime\n$credentialScope\n".hash('sha256', $canonicalRequest);
        $dateKey = hash_hmac('SHA256', $date, 'AWS4'.$this->secretAccessKey, true);
        $regionKey = hash_hmac('SHA256', 'us-east-1', $dateKey, true);
        $serviceKey = hash_hmac('SHA256', 'route53', $regionKey, true);
        $SigningKey = hash_hmac('SHA256', 'aws4_request', $serviceKey, true);
        $signature = hash_hmac('SHA256', $stringToSign, $SigningKey);

        return "AWS4-HMAC-SHA256 Credential=$this->accessKeyID/$credentialScope, SignedHeaders=accept;host;x-amz-date, Signature=$signature";
    }

    public function update(string $latest_ip, string $original_ip, string $type): bool
    {
        $record = $this->getRecords($type, $original_ip);

        if ($record) {
            array_walk_recursive($record, static function (&$value) use ($latest_ip, $original_ip) {
                if ($value === $original_ip) {
                    $value = $latest_ip;
                }
            });

            $response = $this->sendRequest('ChangeResourceRecordSets', $this->generateChangeResourceRecordSetsXml('UPSERT', $record));

            return isset($response['ChangeInfo']['Status']) && $response['ChangeInfo']['Status'] === 'PENDING';
        }

        return $this->store($latest_ip, $type);
    }

    private function getRecords(string $type, string $ip): array
    {
        $response = $this->sendRequest('ListResourceRecordSets');

        if (isset($response['ResourceRecordSets'])) {
            $records = $response['ResourceRecordSets'];

            $filterCondition = function ($record) use ($type, $ip) {
                if ($type && $record['Type'] !== $type) {
                    return false;
                }

                if ($ip && ! in_array($ip, Arr::pluck($record['ResourceRecords'], 'Value'), true)) {
                    return false;
                }

                return $record['Name'] === "$this->subdomain.";
            };

            $filteredRecords = array_filter($records, $filterCondition);

            return array_map(static function ($record) {
                $resourceValues = Arr::pluck($record['ResourceRecords'], 'Value');

                return [
                    'Name' => $record['Name'],
                    'Type' => $record['Type'],
                    'TTL' => $record['TTL'],
                    'ResourceRecords' => $resourceValues,
                ];
            }, $filteredRecords);
        }

        return [];
    }

    private function generateChangeResourceRecordSetsXml(string $action, array $records): string
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><ChangeResourceRecordSetsRequest xmlns="https://route53.amazonaws.com/doc/2013-04-01/"></ChangeResourceRecordSetsRequest>');

        $changeBatch = $xml->addChild('ChangeBatch');
        $changes = $changeBatch->addChild('Changes');

        foreach ($records as $record) {
            $change = $changes->addChild('Change');
            $change->addChild('Action', $action);

            $resourceRecordSet = $change->addChild('ResourceRecordSet');
            $resourceRecordSet->addChild('Name', htmlspecialchars($record['Name'])); // Escape special characters in Name
            $resourceRecordSet->addChild('Type', $record['Type']);
            $resourceRecordSet->addChild('TTL', $record['TTL']);

            $resourceRecords = $resourceRecordSet->addChild('ResourceRecords');
            foreach ($record['ResourceRecords'] as $value) {
                $resourceRecord = $resourceRecords->addChild('ResourceRecord');
                $resourceRecord->addChild('Value', $value);
            }
        }

        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }

    public function store(string $ip, string $type): bool
    {
        $response = $this->sendRequest('ChangeResourceRecordSets', $this->generateChangeResourceRecordSetsXml('UPSERT', [['Name' => $this->subdomain, 'Type' => $type, 'TTL' => 300, 'ResourceRecords' => [$ip]]]));

        return isset($response['ChangeInfo']['Status']) && $response['ChangeInfo']['Status'] === 'PENDING';
    }

    public function destroy(string $type, string $ip): bool
    {
        $records = $this->getRecords($type, $ip);

        if ($records) {
            $response = $this->sendRequest('ChangeResourceRecordSets', $this->generateChangeResourceRecordSetsXml('DELETE', $records));

            return isset($response['ChangeInfo']['Status']) && $response['ChangeInfo']['Status'] === 'PENDING';
        }

        return true;
    }
}
