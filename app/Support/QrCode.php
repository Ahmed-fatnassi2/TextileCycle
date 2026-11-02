<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCode
{
    // Returns an SVG document (without the XML declaration, so it can be inlined in HTML).
    public static function svg(string $data, int $size = 240): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle($size, 1), new SvgImageBackEnd()));

        return preg_replace('/^<\?xml[^>]*>\s*/', '', $writer->writeString($data));
    }
}
