<?php

namespace ModulesGarden\TTSGGSModule\Core\Helper\Converter;

class Json
{
    public static function encodeUTF8($array)
    {
        array_walk_recursive($array, function(&$item, $key) {
            if (is_array($item) || is_object($item))
            {
                foreach ($item as &$param)
                {
                    if (is_array($param) || is_object($param))
                    {
                        $param = self::encodeUTF8($param);
                    }
                    else
                    {
                        if (!mb_detect_encoding($param, 'utf-8', true))
                        {
                            $param = utf8_encode($param);
                        }
                    }
                }
            }
            else
            {
                if (!mb_detect_encoding($item, 'utf-8', true))
                {
                    $item = utf8_encode($item);
                }
            }
        });

        return $array;
    }
}
