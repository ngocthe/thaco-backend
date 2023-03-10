<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class BoxListDefect implements Rule
{
    private $customMsg = '';
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
    public function passes($attribute, $value)
    {
        $params = request()->toArray();
        $defectId = $params['defect_id'] ?? null;
        if ($defectId) {
            $boxList = $params['box_list'];
            $allBoxIsOK = true;
            foreach ($boxList as $box) {
                if ($box['defect_id']) {
                    $allBoxIsOK = false;
                    break;
                }
            }
            if ($allBoxIsOK) {
                $this->customMsg = 'The defect status of the box is not valid';
                return false;
            }
        } else {
            $boxList = $params['box_list'] ?? [];
            $allBoxIsOK = true;
            foreach ($boxList as $box) {
                if ($box['defect_id']) {
                    $allBoxIsOK = false;
                    break;
                }
            }
            if (!$allBoxIsOK) {
                $this->customMsg = 'The defect status of the box is not valid';
                return false;
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->customMsg;
    }
}
