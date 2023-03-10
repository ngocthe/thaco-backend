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
 * App\Models\WarehouseLocation
 *
 * @property int $id
 * @property string $code
 * @property string $warehouse_code
 * @property string $description
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
 * @method static Builder|WarehouseLocation newModelQuery()
 * @method static Builder|WarehouseLocation newQuery()
 * @method static \Illuminate\Database\Query\Builder|WarehouseLocation onlyTrashed()
 * @method static Builder|WarehouseLocation query()
 * @method static Builder|WarehouseLocation whereCode($value)
 * @method static Builder|WarehouseLocation whereCreatedAt($value)
 * @method static Builder|WarehouseLocation whereCreatedBy($value)
 * @method static Builder|WarehouseLocation whereDeletedAt($value)
 * @method static Builder|WarehouseLocation whereDescription($value)
 * @method static Builder|WarehouseLocation whereId($value)
 * @method static Builder|WarehouseLocation wherePlantCode($value)
 * @method static Builder|WarehouseLocation whereUpdatedAt($value)
 * @method static Builder|WarehouseLocation whereUpdatedBy($value)
 * @method static Builder|WarehouseLocation whereWarehouseCode($value)
 * @method static \Illuminate\Database\Query\Builder|WarehouseLocation withTrashed()
 * @method static \Illuminate\Database\Query\Builder|WarehouseLocation withoutTrashed()
 * @mixin Eloquent
 */
class WarehouseLocation extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
		'warehouse_code',
		'description',
		'plant_code'
    ];
}
