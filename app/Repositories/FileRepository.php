<?php

namespace App\Repositories;

use App\Exceptions\EntityNotFoundException;
use App\Models\File;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class FileRepository extends BaseRepository
{
    /**
     * 建構方法
     *
     * @param \App\Models\File $model
     * @return void
     */
    public function __construct(File $model)
    {
        $this->model = $model;
    }

    /**
     * 取得檔案儲存資訊基礎
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function baseGetFile(): Builder
    {
        return $this->model
            ->where('files.is_valid', 1);
    }

    /**
     * 依檔案名稱取得檔案儲存資訊
     *
     * @param string $folder
     * @param string $filename
     * @return \App\Models\File
     *
     * @throws \App\Exceptions\EntityNotFoundException
     */
    public function getSingleFileByFilename(string $folder, string $filename): File
    {
        $file = $this->baseGetFile()
            ->where('files.display_folder', $folder)
            ->where('files.original_filename', $filename)
            ->first();

        if (is_null($file)) {
            throw new EntityNotFoundException('找不到該檔案');
        }

        return $file;
    }

    /**
     * 依多筆檔案名稱取得檔案儲存資訊
     *
     * @param array<int, string> $files
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\File>
     */
    public function getFileByFoldersAndFilenames(array $files): Collection
    {
        return $this->baseGetFile()
            ->where(function ($query) use ($files) {
                foreach ($files as $file) {
                    $query->orWhere(function ($query_clause) use ($file) {
                        $query_clause->where('file.folder', $file['folder'])
                            ->where('file.filename', $file['filename']);
                    });
                }
            })
            ->get();
    }
}
