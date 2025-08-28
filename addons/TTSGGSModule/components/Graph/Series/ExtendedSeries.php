<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph\Series;

use ModulesGarden\TTSGGSModule\Components\Graph\Source\SeriesInterface;

class ExtendedSeries implements SeriesInterface
{
    protected string $name;
    protected array $data;
    protected ?string $type = null;
    protected ?string $color = null;
    protected ?bool $hidden = null;

    public function __construct(string $name, array $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setHidden(bool $hidden = true): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getSeries()
    {
        return array_filter([
            "name"   => $this->name,
            "data"   => $this->data,
            "type"   => $this->type,
            "color"  => $this->color,
            "hidden" => $this->hidden
        ]);
    }
}