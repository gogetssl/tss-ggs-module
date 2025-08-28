<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Libs\CustomFields;

class AbstractCustomField
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $friendlyName = '';

    /**
     * @var string
     */
    protected string $fieldType = '';

    /**
     * @var string
     */
    protected string $description = '';

    /**
     * @var string
     */
    protected string $fieldOptions = '';

    /**
     * @var string
     */
    protected string $regExpr = '';

    /**
     * @var bool
     */
    protected bool $adminOnly = false;

    /**
     * @var bool
     */
    protected bool $required = false;

    /**
     * @var bool
     */
    protected bool $showOnOrder = false;

    /**
     * @var bool
     */
    protected bool $showOnInvoice = false;

    /**
     * @var int
     */
    protected int $sortOrder = 0;

    /**
     * @param string $name
     * @param string $friendlyName
     */
    public function __construct(string $name, string $friendlyName = '')
    {
        $this->name         = $name;
        $this->friendlyName = $friendlyName;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->friendlyName ? $this->name . '|' . $this->friendlyName : $this->name;
    }

    /**
     * @return string
     */
    public function getFieldType(): string
    {
        return $this->fieldType;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldOptions(): string
    {
        return $this->fieldOptions;
    }

    /**
     * @param  $fieldOptions
     */
    public function setFieldOptions($fieldOptions): self
    {
        $this->fieldOptions = is_array($fieldOptions) ? implode(',', $fieldOptions) : $fieldOptions;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegExpr(): string
    {
        return $this->regExpr;
    }

    /**
     * @param string $regExpr
     */
    public function setRegExpr(string $regExpr): self
    {
        $this->regExpr = $regExpr;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAdminOnly(): bool
    {
        return $this->adminOnly;
    }

    /**
     * @param bool $adminOnly
     */
    public function setAdminOnly(bool $adminOnly = true): self
    {
        $this->adminOnly = $adminOnly;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     */
    public function setRequired(bool $required = true): self
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShowOnOrder(): bool
    {
        return $this->showOnOrder;
    }

    /**
     * @param bool $showOnOrder
     */
    public function setShowOnOrder(bool $showOnOrder = true): self
    {
        $this->showOnOrder = $showOnOrder;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShowOnInvoice(): bool
    {
        return $this->showOnInvoice;
    }

    /**
     * @param bool $showOnInvoice
     */
    public function setShowOnInvoice(bool $showOnInvoice = true): self
    {
        $this->showOnInvoice = $showOnInvoice;

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     */
    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}