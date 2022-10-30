<?php

declare(strict_types=1);

namespace WebsysForever\Differ;

/**
 * @throws \Exception
 */
function genDiff(string $pathToFile1, string $pathToFile2): string
{
    $file1 = readJsonFile($pathToFile1);
    $file2 = readJsonFile($pathToFile2);
    $params1 = json_decode($file1, true);
    $params2 = json_decode($file2, true);

    $theSameParams = getTheSameParams($params1, $params2);
    $differentParams = getDifferentParams($params1, $params2);

    $result = array_merge($theSameParams, $differentParams);

    uksort($result, function ($key1, $key2) {
        $keys = [];

        foreach ([$key1, $key2] as $key) {
            $firstChar = substr($key, 0, 1);
            $noSignKey = ($firstChar === '-' || $firstChar === '+')
                ? substr_replace($key, '', 0)
                : $key;

            $keys[] = $noSignKey;
        }

        return $keys[0] < $keys[1];
    });

    return json_encode($result);
}

function getTheSameParams(array $params1, array $params2): array
{
    $result = [];
    foreach (getUniqueNames($params1, $params2) as $name) {
        if (isTheSameParams($name, $params1, $params2)) {
            $result[$name] = $params1[$name];
        }
    }

    return $result;
}

function getDifferentParams(array $params1, array $params2): array
{
    $result = [];

    $differentParamsNames = getDifferentParamsNames($params1, $params2);

    foreach ($differentParamsNames as $name) {
        if (array_key_exists($name, $params1)) {
            $result["-{$name}"] = $params1[$name];
        }

        if (array_key_exists($name, $params2)) {
            $result["+{$name}"] = $params2[$name];
        }
    }

    return $result;
}

function getDifferentParamsNames(array $params1, array $params2): array
{
    $uniqueNames = getUniqueNames($params1, $params2);
    foreach ($uniqueNames as $key => $name) {
        if (isTheSameParams($name, $params1, $params2)) {
            unset($uniqueNames[$key]);
        }
    }

    return $uniqueNames;
}

function isTheSameParams(string $name, array $params1, array $params2): bool
{
    return (array_key_exists($name, $params1) && array_key_exists($name, $params2)
        && $params1[$name] === $params2[$name]);
}

function getUniqueNames(array $params1, array $params2): array
{
    $allParamsNames = array_merge(array_keys($params1), array_keys($params2));
    $allParamsNames = array_unique($allParamsNames);
    sort($allParamsNames);

    return $allParamsNames;
}

/**
 * @throws \Exception
 */
function readJsonFile(string $path): string
{
    if (empty($path)) {
        throw new \Exception("File path not passed");
    }

    $realPath = realpath($path);

    if (false === $realPath) {
        throw new \Exception("File not found");
    }

    if (!is_file($realPath)) {
        throw new \Exception("The passed path points to a directory");
    }

    if (!is_readable($realPath)) {
        throw new \Exception("File isn't readable");
    }

    $content = file_get_contents($path);

    if (!$content) {
        throw new \Exception("File reading error");
    }

    return $content;
}
