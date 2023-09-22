<?php

namespace App\Services;

use App\Commons\Utils;
use App\Enums\IsFinish;
use App\Enums\PathType;
use App\Exceptions\EntityNotFoundException;
use App\Exceptions\IOException;
use App\Repositories\FileRepository;
use App\Repositories\FileUploadRepository;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use ZipArchive;

class FileService
{
    /**
     * FileUploadRepository
     *
     * @var \App\Repositories\FileUploadRepository
     */
    protected $file_upload_repository;

    /**
     * FileRepository
     *
     * @var \App\Repositories\FileRepository
     */
    protected $file_repository;

    /**
     * 建構方法
     *
     * @param \App\Repositories\FileRepository $file_repository
     * @param \App\Repositories\FileUploadRepository $file_upload_repository
     * @return void
     */
    public function __construct(
        FileRepository $file_repository,
        FileUploadRepository $file_upload_repository
    ) {
        $this->file_repository = $file_repository;
        $this->file_upload_repository = $file_upload_repository;

        $check_exists = [
            config('file.save_folder', storage_path('app'.DIRECTORY_SEPARATOR.'files')),
            config('file.temp_folder', storage_path('app'.DIRECTORY_SEPARATOR.'temps')),
            config('file.zip_folder', storage_path('app'.DIRECTORY_SEPARATOR.'zips')),
        ];
        foreach ($check_exists as $check) {
            Utils::checkIfDirectoryExists($check);
        }
    }

    /**
     * 單檔上傳檔案
     *
     * @param int $user_id 使用者帳號 PK
     * @param string $upload_id 上傳階段唯一識別碼
     * @param string $filename 檔案名稱
     * @param \Illuminate\Http\UploadedFile $file 分塊檔案
     * @return array<string, string|int>
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function singleFileUpload(int $user_id, string $upload_id, string $filename, UploadedFile $file): array
    {
        /** @var string 暫存資料夾名稱 */
        $temp_folder = Uuid::uuid4()->toString();
        /** @var \App\Models\FileUpload */
        $upload_record = $this->file_upload_repository->create([
            'user_id' => $user_id,
            'upload_id' => $upload_id,
            'folder' => $temp_folder,
            'filename' => $filename,
            'status' => IsFinish::FINISHED->value
        ]);

        /** @var string 顯示的資料夾名稱 */
        $display_folder = Uuid::uuid4()->toString();
        /** @var string 儲存資料夾名稱 */
        $save_folder = Uuid::uuid4()->toString();
        /** @var string 檔案名稱 */
        $save_filename = Uuid::uuid4()->toString();

        $final_file = Utils::composePath(PathType::SAVE_PATH, $save_folder);
        $file->move($final_file, $save_filename);

        $uploaded_file = $this->file_repository->create([
            'user_id' => $user_id,
            'folder' => $save_folder,
            'filename' => $save_filename,
            'display_folder' => $display_folder,
            'original_filename' => Utils::trimFilename($upload_record->filename),
            'status' => 1,
        ]);

