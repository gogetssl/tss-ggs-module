<?php

namespace ModulesGarden\TSSGGSModule\Core\Components\FormFields;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ActionOnChangeTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ValidatorRulesTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ValueTrait;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\FormFieldInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ValueInterface;
use ModulesGarden\TSSGGSModule\Core\Helper\ModuleNamespace;

/**
 * Class Form
 */
class FormField extends AbstractComponent implements ValueInterface, FormFieldInterface
{
    // const COMPONENT = 'FormField';

    use CssContainerTrait;
    use ValidatorRulesTrait;
    use ValueTrait;
    use ActionOnChangeTrait;

    protected string $customTitle = '';
    protected string $description = '';

    public function __construct()
    {
        parent::__construct();

        $this->setName($id ?? ModuleNamespace::convertNamespaceToString($this));
    }

    public function setName(string $name): self
    {
        $this->setSlot('name', $name);

        return $this;
    }

    public function getName(): string
    {
        return $this->getSlot('name');
    }

    public function setTitle(string $title): self
    {
        $this->customTitle = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->customTitle;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDefaultValue($defaultValue): self
    {
        $this->setSlot('defaultValue', $defaultValue);

        return $this;
    }

    public function setDisabled(bool $disabled = true): self
    {
        $this->setSlot('disabled', $disabled);

        return $this;
    }

    public function setPlaceholder(string $placeholder): self
    {
        $this->setSlot('placeholder', $placeholder);

        return $this;
    }

    public function setReadOnly(bool $readonly = true) : self
    {
        $this->setSlot('readonly', $readonly);

        return $this;
    }

    public function setAutocomplete(string $autocomplete) : self
    {
        $this->setSlot('autocomplete', $autocomplete);

        return $this;
    }
}
