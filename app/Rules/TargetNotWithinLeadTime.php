<?php

namespace App\Rules;

use App\Constants\MRP;
use App\Models\PartGroup;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class TargetNotWithinLeadTime implements Rule
{
    private $partGroup;
    private $mrpRun;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($partGroup, $mrpRun)
    {
        $this->partGroup = $partGroup;
        $this->mrpRun = $mrpRun;
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
        if($this->partGroup && $this->mrpRun) {
            $targetFrom  = Carbon::parse($value);
            $mrpRunAtObj = Carbon::parse($this->mrpRun);

            $partGroup = PartGroup::query()->where('code', '=', $this->partGroup)->first();

            if($partGroup && $this->partGroup === MRP::PART_GROUP_VIETNAM) {
                $mrpRunAtObj->addDays($partGroup->delivery_lead_time);
            } else if($partGroup && $this->partGroup !== MRP::PART_GROUP_VIETNAM) {
                $mrpRunAtObj->addDays($partGroup->lead_time * 7);
            }

            return $targetFrom->isAfter($mrpRunAtObj);
        } else {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Target Span From cannot be set within the lead time.';
    }
}
