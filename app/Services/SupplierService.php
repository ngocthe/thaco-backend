<?php

namespace App\Services;

use App\Models\BwhInventoryLog;
use App\Models\InTransitInventoryLog;
use App\Models\OrderList;
use App\Models\Procurement;
use App\Models\Supplier;
use App\Models\UpkwhInventoryLog;
use App\Models\VietnamSourceLog;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SupplierService extends BaseService
{
    protected array $fieldRelation = ['supplier_code'];

    protected array $classRelationDelete = [
        Procurement::class,
        InTransitInventoryLog::class,
        BwhInventoryLog::class,
        UpkwhInventoryLog::class,
        OrderList::class,
        VietnamSourceLog::class
    ];

    /**
     * @return string
     */
    public function model(): string
    {
        return Supplier::class;
    }

    /**
     * @var array|string[]
     */
    protected array $defaultRelations = [
        'updatedBy', 'remarkable.updatedBy'
    ];

    /**
     * @param $params
     */
    public function addFilter($params = null)
    {

        if (isset($params['description']) && $this->checkParamFilter($params['description'])) {
            $this->whereLike('description', $params['description']);
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param array $attributes
     * @param bool $hasRemark
     * @return Model|bool
     */
    public function store(array $attributes, bool $hasRemark = true)
    {
        $attributes = $this->sanitizeJsonData($attributes);
        $parent = $this->query->create($attributes);
        $this->createRemark($parent);
        return $parent->push() ? $parent : false;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Model|int $parent
     * @param array $attributes
     * @param bool $hasRemark
     * @return Model|bool
     *
     */
    public function update($parent, array $attributes, bool $hasRemark = true)
    {
        $parent = $this->query->findOrFail($parent);
        $attributes = $this->sanitizeJsonData($attributes);
        $parent->fill($attributes);
        $this->createRemark($parent);
        if ($parent->push()) {
            if ($hasRemark) {
                return $this->query->with('remarkable.updatedBy')->first();
            } else {
                return $parent;
            }
        } else {
            return false;
        }
    }

    /**
     * @param array $attributes
     * @return array
     */
    private function sanitizeJsonData(array $attributes): array
    {
        $keys = ['receiver', 'bcc', 'cc'];
        foreach ($keys as $key) {
            if (isset($attributes[$key])) {
                $attributes[$key] = json_encode($attributes[$key]);
            }
        }
        return $attributes;
    }
}
