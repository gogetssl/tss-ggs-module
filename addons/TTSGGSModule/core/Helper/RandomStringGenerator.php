<?php

namespace ModulesGarden\TTSGGSModule\Core\Helper;

/**
 * Helper for generating random strings
 */
class RandomStringGenerator
{
    protected $charSet = '';
    protected $lowerCharSet = 'qwertyuioplkjhgfdsazxcvbnm';
    protected $numbersCharSet = '0123456789';
    protected $stringLength = 10;
    protected $upperCharSet = 'QWERTYUIOPLKJHGFDSAZXCVBNM';
    protected $useLowercase = true;
    protected $useNumbers = true;
    protected $useUppercase = false;

    public function __construct($stringLength = null, $useNumbers = true, $useLowercase = true, $useUppercase = false)
    {
        $this->setLength($stringLength);

        $this->setUseNumbers($useNumbers);
        $this->setUseUppercase($useUppercase);
        $this->setUseLowercase($useLowercase);
    }

    public function setLength($stringLength)
    {
        if ((int)$stringLength > 0)
        {
            $this->stringLength = (int)$stringLength;
        }
    }

    public function setUseNumbers($value = true)
    {
        if (is_bool($value))
        {
            $this->useNumbers = $value;
        }

        $this->loadCharSet();
    }

    public function loadCharSet()
    {
        $this->charSet = '';
        if ($this->useNumbers)
        {
            $this->charSet .= $this->numbersCharSet;
        }
        if ($this->useLowercase)
        {
            $this->charSet .= $this->lowerCharSet;
        }
        if ($this->useUppercase)
        {
            $this->charSet .= $this->upperCharSet;
        }

        //use default set if someone disables all sets
        if ($this->charSet === '')
        {
            $this->charSet = $this->numbersCharSet . $this->lowerCharSet;
        }
    }

    public function setUseUppercase($value = true)
    {
        if (is_bool($value))
        {
            $this->useUppercase = $value;
        }

        $this->loadCharSet();
    }

    public function setUseLowercase($value = true)
    {
        if (is_bool($value))
        {
            $this->useLowercase = $value;
        }

        $this->loadCharSet();
    }

    public function genRandomString(?string $prefix = null)
    {
        $randString = '';
        while (strlen($randString) < $this->stringLength)
        {
            $number     = rand(0, strlen($this->charSet) - 1);
            $randString .= $this->charSet[$number];
        }

        if ($prefix)
        {
            $randString = $prefix . '_' . $randString;
        }

        return $randString;
    }
}
