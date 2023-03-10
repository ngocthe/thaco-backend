<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class AdminService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return Admin::class;
    }

    /**
     * @var array|string[]
     */
    protected array $defaultRelations = [
        'updatedBy', 'roles', 'remarkable.updatedBy'
    ];

    /**
     * @param $params
     */
    public function addFilter($params = null)
    {
        if (isset($params['company']) && $this->checkParamFilter($params['company'])) {
            $this->whereLike('company', $params['company']);
        }

    }

    /**
     * @param int $id
     * @param string $current_pwd
     * @param string $new_pwd
     * @return Admin|false
     */
    public function changePassword(int $id, string $current_pwd, string $new_pwd)
    {
        /**
         * @var Admin $admin
         */
        $admin = $this->query->findOrFail($id);
        if (Hash::check($current_pwd, $admin->password)) {
            $admin->password = Hash::make($new_pwd);
            $admin->password_default = false;
            return $admin->push() ? $admin : false;
        }
        return false;
    }

    /**
     * @param $params
     * @param array $relations
     * @param bool $withTrashed
     * @return LengthAwarePaginator
     */
    public function paginate($params = null, array $relations = [], bool $withTrashed = false): LengthAwarePaginator
    {
        $params = $params ?: request()->toArray();
        $limit = (int)($params['per_page'] ?? 20);

        $this->buildBasicQuery($params, $relations, $withTrashed, true);
        return $this->query->latest('id')->paginate($limit);
    }
}
