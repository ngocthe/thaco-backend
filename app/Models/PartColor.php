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
 * App\Models\PartColor
 *
 * @property int $id
 * @property string $code
 * @property string $part_code
 * @property string $name
 * @property string|null $interior_code
 * @property string|null $vehicle_color_code
 * @property string $ecn_in
 * @property string|null $ecn_out
 * @property string $plant_code
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Ecn|null $ecnInInfo
 * @property-read Ecn|null $ecnOutInfo
 * @property-read Admin $updatedBy
 * @property-read string $remark
 * @property-read Remark|null $remarkable
 * @method static Builder|PartColor newModelQuery()
 * @method static Builder|PartColor newQuery()
 * @method static \Illuminate\Database\Query\Builder|PartColor onlyTrashed()
 * @method static Builder|PartColor query()
 * @method static Builder|PartColor whereCode($value)
 * @method static Builder|PartColor whereCreatedAt($value)
 * @method static Builder|PartColor whereCreatedBy($value)
 * @method static Builder|PartColor whereDeletedAt($value)
 * @method static Builder|PartColor whereEcnIn($value)
 * @method static Builder|PartColor whereEcnOut($value)
 * @method static Builder|PartColor whereId($value)
 * @method static Builder|PartColor whereInteriorCode($value)
 * @method static Builder|PartColor whereName($value)
 * @method static Builder|PartColor wherePartCode($value)
 * @method static Builder|PartColor wherePlantCode($value)
 * @method static Builder|PartColor whereUpdatedAt($value)
 * @method static Builder|PartColor whereUpdatedBy($value)
 * @method static Builder|PartColor whereVehicleColorCode($value)
 * @method static \Illuminate\Database\Query\Builder|PartColor withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PartColor withoutTrashed()
 * @mixin Eloquent
 */
class PartColor extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'part_code',
        'name',
        'interior_code',
        'vehicle_color_code',
        'ecn_in',
        'ecn_out',
        'plant_code'
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
