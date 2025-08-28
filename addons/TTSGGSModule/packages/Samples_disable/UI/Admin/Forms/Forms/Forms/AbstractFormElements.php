<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Forms\Forms\Forms;

use ModulesGarden\TTSGGSModule\Components\Button\ButtonSubmitSuccess;
use ModulesGarden\TTSGGSModule\Components\ColorPicker\ColorPicker;
use ModulesGarden\TTSGGSModule\Components\CopyToClipboardButton\CopyToClipboardButton;
use ModulesGarden\TTSGGSModule\Components\DatePicker\DatePicker;
use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\Builder;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\FormInputFile\FormInputFile;
use ModulesGarden\TTSGGSModule\Components\FormInputGroup\FormInputGroup;
use ModulesGarden\TTSGGSModule\Components\FormInputGroupLabel\FormInputGroupLabel;
use ModulesGarden\TTSGGSModule\Components\FormInputLabel\FormInputLabel;
use ModulesGarden\TTSGGSModule\Components\FormInputPassword\FormInputPassword;
use ModulesGarden\TTSGGSModule\Components\FormInputText;
use ModulesGarden\TTSGGSModule\Components\ImagePicker\ImagePicker;
use ModulesGarden\TTSGGSModule\Components\ImageSelector\ImageSelector;
use ModulesGarden\TTSGGSModule\Components\Number\Number;
use ModulesGarden\TTSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TTSGGSModule\Components\Tagger\Tagger;
use ModulesGarden\TTSGGSModule\Components\TextArea\TextArea;
use ModulesGarden\TTSGGSModule\Components\UploadField\UploadField;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Packages\MediaLibrary\UI\Admin\Pages\MediaLibrary;
use ModulesGarden\TTSGGSModule\Packages\MediaLibrary\UI\Admin\Pages\MediaLibrarySelectOnly;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Forms\Forms\Components\MediaLibraryPresentOnly;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Forms\Forms\Dropdowns\AjaxSearch;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Home\SimpleForm\Providers\SimpleFormProvider;

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
        $passwordGenetator = new \ModulesGarden\TTSGGSModule\Components\FormPasswordGenerator\FormPasswordGenerator();
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
