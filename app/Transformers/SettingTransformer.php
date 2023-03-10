<?php

namespace App\Transformers;

use App\Models\Setting;
use League\Fractal\TransformerAbstract;
use Spatie\Permission\Models\Role;

class SettingTransformer extends TransformerAbstract
{
    /**
     * @param Setting $setting
     * @return array
     */
    public function transform(Setting $setting): array
    {
        return [
            'key' => $setting->key,
            'value' => $setting->key == 'lock_table_master' ? $setting->value : $setting->value[0]
        ];
    }
}
