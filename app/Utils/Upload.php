<?php

namespace App\Utils;

use Illuminate\Http\UploadedFile;
// 引具体类：facade 别名要应用启动后才注册，这里得能在容器外调用
use Illuminate\Support\Str;
use Log;
use Throwable;

/**
 * 后台图片上传的唯一入口：文件名重新生成，扩展名按文件内容推断。
 *
 * 落盘在 public/upload，入库路径是 'upload/文件名'。
 */
class Upload
{
    // 不含 svg：可携带脚本，同域可读就是存储型 XSS
    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

    /**
     * 生成落盘文件名：扩展名由文件内容推断，不在白名单内返回 null。
     */
    public static function imageName(UploadedFile $file): ?string
    {
        $extension = $file->extension();

        if (! $extension || ! in_array(strtolower($extension), self::IMAGE_EXTENSIONS, true)) {
            return null;
        }

        return Str::random(8).time().'.'.$extension;
    }

    /**
     * 存进 public/upload 并返回入库用的相对路径；失败返回 null。
     */
    public static function image(UploadedFile $file): ?string
    {
        $fileName = self::imageName($file);

        if (! $fileName) {
            return null;
        }

        try {
            $file->move(public_path('upload'), $fileName);
        } catch (Throwable $e) {
            Log::error('图片写入 public/upload 失败：'.$e->getMessage());

            return null;
        }

        return 'upload/'.$fileName;
    }
}
