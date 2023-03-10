<?php


namespace App\Constants;


class Permission
{
    /** list const permissions */
	const PART_GROUP_LIST = 'partGroup.list';
	const PART_GROUP_CREATE = 'partGroup.create';
	const PART_GROUP_VIEW = 'partGroup.view';
	const PART_GROUP_EDIT = 'partGroup.edit';
	const PART_GROUP_DELETE = 'partGroup.delete';
	const PLANT_LIST = 'plant.list';
	const PLANT_CREATE = 'plant.create';
	const PLANT_VIEW = 'plant.view';
	const PLANT_EDIT = 'plant.edit';
	const PLANT_DELETE = 'plant.delete';
	const ECN_LIST = 'ecn.list';
	const ECN_CREATE = 'ecn.create';
	const ECN_VIEW = 'ecn.view';
	const ECN_EDIT = 'ecn.edit';
	const ECN_DELETE = 'ecn.delete';
	const VEHICLE_COLOR_LIST = 'vehicleColor.list';
	const VEHICLE_COLOR_CREATE = 'vehicleColor.create';
	const VEHICLE_COLOR_VIEW = 'vehicleColor.view';
	const VEHICLE_COLOR_EDIT = 'vehicleColor.edit';
	const VEHICLE_COLOR_DELETE = 'vehicleColor.delete';
	const MSC_LIST = 'msc.list';
	const MSC_CREATE = 'msc.create';
	const MSC_VIEW = 'msc.view';
	const MSC_EDIT = 'msc.edit';
	const MSC_DELETE = 'msc.delete';
	const PART_LIST = 'part.list';
	const PART_CREATE = 'part.create';
	const PART_VIEW = 'part.view';
	const PART_EDIT = 'part.edit';
	const PART_DELETE = 'part.delete';
	const PART_COLOR_LIST = 'partColor.list';
	const PART_COLOR_CREATE = 'partColor.create';
	const PART_COLOR_VIEW = 'partColor.view';
	const PART_COLOR_EDIT = 'partColor.edit';
	const PART_COLOR_DELETE = 'partColor.delete';
	const BOM_LIST = 'bom.list';
	const BOM_CREATE = 'bom.create';
	const BOM_VIEW = 'bom.view';
	const BOM_EDIT = 'bom.edit';
	const BOM_DELETE = 'bom.delete';
	const SUPPLIER_LIST = 'supplier.list';
	const SUPPLIER_CREATE = 'supplier.create';
	const SUPPLIER_VIEW = 'supplier.view';
	const SUPPLIER_EDIT = 'supplier.edit';
	const SUPPLIER_DELETE = 'supplier.delete';
	const PROCUREMENT_LIST = 'procurement.list';
	const PROCUREMENT_CREATE = 'procurement.create';
	const PROCUREMENT_VIEW = 'procurement.view';
	const PROCUREMENT_EDIT = 'procurement.edit';
	const PROCUREMENT_DELETE = 'procurement.delete';
	const WAREHOUSE_LIST = 'warehouse.list';
	const WAREHOUSE_CREATE = 'warehouse.create';
	const WAREHOUSE_VIEW = 'warehouse.view';
	const WAREHOUSE_EDIT = 'warehouse.edit';
	const WAREHOUSE_DELETE = 'warehouse.delete';
	const WAREHOUSE_LOCATION_LIST = 'warehouseLocation.list';
	const WAREHOUSE_LOCATION_CREATE = 'warehouseLocation.create';
	const WAREHOUSE_LOCATION_VIEW = 'warehouseLocation.view';
	const WAREHOUSE_LOCATION_EDIT = 'warehouseLocation.edit';
	const WAREHOUSE_LOCATION_DELETE = 'warehouseLocation.delete';
	const ORDER_POINT_CONTROL_LIST = 'orderPointControl.list';
	const ORDER_POINT_CONTROL_CREATE = 'orderPointControl.create';
	const ORDER_POINT_CONTROL_VIEW = 'orderPointControl.view';
	const ORDER_POINT_CONTROL_EDIT = 'orderPointControl.edit';
	const ORDER_POINT_CONTROL_DELETE = 'orderPointControl.delete';
	const BOX_TYPE_LIST = 'boxType.list';
	const BOX_TYPE_CREATE = 'boxType.create';
	const BOX_TYPE_VIEW = 'boxType.view';
	const BOX_TYPE_EDIT = 'boxType.edit';
	const BOX_TYPE_DELETE = 'boxType.delete';
	const MRP_WEEK_DEFINITION_LIST = 'mrpWeekDefinition.list';
	const MRP_WEEK_DEFINITION_CREATE = 'mrpWeekDefinition.create';
	const MRP_WEEK_DEFINITION_VIEW = 'mrpWeekDefinition.view';
	const MRP_WEEK_DEFINITION_EDIT = 'mrpWeekDefinition.edit';
	const MRP_WEEK_DEFINITION_DELETE = 'mrpWeekDefinition.delete';
	const PART_USAGE_RESULT_LIST = 'partUsageResult.list';
	const PART_USAGE_RESULT_CREATE = 'partUsageResult.create';
	const PART_USAGE_RESULT_VIEW = 'partUsageResult.view';
	const PART_USAGE_RESULT_EDIT = 'partUsageResult.edit';
	const PART_USAGE_RESULT_DELETE = 'partUsageResult.delete';
	const PRODUCTION_PLAN_LIST = 'productionPlan.list';
	const PRODUCTION_PLAN_CREATE = 'productionPlan.create';
	const PRODUCTION_PLAN_VIEW = 'productionPlan.view';
	const PRODUCTION_PLAN_EDIT = 'productionPlan.edit';
	const PRODUCTION_PLAN_DELETE = 'productionPlan.delete';
	const ORDER_CALENDAR_LIST = 'orderCalendar.list';
	const ORDER_CALENDAR_CREATE = 'orderCalendar.create';
	const ORDER_CALENDAR_VIEW = 'orderCalendar.view';
	const ORDER_CALENDAR_EDIT = 'orderCalendar.edit';
	const ORDER_CALENDAR_DELETE = 'orderCalendar.delete';
	const IN_TRANSIT_INVENTORY_LOG_LIST = 'inTransitInventoryLog.list';
	const IN_TRANSIT_INVENTORY_LOG_CREATE = 'inTransitInventoryLog.create';
	const IN_TRANSIT_INVENTORY_LOG_VIEW = 'inTransitInventoryLog.view';
	const IN_TRANSIT_INVENTORY_LOG_EDIT = 'inTransitInventoryLog.edit';
	const IN_TRANSIT_INVENTORY_LOG_DELETE = 'inTransitInventoryLog.delete';
	const ADMIN_LIST = 'admin.list';
	const ADMIN_CREATE = 'admin.create';
	const ADMIN_VIEW = 'admin.view';
	const ADMIN_EDIT = 'admin.edit';
	const ADMIN_DELETE = 'admin.delete';
	const BWH_INVENTORY_LOG_LIST = 'bwhInventoryLog.list';
	const BWH_INVENTORY_LOG_CREATE = 'bwhInventoryLog.create';
	const BWH_INVENTORY_LOG_VIEW = 'bwhInventoryLog.view';
	const BWH_INVENTORY_LOG_EDIT = 'bwhInventoryLog.edit';
	const BWH_INVENTORY_LOG_DELETE = 'bwhInventoryLog.delete';
	const UPKWH_INVENTORY_LOG_LIST = 'upkwhInventoryLog.list';
	const UPKWH_INVENTORY_LOG_CREATE = 'upkwhInventoryLog.create';
	const UPKWH_INVENTORY_LOG_VIEW = 'upkwhInventoryLog.view';
	const UPKWH_INVENTORY_LOG_EDIT = 'upkwhInventoryLog.edit';
	const UPKWH_INVENTORY_LOG_DELETE = 'upkwhInventoryLog.delete';
	const WH_INVENTORY_SUMMARY_LIST = 'whInventorySummary.list';
	const WH_INVENTORY_SUMMARY_CREATE = 'whInventorySummary.create';
	const WH_INVENTORY_SUMMARY_VIEW = 'whInventorySummary.view';
	const WH_INVENTORY_SUMMARY_EDIT = 'whInventorySummary.edit';
	const WH_INVENTORY_SUMMARY_DELETE = 'whInventorySummary.delete';
	const PLANT_INVENTORY_LOG_LIST = 'plantInventoryLog.list';
	const PLANT_INVENTORY_LOG_CREATE = 'plantInventoryLog.create';
	const PLANT_INVENTORY_LOG_VIEW = 'plantInventoryLog.view';
	const PLANT_INVENTORY_LOG_EDIT = 'plantInventoryLog.edit';
	const PLANT_INVENTORY_LOG_DELETE = 'plantInventoryLog.delete';
    const REMARK_CREATE = 'remark.create';
    const SETTING_LIST = 'setting.list';
    const SETTING_CREATE = 'setting.create';
	const BWH_ORDER_REQUEST_LIST = 'bwhOrderRequest.list';
	const BWH_ORDER_REQUEST_CREATE = 'bwhOrderRequest.create';
	const BWH_ORDER_REQUEST_VIEW = 'bwhOrderRequest.view';
	const BWH_ORDER_REQUEST_EDIT = 'bwhOrderRequest.edit';
	const BWH_ORDER_REQUEST_DELETE = 'bwhOrderRequest.delete';
	const WAREHOUSE_SUMMARY_ADJUSTMENT_LIST = 'warehouseSummaryAdjustment.list';
	const WAREHOUSE_SUMMARY_ADJUSTMENT_CREATE = 'warehouseSummaryAdjustment.create';
	const WAREHOUSE_SUMMARY_ADJUSTMENT_VIEW = 'warehouseSummaryAdjustment.view';
	const WAREHOUSE_SUMMARY_ADJUSTMENT_EDIT = 'warehouseSummaryAdjustment.edit';
	const WAREHOUSE_SUMMARY_ADJUSTMENT_DELETE = 'warehouseSummaryAdjustment.delete';
	const LOGICAL_INVENTORY_PART_ADJUSTMENT_LIST = 'logicalInventoryPartAdjustment.list';
	const LOGICAL_INVENTORY_PART_ADJUSTMENT_CREATE = 'logicalInventoryPartAdjustment.create';
	const LOGICAL_INVENTORY_PART_ADJUSTMENT_VIEW = 'logicalInventoryPartAdjustment.view';
	const LOGICAL_INVENTORY_PART_ADJUSTMENT_EDIT = 'logicalInventoryPartAdjustment.edit';
	const LOGICAL_INVENTORY_PART_ADJUSTMENT_DELETE = 'logicalInventoryPartAdjustment.delete';
	const LOGICAL_INVENTORY_MSC_ADJUSTMENT_LIST = 'logicalInventoryMscAdjustment.list';
	const LOGICAL_INVENTORY_MSC_ADJUSTMENT_CREATE = 'logicalInventoryMscAdjustment.create';
	const LOGICAL_INVENTORY_MSC_ADJUSTMENT_VIEW = 'logicalInventoryMscAdjustment.view';
	const LOGICAL_INVENTORY_MSC_ADJUSTMENT_EDIT = 'logicalInventoryMscAdjustment.edit';
	const LOGICAL_INVENTORY_MSC_ADJUSTMENT_DELETE = 'logicalInventoryMscAdjustment.delete';
	const DEFECT_INVENTORY_LIST = 'defectInventory.list';
	const DEFECT_INVENTORY_CREATE = 'defectInventory.create';
	const DEFECT_INVENTORY_VIEW = 'defectInventory.view';
	const DEFECT_INVENTORY_EDIT = 'defectInventory.edit';
	const DEFECT_INVENTORY_DELETE = 'defectInventory.delete';
	const MRP_RESULT_LIST = 'mrpResult.list';
	const MRP_RESULT_SYSTEM_RUN = 'mrpResult.systemRun';
	const MRP_SIMULATION_RESULT_LIST = 'mrpSimulationResult.list';
	const MRP_SIMULATION_RESULT_CREATE = 'mrpSimulationResult.create';
	const MRP_SIMULATION_RESULT_VIEW = 'mrpSimulationResult.view';
	const MRP_SIMULATION_RESULT_EDIT = 'mrpSimulationResult.edit';
	const MRP_SIMULATION_RESULT_DELETE = 'mrpSimulationResult.delete';
	const LOGICAL_INVENTORY_LIST = 'logicalInventory.list';
	const LOGICAL_INVENTORY_CREATE = 'logicalInventory.create';
	const LOGICAL_INVENTORY_VIEW = 'logicalInventory.view';
	const LOGICAL_INVENTORY_EDIT = 'logicalInventory.edit';
	const LOGICAL_INVENTORY_DELETE = 'logicalInventory.delete';
	const SHORTAGE_PART_LIST = 'shortagePart.list';
	const SHORTAGE_PART_SIMULATION_RUN = 'shortagePart.simulationRun';
	const ORDER_LIST_LIST = 'orderList.list';
	const ORDER_LIST_CREATE = 'orderList.create';
    const ORDER_LIST_SYSTEM_RUN = 'orderList.systemRun';
	const ORDER_LIST_VIEW = 'orderList.view';
	const ORDER_LIST_EDIT = 'orderList.edit';
	const ORDER_LIST_DELETE = 'orderList.delete';
	const MRP_ORDER_CALENDAR_LIST = 'mrpOrderCalendar.list';
	const MRP_ORDER_CALENDAR_CREATE = 'mrpOrderCalendar.create';
	const MRP_ORDER_CALENDAR_VIEW = 'mrpOrderCalendar.view';
	const MRP_ORDER_CALENDAR_EDIT = 'mrpOrderCalendar.edit';
	const MRP_ORDER_CALENDAR_DELETE = 'mrpOrderCalendar.delete';

