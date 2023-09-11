<?php

namespace App\Virtual\Resources;

/**
 * 合併分塊檔案請求 DTO
 *
 * @OA\Schema(
 *   title="合併分塊檔案請求",
 *   description="合併分塊檔案請求",
 *   type="object",
 *   required={"fileName"}
 * )
 */
class MergeChunksRequest
{
    /**
     * 上傳階段唯一識別碼，UUIDv4
     *
     * @var string
     *
     * @OA\Property(
     *   description="上傳階段唯一識別碼，UUIDv4",
     *   example="5e575c5a-9ec3-47c0-82c7-33aa737104dd"
     * )
     */
    public $uploadId;
}
