<?php

namespace TSSGGSModule\App\Libs\Settings;

use TSSGGSModule\App\Libs\Products\CatalogueSync;
use TSSGGSModule\App\Libs\Support\GeneralSettings;

/**
 * Settings > Products: the catalogue sync mode and its e-mail, both in the general settings (the same keys the
 * Finance page used before)
 */
class ProductsSettingsProvider extends Provider
{
    const TEXTS = 'admin.settings.index.providers.products_settings_provider';

    public function read()
    {
        $this->data['catalogueSyncMode']     = CatalogueSync::mode();
        $this->data['catalogueChangesEmail'] = GeneralSettings::enabled('catalogueChangesEmail');

        // assigned whole: availableValues does not take a nested write
        $modes = [];
        foreach(CatalogueSync::MODES as $mode)
        {
            $modes[(string)$mode] = $this->translate('catalogueSync_' . $mode);
        }
        $this->availableValues['catalogueSyncMode'] = $modes;
    }

    public function update()
    {
        $data    = $this->formData->toArray();
        $general = [];
        if(array_key_exists('catalogueSyncMode', $data))
        {
            $general['catalogueSyncMode'] = in_array($data['catalogueSyncMode'], CatalogueSync::MODES, true) ? $data['catalogueSyncMode'] : CatalogueSync::DEFAULT_MODE;
        }
        if(array_key_exists('catalogueChangesEmail', $data))
        {
            $general['catalogueChangesEmail'] = !empty($data['catalogueChangesEmail']) && !in_array($data['catalogueChangesEmail'], ['off', 'false', '0'], true);
        }
        GeneralSettings::save($general);
    }
}
