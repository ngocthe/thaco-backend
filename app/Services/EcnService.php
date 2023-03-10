<?php

namespace App\Services;

use App\Models\Bom;
use App\Models\Ecn;
use App\Models\Part;
use App\Models\PartColor;
use App\Models\VehicleColor;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class EcnService extends BaseService
{
    protected array $fieldRelation = ['ecn_in', 'ecn_out'];

    protected array $classRelationDelete = [Part::class, Bom::class, PartColor::class, VehicleColor::class];

    /**
     * @return string
     */
    public function model(): string
    {
        return Ecn::class;
    }

    /**
     * @var array|string[]
     */
    protected array $defaultRelations = [
        'updatedBy', 'remarkable.updatedBy'
    ];

//    /**
//     * Store a newly created resource in storage.
//     *
//     * @param array $attributes
//     * @param bool $hasRemark
//     * @return Model|bool
//     */
//    public function store(array $attributes, bool $hasRemark = true)
//    {
//        $latest = Ecn::query()->select('actual_line_off_date')->latest()->first();
//
//        if ($latest&&!$latest->actual_line_off_date) {
//            return false;
//        } elseif ($latest && Carbon::createFromFormat('Y-m-d', $attributes['actual_packing_date']) < $latest->actual_line_off_date) {
//            return false;
//        }
//        $parent = $this->query->create($attributes);
//        $this->createRemark($parent);
//        return $parent->push() ? $parent : false;
//    }
//
//    /**
//     * Update the specified resource in storage.
//     *
//     * @param int $ecnId
//     * @param array $attributes
//     * @param bool $hasRemark
//     * @return Model|bool
//     *
//     */
//    public function update($ecnId, array $attributes, bool $hasRemark = true)
//    {
//        $ecn = $this->query->findOrFail($ecnId);
//        $exists = Ecn::query()
//            ->where([
//                'code' => $ecn->code,
//                'page_number' => $attributes['page_number'],
//                'line_number' => $attributes['line_number'],
//                'plant_code' => $ecn->plant_code
//            ])
//            ->where('id', '<>', $ecn->id)
//            ->first();
//        if ($exists) return false;
//
//        $ecn->fill($attributes);
//        $this->createRemark($ecn);
//        return $ecn->push() ? $ecn : false;
//    }

    /**
     * @param $params
     */
    public function addFilter($params = null)
    {

        if (isset($params['page_number']) && $this->checkParamFilter($params['page_number'])) {
            $this->query->where('page_number', 'like', '%' . $params['page_number'] . '%');
        }

        if (isset($params['line_number']) && $this->checkParamFilter($params['line_number'])) {
            $this->query->where('line_number', 'like', '%' . $params['line_number'] . '%');
        }

        if (isset($params['released_party']) && $this->checkParamFilter($params['released_party'])) {
            $this->query->where('released_party', 'like', '%' . $params['released_party'] . '%');
        }

        if (isset($params['released_date']) && $this->checkParamDateFilter($params['released_date'])) {
            $this->query->where('released_date', $params['released_date']);
        }

        if (isset($params['planned_line_off_date']) && $this->checkParamDateFilter($params['planned_line_off_date'])) {
            $this->query->where('planned_line_off_date', $params['planned_line_off_date']);
        }

        if (isset($params['actual_line_off_date']) && $this->checkParamDateFilter($params['actual_line_off_date'])) {
            $this->query->where('actual_line_off_date', $params['actual_line_off_date']);
        }

        if (isset($params['planned_packing_date']) && $this->checkParamDateFilter($params['planned_packing_date'])) {
            $this->query->where('planned_packing_date', $params['planned_packing_date']);
        }

        if (isset($params['actual_packing_date']) && $this->checkParamDateFilter($params['actual_packing_date'])) {
            $this->query->where('actual_packing_date', $params['actual_packing_date']);
        }

        $this->addFilterPlantCode($params);

    }
}
