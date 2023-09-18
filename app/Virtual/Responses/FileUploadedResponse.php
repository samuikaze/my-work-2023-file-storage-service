<?php

namespace App\Virtual\Responses;

/**
 * 檔案上傳成功回應酬載
 *
 * @OA\Schema(
 *   title="檔案上傳成功回應酬載",
 *   description="檔案上傳成功回應酬載",
 *   type="object"
 * )
 */
class FileUploadedResponse
{
    /**
     * 上傳檔案 PK
     *
     * @var int
     *
     * @OA\Property(
     *   description="上傳檔案 PK",
     *   example=1
     * )
     */
    public $id;

    /**
     * 檔案路徑
     *
     * @var string
     *
     * @OA\Property(
     *   description="檔案路徑",
     *   example="test/example.jpg"
     * )
     */
    public $path;
}
