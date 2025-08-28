<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\API;

use ModulesGarden\TTSGGSModule\Core\HandlerError\Exceptions\Exception;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Admins;

class API
{
    protected $admins;

    /**
     * @var string
     */
    protected $username;

    /**
     * @param Admins $admins
     * @throws Exception
     */
    public function __construct(string $username = '')
    {
        if (function_exists('localAPI') === false)
        {
            throw new Exception('localAPI function does not exists');
        }

        $this->username = $username;
    }

    /**
     * @param $command
     * @param $values
     * @param $username
     * @return mixed
     * @throws Exception
     */
    public static function run(string $command, array $values = [], string $username = '')
    {
        return (new self($username))->call($command, $values);
    }

    /**
     * @param $command
     * @param $config
     * @return mixed
     * @throws \Exception
     */
    public function call(string $command, array $config = [])
    {
        $result = localAPI($command, $config, $this->username);

        if ($result['result'] == 'error')
        {
            throw new \Exception($result['message']);
        }

        return $result;
    }

    public function __call(string $command, array $config = [])
    {
        return $this->call($command, $config);
    }
}
