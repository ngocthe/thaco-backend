<?php

namespace App\Services;

use App\Models\Setting;

class SettingService extends BaseService
{
    public function model(): string
    {
        return Setting::class;
    }

    /**
     * @param array $attributes
     * @param bool $hasRemark
     * @return bool
     */
    public function store(array $attributes, bool $hasRemark = true): bool
    {
        if (!is_array($attributes['value'])) {
            $attributes['value'] = [$attributes['value']];
        }
        $this->query->where('key', $attributes['key'])
            ->update(['value' => $attributes['value'], 'updated_by'=>auth()->id()]);
        return true;
    }
}
