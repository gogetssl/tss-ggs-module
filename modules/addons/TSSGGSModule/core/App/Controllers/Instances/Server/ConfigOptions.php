<?php

namespace ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Server;

use ModulesGarden\TSSGGSModule\Components\Alert\AlertDanger;
use ModulesGarden\TSSGGSModule\Components\BlockError\BlockError;
use ModulesGarden\TSSGGSModule\Components\PreBlock\PreBlock;
use ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\AddonController;
use ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Http\ConfigOptionsIntegration;
use ModulesGarden\TSSGGSModule\Core\Events\Events\ConfigOptionsLoaded;
use ModulesGarden\TSSGGSModule\Core\Helper\BuildUrl;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TSSGGSModule\Core\UI\ViewConfigOptions;
use function ModulesGarden\TSSGGSModule\Core\fire;

abstract class ConfigOptions extends AddonController
{
    public function runExecuteProcess($params = null)
    {
        try
        {
            fire(ConfigOptionsLoaded::class);

            if (!\ModulesGarden\TSSGGSModule\Core\Support\Facades\Request::get('loadProductConfiguration', false))
            {
                return [
                    'loading' => [
                        'Type'        => '',
                        'Description' => $this->getJsCode(),
                    ],
                ];
            }

            return parent::runExecuteProcess($params);
        }
        catch (\Throwable $t)
        {
            $error = new BlockError();
            $error->setException($t);

            $view = new ViewConfigOptions();
            $view->addElement($error);

            return (new ConfigOptionsIntegration)->runExecuteProcess($view);
        }
    }

    /**
     * @return string
     * @todo migrate it
     */
    private function getJsCode()
    {
        $dataQuery   = http_build_query(\ModulesGarden\TSSGGSModule\Core\Support\Facades\Request::getFacadeRoot()->request->all());
        $serverGroup = (int)Request::get('servergroup', 0);
        $fullUrl     = BuildUrl::fullUrl();

        //@todo refactor me
        return "
                <script>
                    $('#layers2').remove();
                    $('.lu-alert').remove();
                    $('#tblModuleSettings').addClass('hidden');
                    $('#tblMetricSettings').before('<img style=\"margin-left: 50%; margin-top: 15px; margin-bottom: 15px; height: 20px\" id=\"mg-configoptionLoader\" src=\"images/loading.gif\">');
                    
                    $.post({
                        url: '$fullUrl?$dataQuery&loadProductConfiguration=1'
                    })
                    .done(function( data ){
                        let json = JSON.parse(data);
        
                        $('#mg-configoptionLoader').remove();
                        $('#tblModuleSettings').html(json.content).removeClass('hidden');
                    });
                </script>";
    }
}
