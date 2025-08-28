<?php

namespace ModulesGarden\Servers\TTSGGSModule\core;

use WHMCS\Database\Capsule as DataBase;

class Lang
{
    private static $instance;
    private $dir;
    private $langs = [];
    private $currentLang;
    private $fillLangFile = true;
    public $context = [];
    private $staggedContext = [];
    private $missingLangs = [];
    private function __construct() {}
    private function __clone() {}

    public static function getInstance($dir = null, $lang = null)
    {
        if (self::$instance === null)
        {
            self::$instance      = new self();
            self::$instance->dir = $dir;
            self::$instance->loadLang('english');

            self::$instance->fillLangFile = process\MainInstance::I()->isDebug();

            if (!$lang)
            {
                $lang = self::getLang();
            }

            if ($lang && $lang != 'english')
            {
                self::$instance->loadLang($lang);
            }
        }
        return self::$instance;
    }

    public static function getMissingLangs()
    {
        return self::$instance->missingLangs;
    }

    public static function getLang()
    {
        $language = '';
        if (isset($_SESSION['Language']))
        {
            $language = strtolower($_SESSION['Language']);
        }
        elseif (isset($_SESSION['uid']))
        {
            $row = DataBase::table('tblclients')->where('id', $_SESSION['uid'])->first();
            if (isset($row->language))
            {
                $language = $row->language;
            }
        }

        if (!$language)
        {
            $row = DataBase::table('tblconfiguration')->where('setting', 'Language')->first();
            if (isset($row->value))
            {
                $language = $row->value;
            }
        }

        if (!$language)
        {
            $language = 'english';
        }


        return strtolower($language);
    }

    public static function getAvaiable()
    {
        $langArray = array();
        $handle    = opendir(self::$instance->dir);

        while (false !== ($entry = readdir($handle)))
        {
            list($lang, $ext) = explode('.', $entry);
            if ($lang && isset($ext) && strtolower($ext) == 'php')
            {
                $langArray[] = $lang;
            }
        }

        return $langArray;
    }

    public static function loadLang($lang)
    {
        $originalLanguageFile = self::getInstance()->dir . DS . $lang . '.php';
        if (file_exists($originalLanguageFile))
        {
            include $originalLanguageFile;
            self::getInstance()->langs       = array_merge(self::getInstance()->langs, $_LANG);
            self::getInstance()->currentLang = $lang;
        }

        $file = self::getInstance()->dir . DS . 'overrides' . DS . $lang . '.php';

        if (file_exists($file))
        {
            include $file;
            self::getInstance()->langs       = array_merge(self::getInstance()->langs, $_LANG);
            self::getInstance()->currentLang = $lang;
        }
    }

    public static function setContext()
    {
        self::getInstance()->context = [];
        foreach (func_get_args() as $name)
        {
            self::getInstance()->context[] = $name;
        }
    }

    public static function addToContext()
    {
        foreach (func_get_args() as $name)
        {
            self::getInstance()->context[] = $name;
        }
    }

    public static function stagCurrentContext($stagName)
    {
        self::getInstance()->staggedContext[$stagName] = self::getInstance()->context;
    }

    public static function unstagContext($stagName)
    {
        if (isset(self::getInstance()->staggedContext[$stagName]))
        {
            self::getInstance()->context = self::getInstance()->staggedContext[$stagName];
            unset(self::getInstance()->staggedContext[$stagName]);
        }
    }

    public static function T()
    {
        $lang = self::getInstance()->langs;

        $history = [];


        foreach (self::getInstance()->context as $name)
        {
            if (isset($lang[$name]))
            {
                $lang = $lang[$name];
            }

            $history[] = $name;
        }
        $returnLangArray = false;

        foreach (func_get_args() as $find)
        {

            $history[] = $find;

            if (isset($lang[$find]))
            {
                if (is_array($lang[$find]))
                {
                    $lang = $lang[$find];
                }
                else
                {
                    return htmlentities($lang[$find]);
                }
            }
            else
            {
                if (self::getInstance()->fillLangFile)
                {
                    $returnLangArray = true;
                }
                else
                {
                    return htmlentities($find);
                }
            }
        }

        if ($returnLangArray)
        {

            self::getInstance()->missingLangs['$' . "_LANG['" . implode("']['", $history) . "']"] = ucfirst(end($history));
            return '$' . "_LANG['" . implode("']['", $history) . "']";
        }
        if (is_array($lang) && self::getInstance()->fillLangFile)
        {
            self::getInstance()->missingLangs['$' . "_LANG['" . implode("']['", $history) . "']"] = implode(" ",
                array_slice($history,
                    -3,
                    3,
                    true));
            return '$' . "_LANG['" . implode("']['", $history) . "']";
        }

        return htmlentities($find);
    }

    public static function absoluteT()
    {
        $lang = self::getInstance()->langs;

        $returnLangArray = false;

        foreach (func_get_args() as $find)
        {
            $history[] = $find;
            if (isset($lang[$find]))
            {
                if (is_array($lang[$find]))
                {
                    $lang = $lang[$find];
                }
                else
                {
                    return htmlentities($lang[$find]);
                }
            }
            else
            {

                if (self::getInstance()->fillLangFile)
                {
                    $returnLangArray = true;
                }
                else
                {
                    return htmlentities($find);
                }
            }
        }


        if ($returnLangArray)
        {
            self::getInstance()->missingLangs['$' . "_LANG['" . implode("']['", $history) . "']"] = implode(" ",
                array_slice($history,
                    -3,
                    3,
                    true));
            return '$' . "_LANG['" . implode("']['", $history) . "']";
        }

        return htmlentities($lang);
    }

}
