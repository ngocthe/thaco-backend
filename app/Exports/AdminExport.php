<?php

namespace App\Exports;

use App\Models\Admin;
use App\Services\AdminService;

class AdminExport extends BaseExport
{

    const TITLE = 'User List';

    public function __construct($fileTitle = '')
    {
        parent::__construct(new AdminService, self::TITLE, $fileTitle);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'User Code',
            'User Name',
            'Company',
            'Role Name'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param Admin $row
     * @return array
     */
    public function map($row): array
    {
        $roleNames = [];
        foreach ($row->roles as $role) {
            $roleNames[] = $role->name;
        }
        return $this->transform([
            $row->code,
            $row->name,
            $row->company,
            implode(', ', $roleNames) ,
        ], $this->type == 'xls');
    }
}
