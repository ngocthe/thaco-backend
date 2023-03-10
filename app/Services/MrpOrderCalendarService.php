<?php

namespace App\Services;

use App\Helpers\DateTimeHelper;
use App\Models\MrpOrderCalendar;
use App\Models\OrderList;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MrpOrderCalendarService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return MrpOrderCalendar::class;
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

        if (isset($params['part_group']) && $this->checkParamFilter($params['part_group'])) {
            $this->whereLike('part_group', $params['part_group']);
        }

        if (isset($params['status']) && $this->checkParamFilter($params['status'])) {
            $this->query->where('status', $params['status']);
        }

    }

    /**
     * @param array $attributes
     * @return array
     */
    public function convertPayload(array $attributes): array
    {
        if (isset($attributes['target_plan_from']) && $attributes['target_plan_from']) {
            $attributes['target_plan_from'] = self::__wrapperWeekData($attributes['target_plan_from']);
        }
        if (isset($attributes['target_plan_to']) && $attributes['target_plan_to']) {
            $attributes['target_plan_to'] = self::__wrapperWeekData($attributes['target_plan_to']);
        }

        if (isset($attributes['buffer_span_from']) && $attributes['buffer_span_from']) {
            $attributes['buffer_span_from'] = self::__wrapperWeekData($attributes['buffer_span_from']);
        }
        if (isset($attributes['buffer_span_to']) && $attributes['buffer_span_to']) {
            $attributes['buffer_span_to'] = self::__wrapperWeekData($attributes['buffer_span_to']);
        }

        if (isset($attributes['order_span_from']) && $attributes['order_span_from']) {
            $attributes['order_span_from'] = self::__wrapperWeekData($attributes['order_span_from']);
        }
        if (isset($attributes['order_span_to']) && $attributes['order_span_to']) {
            $attributes['order_span_to'] = self::__wrapperWeekData($attributes['order_span_to']);
        }

        return $attributes;
    }

    /**
     * @param $value
     * @return string
     */
    private function __wrapperWeekData($value): string
    {
        $weekDefinition = DateTimeHelper::getWeekDefinitionFromDate($value);
        if($weekDefinition) {
            $monthNo = $weekDefinition->month_no >= 10 ? $weekDefinition->month_no : '0' . $weekDefinition->month_no;
            $yearNo = $weekDefinition->year >= 10 ? $weekDefinition->year : '0' . $weekDefinition->year;

            return 'W' . $weekDefinition->week_no . '-' . $monthNo . '/' . $yearNo;
        } else {
            return '';
        }
    }

    /**
     * @param $id
     * @param $mrpOrderCalendar
     * @param $payload
     * @return bool|\Illuminate\Database\Eloquent\Model|int
     * @throws \Exception
     */
    public function updateEtaWhenStatusDone($id, $payload) {

        if(isset($payload['eta'])) {
            $payload = [
                'eta' => $payload['eta'] ?? '',
                'remark' => $payload['remark'] ?? '',
            ];

            self::updateEtaOrders($id, $payload);

            return parent::update($id, $payload);
        } else {

            $payload = [
                'remark' => $payload['remark'] ?? '',
            ];
            return parent::update($id, $payload);
        }
    }


    /**
     * @param $id
     * @param $payload
     */
    public function updateEtaOrders($id, $payload) {
        if(isset($payload['eta'])) {
            OrderList::query()->join('mrp_order_calendars', function ($builder) {
                $builder->on('mrp_order_calendars.contract_code', 'order_lists.contract_code')
                    ->on('mrp_order_calendars.part_group', 'order_lists.part_group');
            })->where('mrp_order_calendars.id', '=', $id)
                ->update(['order_lists.eta' => Carbon::parse($payload['eta'])->format('Y-m-d')]);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $mrpOrderCalendar
     * @param array $payload
     */
    public function validateETA($mrpOrderCalendar, $payload)
    {
        if (isset($payload['eta']) && Carbon::parse($mrpOrderCalendar->eta)->notEqualTo(Carbon::parse($payload['eta']))) {
            if (!Carbon::parse($payload['eta'])->isAfter(Carbon::now())) {
                return response()->json([
                    'status' => false,
                    'message' => 'The eta must be a date after today.',
                    'data' => [
                        'eta' => [
                            'code' => 10004,
                            'message' => 'The eta must be a date after today.'
                        ]
                    ]
                ], 400);
            }
        }

        return false;
    }

    public function getColumnValue(): array
    {
        $params = request()->except(['code', 'keyword']);

        $this->addFilterGetColumn($params);

        return parent::getColumnValue(); // TODO: Change the autogenerated stub
    }

    /**
     * @param $params
     */
    public function addFilterGetColumn($params = null)
    {
        if (isset($params['contract_code']) && $this->checkParamFilter($params['contract_code'])) {
            $this->query->where('contract_code', $params['contract_code']);
        }

        if (isset($params['part_group']) && $this->checkParamFilter($params['part_group'])) {
            $this->query->where('part_group', $params['part_group']);
        }

        if (isset($params['status']) && $this->checkParamFilter($params['status'])) {
            $this->query->where('status', $params['status']);
        }

    }
}
