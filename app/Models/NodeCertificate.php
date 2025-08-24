<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * 伪装域名证书.
 */
class NodeCertificate extends Model
{
    protected $table = 'node_certificate';

    protected $guarded = [];

    protected $appends = ['issuer', 'from', 'to'];

    private $certInfo = null;

    protected function getCertInfo(): ?array
    {
        if ($this->certInfo === null && $this->pem) {
            $this->certInfo = openssl_x509_parse($this->pem) ?: false;
        }

        return $this->certInfo ?: null;
    }

    protected function issuer(): Attribute
    {
        return Attribute::make(
            get: function () {
                $certInfo = $this->getCertInfo();

                return $certInfo ? ($certInfo['issuer']['O'] ?? null) : null;
            }
        );
    }

    protected function from(): Attribute
    {
        return Attribute::make(
            get: function () {
                $certInfo = $this->getCertInfo();
                if ($certInfo && isset($certInfo['validFrom_time_t'])) {
                    return date('Y-m-d', $certInfo['validFrom_time_t']);
                }

                return null;
            }
        );
    }

    protected function to(): Attribute
    {
        return Attribute::make(
            get: function () {
                $certInfo = $this->getCertInfo();
                if ($certInfo && isset($certInfo['validTo_time_t'])) {
                    return date('Y-m-d', $certInfo['validTo_time_t']);
                }

                return null;
            }
        );
    }
}
