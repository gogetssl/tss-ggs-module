<?php

namespace ModulesGarden\TTSGGSModule\Components\ListInfo;

class ListInfoItem
{
    protected $title;
    protected $value;
    protected array $elements = [];
    protected $icon;

    public function __construct($title, $value)
    {
        $this->setTitle($title);
        $this->setValue($value);
    }


    /**
     * @param mixed $title
     * @return ListInfoItem
     */
    public function setTitle($title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param mixed $value
     * @return ListInfoItem
     */
    public function setValue($value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param array $elements
     * @return ListInfoItem
     */
    public function setElements(array $elements): self
    {
        $this->elements = $elements;
        return $this;
    }

    /**
     * @param mixed $icon
     * @return ListInfoItem
     */
    public function setIcon($icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'title'    => $this->title,
            'value'    => $this->value,
            'elements' => $this->elements,
            'icon'     => $this->icon
        ];
    }
}
