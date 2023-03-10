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
 * App\Models\LogicalInventoryMscAdjustment
 *
 * @property int $id
 * @property string $msc_code
 * @property int $adjustment_quantity
 * @property string $vehicle_color_code
 * @property string $production_date
 * @property string $plant_code
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Collection|Remark[] $remarkable
 * @property-read int|null $remarkable_count
 * @property-read Admin $updatedBy
 * @method static Builder|LogicalInventoryMscAdjustment newModelQuery()
 * @method static Builder|LogicalInventoryMscAdjustment newQuery()
 * @method static \Illuminate\Database\Query\Builder|LogicalInventoryMscAdjustment onlyTrashed()
 * @method static Builder|LogicalInventoryMscAdjustment query()
 * @method static Builder|LogicalInventoryMscAdjustment whereAdjustmentQuantity($value)
 * @method static Builder|LogicalInventoryMscAdjustment whereCreatedAt($value)
 * @method static Builder|LogicalInventoryMscAdjustment whereCreatedBy($value)
 * @method static Builder|LogicalInventoryMscAdjustment whereDeletedAt($value)
 * @method static Builder|LogicalInventoryMscAdjustment whereId($value)
 * @method static Builder|LogicalInventoryMscAdjustment whereMscCode($value)
 * @method static Builder|LogicalInventoryMscAdjustment wherePlantCode($value)
 * @method static Builder|LogicalInventoryMscAdjustment whereProductionDate($value)
 * @method static Builder|LogicalInventoryMscAdjustment whereUpdatedAt($value)
 * @method static Builder|LogicalInventoryMscAdjustment whereUpdatedBy($value)
 * @method static Builder|LogicalInventoryMscAdjustment whereVehicleColorCode($value)
 * @method static \Illuminate\Database\Query\Builder|LogicalInventoryMscAdjustment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|LogicalInventoryMscAdjustment withoutTrashed()
 * @mixin Eloquent
 */
class LogicalInventoryMscAdjustment extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, HasRemark, WhereInMultiple, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'msc_code',
		'adjustment_quantity',
		'vehicle_color_code',
		'production_date',
		'plant_code'
    ];
}
