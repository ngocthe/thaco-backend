<?php

namespace App\Models;
use App\Traits\CreatedUpdatedByAdmin;
use App\Traits\HasRemark;
use App\Traits\WhereInMultiple;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\PartUsageResult
 *
 * @property int $id
 * @property string $used_date
 * @property string $part_code
 * @property string $part_color_code
 * @property string $plant_code
 * @property int $quantity
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Collection|Remark[] $remarkable
 * @property-read int|null $remarkable_count
 * @property-read Admin $updatedBy
 * @method static Builder|PartUsageResult newModelQuery()
 * @method static Builder|PartUsageResult newQuery()
 * @method static \Illuminate\Database\Query\Builder|PartUsageResult onlyTrashed()
 * @method static Builder|PartUsageResult query()
 * @method static Builder|PartUsageResult whereCreatedAt($value)
 * @method static Builder|PartUsageResult whereCreatedBy($value)
 * @method static Builder|PartUsageResult whereDeletedAt($value)
 * @method static Builder|PartUsageResult whereId($value)
 * @method static Builder|PartUsageResult wherePartCode($value)
 * @method static Builder|PartUsageResult wherePartColorCode($value)
 * @method static Builder|PartUsageResult wherePlantCode($value)
 * @method static Builder|PartUsageResult whereQuantity($value)
 * @method static Builder|PartUsageResult whereUpdatedAt($value)
 * @method static Builder|PartUsageResult whereUpdatedBy($value)
 * @method static Builder|PartUsageResult whereUsedDate($value)
 * @method static \Illuminate\Database\Query\Builder|PartUsageResult withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PartUsageResult withoutTrashed()
 * @mixin Eloquent
 */
class PartUsageResult extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, HasRemark, WhereInMultiple, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'used_date',
		'part_code',
		'part_color_code',
		'plant_code',
		'quantity'
    ];
}
