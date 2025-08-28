<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Providers;

use ModulesGarden\TTSGGSModule\Core\Components\Actions\ReloadParent;
use ModulesGarden\TTSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Translator;
use WHMCS\User\Client;

class TaxExempt extends CrudProvider
{
    public function update()
    {
        Client::where('id', $this->formData['id'])->update([
            'taxexempt' => $this->formData['value'] === 'true',
        ]);

        return (new Response())->setSuccess(Translator::get('XXXX', []))->setActions([new ReloadParent()]);
    }
}
