<?php

namespace App\Models;

use App\Traits\CreatedUpdatedByAdmin;
use App\Traits\HasRemark;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Passport\Client;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Token;
use phpDocumentor\Reflection\Types\Boolean;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;



/**
 * App\Models\Admin
 *
 * @property int $id
 * @property string|null $code
 * @property string $name
 * @property string $username
 * @property string|null $email
 * @property string|null $company
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property Boolean|false $password_default
 * @property string|null $remember_token
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read string $remark
 * @property-read Remark|null $remarkable
 * @property-read Collection|Client[] $clients
 * @property-read int|null $clients_count
 * @property-read Admin|null $createdBy
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection|Role[] $roles
 * @property-read int|null $roles_count
 * @property-read Collection|Token[] $tokens
 * @property-read int|null $tokens_count
 * @property-read Admin|null $updatedBy
 * @method static \Database\Factories\AdminFactory factory(...$parameters)
 * @method static Builder|Admin newModelQuery()
 * @method static Builder|Admin newQuery()
 * @method static \Illuminate\Database\Query\Builder|Admin onlyTrashed()
 * @method static Builder|Admin permission($permissions)
 * @method static Builder|Admin query()
 * @method static Builder|Admin role($roles, $guard = null)
 * @method static Builder|Admin whereCode($value)
 * @method static Builder|Admin whereCompany($value)
 * @method static Builder|Admin whereCreatedAt($value)
 * @method static Builder|Admin whereCreatedBy($value)
 * @method static Builder|Admin whereDeletedAt($value)
 * @method static Builder|Admin whereEmail($value)
 * @method static Builder|Admin whereEmailVerifiedAt($value)
 * @method static Builder|Admin whereId($value)
 * @method static Builder|Admin whereName($value)
 * @method static Builder|Admin wherePassword($value)
 * @method static Builder|Admin whereRememberToken($value)
 * @method static Builder|Admin whereUpdatedAt($value)
 * @method static Builder|Admin whereUpdatedBy($value)
 * @method static Builder|Admin whereUsername($value)
 * @method static \Illuminate\Database\Query\Builder|Admin withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Admin withoutTrashed()
 * @mixin Eloquent
 */
class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, CreatedUpdatedByAdmin, HasRemark;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'username',
        'email',
        'company',
        'password',
        'password_default'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function findForPassport($username)
    {
        return $this->where('username', $username)->first();
    }

    public function guardName(): string
    {
        return 'api_admin';
    }
}
