<?php

namespace ModulesGarden\TTSGGSModule\Core\DataProviders;

use ModulesGarden\TTSGGSModule\Core\Contracts\CrudProviderInterface;
use ModulesGarden\TTSGGSModule\Core\Security\IntegrityToken\IntegrityToken;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Validator;
use ModulesGarden\TTSGGSModule\Core\Translation\TranslatorTrait;

class CrudProvider implements CrudProviderInterface
{
    use TranslatorTrait;

    public const ACTION_CREATE = 'create';
    public const ACTION_DELETE = 'delete';
    public const ACTION_READ   = 'read';
    public const ACTION_UPDATE = 'update';
    protected $actionElementId;
    protected DataContainer $availableValues;

    /**
     * @var DataContainer
     */
    protected DataContainer $data;

    /**
     * @var DataContainer
     */
    protected DataContainer $formData;

    protected DataContainer $ajaxData;
    protected bool $crfProtection = true;

    protected string $csrfToken = '';

    protected array $integrityCheckFields = [
        'id',
    ];

    protected string $integrityCheckToken = '';

    public function __construct()
    {
        $this->formData        = new DataContainer((array)\ModulesGarden\TTSGGSModule\Core\Support\Facades\Request::get('formData', []));
        $this->ajaxData        = new DataContainer((array)\ModulesGarden\TTSGGSModule\Core\Support\Facades\Request::get('ajaxData', []));
        $this->data            = new DataContainer();
        $this->availableValues = new DataContainer();

        $this->actionElementId = $this->formData->get('id');
    }

    public function afterRead()
    {
        $this->addIntegrityCheckToken();
    }

    public final function beforeCreate()
    {
        $this->validateCsrf();
    }

    public function create()
    {
    }


    public final function beforeDelete()
    {
        $this->validateCsrf();

        //@TODO - add validation for delete
        //$this->validateIntegrityCheckToken();
    }

    public function delete()
    {
    }

    public final function beforeRead()
    {
    }

    public function read()
    {
        $this->data = clone $this->formData;
    }

    public function beforeUpdate()
    {
        $this->validateCsrf();
        $this->validateIntegrityCheckToken();
    }

    public function update()
    {
    }

    final public function getAvailableValuesById($name): array
    {
        return $this->availableValues->get($name, []);
    }

    final public function getValueById($name)
    {
        return $this->data->get($name);
    }

    protected function validate(array $data, array $rules, array $translationAttributes = [])
    {
        $customAttributes = [];
        $customValues     = [];

        foreach ($translationAttributes as $attribute => $values)
        {
            $customAttributes[$attribute] = $this->translate("attributes.$attribute");

            foreach ($values as $value)
            {
                $customValues[$attribute][$value] = $this->translate("values.$attribute.$value");
            }
        }

        Validator::validate($data, $rules, $customAttributes, $customValues);
    }

    protected final function validateCsrf()
    {
        if ($this->crfProtection)
        {
            \ModulesGarden\TTSGGSModule\Core\Security\CsrfToken\CsrfToken::validate(get_class($this), \ModulesGarden\TTSGGSModule\Core\Support\Facades\Request::get('csrfProtection', ''));
        }
    }

    public function getIntegrityCheckToken(): string
    {
        return $this->integrityCheckToken;
    }

    protected function addIntegrityCheckToken(): void
    {
        if (!$this->integrityCheckFields)
        {
            return;
        }

        $this->integrityCheckToken = (new IntegrityToken())->generate($this->data->getMany($this->integrityCheckFields));
    }

    protected function validateIntegrityCheckToken(): void
    {
        $fields = array_filter($this->formData->getMany($this->integrityCheckFields));

        //If there is no fields in request that should be validate do not validate
        if (!$fields)
        {
            return;
        }

        (new IntegrityToken())->validate($fields, Request::get('integrityCheckToken'));
    }
}
