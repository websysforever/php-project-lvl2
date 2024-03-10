<?php

declare(strict_types=1);

namespace WebsysForever\Differ\Formatters\Stylish;

use function Functional\flatten;
use const WebsysForever\Differ\DIFF_TYPE_ADDED;
use const WebsysForever\Differ\DIFF_TYPE_REMOVED;
use const WebsysForever\Differ\DIFF_TYPE_CHANGED;
use const WebsysForever\Differ\DIFF_TYPE_UNCHANGED;
use const WebsysForever\Differ\DIFF_TYPE_COMPLEX;

const DEFAULT_INDENT = 4;
const DEFAULT_LEFT_OFFSET = 2;
const DEFAULT_INDENT_CHAR = ' ';

/**
 * @throws \Exception
 */
function makeIndent(int $depth = 0): string
{
    $repeatCount = (DEFAULT_INDENT - DEFAULT_LEFT_OFFSET) * $depth;

    return str_repeat(DEFAULT_INDENT_CHAR, $repeatCount);
}

/**
 * @throws \Exception
 */
function renderValue($value, int $depth): string
{
    if (is_array($value) || is_bool($value) || is_null($value)) {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    if (is_object($value)) {
        $indent = makeIndent($depth);

        $leafs = array_map(
            function ($key) use ($value, $depth): string {
                $nestedIndent = makeIndent($depth + 1);

                return "{$nestedIndent} {$key}: " . renderValue($value->$key, $depth + 1);
            },
            array_keys((array) $value)
        );

        $branch = implode("\n", flatten($leafs));
        return "{\n {$branch} \n {$indent}}";
    }

    return (string) $value;
}

/**
 * @throws \Exception
 */
function renderChanged($item, int $depth): string
{
    $indent = makeIndent($depth);
    $rowOne = "{$indent} - {$item['name']}: " . renderValue($item['oldValue'], $depth);
    $rowTwo = "{$indent} + {$item['name']}: " . renderValue($item['newValue'], $depth);

    return implode("\n", [$rowOne, $rowTwo]);
}

/**
 * @throws \Exception
 */
function render(array $diffItems, int $depth = 0): string
{
    $indent = makeIndent($depth);
    $nextDepth = ++$depth;

    $renderedItems = array_map(
        function ($item) use ($nextDepth) {
            $nestedIndent = makeIndent($nextDepth);

            return match ($item['type']) {
                DIFF_TYPE_ADDED     => "{$nestedIndent} + {$item['name']}: " . renderValue($item['newValue'], $nextDepth),
                DIFF_TYPE_REMOVED   => "{$nestedIndent} - {$item['name']}: " . renderValue($item['oldValue'], $nextDepth),
                DIFF_TYPE_UNCHANGED => "{$nestedIndent}   {$item['name']}: " . renderValue($item['oldValue'], $nextDepth),
                DIFF_TYPE_CHANGED   => renderChanged($item, $nextDepth),
                DIFF_TYPE_COMPLEX   => "{$nestedIndent}   {$item['name']}: " . render($item['nestedDiff']),
                default             => throw new \Exception('Unexpected differing type')
            };
        },
        $diffItems
    );

    $flatten = flatten(["{$indent}\{", $renderedItems, "{$indent}\}"]);

    return implode("\n", $flatten);
}
