<?php

declare(strict_types=1);

namespace Differ\Formatters;

use Differ\Formatters\Stylish ;
use Differ\Formatters\Plain;
use Differ\Formatters\Json;

const FORMAT_DEFAULT = 'stylish';
const FORMAT_PLAIN   = 'plain';
const FORMAT_JSON    = 'json';

/**
 * @throws \InvalidArgumentException|\Exception
 */
function format(string $format, array $diffItems): string
{
    return match ($format) {
        FORMAT_DEFAULT => Stylish\render($diffItems),
        FORMAT_PLAIN   => Plain\render($diffItems),
        FORMAT_JSON    => Json\render($diffItems),
        default        => throw new \InvalidArgumentException('Unexpected format type')
    };
}
