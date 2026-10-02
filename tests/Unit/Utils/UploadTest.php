<?php

namespace Tests\Unit\Utils;

use App\Utils\Upload;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

/**
 * 上传文件名：扩展名取自文件内容，svg 不在白名单内。
 */
class UploadTest extends TestCase
{
    /**
     * @var list<string>
     */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $path) {
            @unlink($path);
        }

        $this->files = [];

        parent::tearDown();
    }

    public function test_image_name_accepts_a_real_png_renamed_to_jpg(): void
    {
        $file = $this->fakeUpload('logo.jpg', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='), 'image/jpeg');

        $name = Upload::imageName($file);

        // 客户端报的是 jpg，内容决定落盘扩展名
        $this->assertNotNull($name);
        $this->assertStringEndsWith('.png', $name);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{8}\d{10}\.png$/', $name);
    }

    public function test_image_name_rejects_a_script_named_as_an_image(): void
    {
        $file = $this->fakeUpload('logo.png', "<?php echo 'pwned';\n", 'image/png');

        $this->assertNull(Upload::imageName($file));
    }

    public function test_image_name_rejects_svg_even_though_it_passes_the_image_rule(): void
    {
        $file = $this->fakeUpload('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"></svg>', 'image/svg+xml');

        $this->assertNull(Upload::imageName($file));
    }

    private function fakeUpload(string $clientName, string $contents, string $mimeType): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'otaku-upload-test-');
        file_put_contents($path, $contents);
        $this->files[] = $path;

        return new UploadedFile($path, $clientName, $mimeType, null, true);
    }
}