    const VIETNAM_SOURCE_LOG_LIST = 'vietnamSourceLog.list';
    const VIETNAM_SOURCE_LOG_CREATE = 'vietnamSourceLog.create';
    const VIETNAM_SOURCE_LOG_VIEW = 'vietnamSourceLog.view';
    const VIETNAM_SOURCE_LOG_EDIT = 'vietnamSourceLog.edit';
    const VIETNAM_SOURCE_LOG_DELETE = 'vietnamSourceLog.delete';
    /** end const permissions */

    /**
     * For permissions seeding
     *
     * @return array
     */
    public static function getAllPermissions(): array
    {
        return [
			static::PART_GROUP_LIST,
			static::PART_GROUP_CREATE,
			static::PART_GROUP_VIEW,
			static::PART_GROUP_EDIT,
			static::PART_GROUP_DELETE,
			static::PLANT_LIST,
			static::PLANT_CREATE,
			static::PLANT_VIEW,
			static::PLANT_EDIT,
			static::PLANT_DELETE,
			static::ECN_LIST,
			static::ECN_CREATE,
			static::ECN_VIEW,
			static::ECN_EDIT,
			static::ECN_DELETE,
			static::VEHICLE_COLOR_LIST,
			static::VEHICLE_COLOR_CREATE,
			static::VEHICLE_COLOR_VIEW,
			static::VEHICLE_COLOR_EDIT,
			static::VEHICLE_COLOR_DELETE,
			static::MSC_LIST,
			static::MSC_CREATE,
			static::MSC_VIEW,
			static::MSC_EDIT,
			static::MSC_DELETE,
			static::PART_LIST,
			static::PART_CREATE,
			static::PART_VIEW,
			static::PART_EDIT,
			static::PART_DELETE,
			static::PART_COLOR_LIST,
			static::PART_COLOR_CREATE,
			static::PART_COLOR_VIEW,
			static::PART_COLOR_EDIT,
			static::PART_COLOR_DELETE,
			static::BOM_LIST,
			static::BOM_CREATE,
			static::BOM_VIEW,
			static::BOM_EDIT,
			static::BOM_DELETE,
            static::SUPPLIER_LIST,
			static::SUPPLIER_CREATE,
			static::SUPPLIER_VIEW,
			static::SUPPLIER_EDIT,
			static::SUPPLIER_DELETE,
			static::PROCUREMENT_LIST,
			static::PROCUREMENT_CREATE,
			static::PROCUREMENT_VIEW,
			static::PROCUREMENT_EDIT,
			static::PROCUREMENT_DELETE,
			static::WAREHOUSE_LIST,
			static::WAREHOUSE_CREATE,
			static::WAREHOUSE_VIEW,
			static::WAREHOUSE_EDIT,
			static::WAREHOUSE_DELETE,
			static::WAREHOUSE_LOCATION_LIST,
			static::WAREHOUSE_LOCATION_CREATE,
			static::WAREHOUSE_LOCATION_VIEW,
			static::WAREHOUSE_LOCATION_EDIT,
			static::WAREHOUSE_LOCATION_DELETE,
			static::ORDER_POINT_CONTROL_LIST,
			static::ORDER_POINT_CONTROL_CREATE,
			static::ORDER_POINT_CONTROL_VIEW,
			static::ORDER_POINT_CONTROL_EDIT,
			static::ORDER_POINT_CONTROL_DELETE,
			static::BOX_TYPE_LIST,
			static::BOX_TYPE_CREATE,
			static::BOX_TYPE_VIEW,
			static::BOX_TYPE_EDIT,
			static::BOX_TYPE_DELETE,
			static::MRP_WEEK_DEFINITION_LIST,
			static::MRP_WEEK_DEFINITION_CREATE,
			static::MRP_WEEK_DEFINITION_VIEW,
			static::MRP_WEEK_DEFINITION_EDIT,
			static::MRP_WEEK_DEFINITION_DELETE,
			static::PART_USAGE_RESULT_LIST,
			static::PART_USAGE_RESULT_CREATE,
			static::PART_USAGE_RESULT_VIEW,
			static::PART_USAGE_RESULT_EDIT,
			static::PART_USAGE_RESULT_DELETE,
			static::PRODUCTION_PLAN_LIST,
			static::PRODUCTION_PLAN_CREATE,
			static::PRODUCTION_PLAN_VIEW,
			static::PRODUCTION_PLAN_EDIT,
			static::PRODUCTION_PLAN_DELETE,
			static::ORDER_CALENDAR_LIST,
			static::ORDER_CALENDAR_CREATE,
			static::ORDER_CALENDAR_VIEW,
			static::ORDER_CALENDAR_EDIT,
			static::ORDER_CALENDAR_DELETE,
			static::IN_TRANSIT_INVENTORY_LOG_LIST,
			static::IN_TRANSIT_INVENTORY_LOG_CREATE,
			static::IN_TRANSIT_INVENTORY_LOG_VIEW,
			static::IN_TRANSIT_INVENTORY_LOG_EDIT,
			static::IN_TRANSIT_INVENTORY_LOG_DELETE,
			static::ADMIN_LIST,
			static::ADMIN_CREATE,
			static::ADMIN_VIEW,
			static::ADMIN_EDIT,
			static::ADMIN_DELETE,
			static::BWH_INVENTORY_LOG_LIST,
			static::BWH_INVENTORY_LOG_CREATE,
			static::BWH_INVENTORY_LOG_VIEW,
			static::BWH_INVENTORY_LOG_EDIT,
			static::BWH_INVENTORY_LOG_DELETE,
			static::UPKWH_INVENTORY_LOG_LIST,
			static::UPKWH_INVENTORY_LOG_CREATE,
			static::UPKWH_INVENTORY_LOG_VIEW,
			static::UPKWH_INVENTORY_LOG_EDIT,
			static::UPKWH_INVENTORY_LOG_DELETE,
			static::WH_INVENTORY_SUMMARY_LIST,
			static::WH_INVENTORY_SUMMARY_CREATE,
			static::WH_INVENTORY_SUMMARY_VIEW,
			static::WH_INVENTORY_SUMMARY_EDIT,
			static::WH_INVENTORY_SUMMARY_DELETE,
			static::PLANT_INVENTORY_LOG_LIST,
			static::PLANT_INVENTORY_LOG_CREATE,
			static::PLANT_INVENTORY_LOG_VIEW,
			static::PLANT_INVENTORY_LOG_EDIT,
			static::PLANT_INVENTORY_LOG_DELETE,
            static::REMARK_CREATE,
			static::BWH_ORDER_REQUEST_LIST,
			static::BWH_ORDER_REQUEST_CREATE,
			static::BWH_ORDER_REQUEST_VIEW,
			static::BWH_ORDER_REQUEST_EDIT,
            static::SETTING_CREATE,
            static::SETTING_LIST,
			static::WAREHOUSE_SUMMARY_ADJUSTMENT_LIST,
			static::WAREHOUSE_SUMMARY_ADJUSTMENT_CREATE,
			static::WAREHOUSE_SUMMARY_ADJUSTMENT_VIEW,
			static::WAREHOUSE_SUMMARY_ADJUSTMENT_EDIT,
			static::WAREHOUSE_SUMMARY_ADJUSTMENT_DELETE,
			static::LOGICAL_INVENTORY_PART_ADJUSTMENT_LIST,
			static::LOGICAL_INVENTORY_PART_ADJUSTMENT_CREATE,
			static::LOGICAL_INVENTORY_PART_ADJUSTMENT_VIEW,
			static::LOGICAL_INVENTORY_PART_ADJUSTMENT_EDIT,
			static::LOGICAL_INVENTORY_PART_ADJUSTMENT_DELETE,
			static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_LIST,
			static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_CREATE,
			static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_VIEW,
			static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_EDIT,
			static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_DELETE,
			static::DEFECT_INVENTORY_LIST,
			static::DEFECT_INVENTORY_CREATE,
			static::DEFECT_INVENTORY_VIEW,
			static::DEFECT_INVENTORY_EDIT,
			static::DEFECT_INVENTORY_DELETE,
			static::MRP_RESULT_LIST,
			static::MRP_RESULT_SYSTEM_RUN,
			static::MRP_SIMULATION_RESULT_LIST,
			static::MRP_SIMULATION_RESULT_CREATE,
			static::MRP_SIMULATION_RESULT_VIEW,
			static::MRP_SIMULATION_RESULT_EDIT,
			static::MRP_SIMULATION_RESULT_DELETE,
			static::LOGICAL_INVENTORY_LIST,
			static::LOGICAL_INVENTORY_CREATE,
			static::LOGICAL_INVENTORY_VIEW,
			static::LOGICAL_INVENTORY_EDIT,
			static::LOGICAL_INVENTORY_DELETE,
			static::SHORTAGE_PART_LIST,
			static::SHORTAGE_PART_SIMULATION_RUN,
			static::ORDER_LIST_LIST,
			static::ORDER_LIST_CREATE,
            static::ORDER_LIST_SYSTEM_RUN,
			static::ORDER_LIST_VIEW,
			static::ORDER_LIST_EDIT,
			static::ORDER_LIST_DELETE,
			static::MRP_ORDER_CALENDAR_LIST,
			static::MRP_ORDER_CALENDAR_CREATE,
			static::MRP_ORDER_CALENDAR_VIEW,
			static::MRP_ORDER_CALENDAR_EDIT,
			static::MRP_ORDER_CALENDAR_DELETE,
            static::VIETNAM_SOURCE_LOG_LIST,
            static::VIETNAM_SOURCE_LOG_VIEW,
            static::VIETNAM_SOURCE_LOG_EDIT,
            static::VIETNAM_SOURCE_LOG_DELETE,
            static::VIETNAM_SOURCE_LOG_CREATE
        ];
    }

