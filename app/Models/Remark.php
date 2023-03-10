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
 * App\Models\Remark
 *
 * @property int $id
 * @property string $modelable_type
 * @property int $modelable_id
 * @property string $content
 * @property int $created_by
 * @property int $updated_by
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Admin $createdBy
 * @property-read Admin $updatedBy
 * @method static Builder|Remark newModelQuery()
 * @method static Builder|Remark newQuery()
 * @method static \Illuminate\Database\Query\Builder|Remark onlyTrashed()
 * @method static Builder|Remark query()
 * @method static Builder|Remark whereContent($value)
 * @method static Builder|Remark whereCreatedAt($value)
 * @method static Builder|Remark whereCreatedBy($value)
 * @method static Builder|Remark whereDeletedAt($value)
 * @method static Builder|Remark whereId($value)
 * @method static Builder|Remark whereModelableId($value)
 * @method static Builder|Remark whereModelableType($value)
 * @method static Builder|Remark whereUpdatedAt($value)
 * @method static Builder|Remark whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|Remark withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Remark withoutTrashed()
 * @mixin Eloquent
 */
class Remark extends Model
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
        'content'
    ];
}
