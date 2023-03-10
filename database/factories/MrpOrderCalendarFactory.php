<?php

namespace Database\Factories;

use App\Constants\MRP;
use App\Models\BwhOrderRequest;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MrpOrderCalendarFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $now = CarbonImmutable::now();
        return [
            'contract_code' => strtoupper(Str::random(5)),
            'part_group' => strtoupper(Str::random(2)),
            'etd' => $now->format('Y-m-d'),
            'eta' => $now->addDays()->format('Y-m-d'),
            'target_plan_from' => $now->format('Y-m-d'),
            'target_plan_to' => $now->addDays()->format('Y-m-d'),
            'buffer_span_from' => $now->format('Y-m-d'),
            'buffer_span_to' => $now->addDays()->format('Y-m-d'),
            'order_span_from' => $now->format('Y-m-d'),
            'order_span_to' => $now->addDays()->format('Y-m-d'),
            'mrp_or_run' => $now->format('Y-m-d'),
            'status' => Arr::random([MRP::MRP_ORDER_CALENDAR_STATUS_WAIT, MRP::MRP_ORDER_CALENDAR_STATUS_DONE]),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): MrpOrderCalendarFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
