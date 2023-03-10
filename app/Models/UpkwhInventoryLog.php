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
 * App\Models\UpkwhInventoryLog
 *
 * @property int $id
 * @property string $contract_code
 * @property string $invoice_code
 * @property string $bill_of_lading_code
 * @property string $container_code
 * @property string $case_code
 * @property string $part_code
 * @property string $part_color_code
 * @property string $box_type_code
 * @property int|null $box_quantity
 * @property int $part_quantity
 * @property string $unit
 * @property string $supplier_code
 * @property Carbon|null $received_date
 * @property string|null $shelf_location_code
 * @property string|null $warehouse_code
 * @property int|null $shipped_box_quantity
 * @property Carbon|null $shipped_date
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
 * @property-read Admin $updatedBy
 * @method static Builder|UpkwhInventoryLog newModelQuery()
 * @method static Builder|UpkwhInventoryLog newQuery()
 * @method static \Illuminate\Database\Query\Builder|UpkwhInventoryLog onlyTrashed()
 * @method static Builder|UpkwhInventoryLog query()
 * @method static Builder|UpkwhInventoryLog whereBillOfLadingCode($value)
 * @method static Builder|UpkwhInventoryLog whereBoxTypeCode($value)
 * @method static Builder|UpkwhInventoryLog whereCaseCode($value)
 * @method static Builder|UpkwhInventoryLog whereContainerCode($value)
 * @method static Builder|UpkwhInventoryLog whereContractCode($value)
 * @method static Builder|UpkwhInventoryLog whereCreatedAt($value)
 * @method static Builder|UpkwhInventoryLog whereCreatedBy($value)
 * @method static Builder|UpkwhInventoryLog whereDeletedAt($value)
 * @method static Builder|UpkwhInventoryLog whereId($value)
 * @method static Builder|UpkwhInventoryLog whereInvoiceCode($value)
 * @method static Builder|UpkwhInventoryLog wherePartCode($value)
 * @method static Builder|UpkwhInventoryLog wherePartColorCode($value)
 * @method static Builder|UpkwhInventoryLog wherePartsQuantity($value)
 * @method static Builder|UpkwhInventoryLog wherePlantCode($value)
 * @method static Builder|UpkwhInventoryLog whereReceivedBoxQuantity($value)
 * @method static Builder|UpkwhInventoryLog whereReceivedDate($value)
 * @method static Builder|UpkwhInventoryLog whereShippedBoxQuantity($value)
 * @method static Builder|UpkwhInventoryLog whereShippedDate($value)
 * @method static Builder|UpkwhInventoryLog whereSupplierCode($value)
 * @method static Builder|UpkwhInventoryLog whereUnit($value)
 * @method static Builder|UpkwhInventoryLog whereUpdatedAt($value)
 * @method static Builder|UpkwhInventoryLog whereUpdatedBy($value)
 * @method static Builder|UpkwhInventoryLog whereWarehouseLocationCode($value)
 * @method static \Illuminate\Database\Query\Builder|UpkwhInventoryLog withTrashed()
 * @method static \Illuminate\Database\Query\Builder|UpkwhInventoryLog withoutTrashed()
 * @mixin Eloquent
 */
class UpkwhInventoryLog extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contract_code',
		'invoice_code',
		'bill_of_lading_code',
		'container_code',
		'case_code',
		'part_code',
		'part_color_code',
		'box_type_code',
		'box_quantity',
		'part_quantity',
		'unit',
		'supplier_code',
		'received_date',
		'shelf_location_code',
        'warehouse_code',
		'shipped_box_quantity',
		'shipped_date',
        'defect_id',
		'plant_code'
    ];

    /**
     * @var string[]
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'received_date',
        'shipped_date'
    ];

    /**
     * @return MorphMany
     */
    public function defectable(): MorphMany
    {
        return $this->morphMany(DefectInventory::class, 'modelable');
    }
}
