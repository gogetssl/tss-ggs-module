<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Actions;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractActionInterface;

class Redirect extends AbstractActionInterface
{
    protected string $url = '';
    protected array $params = [];
    protected string $target = '';
    protected array $targetData = [];

    protected string $type = 'simple';

    public function __construct(string $url, array $params = [])
    {
        $this->url    = $url;
        $this->params = array_map(function($param) {
            return (string)$param;
            }, $params);
    }

    public function openNewWindow(array $data = []): self
    {
        $this->target = 'new';
        $this->targetData = $data;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'action'        => 'redirect',
            'target'        => $this->target,
            'targetData'    => $this->getParsedTargetData(),
            'url'           => $this->url,
            'params'        => $this->params,
            'type'          => $this->type
        ];
    }

    public function getParsedTargetData(): string
    {
        //format 'width=300,height=250'

        return implode(",", array_map(function ($property, $value) {
            return $property . "=" . $value ;
        }, array_keys($this->targetData), $this->targetData));
    }
}
