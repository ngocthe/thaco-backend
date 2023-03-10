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
 * App\Models\Msc
 *
 * @property int $id
 * @property string $code
 * @property string $description
 * @property string $interior_color
 * @property string $car_line
 * @property string $model_grade
 * @property string $body
 * @property string $engine
 * @property string $transmission
 * @property string $plant_code
 * @property Carbon|null $effective_date_in
 * @property Carbon|null $effective_date_out
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Admin $updatedBy
 * @property-read string $remark
 * @property-read Remark|null $remarkable
 * @method static Builder|Msc newModelQuery()
 * @method static Builder|Msc newQuery()
 * @method static \Illuminate\Database\Query\Builder|Msc onlyTrashed()
 * @method static Builder|Msc query()
 * @method static Builder|Msc whereBody($value)
 * @method static Builder|Msc whereCarLine($value)
 * @method static Builder|Msc whereCode($value)
 * @method static Builder|Msc whereCreatedAt($value)
 * @method static Builder|Msc whereCreatedBy($value)
 * @method static Builder|Msc whereDeletedAt($value)
 * @method static Builder|Msc whereDescription($value)
 * @method static Builder|Msc whereEffectiveDateIn($value)
 * @method static Builder|Msc whereEffectiveDateOut($value)
 * @method static Builder|Msc whereEngine($value)
 * @method static Builder|Msc whereId($value)
 * @method static Builder|Msc whereInteriorColor($value)
 * @method static Builder|Msc whereModelGrade($value)
 * @method static Builder|Msc wherePlantCode($value)
 * @method static Builder|Msc whereTransmission($value)
 * @method static Builder|Msc whereUpdatedAt($value)
 * @method static Builder|Msc whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|Msc withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Msc withoutTrashed()
 * @mixin Eloquent
 */
class Msc extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'description',
        'interior_color',
        'car_line',
        'model_grade',
        'body',
        'engine',
        'transmission',
        'plant_code',
        'effective_date_in',
        'effective_date_out'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'effective_date_in',
        'effective_date_out'
    ];
}
