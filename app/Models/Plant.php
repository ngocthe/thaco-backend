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
 * App\Models\Plant
 *
 * @property int $id
 * @property string $code
 * @property string $description
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Admin $updatedBy
 * @property-read string $remark
 * @property-read Collection|Remark[] $remarkable
 * @property-read int|null $remarkable_count
 * @method static Builder|Plant newModelQuery()
 * @method static Builder|Plant newQuery()
 * @method static \Illuminate\Database\Query\Builder|Plant onlyTrashed()
 * @method static Builder|Plant query()
 * @method static Builder|Plant whereCode($value)
 * @method static Builder|Plant whereCreatedAt($value)
 * @method static Builder|Plant whereCreatedBy($value)
 * @method static Builder|Plant whereDeletedAt($value)
 * @method static Builder|Plant whereDescription($value)
 * @method static Builder|Plant whereId($value)
 * @method static Builder|Plant whereUpdatedAt($value)
 * @method static Builder|Plant whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|Plant withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Plant withoutTrashed()
 * @mixin Eloquent
 */
class Plant extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
		'description'
    ];
}
