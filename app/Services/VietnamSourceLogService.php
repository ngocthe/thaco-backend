<?php

namespace App\Services;

use App\Models\VietnamSourceLog;
use Carbon\Carbon;

class VietnamSourceLogService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return VietnamSourceLog::class;
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

        if (isset($params['contract_code']) && $this->checkParamFilter($params['contract_code'])) {
            $this->whereLike('contract_code', $params['contract_code']);
        }

        if (isset($params['invoice_code']) && $this->checkParamFilter($params['invoice_code'])) {
            $this->whereLike('invoice_code', $params['invoice_code']);
        }

        if (isset($params['bill_of_lading_code']) && $this->checkParamFilter($params['bill_of_lading_code'])) {
            $this->whereLike('bill_of_lading_code', $params['bill_of_lading_code']);
        }

        if (isset($params['container_code']) && $this->checkParamFilter($params['container_code'])) {
            $this->whereLike('container_code', $params['container_code']);
        }

        if (isset($params['case_code']) && $this->checkParamFilter($params['case_code'])) {
            $this->whereLike('case_code', $params['case_code']);
        }

        if (isset($params['supplier_code']) && $this->checkParamFilter($params['supplier_code'])) {
            $this->whereLike('supplier_code', $params['supplier_code']);
        }

        if (isset($params['delivery_date']) && $this->checkParamDateFilter($params['delivery_date'], 'Y-m-d')) {
            $this->query->whereDate('delivery_date', '=', Carbon::parse($params['delivery_date'])->format('Y-m-d'));
        }

        $this->addFilterPartAndPartColor($params);
        $this->addFilterPlantCode($params);
        $this->addFilterUpdatedAt($params);

    }
}
