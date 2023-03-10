<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        Schema::defaultstringLength(191);

        $minDuration = env('QUERY_LOG_MIN_DURATION', 'none');

        if ($minDuration !== 'none') {
            DB::listen(function ($query) use ($minDuration) {
                if ($query->time >= (double)$minDuration) {
                    $time = microtime(true);
                    Log::debug("SQLQuery - time: " . $time . " duration(ms): " . $query->time . " query: " . $query->sql,
                        ['bindings' => $query->bindings]);
                }
            });
        }

//        if(config('app.debug')) {
//            $filename = 'query_' . date('d-m-y') . '.log';
//            $dataToLog = 'Time: ' . Carbon::now() . "\n";
//            File::append(storage_path('logs' . DIRECTORY_SEPARATOR . $filename), $dataToLog . "\n" . str_repeat("=", 20) . "\n");
//
//            DB::listen(function($query) use ($filename) {
//                File::append(
//                    storage_path('logs' . DIRECTORY_SEPARATOR . $filename),
//                    "Duration: " . $query->time . "(ms), SQLQuery: " .$query->sql . ' [' . implode(', ', $query->bindings) . ']' . PHP_EOL . "\n"
//                );
//            });
//        }

        /**
         * Custom validate
         */
        Validator::extend('alpha_num_dash', function ($attribute, $value, $parameters) {
            return (bool)preg_match("/^[A-Z0-9]+(?:[-][A-Z0-9]+)*$/", $value);
        });

        Validator::extend('alpha_num_dash_shift', function ($attribute, $value, $parameters) {
            return (bool)preg_match("/^[A-Za-z0-9]+(?:[-_][A-Za-z0-9]+)*$/", $value);
        });

        Validator::extend('unit_of_measure', function ($attribute, $value, $parameters) {
            $value = strtoupper($value);
            return in_array($value, ['PIECES', 'GRAM', 'LITTER', 'KG']);
        });

        Validator::extend('reference_check', function ($attribute, $value, $parameters) {
            if (empty($value)) return true;
            $tableName = array_shift($parameters);
            $conditions = [];
            foreach ($parameters as $key => $parameter) {
                if ($key == 0) {
                    $conditions[$parameter] = $value;
                } else {
                    $conditions[$parameter] = request()->get($parameter);
                }
            }
            return DB::table($tableName)->where($conditions)->whereNull('deleted_at')->exists();
        });

        Validator::replacer('reference_check', function ($message, $attribute, $rule, $parameters) {
            array_splice($parameters, 0, 2);
            return str_replace(':other', implode(', ', $parameters), $message);
        });

        /**
         * reference_check_plant_code:ecns,code,vehicle_colors
         */
        Validator::extend('reference_check_plant_code', function ($attribute, $value, $parameters) {
            if (empty($value)) return true;
            $id = Route::getCurrentRoute()->parameter('id');
            $tableName = array_shift($parameters); // ecns
            $firstColumn = array_shift($parameters); // code
            $tableJoin = array_shift($parameters); // vehicle_colors
            $conditions = [
                $tableName. '.' . $firstColumn => $value,
                $tableJoin.'.id' => $id
            ];
            foreach ($parameters as $key => $parameter) {
                $conditions[$tableName.'.'.$parameter] = request()->get($parameter);
            }
            return DB::table($tableName)
                ->join($tableJoin, $tableJoin.'.plant_code', '=', $tableName.'.plant_code')
                ->where($conditions)
                ->whereNull($tableName.'.deleted_at')
                ->exists();
        });

        Validator::replacer('reference_check_plant_code', function ($message, $attribute, $rule, $parameters) {
            array_splice($parameters, 0, 2);
            return str_replace(':other', implode(', ', $parameters), $message);
        });
    }
}
