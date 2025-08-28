<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Views;

class ServerModuleClientArea extends AbstractView
{
    public function getResponse(): array
    {
        return array_merge([
            'rootElements' => json_encode(array_merge(
                $this->getBody(),
               // $this->getNavbar(),
                //$this->getBreadCrumb(),
                $this->getAlerts(),
            )),
        ], $this->getBaseElements());
    }
}