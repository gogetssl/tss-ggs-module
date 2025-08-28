<?php

namespace ModulesGarden\TTSGGSModule\Core\Traits;

use ModulesGarden\TTSGGSModule\Core\HandlerError\ErrorCodes\ErrorCodes;
use ModulesGarden\TTSGGSModule\Core\HandlerError\ErrorCodes\ErrorCodesLib;

/**
 * @deprecated
 */
trait ErrorCodesLibrary
{
    /**
     * @var ErrorCodesLib
     */
    protected $errorCodesAppHandler = null;

    /**
     * @var ErrorCodesLib
     */
    protected $errorCodesCoreHandler = null;

    public function genErrorCode($code = null)
    {
        $this->loadErrorCodes();

        if ($this->errorCodesAppHandler->errorCodeExists($code[ErrorCodes::CODE]))
        {
            return $this->errorCodesAppHandler->getErrorMessageByCode($code[ErrorCodes::CODE]);
        }

        return $this->errorCodesCoreHandler->getErrorMessageByCode($code[ErrorCodes::CODE]);
    }

    public function loadErrorCodes()
    {
        if ($this->errorCodesCoreHandler === null)
        {
            $this->errorCodesCoreHandler = new ErrorCodesLib();
        }

        if ($this->errorCodesAppHandler === null)
        {
            $this->errorCodesAppHandler = new \ModulesGarden\TTSGGSModule\App\Helpers\ErrorCodesLib();
        }
    }
}
