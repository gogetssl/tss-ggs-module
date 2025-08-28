<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Tools;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentInterface;

class IdGenerator
{
    private static array $ids = [];

    public static function generate(ComponentInterface $component): string
    {
        return self::generateIdAndPush($component::class);
    }

    protected static function generateIdAndPush(string $className): string
    {
        $defaultId = crc32($className);
        $index                 = isset(self::$ids[$defaultId]) ? self::$ids[$defaultId] + 1 : 1;
        self::$ids[$defaultId] = $index;

        return 'id' . $defaultId . self::$ids[$defaultId];
    }
}