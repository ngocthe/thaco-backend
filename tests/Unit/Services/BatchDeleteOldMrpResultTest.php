<?php

namespace Tests\Unit\Services;

use App\Jobs\DeleteOldMrpResult;
use App\Models\Admin;
use App\Models\MrpSimulationResult;
use App\Services\AuthService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class BatchDeleteOldMrpResultTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var AuthService
     */

    public function setUp(): void
    {
        parent::setUp();
        $admin = Admin::factory()->create();
        Auth::login($admin);
    }

    public function test_delete_old_mrp_result_case_created_less_than_3_month()
    {
        $now = CarbonImmutable::now();
        $date = Carbon::now()->toDateTimeString();
        MrpSimulationResult::factory()->sequence(fn($sequence) => [
            'created_at' => $date
        ])->create();

        $row = [
            'production_date' => $now->format('Y-m-d'),
            'part_code' => Str::random(10),
            'part_color_code' => Str::random(2),
            'quantity' => rand(10, 200),
            'plant_code' => Str::random(2),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1,
            'created_at' => $date
        ];
        DB::table('logical_inventory_simulations')->insert($row);

        $job = new DeleteOldMrpResult();
        $job->handle();
        $this->assertDatabaseCount('mrp_simulation_results', 1);
        $this->assertDatabaseCount('logical_inventory_simulations', 1);
        $this->assertDatabaseHas('logical_inventory_simulations', $row);
    }

    public function test_delete_old_mrp_result()
    {
        $date = Carbon::now()->subMonthsWithoutOverflow(3)->format('Y-m-d 00:00:00');
        MrpSimulationResult::factory()->sequence(fn($sequence) => [
            'created_at' => $date
        ])->create();

        $row = [
            'production_date' => Carbon::now()->toDateTimeString(),
            'part_code' => Str::random(10),
            'part_color_code' => Str::random(2),
            'quantity' => rand(10, 200),
            'plant_code' => Str::random(2),
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1,
            'created_at' => $date
        ];
        DB::table('logical_inventory_simulations')->insert($row);

        $job = new DeleteOldMrpResult();
        $job->handle();
        $this->assertDatabaseCount('mrp_simulation_results', 0);
        $this->assertDeleted('logical_inventory_simulations', $row);
        $this->assertDatabaseCount('logical_inventory_simulations', 0);
    }

}
