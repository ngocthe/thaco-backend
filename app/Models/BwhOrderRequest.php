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
 * App\Models\BwhOrderRequest
 *
 * @property int $id
 * @property string|null $order_number
 * @property string $contract_code
 * @property string $invoice_code
 * @property string $bill_of_lading_code
 * @property string $container_code
 * @property string $case_code
 * @property string|null $supplier_code
 * @property string $part_code
 * @property string $part_color_code
 * @property string $box_type_code
 * @property int|null $box_quantity
 * @property int|null $part_quantity
 * @property string|null $warehouse_code
 * @property string|null $warehouse_location_code
 * @property string $plant_code
 * @property int $status
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Collection|Remark[] $remarkable
 * @property-read int|null $remarkable_count
 * @property-read Admin $updatedBy
 * @method static Builder|BwhOrderRequest newModelQuery()
 * @method static Builder|BwhOrderRequest newQuery()
 * @method static \Illuminate\Database\Query\Builder|BwhOrderRequest onlyTrashed()
 * @method static Builder|BwhOrderRequest query()
 * @method static Builder|BwhOrderRequest whereBillOfLadingCode($value)
 * @method static Builder|BwhOrderRequest whereBoxQuantity($value)
 * @method static Builder|BwhOrderRequest whereBoxTypeCode($value)
 * @method static Builder|BwhOrderRequest whereCaseCode($value)
 * @method static Builder|BwhOrderRequest whereContainerCode($value)
 * @method static Builder|BwhOrderRequest whereContractCode($value)
 * @method static Builder|BwhOrderRequest whereCreatedAt($value)
 * @method static Builder|BwhOrderRequest whereCreatedBy($value)
 * @method static Builder|BwhOrderRequest whereDeletedAt($value)
 * @method static Builder|BwhOrderRequest whereId($value)
 * @method static Builder|BwhOrderRequest whereInvoiceCode($value)
 * @method static Builder|BwhOrderRequest whereOrderNumber($value)
 * @method static Builder|BwhOrderRequest wherePartCode($value)
 * @method static Builder|BwhOrderRequest wherePartColorCode($value)
 * @method static Builder|BwhOrderRequest wherePartQuantity($value)
 * @method static Builder|BwhOrderRequest wherePlantCode($value)
 * @method static Builder|BwhOrderRequest whereStatus($value)
 * @method static Builder|BwhOrderRequest whereUpdatedAt($value)
 * @method static Builder|BwhOrderRequest whereUpdatedBy($value)
 * @method static Builder|BwhOrderRequest whereWarehouseCode($value)
 * @method static Builder|BwhOrderRequest whereShelfLocationCode($value)
 * @method static \Illuminate\Database\Query\Builder|BwhOrderRequest withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BwhOrderRequest withoutTrashed()
 * @mixin Eloquent
 */
class BwhOrderRequest extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, HasRemark, WhereInMultiple, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_number',
        'contract_code',
        'invoice_code',
        'bill_of_lading_code',
        'container_code',
        'case_code',
        'supplier_code',
        'part_code',
        'part_color_code',
        'box_type_code',
        'box_quantity',
        'part_quantity',
        'warehouse_code',
        'warehouse_location_code',
        'plant_code'
    ];
}
