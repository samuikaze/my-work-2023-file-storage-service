<?php

namespace App\Repositories;

use App\Models\FileUpload;
use App\Repositories\BaseRepository;

class FileUploadRepository extends BaseRepository
{
    /**
     * @param \App\Models\FileUpload $model
     * @return void
     */
    public function __construct(FileUpload $model)
    {
        $this->model = $model;
    }

    /**
     * 以檔名找出檔案上傳紀錄
     *
     * @param string $filename 檔案名稱
     * @return \App\Models\FileUpload|null
     */
    public function findUploadRecordByFilename(string $filename)
    {
        return $this->model
            ->where('file_uploads.filename', $filename)
            ->first();
    }

    /**
     * 以上傳階段唯一識別碼找出檔案上傳紀錄
     *
     * @param string $upload_id 上傳階段唯一識別碼
     * @return \App\Models\FileUpload|null
     */
    public function findUploadRecordByUploadId(string $upload_id): FileUpload|null
    {
        return $this->model
            ->where('file_uploads.upload_id', $upload_id)
            ->first();
    }

    /**
     * 以帳號 ID 與檔名找出檔案上傳紀錄
     *
     * @param int $user_id 帳號 ID
     * @param string $filename 檔案名稱
     * @return \App\Models\FileUpload|null
     */
    public function findUploadRecordByUserIdAndFilename(int $user_id, string $filename)
    {
        return $this->model
            ->where('user_id', $user_id)
            ->where('filename', $filename)
            ->where('is_finished', 1)
            ->first();
    }
}
