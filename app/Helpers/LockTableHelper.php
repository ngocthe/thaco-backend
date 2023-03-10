<?php

namespace App\Helpers;

use App\Models\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class LockTableHelper
{
    /**
     * @param Request $request
     * @return void
     * @throws Exception
     */
    public static function checkLockTime(Request $request)
    {
        $setting = Setting::query()->where('key', 'lock_table_master')->first();
        $now = Carbon::now();
        if (isset($setting)) {
            $timeLock = $setting->value;
            $end = Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->format('Y-m-d') . ' ' . $timeLock['end_time'] . ':00');
            $start = Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->format('Y-m-d') . ' ' . $timeLock['start_time'] . ':00');
            if ($start > $end) {
                $start->addDay(-1);
            }
            if ($now >= $start && $now <= $end) {
                if ($request->method() !== 'GET') {
                    throw new \Exception(
                        'Database is currently locked. Please try again later.');
                }
            }
        }
    }
}
