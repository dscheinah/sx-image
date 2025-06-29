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
        self::assertTrue(
            $this->imageRenderer->renderToJpeg(__DIR__ . '/data/image.png', $this->testTarget, $width, $height)
        );

        self::assertFileExists($this->testTarget);

        $sizes = getimagesize($this->testTarget);
        self::assertIsArray($sizes);

        self::assertEquals($expectedWidth, $sizes[0]);
        self::assertEquals($expectedHeight, $sizes[1]);
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
