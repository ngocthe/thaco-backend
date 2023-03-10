<?php

namespace App\Services;

use App\Helpers\LockTableHelper;
use App\Models\Admin;
use App\Models\Bom;
use App\Models\BoxType;
use App\Models\BwhInventoryLog;
use App\Models\BwhOrderRequest;
use App\Models\Ecn;
use App\Models\InTransitInventoryLog;
use App\Models\LogicalInventoryMscAdjustment;
use App\Models\LogicalInventoryPartAdjustment;
use App\Models\MrpOrderCalendar;
use App\Models\Msc;
use App\Models\OrderList;
use App\Models\OrderPointControl;
use App\Models\Part;
use App\Models\PartColor;
use App\Models\PartGroup;
use App\Models\PartUsageResult;
use App\Models\Plant;
use App\Models\PlantInventoryLog;
use App\Models\Procurement;
use App\Models\Remark;
use App\Models\Supplier;
use App\Models\UpkwhInventoryLog;
use App\Models\VehicleColor;
use App\Models\VietnamSourceLog;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\WarehouseSummaryAdjustment;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class RemarkService
{
    protected const _CLASS = [
        'part_group' => PartGroup::class,
        'plant' => Plant::class,
        'vehicle_color' => VehicleColor::class,
        'msc' => Msc::class,
        'part' => Part::class,
        'user' => Admin::class,
        'part_color' => PartColor::class,
        'ecn' => Ecn::class,
        'bom' => Bom::class,
        'supplier' => Supplier::class,
        'procurement' => Procurement::class,
        'warehouse' => Warehouse::class,
        'warehouse_location' => WarehouseLocation::class,
        'box_type' => BoxType::class,
        'vietnam_source_log' => VietnamSourceLog::class,
        'in_transit_inventory_log' => InTransitInventoryLog::class,
        'bwh_inventory_log' => BwhInventoryLog::class,
        'plant_wh_inventory_log' => PlantInventoryLog::class,
        'upkwh_inventory_log' => UpkwhInventoryLog::class,
        'order_point_control' => OrderPointControl::class,
        'bwh_order_request' => BwhOrderRequest::class,
        'wh_adjustment_summary' => WarehouseSummaryAdjustment::class,
        'wh_adjustment_logical_part' => LogicalInventoryPartAdjustment::class,
        'wh_adjustment_logical_msc' => LogicalInventoryMscAdjustment::class,
        'mrp_ordering_calendar' => MrpOrderCalendar::class,
        'order_list' => OrderList::class,
        'part_usage_result' => PartUsageResult::class
    ];

    protected const MASTER_DATA = [
        'part_group', 'plant', 'vehicle_color', 'msc', 'part', 'user', 'part_color', 'ecn',
        'bom', 'supplier', 'procurement', 'warehouse', 'warehouse_location', 'box_type'
    ];

    /**
     * @param $data
     * @param $request
     * @return Remark|Builder|Model
     * @throws Exception
     */
    public function create($data, $request)
    {
        if (in_array($data['modelable_type'], self::MASTER_DATA)) {
            LockTableHelper::checkLockTime($request);
        }
        $class = self::_CLASS[$data['modelable_type']];
        return Remark::query()->create(
            [
                'modelable_type' => $class,
                'modelable_id' => $data['modelable_id'],
                'content' => $data['remark']
            ]
        );
    }

}
