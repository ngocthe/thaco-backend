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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\OrderList
 *
 * @property int $id
 * @property string $status
 * @property string $contract_code
 * @property string|null $eta
 * @property string $part_code
 * @property string $part_color_code
 * @property string $part_group
 * @property int $actual_quantity
 * @property string $supplier_code
 * @property int|null $import_id
 * @property int|null $moq
 * @property int|null $mrp_quantity
 * @property string $plant_code
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read MrpProductionPlanImport|null $fileImport
 * @property-read Part|null $part
 * @property-read Collection|Remark[] $remarkable
 * @property-read int|null $remarkable_count
 * @property-read Admin $updatedBy
 * @method static Builder|OrderList newModelQuery()
 * @method static Builder|OrderList newQuery()
 * @method static \Illuminate\Database\Query\Builder|OrderList onlyTrashed()
 * @method static Builder|OrderList query()
 * @method static Builder|OrderList whereActualQuantity($value)
 * @method static Builder|OrderList whereContractCode($value)
 * @method static Builder|OrderList whereCreatedAt($value)
 * @method static Builder|OrderList whereCreatedBy($value)
 * @method static Builder|OrderList whereDeletedAt($value)
 * @method static Builder|OrderList whereEta($value)
 * @method static Builder|OrderList whereId($value)
 * @method static Builder|OrderList whereImportId($value)
 * @method static Builder|OrderList whereMoq($value)
 * @method static Builder|OrderList whereMrpQuantity($value)
 * @method static Builder|OrderList wherePartCode($value)
 * @method static Builder|OrderList wherePartColorCode($value)
 * @method static Builder|OrderList wherePartGroup($value)
 * @method static Builder|OrderList wherePlantCode($value)
 * @method static Builder|OrderList whereStatus($value)
 * @method static Builder|OrderList whereSupplierCode($value)
 * @method static Builder|OrderList whereUpdatedAt($value)
 * @method static Builder|OrderList whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|OrderList withTrashed()
 * @method static \Illuminate\Database\Query\Builder|OrderList withoutTrashed()
 * @mixin Eloquent
 */
class OrderList extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status',
		'contract_code',
		'eta',
		'part_code',
		'part_color_code',
		'part_group',
		'actual_quantity',
		'supplier_code',
		'import_id',
		'moq',
		'mrp_quantity',
		'plant_code'
    ];

    /**
     * @return HasOne
     */
    public function fileImport(): HasOne
    {
        return $this->hasOne(MrpProductionPlanImport::class, 'id', 'import_id');
    }

    /**
     * @return BelongsTo
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'part_code', 'code');
    }
}
