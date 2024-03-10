<?php

declare(strict_types=1);

namespace Differ\Formatters\Stylish;

use function Functional\flatten;
use function Differ\Differ\array_is_list;
use const Differ\Differ\DIFF_TYPE_ADDED;
use const Differ\Differ\DIFF_TYPE_REMOVED;
use const Differ\Differ\DIFF_TYPE_CHANGED;
use const Differ\Differ\DIFF_TYPE_UNCHANGED;
use const Differ\Differ\DIFF_TYPE_NESTED;

const DEFAULT_INDENT = 4;
const DEFAULT_INDENT_CHAR = ' ';

/**
 * @throws \Exception
 */
function makeIndent(int $depth = 0): string
{
    return str_repeat(DEFAULT_INDENT_CHAR, DEFAULT_INDENT * $depth);
}

/**
 * @throws \Exception
 */
function renderValue($value, int $depth): string
{
    if (array_is_list($value) || is_bool($value) || is_null($value)) {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    if (is_object($value)) {
        $indent = makeIndent($depth + 1);

        $leafs = array_map(
            function ($key) use ($value, $depth): string {
                $nestedIndent = makeIndent($depth + 1);

                return "{$nestedIndent}{$key}: " . renderValue($value->$key, $depth + 1);
            },
            array_keys((array) $value)
        );

        $branch = implode("\n", flatten($leafs));
        return "{\n{$branch}\n{$indent}  }";
    }

    return (string) $value;
}

/**
 * @throws \Exception
 */
function renderChanged($item, int $depth): string
{
    $indent = makeIndent($depth);
    $rowOne = "{$indent}- {$item['name']}: " . renderValue($item['oldValue'], $depth);
    $rowTwo = "{$indent}+ {$item['name']}: " . renderValue($item['newValue'], $depth);

    return implode("\n", [$rowOne, $rowTwo]);
}

/**
 * @throws \Exception
 */
function renderNested(callable $renderRows, array $item, int $depth): string
{
    $indent = makeIndent($depth);

    return "{$indent}  {$item['name']}: {\n"
        . implode("\n", flatten($renderRows($item['nestedDiff'], $depth + 1)))
        . "\n{$indent}  }";
}

/**
 * @throws \Exception
 */
function render(array $diffItems): string
{
    $renderRows = function(array $diffItems, int $depth = 1) use (&$renderRows) {
        return array_map(
            function ($item) use ($renderRows, $depth) {
                $indent = makeIndent($depth);

                return match ($item['type']) {
                    DIFF_TYPE_ADDED     => "{$indent}+ {$item['name']}: " . renderValue($item['newValue'], $depth),
                    DIFF_TYPE_REMOVED   => "{$indent}- {$item['name']}: " . renderValue($item['oldValue'], $depth),
                    DIFF_TYPE_UNCHANGED => "{$indent}  {$item['name']}: " . renderValue($item['oldValue'], $depth),
                    DIFF_TYPE_CHANGED   => renderChanged($item, $depth),
                    DIFF_TYPE_NESTED    => renderNested($renderRows, $item, $depth),
                    default             => throw new \Exception('Unexpected differing type')
                };
            },
            $diffItems
        );
    };

    $flatten = flatten(['{', $renderRows($diffItems), '}']);

    return implode("\n", $flatten);
}
