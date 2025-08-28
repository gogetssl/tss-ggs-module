<?php

namespace ModulesGarden\TTSGGSModule\Core\Http;

use Exception;
use InvalidArgumentException;
use ModulesGarden\TTSGGSModule\Core\Helper\Converter\Json;
use ModulesGarden\TTSGGSModule\Core\ServiceLocator;
use Symfony\Component\HttpFoundation\JsonResponse as SymfonyJsonResponse;

/**
 * Class JsonResponse
 */
class JsonResponse extends SymfonyJsonResponse
{
    protected $lang;

    public function getLang()
    {
        return $this->lang;
    }

    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    public function withError($message = '')
    {
        $data            = $this->getData();
        $data['status']  = 'error';
        $data['message'] = $message;

        $this->setData($data);

        return $this;
    }

    public function getData()
    {
        $data = null;
        if (defined('HHVM_VERSION'))
        {
            // HHVM does not trigger any warnings and let exceptions
            // thrown from a JsonSerializable object pass through.
            // If only PHP did the same...
            $data = json_decode($this->data, true, 512, $this->encodingOptions);
        }
        else
        {
            try
            {
                // PHP 5.4 and up wrap exceptions thrown by JsonSerializable
                // objects in a new exception that needs to be removed.
                // Fortunately, PHP 5.5 and up do not trigger any warning anymore.
                $data = json_decode($this->data, true, 512, $this->encodingOptions);
            }
            catch (Exception $e)
            {
                if ('Exception' === get_class($e) && 0 === strpos($e->getMessage(), 'Failed calling '))
                {
                    throw $e->getPrevious() ?: $e;
                }
            }
        }

        if (JSON_ERROR_NONE !== json_last_error())
        {
            throw new InvalidArgumentException(json_last_error_msg());
        }

        return $data;
    }

    public function setData($data = [])
    {
        try
        {
            return parent::setData($data);
        }
        catch (Exception $e)
        {
            return parent::setData(Json::encodeUTF8($data));
        }
    }

    public function withSuccess($message = '')
    {
        $data            = $this->getData();
        $data['status']  = 'success';
        $data['message'] = $message;

        $this->setData($data);

        return $this;
    }
}
