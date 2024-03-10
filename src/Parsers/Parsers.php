<?php

declare(strict_types=1);

namespace WebsysForever\Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

/**
 * @throws \Exception
 */
function getParamsFromFile(string $pathToFile): object
{
    $fileContent = readFile($pathToFile);

    return getParamsFromFileByType($pathToFile, $fileContent);
}

/**
 * @throws \Exception
 */
function getParamsFromFileByType(string $pathToFile, string $fileContent): \stdClass
{
    $ext = pathinfo($pathToFile, PATHINFO_EXTENSION);

    switch ($ext) {
        case 'json':
            $params = getJsonParams($fileContent);

            break;
        case 'yml':
        case 'yaml':
            $params = getYmlParams($fileContent);

            break;
        default:
            throw new \DomainException('Неизвестный тип файла');
    }

    return $params;
}

/**
 * @throws \Exception
 */
function getJsonParams(string $data): \stdClass
{
    return json_decode($data, false);
}

/**
 * @throws \Exception
 */
function getYmlParams(string $data): \stdClass
{
    return Yaml::parse($data, Yaml::PARSE_OBJECT_FOR_MAP);
}

/**
 * @throws \Exception
 */
function getParser(string $file): string
{

}

/**
 * @throws \Exception
 */
function readFile(string $path): string
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
