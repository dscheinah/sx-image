<?php

namespace Sx\Image\DTO;

class RenderedImageDTO
{
    /**
     * The base64 encoded content of the image.
     *
     * @var string
     */
    public string $base64;

    /**
     * The real width of the image.
     *
     * @var positive-int
     */
    public int $width;

    /**
     * The real height of the image.
     *
     * @var positive-int
     */
    public int $height;
}
