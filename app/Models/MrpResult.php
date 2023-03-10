<?php

namespace App\Models;

use App\Traits\CreatedUpdatedByAdmin;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\MrpResult
 *
 * @property int $id
 * @property string $production_date
 * @property string $msc_code
 * @property string $vehicle_color_code
 * @property int $production_volume
 * @property string $part_code
 * @property string $part_color_code
 * @property int $part_requirement_quantity
 * @property int $import_id
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
 * @property mixed $months
 * @method static Builder|MrpResult newModelQuery()
 * @method static Builder|MrpResult newQuery()
 * @method static \Illuminate\Database\Query\Builder|MrpResult onlyTrashed()
 * @method static Builder|MrpResult query()
 * @method static Builder|MrpResult whereCreatedAt($value)
 * @method static Builder|MrpResult whereCreatedBy($value)
 * @method static Builder|MrpResult whereDeletedAt($value)
 * @method static Builder|MrpResult whereId($value)
 * @method static Builder|MrpResult whereImportId($value)
 * @method static Builder|MrpResult whereMscCode($value)
 * @method static Builder|MrpResult wherePartCode($value)
 * @method static Builder|MrpResult wherePartColorCode($value)
 * @method static Builder|MrpResult wherePartRequirementQuantity($value)
 * @method static Builder|MrpResult wherePlantCode($value)
 * @method static Builder|MrpResult whereProductionDate($value)
 * @method static Builder|MrpResult whereProductionVolume($value)
 * @method static Builder|MrpResult whereUpdatedAt($value)
 * @method static Builder|MrpResult whereUpdatedBy($value)
 * @method static Builder|MrpResult whereVehicleColorCode($value)
 * @method static \Illuminate\Database\Query\Builder|MrpResult withTrashed()
 * @method static \Illuminate\Database\Query\Builder|MrpResult withoutTrashed()
 * @mixin Eloquent
 */
class MrpResult extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'production_date',
        'msc_code',
        'vehicle_color_code',
        'production_volume',
        'part_code',
        'part_color_code',
        'part_requirement_quantity',
        'import_id',
        'plant_code'
    ];
}