    public static function getAdminPermissions(): array
    {
        return self::getAllPermissions();
    }

    public static function getPEAdminPermissions(): array
    {
        return [
            static::SETTING_LIST,
            static::PART_GROUP_CREATE,
            static::PART_GROUP_VIEW,
            static::PART_GROUP_EDIT,
            static::PART_GROUP_DELETE,
            static::PLANT_CREATE,
            static::PLANT_VIEW,
            static::PLANT_EDIT,
            static::PLANT_DELETE,
            static::ECN_CREATE,
            static::ECN_VIEW,
            static::ECN_EDIT,
            static::ECN_DELETE,
            static::VEHICLE_COLOR_CREATE,
            static::VEHICLE_COLOR_VIEW,
            static::VEHICLE_COLOR_EDIT,
            static::VEHICLE_COLOR_DELETE,
            static::MSC_CREATE,
            static::MSC_VIEW,
            static::MSC_EDIT,
            static::MSC_DELETE,
            static::PART_CREATE,
            static::PART_VIEW,
            static::PART_EDIT,
            static::PART_DELETE,
            static::PART_COLOR_CREATE,
            static::PART_COLOR_VIEW,
            static::PART_COLOR_EDIT,
            static::PART_COLOR_DELETE,
            static::BOM_CREATE,
            static::BOM_VIEW,
            static::BOM_EDIT,
            static::BOM_DELETE,
            static::REMARK_CREATE,
            static::PART_GROUP_LIST,
            static::PLANT_LIST,
            static::ECN_LIST,
            static::VEHICLE_COLOR_LIST,
            static::MSC_LIST,
            static::PART_LIST,
            static::PART_COLOR_LIST,
            static::BOM_LIST,
            static::SUPPLIER_LIST,
            static::PROCUREMENT_LIST,
            static::WAREHOUSE_LIST,
            static::WAREHOUSE_LOCATION_LIST,
            static::BOX_TYPE_LIST,
            static::MRP_WEEK_DEFINITION_LIST,
            static::PART_USAGE_RESULT_LIST,
            static::PRODUCTION_PLAN_LIST,
            static::ORDER_CALENDAR_LIST,
            static::IN_TRANSIT_INVENTORY_LOG_LIST,
            static::ADMIN_LIST,
            static::BWH_INVENTORY_LOG_LIST,
            static::UPKWH_INVENTORY_LOG_LIST,
            static::WH_INVENTORY_SUMMARY_LIST,
            static::PLANT_INVENTORY_LOG_LIST,
            static::BWH_ORDER_REQUEST_LIST,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_LIST,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_LIST,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_LIST,
            static::DEFECT_INVENTORY_LIST,
            static::MRP_RESULT_LIST,
            static::MRP_SIMULATION_RESULT_LIST,
            static::LOGICAL_INVENTORY_LIST,
            static::SHORTAGE_PART_LIST,
            static::ORDER_LIST_LIST,
            static::MRP_ORDER_CALENDAR_LIST,
            static::VIETNAM_SOURCE_LOG_LIST,
            static::ORDER_POINT_CONTROL_LIST,
            static::MRP_WEEK_DEFINITION_CREATE,
			static::MRP_WEEK_DEFINITION_VIEW,
			static::MRP_WEEK_DEFINITION_EDIT,
			static::MRP_WEEK_DEFINITION_DELETE,
        ]; // pe permissions
    }

