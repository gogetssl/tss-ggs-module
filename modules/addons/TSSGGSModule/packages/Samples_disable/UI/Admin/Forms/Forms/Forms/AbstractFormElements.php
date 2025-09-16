<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Forms\Forms\Forms;

use ModulesGarden\TSSGGSModule\Components\Button\ButtonSubmitSuccess;
use ModulesGarden\TSSGGSModule\Components\ColorPicker\ColorPicker;
use ModulesGarden\TSSGGSModule\Components\CopyToClipboardButton\CopyToClipboardButton;
use ModulesGarden\TSSGGSModule\Components\DatePicker\DatePicker;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\Builder;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputFile\FormInputFile;
use ModulesGarden\TSSGGSModule\Components\FormInputGroup\FormInputGroup;
use ModulesGarden\TSSGGSModule\Components\FormInputGroupLabel\FormInputGroupLabel;
use ModulesGarden\TSSGGSModule\Components\FormInputLabel\FormInputLabel;
use ModulesGarden\TSSGGSModule\Components\FormInputPassword\FormInputPassword;
use ModulesGarden\TSSGGSModule\Components\FormInputText;
use ModulesGarden\TSSGGSModule\Components\ImagePicker\ImagePicker;
use ModulesGarden\TSSGGSModule\Components\ImageSelector\ImageSelector;
use ModulesGarden\TSSGGSModule\Components\Number\Number;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Components\Tagger\Tagger;
use ModulesGarden\TSSGGSModule\Components\TextArea\TextArea;
use ModulesGarden\TSSGGSModule\Components\UploadField\UploadField;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Packages\MediaLibrary\UI\Admin\Pages\MediaLibrary;
use ModulesGarden\TSSGGSModule\Packages\MediaLibrary\UI\Admin\Pages\MediaLibrarySelectOnly;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Forms\Forms\Components\MediaLibraryPresentOnly;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Forms\Forms\Dropdowns\AjaxSearch;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Home\SimpleForm\Providers\SimpleFormProvider;

class AbstractFormElements extends Form implements AdminAreaInterface
{
    protected string $provider = SimpleFormProvider::class;
    protected string $providerAction = SimpleFormProvider::ACTION_UPDATE;
    /**
     * @var Builder
     */
    protected Builder $builder;

 

    public function loadData(): void
    {
        // die('adaw23232d');
        /*$clients = \WHMCS\User\Client::select('id', 'firstname')->get()->toArray();

        $dataProv = new ArrayDataProvider();
        $dataProv->setDefaultSorting('id', 'desc')->setData($clients);
        $this->setDataProvider($dataProv);*/
    }

    public function loadHtml(): void
    {
        $this->createImagePicker();
        $this->createInputText();
        $this->createInputFile();
        $this->createPassword();
        $this->createSwitcher();
        $this->createTagger();
        $this->createTextarea();
        $this->createDropdowns();
        $this->createDropdownAjaxSearch();
        $this->createFormInputGroup();
        $this->createPasswordGenerator();
        $this->createDatePicker();
        $this->createColorPicker();
        $this->createNumberField();
        //$this->createSubmitButton();
    }

    protected function createImagePicker()
    {
        $this->builder->createField(ImageSelector::class, 'imageSelector', true)
            ->setMediaLibrary(new MediaLibrarySelectOnly());

        $this->builder->createField(ImagePicker::class, 'imagePicker', true)
            ->setMediaLibrary(new MediaLibrarySelectOnly());
    }

    protected function createInputText()
    {
        $this->builder->createField(FormInputText\FormInputText::class, 'text', true)
            ->setDefaultValue('QQQQQQQQ')
            ->setReadOnly()
            ->setPlaceholder('232323');
    }

    protected function createInputFile()
    {
        $this->builder->createField(UploadField::class, 'fileXXX');
    }

    protected function createPassword()
    {
        $this->builder->createField(FormInputPassword::class, 'password');
    }

