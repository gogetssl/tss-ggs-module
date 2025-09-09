<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Forms;


use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Modals\ReportingModal;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Providers\ReportingProvider;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Components\RevertSwitcher;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonCancel;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\DatePicker\DatePicker;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\Icon\Icon;
use ModulesGarden\TSSGGSModule\Components\TextArea\TextArea;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalClose;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;


class ReportingForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    public function __construct()
    {
        parent::__construct();


        $this->provider                  = ReportingProvider::class;
        $this->providerAction            = ReportingProvider::ACTION_CREATE;
        $this->providerActionsToValidate = ['send', 'download'];
    }

    public function loadHtml(): void
    {
        $moduleRepository    = new AddonModuleRepository();
        $moduleConfiguration = $moduleRepository->getModuleConfiguration();


        $this->builder = BuilderCreator::twoColumns($this);
        $this->setId('ReportingForm');


        $this->builder->addField(
            (new FormInputText())
                ->setName('whmcsUrl')
                ->setReadOnly(true)
        );

        $this->builder->addField(
            (new FormInputText())
                ->setName('moduleVersion')
                ->setReadOnly(true)
        );

        if($moduleConfiguration['credentials']['ggs']['PartnerCode'])
        {
            $this->builder->addField(
                (new FormInputText())
                    ->setName('ggsPartnerId')
                    ->setReadOnly(true)
            );
        }

        if($moduleConfiguration['credentials']['tss']['PartnerCode'])
        {
            $this->builder->addField(
                (new FormInputText())
                    ->setName('tssPartnerId')
                    ->setReadOnly(true)
            );
        }

        if(!$moduleConfiguration['credentials']['ggs']['PartnerCode'] || !$moduleConfiguration['credentials']['tss']['PartnerCode'])
        {
            $this->builder->addelement(new Icon());//empty field
        }

        $this->builder->addField(
            (new FormInputText())
                ->setName('phpVersion')
                ->setReadOnly(true)
        );
        /*
                $this->builder->addField(
                    (new FormInputText())
                        ->setName('productionMode')
                        ->setReadOnly(true)
                );
        */
        $this->builder->addField(
            (new FormInputText())
                ->setName('cronStatus')
                ->setReadOnly(true)
        );

        $this->builder->addField(
            (new DatePicker())
                ->setName('fromDate')
                ->required()

        );
        $this->builder->addField(
            (new DatePicker())
                ->setName('toDate')
                ->required()
        );

        $this->builder->addField(
            (new TextArea())
                ->setName('issueDetails')
                ->required()
        );

        //Helpers::updateChecksumData();
        $checksumCompare = Helpers::compareChecksumData();
        $modified        = count($checksumCompare['new']) + count($checksumCompare['modified']) + count($checksumCompare['deleted']);

        if($modified > 0)
        {
            $class = 'lu-alert lu-alert--faded lu-alert--danger';

            if($modified == 1)
            {
                $value = $this->translate('modifiedFile');
            }
            else
            {
                $value = $this->translate('modifiedFiles', [':modifiedCount' => $modified]);
            }
        }
        else
        {
            $class = 'lu-alert lu-alert--faded lu-alert--success';
            $value = $this->translate('noModifiedFiles');
        }


        $this->builder->addField(
            (new FormInputText())
                ->setName('modifiedModuleFiles')
                ->setReadOnly(true)
                ->setValue($value)
                ->setCss($class)
        );

        $toolbar = new Toolbar();
        $toolbar->addElement(
            (new ButtonSuccess())
                ->setId('send')
                ->setTitle($this->translate('send'))
                ->onClick(
                    (new FormSubmit($this))->setCustomAction('send')
                )
        );

        $toolbar->addElement(
            (new ButtonSuccess())
                ->setId('send')
                ->setTitle($this->translate('download'))
                ->onClick(
                    (new FormSubmit($this))->setCustomAction('download')
                )
        );

        $toolbar->addElement(
            (new ButtonCancel())
                ->setId('cancel')
                ->setTitle($this->translate('cancel'))
                ->onClick(
                    (new ModalClose(new ReportingModal()))
                )
        );

        $this->addElement($toolbar);
    }
}