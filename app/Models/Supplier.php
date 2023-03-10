<?php

namespace App\Models;

use App\Traits\CreatedUpdatedByAdmin;
use App\Traits\HasRemark;
use App\Traits\WhereInMultiple;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Supplier
 *
 * @property int $id
 * @property string $code
 * @property string $description
 * @property string $address
 * @property string $phone
 * @property int|null $forecast_by_week
 * @property int|null $forecast_by_month
 * @property mixed|null $receiver
 * @property mixed|null $bcc
 * @property mixed|null $cc
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Admin $createdBy
 * @property-read Admin $updatedBy
 * @property-read string $remark
 * @property-read Remark|null $remarkable
 * @method static Builder|Supplier newModelQuery()
 * @method static Builder|Supplier newQuery()
 * @method static \Illuminate\Database\Query\Builder|Supplier onlyTrashed()
 * @method static Builder|Supplier query()
 * @method static Builder|Supplier whereAddress($value)
 * @method static Builder|Supplier whereBcc($value)
 * @method static Builder|Supplier whereCc($value)
 * @method static Builder|Supplier whereCode($value)
 * @method static Builder|Supplier whereCreatedAt($value)
 * @method static Builder|Supplier whereCreatedBy($value)
 * @method static Builder|Supplier whereDeletedAt($value)
 * @method static Builder|Supplier whereDescription($value)
 * @method static Builder|Supplier whereForecastByMonth($value)
 * @method static Builder|Supplier whereForecastByWeek($value)
 * @method static Builder|Supplier whereId($value)
 * @method static Builder|Supplier wherePhone($value)
 * @method static Builder|Supplier whereReceiver($value)
 * @method static Builder|Supplier whereUpdatedAt($value)
 * @method static Builder|Supplier whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|Supplier withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Supplier withoutTrashed()
 * @mixin Eloquent
 */
class Supplier extends Model
{
    use SoftDeletes, CreatedUpdatedByAdmin, WhereInMultiple, HasRemark, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'description',
        'address',
        'phone',
        'forecast_by_week',
        'forecast_by_month',
        'receiver',
        'bcc',
        'cc'
    ];
}
