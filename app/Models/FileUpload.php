<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id 上傳者
 * @property string $upload_id 上傳階段唯一識別碼
 * @property string $folder 暫存資料夾名稱
 * @property string $filename 原始檔案名稱
 * @property \App\Enums\IsFinish $status 上傳狀態
 * @property \Carbon\Carbon $created_at 建立時間
 * @property \Carbon\Carbon $updated_at 最後更新時間
 */
class FileUpload extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'upload_id',
        'folder',
        'filename',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
