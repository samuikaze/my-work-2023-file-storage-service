<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id 使用者帳號 PK
 * @property string $folder 儲存資料夾名稱
 * @property string $filename 檔案名稱
 * @property string $display_folder 顯示的資料夾名稱
 * @property string $original_filename 原始檔案名稱
 * @property bool $status 檔案狀態
 * @property \Carbon\Carbon $created_at 建立時間
 * @property \Carbon\Carbon $updated_at 最後更新時間
 */
class File extends Model
{
    use HasFactory;

    /**
     * 讀取的表格名稱
     *
     * @var string
     */
    protected $table = 'files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'folder',
        'filename',
        'display_folder',
        'original_filename',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
