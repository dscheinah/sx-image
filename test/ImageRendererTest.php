<?php

namespace Sx\ImageTest;

use Sx\Image\ImageRenderer;
use PHPUnit\Framework\TestCase;

class ImageRendererTest extends TestCase
{
    private ImageRenderer $imageRenderer;

    private string $testTarget;

    protected function setUp(): void
    {
        $this->imageRenderer = new ImageRenderer();
        $this->testTarget = sys_get_temp_dir() . '/test-image.jpg';
    }

    /**
     * @dataProvider provideRenderToJpeg
     */
    public function testRenderToJpeg(?int $width, ?int $height, int $expectedWidth, int $expectedHeight): void
    {
        $image = $this->imageRenderer->renderToJpeg(__DIR__ . '/data/image.png', $this->testTarget, $width, $height);

        self::assertNotNull($image);
        self::assertNotEmpty($image->base64);
        self::assertEquals($expectedWidth, $image->width);
        self::assertEquals($expectedHeight, $image->height);

        self::assertFileExists($this->testTarget);
        self::assertStringEqualsFile($this->testTarget, base64_decode($image->base64));

        $testImage = $this->imageRenderer->readFromJpeg($this->testTarget, $width, $height);

        self::assertEquals($image, $testImage);

        self::assertNotNull($this->imageRenderer->readFromJpeg($this->testTarget));
        self::assertNull($this->imageRenderer->readFromJpeg($this->testTarget, width: 1));
        self::assertNull($this->imageRenderer->readFromJpeg($this->testTarget, height: 1));
    }

    public function testFileErrors(): void
    {
        self::assertNull($this->imageRenderer->renderToJpeg('/does/not/exists', $this->testTarget));
        self::assertNull(@$this->imageRenderer->renderToJpeg(__FILE__, $this->testTarget));

        self::assertNull($this->imageRenderer->readFromJpeg('/does/not/exists'));
        self::assertNull($this->imageRenderer->readFromJpeg(__FILE__));
        self::assertNull($this->imageRenderer->readFromJpeg(__DIR__ . '/data/image.png'));
    }

    public static function provideRenderToJpeg(): array
    {
        return [
            'no resize' => [null, null, 100, 100],
            'resize width' => [50, null, 50, 50],
            'resize height' => [null, 50, 50, 50],
            'resize both' => [50, 50, 50, 50],
            'crop width' => [50, 25, 50, 25],
            'crop height' => [25, 50, 25, 50],
        ];
    }

    protected function tearDown(): void
    {
        @unlink($this->testTarget);
    }
}
