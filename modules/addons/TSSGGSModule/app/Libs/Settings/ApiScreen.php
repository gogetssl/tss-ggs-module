<?php

namespace TSSGGSModule\App\Libs\Settings;

use TSSGGSModule\App\Kernel\Lang;
use TSSGGSModule\App\Libs\Support\Edition;
use TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;

/**
 * What Settings > API shows besides its fields: the vendor accounts of the screen (the edition's own, then the other
 * vendor the administrator can connect), which of their tokens are stored (those fields show a mask), and the note
 * about the buttons working on both accounts.
 */
class ApiScreen
{
    public static function data(Lang $lang): array
    {
        $stored  = (new AddonModuleRepository())->storedCredentials();
        $has     = function($vendor, $field) use ($stored) {
            return trim((string)($stored[$vendor][$field] ?? '')) !== '';
        };
        $primary = Edition::primaryVendor();
        $other   = Edition::otherVendor();

        $accounts = [];
        foreach([$primary, $other] as $vendor)
        {
            $accounts[] = [
                'vendor'  => $vendor,
                'own'     => $vendor === $primary,
                'section' => $vendor === $primary ? $vendor : 'connect_' . $vendor,
                'icon'    => $vendor === $primary ? 'key-outline' : 'power-plug-outline',
                // a stored token is never sent back to the page: its field shows a mask as the placeholder
                'hasLive' => $has($vendor, 'AuthToken'),
                'hasTest' => $has($vendor, 'TestAuthToken'),
            ];
        }

        return [
            'accounts' => $accounts,
            'scope'    => $lang->get('admin.settings.index.forms.api_settings_form.buttonsScope', ['primary' => Edition::brandOf($primary), 'other' => Edition::brandOf($other)]),
        ];
    }
}
