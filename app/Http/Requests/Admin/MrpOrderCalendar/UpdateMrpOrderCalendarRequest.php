<?php

namespace App\Http\Requests\Admin\MrpOrderCalendar;

use App\Constants\MRP;
use App\Http\Requests\BaseApiRequest;
use App\Models\MrpOrderCalendar;
use App\Rules\TargetNotWithinLeadTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Schema(
 *     title="Create Mrp Order Calendar Request",
 *     type="object"
 * )
 */
class UpdateMrpOrderCalendarRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var string
     */
    public string $etd;

    /**
     * @OA\Property()
     * @var string
     */
    public string $eta;

    /**
     * @OA\Property()
     * @var string
     */
    public string $target_plan_from;

    /**
     * @OA\Property()
     * @var string
     */
    public string $target_plan_to;

    /**
     * @OA\Property()
     * @var string
     */
    public string $buffer_span_from;

    /**
     * @OA\Property()
     * @var string
     */
    public string $buffer_span_to;

    /**
     * @OA\Property()
     * @var string
     */
    public string $order_span_from;

    /**
     * @OA\Property()
     * @var string
     */
    public string $order_span_to;

    /**
     * @OA\Property()
     * @var string
     */
    public string $mrp_or_run;

    /**
     * @OA\Property()
     * @var string
     */
    public string $remark;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $order_span_to_before_etd = '';
        if($this->get('etd')) {
            $order_span_to_before_etd  = '|before:' . Carbon::parse($this->get('etd'))->subDays(6)->format('Y-m-d');
        }

        $partGroup = null;
        $mrpRun = null;

        if($this->get('id')) {
            $mrpOrdering = MrpOrderCalendar::query()->find($this->get('id'));
            if($mrpOrdering) {
                $partGroup = $mrpOrdering->part_group;
                $mrpRun = $this->get('mrp_or_run');
            }

            if(isset($mrpOrdering) && $mrpOrdering->status === MRP::MRP_ORDER_CALENDAR_STATUS_DONE) {
                return [ 'eta' => 'bail|required|date'];
            }
        }

        // Order Span From <= Order Span to < ETD <= ETA < Target Plan From <= Target Plan To < Buffer Span From <= Buffer Span To
        // target_plan_from,... is the first day on week


        return [
            'etd' => 'bail|required_with:eta|date',
            'eta' => 'bail|required_with:etd|date|after_or_equal:etd',
            'target_plan_from' => ['bail','required','required_with:target_plan_to','date', 'after:eta', New TargetNotWithinLeadTime($partGroup, $mrpRun)],
            'target_plan_to' => 'bail|required|required_with:target_plan_from|date|after_or_equal:target_plan_from',
            'buffer_span_from' => 'bail|nullable|required_with:buffer_span_to|date|after:target_plan_to',
            'buffer_span_to' => 'bail|nullable|required_with:buffer_span_from|date|after_or_equal:buffer_span_from',
            'order_span_from' => 'bail|nullable|required_with:order_span_to|date',
            'order_span_to' => 'bail|nullable|required_with:order_span_from|date|after_or_equal:order_span_from' . $order_span_to_before_etd,
            'mrp_or_run' => 'bail|required|date',
            'remark' => 'nullable|string|max:255'
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'target_plan_from.after' => 'The Target Plan From must be a date after ETA.',
            'order_span_to.before' => 'The Order Span To must be a date before ETD.'
        ];
    }
}
