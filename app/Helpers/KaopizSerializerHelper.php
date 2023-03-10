<?php

namespace App\Helpers;

use League\Fractal\Serializer\DataArraySerializer;

class KaopizSerializerHelper extends DataArraySerializer
{
    /**
     * @param string|null $resourceKey
     * @param array $data
     * @return array
     */
    public function collection(?string $resourceKey, array $data): array
    {
        return $data;
    }

    /**
     * @param string|null $resourceKey
     * @param array $data
     * @return array
     */
    public function item(?string $resourceKey, array $data): array
    {
        return $data;
    }

    /**
     * @return array|null
     */
    public function null(): ?array
    {
        return [];
    }
}