    public static function getPURAdminPermissions(): array
    {
        return [
            static::SETTING_LIST,
            static::SUPPLIER_CREATE,
            static::SUPPLIER_VIEW,
            static::SUPPLIER_EDIT,
            static::SUPPLIER_DELETE,
            static::PROCUREMENT_CREATE,
            static::PROCUREMENT_VIEW,
            static::PROCUREMENT_EDIT,
            static::PROCUREMENT_DELETE,
            static::ORDER_LIST_CREATE,
            static::ORDER_LIST_SYSTEM_RUN,
            static::ORDER_LIST_VIEW,
            static::ORDER_LIST_EDIT,
            static::ORDER_LIST_DELETE,
            static::REMARK_CREATE,
            static::PART_GROUP_LIST,
            static::PLANT_LIST,
            static::ECN_LIST,
            static::VEHICLE_COLOR_LIST,
            static::MSC_LIST,
            static::PART_LIST,
            static::PART_COLOR_LIST,
            static::BOM_LIST,
            static::SUPPLIER_LIST,
            static::PROCUREMENT_LIST,
            static::WAREHOUSE_LIST,
            static::WAREHOUSE_LOCATION_LIST,
            static::BOX_TYPE_LIST,
            static::BOX_TYPE_CREATE,
            static::BOX_TYPE_VIEW,
            static::BOX_TYPE_EDIT,
            static::BOX_TYPE_DELETE,
            static::MRP_WEEK_DEFINITION_LIST,
            static::PART_USAGE_RESULT_LIST,
            static::PRODUCTION_PLAN_LIST,
            static::ORDER_CALENDAR_LIST,
            static::IN_TRANSIT_INVENTORY_LOG_LIST,
            static::ADMIN_LIST,
            static::BWH_INVENTORY_LOG_LIST,
            static::UPKWH_INVENTORY_LOG_LIST,
            static::WH_INVENTORY_SUMMARY_LIST,
            static::PLANT_INVENTORY_LOG_LIST,
            static::BWH_ORDER_REQUEST_LIST,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_LIST,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_LIST,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_LIST,
            static::DEFECT_INVENTORY_LIST,
            static::MRP_RESULT_LIST,
            static::MRP_SIMULATION_RESULT_LIST,
            static::LOGICAL_INVENTORY_LIST,
            static::SHORTAGE_PART_LIST,
            static::ORDER_LIST_LIST,
            static::MRP_ORDER_CALENDAR_LIST,
            static::VIETNAM_SOURCE_LOG_LIST,
            static::ORDER_POINT_CONTROL_LIST,
            static::MRP_WEEK_DEFINITION_CREATE,
			static::MRP_WEEK_DEFINITION_VIEW,
			static::MRP_WEEK_DEFINITION_EDIT,
			static::MRP_WEEK_DEFINITION_DELETE,
        ];
    }

