<?php

namespace App\Models;
use App\Traits\CreatedUpdatedByAdmin;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\ProductionPlan
 *
 * @property int $id
 * @property string $plan_date
 * @property string $msc_code
 * @property string|null $vehicle_color_code
 * @property int $volume
 * @property int|null $import_id
 * @property string $plant_code
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Admin $updatedBy
 * @property-read Msc $msc
 * @property mixed $days
 * @property mixed $volumes
 * @method static Builder|ProductionPlan newModelQuery()
 * @method static Builder|ProductionPlan newQuery()
 * @method static \Illuminate\Database\Query\Builder|ProductionPlan onlyTrashed()
 * @method static Builder|ProductionPlan query()
 * @method static Builder|ProductionPlan whereCreatedAt($value)
 * @method static Builder|ProductionPlan whereCreatedBy($value)
 * @method static Builder|ProductionPlan whereDeletedAt($value)
 * @method static Builder|ProductionPlan whereId($value)
 * @method static Builder|ProductionPlan whereImportId($value)
 * @method static Builder|ProductionPlan whereMscCode($value)
 * @method static Builder|ProductionPlan wherePlanDate($value)
 * @method static Builder|ProductionPlan whereUpdatedAt($value)
 * @method static Builder|ProductionPlan whereUpdatedBy($value)
 * @method static Builder|ProductionPlan whereVehicleColorCode($value)
 * @method static Builder|ProductionPlan whereVolume($value)
 * @method static \Illuminate\Database\Query\Builder|ProductionPlan withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ProductionPlan withoutTrashed()
 * @mixin Eloquent
 */
class ProductionPlan extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'plan_date',
		'msc_code',
		'vehicle_color_code',
		'volume',
		'import_id',
        'plant_code'
    ];

    /**
     * @return BelongsTo
     */
    public function msc(): BelongsTo
    {
        return $this->belongsTo(Msc::class, 'msc_code', 'code');
    }
}
