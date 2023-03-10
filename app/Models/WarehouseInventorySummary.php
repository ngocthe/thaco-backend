<?php

namespace App\Models;
use App\Traits\CreatedUpdatedByAdmin;
use App\Traits\WhereInMultiple;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\WarehouseInventorySummary
 *
 * @property int $id
 * @property string $part_code
 * @property string $part_color_code
 * @property int $quantity
 * @property string $unit
 * @property int $warehouse_type
 * @property string $warehouse_code
 * @property string $plant_code
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Admin $updatedBy
 * @method static Builder|WarehouseInventorySummary newModelQuery()
 * @method static Builder|WarehouseInventorySummary newQuery()
 * @method static \Illuminate\Database\Query\Builder|WarehouseInventorySummary onlyTrashed()
 * @method static Builder|WarehouseInventorySummary query()
 * @method static Builder|WarehouseInventorySummary whereCreatedAt($value)
 * @method static Builder|WarehouseInventorySummary whereCreatedBy($value)
 * @method static Builder|WarehouseInventorySummary whereDeletedAt($value)
 * @method static Builder|WarehouseInventorySummary whereId($value)
 * @method static Builder|WarehouseInventorySummary wherePartCode($value)
 * @method static Builder|WarehouseInventorySummary wherePartColorCode($value)
 * @method static Builder|WarehouseInventorySummary wherePlantCode($value)
 * @method static Builder|WarehouseInventorySummary whereQuantity($value)
 * @method static Builder|WarehouseInventorySummary whereUnit($value)
 * @method static Builder|WarehouseInventorySummary whereUpdatedAt($value)
 * @method static Builder|WarehouseInventorySummary whereUpdatedBy($value)
 * @method static Builder|WarehouseInventorySummary whereWarehouseCode($value)
 * @method static Builder|WarehouseInventorySummary whereWarehouseType($value)
 * @method static \Illuminate\Database\Query\Builder|WarehouseInventorySummary withTrashed()
 * @method static \Illuminate\Database\Query\Builder|WarehouseInventorySummary withoutTrashed()
 * @mixin Eloquent
 */
class WarehouseInventorySummary extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasFactory;

    const TYPE_BWH = 1;
    const TYPE_UPKWH = 2;
    const TYPE_PLANT_WH = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'part_code',
		'part_color_code',
		'quantity',
		'unit',
		'warehouse_type',
		'warehouse_code',
		'plant_code'
    ];
}
