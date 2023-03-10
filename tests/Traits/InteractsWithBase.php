<?php

namespace Tests\Traits;

use Illuminate\Support\Arr;

trait InteractsWithBase
{
    private $service;

    public function getDataPaginate($model, $service, $params, $listItemQuery = null): array
    {
        $this->service = $service;
        $listItemQuery = $listItemQuery ?: $model::query()->limit($params['per_page'])->offset(0)->latest('id')->get();
        $listItemService = $this->service->paginate($params);

        $instanceModel = new $model();
        $fillables = $instanceModel->getFillable();

        $dataQuery = $listItemQuery->toArray();
        $dataService = $listItemService->toArray()['data'];

        foreach ($dataQuery as $key => $val) {
            $dataQuery[$key] = Arr::only($val, $fillables);
        }

        foreach ($dataService as $key => $val) {
            $dataService[$key] = Arr::only($val, $fillables);
        }

        return [$listItemService, $dataQuery, $dataService];
    }
}
