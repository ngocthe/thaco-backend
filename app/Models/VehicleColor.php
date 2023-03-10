<?php

namespace App\Models;

use App\Traits\CreatedUpdatedByAdmin;
use App\Traits\HasRemark;
use App\Traits\WhereInMultiple;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\VehicleColor
 *
 * @property int $id
 * @property string $code
 * @property string $type
 * @property string $name
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
 * @method static Builder|VehicleColor newModelQuery()
 * @method static Builder|VehicleColor newQuery()
 * @method static \Illuminate\Database\Query\Builder|VehicleColor onlyTrashed()
 * @method static Builder|VehicleColor query()
 * @method static Builder|VehicleColor whereCode($value)
 * @method static Builder|VehicleColor whereCreatedAt($value)
 * @method static Builder|VehicleColor whereCreatedBy($value)
 * @method static Builder|VehicleColor whereDeletedAt($value)
 * @method static Builder|VehicleColor whereEcnIn($value)
 * @method static Builder|VehicleColor whereEcnOut($value)
 * @method static Builder|VehicleColor whereId($value)
 * @method static Builder|VehicleColor whereName($value)
 * @method static Builder|VehicleColor wherePlantCode($value)
 * @method static Builder|VehicleColor whereType($value)
 * @method static Builder|VehicleColor whereUpdatedAt($value)
 * @method static Builder|VehicleColor whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|VehicleColor withTrashed()
 * @method static \Illuminate\Database\Query\Builder|VehicleColor withoutTrashed()
 * @mixin Eloquent
 */
class VehicleColor extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'type',
        'name',
        'ecn_in',
        'ecn_out',
        'plant_code'
    ];
}
