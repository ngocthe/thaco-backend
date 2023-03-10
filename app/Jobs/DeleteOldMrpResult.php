<?php

namespace App\Jobs;

use App\Models\MrpResult;
use App\Models\MrpSimulationResult;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteOldMrpResult implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $date = Carbon::now()->subMonthsWithoutOverflow(3)->format('Y-m-d 00:00:00');
        DB::beginTransaction();
        try {
            MrpSimulationResult::withTrashed()
                ->where('created_at', '<=', $date)
                ->forceDelete();
            Log::alert('Delete all data of MRP Results before ' . $date . ' successfully');

            DB::table('logical_inventory_simulations')
                ->where('created_at', '<=', $date)
                ->delete();
            Log::alert('Delete all data of logical inventory simulations before ' . $date . ' successfully');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::emergency('Delete all data of MRP Results and logical inventory simulations before ' . $date . ' errors: ');
            Log::error($exception);
        }
        DB::commit();

    }
}
