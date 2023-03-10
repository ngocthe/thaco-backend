<?php

use App\Http\Controllers\Admin\AuthController;
use \App\Constants\Permission;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function () {
    return response()->json(['status' => 'OK']);
});

Route::prefix('api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('admin.login');
        Route::post('refresh-token', [AuthController::class, 'refreshToken'])->name('admin.refresh_token');
    });

    Route::middleware(['auth:api_admin', 'lock:api_admin'])->group(function () {
        Route::delete('logout', [AuthController::class, 'logout'])->name('admin.logout')->withoutMiddleware('lock:api_admin');
        Route::get('profile', [AuthController::class, 'profile'])->name('admin.profile');
        Route::post('change-password', 'AdminController@changePass');

        Route::prefix('admins')->group(function () {
            Route::get('/', 'AdminController@index')->middleware('can:' . Permission::ADMIN_LIST);
            Route::get('/export', 'AdminController@export');
            Route::post('/', 'AdminController@store')->middleware('can:' . Permission::ADMIN_CREATE);
            Route::get('/{id}', 'AdminController@show')->middleware('can:' . Permission::ADMIN_VIEW);
            Route::put('/{id}', 'AdminController@update')->middleware('can:' . Permission::ADMIN_EDIT);
            Route::delete('/{id}', 'AdminController@destroy')->middleware('can:' . Permission::ADMIN_DELETE);
        });

        Route::prefix('roles')->group(function () {
            Route::get('/', 'RoleController@index');
        });

        Route::prefix('part-groups')->group(function () {
            Route::get('/', 'PartGroupController@index')->middleware('can:' . Permission::PART_GROUP_LIST);
            Route::post('/', 'PartGroupController@store')->middleware('can:' . Permission::PART_GROUP_CREATE);
            Route::get('/codes', 'PartGroupController@searchCodes');
            Route::get('/export', 'PartGroupController@export');
            Route::get('/entity/{code}', 'PartGroupController@getPartGroupByCode');
            Route::get('/{id}', 'PartGroupController@show')->middleware('can:' . Permission::PART_GROUP_VIEW);
            Route::put('/{id}', 'PartGroupController@update')->middleware('can:' . Permission::PART_GROUP_EDIT);
            Route::delete('/{id}', 'PartGroupController@destroy')->middleware('can:' . Permission::PART_GROUP_DELETE);
        });

        Route::prefix('plants')->group(function () {
            Route::get('/', 'PlantController@index')->middleware('can:' . Permission::PLANT_LIST);
            Route::post('/', 'PlantController@store')->middleware('can:' . Permission::PLANT_CREATE);
            Route::get('/codes', 'PlantController@searchCodes');
            Route::get('/export', 'PlantController@export');
            Route::get('/{id}', 'PlantController@show')->middleware('can:' . Permission::PLANT_VIEW);
            Route::put('/{id}', 'PlantController@update')->middleware('can:' . Permission::PLANT_EDIT);
            Route::delete('/{id}', 'PlantController@destroy')->middleware('can:' . Permission::PLANT_DELETE);
        });

        Route::prefix('ecns')->group(function () {
            Route::get('/', 'EcnController@index')->middleware('can:' . Permission::ECN_LIST);
            Route::post('/', 'EcnController@store')->middleware('can:' . Permission::ECN_CREATE);
            Route::get('/codes', 'EcnController@searchCodes');
            Route::get('/columns', 'EcnController@columns');
            Route::get('/export', 'EcnController@export');
            Route::get('/{id}', 'EcnController@show')->middleware('can:' . Permission::ECN_VIEW);
            Route::put('/{id}', 'EcnController@update')->middleware('can:' . Permission::ECN_EDIT);
            Route::delete('/{id}', 'EcnController@destroy')->middleware('can:' . Permission::ECN_DELETE);
        });

        Route::prefix('vehicle-colors')->group(function () {
            Route::get('/', 'VehicleColorController@index')->middleware('can:' . Permission::VEHICLE_COLOR_LIST);
            Route::post('/', 'VehicleColorController@store')->middleware('can:' . Permission::VEHICLE_COLOR_CREATE);
            Route::get('/codes', 'VehicleColorController@searchCodes');
            Route::get('/columns', 'VehicleColorController@columns');
            Route::get('/export', 'VehicleColorController@export');
            Route::get('/{id}', 'VehicleColorController@show')->middleware('can:' . Permission::VEHICLE_COLOR_VIEW);
            Route::put('/{id}', 'VehicleColorController@update')->middleware('can:' . Permission::VEHICLE_COLOR_EDIT);
            Route::delete('/{id}', 'VehicleColorController@destroy')->middleware('can:' . Permission::VEHICLE_COLOR_DELETE);
        });

        Route::prefix('mscs')->group(function () {
            Route::get('/', 'MscController@index')->middleware('can:' . Permission::MSC_LIST);
            Route::post('/', 'MscController@store')->middleware('can:' . Permission::MSC_CREATE);
            Route::get('/codes', 'MscController@searchCodes');
            Route::get('/columns', 'MscController@columns');
            Route::get('/export', 'MscController@export');
            Route::get('/{id}', 'MscController@show')->middleware('can:' . Permission::MSC_VIEW);
            Route::put('/{id}', 'MscController@update')->middleware('can:' . Permission::MSC_EDIT);
            Route::delete('/{id}', 'MscController@destroy')->middleware('can:' . Permission::MSC_DELETE);
        });

        Route::prefix('parts')->group(function () {
            Route::get('/', 'PartController@index')->middleware('can:' . Permission::PART_LIST);
            Route::post('/', 'PartController@store')->middleware('can:' . Permission::PART_CREATE);
            Route::get('/codes', 'PartController@searchCodes');
            Route::get('/columns', 'PartController@columns');
            Route::get('/export', 'PartController@export');
            Route::get('/{id}', 'PartController@show')->middleware('can:' . Permission::PART_VIEW);
            Route::put('/{id}', 'PartController@update')->middleware('can:' . Permission::PART_EDIT);
            Route::delete('/{id}', 'PartController@destroy')->middleware('can:' . Permission::PART_DELETE);
        });

        Route::prefix('part-colors')->group(function () {
            Route::get('/', 'PartColorController@index')->middleware('can:' . Permission::PART_COLOR_LIST);
            Route::post('/', 'PartColorController@store')->middleware('can:' . Permission::PART_COLOR_CREATE);
            Route::get('/codes', 'PartColorController@searchCodes');
            Route::get('/columns', 'PartColorController@columns');
            Route::get('/export', 'PartColorController@export');
            Route::get('/{id}', 'PartColorController@show')->middleware('can:' . Permission::PART_COLOR_VIEW);
            Route::put('/{id}', 'PartColorController@update')->middleware('can:' . Permission::PART_COLOR_EDIT);
            Route::delete('/{id}', 'PartColorController@destroy')->middleware('can:' . Permission::PART_COLOR_DELETE);
        });

        Route::prefix('boms')->group(function () {
            Route::get('/', 'BomController@index')->middleware('can:' . Permission::BOM_LIST);
            Route::post('/', 'BomController@store')->middleware('can:' . Permission::BOM_CREATE);
            Route::get('/columns', 'BomController@columns');
            Route::get('/export', 'BomController@export');
            Route::get('/{id}', 'BomController@show')->middleware('can:' . Permission::BOM_VIEW);
            Route::put('/{id}', 'BomController@update')->middleware('can:' . Permission::BOM_EDIT);
            Route::delete('/{id}', 'BomController@destroy')->middleware('can:' . Permission::BOM_DELETE);
        });

        Route::prefix('suppliers')->group(function () {
            Route::get('/', 'SupplierController@index')->middleware('can:' . Permission::SUPPLIER_LIST);
            Route::post('/', 'SupplierController@store')->middleware('can:' . Permission::SUPPLIER_CREATE);
            Route::get('/codes', 'SupplierController@searchCodes');
            Route::get('/export', 'SupplierController@export');
            Route::get('/{id}', 'SupplierController@show')->middleware('can:' . Permission::SUPPLIER_VIEW);
            Route::put('/{id}', 'SupplierController@update')->middleware('can:' . Permission::SUPPLIER_EDIT);
            Route::delete('/{id}', 'SupplierController@destroy')->middleware('can:' . Permission::SUPPLIER_DELETE);
        });

        Route::prefix('procurements')->group(function () {
            Route::get('/', 'ProcurementController@index')->middleware('can:' . Permission::PROCUREMENT_LIST);
            Route::post('/', 'ProcurementController@store')->middleware('can:' . Permission::PROCUREMENT_CREATE);
            Route::get('/columns', 'ProcurementController@columns');
            Route::get('/export', 'ProcurementController@export');
            Route::get('/{id}', 'ProcurementController@show')->middleware('can:' . Permission::PROCUREMENT_VIEW);
            Route::put('/{id}', 'ProcurementController@update')->middleware('can:' . Permission::PROCUREMENT_EDIT);
            Route::delete('/{id}', 'ProcurementController@destroy')->middleware('can:' . Permission::PROCUREMENT_DELETE);
        });

        Route::prefix('warehouses')->group(function () {
            Route::get('/', 'WarehouseController@index')->middleware('can:' . Permission::WAREHOUSE_LIST);
            Route::post('/', 'WarehouseController@store')->middleware('can:' . Permission::WAREHOUSE_CREATE);
            Route::get('/codes', 'WarehouseController@searchCodes');
            Route::get('/export', 'WarehouseController@export');
            Route::get('/{id}', 'WarehouseController@show')->middleware('can:' . Permission::WAREHOUSE_VIEW);
            Route::put('/{id}', 'WarehouseController@update')->middleware('can:' . Permission::WAREHOUSE_EDIT);
            Route::delete('/{id}', 'WarehouseController@destroy')->middleware('can:' . Permission::WAREHOUSE_DELETE);
        });

        Route::prefix('warehouse-locations')->group(function () {
            Route::get('/', 'WarehouseLocationController@index')->middleware('can:' . Permission::WAREHOUSE_LOCATION_LIST);
            Route::post('/', 'WarehouseLocationController@store')->middleware('can:' . Permission::WAREHOUSE_LOCATION_CREATE);
            Route::get('/codes', 'WarehouseLocationController@searchCodes');
            Route::get('/export', 'WarehouseLocationController@export');
            Route::get('/{id}', 'WarehouseLocationController@show')->middleware('can:' . Permission::WAREHOUSE_LOCATION_VIEW);
            Route::put('/{id}', 'WarehouseLocationController@update')->middleware('can:' . Permission::WAREHOUSE_LOCATION_EDIT);
            Route::delete('/{id}', 'WarehouseLocationController@destroy')->middleware('can:' . Permission::WAREHOUSE_LOCATION_DELETE);
        });

        Route::prefix('box-types')->group(function () {
            Route::get('/', 'BoxTypeController@index')->middleware('can:' . Permission::BOX_TYPE_LIST);
            Route::post('/', 'BoxTypeController@store')->middleware('can:' . Permission::BOX_TYPE_CREATE);
            Route::get('/codes', 'BoxTypeController@searchCodes');
            Route::get('/export', 'BoxTypeController@export');
            Route::get('/{id}', 'BoxTypeController@show')->middleware('can:' . Permission::BOX_TYPE_VIEW);
            Route::put('/{id}', 'BoxTypeController@update')->middleware('can:' . Permission::BOX_TYPE_EDIT);
            Route::delete('/{id}', 'BoxTypeController@destroy')->middleware('can:' . Permission::BOX_TYPE_DELETE);
        });

        Route::prefix('data-imports')->group(function () {
            Route::get('/templates', 'DataImportController@index');
            Route::post('/master', 'DataImportController@master')->middleware(['permission.import']);
            Route::post('/inventory', 'DataImportController@inventory')->middleware(['permission.import'])->withoutMiddleware('lock:api_admin');
            Route::post('/mrp', 'DataImportController@mrp')->middleware(['permission.import'])->withoutMiddleware('lock:api_admin');
        });

        Route::prefix('settings')->group(function () {
            Route::get('/', 'SettingController@index')->middleware('can:' . Permission::SETTING_LIST);
            Route::post('/', 'SettingController@store')->withoutMiddleware('lock:api_admin')->middleware('can:' . Permission::SETTING_CREATE);
        });
    });

    Route::middleware(['auth:api_admin'])->group(function () {

        Route::prefix('remarks')->group(function () {
            Route::post('/', 'RemarkController@store')->middleware('can:' . Permission::REMARK_CREATE);
        });

        Route::prefix('order-point-controls')->group(function () {
            Route::get('/', 'OrderPointControlController@index')->middleware('can:' . Permission::ORDER_POINT_CONTROL_LIST);
            Route::post('/', 'OrderPointControlController@store')->middleware('can:' . Permission::ORDER_POINT_CONTROL_CREATE);
            Route::get('/export', 'OrderPointControlController@export')->middleware('can:' . Permission::ORDER_POINT_CONTROL_LIST);
            Route::get('/{id}', 'OrderPointControlController@show')->middleware('can:' . Permission::ORDER_POINT_CONTROL_VIEW);
            Route::put('/{id}', 'OrderPointControlController@update')->middleware('can:' . Permission::ORDER_POINT_CONTROL_EDIT);
            Route::delete('/{id}', 'OrderPointControlController@destroy')->middleware('can:' . Permission::ORDER_POINT_CONTROL_DELETE);
        });

        Route::prefix('mrp-week-definitions')->group(function () {
            Route::get('/', 'MrpWeekDefinitionController@index')->middleware('can:' . Permission::MRP_WEEK_DEFINITION_LIST);
            Route::post('/', 'MrpWeekDefinitionController@store')->middleware('can:' . Permission::MRP_WEEK_DEFINITION_CREATE);
        });

        Route::prefix('part-usage-results')->group(function () {
            Route::get('/', 'PartUsageResultController@index')->middleware('can:' . Permission::PART_USAGE_RESULT_LIST);
            Route::post('/', 'PartUsageResultController@store')->middleware('can:' . Permission::PART_USAGE_RESULT_CREATE);
            Route::get('/columns', 'PartUsageResultController@columns')->middleware('can:' . Permission::PART_USAGE_RESULT_LIST);
            Route::get('/export', 'PartUsageResultController@export')->middleware('can:' . Permission::PART_USAGE_RESULT_LIST);
            Route::get('/{id}', 'PartUsageResultController@show')->middleware('can:' . Permission::PART_USAGE_RESULT_VIEW);
            Route::put('/{id}', 'PartUsageResultController@update')->middleware('can:' . Permission::PART_USAGE_RESULT_EDIT);
            Route::delete('/{id}', 'PartUsageResultController@destroy')->middleware('can:' . Permission::PART_USAGE_RESULT_DELETE);
        });

        Route::prefix('production-plans')->group(function () {
            Route::get('/', 'ProductionPlanController@index')->middleware('can:' . Permission::PRODUCTION_PLAN_LIST);
            Route::get('/columns', 'ProductionPlanController@columns')->middleware('can:' . Permission::PRODUCTION_PLAN_LIST);
            Route::get('/export', 'ProductionPlanController@export')->middleware('can:' . Permission::PRODUCTION_PLAN_LIST);
            Route::get('/import-files', 'ProductionPlanController@importFiles')->middleware('can:' . Permission::PRODUCTION_PLAN_LIST);
            Route::get('/import-files/{id}', 'ProductionPlanController@importFileDetail')->middleware('can:' . Permission::PRODUCTION_PLAN_LIST);
        });

        Route::prefix('order-calendars')->group(function () {
            Route::get('/', 'OrderCalendarController@index')->middleware('can:' . Permission::ORDER_CALENDAR_LIST);
            Route::post('/', 'OrderCalendarController@store')->middleware('can:' . Permission::ORDER_CALENDAR_CREATE);
            Route::get('/{id}', 'OrderCalendarController@show')->middleware('can:' . Permission::ORDER_CALENDAR_VIEW);
            Route::put('/{id}', 'OrderCalendarController@update')->middleware('can:' . Permission::ORDER_CALENDAR_EDIT);
            Route::delete('/{id}', 'OrderCalendarController@destroy')->middleware('can:' . Permission::ORDER_CALENDAR_DELETE);
        });

        Route::prefix('in-transit-inventory-logs')->group(function () {
            Route::get('/', 'InTransitInventoryLogController@index')->middleware('can:' . Permission::IN_TRANSIT_INVENTORY_LOG_LIST);
            Route::post('/', 'InTransitInventoryLogController@store')->middleware('can:' . Permission::IN_TRANSIT_INVENTORY_LOG_CREATE);
            Route::get('/columns', 'InTransitInventoryLogController@columns')->middleware('can:' . Permission::IN_TRANSIT_INVENTORY_LOG_LIST);
            Route::get('/export', 'InTransitInventoryLogController@export')->middleware('can:' . Permission::IN_TRANSIT_INVENTORY_LOG_LIST);
            Route::get('/{id}', 'InTransitInventoryLogController@show')->middleware('can:' . Permission::IN_TRANSIT_INVENTORY_LOG_VIEW);
            Route::put('/{id}', 'InTransitInventoryLogController@update')->middleware('can:' . Permission::IN_TRANSIT_INVENTORY_LOG_EDIT);
            Route::delete('/{id}', 'InTransitInventoryLogController@destroy')->middleware('can:' . Permission::IN_TRANSIT_INVENTORY_LOG_DELETE);
        });

        Route::prefix('bwh-inventory-logs')->group(function () {
            Route::get('/', 'BwhInventoryLogController@index')->middleware('can:' . Permission::BWH_INVENTORY_LOG_LIST);
            Route::post('/', 'BwhInventoryLogController@store')->middleware('can:' . Permission::BWH_INVENTORY_LOG_CREATE);
            Route::get('/columns', 'BwhInventoryLogController@columns')->middleware('can:' . Permission::BWH_INVENTORY_LOG_LIST);
            Route::get('/parts', 'BwhInventoryLogController@parts')->middleware('can:' . Permission::BWH_INVENTORY_LOG_LIST);
            Route::get('/cases', 'BwhInventoryLogController@cases')->middleware('can:' . Permission::BWH_INVENTORY_LOG_LIST);
            Route::get('/part-colors', 'BwhInventoryLogController@partColors')->middleware('can:' . Permission::BWH_INVENTORY_LOG_LIST);
            Route::get('/export', 'BwhInventoryLogController@export')->middleware('can:' . Permission::BWH_INVENTORY_LOG_LIST);
            Route::get('/{id}', 'BwhInventoryLogController@show')->middleware('can:' . Permission::BWH_INVENTORY_LOG_VIEW);
            Route::put('/{id}', 'BwhInventoryLogController@update')->middleware('can:' . Permission::BWH_INVENTORY_LOG_EDIT);
            Route::delete('/{id}', 'BwhInventoryLogController@destroy')->middleware('can:' . Permission::BWH_INVENTORY_LOG_DELETE);
        });

        Route::prefix('upkwh-inventory-logs')->group(function () {
            Route::get('/', 'UpkwhInventoryLogController@index')->middleware('can:' . Permission::UPKWH_INVENTORY_LOG_LIST);
            Route::post('/', 'UpkwhInventoryLogController@store')->middleware('can:' . Permission::UPKWH_INVENTORY_LOG_CREATE);
            Route::get('/columns', 'UpkwhInventoryLogController@columns')->middleware('can:' . Permission::UPKWH_INVENTORY_LOG_LIST);
            Route::get('/export', 'UpkwhInventoryLogController@export')->middleware('can:' . Permission::UPKWH_INVENTORY_LOG_LIST);
            Route::get('/{id}', 'UpkwhInventoryLogController@show')->middleware('can:' . Permission::UPKWH_INVENTORY_LOG_VIEW);
            Route::put('/{id}', 'UpkwhInventoryLogController@update')->middleware('can:' . Permission::UPKWH_INVENTORY_LOG_EDIT);
            Route::delete('/{id}', 'UpkwhInventoryLogController@destroy')->middleware('can:' . Permission::UPKWH_INVENTORY_LOG_DELETE);
            Route::put('/{id}/defects', 'UpkwhInventoryLogController@defects')->middleware('can:' . Permission::UPKWH_INVENTORY_LOG_CREATE);
        });

        Route::prefix('plant-inventory-logs')->group(function () {
            Route::get('/', 'PlantInventoryLogController@index')->middleware('can:' . Permission::PLANT_INVENTORY_LOG_LIST);
            Route::post('/', 'PlantInventoryLogController@store')->middleware('can:' . Permission::PLANT_INVENTORY_LOG_CREATE);
            Route::get('/columns', 'PlantInventoryLogController@columns')->middleware('can:' . Permission::PLANT_INVENTORY_LOG_LIST);
            Route::get('/export', 'PlantInventoryLogController@export')->middleware('can:' . Permission::PLANT_INVENTORY_LOG_LIST);
            Route::get('/{id}', 'PlantInventoryLogController@show')->middleware('can:' . Permission::PLANT_INVENTORY_LOG_VIEW);
            Route::put('/{id}', 'PlantInventoryLogController@update')->middleware('can:' . Permission::PLANT_INVENTORY_LOG_EDIT);
            Route::delete('/{id}', 'PlantInventoryLogController@destroy')->middleware('can:' . Permission::PLANT_INVENTORY_LOG_DELETE);
            Route::put('/{id}/defects', 'PlantInventoryLogController@defects')->middleware('can:' . Permission::PLANT_INVENTORY_LOG_CREATE);
        });

        Route::prefix('warehouse-inventory-summaries')->group(function () {
            Route::get('/', 'WarehouseInventorySummaryController@index')->middleware('can:' . Permission::WH_INVENTORY_SUMMARY_LIST);
            Route::get('/parts', 'WarehouseInventorySummaryController@parts')->middleware('can:' . Permission::WH_INVENTORY_SUMMARY_LIST);
            Route::get('/columns', 'WarehouseInventorySummaryController@columns')->middleware('can:' . Permission::WH_INVENTORY_SUMMARY_LIST);
            Route::get('/export', 'WarehouseInventorySummaryController@export')->middleware('can:' . Permission::WH_INVENTORY_SUMMARY_LIST);
            Route::get('/parts/export', 'WarehouseInventorySummaryController@partExport')->middleware('can:' . Permission::WH_INVENTORY_SUMMARY_LIST);
            Route::get('/{id}', 'WarehouseInventorySummaryController@show')->middleware('can:' . Permission::WH_INVENTORY_SUMMARY_VIEW);
        });

        Route::prefix('bwh-order-requests')->group(function () {
            Route::get('/', 'BwhOrderRequestController@index')->middleware('can:' . Permission::BWH_ORDER_REQUEST_LIST);
            Route::post('/', 'BwhOrderRequestController@store')->middleware('can:' . Permission::BWH_ORDER_REQUEST_CREATE);
            Route::get('/columns', 'BwhOrderRequestController@columns')->middleware('can:' . Permission::BWH_ORDER_REQUEST_LIST);
            Route::get('/export', 'BwhOrderRequestController@export')->middleware('can:' . Permission::BWH_ORDER_REQUEST_LIST);
            Route::get('/{id}', 'BwhOrderRequestController@show')->middleware('can:' . Permission::BWH_ORDER_REQUEST_LIST);
            Route::post('/{id}/confirm', 'BwhOrderRequestController@confirm')->middleware('can:' . Permission::BWH_ORDER_REQUEST_EDIT);
        });

        Route::prefix('warehouse-summary-adjustments')->group(function () {
            Route::get('/', 'WarehouseSummaryAdjustmentController@index')->middleware('can:' . Permission::WAREHOUSE_SUMMARY_ADJUSTMENT_LIST);
            Route::post('/', 'WarehouseSummaryAdjustmentController@store')->middleware('can:' . Permission::WAREHOUSE_SUMMARY_ADJUSTMENT_CREATE);
            Route::get('/columns', 'WarehouseSummaryAdjustmentController@columns')->middleware('can:' . Permission::WAREHOUSE_SUMMARY_ADJUSTMENT_LIST);
            Route::get('/export', 'WarehouseSummaryAdjustmentController@export')->middleware('can:' . Permission::WAREHOUSE_SUMMARY_ADJUSTMENT_LIST);
            Route::get('/{id}', 'WarehouseSummaryAdjustmentController@show')->middleware('can:' . Permission::WAREHOUSE_SUMMARY_ADJUSTMENT_VIEW);
        });

        Route::prefix('logical-inventory-part-adjustments')->group(function () {
            Route::get('/', 'LogicalInventoryPartAdjustmentController@index')->middleware('can:' . Permission::LOGICAL_INVENTORY_PART_ADJUSTMENT_LIST);
            Route::post('/', 'LogicalInventoryPartAdjustmentController@store')->middleware('can:' . Permission::LOGICAL_INVENTORY_PART_ADJUSTMENT_CREATE);
            Route::get('/columns', 'LogicalInventoryPartAdjustmentController@columns')->middleware('can:' . Permission::LOGICAL_INVENTORY_PART_ADJUSTMENT_LIST);
            Route::get('/export', 'LogicalInventoryPartAdjustmentController@export')->middleware('can:' . Permission::LOGICAL_INVENTORY_PART_ADJUSTMENT_LIST);
            Route::get('/{id}', 'LogicalInventoryPartAdjustmentController@show')->middleware('can:' . Permission::LOGICAL_INVENTORY_PART_ADJUSTMENT_VIEW);
        });

        Route::prefix('logical-inventory-msc-adjustments')->group(function () {
            Route::get('/', 'LogicalInventoryMscAdjustmentController@index')->middleware('can:' . Permission::LOGICAL_INVENTORY_MSC_ADJUSTMENT_LIST);
            Route::post('/', 'LogicalInventoryMscAdjustmentController@store')->middleware('can:' . Permission::LOGICAL_INVENTORY_MSC_ADJUSTMENT_CREATE);
            Route::get('/columns', 'LogicalInventoryMscAdjustmentController@columns')->middleware('can:' . Permission::LOGICAL_INVENTORY_MSC_ADJUSTMENT_LIST);
            Route::get('/export', 'LogicalInventoryMscAdjustmentController@export')->middleware('can:' . Permission::LOGICAL_INVENTORY_MSC_ADJUSTMENT_LIST);
            Route::get('/{id}', 'LogicalInventoryMscAdjustmentController@show')->middleware('can:' . Permission::LOGICAL_INVENTORY_MSC_ADJUSTMENT_VIEW);
        });

        Route::prefix('defect-inventory')->group(function () {
            Route::get('/', 'DefectInventoryController@index')->middleware('can:' . Permission::DEFECT_INVENTORY_LIST);
            Route::post('/', 'DefectInventoryController@store')->middleware('can:' . Permission::DEFECT_INVENTORY_CREATE);
            Route::get('/{id}', 'DefectInventoryController@show')->middleware('can:' . Permission::DEFECT_INVENTORY_VIEW);
            Route::put('/{id}', 'DefectInventoryController@update')->middleware('can:' . Permission::DEFECT_INVENTORY_EDIT);
            Route::delete('/{id}', 'DefectInventoryController@destroy')->middleware('can:' . Permission::DEFECT_INVENTORY_DELETE);
        });

        Route::prefix('mrp-results')->group(function () {
            Route::get('/part', 'MrpResultController@part')->middleware('can:' . Permission::MRP_RESULT_LIST);
            Route::get('/msc', 'MrpResultController@msc')->middleware('can:' . Permission::MRP_RESULT_LIST);
            Route::get('/columns', 'MrpResultController@columns')->middleware('can:' . Permission::MRP_RESULT_LIST);
            Route::get('/part/export', 'MrpResultController@exportByPart')->middleware('can:' . Permission::MRP_RESULT_LIST);
            Route::get('/msc/export', 'MrpResultController@exportByMsc')->middleware('can:' . Permission::MRP_RESULT_LIST);
            Route::post('/system-run', 'MrpResultController@systemRun')->middleware('can:' . Permission::MRP_RESULT_SYSTEM_RUN);
        });

        Route::prefix('logical-inventory')->group(function () {
            Route::get('/', 'LogicalInventoryController@index')->middleware('can:' . Permission::LOGICAL_INVENTORY_LIST);
            Route::get('/forecast', 'LogicalInventoryController@forecastInventory')->middleware('can:' . Permission::LOGICAL_INVENTORY_LIST);
            Route::get('/columns', 'LogicalInventoryController@columns')->middleware('can:' . Permission::LOGICAL_INVENTORY_LIST);
            Route::get('/export', 'LogicalInventoryController@export')->middleware('can:' . Permission::LOGICAL_INVENTORY_LIST);
        });

        Route::prefix('shortage-parts')->group(function () {
            Route::get('/', 'ShortagePartController@index')->middleware('can:' . Permission::SHORTAGE_PART_LIST);
            Route::get('/columns', 'ShortagePartController@columns')->middleware('can:' . Permission::SHORTAGE_PART_LIST);
            Route::get('/export', 'ShortagePartController@export')->middleware('can:' . Permission::SHORTAGE_PART_LIST);
            Route::post('/remarks', 'ShortagePartController@remarks')->middleware('can:' . Permission::SHORTAGE_PART_LIST);
            Route::post('/simulation-run', 'ShortagePartController@simulationRun')->middleware('can:' . Permission::SHORTAGE_PART_SIMULATION_RUN);
        });

        Route::prefix('order-list')->group(function () {
            Route::get('/', 'OrderListController@index')->middleware('can:' . Permission::ORDER_LIST_LIST);
            Route::get('/delivering', 'OrderListController@delivering')->middleware('can:' . Permission::ORDER_LIST_LIST);
            Route::post('/', 'OrderListController@store')->middleware('can:' . Permission::ORDER_LIST_CREATE);
            Route::post('/check-shortage-part', 'OrderListController@checkShortagePart')->middleware('can:' . Permission::ORDER_LIST_SYSTEM_RUN);
            Route::post('/system-run', 'OrderListController@systemRun')->middleware('can:' . Permission::ORDER_LIST_SYSTEM_RUN);
            Route::get('/columns', 'OrderListController@columns')->middleware('can:' . Permission::ORDER_LIST_LIST);
            Route::get('/export', 'OrderListController@export')->middleware('can:' . Permission::ORDER_LIST_LIST);
            Route::get('/export-delivering', 'OrderListController@exportDelivering')->middleware('can:' . Permission::ORDER_LIST_LIST);
            Route::get('/{id}', 'OrderListController@show')->middleware('can:' . Permission::ORDER_LIST_VIEW);
            Route::put('release', 'OrderListController@release')->middleware('can:' . Permission::ORDER_LIST_EDIT);
            Route::put('/{id}', 'OrderListController@update')->middleware('can:' . Permission::ORDER_LIST_EDIT);
            Route::delete('/{id}', 'OrderListController@destroy')->middleware('can:' . Permission::ORDER_LIST_DELETE);
        });

        Route::prefix('mrp-order-calendars')->group(function () {
            Route::get('/', 'MrpOrderCalendarController@index')->middleware('can:' . Permission::MRP_ORDER_CALENDAR_LIST);
            Route::post('/', 'MrpOrderCalendarController@store')->middleware('can:' . Permission::MRP_ORDER_CALENDAR_CREATE);
            Route::get('/columns', 'MrpOrderCalendarController@columns')->middleware('can:' . Permission::MRP_ORDER_CALENDAR_LIST);
            Route::get('/export', 'MrpOrderCalendarController@export')->middleware('can:' . Permission::MRP_ORDER_CALENDAR_LIST);
            Route::get('/{id}', 'MrpOrderCalendarController@show')->middleware('can:' . Permission::MRP_ORDER_CALENDAR_VIEW);
            Route::put('/{id}', 'MrpOrderCalendarController@update')->middleware('can:' . Permission::MRP_ORDER_CALENDAR_EDIT);
            Route::delete('/{id}', 'MrpOrderCalendarController@destroy')->middleware('can:' . Permission::MRP_ORDER_CALENDAR_DELETE);
        });

        Route::prefix('vietnam-source-logs')->group(function () {
            Route::get('/columns', 'VietnamSourceLogController@columns')->middleware('can:' . Permission::VIETNAM_SOURCE_LOG_LIST);
            Route::get('/export', 'VietnamSourceLogController@export')->middleware('can:' . Permission::VIETNAM_SOURCE_LOG_LIST);
            Route::get('/', 'VietnamSourceLogController@index')->middleware('can:' . Permission::VIETNAM_SOURCE_LOG_LIST);
            Route::post('/', 'VietnamSourceLogController@store')->middleware('can:' . Permission::VIETNAM_SOURCE_LOG_CREATE);
            Route::get('/{id}', 'VietnamSourceLogController@show')->middleware('can:' . Permission::VIETNAM_SOURCE_LOG_VIEW);
            Route::put('/{id}', 'VietnamSourceLogController@update')->middleware('can:' . Permission::VIETNAM_SOURCE_LOG_EDIT);
            Route::delete('/{id}', 'VietnamSourceLogController@destroy')->middleware('can:' . Permission::VIETNAM_SOURCE_LOG_DELETE);
        });
    }); // end routes

    Route::prefix('webhooks')->middleware(['webhooks.token'])->group(function () {
        Route::post('bwh-order-requests/{id}/confirm', 'BwhOrderRequestController@confirm');
        Route::get('warehouses/detail', 'WarehouseController@detail');
        Route::get('warehouse-locations/detail', 'WarehouseLocationController@detail');
        Route::post('bwh-inventory-logs/shipped', 'BwhInventoryLogController@shipped');
        Route::get('bwh-order-requests', 'BwhInventoryLogController@orderRequests');
        Route::get('parts/codes', 'PartController@searchCodes');
        Route::get('parts/columns', 'PartController@columns');
        Route::get('part-colors/codes', 'PartColorController@searchCodes');
        Route::get('part-colors/columns', 'PartColorController@columns');
        Route::get('admins/{id}', 'AdminController@show');
        Route::post('admins', 'AdminController@store')->middleware('lock:api_admin');
        Route::get('admins', 'AdminController@index')->middleware('lock:api_admin');
        Route::put('admins/{id}', 'AdminController@update')->middleware('lock:api_admin');
        Route::delete('admins/{id}', 'AdminController@destroy')->middleware('lock:api_admin');
        Route::post('admins/change-password', 'AdminController@changePass')->middleware('lock:api_admin');
        Route::post('plant-inventory-logs', 'PlantInventoryLogController@store');
    });
});