    public static function getPlanAdminPermissions(): array
    {
        return [
            static::SETTING_LIST,
            static::REMARK_CREATE,
            static::IN_TRANSIT_INVENTORY_LOG_CREATE,
            static::IN_TRANSIT_INVENTORY_LOG_VIEW,
            static::IN_TRANSIT_INVENTORY_LOG_EDIT,
            static::IN_TRANSIT_INVENTORY_LOG_DELETE,
            static::MRP_RESULT_SYSTEM_RUN,
            static::MRP_SIMULATION_RESULT_CREATE,
            static::MRP_SIMULATION_RESULT_VIEW,
            static::MRP_SIMULATION_RESULT_EDIT,
            static::MRP_SIMULATION_RESULT_DELETE,
            static::LOGICAL_INVENTORY_CREATE,
            static::LOGICAL_INVENTORY_VIEW,
            static::LOGICAL_INVENTORY_EDIT,
            static::LOGICAL_INVENTORY_DELETE,
            static::SHORTAGE_PART_SIMULATION_RUN,
            static::ORDER_LIST_CREATE,
            static::ORDER_LIST_SYSTEM_RUN,
            static::ORDER_LIST_VIEW,
            static::ORDER_LIST_EDIT,
            static::ORDER_LIST_DELETE,
            static::MRP_ORDER_CALENDAR_CREATE,
            static::MRP_ORDER_CALENDAR_VIEW,
            static::MRP_ORDER_CALENDAR_EDIT,
            static::MRP_ORDER_CALENDAR_DELETE,
            static::VIETNAM_SOURCE_LOG_VIEW,
            static::PART_GROUP_LIST,
            static::PLANT_LIST,
            static::ECN_LIST,
            static::VEHICLE_COLOR_LIST,
            static::MSC_LIST,
            static::PART_LIST,
            static::PART_COLOR_LIST,
            static::BOM_LIST,
            static::SUPPLIER_LIST,
            static::PROCUREMENT_LIST,
            static::WAREHOUSE_LIST,
            static::WAREHOUSE_LOCATION_LIST,
            static::BOX_TYPE_LIST,
            static::MRP_WEEK_DEFINITION_LIST,
            static::PART_USAGE_RESULT_LIST,
            static::PRODUCTION_PLAN_LIST,
            static::ORDER_CALENDAR_LIST,
            static::IN_TRANSIT_INVENTORY_LOG_LIST,
            static::ADMIN_LIST,
            static::BWH_INVENTORY_LOG_LIST,
            static::UPKWH_INVENTORY_LOG_LIST,
            static::WH_INVENTORY_SUMMARY_LIST,
            static::PLANT_INVENTORY_LOG_LIST,
            static::BWH_ORDER_REQUEST_LIST,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_LIST,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_LIST,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_LIST,
            static::DEFECT_INVENTORY_LIST,
            static::MRP_RESULT_LIST,
            static::MRP_SIMULATION_RESULT_LIST,
            static::LOGICAL_INVENTORY_LIST,
            static::SHORTAGE_PART_LIST,
            static::ORDER_LIST_LIST,
            static::MRP_ORDER_CALENDAR_LIST,
            static::VIETNAM_SOURCE_LOG_LIST,
            static::ORDER_POINT_CONTROL_LIST,
            static::PRODUCTION_PLAN_CREATE,
            static::PRODUCTION_PLAN_VIEW,
            static::PRODUCTION_PLAN_EDIT,
            static::PRODUCTION_PLAN_DELETE,
            static::MRP_WEEK_DEFINITION_CREATE,
			static::MRP_WEEK_DEFINITION_VIEW,
			static::MRP_WEEK_DEFINITION_EDIT,
			static::MRP_WEEK_DEFINITION_DELETE,
        ];
    }

