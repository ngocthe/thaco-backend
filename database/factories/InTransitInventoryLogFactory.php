<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class InTransitInventoryLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $now = now()->format('Y-m-d');
        return [
            'contract_code' => strtoupper(Str::random(9)),
            'invoice_code' => strtoupper(Str::random(10)),
            'bill_of_lading_code' => strtoupper(Str::random(13)),
            'container_code' => strtoupper(Str::random(11)),
            'case_code' => strtoupper(Str::random(9)),
            'part_code' => strtoupper(Str::random(2)),
            'part_color_code' => (string)(rand(1, 20)),
            'box_type_code' => strtoupper(Str::random(5)),
            'box_quantity' => rand(1, 100),
            'part_quantity' => rand(1, 100),
            'unit' => Arr::random(['PIECES', 'GRAM', 'LITTER', 'KG']),
            'supplier_code' => strtoupper(Str::random(5)),
            'etd' => $now,
            'container_shipped' => $now,
            'eta' => $now,
            'plant_code' => strtoupper(Str::random(5)),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1
        ];
    }

    public function withAttribute(string $nameAttr, $valueAttr): InTransitInventoryLogFactory
    {
        return $this->state(function (array $attributes) use ($nameAttr, $valueAttr) {
            return [
                $nameAttr => $valueAttr
            ];
        });
    }

    public function withDeleted(Date $date = null): InTransitInventoryLogFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
