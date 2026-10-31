<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\Edition;
use ModulesGarden\TSSGGSModule\App\Migration\Choice;
use ModulesGarden\TSSGGSModule\App\Migration\Detector;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Redirect;
use ModulesGarden\TSSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Core\Routing\Url;

/**
 * Saves the Migrate / Install standalone decision per legacy module. Vendors to configure in step 4 are the
 * edition's own plus every legacy module chosen for migration.
 */
class LegacyChoiceProvider extends CrudProvider
{
    public function read()
    {
        foreach(Detector::detect() as $source => $info)
        {
            $options = [Choice::SKIP => $this->translate('option_skip')];
            if($info['support']['supported'])
            {
                $options = [Choice::MIGRATE => $this->translate('option_migrate')] + $options;
            }
            $this->availableValues['choice_' . $source] = $options;
            $this->data['choice_' . $source]            = Choice::get($source) ?: ($info['support']['supported'] ? Choice::MIGRATE : Choice::SKIP);
        }
    }

    public function update()
    {
        $vendors = [Edition::get()];
        foreach(array_keys(Detector::detect()) as $source)
        {
            $choice = $this->formData['choice_' . $source] ?? Choice::SKIP;
            $choice = $choice === Choice::MIGRATE ? Choice::MIGRATE : Choice::SKIP;
            Choice::set($source, $choice);
            if($choice === Choice::MIGRATE)
            {
                $vendors[] = $source;
            }
        }
        (new AddonModuleRepository())->saveVendor(array_values(array_unique($vendors)));

        return (new Response())->setSuccess($this->translate('saved'))->setActions([new Redirect(Url::route('',
            ['module' => 'TSSGGSModule', 'mg-page' => 'configuration', 'mg-action' => 'step4']
        ))]);
    }
}
