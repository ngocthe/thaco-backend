<?php

namespace App\Models;
use App\Traits\CreatedUpdatedByAdmin;
use App\Traits\HasRemark;
use App\Traits\WhereInMultiple;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Bom
 *
 * @property int $id
 * @property string $msc_code
 * @property string $shop_code
 * @property string $part_code
 * @property string $part_color_code
 * @property int|null $quantity
 * @property string $ecn_in
 * @property string|null $ecn_out
 * @property string $plant_code
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Admin $updatedBy
 * @property-read string $remark
 * @property-read Remark|null $remarkable
 * @property-read Ecn|null $ecnInInfo
 * @property-read Ecn|null $ecnOutInfo
 * @method static Builder|Bom newModelQuery()
 * @method static Builder|Bom newQuery()
 * @method static \Illuminate\Database\Query\Builder|Bom onlyTrashed()
 * @method static Builder|Bom query()
 * @method static Builder|Bom whereCreatedAt($value)
 * @method static Builder|Bom whereCreatedBy($value)
 * @method static Builder|Bom whereDeletedAt($value)
 * @method static Builder|Bom whereEcnIn($value)
 * @method static Builder|Bom whereEcnOut($value)
 * @method static Builder|Bom whereId($value)
 * @method static Builder|Bom whereMscCode($value)
 * @method static Builder|Bom wherePartCode($value)
 * @method static Builder|Bom wherePartColorCode($value)
 * @method static Builder|Bom wherePlantCode($value)
 * @method static Builder|Bom whereQuantity($value)
 * @method static Builder|Bom whereShopCode($value)
 * @method static Builder|Bom whereUpdatedAt($value)
 * @method static Builder|Bom whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|Bom withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Bom withoutTrashed()
 * @mixin Eloquent
 */
class Bom extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'msc_code',
		'shop_code',
		'part_code',
		'part_color_code',
		'quantity',
		'ecn_in',
		'ecn_out',
		'plant_code',
        'part_remarks'
    ];

    /**
     * @return hasOne
     */
    public function ecnInInfo(): hasOne
    {
        return $this->hasOne(Ecn::class, 'code', 'ecn_in');
    }

    /**
     * @return hasOne
     */
    public function ecnOutInfo(): hasOne
    {
        return $this->hasOne(Ecn::class, 'code', 'ecn_out');
    }
}
