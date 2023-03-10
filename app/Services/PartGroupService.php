<?php

namespace App\Services;

use App\Models\MrpOrderCalendar;
use App\Models\OrderCalendar;
use App\Models\PartGroup;
use App\Models\Part;
use Illuminate\Support\Str;

class PartGroupService extends BaseService
{
    protected array $fieldRelation = ['group', 'part_group'];

    protected array $classRelationDelete = [ Part::class, MrpOrderCalendar::class];
    /**
     * @return string
     */
    public function model(): string
    {
        return PartGroup::class;
    }

    /**
     * @var array|string[]
     */
    protected array $defaultRelations = [
        'updatedBy',  'remarkable.updatedBy'
    ];

    public function store(array $attributes, bool $hasRemark = true)
    {
        if (isset($attributes['type_part_group']) && $attributes['type_part_group'] === 'abroad') {
            $attributes['delivery_lead_time'] = null;
        } else {
            $attributes['lead_time'] = null;
        }
        $parent = $this->query->create($attributes);

        if ($hasRemark) {
            $this->createRemark($parent);
        }

        return $parent->push() ? $parent : false;
    }

    public function update($parent, array $attributes, bool $hasRemark = true)
    {
        if (isset($attributes['type_part_group']) && $attributes['type_part_group'] === 'abroad') {
            $attributes['delivery_lead_time'] = null;
        } else {
            $attributes['lead_time'] = null;
        }
        if (is_integer($parent)) {
            $parent = $this->query->findOrFail($parent);
        }
        $parent->fill($attributes);

        if ($hasRemark) {
            $this->createRemark($parent);
        }

        if ($parent->push()) {
            if ($hasRemark) {
                return $parent->with('remarkable.updatedBy')->first();
            } else {
                return $parent;
            }
        } else {
            return false;
        }
    }

    public function getPartGroupByCode($code) {
        if ($this->checkParamFilter($code)) {
            return $this->findBy(['code' => $code]);
        } else {
            return null;
        }
    }

}
