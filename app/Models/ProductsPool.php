<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 产品名称池
 *
 * @property int                             $id
 * @property string|null                     $name       名称
 * @property int|null                        $min_amount 适用最小金额，单位分
 * @property int|null                        $max_amount 适用最大金额，单位分
 * @property int                             $status     状态：0-未启用、1-已启用
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 最后更新时间
 * @method static Builder|ProductsPool newModelQuery()
 * @method static Builder|ProductsPool newQuery()
 * @method static Builder|ProductsPool query()
 * @method static Builder|ProductsPool whereCreatedAt($value)
 * @method static Builder|ProductsPool whereId($value)
 * @method static Builder|ProductsPool whereMaxAmount($value)
 * @method static Builder|ProductsPool whereMinAmount($value)
 * @method static Builder|ProductsPool whereName($value)
 * @method static Builder|ProductsPool whereStatus($value)
 * @method static Builder|ProductsPool whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductsPool extends Model {
	protected $table = 'products_pool';
	protected $primaryKey = 'id';
}
