<?php

declare(strict_types=1);

namespace WebsysForever\Differ\Formatters;

use WebsysForever\Differ\Formatters\Stylish ;
use WebsysForever\Differ\Formatters\Plain;
use WebsysForever\Differ\Formatters\Json;

const FORMAT_DEFAULT = 'stylish';
const FORMAT_PLAIN   = 'plain';
const FORMAT_JSON    = 'json';

/**
 * @throws \InvalidArgumentException|\Exception
 */
function format(string $format, array $diffItems): string
{
    return match($format) {
        FORMAT_DEFAULT => Stylish\render($diffItems),
        FORMAT_PLAIN   => Plain\render($diffItems),
        FORMAT_JSON    => Json\render($diffItems),
        default        => throw new \InvalidArgumentException('Unexpected format type')
    };
}