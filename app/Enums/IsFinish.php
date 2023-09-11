<?php

namespace App\Enums;

enum IsFinish: int
{
    /**
     * 上傳中
     *
     * @var int
     */
    case UPLOADING = 0;

    /**
     * 上傳完成
     *
     * @var int
     */
    case FINISHED = 1;

    /**
     * 上傳被中止
     *
     * @var int
     */
    case TERMINATED = 2;
}
