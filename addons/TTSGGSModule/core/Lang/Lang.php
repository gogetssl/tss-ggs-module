<?php

namespace ModulesGarden\TTSGGSModule\Core\Lang;

use ArrayAccess;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;
use function ModulesGarden\TTSGGSModule\Core\Helper\sl;
use function ModulesGarden\TTSGGSModule\Core\translator;

/**
 * @deprecated - use trlanslator() function
 */
class Lang implements ArrayAccess
{
    /**
     * @var array
     */
    public $context = [];

    /**
     * @var type
     */
    private $currentLang;

    /**
     * @var string
     */
    private $dir;

    /**
     * @var bool
     */
    private $fillLangFile = true;
    private $isDebug;
    private $langReplacements = [];

    /**
     * @var array
     */
    private $langs = [];

    /**
     * @var array
     */
    private $missingLangs = [];

    /**
     * @var array
     */
    private $staggedContext = [];

    /**
     * @param type $dir
     * @param type $lang
     */
    public function __construct($dir = null, $lang = null)
    {
        $this->isDebug = Config::get('configuration.debug');
    }

    public function get(array $langPath)
    {
        $langPathDotted = implode('.', $langPath);

        if (!translator()->has($langPathDotted))
        {
            $this->addMissingLang($langPath);

            return $this->parserMissingLang($langPath);
        }

        return translator()->get($langPathDotted);
    }

    /**
     * @param array $history
     * @param bool $returnLangArray
     */
    protected function addMissingLang($history, $returnLangArray = false)
    {
        if ($returnLangArray)
        {
            $this->missingLangs['$' . "_LANG['" . implode("']['", $history) . "']"] = ucfirst(end($history));
        }
        else
        {
            $this->missingLangs['$' . "_LANG['" . implode("']['", $history) . "']"] = implode(' ', array_slice($history, -3, 3, true));
        }
    }

    /**
     * @param array $history
     * @return string
     */
    protected function parserMissingLang($history)
    {
        if ($this->isDebug)
        {
            return '$' . "_LANG['" . implode("']['", $history) . "']";
        }

        return end($history);
    }

    /**
     * Deprecated
     * @return type mixed
     */
    public function absoluteT()
    {
        return call_user_func_array([$this, 'absoluteTranslate'], func_get_args());
    }

    /**
     * Get Translated Absolute Lang
     *
     * @return string
     */
    public function absoluteTranslate()
    {
        return $this->get(func_get_args());
    }

    /**
     * Alias for absoluteTranslate method
     * @return type mixed
     */
    public function abtr()
    {
        return call_user_func_array([$this, 'absoluteTranslate'], func_get_args());
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function addReplacementConstant($key, $value)
    {
        $this->langReplacements[$key] = $value;

        return $this;
    }

    public function addToContext()
    {
        foreach (func_get_args() as $name)
        {
            $this->context[] = $name;
        }
    }

    /**
     * Alias for absoluteTranslate method
     * @return type mixed
     */
    public function cctr()
    {
        return call_user_func_array([$this, 'controlerContextTranslate'], func_get_args());
    }

    /**
     * Deprecated
     * @return type mixed
     */
    public function controlerContextT()
    {
        return call_user_func_array([$this, 'controlerContextTranslate'], func_get_args());
    }

    /**
     * Get Translated Lang From Main Controler Context
     *
     * @return string
     */
    public function controlerContextTranslate()
    {
        $tempContext      = $this->context;
        $controlerContext = array_slice($tempContext, 0, 2);

        $this->context = $controlerContext;
        $args          = func_get_args();

        $last    = end($args);
        $lastKey = key($args);
        unset($args[$lastKey]);

        foreach ($args as $cont)
        {
            $this->context[] = $cont;
        }

        $result = $this->T($last);

        $this->context = $tempContext;

        return $result;
    }

    /**
     * Deprecated
     * @return type mixed
     */
    public function T()
    {
        return call_user_func_array([$this, 'translate'], func_get_args());
    }

    public function getLangData()
    {
        return $this;
    }

    public function getMissingLangs()
    {
        return $this->missingLangs;
    }

    public function loadLang($lang)
    {
        $file = $this->dir . DIRECTORY_SEPARATOR . $lang . '.php';
        if (file_exists($file))
        {
            include $file;
            $this->langs       = array_merge($this->langs, $_LANG);
            $this->currentLang = $lang;
        }
    }

    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
    }

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

    public function setContext()
    {
        $this->context = [];
        foreach (func_get_args() as $name)
        {
            $this->context[] = $name;
        }
    }

    public function setLang($lang = 'english')
    {
    }

    public function stagCurrentContext($stagName)
    {
        $this->staggedContext[$stagName] = $this->context;
    }

    /**
     * Alias for translate method
     * @return type mixed
     */
    public function tr()
    {
        return call_user_func_array([$this, 'translate'], func_get_args());
    }

    /**
     * Get Translated Lang
     *
     * @return string
     */
    public function translate()
    {
        return $this->get(array_merge($this->context, func_get_args()));
    }

    public function unstagContext($stagName)
    {
        if (isset($this->staggedContext[$stagName]))
        {
            $this->context = $this->staggedContext[$stagName];
            unset($this->staggedContext[$stagName]);
        }
    }
}
