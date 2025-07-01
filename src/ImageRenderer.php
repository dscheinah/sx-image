<?php

namespace Sx\Image;

use GdImage;
use Sx\Image\DTO\RenderedImageDTO;

class ImageRenderer
{
    /**
     * Try to read the given source file as jpeg.
     *
     * If width or height are given, the image is checked to have the correct sizes.
     *
     * @param string $source
     * @param positive-int|null $width
     * @param positive-int|null $height
     *
     * @return RenderedImageDTO|null
     */
    public function readFromJpeg(string $source, ?int $width = null, ?int $height = null): ?RenderedImageDTO
    {
        $data = @file_get_contents($source);
        if (!$data) {
            return null;
        }

        $sizes = getimagesize($source);
        if (!$sizes) {
            return null;
        }
        if ($sizes[2] !== IMAGETYPE_JPEG) {
            return null;
        }
        if ($width && $sizes[0] !== $width) {
            return null;
        }
        if ($height && $sizes[1] !== $height) {
            return null;
        }
        assert($sizes[0] > 0);
        assert($sizes[1] > 0);

        $image = new RenderedImageDTO();
        $image->base64 = base64_encode($data);
        $image->width = $sizes[0];
        $image->height = $sizes[1];
        return $image;
    }

    /**
     * Renders the given source image to the target file.
     *
     * The image will be scaled if width or height are given. If both are present, it is auto-cropped to the new ratio.
     *
     * @param string $source
     * @param string $target
     * @param positive-int|null $width
     * @param positive-int|null $height
     *
     * @return RenderedImageDTO|null
     */
    public function renderToJpeg(string $source, string $target, ?int $width = null, ?int $height = null): ?RenderedImageDTO
    {
        $data = @file_get_contents($source);
        if (!$data) {
            return null;
        }
        $sourceImage = imagecreatefromstring($data);
        if (!$sourceImage) {
            return null;
        }

        $sizes = $this->sizes($sourceImage, $width, $height);

        $targetImage = imagecreatetruecolor($sizes[4], $sizes[5]);
        if (!$targetImage) {
            return null;
        }

        if (!imagecopyresampled($targetImage, $sourceImage, ...$sizes)) {
            return null;
        }
        if (!imagejpeg($targetImage, $target)) {
            return null;
        }

        $file = file_get_contents($target);
        if (!$file) {
            return null;
        }

        $image = new RenderedImageDTO();
        $image->base64 = base64_encode($file);
        $image->width = $sizes[4];
        $image->height = $sizes[5];
        return $image;
    }

    /**
     * Calculates position and sizes to convert from source to target.
     *
     * @param GdImage $image
     * @param positive-int|null $width
     * @param positive-int|null $height
     *
     * @return array{int, int, int, int, positive-int, positive-int, positive-int, positive-int}
     */
    private function sizes(GdImage $image, ?int $width, ?int $height): array
    {
        $w = imagesx($image);
        $h = imagesy($image);

        if (!$width && !$height) {
            return [0, 0, 0, 0, $w, $h, $w, $h];
        }
        if (!$width) {
            $calc = intdiv($w * $height, $h);
            assert($calc > 0);
            return [0, 0, 0, 0, $calc, $height, $w, $h];
        }
        if (!$height) {
            $calc = intdiv($h * $width, $w);
            assert($calc > 0);
            return [0, 0, 0, 0, $width, $calc, $w, $h];
        }

        $sourceRatio = $w / $h;
        $targetRatio = $width / $height;

        if ($sourceRatio > $targetRatio) {
            $crop = intdiv($width * $h, $height);
            assert($crop > 0);
            return [0, 0, intdiv($w - $crop, 2), 0, $width, $height, $crop, $h];
        }
        if ($sourceRatio < $targetRatio) {
            $crop = intdiv($height * $w, $width);
            assert($crop > 0);
            return [0, 0, 0, intdiv($h - $crop, 2), $width, $height, $w, $crop];
        }

        return [0, 0, 0, 0, $width, $height, $w, $h];
    }
}
