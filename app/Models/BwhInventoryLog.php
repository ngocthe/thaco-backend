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
use Illuminate\Support\Str;

/**
 * App\Models\BwhInventoryLog
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
 * @property int $box_quantity
 * @property int $part_quantity
 * @property string $unit
 * @property string $supplier_code
 * @property Carbon|null $container_received
 * @property Carbon|null $devanned_date
 * @property Carbon|null $stored_date
 * @property string|null $warehouse_location_code
 * @property string|null $warehouse_code
 * @property Carbon|null $shipped_date
 * @property string $plant_code
 * @property boolean $requested
 * @property string|null $defect_id
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read string $remark
 * @property-read Remark|null $remarkable
 * @property-read Admin $updatedBy
 * @method static Builder|BwhInventoryLog newModelQuery()
 * @method static Builder|BwhInventoryLog newQuery()
 * @method static \Illuminate\Database\Query\Builder|BwhInventoryLog onlyTrashed()
 * @method static Builder|BwhInventoryLog query()
 * @method static Builder|BwhInventoryLog whereBillOfLadingCode($value)
 * @method static Builder|BwhInventoryLog whereBoxQuantity($value)
 * @method static Builder|BwhInventoryLog whereBoxTypeCode($value)
 * @method static Builder|BwhInventoryLog whereCaseCode($value)
 * @method static Builder|BwhInventoryLog whereContainerCode($value)
 * @method static Builder|BwhInventoryLog whereContractCode($value)
 * @method static Builder|BwhInventoryLog whereCreatedAt($value)
 * @method static Builder|BwhInventoryLog whereCreatedBy($value)
 * @method static Builder|BwhInventoryLog whereDeletedAt($value)
 * @method static Builder|BwhInventoryLog whereDevannedDate($value)
 * @method static Builder|BwhInventoryLog whereId($value)
 * @method static Builder|BwhInventoryLog whereInvoiceCode($value)
 * @method static Builder|BwhInventoryLog wherePartCode($value)
 * @method static Builder|BwhInventoryLog wherePartColorCode($value)
 * @method static Builder|BwhInventoryLog wherePartQuantity($value)
 * @method static Builder|BwhInventoryLog wherePlantCode($value)
 * @method static Builder|BwhInventoryLog whereShippedDate($value)
 * @method static Builder|BwhInventoryLog whereStoredDate($value)
 * @method static Builder|BwhInventoryLog whereSupplierCode($value)
 * @method static Builder|BwhInventoryLog whereUnit($value)
 * @method static Builder|BwhInventoryLog whereUpdatedAt($value)
 * @method static Builder|BwhInventoryLog whereUpdatedBy($value)
 * @method static Builder|BwhInventoryLog whereWarehouseLocationCode($value)
 * @method static \Illuminate\Database\Query\Builder|BwhInventoryLog withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BwhInventoryLog withoutTrashed()
 * @mixin Eloquent
 */
class BwhInventoryLog extends Model
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
        'container_received',
		'devanned_date',
		'stored_date',
		'warehouse_location_code',
        'warehouse_code',
		'shipped_date',
		'plant_code',
        'defect_id'
    ];

    /**
     * @var string[]
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'container_received',
        'devanned_date',
        'stored_date',
        'shipped_date'
    ];
}
