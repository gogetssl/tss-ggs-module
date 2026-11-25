<?php

namespace TSSGGSModule\App\Libs\Support;

/**
 * WHMCS's internal API (localAPI) with an exception instead of an error result:
 *
 *   $result = WhmcsApi::run('AddOrder', ['clientid' => 5, 'pid' => [12], ...]);
 */
class WhmcsApi
{
    /**
     * @param string $admin user name of the admin the call runs as ('' = the first admin)
     * @return array the result of the command
     * @throws \Exception with the message WHMCS gave
     */
    public static function run(string $command, array $values = [], string $admin = ''): array
    {
        if(!function_exists('localAPI'))
        {
            throw new \Exception('localAPI function does not exists');
        }
        $result = \localAPI($command, $values, $admin);
        if(($result['result'] ?? '') === 'error')
        {
            throw new \Exception((string)($result['message'] ?? 'WHMCS API error'));
        }

        return (array)$result;
    }
}
