<?php

namespace App\Services;

use App\Exports\AdminExport;
use App\Exports\BomExport;
use App\Exports\BoxTypeExport;
use App\Exports\EcnExport;
use App\Exports\MscExport;
use App\Exports\PartColorExport;
use App\Exports\PartExport;
use App\Exports\PartGroupExport;
use App\Exports\PlantExport;
use App\Exports\ProcurementExport;
use App\Exports\SupplierExport;
use App\Exports\VehicleColorExport;
use App\Exports\WarehouseExport;
use App\Exports\WarehouseLocationExport;
use App\Imports\BomImport;
use App\Imports\BoxTypeImport;
use App\Imports\EcnImport;
use App\Imports\MscImport;
use App\Imports\PartColorImport;
use App\Imports\PartGroupImport;
use App\Imports\PartImport;
use App\Imports\PlantImport;
use App\Imports\ProcurementImport;
use App\Imports\SupplierImport;
use App\Imports\AdminImport;
use App\Imports\VehicleColorImport;
use App\Imports\WarehouseImport;
use App\Imports\WarehouseLocationImport;

class DataMasterImportService extends BaseDataImportService
{
    /**
     * @var array|string[]
     */
    public array $importClass = [
        'part_group' => PartGroupImport::class,
        'plant' => PlantImport::class,
        'vehicle_color' => VehicleColorImport::class,
        'msc' => MscImport::class,
        'part' => PartImport::class,
        'part_color' => PartColorImport::class,
        'ecn' => EcnImport::class,
        'bom' => BomImport::class,
        'supplier' => SupplierImport::class,
        'procurement' => ProcurementImport::class,
        'warehouse' => WarehouseImport::class,
        'warehouse_location' => WarehouseLocationImport::class,
        'box_type' => BoxTypeImport::class,
        'user' => AdminImport::class
    ];

    /**
     * @var array|string[]
     */
    public array $exportTemplateName = [
        'part_group' => 'part-group-master',
        'plant' => 'plant-master',
        'vehicle_color' => 'vehicle-color-master',
        'msc' => 'msc-master',
        'part' => 'part-master',
        'part_color' => 'part-color-code',
        'ecn' => 'ecn-master',
        'bom' => 'bom',
        'supplier' => 'procurement-supplier-master',
        'procurement' => 'part-procurement',
        'warehouse' => 'warehouse-master',
        'warehouse_location' => 'warehouse-location-master',
        'box_type' => 'box-type-master',
        'user' => 'user-master'
    ];

    /**
     * @var array|string[]
     */
    public array $exportTemplateTitle = [
        'part_group' => PartGroupExport::TITLE,
        'plant' => PlantExport::TITLE,
        'vehicle_color' => VehicleColorExport::TITLE,
        'msc' => MscExport::TITLE,
        'part' => PartExport::TITLE,
        'part_color' => PartColorExport::TITLE,
        'ecn' => EcnExport::TITLE,
        'bom' => BomExport::TITLE,
        'supplier' => SupplierExport::TITLE,
        'procurement' => ProcurementExport::TITLE,
        'warehouse' => WarehouseExport::TITLE,
        'warehouse_location' => WarehouseLocationExport::TITLE,
        'box_type' => BoxTypeExport::TITLE,
        'user' => AdminExport::TITLE
    ];

}
