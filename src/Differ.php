<?php

declare(strict_types=1);

namespace Differ\Differ;

use function Differ\Differ\Parsers\getParamsFromFile;
use function Differ\Formatters\format;
use const Differ\Formatters\FORMAT_DEFAULT;

const DIFF_TYPE_REMOVED   = 0;
const DIFF_TYPE_ADDED     = 1;
const DIFF_TYPE_UNCHANGED = 2;
const DIFF_TYPE_CHANGED   = 3;
const DIFF_TYPE_NESTED    = 4;

function array_is_list(mixed $value): bool
{
    if (!is_array($value)) {
        return false;
    }

    if ($value === []) {
        return true;
    }

    return array_keys($value) === range(0, count($value) - 1);
}

function isComplexValue($value): bool
{
    return (is_object($value) && !array_is_list((array) $value));
}

/**
 * @param mixed $oldValue
 * @param mixed $newValue
 */
function createItem(int $type, string $name, $oldValue, $newValue = null): array
{
    return [
        'type'     => $type,
        'name'     => $name,
        'oldValue' => $oldValue,
        'newValue' => $newValue,
    ];
}

function createComplexItem(string $name, array $nestedDiff): array
{
    return [
        'type'        => DIFF_TYPE_NESTED,
        'name'        => $name,
        'nestedDiff' => $nestedDiff
    ];
}

function generateDiffItems($firstParams, $secondParams): array {
    $uniqueFieldNames = getUniqueFieldNames($firstParams, $secondParams);

    $parameters = array_map(function ($name) use ($firstParams, $secondParams) {
        if (!isset($firstParams->{$name})) {
            return createItem(DIFF_TYPE_ADDED, $name, null, $secondParams->{$name});
        }

        if (!isset($secondParams->{$name})) {
            return createItem(DIFF_TYPE_REMOVED, $name, $firstParams->{$name}, null);
        }

        if (
            isComplexValue($firstParams->{$name}) &&
            isComplexValue($secondParams->{$name})
        ) {
            $nestedDiff = generateDiffItems($firstParams->{$name}, $secondParams->{$name});

            return createComplexItem($name, $nestedDiff);
        }

        if ($firstParams->{$name} === $secondParams->{$name}) {
            return createItem(DIFF_TYPE_UNCHANGED, $name, $firstParams->{$name});
        }

        return createItem(DIFF_TYPE_CHANGED, $name, $firstParams->{$name}, $secondParams->{$name});
    }, $uniqueFieldNames);

    return $parameters;
}

/**
 * @throws \Exception
 */
function genDiff(
    string $pathToFile1,
    string $pathToFile2,
    string $format = FORMAT_DEFAULT
): string {
    $firstParams = getParamsFromFile($pathToFile1);
    $secondParams = getParamsFromFile($pathToFile2);

    $paramsDiff = generateDiffItems($firstParams, $secondParams);

    return format($format, $paramsDiff);
}

function getUniqueFieldNames($paramsObj1, $paramsObj2): array
{
    $params1 = get_object_vars($paramsObj1);
    $params2 = get_object_vars($paramsObj2);

    $allParamsNames = array_merge(array_keys($params1), array_keys($params2));
    $allParamsNames = array_unique($allParamsNames);
    \sort($allParamsNames);

    return $allParamsNames;
}
