<?php

declare(strict_types=1);

namespace Differ\Formatters\Plain;

use function Differ\Differ\array_is_list;

use const Differ\Differ\DIFF_TYPE_ADDED;
use const Differ\Differ\DIFF_TYPE_CHANGED;
use const Differ\Differ\DIFF_TYPE_NESTED;
use const Differ\Differ\DIFF_TYPE_REMOVED;
use const Differ\Differ\DIFF_TYPE_UNCHANGED;

function renderPath(array $names): string
{
    return "'" . implode('.', $names) . "'";
}

/**
 * @throws \Exception
 */
function renderValue(mixed $value): string
{
    if (array_is_list($value) || is_bool($value) || is_null($value)) {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    if (is_int($value)) {
        return (string) $value;
    }

    if (is_object($value)) {
        return '[complex value]';
    }

    $strValue = (string) $value;

    return "'{$strValue}'";
}

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

function filterOnlyChanged(array $diffItems): array
{
    $filtered = [];

    foreach ($diffItems as $item) {
        if ($item['type'] === DIFF_TYPE_NESTED) {
            $filteredNested = filterOnlyChanged($item['nestedDiff']);

            if (count($filteredNested) === 0) {
                $filtered[] = [
                    'type'       => DIFF_TYPE_NESTED,
                    'name'       => $item['name'],
                    'nestedDiff' => $filteredNested,
                ];
            }
        } elseif ($item['type'] !== DIFF_TYPE_UNCHANGED) {
            $filtered[] = $item;
        }
    }

    return $filtered;
}

/**
 * @throws \Exception
 */
function render(array $diffItems): string
{
    $renderRows = function (array $diffItems, array $names = []) use (&$renderRows) {
        $rows = [];

        $onlyChangedItems = filterOnlyChanged($diffItems);

        foreach ($onlyChangedItems as $item) {
            $currentNames = array_merge($names, [$item['name']]);

            $rows[] = match ($item['type']) {
                DIFF_TYPE_ADDED   => renderAdded($item, $currentNames),
                DIFF_TYPE_REMOVED => renderRemoved($currentNames),
                DIFF_TYPE_CHANGED => renderChanged($item, $currentNames),
                DIFF_TYPE_NESTED  => implode("\n", $renderRows($item['nestedDiff'], $currentNames)),
                default => throw new \Exception('Unexpected differing type'),
            };
        }

        return $rows;
    };

    return implode("\n", $renderRows($diffItems));
}
