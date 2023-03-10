<?php

namespace Database\Factories;

use App\Constants\MRP;
use App\Models\OrderList;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderListFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = OrderList::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'status' => Arr::random([MRP::MRP_ORDER_LIST_STATUS_WAIT, MRP::MRP_ORDER_LIST_STATUS_RELEASE, MRP::MRP_ORDER_LIST_STATUS_DONE]),
            'contract_code' => strtoupper(Str::random(10)),
            'eta'  => now()->format('Y-m-d'),
            'part_code' => strtoupper(Str::random(2)),
            'part_color_code' => strtoupper(Str::random(2)),
            'part_group' => strtoupper(Str::random(2)),
            'actual_quantity' => mt_rand(1, 50),
            'supplier_code' => strtoupper(Str::random(5)),
            'import_id' => mt_rand(1, 100),
            'moq' => mt_rand(1, 100),
            'mrp_quantity' => mt_rand(1, 100),
            'plant_code' => strtoupper(Str::random(5)),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withDeleted(Carbon $date = null): OrderListFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
