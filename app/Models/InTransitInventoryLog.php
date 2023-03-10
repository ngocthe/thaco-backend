<?php

namespace App\Models;

use App\Traits\CreatedUpdatedByAdmin;
use App\Traits\HasRemark;
use App\Traits\WhereInMultiple;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\InTransitInventoryLog
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
 * @property int|null $part_quantity
 * @property string|null $unit
 * @property string $supplier_code
 * @property Carbon|null $etd
 * @property Carbon|null $container_shipped
 * @property Carbon|null $eta
 * @property string $plant_code
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Admin $updatedBy
 * @property-read string $remark
 * @property-read Remark|null $remarkable
 * @property-read Part $partInfo
 * @method static Builder|InTransitInventoryLog newModelQuery()
 * @method static Builder|InTransitInventoryLog newQuery()
 * @method static \Illuminate\Database\Query\Builder|InTransitInventoryLog onlyTrashed()
 * @method static Builder|InTransitInventoryLog query()
 * @method static Builder|InTransitInventoryLog whereBillOfLadingCode($value)
 * @method static Builder|InTransitInventoryLog whereBoxQuantity($value)
 * @method static Builder|InTransitInventoryLog whereBoxTypeCode($value)
 * @method static Builder|InTransitInventoryLog whereCaseCode($value)
 * @method static Builder|InTransitInventoryLog whereContainerCode($value)
 * @method static Builder|InTransitInventoryLog whereContainerReceived($value)
 * @method static Builder|InTransitInventoryLog whereContainerShipped($value)
 * @method static Builder|InTransitInventoryLog whereContractCode($value)
 * @method static Builder|InTransitInventoryLog whereCreatedAt($value)
 * @method static Builder|InTransitInventoryLog whereCreatedBy($value)
 * @method static Builder|InTransitInventoryLog whereDeletedAt($value)
 * @method static Builder|InTransitInventoryLog whereEta($value)
 * @method static Builder|InTransitInventoryLog whereEtd($value)
 * @method static Builder|InTransitInventoryLog whereId($value)
 * @method static Builder|InTransitInventoryLog whereInvoiceCode($value)
 * @method static Builder|InTransitInventoryLog wherePartCode($value)
 * @method static Builder|InTransitInventoryLog wherePartColorCode($value)
 * @method static Builder|InTransitInventoryLog wherePartQuantity($value)
 * @method static Builder|InTransitInventoryLog wherePlantCode($value)
 * @method static Builder|InTransitInventoryLog whereSupplierCode($value)
 * @method static Builder|InTransitInventoryLog whereUnit($value)
 * @method static Builder|InTransitInventoryLog whereUpdatedAt($value)
 * @method static Builder|InTransitInventoryLog whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|InTransitInventoryLog withTrashed()
 * @method static \Illuminate\Database\Query\Builder|InTransitInventoryLog withoutTrashed()
 * @mixin Eloquent
 */
class InTransitInventoryLog extends Model
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
        'etd',
        'container_shipped',
        'eta',
        'plant_code'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'etd',
        'container_shipped',
        'eta'
    ];

    /**
     * @return hasOne
     */
    public function partInfo(): hasOne
    {
        return $this->hasOne(Part::class, 'code', 'part_code');
    }

}
