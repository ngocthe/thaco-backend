<?php

namespace App\Models;
use App\Traits\CreatedUpdatedByAdmin;
use App\Traits\HasRemark;
use App\Traits\WhereInMultiple;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\PartGroup
 *
 * @property int $id
 * @property string $code
 * @property string $description
 * @property int $lead_time
 * @property string $ordering_cycle
 * @property int $delivery_lead_time
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Admin $updatedBy
 * @property-read string $remark
 * @property-read Collection|Remark[] $remarkable
 * @method static Builder|PartGroup newModelQuery()
 * @method static Builder|PartGroup newQuery()
 * @method static \Illuminate\Database\Query\Builder|PartGroup onlyTrashed()
 * @method static Builder|PartGroup query()
 * @method static Builder|PartGroup whereCode($value)
 * @method static Builder|PartGroup whereCreatedAt($value)
 * @method static Builder|PartGroup whereCreatedBy($value)
 * @method static Builder|PartGroup whereDeletedAt($value)
 * @method static Builder|PartGroup whereDescription($value)
 * @method static Builder|PartGroup whereId($value)
 * @method static Builder|PartGroup whereLeadTime($value)
 * @method static Builder|PartGroup whereOrderingCycle($value)
 * @method static Builder|PartGroup whereUpdatedAt($value)
 * @method static Builder|PartGroup whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|PartGroup withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PartGroup withoutTrashed()
 * @mixin Eloquent
 */
class PartGroup extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
		'description',
		'lead_time',
		'ordering_cycle',
        'delivery_lead_time'
    ];
}
