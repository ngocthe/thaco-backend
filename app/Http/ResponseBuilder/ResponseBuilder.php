<?php

namespace App\Http\ResponseBuilder;

use App\Constants\ApiCodes;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as Builder;

class ResponseBuilder extends Builder
{
    protected function buildResponse(bool $success, int $api_code, $msg_or_api_code, array $lang_args = null,
                                     $data = null, array $debug_data = null): array
    {
        // tell ResponseBuilder to do all the heavy lifting first
        $tmpResponse = parent::buildResponse($success, $api_code, $msg_or_api_code, $lang_args, $data, $debug_data);

        if ($tmpResponse['success']) {
            $response = [
                'status' => true,
                'message' => $tmpResponse['message'] ?? null,
            ];
            if (isset($tmpResponse['data']->pagination)) {
                $data = (array)($tmpResponse['data']);
                $response = array_merge($response, $data);
            } elseif (isset($tmpResponse['data']->values)) {
                $response['data'] = $tmpResponse['data']->values;
            } elseif (isset($tmpResponse['data']->item)) {
                $response['data'] = $tmpResponse['data']->item;
            } else {
                $response['data'] = $tmpResponse['data'];
            }
            return $response;
        }
        $response = [
            'status' => false,
            'code' => ApiCodes::convertToReadable($tmpResponse['code']),
            'message' => $tmpResponse['message'] ?? null,
        ];
        $response['errors'] = $tmpResponse['data']->values ?? $tmpResponse['data']->item ?? $tmpResponse['data'];
        if (isset($tmpResponse['debug'])) {
            $response['debug'] = $tmpResponse['debug'];
        }
        return $response;
    }
}
