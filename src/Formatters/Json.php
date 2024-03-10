<?php

declare(strict_types=1);

namespace Differ\Formatters\Json;

/**
 * @throws \Exception
 */
function render(array $diffItems): string
{
    return json_encode($diffItems, JSON_THROW_ON_ERROR, JSON_PRETTY_PRINT);
}