    public static function getEDAdminPermissions(): array
    {
        return [
            static::SETTING_LIST,
            static::ADMIN_CREATE,
            static::ADMIN_VIEW,
            static::ADMIN_EDIT,
            static::ADMIN_DELETE,
            static::REMARK_CREATE,
            static::PART_GROUP_LIST,
            static::PLANT_LIST,
            static::ECN_LIST,
            static::VEHICLE_COLOR_LIST,
            static::MSC_LIST,
            static::PART_LIST,
            static::PART_COLOR_LIST,
            static::BOM_LIST,
            static::SUPPLIER_LIST,
            static::PROCUREMENT_LIST,
            static::WAREHOUSE_LIST,
            static::WAREHOUSE_LOCATION_LIST,
            static::BOX_TYPE_LIST,
            static::MRP_WEEK_DEFINITION_LIST,
            static::PART_USAGE_RESULT_LIST,
            static::PRODUCTION_PLAN_LIST,
            static::ORDER_CALENDAR_LIST,
            static::IN_TRANSIT_INVENTORY_LOG_LIST,
            static::ADMIN_LIST,
            static::BWH_INVENTORY_LOG_LIST,
            static::UPKWH_INVENTORY_LOG_LIST,
            static::WH_INVENTORY_SUMMARY_LIST,
            static::PLANT_INVENTORY_LOG_LIST,
            static::BWH_ORDER_REQUEST_LIST,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_LIST,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_LIST,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_LIST,
            static::DEFECT_INVENTORY_LIST,
            static::MRP_RESULT_LIST,
            static::MRP_SIMULATION_RESULT_LIST,
            static::LOGICAL_INVENTORY_LIST,
            static::SHORTAGE_PART_LIST,
            static::ORDER_LIST_LIST,
            static::MRP_ORDER_CALENDAR_LIST,
            static::VIETNAM_SOURCE_LOG_LIST,
            static::ORDER_POINT_CONTROL_LIST,


        ];
    }

    public static function getBwhAdminPermissions(): array
    {
        return [
            static::SETTING_LIST,
            static::WAREHOUSE_CREATE,
            static::WAREHOUSE_VIEW,
            static::WAREHOUSE_EDIT,
            static::WAREHOUSE_DELETE,
            static::WAREHOUSE_LOCATION_CREATE,
            static::WAREHOUSE_LOCATION_VIEW,
            static::WAREHOUSE_LOCATION_EDIT,
            static::WAREHOUSE_LOCATION_DELETE,
            static::BWH_INVENTORY_LOG_CREATE,
            static::BWH_INVENTORY_LOG_VIEW,
            static::BWH_INVENTORY_LOG_EDIT,
            static::BWH_INVENTORY_LOG_DELETE,
            static::WH_INVENTORY_SUMMARY_CREATE,
            static::WH_INVENTORY_SUMMARY_VIEW,
            static::WH_INVENTORY_SUMMARY_EDIT,
            static::WH_INVENTORY_SUMMARY_DELETE,
            static::REMARK_CREATE,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_CREATE,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_VIEW,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_EDIT,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_DELETE,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_CREATE,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_VIEW,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_EDIT,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_DELETE,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_CREATE,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_VIEW,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_EDIT,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_DELETE,
            static::DEFECT_INVENTORY_CREATE,
            static::DEFECT_INVENTORY_VIEW,
            static::DEFECT_INVENTORY_EDIT,
            static::DEFECT_INVENTORY_DELETE,
            static::PART_GROUP_LIST,
            static::PLANT_LIST,
            static::ECN_LIST,
            static::VEHICLE_COLOR_LIST,
            static::MSC_LIST,
            static::PART_LIST,
            static::PART_COLOR_LIST,
            static::BOM_LIST,
            static::SUPPLIER_LIST,
            static::PROCUREMENT_LIST,
            static::WAREHOUSE_LIST,
            static::WAREHOUSE_LOCATION_LIST,
            static::BOX_TYPE_LIST,
            static::MRP_WEEK_DEFINITION_LIST,
            static::PART_USAGE_RESULT_LIST,
            static::PRODUCTION_PLAN_LIST,
            static::ORDER_CALENDAR_LIST,
            static::IN_TRANSIT_INVENTORY_LOG_LIST,
            static::ADMIN_LIST,
            static::BWH_INVENTORY_LOG_LIST,
            static::UPKWH_INVENTORY_LOG_LIST,
            static::WH_INVENTORY_SUMMARY_LIST,
            static::PLANT_INVENTORY_LOG_LIST,
            static::BWH_ORDER_REQUEST_LIST,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_LIST,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_LIST,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_LIST,
            static::DEFECT_INVENTORY_LIST,
            static::MRP_RESULT_LIST,
            static::MRP_SIMULATION_RESULT_LIST,
            static::LOGICAL_INVENTORY_LIST,
            static::SHORTAGE_PART_LIST,
            static::ORDER_LIST_LIST,
            static::MRP_ORDER_CALENDAR_LIST,
            static::VIETNAM_SOURCE_LOG_LIST,
            static::ORDER_POINT_CONTROL_LIST,
            static::VIETNAM_SOURCE_LOG_CREATE,
            static::VIETNAM_SOURCE_LOG_EDIT,
            static::VIETNAM_SOURCE_LOG_DELETE,

        ];
    }

