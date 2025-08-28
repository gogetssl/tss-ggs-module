<?php

namespace ModulesGarden\TTSGGSModule\Components\FileManager\Source;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ActionInterface;

abstract class Item
{
    protected string $name;
    protected string $fileSize;
    protected string $filePermissions;
    protected string $modification;
    protected array $actionButtons;

    protected static bool $isDir = false;
    protected static string $icon;

    abstract public function getClickAction(AbstractComponent $component):?ActionInterface;

    public function __construct($name, $fileSize, $filePermissions, $modification, $actionButtons = [])
    {
        $this->setName($name);
        $this->setFileSize($fileSize);
        $this->setFilePermissions($filePermissions);
        $this->setModification($modification);
        $this->setActionButtons($actionButtons);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return static::$icon;
    }

    /**
     * @return string
     */
    public function getFileSize(): string
    {
        return $this->fileSize;
    }

    /**
     * @param string $fileSize
     */
    public function setFileSize(string $fileSize): self
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilePermissions(): string
    {
        return $this->filePermissions;
    }

    /**
     * @param string $filePermissions
     */
    public function setFilePermissions(string $filePermissions): self
    {
        $this->filePermissions = $filePermissions;

        return $this;
    }

    /**
     * @return string
     */
    public function getModification(): string
    {
        return $this->modification;
    }

    /**
     * @param string $modification
     */
    public function setModification(string $modification): self
    {
        $this->modification = $modification;

        return $this;
    }

    /**
     * @return array
     */
    public function getActionButtons(): array
    {
        return $this->actionButtons;
    }

    /**
     * @param array $actionButtons
     */
    public function setActionButtons(array $actionButtons): self
    {
        $this->actionButtons = $actionButtons;

        return $this;
    }

    /**
     * @param bool
     */
    public function isDir(): bool
    {
        return static::$isDir;
    }
}