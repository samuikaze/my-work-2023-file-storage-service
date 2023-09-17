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
class SingleFileUploadRequest
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
     *   description="要上傳的檔案"
     * )
     */
    public $file;
}
