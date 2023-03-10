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
 * App\Models\MrpWeekDefinition
 *
 * @property int $id
 * @property string $date
 * @property int $day_off
 * @property int $year
 * @property int $month_no
 * @property int $week_no
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Admin $updatedBy
 * @method static Builder|MrpWeekDefinition newModelQuery()
 * @method static Builder|MrpWeekDefinition newQuery()
 * @method static \Illuminate\Database\Query\Builder|MrpWeekDefinition onlyTrashed()
 * @method static Builder|MrpWeekDefinition query()
 * @method static Builder|MrpWeekDefinition whereCreatedAt($value)
 * @method static Builder|MrpWeekDefinition whereCreatedBy($value)
 * @method static Builder|MrpWeekDefinition whereDeletedAt($value)
 * @method static Builder|MrpWeekDefinition whereId($value)
 * @method static Builder|MrpWeekDefinition whereIsHoliday($value)
 * @method static Builder|MrpWeekDefinition whereMonthNo($value)
 * @method static Builder|MrpWeekDefinition whereUpdatedAt($value)
 * @method static Builder|MrpWeekDefinition whereUpdatedBy($value)
 * @method static Builder|MrpWeekDefinition whereWeekNo($value)
 * @method static \Illuminate\Database\Query\Builder|MrpWeekDefinition withTrashed()
 * @method static \Illuminate\Database\Query\Builder|MrpWeekDefinition withoutTrashed()
 * @mixin Eloquent
 */
class MrpWeekDefinition extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, HasFactory, WhereInMultiple;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date',
		'day_off',
        'year',
		'month_no',
		'week_no'
    ];
}
