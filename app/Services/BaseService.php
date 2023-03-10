<?php


namespace App\Services;

use App\Exports\PlantExport;
use App\Models\MrpProductionPlanImport;
use App\Models\Remark;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

abstract class BaseService
{
    const TIME_STAMP = ['created_at', 'updated_at', 'deleted_at'];

    /** @var $model Model */
    protected Model $model;

    protected array $fieldRelation = [];

    /** @var Builder $query */
    public Builder $query;

    /** @var array $query */
    protected array $defaultRelations = [];

    protected array $classRelationDelete = [];

    public function __construct()
    {
        $this->setModel();
        $this->setQuery();
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public abstract function model();

    /**
     * Set Eloquent Model to instantiate
     *
     * @return void
     */
    private function setModel(): void
    {
        $newModel = App::make($this->model());

        if (!$newModel instanceof Model)
            throw new \RuntimeException("Class {$newModel} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        $this->model = $newModel;
    }

    /**
     * @return void
     */
    private function setQuery()
    {
        $this->query = $this->model->query();
    }

    /**
     * Getter for relations.
     */
    public function getRelations(array $relations = []): array
    {
        return array_merge($this->defaultRelations, $relations);
    }

    /**
     * @param $params
     * @param array $relations
     * @param bool $withTrashed
     * @param bool $withDefaultFilter
     */
    public function buildBasicQuery($params = null, array $relations = [], bool $withTrashed = false, bool $withDefaultFilter = false)
    {
        $params = $params ?: request()->toArray();

        $relations = $this->getRelations($relations);
        if ($relations && count($relations)) {
            $this->query->with($relations);
        }
        if ($withTrashed) {
            $this->query->withTrashed();
        }
        $this->addDefaultFilterCode($params);
        if (method_exists($this, 'addFilter')) {
            $this->addFilter($params);
        }
        if ($withDefaultFilter) {
            $this->addDefaultFilter($this->query, $params);
        }
    }

    /**
     * @param Builder $query
     * @param $params
     * @return Builder
     */
    protected function addDefaultFilter(Builder $query, $params = null): Builder
    {
        $params = $params ?: request()->toArray();
        if (isset($params['filter']) && $params['filter']) {
            if (gettype($params['filter']) != 'array') {
                $filters = json_decode($params['filter'], true);
            } else {
                $filters = $params['filter'];
            }
            foreach ($filters as $key => $filter) {
                $this->basicFilter($query, $key, $filter);
            }
        }
        if (isset($params['sort']) && $params['sort']) {
            $sort = explode('|', $params['sort']);
            if ($sort && count($sort) == 2) {
                $query->orderBy($sort[0], $sort[1]);
            }
        }

        return $query;
    }

    /**
     * @param null $params
     * @param bool $searchLike
     * @return void
     */
    protected function addDefaultFilterCode($params = null, bool $searchLike = true)
    {
        $params = $params ?: request()->toArray();
        if (isset($params['code']) && $this->checkParamFilter($params['code']) && in_array('code', $this->model->getFillable())) {
            if ($searchLike) {
                $this->whereLike('code', $params['code']);
            } else {
                $this->query->where('code', $params['code']);
            }
        }
    }

    /**
     * @param Builder $query
     * @param $key
     * @param $filter
     * @return void
     */
    protected function basicFilter(Builder $query, $key, $filter)
    {
        if (is_array($filter)) {
            if ($key == 'equal') {
                foreach ($filter as $index => $value) {
                    if ($this->checkParamFilter($value)) {
                        $query->where($index, $value);
                    }
                }
            } else if ($key == 'like') {
                foreach ($filter as $index => $value) {
                    if ($this->checkParamFilter($value)) {
                        $query->where($index, 'LIKE', '%' . $value . '%');
                    }
                }
            } else if ($key == 'range') {
                foreach ($filter as $index => $value) {
                    if ($this->checkParamFilter($value)) {
                        if (is_array($value) && count($value) == 2 && in_array($index, static::TIME_STAMP)) {
                            $query->whereBetween($index, $value);
                        }
                    }
                }
            } else if ($key == 'in') {
                foreach ($filter as $index => $value) {
                    if ($this->checkParamFilter($value)) {
                        if (is_array($value)) {
                            $query->whereIn($index, $value);
                        }
                    }
                }
            } else if ($key == 'relation') {
                foreach ($filter as $relation => $relationFilters) {
                    if (is_array($relationFilters) && count($relationFilters)) {
                        foreach ($relationFilters as $index => $value) {
                            if ($value && count($value)) {
                                $query->whereHas($relation, function ($builder) use ($index, $value) {
                                    $this->basicFilter($builder, $index, $value);
                                });
                            }
                        }
                    }
                }
            } else {
                if (count($filter)) {
                    $query->whereIn($key, $filter);
                }
            }
        } else {
            $query->where($key, 'LIKE', '%' . $filter . '%');
        }
    }

    /**
     * @param $value
     * @return bool
     */
    protected function checkParamFilter($value): bool
    {
        return $value != '' && $value != null;
    }

    /**
     * @param $value
     * @return bool
     */
    protected function checkParamYearFilter($value): bool
    {
        if ($this->checkParamFilter($value)) {
            return preg_match("/^[0-9]{4}$/", $value);
        }
        return false;
    }

    /**
     * @param $value
     * @return bool
     */
    protected function checkParamMonthFilter($value): bool
    {
        if ($this->checkParamFilter($value)) {
            return preg_match("/^([1-9]|0[1-9]|1[0-2])$/", $value);
        }
        return false;
    }

    /**
     * @param $value
     * @param string $format
     * @return bool
     */
    protected function checkParamDateFilter($value, string $format = 'Y-m-d'): bool
    {
        if ($this->checkParamFilter($value)) {
            $date_parse = date_parse_from_format($format, $value);
            return !$date_parse['error_count'];
        }
        return false;
    }

    /**
     * Escape special characters for a LIKE query.
     *
     * @param string $value
     * @param string $char
     *
     * @return string
     */
    function escapeLike(string $value, string $char = '\\'): string
    {
        return str_replace(
            [$char, '%', '_'],
            [$char . $char, $char . '%', $char . '_'],
            $value
        );
    }

    /**
     * @param array $columns
     * @return Builder[]|Collection
     */
    public function findAll(array $columns = ['*'])
    {
        return $this->query->get(is_array($columns) ? $columns : func_get_args());
    }

    /**
     * Retrieve the specified resource.
     *
     * @param int $id
     * @param array $relations
     * @param array $appends
     * @param array $hidden
     * @param bool $withTrashed
     * @return Model
     */
    public function show(int $id, array $relations = [], array $appends = [], array $hidden = [], bool $withTrashed = false): Model
    {
        if ($withTrashed) {
            $this->query->withTrashed();
        }
        $relations = $this->getRelations($relations);
        return $this->query->with($relations)->findOrFail($id)->setAppends($appends)->makeHidden($hidden);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param array $attributes
     * @param bool $hasRemark
     * @return Model|bool
     * @throws Exception
     */
    public function store(array $attributes, bool $hasRemark = true)
    {

        $parent = $this->query->create($attributes);
        $relations = [];

        foreach (array_filter($attributes, [$this, 'isRelation']) as $key => $models) {
            if (method_exists($parent, $relation = Str::camel($key))) {
                $relations[] = $relation;
                $this->syncRelations($parent->$relation(), $models, false);
            }
        }
        if (count($relations)) {
            $parent->load($relations);
        }

        if ($hasRemark) {
            $this->createRemark($parent);
        }

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
     * @throws Exception
     */
    public function update($parent, array $attributes, bool $hasRemark = true)
    {
        if (is_integer($parent)) {
            $parent = $this->query->findOrFail($parent);
        }
        $parent->fill($attributes);
        $relations = [];

        foreach (array_filter($attributes, [$this, 'isRelation']) as $key => $models) {
            if (method_exists($parent, $relation = Str::camel($key))) {
                $relations[] = $relation;
                $this->syncRelations($parent->$relation(), $models);
            }
        }
        if (count($relations)) {
            $parent->load($relations);
        }

        if ($hasRemark) {
            $this->createRemark($parent);
        }

        if ($parent->push()) {
            return $parent;
        } else {
            return false;
        }
    }

    /**
     * @param $parent
     * @return void
     */
    protected function createRemark($parent)
    {
        $remark_content = request()->get('remark', '');
        if ($remark_content) {
            Remark::query()->create(
                [
                    'modelable_type' => get_class($this->model),
                    'modelable_id' => $parent->id,
                    'content' => $remark_content
                ]
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Model|int $item
     * @param bool $force
     * @return bool|array
     *
     */
    public function destroy($item, bool $force = false)
    {
        if (is_integer($item)) {
            $item = $this->query->findOrFail($item);
        }
        $used = false;
        foreach ($this->classRelationDelete as $model) {
            $model = new $model;
            $modelQuery = $model::query();
            if (in_array('plant_code', $model->getFillable()) && isset($item->plant_code)) {
                $modelQuery->where('plant_code', $item->plant_code);
            }
            $fieldRelations = $this->fieldRelation;
            $modelQuery->where(function ($query) use ($fieldRelations, $model, $item) {
                foreach ($fieldRelations as $field) {
                    if (in_array($field, $model->getFillable()))
                        $query->orWhere($field, $item->code);
                }
            });
            if ($modelQuery->count() > 0) {
                $used = true;
                break;
            }
        }
        if ($used) {
            return false;
        }

        return $item->{$force ? 'forceDelete' : 'delete'}();
    }

    /**
     * @param $id
     * @return bool
     */
    public function restore($id): bool
    {
        return $this->query->withTrashed()->findOrFail($id)->restore();
    }

    /**
     * @param array $attrs
     * @return Builder|Model|null|object
     */
    public function findBy(array $attrs)
    {
        return $this->query->where($attrs)->first();
    }

    /**
     * @param array $attributes
     * @param array $values
     * @return Builder|Model
     */
    public function firstOrCreate(array $attributes, array $values = [])
    {
        return $this->query->firstOrCreate($attributes, $values);
    }

    /**
     * @param array $attributes
     * @param array $values
     * @return Builder|Model
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->query->updateOrCreate($attributes, $values);
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

        $this->buildBasicQuery($params, $relations, $withTrashed);
        return $this->query->latest('id')->paginate($limit);
    }

    /**
     * @param $id
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function findById($id)
    {
        return $this->model->query()->find($id);
    }

    /**
     * @param $value
     * @return bool
     */
    private function isRelation($value): bool
    {
        return is_array($value) || $value instanceof Model;
    }

    /**
     * @param Relation $relation
     * @param array | Model $conditions
     * @param bool $detaching
     * @return void
     * @throws Exception
     */
    private function syncRelations(Relation $relation, $conditions, bool $detaching = true): void
    {
        $conditions = is_array($conditions) ? $conditions : [$conditions];
        $relatedModels = [];
        foreach ($conditions as $condition) {
            if ($condition instanceof Model) {
                $relatedModels[] = $condition;
            } else if (is_array($condition)) {
                $relatedModels[] = $relation->firstOrCreate($condition);
            }
        }

        if ($relation instanceof BelongsToMany && method_exists($relation, 'sync')) {
            $relation->sync($this->parseIds($relatedModels), $detaching);
        } else if ($relation instanceof HasMany | $relation instanceof HasOne) {
            $relation->saveMany($relatedModels);
        } else {
            throw new Exception('Relation not supported');
        }
    }

    /**
     * @param array $models
     * @return array
     */
    private function parseIds(array $models): array
    {
        $ids = [];
        foreach ($models as $model) {
            $ids[] = $model instanceof Model ? $model->getKey() : $model;
        }

        return $ids;
    }

    /**
     * @return array
     */
    public function searchCode(): array
    {
        $params = request()->toArray();
        $limit = (int)($params['per_page'] ?? 20);

        if (method_exists($this, 'addFilterCode')) {
            $this->addFilterCode($params);
        } elseif (method_exists($this, 'addFilter')) {
            $this->addFilter($params);
        }

        $this->addDefaultFilterCode($params);

        return $this->query
            ->select('code')
            ->distinct()
            ->orderBy('code')
            ->limit($limit)
            ->pluck('code')
            ->toArray();
    }

    /**
     * @param $column
     * @param $search
     */
    public function whereLike($column, $search)
    {
        $search = $this->escapeLike($search);
        $this->query->where($column, 'LIKE', '%' . $search . '%');
    }

    /**
     * @return array
     */
    public function getColumnValue(): array
    {
        $column = request()->get('column', 'code');
        if (!in_array($column, $this->model->getFillable())) {
            return [];
        }

        $keyword = request()->get('keyword');
        if ($this->checkParamFilter($keyword)) {
            $this->whereLike($column, $keyword);
        }

        $plantCode = request()->get('plant_code');
        if ($plantCode && in_array('plant_code', $this->model->getFillable())) {
            $this->query->where('plant_code', $plantCode);
        }

        $importId = request()->get('import_id');
        if ($importId) {
            $this->query->where('import_id', '=', $importId);
        }

        $limit = (int)(request()->get('per_page', 20));
        $values = $this->query
            ->select($column)
            ->distinct()
            ->orderBy($column)
            ->limit($limit)
            ->pluck($column)
            ->toArray();
        $trimmedArray = array_map('trim', $values);
        return array_values(array_filter($trimmedArray));
    }

    /**
     * Replaces spaces with full text search wildcards
     *
     * @param string $term
     * @return string
     */
    protected function fullTextWildcards(string $term): string
    {
        // removing symbols used by MySQL
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
        $term = str_replace($reservedSymbols, '', $term);

        $words = explode(' ', $term);

        foreach ($words as $key => $word) {
            /*
             * applying + operator (required word) only big words
             * because smaller ones are not indexed by mysql
             */
            if (strlen($word) >= 2) {
                $words[$key] = '+' . $word . '*';
            }
        }

        return implode(' ', $words);
    }

    /**
     * @param Request $request
     * @param $export
     * @param string $fileName
     * @return BinaryFileResponse
     */
    public function export(Request $request, $export, string $fileName): BinaryFileResponse
    {
        $type = $request->get('type', 'xls');
        $dateFile = Carbon::now()->format('dmY');
        if ($type == 'pdf') {
            $fileName = $fileName . '_' . $dateFile . '.pdf';
            return Excel::download(new $export($fileName), $fileName, \Maatwebsite\Excel\Excel::MPDF);
        } else {
            return Excel::download(new $export, $fileName . '_' . $dateFile . '.xlsx');
        }
    }

    /**
     * @param $params
     * @return void
     */
    public function addFilterUpdatedAt($params)
    {
        if (isset($params['updated_at']) && $this->checkParamDateFilter($params['updated_at'])) {
            $this->query->whereDate('updated_at', '=', $params['updated_at']);
        }
    }

    /**
     * @param $params
     * @param bool $searchLike
     * @return void
     */
    public function addFilterPlantCode($params, bool $searchLike = null)
    {
        if ($searchLike == null) {
            $searchLike = request()->get('search_like') !== 'false';
        }

        if ($searchLike) {
            if (isset($params['plant_code']) && $this->checkParamFilter($params['plant_code'])) {
                $this->whereLike('plant_code', $params['plant_code']);
            }
        } else {
            if (isset($params['plant_code']) && $this->checkParamFilter($params['plant_code'])) {
                $this->query->where('plant_code', $params['plant_code']);
            }
        }
    }

    /**
     * @param $params
     * @param bool $searchLike
     * @return void
     */
    public function addFilterWarehouseCode($params, bool $searchLike = true)
    {
        if ($searchLike) {
            if (isset($params['warehouse_code']) && $this->checkParamFilter($params['warehouse_code'])) {
                $this->whereLike('warehouse_code', $params['warehouse_code']);
            }
        } else {
            if (isset($params['warehouse_code']) && $this->checkParamFilter($params['warehouse_code'])) {
                $this->query->where('warehouse_code', $params['warehouse_code']);
            }
        }
    }

    /**
     * @param $params
     * @param bool $searchLike
     * @return void
     */
    public function addFilterPartAndPartColor($params, bool $searchLike = true)
    {
        if ($searchLike) {
            if (isset($params['part_code']) && $this->checkParamFilter($params['part_code'])) {
                $this->whereLike('part_code', $params['part_code']);
            }

            if (isset($params['part_color_code']) && $this->checkParamFilter($params['part_color_code'])) {
                $this->whereLike('part_color_code', $params['part_color_code']);
            }
        } else {
            if (isset($params['part_code']) && $this->checkParamFilter($params['part_code'])) {
                $this->query->where('part_code', $params['part_code']);
            }

            if (isset($params['part_color_code']) && $this->checkParamFilter($params['part_color_code'])) {
                $this->query->where('part_color_code', $params['part_color_code']);
            }
        }
    }

    /**
     * @param $params
     * @return void
     */
    public function addFilterDefect($params)
    {
        if (isset($params['defect_id'])) {
            if ($params['defect_id'])
                $this->query->whereNotNull('defect_id');
            else
                $this->query->whereNull('defect_id');
        }

    }

    /**
     * @param $params
     * @param $status
     * @return void
     */
    protected function addFilterImportFile(&$params, $status = null)
    {
        $importId = $params['import_id'] ?? null;
        if (!$importId) {
            $importFile = MrpProductionPlanImportService::getLastRunFileByStatus($status);
            if (!$importFile) {
                return;
            } else {
                $importId = $importFile->id;
            }
        } else {
            $importFile = MrpProductionPlanImport::query()->where('id', $importId)->first();
        }
        if ($importFile) {
            $params['import_file'] = [
                'id' => $importFile->id,
                'original_file_name' => $importFile->original_file_name,
                'mrp_or_result' => $importFile->mrp_or_result,
                'mrp_or_status' => $importFile->mrp_or_status
            ];
        }
        $this->query->where('import_id', $importId);
    }

    /**
     * @param $params
     * @return void
     */
    protected function addFilterMrpWeekDefinitionMonthYear($params)
    {
        if (isset($params['year']) && $this->checkParamYearFilter($params['year'])) {
            $year = $params['year'];
        } else {
            $year = Carbon::now()->year;
        }

        if (isset($params['month']) && $this->checkParamMonthFilter($params['month'])) {
            $month = $params['month'];
        } else {
            $month = Carbon::now()->month;
        }

        if (isset($params['group_by']) && $this->checkParamFilter($params['group_by'])) {
            $groupBy = $params['group_by'];
        } else {
            $groupBy = 'day';
        }

        if ($groupBy == 'day' || $groupBy == 'week') {
            $this->query->where(['year' => $year, 'month_no' => $month]);
        } else {
            $this->query->where(['year' => $year]);
        }
    }

    /**
     * @param $defectId
     * @return bool
     */
    private function defectIsOK($defectId): bool
    {
        return strtolower($defectId) == 'o';
    }

    /**
     * @param $keys
     * @param $data
     * @return string
     */
    protected function convertDataToKey($keys, $data): string
    {
        $data = array_map(function ($key) use ($data) {
            return $data[$key];
        }, $keys);

        return implode('-', $data);
    }
}
