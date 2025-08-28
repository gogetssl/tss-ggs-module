<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Messages;

use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;

class Message
{
    public const TYPE_FLASH = 'flash';
    public const TYPE_ALERT = 'alert';
    public const TYPE_TOAST = 'toast';
    protected $title = '';
    protected $text = '';
    protected $type = '';
    protected $displayType = '';

    public function withSuccess(): self
    {
        $this->setType(Color::SUCCESS);

        return $this;
    }

    public function withWarning(): self
    {
        $this->setType(Color::WARNING);

        return $this;
    }

    public function withInfo(): self
    {
        $this->setType(Color::INFO);

        return $this;
    }

    public function withDanger(): self
    {
        $this->setType(Color::DANGER);

        return $this;
    }

    public function asAlert(): self
    {
        $this->setDisplayType('alert');

        return $this;
    }

    public function asToast(): self
    {
        $this->setDisplayType('toast');

        return $this;
    }

    public function asFlash(): self
    {
        return $this->setDisplayType('flash');

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    protected function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDisplayType(): string
    {
        return $this->displayType;
    }

    /**
     * @param mixed $displayType
     */
    protected function setDisplayType(string $displayType): self
    {
        $this->displayType = $displayType;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'title'       => $this->title,
            'text'        => $this->text,
            'type'        => $this->type,
            'displayType' => $this->displayType
        ];
    }
}