<?php

namespace Database\Factories;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BwhInventoryLogFactory extends Factory
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
            'contract_code' =>strtoupper(Str::random(9)),
            'invoice_code' =>strtoupper(Str::random(10)),
            'bill_of_lading_code' =>strtoupper(Str::random(13)),
            'container_code' =>strtoupper(Str::random(11)),
            'case_code' =>strtoupper(Str::random(9)),
            'part_code' => strtoupper(Str::random(10)),
            'part_color_code' => (string)(rand(1, 20)),
            'box_type_code' => strtoupper(Str::random(5)),
            'box_quantity' => rand(1, 100),
            'part_quantity' => rand(1, 100),
            'unit' => Arr::random(['PIECES', 'GRAM', 'LITTER', 'KG']),
            'supplier_code' => strtoupper(Str::random(5)),
            'container_received' => $now->format('Y-m-d'),
            'devanned_date' => $now->addDays(1)->format('Y-m-d'),
            'stored_date' => $now->addDays(2)->format('Y-m-d'),
            'warehouse_location_code' => strtoupper(Str::random(5)),
            'warehouse_code' => strtoupper(Str::random(5)),
            'shipped_date' => $now->format('Y-m-d'),
            'plant_code' => strtoupper(Str::random(5)),
            'requested' => false,
            'defect_id' => Arr::random(['W','D','X','S']),
            'is_parent_case' => true,
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withAttribute(string $nameAttr, $valueAttr): BwhInventoryLogFactory
    {
        return $this->state(function (array $attributes) use ($nameAttr, $valueAttr) {
            return [
                $nameAttr => $valueAttr
            ];
        });
    }

    public function withDeleted(Carbon $date = null): BwhInventoryLogFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
