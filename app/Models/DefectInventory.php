<?php

namespace App\Models;

use App\Traits\CreatedUpdatedByAdmin;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\DefectInventory
 *
 * @property int $id
 * @property string $modelable_type
 * @property int $modelable_id
 * @property int|null $box_id
 * @property string|null $defect_id
 * @property int|null $part_defect_quantity
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read string $remark
 * @property-read Remark|null $remarkable
 * @property-read Admin $updatedBy
 * @method static Builder|DefectInventory newModelQuery()
 * @method static Builder|DefectInventory newQuery()
 * @method static \Illuminate\Database\Query\Builder|DefectInventory onlyTrashed()
 * @method static Builder|DefectInventory query()
 * @method static Builder|DefectInventory whereBoxId($value)
 * @method static Builder|DefectInventory whereCreatedAt($value)
 * @method static Builder|DefectInventory whereCreatedBy($value)
 * @method static Builder|DefectInventory whereDefectId($value)
 * @method static Builder|DefectInventory whereDeletedAt($value)
 * @method static Builder|DefectInventory whereId($value)
 * @method static Builder|DefectInventory whereModelableId($value)
 * @method static Builder|DefectInventory whereModelableType($value)
 * @method static Builder|DefectInventory wherePartDefectQuantity($value)
 * @method static Builder|DefectInventory whereUpdatedAt($value)
 * @method static Builder|DefectInventory whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|DefectInventory withTrashed()
 * @method static \Illuminate\Database\Query\Builder|DefectInventory withoutTrashed()
 * @mixin Eloquent
 */
class DefectInventory extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'modelable_type',
        'modelable_id',
        'box_id',
        'defect_id',
        'part_defect_quantity'
    ];

    /**
     * @return MorphOne
     */
    public function remarkable(): MorphOne
    {
        return $this->morphOne(Remark::class, 'modelable');
    }

    /**
     * @return string
     */
    public function getRemarkAttribute(): string
    {
        return $this->remarkable ? $this->remarkable->content : '';
    }
}
