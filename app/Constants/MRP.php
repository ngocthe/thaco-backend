<?php


namespace App\Constants;


class MRP
{
    const DEFAULT_PLANT_CODE = 'TMAC';
//    MRP Ordering Calendar
    const MRP_ORDER_CALENDAR_STATUS_WAIT = 1;
    const MRP_ORDER_CALENDAR_STATUS_DONE = 2;

    /**
     * @return int[]
     */
    public static function getAllStatusMRPOrderCalendar(): array
    {
        return [self::MRP_ORDER_CALENDAR_STATUS_WAIT, self::MRP_ORDER_CALENDAR_STATUS_DONE];
    }

    /**
     * @param $status
     * @return string
     */
    public static function getTextStatusMRPOrderCalendar($status): string
    {
        switch ($status) {
            case self::MRP_ORDER_CALENDAR_STATUS_WAIT:
                return 'Wait';
            case self::MRP_ORDER_CALENDAR_STATUS_DONE:
                return 'Done';
            default:
                return '';
        }
    }

//    Order List
    const MRP_ORDER_LIST_STATUS_WAIT = 1;
    const MRP_ORDER_LIST_STATUS_RELEASE = 2;
    const MRP_ORDER_LIST_STATUS_DONE = 3;

    /**
     * @return int[]
     */
    public static function getAllStatusMRPOrderList(): array
    {
        return [self::MRP_ORDER_LIST_STATUS_WAIT, self::MRP_ORDER_LIST_STATUS_RELEASE, self::MRP_ORDER_LIST_STATUS_DONE];
    }


    /**
     * @param $status
     * @return string
     */
    public static function getTextStatusMRPOrderList($status): string
    {
        switch ($status) {
            case self::MRP_ORDER_LIST_STATUS_WAIT:
                return 'Wait';
            case self::MRP_ORDER_LIST_STATUS_RELEASE:
                return 'Shipping';
            case self::MRP_ORDER_LIST_STATUS_DONE:
                return 'Done';
            default:
                return '';
        }
    }

    const PART_GROUP_VIETNAM = 'VS';
}
