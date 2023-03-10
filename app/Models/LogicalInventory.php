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
 * App\Models\LogicalInventory
 *
 * @property int $id
 * @property string $production_date
 * @property string $part_code
 * @property string $part_color_code
 * @property int $quantity
 * @property string $plant_code
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Admin $updatedBy
 * @property mixed $quantities
 * @property mixed $days
 * @property mixed $warehouse_types
 * @property mixed $logical_quantities
 * @method static Builder|LogicalInventory newModelQuery()
 * @method static Builder|LogicalInventory newQuery()
 * @method static \Illuminate\Database\Query\Builder|LogicalInventory onlyTrashed()
 * @method static Builder|LogicalInventory query()
 * @method static Builder|LogicalInventory whereCreatedAt($value)
 * @method static Builder|LogicalInventory whereCreatedBy($value)
 * @method static Builder|LogicalInventory whereDeletedAt($value)
 * @method static Builder|LogicalInventory whereId($value)
 * @method static Builder|LogicalInventory wherePartCode($value)
 * @method static Builder|LogicalInventory wherePartColorCode($value)
 * @method static Builder|LogicalInventory wherePlantCode($value)
 * @method static Builder|LogicalInventory whereProductionDate($value)
 * @method static Builder|LogicalInventory whereQuantity($value)
 * @method static Builder|LogicalInventory whereUpdatedAt($value)
 * @method static Builder|LogicalInventory whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|LogicalInventory withTrashed()
 * @method static \Illuminate\Database\Query\Builder|LogicalInventory withoutTrashed()
 * @mixin Eloquent
 */
class LogicalInventory extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'production_date',
		'part_code',
		'part_color_code',
		'quantity',
		'plant_code'
    ];

}
