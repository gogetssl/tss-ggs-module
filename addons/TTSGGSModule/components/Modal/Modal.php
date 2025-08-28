<?php

namespace ModulesGarden\TTSGGSModule\Components\Modal;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ActionOnCloseTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\AjaxTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ContentTrait;

class Modal extends AbstractComponent
{
    use AjaxTrait;
    use ActionOnCloseTrait;
    use ComponentsContainerTrait;
    use ContentTrait;

    public const COMPONENT          = 'Modal';

    public const SIZE_FULL_PAGE     = 'fullPage';
    public const SIZE_ULTRA_LARGE   = 'ultraLarge';
    public const SIZE_EXTRA_LARGE   = 'extraLarge';
    public const SIZE_LARGE         = 'large';
    public const SIZE_SMALL         = 'small';

    public const TYPE_DANGER        = 'danger';
    public const TYPE_EDIT          = 'edit';
    public const TYPE_SUCCESS       = 'success';
    public const TYPE_WARNING       = 'warning';

    protected $actionModal = false;
    protected $type = '';

    public function __construct()
    {
        parent::__construct();

        $this->setSlot('actionModal', $this->actionModal);
        $this->setSlot('type', $this->type);
    }

    /**
     * @param $button
     */
    public function addActionButton($button)
    {
        $this->addComponent('actionButtons', $button);
    }


    public function setEditMode($editMode = true): self
    {
        $this->setSlot('isEditModal', $editMode);

        return $this;
    }

    /**
     * Set modal size. Allowed values: Modal::SIZE_LARGE, Modal::SIZE_SMALL
     * @param $size
     * @return $this
     */
    public function setSize($size): self
    {
        $this->setSlot('size', $size);

        return $this;
    }

    /**
     * Set modal title
     * @param $title
     */
    public function setTitle($title): self
    {
        $this->setSlot('title', $title);

        return $this;
    }

    /**
     * Set modal type
     * @param $type
     */
    public function setType($type): self
    {
        $this->setSlot('type', $type);

        return $this;
    }
}
