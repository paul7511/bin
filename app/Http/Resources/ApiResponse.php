<?php

namespace App\Http\Resources;

use App\Enums\EnumResultCode;
use Illuminate\Http\JsonResponse;

class ApiResponse
{
    protected $resource;

    protected $apiStatusCode;

    /** @var EnumResultCode */
    protected $metaResultCode;

    protected $metaDesc;

    /** @var array */
    protected $pagination;

    protected $headers = [
        'Content-Type' => 'application/json; charset=utf-8',
    ];

    public function __construct(?array $data)
    {
        $this->resource = $data;
        $this->setMetaResult(EnumResultCode::SUCCESS());
        $this->setApiStatusCode(JsonResponse::HTTP_OK);
    }

    public function setApiStatusCode(?int $code): self
    {
        if(!empty($code))
        {
            $this->apiStatusCode = $code;
        }

        return $this;
    }

    public function setMetaResult(EnumResultCode $resultCode): self
    {
        $this->metaResultCode = $resultCode;

        return $this;
    }

    public function setMetaDesc(?string $metaDesc): self
    {
        $this->metaDesc = $metaDesc;

        return $this;
    }

    public function setPagination(array $pagination): self
    {
        $this->pagination = $pagination;

        return $this;
    }

    public function toJsonResponse(): JsonResponse
    {
        return response()->json($this->format(), $this->apiStatusCode, $this->headers);
    }

    protected function getMetaStatus(): string
    {
        return $this->metaResultCode->getValue();
    }

    protected function getMetaDesc(): string
    {
        return $this->metaDesc ?? $this->metaResultCode->getDesc();
    }

    protected function format(): array
    {
        if (null !== $this->pagination) {
            return [
                'metadata' => [
                    'status' => $this->getMetaStatus(),
                    'desc' => $this->getMetaDesc(),
                    'pagination' => $this->pagination,
                ],
                'data' => $this->resource,
            ];
        }

        return [
            'metadata' => [
                'status' => $this->getMetaStatus(),
                'desc' => $this->getMetaDesc(),
            ],
            'data' => $this->resource,
        ];
    }
}
