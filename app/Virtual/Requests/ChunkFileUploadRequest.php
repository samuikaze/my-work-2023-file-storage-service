<?php

namespace App\Virtual\Requests;

/**
 * 分塊上傳檔案請求 DTO
 *
 * @OA\Schema(
 *   title="分塊上傳檔案請求",
 *   description="分塊上傳檔案請求",
 *   type="object",
 *   required={"fileName", "chunk", "isLast"}
 * )
 */
class ChunkFileUploadRequest
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

    /**
     * 檔案名稱
     *
     * @var string
     *
     * @OA\Property(
     *   description="原始檔案名稱",
     *   example="example.zip"
     * )
     */
    public $filename;

    /**
     * 檔案分塊
     *
     * @var \Illuminate\Http\UploadedFile
     *
     * @OA\Property(
     *   description="經切塊後的檔案",
     *   format="binary",
     *   type="string"
     * )
     */
    public $chunk;

    /**
     * 檔案分塊
     *
     * @var int
     *
     * @OA\Property(
     *   description="第幾片分塊",
     *   example="1"
     * )
     */
    public $count;

    /**
     * 是否為最後一塊
     *
     * @var bool
     *
     * @OA\Property(
     *   title="是否為最後一塊",
     *   description="此分塊是否為最後一個分塊",
     *   example="true"
     * )
     */
    public $isLast;
}
