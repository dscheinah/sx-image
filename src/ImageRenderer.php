<?php

namespace Sx\Image;

use GdImage;

class ImageRenderer
{
    /**
     * Renders the given source image to the target file.
     *
     * The image will be scaled if width or height are given. If both are present, it is auto-cropped to the new ratio.
     *
     * @param string            $source
     * @param string            $target
     * @param positive-int|null $width
     * @param positive-int|null $height
     *
     * @return bool
     */
    public function renderToJpeg(string $source, string $target, ?int $width = null, ?int $height = null): bool
    {
        $data = @file_get_contents($source);
        if (!$data) {
            return false;
        }
        $sourceImage = imagecreatefromstring($data);
        if (!$sourceImage) {
            return false;
        }

        $sizes = $this->sizes($sourceImage, $width, $height);

        $targetImage = imagecreatetruecolor($sizes[4], $sizes[5]);
        if (!$targetImage) {
            return false;
        }

        return imagecopyresampled($targetImage, $sourceImage, ...$sizes)
            && imagejpeg($targetImage, $target);
    }

    /**
     * Calculates position and sizes to convert from source to target.
     *
     * @param GdImage           $image
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
