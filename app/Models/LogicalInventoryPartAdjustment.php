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
 * App\Models\LogicalInventoryPartAdjustment
 *
 * @property int $id
 * @property string $adjustment_date
 * @property string $part_code
 * @property string $part_color_code
 * @property int $old_quantity
 * @property int $new_quantity
 * @property int $adjustment_quantity
 * @property string $plant_code
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Admin $updatedBy
 * @property-read Collection|Remark[] $remarkable
 * @property-read int|null $remarkable_count
 * @method static Builder|LogicalInventoryPartAdjustment newModelQuery()
 * @method static Builder|LogicalInventoryPartAdjustment newQuery()
 * @method static \Illuminate\Database\Query\Builder|LogicalInventoryPartAdjustment onlyTrashed()
 * @method static Builder|LogicalInventoryPartAdjustment query()
 * @method static Builder|LogicalInventoryPartAdjustment whereAdjustmentDate($value)
 * @method static Builder|LogicalInventoryPartAdjustment whereAdjustmentQuantity($value)
 * @method static Builder|LogicalInventoryPartAdjustment whereCreatedAt($value)
 * @method static Builder|LogicalInventoryPartAdjustment whereCreatedBy($value)
 * @method static Builder|LogicalInventoryPartAdjustment whereDeletedAt($value)
 * @method static Builder|LogicalInventoryPartAdjustment whereId($value)
 * @method static Builder|LogicalInventoryPartAdjustment whereNewQuantity($value)
 * @method static Builder|LogicalInventoryPartAdjustment whereOldQuantity($value)
 * @method static Builder|LogicalInventoryPartAdjustment wherePartCode($value)
 * @method static Builder|LogicalInventoryPartAdjustment wherePartColorCode($value)
 * @method static Builder|LogicalInventoryPartAdjustment wherePlantCode($value)
 * @method static Builder|LogicalInventoryPartAdjustment whereUpdatedAt($value)
 * @method static Builder|LogicalInventoryPartAdjustment whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|LogicalInventoryPartAdjustment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|LogicalInventoryPartAdjustment withoutTrashed()
 * @mixin Eloquent
 */
class LogicalInventoryPartAdjustment extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, HasRemark, WhereInMultiple, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'adjustment_date',
		'part_code',
		'part_color_code',
		'old_quantity',
		'new_quantity',
		'adjustment_quantity',
		'plant_code'
    ];
}
