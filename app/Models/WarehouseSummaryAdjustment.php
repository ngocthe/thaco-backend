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
 * App\Models\WarehouseSummaryAdjustment
 *
 * @property int $id
 * @property string $warehouse_code
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
 * @method static Builder|WarehouseSummaryAdjustment newModelQuery()
 * @method static Builder|WarehouseSummaryAdjustment newQuery()
 * @method static \Illuminate\Database\Query\Builder|WarehouseSummaryAdjustment onlyTrashed()
 * @method static Builder|WarehouseSummaryAdjustment query()
 * @method static Builder|WarehouseSummaryAdjustment whereAdjustmentQuantity($value)
 * @method static Builder|WarehouseSummaryAdjustment whereCreatedAt($value)
 * @method static Builder|WarehouseSummaryAdjustment whereCreatedBy($value)
 * @method static Builder|WarehouseSummaryAdjustment whereDeletedAt($value)
 * @method static Builder|WarehouseSummaryAdjustment whereId($value)
 * @method static Builder|WarehouseSummaryAdjustment whereNewQuantity($value)
 * @method static Builder|WarehouseSummaryAdjustment whereOldQuantity($value)
 * @method static Builder|WarehouseSummaryAdjustment wherePartCode($value)
 * @method static Builder|WarehouseSummaryAdjustment wherePartColorCode($value)
 * @method static Builder|WarehouseSummaryAdjustment wherePlantCode($value)
 * @method static Builder|WarehouseSummaryAdjustment whereUpdatedAt($value)
 * @method static Builder|WarehouseSummaryAdjustment whereUpdatedBy($value)
 * @method static Builder|WarehouseSummaryAdjustment whereWarehouseCode($value)
 * @method static \Illuminate\Database\Query\Builder|WarehouseSummaryAdjustment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|WarehouseSummaryAdjustment withoutTrashed()
 * @mixin Eloquent
 */
class WarehouseSummaryAdjustment extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, HasRemark, WhereInMultiple, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'warehouse_code',
		'part_code',
		'part_color_code',
		'old_quantity',
		'new_quantity',
		'adjustment_quantity',
		'plant_code'
    ];
}
