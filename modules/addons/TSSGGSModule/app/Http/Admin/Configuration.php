<?php

namespace ModulesGarden\TSSGGSModule\App\Http\Admin;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Pages\ConfigurationStep1;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Pages\ConfigurationStep2;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Pages\ConfigurationStep3;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Pages\ConfigurationStep4;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Pages\ConfigurationStep5;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Pages\ConfigurationStep6;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Pages\ConfigurationStep7;
use ModulesGarden\TSSGGSModule\App\Libs\Edition;
use ModulesGarden\TSSGGSModule\App\Migration\Choice;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared\WizardLayout;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TSSGGSModule\Core\Helper;

class Configuration extends AbstractController implements AdminAreaInterface
{
    public function index()
    {
        return Helper\redirect('configuration', 'step1');
    }

    public function step1()
    {
        return Helper\view()
            ->addElement(ConfigurationStep1::class);
    }

    public function step2()
    {
        return Helper\view()
            ->addElement(ConfigurationStep2::class);
    }
    /**
     * Legacy modules found. Without one the wizard has no such step: the vendor is fixed by the edition.
     */
    public function step3()
    {
        if(!WizardLayout::hasLegacy())
        {
            (new AddonModuleRepository())->saveVendor([Edition::get()]);

            return Helper\redirect('configuration', 'step4');
        }

        return Helper\view()
            ->addElement(ConfigurationStep3::class);
    }

    /**
     * POST from step 3: Migrate / Install standalone per legacy module. Vendors to configure in step 4 are the
     * edition's own plus every legacy module chosen for migration.
     */
    public function saveLegacyChoice()
    {
        $token = (string)($_POST['token'] ?? '');
        $valid = (string)($_SESSION['tkval'] ?? '');
        if($_SERVER['REQUEST_METHOD'] !== 'POST' || $token === '' || $valid === '' || !hash_equals($valid, $token))
        {
            return Helper\redirect('configuration', 'step3');
        }

        $vendors = [Edition::get()];
        foreach((array)($_POST['choice'] ?? []) as $source => $choice)
        {
            if(!in_array($source, ['ggs', 'tss'], true))
            {
                continue;
            }
            $choice = $choice === Choice::MIGRATE ? Choice::MIGRATE : Choice::SKIP;
            Choice::set($source, $choice);
            if($choice === Choice::MIGRATE)
            {
                $vendors[] = $source;
            }
        }
        (new AddonModuleRepository())->saveVendor(array_values(array_unique($vendors)));

        return Helper\redirect('configuration', 'step4');
    }

    public function step4()
    {
        return Helper\view()
            ->addElement(ConfigurationStep4::class);
    }

    public function step5()
    {
        return Helper\view()
            ->addElement(ConfigurationStep5::class);
    }

    public function step6()
    {
        return Helper\view()
            ->addElement(ConfigurationStep6::class);
    }

    public function step7()
    {
        return Helper\view()
            ->addElement(ConfigurationStep7::class);
    }


}
