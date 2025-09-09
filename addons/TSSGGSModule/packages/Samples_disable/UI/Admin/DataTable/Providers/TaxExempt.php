<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Providers;

use ModulesGarden\TSSGGSModule\Core\Components\Actions\ReloadParent;
use ModulesGarden\TSSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Translator;
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
