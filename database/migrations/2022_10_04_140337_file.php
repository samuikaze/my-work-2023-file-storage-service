<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class File extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->comment('檔案資訊');

            $table->id();
            $table->bigInteger('user_id')->comment('使用者帳號 PK');
            $table->string('folder', 36)->comment('儲存資料夾名稱');
            $table->string('filename', 128)->comment('檔案名稱');
            $table->string('display_folder', 64)->comment('顯示的資料夾名稱');
            $table->string('original_filename', 128)->comment('原始檔案名稱');
            $table->tinyInteger('status')->default(1)->comment('檔案狀態，0: 無效，1: 有效');
            $table->dateTime('created_at')->nullable()->comment('建立時間');
            $table->dateTime('updated_at')->nullable()->comment('最後更新時間');

            $table->index('user_id', 'user_id_index');
            $table->index('filename', 'filename_index');
            $table->index('original_filename', 'original_filename_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}
