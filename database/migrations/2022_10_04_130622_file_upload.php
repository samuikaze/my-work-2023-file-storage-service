<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FileUpload extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_uploads', function (Blueprint $table) {
            $table->comment('檔案上傳暫存表');

            $table->id();
            $table->bigInteger('user_id')->comment('使用者帳號 PK');
            $table->string('upload_id', 36)->comment('上傳階段唯一識別碼');
            $table->string('folder', 36)->comment('暫存資料夾名稱');
            $table->string('filename', 128)->comment('原始檔案名稱');
            $table->tinyInteger('status')->default(0)->comment('0: 上傳中, 1: 上傳完成, 2: 上傳中斷');
            $table->dateTime('created_at')->nullable()->comment('建立時間');
            $table->dateTime('updated_at')->nullable()->comment('最後更新時間');

            $table->index('user_id', 'user_id_index');
            $table->index('upload_id', 'upload_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_uploads');
    }
}