    protected function createSwitcher()
    {
        $field = new Switcher();
        $field->setName('switcher');
        $field->setTitle("Customowy Title");
        $field->setDescription("Customowy Description");
        $this->builder->addField($field);
    }

    protected function createTagger()
    {
        $this->builder->addField((new Tagger())
            ->setName('tagger')
            ->setOptions([
                [
                    'value' => 1,
                    'name'  => 'XXXXtttt',
                ],
                [
                    'value' => 2,
                    'name'  => 'YYYY',
                ],
            ])
            ->setValue(2)
        );
    }

    protected function createTextarea()
    {
        $this->builder->addField((new TextArea())->setName('Textarea'));
    }

    protected function createDropdowns()
    {
        $this->builder->addField((new Dropdown())
            ->setName('dropdown][')
            ->setOptions([
                [
                    'value' => 1,
                    'name'  => 'XXXX',
                    'group' => 'Z'
                ],
                [
                    'value' => 2,
                    'name'  => 'YYYY',
                    'group' => 'Y'
                ],
            ])
            ->setMultiple()
            ->setGroups([
                'Z' => 'Z Name',
                'Y' => 'Y Name'
            ])
            ->setValue(1, 2)
        );
    }

    protected function createDropdownAjaxSearch()
    {
        $this->builder->addField(new AjaxSearch());
    }

    protected function createFormInputGroup()
    {
        $inputGrup = new FormInputGroup();
        $inputGrup->addElement((new FormInputGroupLabel())->setText('/home/user'));
        $inputGrup->addElement((new FormInputText\FormInputText())->setName('X1234'));
        $this->builder->addField($inputGrup);

        $inputGrup = new FormInputGroup();
        $inputGrup->addElement((new FormInputText\FormInputText())->setName('Xw12234'));
        $inputGrup->addElement((new FormInputGroupLabel())->setText('@'));
        $inputGrup->addElement((new Dropdown())
            ->setName('dropdown2')
            ->setOptions([
                [
                    'value' => 1,
                    'name'  => 'XXXX',
                ],
                [
                    'value' => 2,
                    'name'  => 'YYYY',
                ],
            ])
            ->setDefaultValue(2));
        $this->builder->addField($inputGrup);

        $inputGrup = new FormInputGroup();
        $inputGrup->addElement((new FormInputGroupLabel())->setText('/home/user'));
        $inputGrup->addElement((new Dropdown())
            ->setName('dropdown3')
            ->setOptions([
                [
                    'value' => 1,
                    'name'  => 'XXXX',
                ],
                [
                    'value' => 2,
                    'name'  => 'YYYY',
                ],
            ])
            ->setDefaultValueAsFirstOption());
        $this->builder->addField($inputGrup);
    }

    protected function createPasswordGenerator()
    {
        $passwordGenetator = new \ModulesGarden\TSSGGSModule\Components\FormPasswordGenerator\FormPasswordGenerator();
        $passwordGenetator->setName('xxxxx');

        $copyToClipboard = new CopyToClipboardButton();
        $copyToClipboard->copyFromUsingId($passwordGenetator->getId());

        $passwordGenetator->addElement($copyToClipboard);

        $this->builder->addField($passwordGenetator);
    }

    protected function createDatePicker()
    {
        $datePicker = new DatePicker();
        $datePicker->setPlaceholder(date('Y-m-d'));

        $this->builder->addField($datePicker);
    }

    protected function createColorPicker()
    {
        $colorPicker = new ColorPicker();
        $colorPicker->setValue('8A3CFF');

        $this->builder->addField($colorPicker);
    }

    protected function createNumberField()
    {
        $this->builder->addField((new Number('number'))
            ->setRange(10, 100)
            ->setStep(5)
            ->required()
        );
    }

    protected function createSubmitButton()
    {
        $this->builder->addField((new ButtonSubmitSuccess())
            ->setTitle('Submit')
            ->onClick(new FormSubmit($this)));
    }
}
