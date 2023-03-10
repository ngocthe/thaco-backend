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
 * App\Models\ShortagePart
 *
 * @property int $id
 * @property string $plan_date
 * @property string $part_code
 * @property string $part_color_code
 * @property int $quantity
 * @property int $import_id
 * @property string $plant_code
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Admin $updatedBy
 * @property mixed $days
 * @property mixed $quantities
 * @method static Builder|ShortagePart newModelQuery()
 * @method static Builder|ShortagePart newQuery()
 * @method static \Illuminate\Database\Query\Builder|ShortagePart onlyTrashed()
 * @method static Builder|ShortagePart query()
 * @method static Builder|ShortagePart whereCreatedAt($value)
 * @method static Builder|ShortagePart whereCreatedBy($value)
 * @method static Builder|ShortagePart whereDeletedAt($value)
 * @method static Builder|ShortagePart whereId($value)
 * @method static Builder|ShortagePart whereImportId($value)
 * @method static Builder|ShortagePart wherePartCode($value)
 * @method static Builder|ShortagePart wherePartColorCode($value)
 * @method static Builder|ShortagePart wherePlanDate($value)
 * @method static Builder|ShortagePart wherePlantCode($value)
 * @method static Builder|ShortagePart whereQuantity($value)
 * @method static Builder|ShortagePart whereUpdatedAt($value)
 * @method static Builder|ShortagePart whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|ShortagePart withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ShortagePart withoutTrashed()
 * @mixin Eloquent
 */
class ShortagePart extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'plan_date',
		'part_code',
		'part_color_code',
		'quantity',
		'import_id',
		'plant_code'
    ];
}
