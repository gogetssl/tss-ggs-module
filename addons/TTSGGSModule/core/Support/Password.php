<?php

namespace ModulesGarden\TTSGGSModule\Core\Support;

class Password
{
    public static function generate(int $length = 8, bool $letters = true, bool $numbers = true, bool $symbols = true, string $chars = ""): string
    {
        if (empty($chars))
        {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*-_';
        }

        $sets = [];
        preg_match_all("/[a-z]+/", $chars, $outputArray);
        if ($letters && !empty($outputArray[0]))
        {
            $sets[] = implode('', array_unique($outputArray[0]));
        }
        preg_match_all("/[A-Z]+/", $chars, $outputArray);
        if ($letters && !empty($outputArray[0]))
        {
            $sets[] = implode('', array_unique($outputArray[0]));
        }
        preg_match_all("/[0-9]+/", $chars, $outputArray);
        if ($numbers && !empty($outputArray[0]))
        {
            $sets[] = implode('', array_unique($outputArray[0]));
        }
        preg_match_all("/[\W_]+/", $chars, $outputArray);
        if ($symbols && !empty($outputArray[0]))
        {
            $sets[] = implode('', array_unique($outputArray[0]));
        }

        $password = '';

        if ($length <= count($sets))
        {
            $allSetsKeys = range(0, count($sets) - 1);

            for ($i = 0; $i < $length; $i++)
            {
                $currentSetKey = array_rand($allSetsKeys);
                unset($allSetsKeys[$currentSetKey]);

                $currentSet = $sets[$currentSetKey];
                $password .= $currentSet[array_rand(str_split($currentSet))];
            }

            return str_shuffle($password);
        }

        $all = '';
        foreach($sets as $set)
        {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }

        $all = str_split($all);
        for($i = 0; $i < $length - count($sets); $i++)
        {
            $password .= $all[array_rand($all)];
        }

        return str_shuffle($password);
    }
}