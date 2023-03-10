<?php

namespace App\Rules;

use App\Models\PartColor;
use Illuminate\Contracts\Validation\Rule;

class BomPartColorCode implements Rule
{

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $params = request()->toArray();
        $partCode = $params['part_code'];
        $plantCode = $params['plant_code'];
        if ($value == 'XX') {
            return true;
        }

        return PartColor::query()
            ->where([
                'code' => $value,
                'part_code' => $partCode,
                'plant_code' => $plantCode
            ])
            ->count();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Part No, Part Color Code, Plant Code are not linked together.';
    }
}
