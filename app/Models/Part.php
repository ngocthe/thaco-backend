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
 * App\Models\Part
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $group
 * @property string $ecn_in
 * @property string|null $ecn_out
 * @property string $plant_code
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Ecn|null $ecnInInfo
 * @property-read Ecn|null $ecnOutInfo
 * @property-read Admin $updatedBy
 * @property-read string $remark
 * @property-read Remark|null $remarkable
 * @method static Builder|Part newModelQuery()
 * @method static Builder|Part newQuery()
 * @method static \Illuminate\Database\Query\Builder|Part onlyTrashed()
 * @method static Builder|Part query()
 * @method static Builder|Part whereCode($value)
 * @method static Builder|Part whereCreatedAt($value)
 * @method static Builder|Part whereCreatedBy($value)
 * @method static Builder|Part whereDeletedAt($value)
 * @method static Builder|Part whereEcnIn($value)
 * @method static Builder|Part whereEcnOut($value)
 * @method static Builder|Part whereGroup($value)
 * @method static Builder|Part whereId($value)
 * @method static Builder|Part whereName($value)
 * @method static Builder|Part wherePlantCode($value)
 * @method static Builder|Part whereUpdatedAt($value)
 * @method static Builder|Part whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|Part withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Part withoutTrashed()
 * @mixin Eloquent
 */
class Part extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
		'name',
		'group',
		'ecn_in',
		'ecn_out',
		'plant_code'
    ];

    /**
     * @return hasOne
     */
    public function ecnInInfo(): hasOne
    {
        return $this->hasOne(Ecn::class, 'code', 'ecn_in');
    }

    /**
     * @return hasOne
     */
    public function ecnOutInfo(): hasOne
    {
        return $this->hasOne(Ecn::class, 'code', 'ecn_out');
    }
}