    public static function getUpkAdminPermissions(): array
    {
        return [
            static::SETTING_LIST,
            static::WAREHOUSE_CREATE,
            static::WAREHOUSE_VIEW,
            static::WAREHOUSE_EDIT,
            static::WAREHOUSE_DELETE,
            static::WAREHOUSE_LOCATION_CREATE,
            static::WAREHOUSE_LOCATION_VIEW,
            static::WAREHOUSE_LOCATION_EDIT,
            static::WAREHOUSE_LOCATION_DELETE,
            static::ORDER_POINT_CONTROL_CREATE,
            static::ORDER_POINT_CONTROL_VIEW,
            static::ORDER_POINT_CONTROL_EDIT,
            static::ORDER_POINT_CONTROL_DELETE,
            static::UPKWH_INVENTORY_LOG_CREATE,
            static::UPKWH_INVENTORY_LOG_VIEW,
            static::UPKWH_INVENTORY_LOG_EDIT,
            static::UPKWH_INVENTORY_LOG_DELETE,
            static::WH_INVENTORY_SUMMARY_CREATE,
            static::WH_INVENTORY_SUMMARY_VIEW,
            static::WH_INVENTORY_SUMMARY_EDIT,
            static::WH_INVENTORY_SUMMARY_DELETE,
            static::REMARK_CREATE,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_CREATE,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_VIEW,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_EDIT,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_DELETE,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_CREATE,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_VIEW,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_EDIT,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_DELETE,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_CREATE,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_VIEW,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_EDIT,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_DELETE,
            static::DEFECT_INVENTORY_CREATE,
            static::DEFECT_INVENTORY_VIEW,
            static::DEFECT_INVENTORY_EDIT,
            static::DEFECT_INVENTORY_DELETE,
            static::PART_GROUP_LIST,
            static::PLANT_LIST,
            static::ECN_LIST,
            static::VEHICLE_COLOR_LIST,
            static::MSC_LIST,
            static::PART_LIST,
            static::PART_COLOR_LIST,
            static::BOM_LIST,
            static::SUPPLIER_LIST,
            static::PROCUREMENT_LIST,
            static::WAREHOUSE_LIST,
            static::WAREHOUSE_LOCATION_LIST,
            static::BOX_TYPE_LIST,
            static::MRP_WEEK_DEFINITION_LIST,
            static::PART_USAGE_RESULT_LIST,
            static::PRODUCTION_PLAN_LIST,
            static::ORDER_CALENDAR_LIST,
            static::IN_TRANSIT_INVENTORY_LOG_LIST,
            static::ADMIN_LIST,
            static::BWH_INVENTORY_LOG_LIST,
            static::UPKWH_INVENTORY_LOG_LIST,
            static::WH_INVENTORY_SUMMARY_LIST,
            static::PLANT_INVENTORY_LOG_LIST,
            static::BWH_ORDER_REQUEST_LIST,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_LIST,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_LIST,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_LIST,
            static::DEFECT_INVENTORY_LIST,
            static::MRP_RESULT_LIST,
            static::MRP_SIMULATION_RESULT_LIST,
            static::LOGICAL_INVENTORY_LIST,
            static::SHORTAGE_PART_LIST,
            static::ORDER_LIST_LIST,
            static::MRP_ORDER_CALENDAR_LIST,
            static::VIETNAM_SOURCE_LOG_LIST,
            static::ORDER_POINT_CONTROL_LIST,
            static::BWH_ORDER_REQUEST_CREATE,
            static::BWH_ORDER_REQUEST_VIEW,
            static::BWH_ORDER_REQUEST_EDIT,
            static::VIETNAM_SOURCE_LOG_CREATE,
            static::VIETNAM_SOURCE_LOG_EDIT,
            static::VIETNAM_SOURCE_LOG_DELETE,
        ];
    }

