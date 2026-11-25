<?php

/**
 * PHPStan stubs of the WHMCS classes and functions the module uses. WHMCS ships them ionCube-encoded, so they can not
 * be analysed; Illuminate and Symfony are read from the WHMCS install (phpstan.neon.dist, %env.QA_WHMCS%).
 * Signatures follow the WHMCS developer documentation; "mixed" where WHMCS documents nothing better.
 * Never loaded at runtime.
 */

namespace WHMCS\Database {
    class Capsule extends \Illuminate\Database\Capsule\Manager
    {
    }
}

namespace WHMCS\Config {
    class Setting extends \Illuminate\Database\Eloquent\Model
    {
        /** @return mixed */
        public static function getValue(string $setting) {}

        /** @param mixed $value */
        public static function setValue(string $setting, $value): self {}
    }
}

namespace WHMCS\User\Admin {
    class Permission
    {
        public static function currentAdminHasPermissionName(string $name): bool {}

        /** @return array<int, string> */
        public static function all(): array {}
    }
}

namespace WHMCS {
    /** classes the module only calls into: any method, any result */
    class Helper
    {
        /** @param array<mixed> $args @return mixed */
        public static function __callStatic(string $name, array $args) {}
    }

    class URL
    {
        /** @param array<mixed> $args @return mixed */
        public static function __callStatic(string $name, array $args) {}
    }
}

namespace WHMCS\URL {
    class Admin
    {
        /** @param array<mixed> $args @return mixed */
        public static function __callStatic(string $name, array $args) {}
    }
}

namespace WHMCS\Billing {
    class Tax
    {
        /** @param array<mixed> $args @return mixed */
        public function __call(string $name, array $args) {}
        /** @param array<mixed> $args @return mixed */
        public static function __callStatic(string $name, array $args) {}
    }
}

namespace WHMCS\Utility {
    class Country
    {
        /** @param array<mixed> $args @return mixed */
        public function __call(string $name, array $args) {}
    }
}

namespace WHMCS\Mail {
    class Template extends \Illuminate\Database\Eloquent\Model
    {
    }

    class Message
    {
        /** @param array<mixed> $args @return mixed */
        public function __call(string $name, array $args) {}
        /** @param array<mixed> $args @return mixed */
        public static function __callStatic(string $name, array $args) {}
    }

    class Emailer
    {
        /** @param array<mixed> $args @return mixed */
        public function __call(string $name, array $args) {}
        /** @param array<mixed> $args @return mixed */
        public static function __callStatic(string $name, array $args) {}
    }
}

namespace WHMCS\Module {
    class Mail
    {
        /** @param array<mixed> $args @return mixed */
        public function __call(string $name, array $args) {}
        /** @param array<mixed> $args @return mixed */
        public static function __callStatic(string $name, array $args) {}
    }
}

namespace WHMCS\User {
    class Client extends \Illuminate\Database\Eloquent\Model
    {
    }
}

namespace WHMCS\Service {
    class Service extends \Illuminate\Database\Eloquent\Model
    {
    }
}

namespace {
    /** @param array<mixed> $values @return array<mixed> */
    function localAPI(string $command, array $values = [], string $adminUser = '') {}
    /** @param mixed $customVars @return mixed */
    function sendMessage($template, $relatedId, $customVars = [], bool $displayResult = false, $attachments = []) {}
    /** @return array<mixed> */
    function getCurrency($userId = '', $currencyId = '') {}
    /** @return string */
    function formatCurrency($amount, $currencyId = false) {}
    function logActivity(string $description, int $userId = 0): void {}
    /** @param array<mixed> $params @return mixed */
    function ServerRenew($params) {}
    function encrypt(string $string): string {}
    function decrypt(string $string): string {}
    /** @param mixed $request @param mixed $response @param mixed $data @param array<mixed> $replace */
    function logModuleCall(string $module, string $action, $request, $response, $data = '', array $replace = []): void {}
    /** @return float */
    function convertCurrency($amount, $fromCurrency, $toCurrency, $baseCurrencyExchangeRate = '') {}
    /** @return mixed */
    function recalcRecurringProductPrice($serviceId, $userId = '', $pid = '', $billingCycle = '', $configOptionsRecurring = 'empty', $promoId = 0, $includeSetup = false) {}
    function add_hook(string $hookPoint, int $priority, callable $function): void {}
    /** @return string */
    function generate_token(string $type = 'form') {}
    /** @param string|array<mixed> $vars */
    function redir($vars = '', string $file = '') {}
    /** @return array<mixed> */
    function getCartConfigOptions($pid, $values, $cycle, $accountId = '', $orderForm = '', $showHiddenOptions = false) {}
    /** @param mixed $vars @return array<mixed> */
    function run_hook(string $hookName, $vars, bool $unpackArguments = false) {}
    /** checks the CSRF token of the request (dies on a wrong one) @return mixed */
    function check_token(string $type = 'WHMCS.default') {}
}