        return [
            'id' => $uploaded_file->id,
            'path' => $uploaded_file->display_folder . '/' . $uploaded_file->original_filename
        ];
    }

    /**
     * 分塊上傳檔案
     *
     * @param int $user_id 使用者帳號 PK
     * @param string $upload_id 上傳階段唯一識別碼
     * @param string $filename 檔案名稱
     * @param \Illuminate\Http\UploadedFile $chunk 分塊檔案
     * @param int $count 分塊計數器
     * @param bool $is_last 是否為最後一塊分塊
     * @return void
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function chunkFileUpload(int $user_id, string $upload_id, string $filename, UploadedFile $chunk, int $count, bool $is_last): void
    {
        $upload_record = $this->file_upload_repository->findUploadRecordByUploadId($upload_id);
        // 找不到資料表示第一塊上傳，寫入暫存資訊，否則直接拿資料夾名稱使用
        if (is_null($upload_record)) {
            $folder = Uuid::uuid4()->toString();
            /** @var \App\Models\FileUpload */
            $upload_record = $this->file_upload_repository->create([
                'user_id' => $user_id,
                'upload_id' => $upload_id,
                'folder' => $folder,
                'filename' => $filename,
                'status' => IsFinish::UPLOADING->value
            ]);
        } else {
            $folder = $upload_record->folder;
        }

        // 最後一塊要把暫存資訊標示為上傳完成
        if ($is_last) {
            $this->file_upload_repository->safeUpdate(
                $upload_record->id,
                ['status' => IsFinish::FINISHED->value]
            );
        }

        $this->moveToTempFolder(
            $filename,
            $count,
            $upload_record->temp,
            $chunk
        );
    }

    /**
     * 合併分塊並移動檔案到指定路徑
     *
     * @param string $upload_id 上傳階段唯一識別碼
     * @param int $user_id 使用者帳號 PK
     * @return array<string, string|int>
     *
     * @throws \App\Exceptions\EntityNotFoundException
     * @throws \App\Exceptions\IOException
     */
    public function mergeFile(string $upload_id): array
    {
        $file = $this->file_upload_repository->findUploadRecordByUploadId($upload_id);

        if (is_null($file)) {
            throw new EntityNotFoundException('找不到該檔案資料');
        }

        /** @var string 顯示的資料夾名稱 */
        $display_folder = Uuid::uuid4()->toString();
        /** @var string 儲存資料夾名稱 */
        $save_folder = Uuid::uuid4()->toString();
        /** @var string 檔案名稱 */
        $save_filename = Uuid::uuid4()->toString();

        $tmp_files = Utils::composePath(PathType::TEMP_PATH, $file->folder, '*.tmp');
        $final_file = Utils::composePath(PathType::SAVE_PATH, $save_folder, $save_filename);

        $chunks = glob($tmp_files);
        natsort($chunks);
        $chunks = array_values($chunks);

        $counts = count($chunks);

        $gc_path = Utils::composePath(PathType::TEMP_PATH, $file->folder);

        for ($i = 0; $i < $counts; $i++) {
            $full_path = $chunks[$i];
            if ($i == 0) {
                $check_path = Utils::getPathFromFullPath($final_file);
                Utils::checkIfDirectoryExists($check_path);
                File::move($full_path, $final_file);
            } else {
                try {
                    $buff = $this->readFile($full_path);
                    $this->writeFile($final_file, $buff);
                } catch (Exception $e) {
                    Utils::GCSpecificPath($gc_path);
                    throw $e;
                }
            }
        }

        $uploaded_file = $this->file_repository->create([
            'user_id' => $file->user_id,
            'folder' => $save_folder,
            'filename' => $save_filename,
            'display_folder' => $display_folder,
            'original_filename' => Utils::trimFilename($file->filename),
            'status' => 1,
        ]);

        Utils::GCSpecificPath($gc_path);
        $this->file_upload_repository->delete($file->id);

        return [
            'id' => $uploaded_file->id,
            'path' => $uploaded_file->display_folder . '/' . $uploaded_file->original_filename
        ];
    }

    /**
     * 刪除檔案
     *
     * @param string $folder 資料夾名稱
     * @param string $filename 檔案名稱
     * @return int
     *
     * @throws \App\Exceptions\EntityNotFoundException
     * @throws \App\Exceptions\IOException
     */
    public function deleteFile(string $folder, string $filename): int
    {
        $file = $this->file_repository->getSingleFileByFilename($folder, $filename);
        if (is_null($file)) {
            throw new EntityNotFoundException('找不到檔案');
        }

        $result = $this->removeFile(Utils::composePath(PathType::SAVE_PATH, $file->folder, $file->filename), true);
        if (! $result) {
            throw new IOException('移除檔案失敗');
        }

        $effected = $this->file_repository->safeDelete($file->id);

        return $effected;
    }

    /**
     * 取得檔案資訊
     *
     * @param string $folder 資料夾名稱
     * @param string $filename 檔案名稱
     * @return array<string, string>
     *
     * @throws \App\Exceptions\EntityNotFoundException
     */
    public function getFileInformation(string $folder, string $filename): array
    {
        $filename = urldecode($filename);
        $file = $this->file_repository->getSingleFileByFilename($folder, $filename);

        $fullpath = Utils::composePath(PathType::SAVE_PATH, $file->folder, $file->filename);
        $size = Utils::calculateFileSize(0, filesize($fullpath));

        return [
            'filename' => $file->original_filename,
            'filesize' => $size,
            'createdAt' => $file->created_at,
            'updatedAt' => $file->updated_at,
        ];
    }

    /**
     * 取得單一檔案完整路徑
     *
     * @param string $folder 資料夾名稱
     * @param string $filename 檔案名稱
     * @return array<string, string>
     *
     * @throws \App\Exceptions\EntityNotFoundException
     */
    public function getSingleFile(string $folder, string $filename): array
    {
        $filename = urldecode($filename);
        $file = $this->file_repository->getSingleFileByFilename($folder, $filename);

        $fullpath = Utils::composePath(PathType::SAVE_PATH, $file->folder, $file->filename);
        $real_filename = $file->original_filename;

        return [
            'fullpath' => $fullpath,
            'real_filename' => $real_filename,
        ];
    }

    /**
     * 多檔包成壓縮檔，並返回完整路徑與檔名
     *
     * @param array<int, string> $files 檔案名稱
     * @return array<string, string>
     */
    public function zipMultipleFiles(array $filenames): array
    {
        $processed_filenames = $this->preprocessMultipleFilenames($filenames);
        $file_infos = $this->file_repository->getFileByFoldersAndFilenames($processed_filenames);
        if ($file_infos->count() === 0) {
            throw new EntityNotFoundException('依據給定的檔名找不到任何檔案');
        }

        $zip = new ZipArchive();
        $zip_name = Uuid::uuid4()->toString().'.zip';

        $zip_file = Utils::composePath(type: PathType::ZIP_PATH, filename: $zip_name);

        $result = $zip->open($zip_file, ZipArchive::CREATE);
        if ($result === false) {
            throw new IOException('壓縮檔建立失敗，請再試一次');
        }

        $index = 0;
        foreach ($file_infos as $info) {
            $fullpath = Utils::composePath(PathType::SAVE_PATH, $info->folder, $info->filename);

            if (! File::exists($fullpath)) {
                continue;
            }

            $filename = $info->original_filename;
            if ($zip->locateName($filename, ZipArchive::FL_NODIR) !== false) {
                $extensions = Utils::getExtensionsFromFilename($filename);
                $filename =
                    str_replace('.'.$extensions, '', $filename).
                    ' ('.$index.').'.
                    $extensions;
            }

            $zip->addFile($fullpath, $filename);
            $index++;
        }

        $zip->close();

        Utils::GC();

        return [
            'fullpath' => $zip_file,
            'real_filename' => $zip_name,
        ];
    }

    /**
     * 把傳入的資料夾名稱與檔案名稱處理成陣列資料
     *
     * @param array<int, string> $filenames 資料夾名稱與檔案名稱
     * @return array<int, array<string, string>>
     */
    protected function preprocessMultipleFilenames(array $filenames): array
    {
        $processed = [];

        foreach ($filenames as $filename) {
            $exploded = explode('/', $filename);
            if (count($exploded) != 2) {
                throw new InvalidArgumentException('給定的資料中包含有多層資料夾結構，系統並不支援此種結構');
            }

            $processed[] = [
                'folder' => $exploded[0],
                'filename' => $exploded[1],
            ];
        }

        return $processed;
    }

    /**
     * 移動到暫存資料夾
     *
     * @param string $filename 檔案名稱
     * @param int $count 分塊計數器
     * @param string $tmp_folder 暫存資料夾名稱
     * @param \Illuminate\Http\UploadedFile $chunk 分塊檔案
     * @return void
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    protected function moveToTempFolder(string $filename, int $count, string $tmp_folder, UploadedFile $chunk): void
    {
        $temp_directory = Utils::composePath(type: PathType::TEMP_PATH, folder: $tmp_folder);

        $tmp_filename = $filename.'.chunked-'.$count.'.tmp';
        $chunk->move($temp_directory, $tmp_filename);
    }

    /**
     * 讀取檔案並返回
     *
     * @param string $full_path 完整檔案路徑
     * @param int|null $read_size 讀取大小
     * @return string
     *
     * @throws \App\Exceptions\IOException
     */
    protected function readFile(string $full_path, int $read_size = null): string
    {
        if (is_null($read_size)) {
            $read_size = filesize($full_path);
        }

        $chunk = fopen($full_path, 'rb');
        $buff = fread($chunk, $read_size);
        fclose($chunk);

        if ($buff === false) {
            throw new IOException('讀取分塊檔案失敗');
        }

        return $buff;
    }

    /**
     * 寫入檔案
     *
     * @param string $file 要寫入的檔案
     * @param string $content 要寫入的內容
     * @return bool
     *
     * @throws \App\Exceptions\IOException
     */
    protected function writeFile(string $file, string $content): bool
    {
        $final = fopen($file, 'ab');
        $write = fwrite($final, $content);
        fclose($final);

        if ($write === false) {
            throw new IOException('寫入檔案失敗');
        }

        return true;
    }

    /**
     * 移除檔案
     *
     * @param string $path 檔案路徑
     * @param bool $remove_folder 是否連同資料夾一起移除 (若資料夾內仍有檔案則此參數無效)
     * @return bool 移除成功或失敗
     */
    protected function removeFile(string $path, bool $remove_folder = false): bool
    {
        if (! File::exists($path)) {
            return false;
        }

        $unlink_result = unlink($path);

        if ($remove_folder) {
            $folder = explode(DIRECTORY_SEPARATOR, $path);
            array_pop($folder);
            $folder = implode(DIRECTORY_SEPARATOR, $folder);

            $file_count = count(array_diff(scandir($folder), ['.', '..']));
            if ($file_count === 0) {
                rmdir($folder);
            }
        }

        return $unlink_result;
    }
}
