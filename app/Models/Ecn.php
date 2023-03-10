<?php

namespace App\Models;

use App\Traits\CreatedUpdatedByAdmin;
use App\Traits\HasRemark;
use App\Traits\WhereInMultiple;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Ecn
 *
 * @property int $id
 * @property string $code
 * @property int $page_number
 * @property int $line_number
 * @property string $description
 * @property string|null $mandatory_level
 * @property string|null $production_interchangeability
 * @property string|null $service_interchangeability
 * @property string|null $released_party
 * @property Carbon|null $released_date
 * @property Carbon|null $planned_line_off_date
 * @property Carbon|null $actual_line_off_date
 * @property Carbon|null $planned_packing_date
 * @property Carbon|null $actual_packing_date
 * @property string|null $vin
 * @property string|null $complete_knockdown
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
 * @method static Builder|Ecn newModelQuery()
 * @method static Builder|Ecn newQuery()
 * @method static \Illuminate\Database\Query\Builder|Ecn onlyTrashed()
 * @method static Builder|Ecn query()
 * @method static Builder|Ecn whereActualLineOffDate($value)
 * @method static Builder|Ecn whereActualPackingDate($value)
 * @method static Builder|Ecn whereCode($value)
 * @method static Builder|Ecn whereCompleteKnockdown($value)
 * @method static Builder|Ecn whereCreatedAt($value)
 * @method static Builder|Ecn whereCreatedBy($value)
 * @method static Builder|Ecn whereDeletedAt($value)
 * @method static Builder|Ecn whereDescription($value)
 * @method static Builder|Ecn whereId($value)
 * @method static Builder|Ecn whereLineNumber($value)
 * @method static Builder|Ecn whereMandatoryLevel($value)
 * @method static Builder|Ecn wherePageNumber($value)
 * @method static Builder|Ecn wherePlannedLineOffDate($value)
 * @method static Builder|Ecn wherePlannedPackingDate($value)
 * @method static Builder|Ecn wherePlantCode($value)
 * @method static Builder|Ecn whereProductionInterchangeability($value)
 * @method static Builder|Ecn whereReleasedDate($value)
 * @method static Builder|Ecn whereReleasedParty($value)
 * @method static Builder|Ecn whereServiceInterchangeability($value)
 * @method static Builder|Ecn whereUpdatedAt($value)
 * @method static Builder|Ecn whereUpdatedBy($value)
 * @method static Builder|Ecn whereVin($value)
 * @method static \Illuminate\Database\Query\Builder|Ecn withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Ecn withoutTrashed()
 * @mixin Eloquent
 */
class Ecn extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'page_number',
        'line_number',
        'description',
        'mandatory_level',
        'production_interchangeability',
        'service_interchangeability',
        'released_party',
        'released_date',
        'planned_line_off_date',
        'actual_line_off_date',
        'planned_packing_date',
        'actual_packing_date',
        'vin',
        'complete_knockdown',
        'plant_code'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'released_date',
        'planned_line_off_date',
        'actual_line_off_date',
        'planned_packing_date',
        'actual_packing_date'
    ];

}