    public static function getPlantAdminPermissions(): array
    {
        return [
            static::SETTING_LIST,
            static::WAREHOUSE_CREATE,
            static::WAREHOUSE_VIEW,
            static::WAREHOUSE_EDIT,
            static::WAREHOUSE_DELETE,
            static::WAREHOUSE_LOCATION_CREATE,
            static::WAREHOUSE_LOCATION_VIEW,
            static::WAREHOUSE_LOCATION_EDIT,
            static::WAREHOUSE_LOCATION_DELETE,
            static::BOX_TYPE_CREATE,
            static::BOX_TYPE_VIEW,
            static::BOX_TYPE_EDIT,
            static::BOX_TYPE_DELETE,
            static::WH_INVENTORY_SUMMARY_CREATE,
            static::WH_INVENTORY_SUMMARY_VIEW,
            static::WH_INVENTORY_SUMMARY_EDIT,
            static::WH_INVENTORY_SUMMARY_DELETE,
            static::PLANT_INVENTORY_LOG_CREATE,
            static::PLANT_INVENTORY_LOG_VIEW,
            static::PLANT_INVENTORY_LOG_EDIT,
            static::PLANT_INVENTORY_LOG_DELETE,
            static::REMARK_CREATE,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_CREATE,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_VIEW,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_EDIT,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_DELETE,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_CREATE,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_VIEW,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_EDIT,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_DELETE,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_CREATE,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_VIEW,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_EDIT,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_DELETE,
            static::DEFECT_INVENTORY_CREATE,
            static::DEFECT_INVENTORY_VIEW,
            static::DEFECT_INVENTORY_EDIT,
            static::DEFECT_INVENTORY_DELETE,
            static::PART_GROUP_LIST,
            static::PLANT_LIST,
            static::ECN_LIST,
            static::VEHICLE_COLOR_LIST,
            static::MSC_LIST,
            static::PART_LIST,
            static::PART_COLOR_LIST,
            static::BOM_LIST,
            static::SUPPLIER_LIST,
            static::PROCUREMENT_LIST,
            static::WAREHOUSE_LIST,
            static::WAREHOUSE_LOCATION_LIST,
            static::BOX_TYPE_LIST,
            static::MRP_WEEK_DEFINITION_LIST,
            static::PART_USAGE_RESULT_LIST,
            static::PRODUCTION_PLAN_LIST,
            static::ORDER_CALENDAR_LIST,
            static::IN_TRANSIT_INVENTORY_LOG_LIST,
            static::ADMIN_LIST,
            static::BWH_INVENTORY_LOG_LIST,
            static::UPKWH_INVENTORY_LOG_LIST,
            static::WH_INVENTORY_SUMMARY_LIST,
            static::PLANT_INVENTORY_LOG_LIST,
            static::BWH_ORDER_REQUEST_LIST,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_LIST,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_LIST,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_LIST,
            static::DEFECT_INVENTORY_LIST,
            static::MRP_RESULT_LIST,
            static::MRP_SIMULATION_RESULT_LIST,
            static::LOGICAL_INVENTORY_LIST,
            static::SHORTAGE_PART_LIST,
            static::ORDER_LIST_LIST,
            static::MRP_ORDER_CALENDAR_LIST,
            static::VIETNAM_SOURCE_LOG_LIST,
            static::ORDER_POINT_CONTROL_LIST,
            static::PART_USAGE_RESULT_CREATE,
            static::PART_USAGE_RESULT_VIEW,
            static::PART_USAGE_RESULT_EDIT,
            static::PART_USAGE_RESULT_DELETE,
            static::VIETNAM_SOURCE_LOG_CREATE,
            static::VIETNAM_SOURCE_LOG_EDIT,
            static::VIETNAM_SOURCE_LOG_DELETE,
        ];
    }

    public static function getORHAdminPermissions(): array
    {
        return [
            static::REMARK_CREATE,
            static::SETTING_LIST,
            static::IN_TRANSIT_INVENTORY_LOG_CREATE,
            static::IN_TRANSIT_INVENTORY_LOG_VIEW,
            static::IN_TRANSIT_INVENTORY_LOG_EDIT,
            static::IN_TRANSIT_INVENTORY_LOG_DELETE,
            static::PART_GROUP_LIST,
            static::PLANT_LIST,
            static::ECN_LIST,
            static::VEHICLE_COLOR_LIST,
            static::MSC_LIST,
            static::PART_LIST,
            static::PART_COLOR_LIST,
            static::BOM_LIST,
            static::SUPPLIER_LIST,
            static::PROCUREMENT_LIST,
            static::WAREHOUSE_LIST,
            static::WAREHOUSE_LOCATION_LIST,
            static::BOX_TYPE_LIST,
            static::MRP_WEEK_DEFINITION_LIST,
            static::PART_USAGE_RESULT_LIST,
            static::PRODUCTION_PLAN_LIST,
            static::ORDER_CALENDAR_LIST,
            static::IN_TRANSIT_INVENTORY_LOG_LIST,
            static::ADMIN_LIST,
            static::BWH_INVENTORY_LOG_LIST,
            static::UPKWH_INVENTORY_LOG_LIST,
            static::WH_INVENTORY_SUMMARY_LIST,
            static::PLANT_INVENTORY_LOG_LIST,
            static::BWH_ORDER_REQUEST_LIST,
            static::WAREHOUSE_SUMMARY_ADJUSTMENT_LIST,
            static::LOGICAL_INVENTORY_PART_ADJUSTMENT_LIST,
            static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_LIST,
            static::DEFECT_INVENTORY_LIST,
            static::MRP_RESULT_LIST,
            static::MRP_SIMULATION_RESULT_LIST,
            static::LOGICAL_INVENTORY_LIST,
            static::SHORTAGE_PART_LIST,
            static::ORDER_LIST_LIST,
            static::MRP_ORDER_CALENDAR_LIST,
            static::VIETNAM_SOURCE_LOG_LIST,
            static::ORDER_POINT_CONTROL_LIST,

        ];
    }

    /**
     * @param $importType
     * @return string|null
     */
    public static function getPermissionByImportType($importType): ?string
    {
        $listPermissions = [
            'user' => static::ADMIN_CREATE,
            'part_group' => static::PART_GROUP_CREATE,
            'plant' => static::PLANT_CREATE,
            'ecn' => static::ECN_CREATE,
            'vehicle_color' => static::VEHICLE_COLOR_CREATE,
            'msc' => static::MSC_CREATE,
            'part' => static::PART_CREATE,
            'part_color' => static::PART_COLOR_CREATE,
            'bom' => static::BOM_CREATE,
            'supplier' => static::SUPPLIER_CREATE,
            'procurement' => static::PROCUREMENT_CREATE,
            'warehouse' => static::WAREHOUSE_CREATE,
            'warehouse_location' => static::WAREHOUSE_LOCATION_CREATE,
            'box_type' => static::BOX_TYPE_CREATE,
            'in_transit_inventory_log' => static::IN_TRANSIT_INVENTORY_LOG_CREATE,
            'bwh_inventory_log' => static::BWH_INVENTORY_LOG_CREATE,
            'upkwh_inventory_log' => static::UPKWH_INVENTORY_LOG_CREATE,
            'order_point_control' => static::ORDER_POINT_CONTROL_CREATE,
            'vietnam_source_log' => static::VIETNAM_SOURCE_LOG_CREATE,
            'warehouse_summary_adjustment' => static::WAREHOUSE_SUMMARY_ADJUSTMENT_CREATE,
            'warehouse_logical_adjustment_part' => static::LOGICAL_INVENTORY_PART_ADJUSTMENT_CREATE,
            'warehouse_logical_adjustment_msc' => static::LOGICAL_INVENTORY_MSC_ADJUSTMENT_CREATE,
            'production_plan' => static::PRODUCTION_PLAN_CREATE,
            'part_usage_result' => static::PART_USAGE_RESULT_CREATE,
            'shortage_part' => static::PRODUCTION_PLAN_CREATE,
            'mrp_ordering_calendar' => static::MRP_ORDER_CALENDAR_CREATE
        ];
        return $listPermissions[$importType] ?? null;
    }
}
