<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResponse;
use App\Enums\EnumResultCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class ApiBaseController extends BaseController
{
    protected function jsonRender(?array $data = [], ?int $apiStatusCode = null): JsonResponse
    {
        return (new ApiResponse($data))->setApiStatusCode($apiStatusCode)->toJsonResponse();
    }

    protected function jsonFailRender(?array $data = [], EnumResultCode $resultCode, ?int $apiStatusCode = null): JsonResponse
    {
        return (new ApiResponse($data))
            ->setMetaResult($resultCode)
            ->setApiStatusCode($apiStatusCode)
            ->toJsonResponse();
    }
}
