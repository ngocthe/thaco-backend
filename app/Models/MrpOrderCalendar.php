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
 * App\Models\MrpOrderCalendar
 *
 * @property int $id
 * @property string $contract_code
 * @property string $part_group
 * @property Carbon $etd
 * @property Carbon $eta
 * @property string $target_plan_from
 * @property string $target_plan_to
 * @property string|null $buffer_span_from
 * @property string|null $buffer_span_to
 * @property string|null $order_span_from
 * @property string|null $order_span_to
 * @property Carbon $mrp_or_run
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int $status 1: Wait, 2: Done
 * @property-read Admin $createdBy
 * @property-read Collection|Remark[] $remarkable
 * @property-read int|null $remarkable_count
 * @property-read Admin $updatedBy
 * @method static Builder|MrpOrderCalendar newModelQuery()
 * @method static Builder|MrpOrderCalendar newQuery()
 * @method static \Illuminate\Database\Query\Builder|MrpOrderCalendar onlyTrashed()
 * @method static Builder|MrpOrderCalendar query()
 * @method static Builder|MrpOrderCalendar whereBufferSpanFrom($value)
 * @method static Builder|MrpOrderCalendar whereBufferSpanTo($value)
 * @method static Builder|MrpOrderCalendar whereContractCode($value)
 * @method static Builder|MrpOrderCalendar whereCreatedAt($value)
 * @method static Builder|MrpOrderCalendar whereCreatedBy($value)
 * @method static Builder|MrpOrderCalendar whereDeletedAt($value)
 * @method static Builder|MrpOrderCalendar whereEta($value)
 * @method static Builder|MrpOrderCalendar whereEtd($value)
 * @method static Builder|MrpOrderCalendar whereId($value)
 * @method static Builder|MrpOrderCalendar whereMrpOrRun($value)
 * @method static Builder|MrpOrderCalendar whereOrderSpanFrom($value)
 * @method static Builder|MrpOrderCalendar whereOrderSpanTo($value)
 * @method static Builder|MrpOrderCalendar wherePartGroup($value)
 * @method static Builder|MrpOrderCalendar whereStatus($value)
 * @method static Builder|MrpOrderCalendar whereTargetPlanFrom($value)
 * @method static Builder|MrpOrderCalendar whereTargetPlanTo($value)
 * @method static Builder|MrpOrderCalendar whereUpdatedAt($value)
 * @method static Builder|MrpOrderCalendar whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|MrpOrderCalendar withTrashed()
 * @method static \Illuminate\Database\Query\Builder|MrpOrderCalendar withoutTrashed()
 * @mixin Eloquent
 */
class MrpOrderCalendar extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contract_code',
		'part_group',
		'etd',
		'eta',
		'target_plan_from',
		'target_plan_to',
		'buffer_span_from',
		'buffer_span_to',
		'order_span_from',
		'order_span_to',
		'mrp_or_run',
        'status'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'etd',
        'eta',
        'mrp_or_run',
    ];
}
