<?php

namespace ModulesGarden\TTSGGSModule\Core\Components;

use ModulesGarden\TTSGGSModule\Core\Components\Hooks\Manager;
use ModulesGarden\TTSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentInterface;

class DataBuilder
{
    /**
     * @var AbstractComponent
     */
    protected AbstractComponent $component;

    protected bool $withHtml = false;

    protected bool $withData = false;

    public function __construct(AbstractComponent $component)
    {
        $this->component = $component;
    }

    /**
     * @return \ModulesGarden\TTSGGSModule\Core\Contracts\ResponseInterface
     * @todo - do that better...
     */
    public function returnAjaxData()
    {
        try
        {
            Manager::call(__METHOD__, [
                'component' => $this->component,
            ]);

            return $this->component->returnAjaxData();
        }
        catch (\Throwable $ex)
        {
            return (new Response())
                ->setError($ex->getMessage());
        }
    }

    public function toArray(): array
    {
        $withHtml = true;
        $withData = true;
        $toArray  = true;

        Manager::call(__METHOD__, [
            'component' => $this->component,
            'withHtml'  => &$withHtml,
            'withData'  => &$withData,
            'toArray'   => &$toArray
        ]);

        if ($this->withHtml && $withHtml)
        {
            $this->component->buildHtml();
        }

        if ($this->withData && $withData)
        {
            $this->component->preLoadData();
            $this->component->loadData();
            $this->component->postLoadData();
        }

        return $toArray ? $this->toArrayRecursive($this->component->toArray()) : [];
    }

    protected function toArrayRecursive(array $data)
    {
        foreach ($data as $key => &$val)
        {
            if (is_array($val))
            {
                $val = $this->toArrayRecursive($val);
            }

            if ($val instanceof ComponentInterface)
            {
                $val = (new self($val))
                    ->toArray();

                if (empty($val))
                {
                    unset($data[$key]);
                }
            }
        }

        return $data;
    }

    public function withHtml(bool $withHtml = true): self
    {
        $this->withHtml = $withHtml;

        return $this;
    }

    public function withData(bool $withData = true): self
    {
        $this->withData = $withData;

        return $this;
    }
}
