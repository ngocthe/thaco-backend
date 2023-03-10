<?php

namespace App\Models;
use App\Traits\CreatedUpdatedByAdmin;
use App\Traits\HasRemark;
use App\Traits\WhereInMultiple;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\PlantInventoryLog
 *
 * @property int $id
 * @property string $part_code
 * @property string $part_color_code
 * @property string $box_type_code
 * @property Carbon|null $received_date
 * @property int|null $quantity
 * @property int $received_box_quantity
 * @property string|null $unit
 * @property string $warehouse_code
 * @property string|null $defect_id
 * @property string $plant_code
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read string $remark
 * @property-read Remark|null $remarkable
 * @property-read DefectInventory|null $defectable
 * @property-read Admin $updatedBy
 * @method static Builder|PlantInventoryLog newModelQuery()
 * @method static Builder|PlantInventoryLog newQuery()
 * @method static \Illuminate\Database\Query\Builder|PlantInventoryLog onlyTrashed()
 * @method static Builder|PlantInventoryLog query()
 * @method static Builder|PlantInventoryLog whereBoxTypeCode($value)
 * @method static Builder|PlantInventoryLog whereCreatedAt($value)
 * @method static Builder|PlantInventoryLog whereCreatedBy($value)
 * @method static Builder|PlantInventoryLog whereDeletedAt($value)
 * @method static Builder|PlantInventoryLog whereId($value)
 * @method static Builder|PlantInventoryLog wherePartCode($value)
 * @method static Builder|PlantInventoryLog wherePartColorCode($value)
 * @method static Builder|PlantInventoryLog wherePlantCode($value)
 * @method static Builder|PlantInventoryLog whereQuantity($value)
 * @method static Builder|PlantInventoryLog whereReceivedDate($value)
 * @method static Builder|PlantInventoryLog whereUnit($value)
 * @method static Builder|PlantInventoryLog whereUpdatedAt($value)
 * @method static Builder|PlantInventoryLog whereUpdatedBy($value)
 * @method static Builder|PlantInventoryLog whereWarehouseCode($value)
 * @method static \Illuminate\Database\Query\Builder|PlantInventoryLog withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PlantInventoryLog withoutTrashed()
 * @mixin Eloquent
 */
class PlantInventoryLog extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'part_code',
		'part_color_code',
		'box_type_code',
		'received_date',
		'quantity',
        'received_box_quantity',
		'unit',
		'warehouse_code',
        'defect_id',
		'plant_code'
    ];

    protected $dates = [
        'received_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * @return MorphMany
     */
    public function defectable(): MorphMany
    {
        return $this->morphMany(DefectInventory::class, 'modelable');
    }
}
