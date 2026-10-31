<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Content;

use ModulesGarden\TSSGGSModule\App\Migration\Credentials;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared\WizardLayout;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms\ApiSettingsForm;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\Components\Action;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;

class Step4 extends Widget implements AjaxComponentInterface
{
    use TranslatorTrait;

    public function loadHtml(): void
    {
        WizardLayout::prepareStep($this, 4, $this->translate('step4_title'));

        [, $filled] = Credentials::prefilled();
        if($filled)
        {
            $names = array_map(function($s) { return $s === 'ggs' ? 'GoGetSSL' : 'TheSSLStore'; }, $filled);
            $note  = new Text();
            $note->setCss('tssggs-html');
            $note->setText('<div class="tssggs-note is-ok">' . htmlspecialchars($this->translate('credentials_prefilled', ['modules' => implode(', ', $names)])) . '</div>');
            $this->addElement($note);
        }

        $this->addElement(new ApiSettingsForm(true));

    }
}