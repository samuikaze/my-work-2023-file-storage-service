<?php

namespace App\Virtual\Requests;

/**
 * 多檔包 Zip 下載請求
 *
 * @OA\Schema(
 *   title="多檔包 Zip 下載請求",
 *   description="多檔包 Zip 下載請求",
 *   type="object",
 *   required={"files"}
 * )
 */
class MultipleFilesDownloadRequest
{
    /**
     * 檔案名稱
     *
     * @var array
     *
     * @OA\Property(
     *   description="檔案名稱",
     *   @OA\Items(
     *     type="string",
     *     example="testfolder/test.jpg"
     *   )
     * )
     */
    public $files;

    /**
     * 希望檔案下載的預設名稱
     *
     * @var string
     *
     * @OA\Property(
     *   description="希望檔案下載的預設名稱",
     *   example="test.zip"
     * )
     */
    public $filename;
}
