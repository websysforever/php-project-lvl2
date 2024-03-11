<?php

declare(strict_types=1);

namespace Differ\Formatters\Plain;

use function Differ\Differ\array_is_list;
use const Differ\Differ\DIFF_TYPE_ADDED;
use const Differ\Differ\DIFF_TYPE_CHANGED;
use const Differ\Differ\DIFF_TYPE_NESTED;
use const Differ\Differ\DIFF_TYPE_REMOVED;
use const Differ\Differ\DIFF_TYPE_UNCHANGED;

function renderPath(array $properties): string
{
    return implode('.', $properties);
}

/**
 * @throws \Exception
 */
function renderValue($value): string
{
    if (array_is_list($value) || is_bool($value) || is_null($value)) {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    if (is_object($value)) {
        return '[complex value]';
    }

    $strValue = (string) $value;

    return "'{$strValue}'";
}

/**
 * @throws \Exception
 */
function renderAdded(mixed $item, array $names): string
{
    return 'Property ' . renderPath($names) . ' was added with value: ' . renderValue($item['newValue']);
}

/**
 * @throws \Exception
 */
function renderChanged(mixed $item, array $names): string
{
    return 'Property '
        . renderPath($names)
        . ' was updated. From '
        . renderValue($item['oldValue'])
        . ' to ' . renderValue($item['newValue']);
}

/**
 * @throws \Exception
 */
function renderRemoved(array $names): string
{
    return 'Property ' . renderPath($names) . ' was removed';
}

/**
 * @throws \Exception
 */
function renderUnchanged(array $names): string
{
    return 'Property ' . renderPath($names) . ' not changed';
}

/**
 * @throws \Exception
 */
function render(array $diffItems): string
{
    $renderRows = function(array $diffItems, array $names = []) use (&$renderRows) {
        return array_map(
            function ($item) use ($names, $renderRows) {
                $names[] = $item['name'];

                return match ($item['type']) {
                    DIFF_TYPE_ADDED     => renderAdded($item, $names),
                    DIFF_TYPE_REMOVED   => renderRemoved($names),
                    DIFF_TYPE_UNCHANGED => renderUnchanged($names),
                    DIFF_TYPE_CHANGED   => renderChanged($item, $names),
                    DIFF_TYPE_NESTED    => implode("\n", $renderRows($item['nestedDiff'], $names)),
                    default             => throw new \Exception('Unexpected differing type')
                };
            },
            $diffItems
        );
    };

    return implode("\n", $renderRows($diffItems));
}
