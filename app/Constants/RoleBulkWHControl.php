<?php


namespace App\Constants;


class RoleBulkWHControl
{
    const ROLE_ADMIN_BULK = 'admin_bulk';
    const BWH_STAFF= 'bwh_staff';
    const UPKWH_STAFF= 'upkwh_staff';
    const TMACTRIM_STAFF= 'tmactrim_staff';
    const TMACFINAL_STAFF= 'tmacfinal_staff';

    public static function allRoleBWHControl() {
        return [
            self::ROLE_ADMIN_BULK,
            self::BWH_STAFF,
            self::UPKWH_STAFF,
            self::TMACTRIM_STAFF,
            self::TMACFINAL_STAFF
        ];
    }
}
