<?php

namespace App\Services;

use App\Models\MrpProductionPlanImport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class MrpProductionPlanImportService extends BaseService
{

    /**
     * @return string
     */
    public function model(): string
    {
        return MrpProductionPlanImport::class;
    }

    /**
     * @return Builder|Model|object|null
     */
    public function getFileRunningShortage()
    {
        return $this->getFileImportByStatus(MrpProductionPlanImport::STATUS_NOT_RUN);
    }

    /**
     * @return Builder|Model|object|null
     */
    public function getFileRunningMrp()
    {
        return $this->getFileImportByStatus(MrpProductionPlanImport::STATUS_CHECKED_SHORTAGE);
    }

    /**
     * @return Builder|Model|object|null
     */
    public function getFileRunningOrder()
    {
        return $this->getFileImportByStatus(MrpProductionPlanImport::STATUS_RAN_MRP);
    }

    /**
     * @param $status
     * @return Builder|Model|object|null
     */
    private function getFileImportByStatus($status)
    {
        return $this->query
            ->select(['id', 'original_file_name', 'mrp_or_status', 'mrp_or_progress', 'mrp_or_result'])
            ->where('mrp_or_status', $status)
            ->where('mrp_or_progress', '>', 0)
            ->where('mrp_or_progress', '<', 100)
            ->latest()
            ->first();
    }

    /**
     * @return array
     */
    public function getImportFiles(): array
    {
        $params = request()->toArray();
        $limit = (int)($params['per_page'] ?? 20);

        $query = $this->query
            ->select('id', 'original_file_name')
            ->limit($limit);
        if (isset($params['mrp_or_status'])) {
            $query->where('mrp_or_status', '>=', $params['mrp_or_status']);
        }
        if (isset($params['original_file_name'])) {
            $query->where('original_file_name', 'LIKE', '%' . $params['original_file_name'] . '%');
        }

        return $query->latest('updated_at')->get()->toArray();
    }

    /**
     * @param $id
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function getImportFile($id)
    {
        return $this->query
            ->select(['id', 'original_file_name', 'mrp_or_status', 'mrp_or_progress', 'mrp_or_result'])
            ->find($id);
    }

    /**
     * @param $status
     * @return Builder|Model|object|null
     */
    public static function getLastRunFileByStatus($status)
    {
        $q = MrpProductionPlanImport::query();
        if ($status) {
            $q->where(function ($sql) use ($status) {
                $sql->where(function ($sql) use ($status) {
                    $sql->where('mrp_or_status', '>=', $status);
                })->orWhere(function ($sql) use ($status) {
                    $sql->where('mrp_or_status', $status - 1)
                        ->where('mrp_or_progress', '>', 0)
                        ->where('mrp_or_progress', '<', 100);
                });
            });
        }
        return $q
            ->orderBy('mrp_or_status')
            ->orderBy('updated_at', 'desc')->first();
    }
}
