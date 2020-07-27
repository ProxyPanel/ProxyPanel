<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 伪装域名证书
 *
 * @property int                        $id
 * @property string                     $domain 域名
 * @property string|null                $key    域名证书KEY
 * @property string|null                $pem    域名证书PEM
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static Builder|NodeCertificate newModelQuery()
 * @method static Builder|NodeCertificate newQuery()
 * @method static Builder|NodeCertificate query()
 * @method static Builder|NodeCertificate whereCreatedAt($value)
 * @method static Builder|NodeCertificate whereDomain($value)
 * @method static Builder|NodeCertificate whereId($value)
 * @method static Builder|NodeCertificate whereKey($value)
 * @method static Builder|NodeCertificate wherePem($value)
 * @method static Builder|NodeCertificate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NodeCertificate extends Model {
	protected $table = 'node_certificate';
}
